<?php

use App\Http\Controllers\Api\V1\CashController;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::view('/login', 'auth.login')->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');
});

Route::middleware('auth')->group(function () {
    Route::view('/', 'pos')->name('pos.index');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::prefix('api/v1')->group(function () {
        Route::post('/cash/open', [CashController::class, 'open']);
        Route::get('/cash/current', [CashController::class, 'current']);
    });
});
