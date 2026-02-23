<?php

use App\Http\Controllers\IntegrationHub\ConnectionController;
use App\Http\Controllers\IntegrationHub\ConnectorController;
use App\Http\Controllers\IntegrationHub\DashboardController;
use App\Http\Controllers\IntegrationHub\FieldMappingController;
use App\Http\Controllers\IntegrationHub\IntegrationJobController;
use App\Http\Controllers\IntegrationHub\MappingTemplateController;
use Illuminate\Support\Facades\Route;

Route::get('/', DashboardController::class)->name('dashboard');

Route::resource('connections', ConnectionController::class);
Route::post('connections/{connection}/test', [ConnectionController::class, 'test'])->name('connections.test');

Route::resource('mappings', FieldMappingController::class);
Route::post('mappings/{mapping}/preview', [FieldMappingController::class, 'preview'])->name('mappings.preview');

Route::get('connectors', [ConnectorController::class, 'catalog'])->name('connectors.catalog');
Route::get('connectors/{profile}/setup', [ConnectorController::class, 'setup'])->name('connectors.setup');
Route::post('connectors/{profile}/store', [ConnectorController::class, 'store'])->name('connectors.store');

Route::get('templates', [MappingTemplateController::class, 'index'])->name('templates.index');
Route::get('templates/{template}/review', [MappingTemplateController::class, 'review'])->name('templates.review');
Route::post('templates/{template}/apply', [MappingTemplateController::class, 'apply'])->name('templates.apply');

Route::get('jobs', [IntegrationJobController::class, 'index'])->name('jobs.index');
Route::get('jobs/{job}', [IntegrationJobController::class, 'show'])->name('jobs.show');
Route::post('jobs', [IntegrationJobController::class, 'store'])->name('jobs.store');
Route::post('jobs/{job}/retry', [IntegrationJobController::class, 'retry'])->name('jobs.retry');
Route::delete('jobs/{job}', [IntegrationJobController::class, 'destroy'])->name('jobs.destroy');
