<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Application\Infrastructure\Http\Controllers\WindStockController;

Route::prefix('windbox')->group(function () {
    Route::post('store', [WindStockController::class, 'storeWind']);
    Route::post('allocate', [WindStockController::class, 'allocateWind']);
    Route::get('available/{location}', [WindStockController::class, 'getAvailableWind']);
});
