<?php

use App\Http\Controllers\WorkshopSurveyController;
use Illuminate\Support\Facades\Route;

Route::get('/{survey}/results', [WorkshopSurveyController::class, 'results'])->name('results');
Route::post('/{survey}/upload', [WorkshopSurveyController::class, 'upload'])->name('upload');
Route::delete('/{survey}/batch', [WorkshopSurveyController::class, 'destroyBatch'])->name('destroy-batch');
