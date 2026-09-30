<?php

use Illuminate\Support\Facades\Route;
use Automas\SalesOrder\Http\Controllers\Api\SalesOrderApiController;

Route::prefix('api')
    ->middleware(['api.json'])
    ->group(function () {
        Route::group(['middleware' => ['auth:sanctum'], 'prefix' => 'sales-orders'], function () {
            Route::get('/', [SalesOrderApiController::class, 'index']);
            Route::post('/store', [SalesOrderApiController::class, 'store']);
            Route::post('/update/{id}', [SalesOrderApiController::class, 'update']);
            Route::delete('/delete/{id}', [SalesOrderApiController::class, 'destroy']);
        });
    });
