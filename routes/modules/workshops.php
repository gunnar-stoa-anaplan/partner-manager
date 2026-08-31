<?php

use App\Http\Controllers\WorkshopController;
use App\Http\Controllers\WorkshopParticipantController;
use App\Http\Controllers\WorkshopSessionController;
use App\Http\Controllers\WorkshopThreadController;
use App\Http\Controllers\WorkshopToolkitAssetController;
use App\Http\Controllers\WorkshopUploadController;
use Illuminate\Support\Facades\Route;

Route::get('/dashboard', [WorkshopController::class, 'dashboard'])->name('dashboard');
Route::get('/calendar', [WorkshopController::class, 'calendar'])->name('calendar');
Route::get('/calendar/events', [WorkshopController::class, 'calendarEvents'])->name('calendar.events');
Route::get('/', [WorkshopController::class, 'index'])->name('index');
Route::get('/create', [WorkshopController::class, 'create'])->name('create');
Route::post('/', [WorkshopController::class, 'store'])->name('store');
Route::get('/{workshop}', [WorkshopController::class, 'show'])->name('show');
Route::get('/{workshop}/rollup', [WorkshopController::class, 'rollup'])->name('rollup');
Route::get('/{workshop}/edit', [WorkshopController::class, 'edit'])->name('edit');
Route::put('/{workshop}', [WorkshopController::class, 'update'])->name('update');
Route::delete('/{workshop}', [WorkshopController::class, 'destroy'])->name('destroy');
Route::post('/{workshop}/archive', [WorkshopController::class, 'archive'])->name('archive');

Route::post('/{workshop}/sessions', [WorkshopSessionController::class, 'store'])->name('sessions.store');
Route::get('/{workshop}/sessions/{session}', [WorkshopSessionController::class, 'show'])->name('sessions.show');
Route::get('/{workshop}/sessions/{session}/edit', [WorkshopSessionController::class, 'edit'])->name('sessions.edit');
Route::put('/{workshop}/sessions/{session}', [WorkshopSessionController::class, 'update'])->name('sessions.update');
Route::delete('/{workshop}/sessions/{session}', [WorkshopSessionController::class, 'destroy'])->name('sessions.destroy');
Route::post('/{workshop}/sessions/{session}/archive', [WorkshopSessionController::class, 'archive'])->name('sessions.archive');

Route::get('/{workshop}/sessions/{session}/participants', [WorkshopParticipantController::class, 'index'])->name('sessions.participants.index');
Route::post('/{workshop}/sessions/{session}/participants', [WorkshopParticipantController::class, 'invite'])->name('sessions.participants.invite');
Route::delete('/{workshop}/sessions/{session}/participants/{participant}', [WorkshopParticipantController::class, 'remove'])->name('sessions.participants.remove');
Route::post('/{workshop}/sessions/{session}/participants/{participant}/complete', [WorkshopParticipantController::class, 'complete'])->name('sessions.participants.complete');
Route::post('/{workshop}/sessions/{session}/participants/{participant}/uncomplete', [WorkshopParticipantController::class, 'uncomplete'])->name('sessions.participants.uncomplete');
Route::post('/{workshop}/sessions/{session}/participants/contacts/{contact}/reset-password', [WorkshopParticipantController::class, 'resetPassword'])->name('sessions.participants.reset-password');
Route::post('/{workshop}/sessions/{session}/monitors', [WorkshopParticipantController::class, 'assignMonitor'])->name('sessions.monitors.store');
Route::delete('/{workshop}/sessions/{session}/monitors/{monitorUser}', [WorkshopParticipantController::class, 'removeMonitor'])->name('sessions.monitors.destroy');

Route::get('/{workshop}/sessions/{session}/feed', [WorkshopSessionController::class, 'feed'])->name('sessions.feed');

Route::post('/{workshop}/sessions/{session}/threads', [WorkshopThreadController::class, 'store'])->name('sessions.threads.store');
Route::post('/{workshop}/sessions/{session}/threads/{thread}/reply', [WorkshopThreadController::class, 'reply'])->name('sessions.threads.reply');
Route::post('/{workshop}/sessions/{session}/threads/{thread}/answer', [WorkshopThreadController::class, 'markAnswered'])->name('sessions.threads.answer');
Route::post('/{workshop}/sessions/{session}/threads/{thread}/hide', [WorkshopThreadController::class, 'hide'])->name('sessions.threads.hide');
Route::post('/{workshop}/sessions/{session}/threads/{thread}/react', [WorkshopThreadController::class, 'react'])->name('sessions.threads.react');
Route::get('/{workshop}/sessions/{session}/threads/{thread}/suggest-reply', [WorkshopThreadController::class, 'suggestReply'])->name('sessions.threads.suggest-reply');

Route::get('/uploads/{upload}', [WorkshopUploadController::class, 'download'])->name('uploads.download');

// Content pages (WorkshopPageController) and the review queue / content
// flags queue live in routes/modules/workshop-content.php instead, gated
// by module:workshops.content - editing workshop content is a content-
// administration action, kept separate from session/participant/monitor
// operations which is what this file is gated for.

Route::get('/{workshop}/toolkit', [WorkshopToolkitAssetController::class, 'index'])->name('toolkit.index');
Route::post('/{workshop}/toolkit', [WorkshopToolkitAssetController::class, 'store'])->name('toolkit.store');
Route::get('/{workshop}/toolkit/{asset}/download', [WorkshopToolkitAssetController::class, 'download'])->name('toolkit.download');
Route::delete('/{workshop}/toolkit/{asset}', [WorkshopToolkitAssetController::class, 'destroy'])->name('toolkit.destroy');
