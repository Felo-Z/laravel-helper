<?php

namespace Tests\Feature;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class SqlLoggerDisabledFeatureTest extends TestCase
{
    protected string $sqlLogDirectory;

    protected function getEnvironmentSetUp($app): void
    {
        parent::getEnvironmentSetUp($app);

        $app['config']->set('felo-helper.sql_logger.enabled', false);
        $app['config']->set('felo-helper.sql_logger.directory', sys_get_temp_dir().'/felo-helper-sql-logger-disabled');
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->sqlLogDirectory = config('felo-helper.sql_logger.directory');
        $this->deleteDirectory($this->sqlLogDirectory);

        Route::get('/api/__sql_logger_disabled__', static function () {
            DB::select('select ? as value', ['disabled']);

            return response()->json(['ok' => true]);
        });
    }

    protected function tearDown(): void
    {
        $this->deleteDirectory($this->sqlLogDirectory);

        parent::tearDown();
    }

    public function test_disabled_logger_does_not_create_sql_log_file(): void
    {
        $this->getJson('/api/__sql_logger_disabled__')->assertOk();

        $path = $this->sqlLogDirectory.'/'.Carbon::now()->format('Y-m-d').'-all.sql';

        $this->assertFileDoesNotExist($path);
    }

    protected function deleteDirectory(string $directory): void
    {
        if (! is_dir($directory)) {
            return;
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($iterator as $item) {
            if ($item->isDir()) {
                @rmdir($item->getPathname());
            } else {
                @unlink($item->getPathname());
            }
        }

        @rmdir($directory);
    }
}
