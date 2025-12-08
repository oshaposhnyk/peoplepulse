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
    
    // Employee endpoints (to be implemented in Phase 5)
    // Route::apiResource('employees', EmployeeController::class);
    
    // Team endpoints (to be implemented in Phase 6)
    // Route::apiResource('teams', TeamController::class);
    
    // Equipment endpoints (to be implemented in Phase 7)
    // Route::apiResource('equipment', EquipmentController::class);
    
    // Leave endpoints (to be implemented in Phase 8)
    // Route::apiResource('leaves', LeaveController::class);
});

