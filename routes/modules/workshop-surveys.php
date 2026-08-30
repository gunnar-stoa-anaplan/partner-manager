<?php

use App\Http\Controllers\WorkshopSurveyController;
use Illuminate\Support\Facades\Route;

Route::get('/{workshop}', [WorkshopSurveyController::class, 'index'])->name('index');
Route::get('/{survey}/edit', [WorkshopSurveyController::class, 'edit'])->name('edit');
Route::put('/{survey}', [WorkshopSurveyController::class, 'update'])->name('update');
Route::delete('/{survey}', [WorkshopSurveyController::class, 'destroy'])->name('destroy');
Route::get('/{survey}/preview', [WorkshopSurveyController::class, 'preview'])->name('preview');
Route::post('/{survey}/import-questions', [WorkshopSurveyController::class, 'importQuestions'])->name('import-questions');
Route::get('/{survey}/results', [WorkshopSurveyController::class, 'results'])->name('results');
Route::post('/{survey}/upload', [WorkshopSurveyController::class, 'upload'])->name('upload');
Route::delete('/{survey}/batch', [WorkshopSurveyController::class, 'destroyBatch'])->name('destroy-batch');
