<?php

use App\Http\Controllers\WorkshopSurveyController;
use Illuminate\Support\Facades\Route;

Route::get('/', [WorkshopSurveyController::class, 'index'])->name('index');
Route::get('/create', [WorkshopSurveyController::class, 'create'])->name('create');
Route::post('/', [WorkshopSurveyController::class, 'store'])->name('store');
Route::get('/{survey}/edit', [WorkshopSurveyController::class, 'edit'])->name('edit');
Route::put('/{survey}', [WorkshopSurveyController::class, 'update'])->name('update');
Route::get('/{survey}/results', [WorkshopSurveyController::class, 'results'])->name('results');
