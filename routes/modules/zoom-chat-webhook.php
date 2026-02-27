<?php

use App\Http\Controllers\Zoom\WebhookController;
use Illuminate\Support\Facades\Route;

Route::post('zoom/chat', [WebhookController::class, 'handle'])->name('zoom.chat');
