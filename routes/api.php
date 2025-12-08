<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public routes
Route::prefix('v1')->group(function () {
    // Authentication
    Route::post('/auth/login', [AuthController::class, 'login'])->name('api.login');
    Route::post('/auth/register', [AuthController::class, 'register'])->name('api.register');
    
    // Password reset (to be implemented)
    // Route::post('/auth/password/forgot', [AuthController::class, 'forgotPassword']);
    // Route::post('/auth/password/reset', [AuthController::class, 'resetPassword']);
});

// Protected routes (require authentication)
Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    // Authentication
    Route::post('/auth/logout', [AuthController::class, 'logout'])->name('api.logout');
    Route::post('/auth/refresh', [AuthController::class, 'refresh'])->name('api.refresh');
    Route::get('/auth/me', [AuthController::class, 'me'])->name('api.me');
    
    // Employee endpoints
    Route::apiResource('employees', \App\Http\Controllers\Api\EmployeeController::class)
        ->parameters(['employees' => 'employeeId']);
    
    Route::prefix('employees/{employeeId}')->group(function () {
        Route::post('/position', [\App\Http\Controllers\Api\EmployeeController::class, 'changePosition'])
            ->name('api.employees.position');
        Route::post('/location', [\App\Http\Controllers\Api\EmployeeController::class, 'changeLocation'])
            ->name('api.employees.location');
        Route::post('/remote-work', [\App\Http\Controllers\Api\EmployeeController::class, 'configureRemoteWork'])
            ->name('api.employees.remote-work');
        Route::post('/terminate', [\App\Http\Controllers\Api\EmployeeController::class, 'terminate'])
            ->name('api.employees.terminate');
        Route::get('/history', [\App\Http\Controllers\Api\EmployeeController::class, 'history'])
            ->name('api.employees.history');
    });
    
    // Team endpoints
    Route::apiResource('teams', \App\Http\Controllers\Api\TeamController::class)
        ->parameters(['teams' => 'teamId']);
    
    Route::prefix('teams/{teamId}')->group(function () {
        Route::post('/members', [\App\Http\Controllers\Api\TeamController::class, 'assignMember'])
            ->name('api.teams.members.assign');
        Route::delete('/members/{employeeId}', [\App\Http\Controllers\Api\TeamController::class, 'removeMember'])
            ->name('api.teams.members.remove');
        Route::post('/transfer', [\App\Http\Controllers\Api\TeamController::class, 'transfer'])
            ->name('api.teams.transfer');
        Route::post('/lead', [\App\Http\Controllers\Api\TeamController::class, 'changeTeamLead'])
            ->name('api.teams.lead');
        Route::get('/members', [\App\Http\Controllers\Api\TeamController::class, 'members'])
            ->name('api.teams.members');
    });
    
    // Equipment endpoints
    Route::apiResource('equipment', \App\Http\Controllers\Api\EquipmentController::class);
    
    Route::prefix('equipment/{equipmentId}')->group(function () {
        Route::post('/issue', [\App\Http\Controllers\Api\EquipmentController::class, 'issue'])
            ->name('api.equipment.issue');
        Route::post('/return', [\App\Http\Controllers\Api\EquipmentController::class, 'return'])
            ->name('api.equipment.return');
        Route::post('/transfer', [\App\Http\Controllers\Api\EquipmentController::class, 'transfer'])
            ->name('api.equipment.transfer');
        Route::get('/history', [\App\Http\Controllers\Api\EquipmentController::class, 'history'])
            ->name('api.equipment.history');
    });
    
    // Leave endpoints (to be implemented in Phase 8)
    // Route::apiResource('leaves', LeaveController::class);
});

