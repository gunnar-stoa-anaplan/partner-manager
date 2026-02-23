<?php

use App\Http\Controllers\IdeaRoom\Api\AgentMemoryController;
use App\Http\Controllers\IdeaRoom\Api\ArtifactController;
use App\Http\Controllers\IdeaRoom\Api\ChatController;
use App\Http\Controllers\IdeaRoom\Api\DashboardController;
use App\Http\Controllers\IdeaRoom\Api\GalleryController;
use App\Http\Controllers\IdeaRoom\Api\IdeaController;
use App\Http\Controllers\IdeaRoom\Api\IdeaTriageController;
use App\Http\Controllers\IdeaRoom\Api\RoomController;
use App\Http\Controllers\IdeaRoom\Api\RoomMessageController;
use Illuminate\Support\Facades\Route;

// Dashboard
Route::get('/dashboard/stats', [DashboardController::class, 'stats']);

// Chat (Global Orchestrator)
Route::post('/chat', [ChatController::class, 'store']);
Route::get('/chat/history', [ChatController::class, 'index']);

// Ideas
Route::get('/ideas/constellation', [IdeaController::class, 'constellation']);
Route::get('/ideas', [IdeaController::class, 'index']);
Route::get('/ideas/{idea}', [IdeaController::class, 'show']);
Route::post('/ideas/{idea}/route-to-room', [IdeaTriageController::class, 'routeToRoom']);

// Rooms
Route::post('/ideas/{idea}/rooms', [RoomController::class, 'store']);
Route::get('/rooms', [RoomController::class, 'index']);
Route::get('/rooms/{room}', [RoomController::class, 'show']);
Route::patch('/rooms/{room}', [RoomController::class, 'update']);

// Room Messages
Route::get('/rooms/{room}/messages', [RoomMessageController::class, 'index']);
Route::post('/rooms/{room}/messages', [RoomMessageController::class, 'store']);

// Agent Memories
Route::get('/rooms/{room}/memories', [AgentMemoryController::class, 'index']);
Route::delete('/rooms/{room}/memories/{memory}', [AgentMemoryController::class, 'destroy']);

// Artifacts
Route::post('/ideas/{idea}/artifacts', [ArtifactController::class, 'storeForIdea']);
Route::post('/rooms/{room}/artifacts', [ArtifactController::class, 'storeForRoom']);
Route::get('/artifacts/{artifact}', [ArtifactController::class, 'show']);
Route::get('/artifacts/{artifact}/download', [ArtifactController::class, 'download']);
Route::patch('/artifacts/{artifact}', [ArtifactController::class, 'update']);
Route::delete('/artifacts/{artifact}', [ArtifactController::class, 'destroy']);

// Gallery (requires Phase 5 — gallery compositor)
Route::get('/gallery/templates', [GalleryController::class, 'templates']);
Route::get('/gallery/config', [GalleryController::class, 'config']);
Route::post('/gallery/template', [GalleryController::class, 'setTemplate']);
