<?php

use App\Http\Controllers\ActionPlanController;
use App\Http\Controllers\CompetencyMatrixController;
use App\Http\Controllers\GoalsController;
use App\Http\Controllers\GoalTemplateController;
use App\Http\Controllers\NotificationTemplateController;
use App\Http\Controllers\PartnerGoalController;
use App\Http\Controllers\PartnerPathController;
use App\Http\Controllers\PathConfigurationController;
use App\Http\Controllers\ResourcePathConfigurationController;
use App\Http\Controllers\ResourcePathController;
use App\Http\Controllers\ScorecardController;
use App\Http\Controllers\TaskBoardController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Competency Module Routes
|--------------------------------------------------------------------------
|
| Feature slug: competency
| Gated by module:competency middleware via bootstrap/app.php
|
*/

// Plan Section — Strategic Planning & Readiness
Route::prefix('plan')->name('plan.')->group(function () {
    // Partner Paths
    Route::get('/paths', [PartnerPathController::class, 'index'])->name('paths.index');
    Route::post('/paths/enroll', [PartnerPathController::class, 'enroll'])->name('paths.enroll');
    Route::get('/paths/{enrollment}', [PartnerPathController::class, 'show'])->name('paths.show');
    Route::post('/paths/{enrollment}/advance', [PartnerPathController::class, 'advanceStage'])->name('paths.advance');

    // Resource Paths
    Route::get('/resource-paths', [ResourcePathController::class, 'index'])->name('resource-paths.index');
    Route::post('/resource-paths/enroll', [ResourcePathController::class, 'enroll'])->name('resource-paths.enroll');
    Route::get('/resource-paths/{enrollment}', [ResourcePathController::class, 'show'])->name('resource-paths.show');
    Route::post('/resource-paths/{enrollment}/advance', [ResourcePathController::class, 'advanceStage'])->name('resource-paths.advance');

    // Scorecard
    Route::get('/scorecard', [ScorecardController::class, 'index'])->name('scorecard.index');
    Route::get('/scorecard/{partner}', [ScorecardController::class, 'show'])->name('scorecard.show');

    // Competency Matrix
    Route::get('/competency-matrix', [CompetencyMatrixController::class, 'index'])->name('competency-matrix.index');
    Route::get('/competency-matrix/{partner}', [CompetencyMatrixController::class, 'show'])->name('competency-matrix.show');

    // Task Board
    Route::get('/tasks', [TaskBoardController::class, 'index'])->name('tasks.index');
    Route::post('/tasks', [TaskBoardController::class, 'store'])->name('tasks.store');
    Route::put('/tasks/{task}/status', [TaskBoardController::class, 'updateStatus'])->name('tasks.update-status');
    Route::post('/tasks/{task}/comments', [TaskBoardController::class, 'addComment'])->name('tasks.comments.store');

    // Goals & Targets
    Route::get('/goals', [GoalsController::class, 'index'])->name('goals.index');
    Route::post('/goals', [GoalsController::class, 'store'])->name('goals.store');
    Route::put('/goals/{goal}', [GoalsController::class, 'update'])->name('goals.update');
    Route::delete('/goals/{goal}', [GoalsController::class, 'destroy'])->name('goals.destroy');
    Route::post('/goals/refresh-actuals', [GoalsController::class, 'refreshActuals'])->name('goals.refresh-actuals');

    // Action Plans
    Route::get('/action-plans', [ActionPlanController::class, 'index'])->name('action-plans.index');
    Route::post('/action-plans', [ActionPlanController::class, 'store'])->name('action-plans.store');
    Route::put('/action-plans/{plan}', [ActionPlanController::class, 'update'])->name('action-plans.update');
    Route::post('/action-plans/{plan}/items', [ActionPlanController::class, 'addItem'])->name('action-plans.items.store');
    Route::put('/action-plan-items/{item}', [ActionPlanController::class, 'updateItem'])->name('action-plan-items.update');
});

// Partner Goals (PSM + Admin)
Route::get('/partner-goals', [PartnerGoalController::class, 'index'])->name('partner-goals.index');
Route::put('/partner-goals', [PartnerGoalController::class, 'update'])->name('partner-goals.update');

