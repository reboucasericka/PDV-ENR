<?php

use App\Http\Controllers\Api\V1\CashController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\DashboardController;
use App\Http\Controllers\Api\V1\OrderController;
use App\Http\Controllers\Api\V1\ProductController;
use App\Http\Controllers\Api\V1\ReportController;
use App\Http\Controllers\Api\V1\SettingController;
use App\Http\Controllers\Api\V1\StoreController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::get('products', [ProductController::class, 'index']);
    Route::get('products/{id}', [ProductController::class, 'show'])->whereNumber('id');

    Route::get('categories', [CategoryController::class, 'index']);
    Route::get('categories/active', [CategoryController::class, 'active']);
    Route::get('categories/{id}', [CategoryController::class, 'show'])->whereNumber('id');

    Route::get('stores', [StoreController::class, 'index']);
    Route::get('stores/active', [StoreController::class, 'active']);

    Route::prefix('orders')->group(function () {
        Route::get('/', [OrderController::class, 'index']);
        Route::post('/', [OrderController::class, 'store']);
        Route::get('{id}', [OrderController::class, 'show']);
        Route::post('{orderId}/items', [OrderController::class, 'addItem']);
    });

    Route::middleware(['web', 'auth'])->group(function () {
        Route::get('dashboard', [DashboardController::class, 'index']);

        Route::prefix('reports')->group(function () {
            Route::get('summary', [ReportController::class, 'summary']);
            Route::get('sales', [ReportController::class, 'sales']);
            Route::get('products', [ReportController::class, 'products']);
            Route::get('categories', [ReportController::class, 'categories']);
            Route::get('payments', [ReportController::class, 'payments']);
            Route::get('cash-registers', [ReportController::class, 'cashRegisters']);
        });

        Route::get('settings', [SettingController::class, 'index']);
        Route::get('settings/current', [SettingController::class, 'current']);
        Route::post('settings', [SettingController::class, 'store']);
        Route::post('settings/current', [SettingController::class, 'updateCurrent']);
        Route::get('settings/{id}', [SettingController::class, 'show'])->whereNumber('id');
        Route::put('settings/{id}', [SettingController::class, 'update'])->whereNumber('id');
        Route::post('settings/{id}', [SettingController::class, 'update'])->whereNumber('id');
        Route::delete('settings/{id}', [SettingController::class, 'destroy'])->whereNumber('id');

        Route::post('cash/open', [CashController::class, 'open']);
        Route::get('cash/current', [CashController::class, 'current']);
        Route::post('cash/close', [CashController::class, 'close']);
        Route::get('cash/history', [CashController::class, 'history']);
        Route::get('cash/{id}', [CashController::class, 'show'])->whereNumber('id');

        Route::post('categories', [CategoryController::class, 'store']);
        Route::put('categories/{id}', [CategoryController::class, 'update'])->whereNumber('id');
        Route::post('categories/{id}/activate', [CategoryController::class, 'activate'])->whereNumber('id');
        Route::post('categories/{id}/deactivate', [CategoryController::class, 'deactivate'])->whereNumber('id');
        Route::delete('categories/{id}', [CategoryController::class, 'destroy'])->whereNumber('id');

        Route::post('products', [ProductController::class, 'store']);
        Route::put('products/{id}', [ProductController::class, 'update'])->whereNumber('id');
        Route::post('products/{id}/activate', [ProductController::class, 'activate'])->whereNumber('id');
        Route::post('products/{id}/deactivate', [ProductController::class, 'deactivate'])->whereNumber('id');
        Route::delete('products/{id}', [ProductController::class, 'destroy'])->whereNumber('id');
    });
});
