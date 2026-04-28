<?php

namespace Tests\Feature;

use Carbon\Carbon;
use FeloZ\LaravelHelper\Support\SqlLogger\QueryLogger;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class SqlLoggerFeatureTest extends TestCase
{
    protected string $sqlLogDirectory;

    protected function getEnvironmentSetUp($app): void
    {
        parent::getEnvironmentSetUp($app);

        $app['config']->set('felo-helper.sql_logger.enabled', true);
        $app['config']->set('felo-helper.sql_logger.directory', sys_get_temp_dir().'/felo-helper-sql-logger');
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->sqlLogDirectory = config('felo-helper.sql_logger.directory');
        $this->deleteDirectory($this->sqlLogDirectory);

        Route::get('/api/__sql_logger__', static function () {
            DB::select('select ? as name', ["O'Reilly"]);

            return response()->json(['ok' => true]);
        });

        Route::post('/api/__sql_logger_post__', static function () {
            DB::select('select ? as action', ['posted']);

            return response()->json(['ok' => true]);
        });

        Route::get('/api/__sql_logger_multi__', static function () {
            DB::select('select ? as first_value', ['first']);
            DB::select('select ? as second_value', ['second']);

            return response()->json(['ok' => true]);
        });
    }

    protected function tearDown(): void
    {
        $this->deleteDirectory($this->sqlLogDirectory);

        parent::tearDown();
    }

    public function test_http_query_is_logged_to_all_channel(): void
    {
        $this->getJson('/api/__sql_logger__')->assertOk();

        $path = $this->sqlLogDirectory.'/'.Carbon::now()->format('Y-m-d').'-all.sql';

        $this->assertFileExists($path);

        $content = file_get_contents($path);

        $this->assertStringContainsString('[all]', $content);
        $this->assertStringContainsString('[testing]', $content);
        $this->assertStringContainsString("select 'O''Reilly' as name", $content);
        $this->assertStringContainsString('http: GET http://localhost/api/__sql_logger__', $content);
    }

    public function test_slow_query_is_logged_to_slow_channel(): void
    {
        $event = new QueryExecuted(
            'select ? as slow_value',
            ['slow'],
            150.0,
            DB::connection()
        );

        $this->app->make(QueryLogger::class)->handle($event);

        $path = $this->sqlLogDirectory.'/'.Carbon::now()->format('Y-m-d').'-slow.sql';

        $this->assertFileExists($path);
        $this->assertStringContainsString('[slow]', (string) file_get_contents($path));
        $this->assertStringContainsString("select 'slow' as slow_value", (string) file_get_contents($path));
    }

    public function test_pattern_filter_can_skip_query_logging(): void
    {
        config(['felo-helper.sql_logger.channels.all.pattern' => '/^insert/i']);

        DB::select('select ? as answer', [123]);

        $path = $this->sqlLogDirectory.'/'.Carbon::now()->format('Y-m-d').'-all.sql';

        $this->assertFileDoesNotExist($path);
    }

    public function test_exclude_patterns_can_skip_query_logging(): void
    {
        config(['felo-helper.sql_logger.exclude_patterns' => ['/sqlite_master/i']]);

        DB::select('select * from sqlite_master');

        $path = $this->sqlLogDirectory.'/'.Carbon::now()->format('Y-m-d').'-all.sql';

        $this->assertFileDoesNotExist($path);
    }

    public function test_ignored_connection_can_skip_query_logging(): void
    {
        config(['felo-helper.sql_logger.ignore_connections' => ['testing']]);

        DB::select('select ? as answer', [123]);

        $path = $this->sqlLogDirectory.'/'.Carbon::now()->format('Y-m-d').'-all.sql';

        $this->assertFileDoesNotExist($path);
    }

    public function test_only_methods_allows_only_configured_http_methods(): void
    {
        config(['felo-helper.sql_logger.only_methods' => ['POST']]);

        $this->getJson('/api/__sql_logger__')->assertOk();

        $path = $this->sqlLogDirectory.'/'.Carbon::now()->format('Y-m-d').'-all.sql';

        $this->assertFileDoesNotExist($path);

        $this->postJson('/api/__sql_logger_post__')->assertOk();

        $this->assertFileExists($path);
        $this->assertStringContainsString("select 'posted' as action", (string) file_get_contents($path));
    }

    public function test_exclude_methods_skips_configured_http_methods(): void
    {
        config(['felo-helper.sql_logger.exclude_methods' => ['GET']]);

        $this->getJson('/api/__sql_logger__')->assertOk();

        $path = $this->sqlLogDirectory.'/'.Carbon::now()->format('Y-m-d').'-all.sql';

        $this->assertFileDoesNotExist($path);

        $this->postJson('/api/__sql_logger_post__')->assertOk();

        $this->assertFileExists($path);
        $this->assertStringContainsString("select 'posted' as action", (string) file_get_contents($path));
    }

    public function test_max_query_length_truncates_logged_sql(): void
    {
        config(['felo-helper.sql_logger.max_query_length' => 20]);

        DB::select('select ? as very_long_column_name', ['12345678901234567890']);

        $path = $this->sqlLogDirectory.'/'.Carbon::now()->format('Y-m-d').'-all.sql';

        $this->assertFileExists($path);
        $this->assertStringContainsString("select '123456789012 ...[truncated]", (string) file_get_contents($path));
    }

    public function test_channel_pattern_uses_untruncated_sql_for_matching(): void
    {
        config([
            'felo-helper.sql_logger.max_query_length' => 20,
            'felo-helper.sql_logger.channels.all.pattern' => '/very_long_column_name$/',
        ]);

        DB::select('select ? as very_long_column_name', ['12345678901234567890']);

        $path = $this->sqlLogDirectory.'/'.Carbon::now()->format('Y-m-d').'-all.sql';

        $this->assertFileExists($path);
    }

    public function test_group_by_scope_writes_single_header_for_same_request(): void
    {
        config(['felo-helper.sql_logger.group_by_scope' => true]);

        $this->getJson('/api/__sql_logger_multi__')->assertOk();

        $path = $this->sqlLogDirectory.'/'.Carbon::now()->format('Y-m-d').'-all.sql';
        $content = (string) file_get_contents($path);

        $this->assertSame(1, substr_count($content, '================ SCOPE START ================'));
        $this->assertStringContainsString('scope: http', $content);
        $this->assertStringContainsString("select 'first' as first_value", $content);
        $this->assertStringContainsString("select 'second' as second_value", $content);
    }

    public function test_group_by_scope_creates_new_header_for_repeated_requests(): void
    {
        config(['felo-helper.sql_logger.group_by_scope' => true]);

        $this->getJson('/api/__sql_logger__')->assertOk();
        $this->getJson('/api/__sql_logger__')->assertOk();

        $path = $this->sqlLogDirectory.'/'.Carbon::now()->format('Y-m-d').'-all.sql';
        $content = (string) file_get_contents($path);

        $this->assertSame(2, substr_count($content, '================ SCOPE START ================'));
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
