<?php

namespace Tests;

use FeloZ\LaravelHelper\HelperServiceProvider;
use Illuminate\Foundation\Application;

abstract class TestCase extends \Orchestra\Testbench\TestCase
{
    /**
     * Load package service provider.
     *
     * @param  Application  $app
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [HelperServiceProvider::class];
    }

    /**
     * Define environment setup.
     *
     * @param  Application  $app
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
        $app['config']->set('felo-helper.sql_logger', [
            'enabled' => false,
            'directory' => storage_path('logs/sql-tests'),
            'replace_bindings' => true,
            'collapse_whitespace' => true,
            'ignore_connections' => [],
            'exclude_patterns' => [],
            'only_methods' => [],
            'exclude_methods' => [],
            'include_raw_sql' => true,
            'include_bindings' => true,
            'max_query_length' => 0,
            'max_binding_length' => 0,
            'group_by_scope' => false,
            'scope_header_format' => "================ SCOPE START ================\n".
                "scope: {scope_type}\n".
                "id: {scope_id}\n".
                "name: {scope_name}\n".
                "time: {scope_started_at}\n".
                "{scope_context}\n",
            'date_format' => 'Y-m-d H:i:s.u',
            'entry_format' => "[{datetime}] [{channel}] [{origin}] [{connection}] [{duration}]\n{sql}\n{separator}\n",
            'channels' => [
                'all' => [
                    'enabled' => true,
                    'pattern' => '/.+/s',
                    'file_name' => '{date:Y-m-d}-all.sql',
                    'append' => true,
                ],
                'slow' => [
                    'enabled' => true,
                    'threshold_ms' => 100,
                    'pattern' => '/.+/s',
                    'file_name' => '{date:Y-m-d}-slow.sql',
                    'append' => true,
                ],
            ],
        ]);
    }

    protected function setUp(): void
    {
        parent::setUp();
    }
}
