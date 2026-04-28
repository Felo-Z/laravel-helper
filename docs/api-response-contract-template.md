# API 响应约定模板（前后端协作）

本文档用于团队约定统一的 API 响应协议，可直接复制到业务项目中调整。

## 1. 统一响应结构

```json
{
  "status": true,
  "code": 200,
  "message": "OK",
  "data": {},
  "error": {}
}
```

字段定义：

- `status`：业务成功标记（`true/false`）
- `code`：业务码或 HTTP 码（建议与 HTTP 语义对齐）
- `message`：面向客户端的简要提示
- `data`：成功结果主体
- `error`：失败详情（生产环境可隐藏）

## 2. 状态码使用约定

推荐规则：

- 2xx：成功
- 4xx：客户端参数/权限/资源问题
- 5xx：服务端异常

### 2.1 成功类推荐

- `200`：查询/更新成功
- `201`：创建成功
- `202`：异步任务已受理
- `204`：无内容成功（如删除）

### 2.2 错误类推荐

- `400`：请求参数非法
- `401`：未认证
- `403`：无权限
- `404`：资源不存在
- `409`：状态冲突
- `422`：业务校验失败
- `429`：限流
- `500`：系统异常

## 3. 业务码规范（可选）

当你需要比 HTTP 更细粒度的业务语义，建议在 `code` 中使用业务码段：

- 通用：`1xxxxx`
- 用户域：`2xxxxx`
- 订单域：`3xxxxx`
- 支付域：`4xxxxx`

示例：

- `200001`：用户已存在
- `300102`：订单状态不可流转
- `400301`：支付渠道不可用

> 建议：HTTP 状态放到响应状态码中，业务码放在 `code` 字段；若未使用业务码，可直接复用 HTTP 码。

## 4. 错误对象约定

推荐 `error` 结构：

```json
{
  "type": "validation_error",
  "details": {
    "email": [
      "邮箱格式不正确"
    ]
  },
  "trace_id": "trace_abc123"
}
```

字段建议：

- `type`：错误类型（如 `validation_error`、`biz_error`、`system_error`）
- `details`：字段级错误或业务细节
- `trace_id`：链路追踪标识，便于排查

## 5. 成功响应示例

### 5.1 列表查询

```json
{
  "status": true,
  "code": 200,
  "message": "OK",
  "data": {
    "list": [
      { "id": 1, "name": "Alice" },
      { "id": 2, "name": "Bob" }
    ],
    "total": 2
  }
}
```

### 5.2 创建成功

```json
{
  "status": true,
  "code": 201,
  "message": "Created",
  "data": {
    "id": 1001
  }
}
```

## 6. 失败响应示例

### 6.1 参数校验失败（422）

```json
{
  "status": false,
  "code": 422,
  "message": "邮箱格式不正确",
  "error": {
    "type": "validation_error",
    "details": {
      "email": [
        "邮箱格式不正确"
      ]
    }
  }
}
```

### 6.2 业务冲突（409）

```json
{
  "status": false,
  "code": 409,
  "message": "订单状态不可变更",
  "error": {
    "type": "biz_error",
    "details": {
      "order_status": "paid"
    }
  }
}
```

### 6.3 系统异常（500）

```json
{
  "status": false,
  "code": 500,
  "message": "Internal Server Error"
}
```

## 7. 前后端协作建议

- 前端渲染提示优先使用 `message`。
- 前端先根据 `status` 判断成功/失败；当 `status=false` 时，再根据 `code` 做分支处理（如登录态失效、限流重试）。
- `error.details` 仅用于表单/调试，不建议直接展示完整技术信息。
- 生产环境不要暴露堆栈，建议配置 `hide_error_when_not_debug=true`。
- 所有 API 保持同一结构，避免每个接口自定义返回格式。

## 8. Laravel Helper 对应实现建议

建议控制器优先使用：

- 成功：`ap()->ok()`、`ap()->created()`、`ap()->accepted()`
- 失败：`ap()->badRequest()`、`ap()->notFound()`、`ap()->unprocessableEntity()`、`ap()->tooManyRequests()`
- 异常：抛异常并交给 `render_using` 自动接管

如需业务码映射，建议通过自定义 `exception_pipes` 统一处理。
