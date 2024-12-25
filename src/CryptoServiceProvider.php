<?php

namespace Jacknguyen\Crypto;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use JackNguyen\Crypto\Console\Commands\SolanaBlockListener;

class CryptoServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishConfig();
        $this->loadMigrationsFrom(__DIR__.'/../migrations');
        $this->registerRoutes();
        if ($this->app->runningInConsole()) {
            $this->commands([
                SolanaBlockListener::class,
            ]);
        }
    }

    public function register()
    {
        // Merge config
        $this->mergeConfigFrom(
            __DIR__.'/../config/solana.php', 'solana'
        );
    }
    private function registerRoutes()
    {
        Route::group($this->routeConfiguration(), function () {
            $this->loadRoutesFrom(__DIR__ . '/Http/routes.php');
        });
    }
    private function routeConfiguration()
    {
        return [
            'namespace'  => "Jacknguyen\Crypto\Http\Controllers",
            'middleware' => 'api',
            'prefix'     => 'api'
        ];
    }
    public function publishConfig()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/solana.php' => config_path('solana.php'),
            ], 'config');
        }
    }

}
