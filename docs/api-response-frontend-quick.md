# API 响应协议（前端精简版）

这是一份给前端同学的快速约定，只保留最常用内容。

## 1. 统一响应格式

```json
{
  "status": true,
  "code": 200,
  "message": "OK",
  "data": {},
  "error": {}
}
```

字段含义：

- `status`：是否成功（`true/false`）
- `code`：状态码（HTTP 或业务码）
- `message`：展示给用户的提示文案
- `data`：成功数据
- `error`：错误详情（生产环境可能省略）

## 2. 常用状态码（建议前端重点处理）

- `200`：成功
- `201`：创建成功
- `400`：请求参数错误
- `401`：未登录/登录失效（建议跳登录）
- `403`：无权限
- `404`：资源不存在
- `422`：表单校验失败（优先读取 `error.details`）
- `429`：请求过于频繁（建议提示后重试）
- `500`：服务异常

## 3. 常见响应示例

### 3.1 成功

```json
{
  "status": true,
  "code": 200,
  "message": "查询成功",
  "data": {
    "id": 1,
    "name": "Alice"
  }
}
```

### 3.2 校验失败（422）

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

### 3.3 未登录（401）

```json
{
  "status": false,
  "code": 401,
  "message": "Unauthenticated."
}
```

### 3.4 系统异常（500）

```json
{
  "status": false,
  "code": 500,
  "message": "Internal Server Error"
}
```

## 4. 前端处理建议（最小版）

- 先判断 `status`；失败时按 `code` 分支处理。
- `401`：清理登录态并跳转登录页。
- `422`：读取 `error.details` 渲染字段错误。
- `429`：提示“请求频繁”，可做延迟重试。
- 其他失败：优先展示 `message`。

## 5. 统一判定规则（建议固定）

推荐统一规则（适用于 `status_code_strategy=smart` 与 `legacy`）：

1. 先看 `status`，作为成功/失败的唯一入口判断。  
2. 当 `status=false` 时，再按 `code` 做业务分支（如 401、422、429）。  
3. 前端不要用 HTTP 状态码直接判业务成功失败，HTTP 仅作网络层辅助信息。

示例伪代码：

```ts
if (resp.status === true) {
  // 成功
  return resp.data;
}

switch (resp.code) {
  case 401:
    // 跳转登录
    break;
  case 422:
    // 表单校验提示
    break;
  case 429:
    // 限流提示
    break;
  default:
    // 通用错误提示
    toast(resp.message || '请求失败');
}
```

### smart 与 legacy 区别（只影响 HTTP 状态码）

- `smart`：业务码失败（如 `200404`）默认映射 HTTP `400`
- `legacy`：业务码失败（如 `200404`）默认映射 HTTP `500`

无论哪种策略，前端都建议按上面的规则：**优先看 `status`，再看 `code`**。
