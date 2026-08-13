<?php

declare(strict_types=1);

namespace Crmleaf\Payroll\Tools\RoiCalculator;

use Crmleaf\Payroll\Calculators\RoiCalculator;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

/**
 * Registers Payroll ROI Calculator with a Laravel application.
 *
 * Everything this provider adds is either inert or off by default: the
 * calculator binding, one Blade component and a set of publishable paths. The
 * HTTP route is opt-in through `config('roi-calculator.route.enabled')`, because a
 * package that installs a public URL into your application without being asked
 * is a package that has made a routing decision on your behalf.
 */
final class RoiCalculatorServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/roi-calculator.php', 'roi-calculator');

        // A singleton because the calculator is stateless and its rate
        // repository parses the statutory tables once per process.
        $this->app->singleton(RoiCalculator::class, static fn (): RoiCalculator => new RoiCalculator());
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'roi-calculator');

        // One component per tool: resources/views/components/roi-calculator.blade.php,
        // written as <x-crmleaf::roi-calculator />. Every tool registers the same
        // 'crmleaf' prefix, so fifteen independently installed packages share one
        // component namespace instead of contributing fifteen aliases.
        Blade::anonymousComponentPath(__DIR__.'/../resources/views/components', 'crmleaf');

        if ($this->routeEnabled()) {
            $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        }

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/roi-calculator.php' => config_path('roi-calculator.php'),
            ], 'roi-calculator-config');

            $this->publishes([
                __DIR__.'/../resources/views' => resource_path('views/vendor/roi-calculator'),
            ], 'roi-calculator-views');

            $this->publishes([
                __DIR__.'/../resources/assets' => public_path('vendor/roi-calculator'),
            ], 'roi-calculator-assets');
        }
    }

    /**
     * @return array<int, string>
     */
    public function provides(): array
    {
        return [
            RoiCalculator::class,
        ];
    }

    private function routeEnabled(): bool
    {
        /** @var \Illuminate\Contracts\Config\Repository $config */
        $config = $this->app->make('config');

        return (bool) $config->get('roi-calculator.route.enabled', false);
    }
}
