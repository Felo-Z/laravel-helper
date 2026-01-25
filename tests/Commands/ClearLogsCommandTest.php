<?php

namespace Tests\Commands;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ClearLogsCommandTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('logs');
    }

    public function test_clear_logs_command()
    {
        $logDir = storage_path('logs');

        if (! is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }

        file_put_contents($logDir.'/test1.log', 'test content 1');
        file_put_contents($logDir.'/test2.log', 'test content 2');
        file_put_contents($logDir.'/test3.json', 'test content 3');

        $this->assertFileExists($logDir.'/test1.log');
        $this->assertFileExists($logDir.'/test2.log');
        $this->assertFileExists($logDir.'/test3.json');

        $this->artisan('felo:clear-logs')
            ->assertExitCode(0)
            ->expectsOutput('Log files cleared successfully.');

        $this->assertFileDoesNotExist($logDir.'/test1.log');
        $this->assertFileDoesNotExist($logDir.'/test2.log');
        $this->assertFileDoesNotExist($logDir.'/test3.json');
    }

    public function test_clear_logs_command_with_exclude_files()
    {
        Config::set('felo-helper.clear_logs', [
            'directories' => [storage_path('logs')],
            'extensions' => ['log', 'json'],
            'exclude' => ['important.log'],
        ]);

        $logDir = storage_path('logs');

        if (! is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }

        file_put_contents($logDir.'/important.log', 'important content');
        file_put_contents($logDir.'/normal.log', 'normal content');

        $this->assertFileExists($logDir.'/important.log');
        $this->assertFileExists($logDir.'/normal.log');

        $this->artisan('felo:clear-logs')
            ->assertExitCode(0)
            ->expectsOutput('Log files cleared successfully.');

        $this->assertFileExists($logDir.'/important.log');
        $this->assertFileDoesNotExist($logDir.'/normal.log');

        @unlink($logDir.'/important.log');
    }

    public function test_clear_logs_command_with_custom_extensions()
    {
        Config::set('felo-helper.clear_logs', [
            'directories' => [storage_path('logs')],
            'extensions' => ['log'],
            'exclude' => [],
        ]);

        $logDir = storage_path('logs');

        if (! is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }

        file_put_contents($logDir.'/test.log', 'log content');
        file_put_contents($logDir.'/test.json', 'json content');

        $this->assertFileExists($logDir.'/test.log');
        $this->assertFileExists($logDir.'/test.json');

        $this->artisan('felo:clear-logs')
            ->assertExitCode(0)
            ->expectsOutput('Log files cleared successfully.');

        $this->assertFileDoesNotExist($logDir.'/test.log');
        $this->assertFileExists($logDir.'/test.json');

        @unlink($logDir.'/test.json');
    }
}
