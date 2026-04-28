<?php

namespace Tests\Unit;

use Carbon\CarbonImmutable;
use FeloZ\LaravelHelper\Support\SqlLogger\BindingInterpolator;
use FeloZ\LaravelHelper\Support\SqlLogger\FileNameResolver;
use FeloZ\LaravelHelper\Support\SqlLogger\SqlLogEntry;
use FeloZ\LaravelHelper\Support\SqlLogger\SqlLogFormatter;
use FeloZ\LaravelHelper\Support\SqlLogger\SqlLoggerConfig;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SqlLoggerSupportTest extends TestCase
{
    public function test_binding_interpolator_replaces_positional_bindings(): void
    {
        $interpolator = new BindingInterpolator;
        $event = new QueryExecuted(
            'select ? as name, ? as enabled, ? as created_at, ? as nullable_value',
            ["O'Reilly", true, CarbonImmutable::parse('2026-04-28 12:34:56'), null],
            12.5,
            DB::connection()
        );

        $sql = $interpolator->interpolate($event);

        $this->assertSame(
            "select 'O''Reilly' as name, 1 as enabled, '2026-04-28 12:34:56' as created_at, null as nullable_value",
            $sql
        );
    }

    public function test_binding_interpolator_does_not_replace_placeholders_inside_string_literals(): void
    {
        $interpolator = new BindingInterpolator;
        $event = new QueryExecuted(
            "select '?' as literal_question, ':name' as literal_named, ? as actual_value, :name as named_value",
            ['actual', 'name' => 'demo'],
            8.5,
            DB::connection()
        );

        $sql = $interpolator->interpolate($event);

        $this->assertSame(
            "select '?' as literal_question, ':name' as literal_named, 'actual' as actual_value, 'demo' as named_value",
            $sql
        );
    }

    public function test_file_name_resolver_supports_date_and_entry_tokens(): void
    {
        $entry = new SqlLogEntry(
            sequence: 1,
            connectionName: 'mysql',
            origin: 'http: GET http://localhost/test',
            sourceSql: 'select 1',
            rawSql: 'select 1',
            sql: 'select 1',
            bindings: [],
            durationMs: 10,
            executedAt: CarbonImmutable::parse('2026-04-28 12:34:56')
        );

        $resolver = new FileNameResolver;
        $fileName = $resolver->resolve('{date:Y-m-d}-{channel}-{connection}-{origin}.sql', 'slow', $entry);

        $this->assertSame(
            '2026-04-28-slow-mysql-http-GET-http-localhost-test.sql',
            $fileName
        );
    }

    public function test_formatter_uses_configured_placeholders(): void
    {
        config(['felo-helper.sql_logger.entry_format' => '{channel}|{sequence}|{duration}|{sql}|{bindings}|{separator}']);

        $formatter = new SqlLogFormatter($this->app->make(SqlLoggerConfig::class));
        $entry = new SqlLogEntry(
            sequence: 3,
            connectionName: 'testing',
            origin: 'unknown',
            sourceSql: 'select * from "users"',
            rawSql: 'select * from "users"',
            sql: 'select * from "users"',
            bindings: ['role' => 'admin'],
            durationMs: 15.25,
            executedAt: CarbonImmutable::parse('2026-04-28 12:34:56')
        );

        $formatted = $formatter->format($entry, 'all');

        $this->assertSame(
            'all|3|15.25 ms|select * from "users"|{"role":"admin"}|/*==================================================*/',
            $formatted
        );
    }

    public function test_formatter_can_hide_raw_sql_and_bindings(): void
    {
        config([
            'felo-helper.sql_logger.include_raw_sql' => false,
            'felo-helper.sql_logger.include_bindings' => false,
            'felo-helper.sql_logger.entry_format' => '{sql}|{raw_sql}|{bindings}',
        ]);

        $formatter = new SqlLogFormatter($this->app->make(SqlLoggerConfig::class));
        $entry = new SqlLogEntry(
            sequence: 1,
            connectionName: 'testing',
            origin: 'unknown',
            sourceSql: 'select * from users where id = ?',
            rawSql: 'select * from users where id = ?',
            sql: 'select * from users where id = 1',
            bindings: [1],
            durationMs: 10,
            executedAt: CarbonImmutable::parse('2026-04-28 12:34:56')
        );

        $formatted = $formatter->format($entry, 'all');

        $this->assertSame('select * from users where id = 1||', $formatted);
    }

    public function test_sql_logger_config_returns_null_when_max_query_length_disabled(): void
    {
        config(['felo-helper.sql_logger.max_query_length' => 0]);

        $config = $this->app->make(SqlLoggerConfig::class);

        $this->assertNull($config->maxQueryLength());
    }

    public function test_formatter_can_truncate_bindings_output(): void
    {
        config([
            'felo-helper.sql_logger.max_binding_length' => 20,
            'felo-helper.sql_logger.entry_format' => '{bindings}',
        ]);

        $formatter = new SqlLogFormatter($this->app->make(SqlLoggerConfig::class));
        $entry = new SqlLogEntry(
            sequence: 1,
            connectionName: 'testing',
            origin: 'unknown',
            sourceSql: 'select * from users where payload = ?',
            rawSql: 'select * from users where payload = ?',
            sql: 'select * from users where payload = ?',
            bindings: ['payload' => str_repeat('a', 30)],
            durationMs: 10,
            executedAt: CarbonImmutable::parse('2026-04-28 12:34:56')
        );

        $formatted = $formatter->format($entry, 'all');

        $this->assertSame('{"payload":"aaaaaaaa ...[truncated]', $formatted);
    }

    public function test_sql_logger_config_returns_null_when_max_binding_length_disabled(): void
    {
        config(['felo-helper.sql_logger.max_binding_length' => 0]);

        $config = $this->app->make(SqlLoggerConfig::class);

        $this->assertNull($config->maxBindingLength());
    }
}
