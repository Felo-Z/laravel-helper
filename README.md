# Laravel Helper

Laravel 辅助工具包，提供 SQL 日志、日志清理和缓存清理功能。

> API 响应功能已拆分为独立包 [`felo-z/laravel-api-response`](../laravel-api-response/README.md)。

## 要求

- PHP >= 8.4
- Laravel `^13.0`

## 安装

```shell
composer require felo-z/laravel-helper -vvv
```

如需统一 API 响应，请额外安装：

```shell
composer require felo-z/laravel-api-response
```

### 发布配置文件

```bash
php artisan vendor:publish --provider="FeloZ\LaravelHelper\HelperServiceProvider"
```

## 使用

### 辅助函数

#### 清理日志文件

```php
clear_logs();
```

此函数会根据配置清理指定目录下的日志文件。

#### 清理缓存

```php
clear_cache();
```

此函数会清理 Laravel 缓存和 Redis 缓存。

### SQL 日志

SQL 日志模块会在包启动时自动监听 `DB::listen()`，不提供额外 helper / facade API。

支持能力包括：

- 全量 SQL 与慢查询分流
- HTTP / Console / Queue Job 来源识别
- 绑定值替换、请求方法过滤、连接过滤
- 按执行作用域分组输出

最小配置示例：

```php
'sql_logger' => [
    'enabled' => env('FELO_HELPER_SQL_LOGGER_ENABLED', false),
]
```

详细配置、占位符、环境变量与生产建议见：[`docs/sql-logger.md`](docs/sql-logger.md)

### Artisan 命令

#### 清理日志文件

```bash
php artisan felo:clear-logs
```

#### 清理缓存

```bash
php artisan felo:clear-cache
```

### 配置说明

发布配置文件后，可以在 `config/felo-helper.php` 中进行配置：

```php
return [
    'clear_logs' => [
        'directories' => env('FELO_HELPER_LOG_DIRECTORIES', [storage_path('logs')]),
        'extensions' => env('FELO_HELPER_LOG_EXTENSIONS', 'log,json'),
        'exclude' => env('FELO_HELPER_LOG_EXCLUDE', 'laravel.log'),
    ],
    'clear_cache' => [
        'clear_laravel_cache' => env('FELO_HELPER_CLEAR_LARAVEL_CACHE', true),
        'redis_connections' => env('FELO_HELPER_REDIS_CONNECTIONS', 'default'),
    ],
    'sql_logger' => [
        'enabled' => env('FELO_HELPER_SQL_LOGGER_ENABLED', false),
        'directory' => env('FELO_HELPER_SQL_LOGGER_DIRECTORY', storage_path('logs/sql')),
        'replace_bindings' => env('FELO_HELPER_SQL_LOGGER_REPLACE_BINDINGS', true),
    ],
];
```

SQL 日志详细文档见：[`docs/sql-logger.md`](docs/sql-logger.md)

### 环境变量

```env
FELO_HELPER_LOG_DIRECTORIES=/path/to/logs1,/path/to/logs2
FELO_HELPER_LOG_EXTENSIONS=log,json,txt
FELO_HELPER_LOG_EXCLUDE=laravel.log,important.log
FELO_HELPER_CLEAR_LARAVEL_CACHE=true
FELO_HELPER_REDIS_CONNECTIONS=default,cache
FELO_HELPER_SQL_LOGGER_ENABLED=false
FELO_HELPER_SQL_LOGGER_DIRECTORY=/path/to/storage/logs/sql
FELO_HELPER_SQL_LOGGER_REPLACE_BINDINGS=true
```

更多 SQL 日志环境变量见：[`docs/sql-logger.md`](docs/sql-logger.md)

## 开发

```bash
composer run fix-style
composer run phpstan
composer run test
```

## License

MIT
