<?php

namespace Jacknguyen\Crypto;

use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Support\Facades\Config;

class CryptoServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishConfig();

        $this->loadViewsFrom(__DIR__.'/resources/views', 'crypto');
        $this->publishes([
            __DIR__.'/resources/views' => base_path('resources/views/vendor'),
        ]);
        $this->loadMigrationsFrom(__DIR__.'/../migrations');
        $this->registerRoutes();
    }

    public function register()
    {
        // Merge config
        $this->mergeConfigFrom(
            __DIR__.'/../config/crypto.php', 'crypto'
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
                __DIR__ . '/../config/crypto.php' => config_path('crypto.php'),
            ], 'config');
        }
    }

}
