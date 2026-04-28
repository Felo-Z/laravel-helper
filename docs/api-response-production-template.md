# API 响应生产建议配置模板

本文档提供前后端分离项目（含 `api` + `admin` 接口）推荐的生产配置。

## 1. 适用场景

- 前后端分离
- `api/*` 与 `admin/*` 都是 JSON 接口
- 希望异常自动统一为 API 响应

## 2. 推荐配置

```php
// config/felo-helper.php
return [
    'api_response' => [
        // 开启异常自动接管
        'enable_render_using' => true,

        // 默认渲染策略（根据请求判断是否接管）
        'render_using' => \FeloZ\LaravelHelper\Support\RenderUsings\ShouldReturnJsonRenderUsing::class,

        // 同时接管 api 与 admin（前后端分离场景）
        'render_api_paths' => [
            'api/*',
            'admin/*',
        ],

        // 推荐 smart：业务码失败默认映射为 HTTP 400
        'status_code_strategy' => env('FELO_HELPER_API_STATUS_CODE_STRATEGY', 'smart'),

        // 生产环境隐藏 error 细节
        'hide_error_when_not_debug' => true,

        // 默认响应管道
        'pipes' => [
            \FeloZ\LaravelHelper\Support\Pipes\MessagePipe::class,
            \FeloZ\LaravelHelper\Support\Pipes\ErrorPipe::class,
            \FeloZ\LaravelHelper\Support\Pipes\StatusCodePipe::class,
        ],

        // 默认异常管道
        'exception_pipes' => [
            \FeloZ\LaravelHelper\Support\ExceptionPipes\AuthenticationExceptionPipe::class,
            \FeloZ\LaravelHelper\Support\ExceptionPipes\HttpExceptionPipe::class,
            \FeloZ\LaravelHelper\Support\ExceptionPipes\ValidationExceptionPipe::class,
        ],
    ],
];
```

如果你的后台 `admin` 同时存在 HTML 页面（非纯接口），建议改为更精细的路径，例如：

```php
'render_api_paths' => [
    'api/*',
    'admin/api/*',
],
```

## 3. 环境变量建议

```env
APP_DEBUG=false
FELO_HELPER_API_ENABLE_RENDER_USING=true
FELO_HELPER_API_STATUS_CODE_STRATEGY=smart
FELO_HELPER_API_HIDE_ERROR=true
```

## 4. 前端配合建议

- 请求统一带 `Accept: application/json`
- 解析统一规则：先看 `status`，失败再按 `code` 分支
- `401` 统一处理登录态
- `422` 读取 `error.details` 渲染表单错误

## 5. 排错清单

若异常未自动转 JSON，优先检查：

1. `enable_render_using` 是否为 `true`
2. 路径是否命中 `render_api_paths`
3. 请求头是否包含 `Accept: application/json`
4. 是否执行了 `php artisan config:clear`
