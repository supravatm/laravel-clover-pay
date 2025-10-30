<?php

use Illuminate\Support\Facades\Route;
use Supravatm\CloverPayment\Http\Controllers\CloverCheckoutController;
use Supravatm\CloverPayment\Http\Controllers\CloverPaymentController;
use Supravatm\CloverPayment\Http\Controllers\CloverOAuthController;


Route::prefix('api')->group(function () {
    Route::post('/make-payment', [CloverPaymentController::class, 'makePayment']);
    Route::post('/create-token', [CloverPaymentController::class, 'createToken']);
});

Route::group(['middleware' => ['web']], function () {
    Route::get('/test', [CloverCheckoutController::class, 'test']);
    Route::get('/checkout', [CloverCheckoutController::class, 'index'])->name('checkout');
    Route::get('/oauth/callback', [CloverOAuthController::class, 'handleCallback']);
});
