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
use App\Http\Controllers\API\BlogController;

Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [ResetPasswordController::class, 'requestReset']);
Route::post('/reset-password', [ResetPasswordController::class, 'resetPassword']);
Route::post('/verify-otp', [ResetPasswordController::class, 'verifyOtp']);
Route::get('/categories/{categoryId}/article/{articleId}/summary', [BlogController::class, 'getCategoryArticleSummary']);
Route::get('/categories/{categoryId}/article/{articleId}', [BlogController::class, 'getCategoryArticleDetail']);
Route::get('categories/{id}', [BlogController::class, 'getCategory']);
Route::get('categories', [BlogController::class, 'getCategories']);
Route::get('/categories/{id}/articles', [BlogController::class, 'getArticlesByCategory']);
Route::middleware(['auth:sanctum', 'check_token_expiry'])->group(function () {
    // Umum (user & admin)
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [UserController::class, 'show']);
    Route::put('/user/update', [UserController::class, 'update']);
    Route::post('/update-player-id', [UserController::class, 'updatePlayerId']);
    Route::get('/health-conditions-one', [HealthConditionController::class, 'show']);

    // Antrian User
    Route::post('/queues', [QueueController::class, 'store']);
    Route::get('/queues/my', [QueueController::class, 'myQueue']);
    Route::get('/queues/current', [QueueController::class, 'currentQueue']);
    Route::get('/queues/history', [QueueController::class, 'history']);
    Route::get('/queue/current-active', [QueueController::class, 'antrianSekarang']);
    Route::post('/queues/cancel', [QueueController::class, 'cancelQueue']);

    // Admin Only
    Route::middleware('admin')->group(function () {
        // Auth & User Management
        Route::post('/register', [AuthController::class, 'register']);
        Route::get('/users/{id}', [UserController::class, 'showById']);
        Route::get('/users', [UserController::class, 'index']);
        Route::put('users/{id}/update-class-department', [AuthController::class, 'updateClassAndDepartment']);
        Route::delete('/users/{id}', [UserController::class, 'destroy']);

        // Data Kdesehatan
        Route::post('/health-conditions', [HealthConditionController::class, 'store']);
        Route::get('/health-conditions-all', [HealthConditionController::class, 'index']);
        Route::put('/health-conditions/{userId}/{idUserCondition}', [HealthConditionController::class, 'update']);
        Route::delete('/health-conditions/{userId}/{idUserCondition}', [HealthConditionController::class, 'destroy']);
        Route::get('/healthcondition/{userid}', [HealthConditionController::class, 'getByUserId']); 

        // Antrian Admin
        Route::get('/admin/queues/today', [QueueAdminController::class, 'today']);
        Route::get('/admin/queues/current', [QueueAdminController::class, 'current']);
        Route::get('/admin/queues/history', [QueueAdminController::class, 'history']);
        Route::get('/admin/queues/stats', [QueueAdminController::class, 'stats']);
        Route::put('/admin/queues/{id}/process', [QueueAdminController::class, 'process']);
        Route::put('/admin/queues/{id}/finish', [QueueAdminController::class, 'finish']);
        Route::put('/admin/queues/{id}/skip', [QueueAdminController::class, 'skip']);
        Route::get('/admin/queue/history/user/{id}', [QueueAdminController::class, 'userHistory']);

        // Data Departments
        Route::prefix('departments')->group(function () {
            Route::get('/', [DepartmentController::class, 'index']);
            Route::post('/', [DepartmentController::class, 'store']);
            Route::get('/{id}', [DepartmentController::class, 'show']);
            Route::put('/{id}', [DepartmentController::class, 'update']);
            Route::delete('/{id}', [DepartmentController::class, 'destroy']);
        });

        // Data Grades
        Route::prefix('grades')->group(function () {
            Route::get('/', [GradeController::class, 'index']);
            Route::post('/', [GradeController::class, 'store']);
            Route::get('/{id}', [GradeController::class, 'show']);
            Route::put('/{id}', [GradeController::class, 'update']);
            Route::delete('/{id}', [GradeController::class, 'destroy']);
        });

        // CATEGORY
        Route::post('/categories', [BlogController::class, 'createCategory']);
        Route::put('/categories/{id}', [BlogController::class, 'updateCategory']);
        Route::delete('/categories/{id}', [BlogController::class, 'deleteCategory']);

        // ARTICLE
        Route::post('/articles', [BlogController::class, 'createArticle']);
        Route::put('/articles/{id}', [BlogController::class, 'updateArticle']);
        Route::delete('/articles/{id}', [BlogController::class, 'deleteArticle']);
    });
});