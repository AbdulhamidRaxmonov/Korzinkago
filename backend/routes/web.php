<?php

use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CourierManagementController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\OrderManagementController;
use App\Http\Controllers\Admin\ProductController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('admin.login');
});

Route::prefix('admin')->name('admin.')->group(function () {
    // Auth
    Route::get('login', [AdminAuthController::class, 'showLogin'])->name('login');
    Route::post('login', [AdminAuthController::class, 'login']);
    Route::post('logout', [AdminAuthController::class, 'logout'])->name('logout');

    Route::middleware('admin')->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        // Kategoriyalar
        Route::resource('categories', CategoryController::class)->except('show');

        // Mahsulotlar
        Route::resource('products', ProductController::class)->except('show');

        // Buyurtmalar
        Route::get('orders', [OrderManagementController::class, 'index'])->name('orders.index');
        Route::get('orders/{order}', [OrderManagementController::class, 'show'])->name('orders.show');
        Route::post('orders/{order}/status', [OrderManagementController::class, 'updateStatus'])->name('orders.status');
        Route::post('orders/{order}/assign', [OrderManagementController::class, 'assignCourier'])->name('orders.assign');

        // Kuryerlar
        Route::get('couriers', [CourierManagementController::class, 'index'])->name('couriers.index');
        Route::get('couriers/create', [CourierManagementController::class, 'create'])->name('couriers.create');
        Route::post('couriers', [CourierManagementController::class, 'store'])->name('couriers.store');
        Route::post('couriers/{courier}/toggle', [CourierManagementController::class, 'toggleActive'])->name('couriers.toggle');
    });
});
