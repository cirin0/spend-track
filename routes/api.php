<?php


use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ExpenseController;

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
        // Не знаю як правильно зробити
        Route::get('{type}/{id}', [CategoryController::class, 'show'])
            ->where(['type' => 'default|user', 'id' => '[0-9]+']);
        Route::get('{slug}', [CategoryController::class, 'showBySlug']);
        Route::patch('/{id}', [CategoryController::class, 'update']);
        Route::delete('/{id}', [CategoryController::class, 'destroy']);
    });
});

Route::middleware('auth:api')->group(function () {
    Route::prefix('expenses')->group(function () {
        Route::get('/', [ExpenseController::class, 'index']);
        Route::post('/', [ExpenseController::class, 'store']);
        Route::get('/stats', [ExpenseController::class, 'stats']);
        Route::get('/{id}', [ExpenseController::class, 'show']);
        Route::get('/category/{categoryId}/{categoryType}', [ExpenseController::class, 'getExpensesByCategory'])
            ->where(['categoryType' => 'default|user']);
        Route::patch('/{id}', [ExpenseController::class, 'update']);
        Route::delete('/{id}', [ExpenseController::class, 'destroy']);
    });
});
