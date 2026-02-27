<?php

use App\Http\Controllers\ZoomChat\MessageController;
use App\Http\Controllers\ZoomChat\ResponderController;
use Illuminate\Support\Facades\Route;

Route::resource('responders', ResponderController::class);
Route::post('responders/{responder}/toggle', [ResponderController::class, 'toggle'])->name('responders.toggle');

Route::get('messages', [MessageController::class, 'index'])->name('messages.index');
Route::get('messages/{message}', [MessageController::class, 'show'])->name('messages.show');
