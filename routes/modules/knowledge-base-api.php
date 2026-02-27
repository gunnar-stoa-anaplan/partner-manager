<?php

use App\Http\Controllers\Api\KnowledgeSearchController;
use Illuminate\Support\Facades\Route;

Route::post('search', KnowledgeSearchController::class)->name('search');
