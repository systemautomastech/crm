<?php

use Illuminate\Support\Facades\Route;
use Automas\Pos\Http\Controllers\DashboardController;
use Automas\Pos\Http\Controllers\PosController;
use Automas\Pos\Http\Controllers\PosBillingCounterController;
use Automas\Pos\Http\Controllers\PosDiscountController;
use Automas\Pos\Http\Controllers\PosReportController;
use Automas\Pos\Http\Controllers\PosReturnController;

Route::middleware(['web', 'auth', 'verified', 'PlanModuleCheck:Pos'])->group(function () {
    Route::get('/pos/dashboard', [DashboardController::class, 'index'])->name('pos.index');

    // POS Routes
    Route::get('/pos/orders', [PosController::class, 'index'])->name('pos.orders');
    Route::get('/pos/create', [PosController::class, 'create'])->name('pos.create');
    Route::get('/pos/products', [PosController::class, 'getProducts'])->name('pos.products');
    Route::get('/pos/pos-number', [PosController::class, 'getNextPosNumber'])->name('pos.pos-number');
    Route::post('/pos/store', [PosController::class, 'store'])->name('pos.store');
    Route::get('/pos/orders/{sale}', [PosController::class, 'show'])->name('pos.show');
    Route::get('/pos/barcode', [PosController::class, 'barcode'])->name('pos.barcode');
    Route::get('/pos/barcode/{sale}', [PosController::class, 'printBarcode'])->name('pos.barcode.print');
    Route::get('/pos/orders/{sale}/print', [PosController::class, 'print'])->name('pos-orders.print');
    
    // POS Billing Counter
    Route::get('/pos/billing-counters', [PosBillingCounterController::class, 'index'])->name('pos.billing-counters');
    Route::post('/pos/billing-counters', [PosBillingCounterController::class, 'store'])->name('pos.billing-counters.store');
    Route::put('/pos/billing-counters/{pos_billing_counter}', [PosBillingCounterController::class, 'update'])->name('pos.billing-counters.update');
    Route::delete('/pos/billing-counters/{pos_billing_counter}', [PosBillingCounterController::class, 'destroy'])->name('pos.billing-counters.destroy');

    // POS Discounts
    Route::get('/pos/discounts', [PosDiscountController::class, 'index'])->name('pos.discounts.index');
    Route::get('/pos/discounts/create', [PosDiscountController::class, 'create'])->name('pos.discounts.create');
    Route::post('/pos/discounts', [PosDiscountController::class, 'store'])->name('pos.discounts.store');
    Route::get('/pos/discounts/{pos_discount}', [PosDiscountController::class, 'show'])->name('pos.discounts.show');
    Route::get('/pos/discounts/{pos_discount}/edit', [PosDiscountController::class, 'edit'])->name('pos.discounts.edit');
    Route::put('/pos/discounts/{pos_discount}', [PosDiscountController::class, 'update'])->name('pos.discounts.update');
    Route::delete('/pos/discounts/{pos_discount}', [PosDiscountController::class, 'destroy'])->name('pos.discounts.destroy');


    // POS Reports

    Route::prefix('pos/reports')->name('pos.reports.')->group(function () {
        Route::get('/sales', [PosReportController::class, 'sales'])->name('sales');
        Route::get('/products', [PosReportController::class, 'products'])->name('products');
        Route::get('/customers', [PosReportController::class, 'customers'])->name('customers');
    });

    // POS Returns
    Route::prefix('pos/returns')->name('pos.returns.')->group(function () {
        Route::get('/', [PosReturnController::class, 'index'])->name('index');
        Route::get('/create', [PosReturnController::class, 'create'])->name('create');
        Route::post('/', [PosReturnController::class, 'store'])->name('store');
        Route::get('/{posReturn}', [PosReturnController::class, 'show'])->name('show');
        Route::post('/{posReturn}/approve', [PosReturnController::class, 'approve'])->name('approve');
        Route::post('/{posReturn}/complete', [PosReturnController::class, 'complete'])->name('complete');
        Route::delete('/{posReturn}', [PosReturnController::class, 'destroy'])->name('destroy');
    });

    
});