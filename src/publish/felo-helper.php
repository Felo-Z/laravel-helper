<?php

/**
 * @File Desc:
 * @File Name: felo-z-helper.php
 *
 * @Created By: zhanglongfei
 * @Created At: 2026/1/24 20:08
 */
return [
    'clear_logs' => [
        // 日志文件目录，支持多个目录，逗号分隔或数组形式
        'directories' => env('FELO_HELPER_LOG_DIRECTORIES', [storage_path('logs')]),
        // 需要清理的日志文件扩展名，支持多个扩展名，逗号分隔或数组形式
        'extensions' => env('FELO_HELPER_LOG_EXTENSIONS', 'log,sql,json'),
        // 排除的文件名，不会被删除的文件，逗号分隔或数组形式
        'exclude' => env('FELO_HELPER_LOG_EXCLUDE', 'laravel.log'),
    ],
    'clear_cache' => [
        // 是否清理 Laravel 缓存
        'clear_laravel_cache' => env('FELO_HELPER_CLEAR_LARAVEL_CACHE', true),
        // Redis 连接名称，支持多个连接，逗号分隔或数组形式
        'redis_connections' => env('FELO_HELPER_REDIS_CONNECTIONS', 'default'),
    ],
    'sql_logger' => [
        'enabled' => env('FELO_HELPER_SQL_LOGGER_ENABLED', false),
        'directory' => env('FELO_HELPER_SQL_LOGGER_DIRECTORY', storage_path('logs/sql')),
        'replace_bindings' => env('FELO_HELPER_SQL_LOGGER_REPLACE_BINDINGS', true),
        'collapse_whitespace' => env('FELO_HELPER_SQL_LOGGER_COLLAPSE_WHITESPACE', true),
        'ignore_connections' => env('FELO_HELPER_SQL_LOGGER_IGNORE_CONNECTIONS', ''),
        'exclude_patterns' => env('FELO_HELPER_SQL_LOGGER_EXCLUDE_PATTERNS', ''),
        'only_methods' => env('FELO_HELPER_SQL_LOGGER_ONLY_METHODS', ''),
        'exclude_methods' => env('FELO_HELPER_SQL_LOGGER_EXCLUDE_METHODS', ''),
        'include_raw_sql' => env('FELO_HELPER_SQL_LOGGER_INCLUDE_RAW_SQL', true),
        'include_bindings' => env('FELO_HELPER_SQL_LOGGER_INCLUDE_BINDINGS', true),
        'max_query_length' => (int) env('FELO_HELPER_SQL_LOGGER_MAX_QUERY_LENGTH', 0),
        'max_binding_length' => (int) env('FELO_HELPER_SQL_LOGGER_MAX_BINDING_LENGTH', 0),
        'group_by_scope' => env('FELO_HELPER_SQL_LOGGER_GROUP_BY_SCOPE', false),
        'scope_header_format' => env(
            'FELO_HELPER_SQL_LOGGER_SCOPE_HEADER_FORMAT',
            "================ SCOPE START ================\n".
            "scope: {scope_type}\n".
            "id: {scope_id}\n".
            "name: {scope_name}\n".
            "time: {scope_started_at}\n".
            "{scope_context}\n"
        ),
        'date_format' => env('FELO_HELPER_SQL_LOGGER_DATE_FORMAT', 'Y-m-d H:i:s.u'),
        'entry_format' => env(
            'FELO_HELPER_SQL_LOGGER_ENTRY_FORMAT',
            "[{datetime}] [{channel}] [{origin}] [{connection}] [{duration}]\n{sql}\n{separator}\n"
        ),
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
    ],
];
