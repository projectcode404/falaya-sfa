<?php

use App\Http\Controllers\Pwa\CollectionController;
use App\Http\Controllers\Pwa\CreditOverrideController;
use App\Http\Controllers\Pwa\CustomerController;
use App\Http\Controllers\Pwa\CustomerReturnController;
use App\Http\Controllers\Pwa\SalesOrderController;
use App\Http\Controllers\Pwa\VisitController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->prefix('pwa/api')->name('pwa.')->group(function () {

    // Visit
    Route::post('/visits/checkin', [VisitController::class, 'checkin'])->name('visits.checkin');
    Route::post('/visits/checkout', [VisitController::class, 'checkout'])->name('visits.checkout');
    Route::post('/visits/unplanned', [VisitController::class, 'createUnplanned'])->name('visits.unplanned');

    // Sales Order
    Route::post('/sales-orders', [SalesOrderController::class, 'store'])->name('sales-orders.store');
    Route::post('/sales-orders/{salesOrder}/post', [SalesOrderController::class, 'post'])->name('sales-orders.post');
    Route::post('/sales-orders/{salesOrder}/cancel', [SalesOrderController::class, 'cancel'])->name('sales-orders.cancel');

    // Customer
    Route::post('/customers', [CustomerController::class, 'store'])->name('customers.store');

    // Collection
    Route::post('/collection/payment', [CollectionController::class, 'recordCashPayment'])->name('collection.payment');
    Route::post('/collection/skip', [CollectionController::class, 'skip'])->name('collection.skip');

    // Customer Return
    Route::post('/customer-returns', [CustomerReturnController::class, 'store'])->name('customer-returns.store');

    // Credit Override
    Route::post('/credit-override/request', [CreditOverrideController::class, 'request'])->name('credit-override.request');
    Route::get('/credit-override/{salesOrder}/status', [CreditOverrideController::class, 'checkStatus'])->name('credit-override.status');
});
