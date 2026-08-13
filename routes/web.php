<?php

declare(strict_types=1);

use Crmleaf\Payroll\Tools\RoiCalculator\Http\Controllers\RoiCalculatorController;
use Illuminate\Support\Facades\Route;

/*
 * Loaded by RoiCalculatorServiceProvider only when config('roi-calculator.route.enabled')
 * is true, so requiring the package never adds a URL on its own.
 */

/** @var \Illuminate\Contracts\Config\Repository $config */
$config = app('config');

Route::middleware((array) $config->get('roi-calculator.route.middleware', ['web']))
    ->prefix((string) $config->get('roi-calculator.route.prefix', 'tools'))
    ->group(static function () use ($config): void {
        Route::match(['get', 'post'], '/roi-calculator', RoiCalculatorController::class)
            ->name((string) $config->get('roi-calculator.route.name', 'roi-calculator'));
    });
