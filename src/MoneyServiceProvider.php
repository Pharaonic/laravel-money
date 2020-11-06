<?php

namespace Pharaonic\Laravel\Money;

use Illuminate\Support\ServiceProvider;
use Pharaonic\Laravel\Money\Facades\Money;

class MoneyServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        // Config Merge
        $this->mergeConfigFrom(__DIR__ . '/config/money.php', ['pharaonic', 'laravel-money']);

        // Loads
        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        // Publishes
        $this->publishes([
            __DIR__ . '/config/money.php'                   => config_path('Pharaonic/money.php'),
            __DIR__ . '/database/migrations/money.stub'     => database_path(sprintf('migrations/%s_create_money_table.php',   date('Y_m_d_His', time() + 1)))
        ], ['pharaonic', 'laravel-money']);

        // Initialization
        $this->app->instance('Money', new Money);
        app('Money')->init();
    }
}
