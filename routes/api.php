<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\HealthConditionController;
use App\Http\Controllers\API\ResetPasswordController;

Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [ResetPasswordController::class, 'requestReset']);
Route::post('/reset-password', [ResetPasswordController::class, 'resetPassword']);
Route::post('/verify-otp', [ResetPasswordController::class, 'verifyOtp']);

Route::middleware(['auth:sanctum', 'check_token_expiry'])->group(function () {
    // Umum (user & admin)
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/profile', [UserController::class, 'show']);
    Route::put('/profile/update', [UserController::class, 'update']);
    Route::get('/health-conditions-one', [HealthConditionController::class, 'show']);
    // Admin Only
    Route::middleware('admin')->group(function () {
        Route::post('/register', [AuthController::class, 'register']);
        Route::get('/users/search', [UserController::class, 'search']);
        Route::get('/users/{id}', [UserController::class, 'showById']);
        Route::get('/users', [UserController::class, 'index']);

        Route::post('/health-conditions', [HealthConditionController::class, 'store']);
        Route::put('/health-conditions/{id}', [HealthConditionController::class, 'update']);
        Route::delete('/health-conditions/{id}', [HealthConditionController::class, 'destroy']);
   
    });

    // User only (atau public)
    Route::get('/health-conditions-all', [HealthConditionController::class, 'index']);
 
});
