<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Simtabi\Laranail\Product\Updater\Http\Controllers\HealthController;

Route::prefix((string) config('product-updater.api.prefix', 'api/product-updater/v1'))
    ->middleware((array) config('product-updater.api.middleware', ['api']))
    ->group(function (): void {
        Route::get('health', [HealthController::class, 'show'])->name('product-updater.health');
    });
