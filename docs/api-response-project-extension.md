# API 响应项目扩展指南

当不同项目对 `code` 的语义不一致时，不建议修改包内 `ApiResponseContract`。  
推荐使用“项目侧扩展”方案：业务码常量 + 业务异常 + 自定义异常 pipe + 宏方法。

## 1. 业务码常量（项目内维护）

```php
<?php

namespace App\Support;

class ApiCode
{
    public const OK = 200;
    public const USER_NOT_FOUND = 200404;
    public const ORDER_STATUS_INVALID = 300422;
}
```

业务代码中：

```php
return ap()->failed('用户不存在', \App\Support\ApiCode::USER_NOT_FOUND);
```

如果你希望先用一套通用常量，包内已内置（兼容入口）：

```php
use FeloZ\LaravelHelper\Support\ApiCode;

return ap()->failed('参数错误', ApiCode::BIZ_VALIDATION_ERROR);
// 或使用 HTTP 映射常量
return ap()->failed('未登录', ApiCode::HTTP_UNAUTHORIZED);
```

内置常量包括：

- 业务码：`BIZ_OK`、`BIZ_FAILED`、`BIZ_VALIDATION_ERROR`、`BIZ_UNAUTHORIZED`、`BIZ_FORBIDDEN`、`BIZ_NOT_FOUND`、`BIZ_CONFLICT`、`BIZ_TOO_MANY_REQUESTS`、`BIZ_SYSTEM_ERROR`
- HTTP 映射：`HTTP_OK`、`HTTP_CREATED`、`HTTP_ACCEPTED`、`HTTP_NO_CONTENT`、`HTTP_BAD_REQUEST`、`HTTP_UNAUTHORIZED`、`HTTP_FORBIDDEN`、`HTTP_NOT_FOUND`、`HTTP_CONFLICT`、`HTTP_UNPROCESSABLE_ENTITY`、`HTTP_TOO_MANY_REQUESTS`、`HTTP_INTERNAL_SERVER_ERROR`

另外提供了按模块分组的常量目录（推荐在新项目中使用）：

- `FeloZ\LaravelHelper\Support\ApiCodes\CommonCode`（通用）
- `FeloZ\LaravelHelper\Support\ApiCodes\UserCode`（用户域，2xxxxx）
- `FeloZ\LaravelHelper\Support\ApiCodes\OrderCode`（订单域，3xxxxx）

示例：

```php
use FeloZ\LaravelHelper\Support\ApiCodes\UserCode;
use FeloZ\LaravelHelper\Support\ApiCodes\OrderCode;

return ap()->failed('用户不存在', UserCode::USER_NOT_FOUND);
return ap()->failed('订单状态不可变更', OrderCode::ORDER_STATUS_INVALID);
```

## 2. 业务异常 + Exception Pipe（推荐）

### 2.1 定义异常

```php
<?php

namespace App\Exceptions;

use RuntimeException;

class BizException extends RuntimeException
{
    public function __construct(
        string $message,
        protected int $bizCode,
        protected array $bizError = []
    ) {
        parent::__construct($message);
    }

    public function bizCode(): int
    {
        return $this->bizCode;
    }

    public function bizError(): array
    {
        return $this->bizError;
    }
}
```

### 2.2 定义 Pipe

```php
<?php

namespace App\Support\ApiResponse\ExceptionPipes;

use App\Exceptions\BizException;
use Closure;
use Throwable;

class BizExceptionPipe
{
    public function handle(Throwable $throwable, Closure $next): array
    {
        $structure = $next($throwable);

        if (! $throwable instanceof BizException) {
            return $structure;
        }

        return [
            'code' => $throwable->bizCode(),
            'message' => $throwable->getMessage(),
            'error' => $throwable->bizError(),
        ] + $structure;
    }
}
```

### 2.3 注册 Pipe

```php
// config/felo-helper.php
'api_response' => [
    'exception_pipes' => [
        \FeloZ\LaravelHelper\Support\ExceptionPipes\AuthenticationExceptionPipe::class,
        \FeloZ\LaravelHelper\Support\ExceptionPipes\HttpExceptionPipe::class,
        \FeloZ\LaravelHelper\Support\ExceptionPipes\ValidationExceptionPipe::class,
        \App\Support\ApiResponse\ExceptionPipes\BizExceptionPipe::class,
    ],
],
```

## 2.4 大量业务异常的“映射表”写法（推荐在项目里收敛）

当业务异常类型很多时，不建议为每一种异常都写一个 pipe（会导致 `exception_pipes` 过长、顺序难维护、容易互相覆盖）。

