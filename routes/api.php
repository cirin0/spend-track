<?php


use App\Http\Controllers\Api\AuthController;

Route::prefix('auth')->controller(AuthController::class)->group(function () {
    Route::post('login', 'login');
    Route::post('register', 'register');
    Route::middleware('auth:api')->group(function () {
        Route::get('me', 'me');
        Route::post('logout', 'logout');
    });
});
