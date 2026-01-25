<?php

namespace Tests;

use FeloZ\LaravelHelper\HelperServiceProvider;

abstract class TestCase extends \Orchestra\Testbench\TestCase
{
    /**
     * Load package service provider.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [HelperServiceProvider::class];
    }

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     */
    protected function getEnvironmentSetUp($app)
    {
        // Setup default database to use sqlite :memory:
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        // Configure felo-helper for testing
        $app['config']->set('felo-helper.clear_cache', [
            'clear_laravel_cache' => true,
            'redis_connections' => [],
        ]);
    }

    protected function setUp(): void
    {
        parent::setUp();
    }
}