推荐做法是：

- 业务层只抛“统一的业务异常基类”（或少量几类）
- 通过 **一个** exception pipe 做“异常类型/错误码映射表”，集中管理

### A) 定义业务异常（带 code + context）

```php
<?php

namespace App\Exceptions;

use RuntimeException;

class BizException extends RuntimeException
{
    public function __construct(
        string $message,
        protected int $bizCode,
        protected array $context = []
    ) {
        parent::__construct($message);
    }

    public function bizCode(): int
    {
        return $this->bizCode;
    }

    public function context(): array
    {
        return $this->context;
    }
}
```

### B) 定义映射表（集中管理）

```php
<?php

namespace App\Support\ApiResponse;

use FeloZ\LaravelHelper\Support\ApiCodes\CommonCode;
use FeloZ\LaravelHelper\Support\ApiCodes\OrderCode;
use FeloZ\LaravelHelper\Support\ApiCodes\UserCode;

class BizCodeMap
{
    public const MAP = [
        // User domain
        'user.not_found' => UserCode::USER_NOT_FOUND,
        'user.already_exists' => UserCode::USER_ALREADY_EXISTS,

        // Order domain
        'order.not_found' => OrderCode::ORDER_NOT_FOUND,
        'order.status_invalid' => OrderCode::ORDER_STATUS_INVALID,

        // Common
        'common.validation' => CommonCode::VALIDATION_ERROR,
        'common.failed' => CommonCode::FAILED,
    ];
}
```

### C) 一个 pipe 处理所有业务异常（含映射表）

```php
<?php

namespace App\Support\ApiResponse\ExceptionPipes;

use App\Exceptions\BizException;
use App\Support\ApiResponse\BizCodeMap;
use Closure;
use Throwable;

class BizExceptionMapPipe
{
    public function handle(Throwable $throwable, Closure $next): array
    {
        $structure = $next($throwable);
        if (! $throwable instanceof BizException) {
            return $structure;
        }

        // 约定：context 中可以带一个 key，用于映射业务码
        $key = (string) ($throwable->context()['key'] ?? '');
        $mappedCode = $key !== '' ? (BizCodeMap::MAP[$key] ?? null) : null;

        return [
            'code' => $mappedCode ?? $throwable->bizCode(),
            'message' => $throwable->getMessage(),
            'error' => $throwable->context(),
        ] + $structure;
    }
}
```

### D) 注册到 `exception_pipes`

```php
'api_response' => [
    'exception_pipes' => [
        \FeloZ\LaravelHelper\Support\ExceptionPipes\AuthenticationExceptionPipe::class,
        \FeloZ\LaravelHelper\Support\ExceptionPipes\HttpExceptionPipe::class,
        \FeloZ\LaravelHelper\Support\ExceptionPipes\ValidationExceptionPipe::class,

        // 收敛所有业务异常映射（建议放在后面，避免覆盖 Http/Validation）
        \App\Support\ApiResponse\ExceptionPipes\BizExceptionMapPipe::class,
    ],
],
```

### E) 业务代码中使用

```php
throw new \App\Exceptions\BizException(
    '用户不存在',
    200404, // 可作为兜底
    ['key' => 'user.not_found', 'user_id' => $id]
);
```

> 提示：如果你启用了 `render_using`，你只需要抛异常，不需要手动 `return ap()->exception(...)`。

## 3. 宏方法扩展（无需改接口）

`ApiResponse` 已支持 Macro，你可以在项目启动时注册自定义语义方法。

```php
<?php

namespace App\Providers;

use FeloZ\LaravelHelper\Support\ApiResponse;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        ApiResponse::macro('userNotFound', function () {
            /** @var ApiResponse $this */
            return $this->failed('用户不存在', 200404, ['type' => 'biz_error']);
        });
    }
}
```

业务中直接调用：

```php
return ap()->userNotFound();
```

## 4. 什么时候需要替换实现

如果项目希望完全自定义 `ap()` 行为（结构、字段、状态码策略都不同），可在项目中重绑契约：

```php
$this->app->singleton(
    \FeloZ\LaravelHelper\Support\Contracts\ApiResponseContract::class,
    \App\Support\ProjectApiResponse::class
);
```

这是“重度定制”方案，适合多业务线差异很大的场景。

## 5. 建议结论

- 包层保持通用：`ok/failed/exception/json` + pipes
- 项目层承接差异：业务码常量、业务异常、宏方法
- 避免在包里堆积项目特有 code 语义，降低升级成本
