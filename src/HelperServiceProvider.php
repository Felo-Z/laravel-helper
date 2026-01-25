<?php

namespace FeloZ\LaravelHelper;

use Illuminate\Support\ServiceProvider;

class HelperServiceProvider extends ServiceProvider
{
    protected bool $defer = true;

    /**
     * Perform post-registration booting of services.
     */
    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/publish/felo-helper.php' => config_path('felo-helper.php'),
        ], 'config');

        if ($this->app->runningInConsole()) {
            $this->commands([
                \FeloZ\LaravelHelper\Commands\ClearLogsCommand::class,
                \FeloZ\LaravelHelper\Commands\ClearCacheCommand::class,
            ]);
        }
    }

    public function register(): void
    {
        $configPath = \dirname(__DIR__).'/publish/felo-helper.php';

        if (file_exists($configPath)) {
            $this->mergeConfigFrom(
                $configPath,
                'felo-helper'
            );
        }
    }
}
