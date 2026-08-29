<?php

use App\Http\Controllers\WorkshopReportController;
use Illuminate\Support\Facades\Route;

Route::get('/{workshop}/sessions/{session}/leaderboard', [WorkshopReportController::class, 'sessionLeaderboard'])->name('leaderboard');
Route::get('/{workshop}/sessions/{session}/exec-summary', [WorkshopReportController::class, 'execSummary'])->name('exec-summary');
Route::get('/{workshop}/sessions/{session}/slack', [WorkshopReportController::class, 'slackPost'])->name('slack');
Route::get('/{workshop}/sessions/{session}/export.json', [WorkshopReportController::class, 'exportJson'])->name('export-json');
Route::get('/{workshop}/sessions/{session}/partner/{partner}', [WorkshopReportController::class, 'partnerReport'])->name('partner');
Route::get('/{workshop}/sessions/{session}/partner/{partner}/zip', [WorkshopReportController::class, 'partnerReportZip'])->name('partner-zip');
