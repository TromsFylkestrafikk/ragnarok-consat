<?php

namespace Ragnarok\Consat;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Ragnarok\Consat\Jobs\DeleteAgedData;
use Ragnarok\Consat\Services\ConsatFiles;
use Ragnarok\Consat\Services\ConsatImporter;
use Ragnarok\Consat\Sinks\SinkConsat;
use Ragnarok\Sink\Facades\SinkRegistrar;

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
        SinkRegistrar::register(SinkConsat::class);
        // $this->registerRoutes();
        $this->registerScheduledJobs();
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

    protected function registerScheduledJobs()
    {
        $this->app->booted(function () {
            /** @var Schedule */
            $schedule = $this->app->make(Schedule::class);
            $schedule->job(DeleteAgedData::class)->daily();
        });
    }
}
