<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ScanController;
use App\Http\Controllers\Api\AIController;  // ← أضف ده

/*
|--------------------------------------------------------------------------
| API Routes - Simple User System
|--------------------------------------------------------------------------
*/

/*
|---------------------------------------------------------
| Public Routes (No Authentication Required)
|---------------------------------------------------------
*/
Route::middleware('throttle:10,1')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    
    // Email availability check (for live validation)
    Route::get('/check-email', [AuthController::class, 'checkEmail']);
});

/*
|---------------------------------------------------------
| Authenticated Routes (All Users)
|---------------------------------------------------------
*/
Route::middleware(['auth:api'])->group(function () {
    
    // User Profile
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
    
    // Scans Management
    Route::post('/scans', [ScanController::class, 'upload']);
    Route::post('/scans/{id}/analyze', [ScanController::class, 'analyze']);
    Route::get('/scans', [ScanController::class, 'history']);
    Route::get('/scans/{id}', [ScanController::class, 'show']);
    Route::delete('/scans/{id}', [ScanController::class, 'delete']);
    
    // User Statistics
    Route::get('/stats', [ScanController::class, 'myStats']);
    
    /*
    |---------------------------------------------------------
    | AI Integration Routes (Authenticated Users)
    |---------------------------------------------------------
    */
    Route::prefix('ai')->group(function () {
        // AI Analysis - للمستخدمين المسجلين فقط
        Route::post('/analyze', [AIController::class, 'analyze']);
        
        // Gemini Chat - medical advisor
        Route::post('/chat', [AIController::class, 'chat']);
    });
});

/*
|---------------------------------------------------------
| AI Public Routes (for testing/health checks)
|---------------------------------------------------------
*/
Route::prefix('ai')->group(function () {
    // Health check - anyone can check
    Route::get('/health', [AIController::class, 'health']);
    
    // Models status
    Route::get('/models/status', [AIController::class, 'modelsStatus']);
});