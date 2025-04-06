<div class="container">
    <h1 class="mb-4">KNN Clustering</h1>

    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">Upload Training Data</div>
                <div class="card-body">
                    <form action="{{ route('knn.upload.train') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="form-group">
                            <label for="train_file">Excel File</label>
                            <input type="file" class="form-control" id="train_file" name="train_file" required>
                            <small class="form-text text-muted">
                                Format: sex, marital_status, age, education, income, occupation, settlement_size
                            </small>
                        </div>
                        <button type="submit" class="btn btn-primary mt-2">Upload</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">Upload Testing Data</div>
                <div class="card-body">
                    <form action="{{ route('knn.upload.test') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="form-group">
                            <label for="test_file">Excel File</label>
                            <input type="file" class="form-control" id="test_file" name="test_file" required>
                            <small class="form-text text-muted">
                                Format: sex, marital_status, age, education, income, occupation, settlement_size
                                (optional)
                            </small>
                        </div>
                        <button type="submit" class="btn btn-primary mt-2">Upload</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">Run KNN</div>
        <div class="card-body">
            <form action="{{ route('knn.calculate') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label for="k">K Value</label>
                    <input type="number" class="form-control" id="k" name="k" min="1"
                        value="3" required>
                </div>
                <button type="submit" class="btn btn-success mt-2">Calculate KNN</button>
            </form>
        </div>
    </div>

    @if (session('confusionMatrix'))
        <div class="card mb-4">
            <div class="card-header">Confusion Matrix</div>
            <div class="card-body">
                @php
                    $confusionMatrix = session('confusionMatrix');
                    $totalCorrect = 0;
                    $totalPredictions = 0;
                @endphp

                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Actual \ Predicted</th>
                            @foreach (array_keys($confusionMatrix) as $class)
                                <th>{{ $class }}</th>
                            @endforeach
                            <th>Total</th>
                            <th>Correct</th>
                            <th>Accuracy</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($confusionMatrix as $trueClass => $predictions)
                            @php
                                $rowTotal = array_sum($predictions);
                                $correct = $predictions[$trueClass] ?? 0;
                                $accuracy = $rowTotal > 0 ? round(($correct / $rowTotal) * 100, 2) : 0;
                                $totalCorrect += $correct;
                                $totalPredictions += $rowTotal;
                            @endphp
                            <tr>
                                <td><strong>{{ $trueClass }}</strong></td>
                                @foreach ($predictions as $predictedClass => $count)
                                    <td class="{{ $trueClass == $predictedClass ? 'bg-success text-white' : '' }}">
                                        {{ $count }}
                                    </td>
                                @endforeach
                                <td>{{ $rowTotal }}</td>
                                <td>{{ $correct }}</td>
                                <td>{{ $accuracy }}%</td>
                            </tr>
                        @endforeach

                        @php
                            $overallAccuracy =
                                $totalPredictions > 0 ? round(($totalCorrect / $totalPredictions) * 100, 2) : 0;
                        @endphp
                        <tr class="table-primary">
                            <td><strong>Total</strong></td>
                            @foreach (array_keys($confusionMatrix) as $class)
                                <td>{{ array_sum(array_column($confusionMatrix, $class)) }}</td>
                            @endforeach
                            <td>{{ $totalPredictions }}</td>
                            <td>{{ $totalCorrect }}</td>
                            <td>{{ $overallAccuracy }}%</td>
                        </tr>
                    </tbody>
                </table>

                <div class="mt-3">
                    <h5>Performance Metrics:</h5>
                    <ul>
                        <li><strong>Overall Accuracy:</strong> {{ $overallAccuracy }}%</li>
                        <li><strong>Total Predictions:</strong> {{ $totalPredictions }}</li>
                        <li><strong>Correct Predictions:</strong> {{ $totalCorrect }}</li>
                        <li><strong>Incorrect Predictions:</strong> {{ $totalPredictions - $totalCorrect }}</li>
                    </ul>
                </div>
            </div>
        </div>
    @endif

    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">Training Data ({{ $trainData->count() }} records)</div>
                <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Sex</th>
                                <th>Marital Status</th>
                                <th>Age</th>
                                <th>Education</th>
                                <th>Income</th>
                                <th>Occupation</th>
                                <th>Settlement</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($trainData as $data)
                                <tr>
                                    <td>{{ $data->sex }}</td>
                                    <td>{{ $data->marital_status }}</td>
                                    <td>{{ $data->age }}</td>
                                    <td>{{ $data->education }}</td>
                                    <td>{{ $data->income }}</td>
                                    <td>{{ $data->occupation }}</td>
                                    <td>{{ $data->settlement_size }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">Testing Data ({{ $testData->count() }} records)</div>
                <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Sex</th>
                                <th>Marital Status</th>
                                <th>Age</th>
                                <th>Education</th>
                                <th>Income</th>
                                <th>Occupation</th>
                                <th>Actual Settlement</th>
                                <th>Predicted</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($testData as $data)
                                <tr>
                                    <td>{{ $data->sex }}</td>
                                    <td>{{ $data->marital_status }}</td>
                                    <td>{{ $data->age }}</td>
                                    <td>{{ $data->education }}</td>
                                    <td>{{ $data->income }}</td>
                                    <td>{{ $data->occupation }}</td>
                                    <td>{{ $data->settlement_size ?? 'N/A' }}</td>
                                    <td>{{ $data->predicted_settlement ?? 'Not calculated' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .table-bordered th,
    .table-bordered td {
        text-align: center;
    }

    .bg-success {
        background-color: #28a745 !important;
    }

    .table-primary {
        background-color: #c6e0f5;
    }
</style>
