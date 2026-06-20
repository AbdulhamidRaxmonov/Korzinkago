<?php

use App\Http\Controllers\Api\AddressController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\CatalogController;
use App\Http\Controllers\Api\ClickController;
use App\Http\Controllers\Api\CourierController;
use App\Http\Controllers\Api\DeviceTokenController;
use App\Http\Controllers\Api\FavoriteController;
use App\Http\Controllers\Api\MapController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\PaymeController;
use App\Http\Controllers\Api\PromoController;
use App\Http\Controllers\Api\ReviewController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Korzinkago API
|--------------------------------------------------------------------------
*/

Route::prefix('auth')->group(function () {
    Route::post('send-otp', [AuthController::class, 'sendOtp']);
    Route::post('verify-otp', [AuthController::class, 'verifyOtp']);
});

// Ochiq katalog (auth talab qilinmaydi)
Route::get('home', [CatalogController::class, 'home']);
Route::get('categories', [CatalogController::class, 'categories']);
Route::get('products', [CatalogController::class, 'products']);
Route::get('products/{product}', [CatalogController::class, 'product']);
Route::get('products/{product}/reviews', [ReviewController::class, 'productReviews']);

// Payme webhook — auth middlewaresiz (o'zining Basic auth tekshiruvi bor)
Route::post('payme/callback', [PaymeController::class, 'callback']);

// Click webhook'lari — o'zining md5 imzo tekshiruvi bor
Route::post('click/prepare', [ClickController::class, 'prepare']);
Route::post('click/complete', [ClickController::class, 'complete']);

Route::middleware('auth:sanctum')->group(function () {
    // Profil
    Route::get('me', [AuthController::class, 'me']);
    Route::put('profile', [AuthController::class, 'updateProfile']);
    Route::post('logout', [AuthController::class, 'logout']);

    // Savat
    Route::get('cart', [CartController::class, 'index']);
    Route::post('cart', [CartController::class, 'store']);
    Route::put('cart/{item}', [CartController::class, 'update']);
    Route::delete('cart/{item}', [CartController::class, 'destroy']);
    Route::post('cart/clear', [CartController::class, 'clear']);

    // Manzillar
    Route::apiResource('addresses', AddressController::class)->except('show');

    // Buyurtmalar
    Route::get('orders', [OrderController::class, 'index']);
    Route::get('orders/{order}', [OrderController::class, 'show']);
    Route::post('orders/calculate', [OrderController::class, 'calculate']);
    Route::post('orders', [OrderController::class, 'store']);
    Route::post('orders/{order}/cancel', [OrderController::class, 'cancel']);

    // To'lov
    Route::post('payme/checkout', [PaymeController::class, 'checkout']);
    Route::post('click/checkout', [ClickController::class, 'checkout']);

    // Promokod
    Route::post('promo/apply', [PromoController::class, 'apply']);

    // Sevimlilar (wishlist)
    Route::get('favorites', [FavoriteController::class, 'index']);
    Route::get('favorites/ids', [FavoriteController::class, 'ids']);
    Route::post('favorites/toggle', [FavoriteController::class, 'toggle']);

    // Baho va izohlar
    Route::post('reviews', [ReviewController::class, 'store']);

    // Push-bildirishnoma uchun qurilma tokenini ro'yxatga olish
    Route::post('device-token', [DeviceTokenController::class, 'store']);

    // Xarita
    Route::post('map/geocode', [MapController::class, 'geocode']);
    Route::post('map/reverse', [MapController::class, 'reverse']);

    // Kuryer (faqat courier roli)
    Route::middleware('courier')->prefix('courier')->group(function () {
        Route::post('online', [CourierController::class, 'toggleOnline']);
        Route::post('location', [CourierController::class, 'updateLocation']);
        Route::get('available', [CourierController::class, 'available']);
        Route::get('orders', [CourierController::class, 'myOrders']);
        Route::post('orders/{order}/accept', [CourierController::class, 'accept']);
        Route::post('orders/{order}/status', [CourierController::class, 'updateStatus']);
    });
});
