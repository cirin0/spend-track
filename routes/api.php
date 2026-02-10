<?php


use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ExpenseController;
use App\Http\Controllers\Api\GroupController;

Route::prefix('auth')->controller(AuthController::class)->group(function () {
    Route::post('login', 'login');
    Route::post('register', 'register');
    Route::middleware('auth:api')->group(function () {
        Route::get('me', 'me');
        Route::post('logout', 'logout');
    });
});

Route::middleware('auth:api')->group(function () {
    Route::prefix('categories')->group(function () {
        Route::get('/', [CategoryController::class, 'index']);
        Route::post('/', [CategoryController::class, 'store']);
        Route::get('/{id}', [CategoryController::class, 'show'])->where(['id' => '[0-9]+']);
        Route::get('/{slug}', [CategoryController::class, 'showBySlug'])->where(['slug' => '[a-z0-9-]+']);
        Route::patch('/{id}', [CategoryController::class, 'update']);
        Route::delete('/{id}', [CategoryController::class, 'destroy']);
    });
});

Route::middleware('auth:api')->group(function () {
    Route::prefix('expenses')->group(function () {
        Route::get('/', [ExpenseController::class, 'index']);
        Route::post('/', [ExpenseController::class, 'store']);
        Route::get('/stats', [ExpenseController::class, 'stats']);
        Route::get('/category/{categoryId}', [ExpenseController::class, 'getExpensesByCategory']);
        Route::post('/category/{categoryId}', [ExpenseController::class, 'storeByCategory']);
        Route::get('/{id}', [ExpenseController::class, 'show'])->where(['id' => '[0-9]+']);
        Route::patch('/{id}', [ExpenseController::class, 'update']);
        Route::delete('/{id}', [ExpenseController::class, 'destroy']);
    });
});

Route::middleware('auth:api')->group(function () {
    Route::prefix('groups')->group(function () {
        Route::get('/', [GroupController::class, 'index']);
        Route::post('/', [GroupController::class, 'store']);
        Route::get('/{id}', [GroupController::class, 'show']);
        Route::patch('/{id}', [GroupController::class, 'update']);
        Route::delete('/{id}', [GroupController::class, 'destroy']);
        Route::post('/{id}/members', [GroupController::class, 'addMember']);
        Route::delete('/{id}/members/{userId}', [GroupController::class, 'removeMember']);
        Route::post('/{id}/leave', [GroupController::class, 'leave']);
    });
});
