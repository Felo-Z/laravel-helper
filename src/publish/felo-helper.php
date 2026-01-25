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
];
