# Laravel Helper

Laravel 辅助工具包，提供 API 响应、日志清理和缓存清理功能。

## 要求

- PHP >= 8.4
- Laravel `^13.0`

## 安装

```shell
composer require felo-z/laravel-helper -vvv
```

### 发布配置文件

你可以将配置文件发布到项目的 `config/` 目录：

```bash
php artisan vendor:publish --provider="FeloZ\LaravelHelper\HelperServiceProvider"
```

如果你已经发布过配置文件，升级后新增配置项不会自动写入现有配置。请参考升级说明：[`docs/api-response-upgrade.md`](docs/api-response-upgrade.md)（配置合并/覆盖发布）。

## 使用

### 辅助函数

#### API 响应助手

```php
ap()->ok(['id' => 1], 'ok');
ap()->success(['id' => 1], 'success');
ap()->message('created', 201, ['id' => 1]);
ap()->failed('bad request', 400, ['field' => 'name']);
ap()->debug(['query' => 'select 1'], 'debug message');
ap()->exception(new \Exception('boom'));
```

也支持 Facade 调用：

```php
\FeloZ\LaravelHelper\Facades\Ap::ok(['id' => 1], 'ok');
```

完整文档请查看：[`docs/api-response.md`](docs/api-response.md)  
接入示例请查看：[`docs/api-response-examples.md`](docs/api-response-examples.md)
前后端约定模板：[`docs/api-response-contract-template.md`](docs/api-response-contract-template.md)
前端精简版：[`docs/api-response-frontend-quick.md`](docs/api-response-frontend-quick.md)
项目扩展指南：[`docs/api-response-project-extension.md`](docs/api-response-project-extension.md)
升级说明：[`docs/api-response-upgrade.md`](docs/api-response-upgrade.md)
性能压测指南：[`docs/api-response-benchmark.md`](docs/api-response-benchmark.md)
生产配置模板：[`docs/api-response-production-template.md`](docs/api-response-production-template.md)

方法场景对照（建议）：

| 方法 | 主要场景 | 说明 |
| --- | --- | --- |
| `ok($data, $message)` | 常规查询成功 | 固定 200 |
| `created($data, $message, $location)` | 创建成功 | 201，可带 `Location` |
| `accepted($data, $message)` | 异步受理 | 202 |
| `success($data, $message, $code)` | 通用成功 | 可自定义成功码 |
| `message($message, $code, $data)` | 文案优先成功 | 参数顺序更偏提示语 |
| `failed($message, $code, $error)` | 通用失败 | 推荐主入口 |
| `error($message, $code, $error)` | 通用失败 | `failed` 别名 |
| `exception($throwable)` | 异常转统一响应 | 走 `exception_pipes` |

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
    'api_response' => [
        // 是否启用异常渲染接管（仅 JSON/API 请求）
        'enable_render_using' => env('FELO_HELPER_API_ENABLE_RENDER_USING', true),
        // 异常渲染策略
        'render_using' => \FeloZ\LaravelHelper\Support\RenderUsings\ShouldReturnJsonRenderUsing::class,
        // 按路径匹配 API 请求
        'render_api_paths' => ['api/*'],
        // 状态码策略：smart 或 legacy
        'status_code_strategy' => env('FELO_HELPER_API_STATUS_CODE_STRATEGY', 'smart'),
        // 非 debug 环境是否隐藏 error 字段
        'hide_error_when_not_debug' => env('FELO_HELPER_API_HIDE_ERROR', true),
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

# API 响应配置
FELO_HELPER_API_HIDE_ERROR=true
FELO_HELPER_API_ENABLE_RENDER_USING=true
FELO_HELPER_API_STATUS_CODE_STRATEGY=smart
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
