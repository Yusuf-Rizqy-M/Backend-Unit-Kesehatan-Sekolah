<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\HealthConditionController;
use App\Http\Controllers\API\ResetPasswordController;
use App\Http\Controllers\API\QueueController;
use App\Http\Controllers\API\QueueAdminController;
use App\Http\Controllers\API\DepartmentController;
use App\Http\Controllers\API\GradeController;

Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [ResetPasswordController::class, 'requestReset']);
Route::post('/reset-password', [ResetPasswordController::class, 'resetPassword']);
Route::post('/verify-otp', [ResetPasswordController::class, 'verifyOtp']);

Route::middleware(['auth:sanctum', 'check_token_expiry'])->group(function () {
    // Umum (user & admin)
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [UserController::class, 'show']);
    Route::put('/user/update', [UserController::class, 'update']);
    Route::get('/health-conditions-one', [HealthConditionController::class, 'show']);
    Route::post('/update-player-id', [UserController::class, 'updatePlayerId']);

    // Antrian User
    Route::post('/queues', [QueueController::class, 'store']); // Ambil nomor antrian
    Route::get('/queues/my', [QueueController::class, 'myQueue']); // Lihat antrian milik sendiri
    Route::get('/queues/current', [QueueController::class, 'currentQueue']); // Antrian aktif user
    Route::get('/queues/history', [QueueController::class, 'history']); // Riwayat antrian user
    Route::get('/queue/current-active', [QueueController::class, 'antrianSekarang']); // Antrian yang sedang diproses (global)

    // Admin Only
    Route::middleware('admin')->group(function () {
        // Auth & User Management
        Route::post('/register', [AuthController::class, 'register']);
        Route::get('/users/search', [UserController::class, 'search']);
        Route::get('/users/{id}', [UserController::class, 'showById']);
        Route::get('/users', [UserController::class, 'index']);

        // Data Kesehatan
        Route::post('/health-conditions', [HealthConditionController::class, 'store']);
        Route::put('/health-conditions/{id}', [HealthConditionController::class, 'update']);
        Route::delete('/health-conditions/{id}', [HealthConditionController::class, 'destroy']);
        Route::get('/health-conditions-all', [HealthConditionController::class, 'index']);

        // Antrian Admin
        Route::get('/admin/queues/today', [QueueAdminController::class, 'today']);
        Route::get('/admin/queues/current', [QueueAdminController::class, 'current']); // ✅ Tambahan: antrian aktif hari ini
        Route::get('/admin/queues/history', [QueueAdminController::class, 'history']);
        Route::get('/admin/queues/stats', [QueueAdminController::class, 'stats']);
        Route::put('/admin/queues/{id}/process', [QueueAdminController::class, 'process']);
        Route::put('/admin/queues/{id}/finish', [QueueAdminController::class, 'finish']);
        Route::put('/admin/queues/{id}/skip', [QueueAdminController::class, 'skip']);

        // Data Tambahan
        Route::get('/departments', [DepartmentController::class, 'index']);
        Route::get('/grades', [GradeController::class, 'index']);

        // Filter user berdasarkan departemen (contoh: RPL)
        Route::get('/admin/department/{department}', [UserController::class, 'filterByDepartment']);

        // Filter user berdasarkan grade lengkap (contoh: 10 RPL 1)
        Route::get('/admin/class/{class}/grades/{grades}', [UserController::class, 'filterByClassAndGrade']);

        // Filter user kelas 10 berdasarkan jurusan (contoh: 10 Animasi 3D)
        Route::get('/admin/class/{class}/department/{department}', [UserController::class, 'filterByClassAndDepartment']);

        // Semua user berdasarkan level kelas (kelas 10, 11, 12)
        Route::get('/admin/class/{class}', [UserController::class, 'filterByClass']);
    });
});
