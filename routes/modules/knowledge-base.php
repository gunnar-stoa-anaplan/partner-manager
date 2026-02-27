<?php

use App\Http\Controllers\KnowledgeBase\DashboardController;
use App\Http\Controllers\KnowledgeBase\DocumentController;
use App\Http\Controllers\KnowledgeBase\IngestionController;
use Illuminate\Support\Facades\Route;

Route::get('/', DashboardController::class)->name('dashboard');

Route::resource('documents', DocumentController::class)->only(['index', 'show', 'destroy']);
Route::post('documents/{document}/reingest', [DocumentController::class, 'reingest'])->name('documents.reingest');

Route::get('ingest', [IngestionController::class, 'create'])->name('ingest.create');
Route::post('ingest/upload', [IngestionController::class, 'upload'])->name('ingest.upload');
Route::post('ingest/directory', [IngestionController::class, 'directory'])->name('ingest.directory');
Route::post('ingest/storage-disk', [IngestionController::class, 'storageDisk'])->name('ingest.storage-disk');
