<?php

namespace App\Http\Controllers;

use App\Models\KnnTrain;
use App\Models\KnnTest;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\DB;

class KnnController extends Controller
{
    public function index()
    {
        $trainData = KnnTrain::all();
        $testData = KnnTest::all();
        return view('knn.index', compact('trainData', 'testData'));
    }

    public function uploadTrain(Request $request)
    {
        $request->validate([
            'train_file' => 'required|file|mimes:xlsx,xls,csv'
        ]);

        $file = $request->file('train_file');
        $spreadsheet = IOFactory::load($file->getRealPath());
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray();

        // Skip header
        array_shift($rows);

        DB::beginTransaction();
        try {
            KnnTrain::truncate();
            foreach ($rows as $row) {
                KnnTrain::create([
                    'id' => $row[0], // Assuming ID is in first column
                    'sex' => $row[1],
                    'marital_status' => $row[2],
                    'age' => $row[3],
                    'education' => $row[4],
                    'income' => $row[5],
                    'occupation' => $row[6],
                    'settlement_size' => $row[7]
                ]);
            }
            DB::commit();
            return redirect()->back()->with('success', 'Training data uploaded successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error uploading training data: ' . $e->getMessage());
        }
    }

    public function uploadTest(Request $request)
    {
        $request->validate([
            'test_file' => 'required|file|mimes:xlsx,xls,csv'
        ]);

        $file = $request->file('test_file');
        $spreadsheet = IOFactory::load($file->getRealPath());
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray();

        // Skip header
        array_shift($rows);

        DB::beginTransaction();
        try {
            KnnTest::truncate();
            foreach ($rows as $row) {
                KnnTest::create([
                    'id' => $row[0], // Assuming ID is in first column
                    'sex' => $row[1],
                    'marital_status' => $row[2],
                    'age' => $row[3],
                    'education' => $row[4],
                    'income' => $row[5],
                    'occupation' => $row[6],
                    'settlement_size' => $row[7] ?? null
                ]);
            }
            DB::commit();
            return redirect()->back()->with('success', 'Testing data uploaded successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error uploading testing data: ' . $e->getMessage());
        }
    }

    public function calculateKnn(Request $request)
    {
        $request->validate([
            'k' => 'required|integer|min:1'
        ]);

        $k = $request->k;
        $trainData = KnnTrain::all();
        $testData = KnnTest::all();

        foreach ($testData as $test) {
            $distances = [];

            foreach ($trainData as $train) {
                // Calculate distance (simple numerical difference for age and income)
                $distance = sqrt(
                    pow(($test->age - $train->age), 2) +
                        pow(($test->income - $train->income), 2)
                );

                $distances[] = [
                    'distance' => $distance,
                    'settlement' => $train->settlement_size
                ];
            }

            // Sort by distance
            usort($distances, function ($a, $b) {
                return $a['distance'] <=> $b['distance'];
            });

            // Get k nearest neighbors
            $nearest = array_slice($distances, 0, $k);

            // Count settlements
            $settlementCounts = [];
            foreach ($nearest as $n) {
                $settlement = $n['settlement'];
                if (!isset($settlementCounts[$settlement])) {
                    $settlementCounts[$settlement] = 0;
                }
                $settlementCounts[$settlement]++;
            }

            // Get the most frequent settlement
            arsort($settlementCounts);
            $predicted = array_key_first($settlementCounts);

            // Update test record
            $test->predicted_settlement = $predicted;
            $test->save();
        }

        // Calculate confusion matrix
        $confusionMatrix = $this->calculateConfusionMatrix();

        return redirect()->back()->with([
            'success' => 'KNN calculation completed!',
            'confusionMatrix' => $confusionMatrix
        ]);
    }

    private function calculateConfusionMatrix()
    {
        $testData = KnnTest::whereNotNull('settlement_size')->get();
        $actual = $testData->pluck('settlement_size')->toArray();
        $predicted = $testData->pluck('predicted_settlement')->toArray();

        $classes = array_unique(array_merge($actual, $predicted));
        sort($classes);

        $matrix = array_fill_keys($classes, array_fill_keys($classes, 0));

        foreach ($actual as $index => $trueClass) {
            $predictedClass = $predicted[$index];
            $matrix[$trueClass][$predictedClass]++;
        }

        return $matrix;
    }
}
