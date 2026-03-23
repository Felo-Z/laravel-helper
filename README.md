# Laravel Helper

Laravel 辅助工具包，提供日志清理和缓存清理功能。

## 要求

- PHP >= 8.2
- Laravel `^12.0|^13.0`

## 安装

```shell
composer require felo-z/laravel-helper -vvv
```

### 发布配置文件

你可以将配置文件发布到项目的 `config/` 目录：

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
        // 日志文件目录，支持多个目录，逗号分隔或数组形式
        'directories' => env('FELO_HELPER_LOG_DIRECTORIES', [storage_path('logs')]),
        // 需要清理的日志文件扩展名，支持多个扩展名，逗号分隔或数组形式
        'extensions' => env('FELO_HELPER_LOG_EXTENSIONS', 'log,json'),
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
```

### 环境变量

你也可以通过环境变量进行配置：

```env
# 日志清理配置
FELO_HELPER_LOG_DIRECTORIES=/path/to/logs1,/path/to/logs2
FELO_HELPER_LOG_EXTENSIONS=log,json,txt
FELO_HELPER_LOG_EXCLUDE=laravel.log,important.log

# 缓存清理配置
FELO_HELPER_CLEAR_LARAVEL_CACHE=true
FELO_HELPER_REDIS_CONNECTIONS=default,cache
```

## 开发

### 代码检查

项目使用以下工具来保证代码质量：

#### Laravel Pint - 代码格式化

```bash
# 检查代码风格（不修改文件）
composer run check-style

# 自动修复代码风格问题
composer run fix-style
```

#### PHPStan - 静态分析

```bash
# 运行 PHPStan 静态分析
composer run phpstan
```

PHPStan 配置文件位于项目根目录的 `phpstan.neon`，分析级别为 5。

#### PHPUnit - 单元测试

```bash
# 运行所有测试
composer run test
```

### 提交代码前检查

在提交代码前，建议运行以下命令：

```bash
# 1. 代码格式化
composer run fix-style

# 2. 静态分析
composer run phpstan

# 3. 运行测试
composer run test
```

## 贡献

欢迎贡献代码！你可以通过以下方式参与：

1. 在 [问题追踪器](https://github.com/felo-z/laravel-helper/issues) 上报告 Bug
2. 在 [问题追踪器](https://github.com/felo-z/laravel-helper/issues) 上回答问题或修复 Bug
3. 贡献新功能或更新文档

_代码贡献过程不需要太正式。你只需要确保遵循 PSR-0、PSR-1 和 PSR-2 编码规范。任何新的代码贡献都必须附带相应的单元测试。_

## License

MIT
