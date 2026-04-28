# API 响应升级说明

本文档用于说明近期 API 响应模块的重要变更，帮助已有项目平滑升级。

## 1. 新增 `error()` 别名

- 新增 `ap()->error($message, $code, $error)`  
- 语义等同于 `ap()->failed(...)`

建议：

- 老代码可保持 `failed()` 不动
- 新增业务如果更习惯 `error()` 命名，可直接使用

## 2. 状态码策略新增 `status_code_strategy`

新增配置：

```php
'api_response' => [
    'status_code_strategy' => env('FELO_HELPER_API_STATUS_CODE_STRATEGY', 'smart'),
],
```

### `smart`（默认）

- 当 `code` 不是 HTTP 状态码（如业务码 `200404`）：
  - 成功响应 HTTP -> `200`
  - 失败响应 HTTP -> `400`

适用场景：

- 希望“业务失败”不再被误判为系统异常（500）

### `legacy`

- 保持旧行为：
  - 当 `code` 不是 HTTP 状态码时，失败响应 HTTP -> `500`

适用场景：

- 需要与旧系统完全一致

## 3. `ApiCode` 与 `ApiCodes/*` 的推荐关系

- `FeloZ\LaravelHelper\Support\ApiCode`：兼容入口（平铺常量）
- `FeloZ\LaravelHelper\Support\ApiCodes\*`：推荐新项目使用（分组常量）

推荐：

- 新项目优先使用分组常量（如 `CommonCode`、`UserCode`、`OrderCode`）
- 老项目可继续使用 `ApiCode`，逐步迁移即可

## 4. 升级后建议检查项

1. 前端是否按 `status -> code` 统一判定
2. 是否需要将 `status_code_strategy` 设为 `legacy`
3. 业务码是否迁移到 `ApiCodes/*` 分组目录

## 5. 配置文件升级/发布说明（重要）

如果你**之前已经发布过配置文件**（项目里已有 `config/felo-helper.php`），升级包后新增配置项**不会自动出现在你的配置文件中**。

原因：

- `php artisan vendor:publish` 默认不会覆盖已存在的配置文件

推荐做法：

1. **手动合并新增配置项（推荐）**
   - 对照包内模板：`vendor/felo-z/laravel-helper/src/publish/felo-helper.php`
   - 将新增项拷贝到项目配置：`config/felo-helper.php`

2. **强制覆盖发布（谨慎）**

```bash
php artisan vendor:publish --provider="FeloZ\LaravelHelper\HelperServiceProvider" --force
```

注意：

- `--force` 会覆盖你现有配置，建议先备份或用 diff 工具合并

合并/覆盖后建议执行：

```bash
php artisan config:clear
```
