<?php

namespace TromsFylkestrafikk\RagnarokConsat;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use TromsFylkestrafikk\RagnarokConsat\Services\ConsatFiles;

class RagnarokConsatServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishConfig();
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        // $this->registerRoutes();
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/ragnarok_consat.php', 'ragnarok_consat');
        $this->app->singleton(ConsatFiles::class, function () {
            return new ConsatFiles();
        });
    }

    /**
     * Register the package routes.
     *
     * @return void
     */
    private function registerRoutes(): void
    {
        Route::group($this->routeConfiguration(), function () {
            $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        });
    }

    /**
    * Get route group configuration array.
    *
    * @return array
    */
    private function routeConfiguration(): array
    {
        return [
            'namespace'  => "TromsFylkestrafikk\RagnarokConsat\Http\Controllers",
            'middleware' => 'api',
            'prefix'     => 'api'
        ];
    }

    /**
     * Publish Config
     *
     * @return void
     */
    public function publishConfig()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/ragnarok_consat.php' => config_path('ragnarok_consat.php'),
            ], ['config', 'ragnarok_consat', 'ragnarok_consat.config']);
        }
    }
}
