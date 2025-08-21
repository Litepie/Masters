<?php

namespace Litepie\Masters;

use Illuminate\Support\ServiceProvider;
use Litepie\Masters\Console\Commands\InstallMastersCommand;
use Litepie\Masters\Console\Commands\ImportMasterDataCommand;
use Litepie\Masters\Console\Commands\SeedMasterDataCommand;

class MastersServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/masters.php', 'masters'
        );

        $this->app->singleton('masters', function ($app) {
            return new MastersManager($app);
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../routes/api.php');
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'masters');
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'masters');

        if ($this->app->runningInConsole()) {
            $this->commands([
                InstallMastersCommand::class,
                ImportMasterDataCommand::class,
                SeedMasterDataCommand::class,
            ]);

            $this->publishes([
                __DIR__.'/../config/masters.php' => config_path('masters.php'),
            ], 'config');

            $this->publishes([
                __DIR__.'/../database/migrations' => database_path('migrations'),
            ], 'migrations');

            $this->publishes([
                __DIR__.'/../resources/views' => resource_path('views/vendor/masters'),
            ], 'views');

            $this->publishes([
                __DIR__.'/../resources/lang' => resource_path('lang/vendor/masters'),
            ], 'lang');

            $this->publishes([
                __DIR__.'/../public' => public_path('vendor/masters'),
            ], 'assets');
        }
    }
}
