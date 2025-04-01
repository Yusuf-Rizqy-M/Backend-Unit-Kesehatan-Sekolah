<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\UserController;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/register', [AuthController::class, 'register']);
    Route::get('/profile', [UserController::class, 'show']);
    Route::put('/profile/update', [UserController::class, 'update']);
});

// Route::middleware('auth:sanctum')->group(function () {
//     Route::get('/profile', [UserController::class, 'show']);
//     Route::put('/profile/update', [UserController::class, 'update']);
// });

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });
