<?php

use App\Http\Controllers\WorkshopSurveyController;
use Illuminate\Support\Facades\Route;

Route::get('/{workshop}', [WorkshopSurveyController::class, 'index'])->name('index');
Route::get('/{survey}/edit', [WorkshopSurveyController::class, 'edit'])->name('edit');
Route::put('/{survey}', [WorkshopSurveyController::class, 'update'])->name('update');
Route::delete('/{survey}', [WorkshopSurveyController::class, 'destroy'])->name('destroy');
Route::get('/{survey}/preview', [WorkshopSurveyController::class, 'preview'])->name('preview');
Route::get('/{survey}/history', [WorkshopSurveyController::class, 'history'])->name('history');
Route::get('/{survey}/diff', [WorkshopSurveyController::class, 'diff'])->name('diff');
Route::post('/{survey}/publish', [WorkshopSurveyController::class, 'publish'])->name('publish');
Route::post('/{survey}/reject', [WorkshopSurveyController::class, 'reject'])->name('reject');
Route::post('/{survey}/comments', [WorkshopSurveyController::class, 'comment'])->name('comments');
Route::post('/{survey}/import-questions', [WorkshopSurveyController::class, 'importQuestions'])->name('import-questions');
