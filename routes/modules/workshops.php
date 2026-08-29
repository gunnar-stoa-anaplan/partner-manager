<?php

use App\Http\Controllers\WorkshopController;
use App\Http\Controllers\WorkshopParticipantController;
use App\Http\Controllers\WorkshopSessionController;
use Illuminate\Support\Facades\Route;

Route::get('/', [WorkshopController::class, 'index'])->name('index');
Route::get('/create', [WorkshopController::class, 'create'])->name('create');
Route::post('/', [WorkshopController::class, 'store'])->name('store');
Route::get('/{workshop}', [WorkshopController::class, 'show'])->name('show');
Route::get('/{workshop}/edit', [WorkshopController::class, 'edit'])->name('edit');
Route::put('/{workshop}', [WorkshopController::class, 'update'])->name('update');
Route::delete('/{workshop}', [WorkshopController::class, 'destroy'])->name('destroy');

Route::post('/{workshop}/sessions', [WorkshopSessionController::class, 'store'])->name('sessions.store');
Route::get('/{workshop}/sessions/{session}', [WorkshopSessionController::class, 'show'])->name('sessions.show');
Route::get('/{workshop}/sessions/{session}/edit', [WorkshopSessionController::class, 'edit'])->name('sessions.edit');
Route::put('/{workshop}/sessions/{session}', [WorkshopSessionController::class, 'update'])->name('sessions.update');
Route::delete('/{workshop}/sessions/{session}', [WorkshopSessionController::class, 'destroy'])->name('sessions.destroy');

Route::post('/{workshop}/sessions/{session}/participants', [WorkshopParticipantController::class, 'invite'])->name('sessions.participants.invite');
Route::delete('/{workshop}/sessions/{session}/participants/{participant}', [WorkshopParticipantController::class, 'remove'])->name('sessions.participants.remove');
Route::post('/{workshop}/sessions/{session}/monitors', [WorkshopParticipantController::class, 'assignMonitor'])->name('sessions.monitors.store');
Route::delete('/{workshop}/sessions/{session}/monitors/{monitorUser}', [WorkshopParticipantController::class, 'removeMonitor'])->name('sessions.monitors.destroy');
