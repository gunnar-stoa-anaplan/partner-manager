<?php

use App\Http\Controllers\IdeaRoom\Admin\DetectFramesController;
use App\Http\Controllers\IdeaRoom\Admin\SettingsController;
use App\Http\Controllers\IdeaRoom\Admin\ThemeController;
use App\Http\Controllers\IdeaRoom\ChatController;
use App\Http\Controllers\IdeaRoom\ConstellationController;
use App\Http\Controllers\IdeaRoom\DashboardController;
use App\Http\Controllers\IdeaRoom\GalleryAssetController;
use App\Http\Controllers\IdeaRoom\IdeaListController;
use App\Http\Controllers\IdeaRoom\IdeaRoomListController;
use App\Http\Controllers\IdeaRoom\RoomController;
use App\Http\Controllers\IdeaRoom\WhiteboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', DashboardController::class)->name('dashboard');
Route::get('/rooms-list', IdeaRoomListController::class)->name('rooms');
Route::get('/ideas-list', IdeaListController::class)->name('ideas');
Route::get('/chat', ChatController::class)->name('chat');
Route::get('/constellation', ConstellationController::class)->name('constellation');
Route::get('/whiteboard', WhiteboardController::class)->name('whiteboard');

// Gallery asset routes
Route::get('/gallery/wall', [GalleryAssetController::class, 'wall'])->name('gallery.wall');
Route::get('/gallery/template', [GalleryAssetController::class, 'template'])->name('gallery.template');

// Idea room entry and view
Route::get('/ideas/{idea}/enter', [RoomController::class, 'enter'])->name('idea.enter');
Route::get('/rooms/{room}', [RoomController::class, 'show'])->name('room');

/*
|--------------------------------------------------------------------------
| Admin-Only Idea Room Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('settings', [SettingsController::class, 'index'])->name('settings');
    Route::put('settings', [SettingsController::class, 'update'])->name('settings.update');

    Route::post('themes/detect-frames', DetectFramesController::class)->name('themes.detect-frames');
    Route::resource('themes', ThemeController::class)->except(['show']);
    Route::post('themes/{theme}/toggle-publish', [ThemeController::class, 'togglePublish'])->name('themes.toggle-publish');
    Route::post('themes/{theme}/set-default', [ThemeController::class, 'setDefault'])->name('themes.set-default');
});
