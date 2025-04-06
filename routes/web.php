<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\KnnController;

// Route::get('/', function () {
//     return view('welcome');
// });

Route::prefix('knn')->group(function () {
    Route::get('/', [KnnController::class, 'index'])->name('knn.index');
    Route::post('/upload-train', [KnnController::class, 'uploadTrain'])->name('knn.upload.train');
    Route::post('/upload-test', [KnnController::class, 'uploadTest'])->name('knn.upload.test');
    Route::post('/calculate', [KnnController::class, 'calculateKnn'])->name('knn.calculate');
});