/*
|--------------------------------------------------------------------------
| Admin-Only Competency Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['admin'])->prefix('admin')->name('admin.')->group(function () {
    // Path Configuration
    Route::get('/paths', [PathConfigurationController::class, 'index'])->name('paths.index');
    Route::post('/paths', [PathConfigurationController::class, 'store'])->name('paths.store');
    Route::get('/paths/{path}', [PathConfigurationController::class, 'show'])->name('paths.show');
    Route::put('/paths/{path}', [PathConfigurationController::class, 'update'])->name('paths.update');
    Route::post('/paths/{path}/stages', [PathConfigurationController::class, 'addStage'])->name('paths.stages.store');
    Route::put('/path-stages/{stage}', [PathConfigurationController::class, 'updateStage'])->name('path-stages.update');
    Route::delete('/path-stages/{stage}', [PathConfigurationController::class, 'destroyStage'])->name('path-stages.destroy');
    Route::post('/paths/{path}/gates', [PathConfigurationController::class, 'addGate'])->name('paths.gates.store');
    Route::put('/path-gates/{gate}', [PathConfigurationController::class, 'updateGate'])->name('path-gates.update');
    Route::delete('/path-gates/{gate}', [PathConfigurationController::class, 'destroyGate'])->name('path-gates.destroy');

    // Resource Path Configuration
    Route::get('/resource-paths', [ResourcePathConfigurationController::class, 'index'])->name('resource-paths.index');
    Route::post('/resource-paths', [ResourcePathConfigurationController::class, 'store'])->name('resource-paths.store');
    Route::get('/resource-paths/{path}', [ResourcePathConfigurationController::class, 'show'])->name('resource-paths.show');
    Route::put('/resource-paths/{path}', [ResourcePathConfigurationController::class, 'update'])->name('resource-paths.update');
    Route::post('/resource-paths/{path}/stages', [ResourcePathConfigurationController::class, 'addStage'])->name('resource-paths.stages.store');
    Route::put('/resource-path-stages/{stage}', [ResourcePathConfigurationController::class, 'updateStage'])->name('resource-path-stages.update');
    Route::delete('/resource-path-stages/{stage}', [ResourcePathConfigurationController::class, 'destroyStage'])->name('resource-path-stages.destroy');
    Route::post('/resource-paths/{path}/gates', [ResourcePathConfigurationController::class, 'addGate'])->name('resource-paths.gates.store');
    Route::put('/resource-path-gates/{gate}', [ResourcePathConfigurationController::class, 'updateGate'])->name('resource-path-gates.update');
    Route::delete('/resource-path-gates/{gate}', [ResourcePathConfigurationController::class, 'destroyGate'])->name('resource-path-gates.destroy');

    // Resource Task Template Management
    Route::get('/resource-task-templates', [ResourcePathConfigurationController::class, 'taskTemplates'])->name('resource-task-templates.index');
    Route::post('/resource-task-templates', [ResourcePathConfigurationController::class, 'storeTaskTemplate'])->name('resource-task-templates.store');
    Route::put('/resource-task-templates/{template}', [ResourcePathConfigurationController::class, 'updateTaskTemplate'])->name('resource-task-templates.update');
    Route::delete('/resource-task-templates/{template}', [ResourcePathConfigurationController::class, 'destroyTaskTemplate'])->name('resource-task-templates.destroy');

    // Task Template Management
    Route::get('/task-templates', [PathConfigurationController::class, 'taskTemplates'])->name('task-templates.index');
    Route::post('/task-templates', [PathConfigurationController::class, 'storeTaskTemplate'])->name('task-templates.store');
    Route::put('/task-templates/{template}', [PathConfigurationController::class, 'updateTaskTemplate'])->name('task-templates.update');
    Route::delete('/task-templates/{template}', [PathConfigurationController::class, 'destroyTaskTemplate'])->name('task-templates.destroy');

    // Scorecard Weight Configuration
    Route::get('/scorecard-weights', [ScorecardController::class, 'weights'])->name('scorecard-weights.index');
    Route::put('/scorecard-weights', [ScorecardController::class, 'updateWeights'])->name('scorecard-weights.update');

    // Goal Templates
    Route::get('/goal-templates', [GoalTemplateController::class, 'index'])->name('goal-templates.index');
    Route::post('/goal-templates', [GoalTemplateController::class, 'store'])->name('goal-templates.store');
    Route::put('/goal-templates/{template}', [GoalTemplateController::class, 'update'])->name('goal-templates.update');
    Route::delete('/goal-templates/{template}', [GoalTemplateController::class, 'destroy'])->name('goal-templates.destroy');
    Route::post('/goal-templates/{template}/assign', [GoalTemplateController::class, 'assign'])->name('goal-templates.assign');

    // Notification Templates
    Route::get('/notification-templates', [NotificationTemplateController::class, 'index'])->name('notification-templates.index');
    Route::post('/notification-templates', [NotificationTemplateController::class, 'store'])->name('notification-templates.store');
    Route::put('/notification-templates/{template}', [NotificationTemplateController::class, 'update'])->name('notification-templates.update');
    Route::delete('/notification-templates/{template}', [NotificationTemplateController::class, 'destroy'])->name('notification-templates.destroy');
    Route::post('/notification-templates/{template}/test', [NotificationTemplateController::class, 'preview'])->name('notification-templates.test');
});
