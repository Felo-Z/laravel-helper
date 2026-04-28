# API 响应性能压测指南

本文档用于快速评估 `pipes` 对接口性能的影响，并给出可复现的压测步骤。

## 1. 目标

对比以下两种配置下的响应耗时差异：

- **基线组**：关闭 `api_response.pipes`
- **实验组**：开启默认 `MessagePipe + ErrorPipe + StatusCodePipe`

重点观察：

- 平均延迟（avg）
- P95 / P99 延迟
- 吞吐（requests/sec）

## 2. 准备压测路由

在业务项目中临时增加一个轻量路由（不查库）：

```php
use Illuminate\Support\Facades\Route;

Route::get('/api/__bench__/ok', static function () {
    return ap()->ok(['ping' => 'pong'], 'ok');
});
```

> 建议：压测期间关闭 Xdebug，使用与线上一致的 PHP-FPM/Nginx 环境。

## 3. 准备两组配置

### 3.1 基线组（关闭 pipes）

```php
'api_response' => [
    // 仅压测时临时修改
    'pipes' => [],
],
```

### 3.2 实验组（默认 pipes）

```php
'api_response' => [
    'pipes' => [
        \FeloZ\LaravelHelper\Support\Pipes\MessagePipe::class,
        \FeloZ\LaravelHelper\Support\Pipes\ErrorPipe::class,
        \FeloZ\LaravelHelper\Support\Pipes\StatusCodePipe::class,
    ],
],
```

每次切换后执行：

```bash
php artisan config:clear
php artisan route:clear
```

## 4. 压测命令示例

### 使用 `wrk`

```bash
wrk -t4 -c100 -d30s --latency "http://127.0.0.1:8000/api/__bench__/ok"
```

### 使用 `ab`

```bash
ab -n 10000 -c 100 "http://127.0.0.1:8000/api/__bench__/ok"
```

## 5. 结果记录模板

| 组别 | Avg Latency | P95 | P99 | Req/Sec |
| --- | --- | --- | --- | --- |
| 基线组（无 pipes） |  |  |  |  |
| 实验组（默认 pipes） |  |  |  |  |
| 差值（实验-基线） |  |  |  |  |

建议跑 3 轮取平均，减少抖动影响。

## 6. 结果解读建议

- 若差值在单毫秒级且吞吐变化很小：可认为 `pipes` 开销可接受。
- 若差值明显增大：
  1. 检查是否在自定义 pipe 中做了 IO（查库/HTTP 请求）
  2. 检查是否有复杂序列化或大 payload
  3. 逐个 pipe 开关定位具体开销来源

## 7. 生产建议

- 保持 pipe 逻辑“轻量、无 IO、无阻塞”
- 重业务逻辑放在服务层，不放 pipe
- 保留默认 3 个 pipe 通常不会成为瓶颈
