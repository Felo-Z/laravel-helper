<?php

use Carbon\Carbon;

/**
 * @File Desc:
 * @File Name: helper.php
 *
 * @Created By: zhanglongfei
 * @Created At: 2026/1/24 19:55
 */

/**
 * 清理日志文件
 *
 * 根据配置清理指定目录下的日志文件，支持自定义目录、文件扩展名和排除文件
 *
 * @return void
 */
function clear_logs()
{
    $config = config('felo-helper.clear_logs', [
        'directories' => [storage_path('logs')],
        'extensions' => ['log', 'json'],
        'exclude' => [],
        'recursive' => true,
    ]);

    $directories = $config['directories'] ?? [storage_path('logs')];
    $extensions = $config['extensions'] ?? ['log', 'json'];
    $exclude = $config['exclude'] ?? [];
    $recursive = $config['recursive'] ?? true;

    if (is_string($directories)) {
        $directories = explode(',', $directories);
    }

    if (is_string($extensions)) {
        $extensions = explode(',', $extensions);
        $extensions = array_map('trim', $extensions);
    }

    if (is_string($exclude)) {
        $exclude = explode(',', $exclude);
        $exclude = array_map('trim', $exclude);
    }

    foreach ($directories as $directory) {
        $directory = trim($directory);

        if (! is_dir($directory)) {
            continue;
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS),
            $recursive ? RecursiveIteratorIterator::CHILD_FIRST : RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $extension = $file->getExtension();
                $fileName = $file->getFilename();

                if (! in_array($extension, $extensions)) {
                    continue;
                }

                if (in_array($fileName, $exclude)) {
                    continue;
                }

                @unlink($file->getPathname());
            }
        }
    }
}

/**
 * 清理缓存
 *
 * 清理 Laravel 缓存和 Redis 缓存，支持配置是否清理 Laravel 缓存以及指定 Redis 连接
 *
 * @return void
 */
function clear_cache()
{
    $config = config('felo-helper.clear_cache', [
        'clear_laravel_cache' => true,
        'redis_connections' => 'default',
    ]);

    $clearLaravelCache = $config['clear_laravel_cache'] ?? true;
    $redisConnections = $config['redis_connections'] ?? 'default';

    if ($clearLaravelCache) {
        \Illuminate\Support\Facades\Cache::flush();
    }

    if (is_string($redisConnections)) {
        $redisConnections = explode(',', $redisConnections);
        $redisConnections = array_map('trim', $redisConnections);
    }

    foreach ($redisConnections as $connection) {
        $redis = \Illuminate\Support\Facades\Redis::connection($connection);

        $redis->flushDB();
    }
}

if (! function_exists('das')) {
    function das(...$vars): void
    {
        $fileName = Carbon::now('Asia/Shanghai')->format('md-Hi-s-v').'.json';
        $filePath = storage_path('logs').'/'.$fileName;
        if (count($vars) == 1) {
            $vars = is_array($vars[0]) ? $vars[0] : [$vars[0]];
        }
        $jsonData = json_encode($vars, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

        file_put_contents($filePath, $jsonData);
        dd($vars);
    }
}
if (! function_exists('las')) {
    function las(...$vars): void
    {
        $fileName = Carbon::now('Asia/Shanghai')->format('Hi-s-v').'.json';
        $filePath = storage_path('logs').'/'.$fileName;
        if (count($vars) == 1) {
            $vars = is_array($vars[0]) ? $vars[0] : [$vars[0]];
        }
        $jsonData = json_encode($vars, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

        file_put_contents($filePath, $jsonData);
    }
}

if (! function_exists('zas')) {
    function zas($fileName = null, ...$vars): void
    {
        if (! filled($fileName)) {
            $fileName = Carbon::now('Asia/Shanghai')->format('Hi-s-v').'.json';
        } else {
            $fileName .= '.json';
        }
        $filePath = storage_path('logs').'/'.$fileName;
        if (count($vars) == 1) {
            $vars = is_array($vars[0]) ? $vars[0] : [$vars[0]];
        }
        $jsonData = json_encode($vars, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

        file_put_contents($filePath, $jsonData);
    }
}
if (!function_exists('now_tz_bj')) {
    /**
     * @Desc: 北京时间
     * @Created By: zhanglongfei
     * @Created At: 2026/2/12 17:43
     * @return Carbon
     */
    function now_tz_bj(): Carbon
    {
        return  Carbon::now()->timezone('Asia/Shanghai');
    }
}
if (!function_exists('format_bytes')) {
    function format_bytes($bytes): string
    {
        $bytes = (int) $bytes;
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow   = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow   = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));

        return round($bytes, 2) . ' ' . $units[$pow];
    }
}

if (!function_exists('format_duration')) {
    function format_duration($ms): string
    {
        $ms = (int) $ms;
        if ($ms <= 0) {
            return 'N/A';
        }

        if ($ms < 1000) {
            return $ms . ' ms';
        }

        $seconds = $ms / 1000;
        if ($seconds < 60) {
            return number_format($seconds, 2) . ' s';
        }

        $minutes = intdiv($seconds, 60);
        $remain  = $seconds % 60;

        if ($remain === 0) {
            return $minutes . ' min';
        }

        return sprintf('%d min %d s', $minutes, $remain);
    }
}