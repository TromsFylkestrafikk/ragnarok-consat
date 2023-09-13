<?php

namespace Ragnarok\Consat;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Ragnarok\Consat\Services\ConsatFiles;
use Ragnarok\Consat\Services\ConsatImporter;

class RagnarokConsatServiceProvider extends ServiceProvider
{
    public $singletons = [
        ConsatFiles::class => ConsatFiles::class,
        ConsatImporter::class => ConsatImporter::class,
    ];

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
            'namespace'  => "Ragnarok\Consat\Http\Controllers",
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
