<?php

use AhmadShebbo\LaravelHyperpay\Http\Controllers\CreditCardController;
use AhmadShebbo\LaravelHyperpay\Http\Controllers\PaymentController;
use AhmadShebbo\LaravelHyperpay\Http\Controllers\WebhookController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| HyperPay Routes
|--------------------------------------------------------------------------
|
| Here are the routes for the HyperPay payment gateway integration.
| You can customize these routes as needed for your application.
|
*/

Route::middleware(['web'])->group(function () {
    // Payment processing routes
    Route::prefix('payment')->name('payment.')->group(function () {
        Route::post('/process', [PaymentController::class, 'process'])->name('process');
        Route::get('/success', [PaymentController::class, 'success'])->name('success');
        Route::get('/failed', [PaymentController::class, 'failed'])->name('failed');
        Route::get('/pending', [PaymentController::class, 'pending'])->name('pending');
        Route::get('/cancelled', [PaymentController::class, 'cancelled'])->name('cancelled');
        Route::get('/retry', [PaymentController::class, 'retry'])->name('retry');
        Route::get('/status/{paymentId}', [PaymentController::class, 'status'])->name('status');
    });

    // Credit card management routes (requires authentication)
    Route::middleware(['auth'])->prefix('credit-cards')->name('credit-cards.')->group(function () {
        Route::get('/', [CreditCardController::class, 'index'])->name('index');
        Route::get('/create', [CreditCardController::class, 'create'])->name('create');
        Route::post('/', [CreditCardController::class, 'store'])->name('store');
        Route::get('/{registrationId}', [CreditCardController::class, 'show'])->name('show');
        Route::put('/{registrationId}', [CreditCardController::class, 'update'])->name('update');
        Route::delete('/{registrationId}', [CreditCardController::class, 'destroy'])->name('destroy');
        Route::post('/{registrationId}/default', [CreditCardController::class, 'setDefault'])->name('set-default');
        Route::post('/{registrationId}/pay', [CreditCardController::class, 'payWithCard'])->name('pay-with-card');
    });

    // Webhook route (should be publicly accessible)
    Route::post('/hyperpay/webhook', [WebhookController::class, 'handle'])->name('hyperpay.webhook');
});

// API routes (if you want to expose payment APIs)
Route::middleware(['api'])->prefix('api/payment')->name('api.payment.')->group(function () {
    Route::post('/checkout', [PaymentController::class, 'createCheckout'])->name('checkout');
    Route::post('/process', [PaymentController::class, 'processPayment'])->name('process');
    Route::get('/status/{paymentId}', [PaymentController::class, 'getStatus'])->name('status');
    Route::post('/refund/{paymentId}', [PaymentController::class, 'refund'])->name('refund');
    Route::post('/capture/{paymentId}', [PaymentController::class, 'capture'])->name('capture');
});

// API routes for credit card management (requires authentication)
Route::middleware(['auth:sanctum'])->prefix('api/credit-cards')->name('api.credit-cards.')->group(function () {
    Route::get('/', [CreditCardController::class, 'getCards'])->name('index');
    Route::post('/', [CreditCardController::class, 'store'])->name('store');
    Route::put('/{registrationId}', [CreditCardController::class, 'update'])->name('update');
    Route::delete('/{registrationId}', [CreditCardController::class, 'destroy'])->name('destroy');
    Route::post('/{registrationId}/default', [CreditCardController::class, 'setDefault'])->name('set-default');
    Route::post('/{registrationId}/pay', [CreditCardController::class, 'payWithCard'])->name('pay-with-card');
});
