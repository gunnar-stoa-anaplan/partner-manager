<?php

use App\Http\Controllers\WorkshopController;
use App\Http\Controllers\WorkshopPageController;
use Illuminate\Support\Facades\Route;

Route::get('/{workshop}/review', [WorkshopController::class, 'reviewQueue'])->name('review-queue');
Route::get('/{workshop}/content-flags', [WorkshopController::class, 'contentFlags'])->name('content-flags');
Route::get('/{workshop}/audit-log', [WorkshopController::class, 'auditLog'])->name('audit-log');

Route::get('/{workshop}/pages', [WorkshopPageController::class, 'index'])->name('pages.index');
Route::get('/{workshop}/pages/create', [WorkshopPageController::class, 'create'])->name('pages.create');
Route::post('/{workshop}/pages', [WorkshopPageController::class, 'store'])->name('pages.store');
Route::post('/{workshop}/pages/reorder', [WorkshopPageController::class, 'reorder'])->name('pages.reorder');
Route::get('/{workshop}/pages/{page}/edit', [WorkshopPageController::class, 'edit'])->name('pages.edit');
Route::get('/{workshop}/pages/{page}/history', [WorkshopPageController::class, 'history'])->name('pages.history');
Route::get('/{workshop}/pages/{page}/diff', [WorkshopPageController::class, 'diff'])->name('pages.diff');
Route::post('/{workshop}/pages/{page}/publish', [WorkshopPageController::class, 'publish'])->name('pages.publish');
Route::post('/{workshop}/pages/{page}/reject', [WorkshopPageController::class, 'reject'])->name('pages.reject');
Route::post('/{workshop}/pages/{page}/comments', [WorkshopPageController::class, 'comment'])->name('pages.comments');
Route::get('/{workshop}/pages/{page}', [WorkshopPageController::class, 'show'])->name('pages.show');
Route::put('/{workshop}/pages/{page}', [WorkshopPageController::class, 'update'])->name('pages.update');
Route::delete('/{workshop}/pages/{page}', [WorkshopPageController::class, 'destroy'])->name('pages.destroy');
