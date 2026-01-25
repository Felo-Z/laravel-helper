<?php

namespace Tests\Commands;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class ClearCacheCommandTest extends TestCase
{
    public function test_clear_cache_command()
    {
        Cache::put('test_key', 'test_value');
        $this->assertEquals('test_value', Cache::get('test_key'));

        $this->artisan('felo:clear-cache')
            ->assertExitCode(0)
            ->expectsOutput('Cache cleared successfully.');

        $this->assertNull(Cache::get('test_key'));
    }

    public function test_clear_cache_command_without_laravel_cache()
    {
        Config::set('felo-helper.clear_cache', [
            'clear_laravel_cache' => false,
            'redis_connections' => [],
        ]);

        Cache::put('test_key', 'test_value');
        $this->assertEquals('test_value', Cache::get('test_key'));

        $this->artisan('felo:clear-cache')
            ->assertExitCode(0)
            ->expectsOutput('Cache cleared successfully.');

        $this->assertEquals('test_value', Cache::get('test_key'));
    }
}
