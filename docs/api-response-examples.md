# API 响应接入示例

本文档提供业务项目中的典型接入方式，建议结合 `api-response.md` 一起阅读。

## 1. Controller 基础示例

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function show(int $id)
    {
        $user = User::query()->find($id);
        if (! $user) {
            return ap()->notFound('用户不存在');
        }

        return ap()->ok($user, '查询成功');
    }

    public function store(Request $request)
    {
        $user = User::query()->create($request->only(['name', 'email']));

        return ap()->created($user, '创建成功', route('api.users.show', ['id' => $user->id]));
    }
}
```

## 2. FormRequest 校验失败示例

如果启用了 `render_using`，校验失败会由 `ValidationExceptionPipe` 自动转换为统一结构；  
你只需要正常使用 FormRequest：

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:50'],
            'email' => ['required', 'email', 'unique:users,email'],
        ];
    }
}
```

控制器中：

```php
public function store(CreateUserRequest $request)
{
    $user = User::query()->create($request->validated());
    return ap()->created($user, '创建成功');
}
```

## 3. 业务异常（领域异常）示例

### 3.1 定义业务异常

```php
<?php

namespace App\Exceptions;

use RuntimeException;

class BizException extends RuntimeException
{
    public function __construct(
        string $message = '业务异常',
        protected int $bizCode = 422001,
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

### 3.2 定义异常 pipe

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

### 3.3 注册到配置

```php
// config/felo-helper.php
'api_response' => [
    // ...
    'exception_pipes' => [
        \FeloZ\LaravelHelper\Support\ExceptionPipes\AuthenticationExceptionPipe::class,
        \FeloZ\LaravelHelper\Support\ExceptionPipes\HttpExceptionPipe::class,
        \FeloZ\LaravelHelper\Support\ExceptionPipes\ValidationExceptionPipe::class,
        \App\Support\ApiResponse\ExceptionPipes\BizExceptionPipe::class,
    ],
],
```

## 4. Service 类中抛出异常 + 自动统一输出

```php
<?php

namespace App\Services;

use App\Exceptions\BizException;
use App\Models\Order;

class OrderService
{
    public function cancel(int $orderId): void
    {
        $order = Order::query()->find($orderId);
        if (! $order) {
            throw new BizException('订单不存在', 404001);
        }

        if ($order->status === 'paid') {
            throw new BizException('已支付订单不可取消', 422002, ['status' => $order->status]);
        }

        $order->update(['status' => 'canceled']);
    }
}
```

对应控制器：

```php
public function cancel(int $id, OrderService $service)
{
    $service->cancel($id);
    return ap()->ok(null, '取消成功');
}
```

## 5. Debug 场景示例

本地调试可以主动输出调试负载：

```php
return ap()->debug(
    ['sql' => $query, 'bindings' => $bindings],
    '调试信息',
    500
);
```

注意：

- `app.debug=true` 时会返回调试详情
- 生产环境建议保持 `hide_error_when_not_debug=true`

## 6. Facade 风格示例

```php
use FeloZ\LaravelHelper\Facades\Ap;

return Ap::tooManyRequests('请求过于频繁');
```

## 7. 推荐目录结构（可选）

```text
app/
├── Exceptions/
│   └── BizException.php
├── Http/
│   ├── Controllers/Api/
│   └── Requests/
└── Support/
    └── ApiResponse/
        ├── Pipes/
        └── ExceptionPipes/
```

## 8. 常见问题

### Q1: 为什么有时返回的不是统一 JSON？

检查：

- `config('felo-helper.api_response.enable_render_using')` 是否为 `true`
- 请求是否满足 `expectsJson()` 或命中 `render_api_paths`
- 是否发布了最新配置文件并清理配置缓存（`php artisan config:clear`）

### Q2: 如何对不同模块使用不同错误码？

建议使用“业务异常 + 自定义 exception pipe”的方式，根据异常类型映射到不同 `code/message/error`。
