<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Owner\OwnerController;
use App\Http\Controllers\Pwa\CollectionController;
use App\Http\Controllers\Pwa\CreditOverrideController;
use App\Http\Controllers\Pwa\CustomerController;
use App\Http\Controllers\Pwa\CustomerReturnController;
use App\Http\Controllers\Pwa\SalesOrderController;
use App\Http\Controllers\Pwa\VisitController;
use App\Http\Controllers\Reports\ReportsController;
use App\Http\Controllers\Settings\SettingsController;
use App\Livewire\Pwa\Dashboard;
use App\Livewire\Pwa\StockBalance;
use App\Livewire\Pwa\VisitDetail;
use App\Livewire\Pwa\VisitList;
use App\Models\PaymentReceipt;
use Illuminate\Support\Facades\Route;

// Root redirect
Route::get('/', fn () => redirect('/login'));

// Auth
Route::get('/login', [LoginController::class, 'showLogin'])->name('login')->middleware('guest');
Route::post('/login', [LoginController::class, 'login'])->middleware('guest');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

// Owner routes
Route::middleware(['auth', 'role:OWNER'])->prefix('owner')->name('owner.')->group(function () {
    Route::get('/dashboard', [OwnerController::class, 'dashboard'])->name('dashboard');
    Route::get('/approvals', [OwnerController::class, 'approvals'])->name('approvals');
});

// Admin routes
Route::middleware(['auth', 'role:ADMIN'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/closing', [AdminController::class, 'closing'])->name('closing');
    Route::post('/closing/execute', [AdminController::class, 'executeClosing'])->name('closing.execute');
});

// Shared routes (Owner + Admin)
Route::middleware(['auth', 'role:OWNER|ADMIN'])->name('reports.')->group(function () {
    Route::get('/reports/sales', [ReportsController::class, 'sales'])->name('sales');
    Route::get('/reports/stock', [ReportsController::class, 'stock'])->name('stock');
    Route::get('/reports/visits', [ReportsController::class, 'visits'])->name('visits');
    Route::get('/reports/ar', [ReportsController::class, 'ar'])->name('ar');
    Route::get('/reports/collection-risk', [ReportsController::class, 'collectionRisk'])->name('collection-risk');
    Route::get('/reports/bad-stock', [ReportsController::class, 'badStock'])->name('bad-stock');
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings');
    Route::post('/settings', [SettingsController::class, 'update'])->name('settings.update');
});

// Approval actions (Owner only)
Route::middleware(['auth', 'role:OWNER'])->prefix('owner/approvals')->name('owner.approvals.')->group(function () {
    Route::post('/customer-credit/{customer}/approve', [OwnerController::class, 'approveCustomerCredit'])->name('customer-credit.approve');
    Route::post('/customer-credit/{customer}/reject', [OwnerController::class, 'rejectCustomerCredit'])->name('customer-credit.reject');
    Route::post('/stock-adjustment/{adjustment}/approve', [OwnerController::class, 'approveStockAdjustment'])->name('stock-adjustment.approve');
    Route::post('/stock-adjustment/{adjustment}/reject', [OwnerController::class, 'rejectStockAdjustment'])->name('stock-adjustment.reject');
    Route::post('/customer-return/{return}/approve', [OwnerController::class, 'approveCustomerReturn'])->name('customer-return.approve');
    Route::post('/customer-return/{return}/reject', [OwnerController::class, 'rejectCustomerReturn'])->name('customer-return.reject');
    Route::post('/stock-writeoff/{writeoff}/approve', [OwnerController::class, 'approveStockWriteoff'])->name('stock-writeoff.approve');
    Route::post('/stock-writeoff/{writeoff}/reject', [OwnerController::class, 'rejectStockWriteoff'])->name('stock-writeoff.reject');
    Route::post('/credit-override/{override}/approve', [OwnerController::class, 'approveCreditOverride'])->name('credit-override.approve');
    Route::post('/credit-override/{override}/reject', [OwnerController::class, 'rejectCreditOverride'])->name('credit-override.reject');
});

// Payment Receipt verification (public — untuk QR Code scan)
Route::get('/receipts/{receipt}/verify', function (string $receipt) {
    $rec = PaymentReceipt::where('receipt_number', $receipt)
        ->with(['payment.customer', 'payment.collectedBy'])
        ->firstOrFail();

    return response()->json([
        'receipt_number' => $rec->receipt_number,
        'customer' => $rec->customer_name_snapshot,
        'total_paid' => $rec->total_paid,
        'remaining_after' => $rec->remaining_after,
        'receipt_date' => $rec->receipt_date,
        'collector' => $rec->collector_name_snapshot,
        'status' => $rec->status,
    ]);
})->name('receipts.verify');

// PWA routes (Salesman only)
Route::middleware(['auth', 'role:SALESMAN'])->prefix('pwa/api')->name('pwa.')->group(function () {
    Route::post('/visits/checkin', [VisitController::class, 'checkin'])->name('visits.checkin');
    Route::post('/visits/checkout', [VisitController::class, 'checkout'])->name('visits.checkout');
    Route::post('/visits/unplanned', [VisitController::class, 'createUnplanned'])->name('visits.unplanned');
    Route::post('/sales-orders', [SalesOrderController::class, 'store'])->name('sales-orders.store');
    Route::post('/sales-orders/{salesOrder}/post', [SalesOrderController::class, 'post'])->name('sales-orders.post');
    Route::post('/sales-orders/{salesOrder}/cancel', [SalesOrderController::class, 'cancel'])->name('sales-orders.cancel');
    Route::post('/customers', [CustomerController::class, 'store'])->name('customers.store');
    Route::post('/collection/payment', [CollectionController::class, 'recordCashPayment'])->name('collection.payment');
    Route::post('/collection/skip', [CollectionController::class, 'skip'])->name('collection.skip');
    Route::post('/customer-returns', [CustomerReturnController::class, 'store'])->name('customer-returns.store');
    Route::post('/credit-override/request', [CreditOverrideController::class, 'request'])->name('credit-override.request');
    Route::get('/credit-override/{salesOrder}/status', [CreditOverrideController::class, 'checkStatus'])->name('credit-override.status');
});

// PWA web pages (Salesman only)
Route::middleware(['auth', 'role:SALESMAN'])->prefix('pwa')->name('pwa.pages.')->group(function () {
    Route::get('/dashboard', Dashboard::class)->name('dashboard');
    Route::get('/visits', VisitList::class)->name('visits');
    Route::get('/stock', StockBalance::class)->name('stock');
    Route::get('/visits/{visitPlan}', VisitDetail::class)->name('visits.detail')->whereNumber('visitPlan');
});
