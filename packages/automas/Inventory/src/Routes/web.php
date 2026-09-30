<?php

use Illuminate\Support\Facades\Route;
use Automas\Inventory\Http\Controllers\InventoryItemController;
use Automas\Inventory\Http\Controllers\InventoryAdjustmentController;
use Automas\Inventory\Http\Controllers\InventoryReportController;

Route::middleware(['web', 'auth'])->prefix('inventory')->name('inventory.')->group(function () {
    Route::resource('items', InventoryItemController::class);
    Route::get('inventory/items/by-product/{productId}', [InventoryItemController::class, 'getByProduct'])->name('items.by-product');
    
    Route::get('adjustments/get-quantity', [InventoryAdjustmentController::class, 'getQuantity'])->name('adjustments.get-quantity');
    Route::get('adjustments/warehouse/items', [InventoryAdjustmentController::class, 'getWarehouseItems'])->name('adjustments.warehouse.items');
    Route::resource('adjustments', InventoryAdjustmentController::class);
    Route::post('adjustments/{adjustment}/approve', [InventoryAdjustmentController::class, 'approve'])->name('adjustments.approve');
    Route::post('adjustments/{adjustment}/post', [InventoryAdjustmentController::class, 'post'])->name('adjustments.post');
    
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/stock-valuation', [InventoryReportController::class, 'stockValuationReport'])->name('stock-valuation');
        Route::get('/cogs', [InventoryReportController::class, 'cogsReport'])->name('cogs');
        Route::get('/stock-movement', [InventoryReportController::class, 'stockMovementReport'])->name('stock-movement');
    });
});
