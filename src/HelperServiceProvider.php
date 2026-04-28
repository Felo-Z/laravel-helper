<?php

namespace FeloZ\LaravelHelper;

use FeloZ\LaravelHelper\Commands\ClearCacheCommand;
use FeloZ\LaravelHelper\Commands\ClearLogsCommand;
use FeloZ\LaravelHelper\Support\ApiResponse;
use FeloZ\LaravelHelper\Support\Contracts\ApiResponseContract;
use FeloZ\LaravelHelper\Support\SqlLogger\BindingInterpolator;
use FeloZ\LaravelHelper\Support\SqlLogger\FileNameResolver;
use FeloZ\LaravelHelper\Support\SqlLogger\QueryLogger;
use FeloZ\LaravelHelper\Support\SqlLogger\SqlLogFormatter;
use FeloZ\LaravelHelper\Support\SqlLogger\SqlLoggerConfig;
use FeloZ\LaravelHelper\Support\SqlLogger\SqlLogWriter;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\ServiceProvider;

class HelperServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     */
    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/publish/felo-helper.php' => config_path('felo-helper.php'),
        ], 'config');

        $this->registerApiResponseRenderUsing();
        $this->registerSqlLogger();

        if ($this->app->runningInConsole()) {
            $this->commands([
                ClearLogsCommand::class,
                ClearCacheCommand::class,
            ]);
        }
    }

    public function register(): void
    {
        $configPath = __DIR__.'/publish/felo-helper.php';

        if (file_exists($configPath)) {
            $this->mergeConfigFrom(
                $configPath,
                'felo-helper'
            );
        }

        $this->app->singleton(ApiResponse::class, static fn (): ApiResponse => new ApiResponse);
        $this->app->singleton(ApiResponseContract::class, static fn ($app): ApiResponse => $app->make(ApiResponse::class));
        $this->app->singleton(SqlLoggerConfig::class);
        $this->app->singleton(BindingInterpolator::class);
        $this->app->singleton(FileNameResolver::class);
        $this->app->singleton(SqlLogFormatter::class);
        $this->app->singleton(SqlLogWriter::class);
        $this->app->singleton(QueryLogger::class);
    }

    protected function registerApiResponseRenderUsing(): void
    {
        if (! config('felo-helper.api_response.enable_render_using', true)) {
            return;
        }

        $renderUsing = config('felo-helper.api_response.render_using');
        if (! $renderUsing) {
            return;
        }

        $exceptionHandler = $this->app->make(ExceptionHandler::class);
        if (! is_callable([$exceptionHandler, 'renderable'])) {
            return;
        }

        if (! is_callable($renderUsing)) {
            $renderUsing = $this->app->make($renderUsing);
        }

        if (is_callable($renderUsing)) {
            call_user_func([$exceptionHandler, 'renderable'], $renderUsing);
        }
    }

    protected function registerSqlLogger(): void
    {
        /** @var SqlLoggerConfig $config */
        $config = $this->app->make(SqlLoggerConfig::class);
        if (! $config->isEnabled()) {
            return;
        }

        /** @var DatabaseManager $db */
        $db = $this->app->make('db');
        $db->listen($this->app->make(QueryLogger::class)->handle(...));
    }
}
