# SQL 日志

`laravel-helper` 内置了 SQL 日志模块，用于自动监听 Laravel 数据库查询并写入本地文件。它基于 `DB::listen()` / `QueryExecuted` 事件工作，不需要额外 helper 或 facade 调用，适合排查慢查询、请求级 SQL 行为和生产环境临时诊断。

## 能力概览

- 自动监听数据库查询，启用后立即生效
- 支持全量 SQL 与慢查询分流
- 支持按连接名、请求方法、SQL 正则过滤
- 支持记录 HTTP / Console / Queue Job 来源
- 支持输出替换绑定值后的 SQL
- 支持按执行作用域输出分组头
- 写文件时使用独占锁，降低并发写入交错风险

## 工作方式

当 `sql_logger.enabled=true` 时，服务提供者会在包启动时注册数据库监听器。每次查询执行后：

1. 判断是否应忽略当前查询
2. 生成标准化日志条目
3. 根据通道配置决定写入 `all`、`slow` 或同时写入两个通道
4. 按文件名模板写入目标日志文件

默认提供两个通道：

- `all`：记录所有命中的查询
- `slow`：仅记录耗时超过阈值的查询

## 快速开始

先发布配置文件：

```bash
php artisan vendor:publish --provider="FeloZ\LaravelHelper\HelperServiceProvider"
```

然后在 `config/felo-helper.php` 中启用：

```php
'sql_logger' => [
    'enabled' => env('FELO_HELPER_SQL_LOGGER_ENABLED', true),
],
```

最小环境变量示例：

```env
FELO_HELPER_SQL_LOGGER_ENABLED=true
FELO_HELPER_SQL_LOGGER_DIRECTORY=/path/to/storage/logs/sql
```

启用后，默认会在 `storage/logs/sql` 目录下输出：

- `YYYY-MM-DD-all.sql`
- `YYYY-MM-DD-slow.sql`

## 完整配置

```php
'sql_logger' => [
    'enabled' => env('FELO_HELPER_SQL_LOGGER_ENABLED', false),
    'directory' => env('FELO_HELPER_SQL_LOGGER_DIRECTORY', storage_path('logs/sql')),
    'replace_bindings' => env('FELO_HELPER_SQL_LOGGER_REPLACE_BINDINGS', true),
    'collapse_whitespace' => env('FELO_HELPER_SQL_LOGGER_COLLAPSE_WHITESPACE', true),
    'ignore_connections' => env('FELO_HELPER_SQL_LOGGER_IGNORE_CONNECTIONS', ''),
    'exclude_patterns' => env('FELO_HELPER_SQL_LOGGER_EXCLUDE_PATTERNS', ''),
    'only_methods' => env('FELO_HELPER_SQL_LOGGER_ONLY_METHODS', ''),
    'exclude_methods' => env('FELO_HELPER_SQL_LOGGER_EXCLUDE_METHODS', ''),
    'include_raw_sql' => env('FELO_HELPER_SQL_LOGGER_INCLUDE_RAW_SQL', true),
    'include_bindings' => env('FELO_HELPER_SQL_LOGGER_INCLUDE_BINDINGS', true),
    'max_query_length' => (int) env('FELO_HELPER_SQL_LOGGER_MAX_QUERY_LENGTH', 0),
    'max_binding_length' => (int) env('FELO_HELPER_SQL_LOGGER_MAX_BINDING_LENGTH', 0),
    'group_by_scope' => env('FELO_HELPER_SQL_LOGGER_GROUP_BY_SCOPE', false),
    'scope_header_format' => env(
        'FELO_HELPER_SQL_LOGGER_SCOPE_HEADER_FORMAT',
        "================ SCOPE START ================\n".
        "scope: {scope_type}\n".
        "id: {scope_id}\n".
        "name: {scope_name}\n".
        "time: {scope_started_at}\n".
        "{scope_context}\n"
    ),
    'date_format' => env('FELO_HELPER_SQL_LOGGER_DATE_FORMAT', 'Y-m-d H:i:s.u'),
    'entry_format' => env(
        'FELO_HELPER_SQL_LOGGER_ENTRY_FORMAT',
        "[{datetime}] [{channel}] [{origin}] [{connection}] [{duration}]\n{sql}\n{separator}\n"
    ),
    'channels' => [
        'all' => [
            'enabled' => env('FELO_HELPER_SQL_LOGGER_ALL_ENABLED', true),
            'pattern' => env('FELO_HELPER_SQL_LOGGER_ALL_PATTERN', '/.+/s'),
            'file_name' => env('FELO_HELPER_SQL_LOGGER_ALL_FILE_NAME', '{date:Y-m-d}-all.sql'),
            'append' => env('FELO_HELPER_SQL_LOGGER_ALL_APPEND', true),
        ],
        'slow' => [
            'enabled' => env('FELO_HELPER_SQL_LOGGER_SLOW_ENABLED', true),
            'threshold_ms' => (float) env('FELO_HELPER_SQL_LOGGER_SLOW_THRESHOLD_MS', 100),
            'pattern' => env('FELO_HELPER_SQL_LOGGER_SLOW_PATTERN', '/.+/s'),
            'file_name' => env('FELO_HELPER_SQL_LOGGER_SLOW_FILE_NAME', '{date:Y-m-d}-slow.sql'),
            'append' => env('FELO_HELPER_SQL_LOGGER_SLOW_APPEND', true),
        ],
    ],
],
```

## 配置项说明

| 配置项 | 说明 |
| --- | --- |
| `enabled` | 是否启用 SQL 日志监听 |
| `directory` | SQL 日志输出目录 |
| `replace_bindings` | 是否将绑定值替换回 SQL，占位符 `?` / `:name` 会被插值 |
| `collapse_whitespace` | 是否将 SQL 中连续空白压平成单行，便于匹配与查看 |
| `ignore_connections` | 忽略指定连接名，支持数组或逗号分隔字符串 |
| `exclude_patterns` | 命中后跳过记录的 SQL 正则，支持数组或逗号分隔字符串 |
| `only_methods` | 仅记录指定 HTTP Method，请求外场景不受影响 |
| `exclude_methods` | 排除指定 HTTP Method，请求外场景不受影响 |
| `include_raw_sql` | 是否允许 `{raw_sql}` 输出原始 SQL |
| `include_bindings` | 是否允许 `{bindings}` 输出绑定值 JSON |
| `max_query_length` | 限制最终写入日志的 SQL 长度，`0` 表示不限制 |
| `max_binding_length` | 限制 `{bindings}` 输出长度，`0` 表示不限制 |
| `group_by_scope` | 是否按执行作用域输出分组头 |
| `scope_header_format` | 分组头模板 |
| `date_format` | 日志时间格式 |
| `entry_format` | 单条日志模板 |
| `channels.all` | 全量日志通道配置 |
| `channels.slow` | 慢查询通道配置 |
| `channels.slow.threshold_ms` | 慢查询毫秒阈值 |
| `channels.*.pattern` | 仅匹配到该正则的 SQL 才写入对应通道 |
| `channels.*.file_name` | 输出文件名模板 |
| `channels.*.append` | 是否追加写入，关闭则覆盖写入 |

## 通道规则

SQL 日志支持多通道并行写入。每条查询会依次判断所有通道：

- 通道未启用时跳过
- `sourceSql` 未命中 `pattern` 时跳过
- `slow` 通道下，若执行耗时小于 `threshold_ms` 则跳过

因此一条慢查询可能同时写入：

- `all`
- `slow`

如果你只想保留慢查询文件，可以关闭 `all.enabled`。

## 请求方法过滤

`only_methods` 和 `exclude_methods` 只在 HTTP 请求场景下生效：

- 非 HTTP 场景（如 Artisan 命令、Queue Job）不会因为这两个配置被过滤
- 同时配置时，会先判断 `only_methods`，再判断 `exclude_methods`

示例：

```php
'sql_logger' => [
    'enabled' => true,
    'only_methods' => ['POST', 'PUT', 'PATCH', 'DELETE'],
    'exclude_methods' => ['DELETE'],
],
```

上面的配置最终会记录 `POST` / `PUT` / `PATCH`，但排除 `DELETE`。

## 绑定值插值说明

当 `replace_bindings=true` 时，日志中的 `{sql}` 会把绑定值替换回原 SQL：

- `null` 会转成 `null`
- 布尔值会转成 `1` / `0`
- 数字保持原样
- 字符串会自动加单引号并转义单引号
- 日期对象会被格式化
- 二进制字符串和资源类型会被标准化处理

这让日志更接近“可直接复制执行”的 SQL，但也意味着敏感参数可能被写入磁盘。

如果你更希望保留原始占位符，请关闭 `replace_bindings`，并在 `entry_format` 中使用 `{raw_sql}` 与 `{bindings}`。

## 作用域分组

开启 `group_by_scope=true` 后，每个新的执行作用域首次写日志时会先输出一个 header。当前支持：

- HTTP 请求
- Artisan 命令
- Queue Job
- 其他未知来源

默认 header 模板：

```text
================ SCOPE START ================
scope: {scope_type}
id: {scope_id}
name: {scope_name}
time: {scope_started_at}
{scope_context}
```

可用占位符：

- `{scope_type}`
- `{scope_id}`
- `{scope_name}`
- `{scope_started_at}`
- `{scope_context}`

其中 `{scope_context}` 会按多行展开，例如：

```text
method: POST
url: http://localhost/api/orders
```

## 日志模板占位符

`entry_format` 支持以下占位符：

- `{sequence}`：当前 PHP 进程内的查询序号
- `{datetime}`：查询记录时间
- `{channel}`：当前通道，如 `all` / `slow`
- `{origin}`：来源信息，如 `http: GET http://...`、`console: artisan migrate`
- `{connection}`：数据库连接名
- `{duration}`：耗时字符串，单位毫秒
- `{duration_ms}`：原始毫秒数
- `{sql}`：最终写入 SQL
- `{raw_sql}`：原始 SQL
- `{bindings}`：JSON 格式的绑定值
- `{separator}`：分隔线

默认模板：

```text
[{datetime}] [{channel}] [{origin}] [{connection}] [{duration}]
{sql}
{separator}
```

## 文件名模板

`channels.*.file_name` 支持以下占位符：

- `{date:Y-m-d}`：按时间格式渲染当前日期
- `{channel}`：当前日志通道
- `{origin}`：来源信息，会自动清理为安全文件名片段
- `{connection}`：连接名

例如：

```php
'file_name' => '{date:Y-m-d}-{channel}-{connection}.sql',
```

可能生成：

```text
2026-04-28-all-mysql.sql
```

建议优先使用日期、通道和连接名，避免包含过长的 URL 或命令内容。

## 常用配置示例

### 仅记录慢查询

```php
'sql_logger' => [
    'enabled' => true,
    'channels' => [
        'all' => ['enabled' => false],
        'slow' => [
            'enabled' => true,
            'threshold_ms' => 200,
            'pattern' => '/^(select|update|delete|insert)/i',
            'file_name' => '{date:Y-m-d}-slow.sql',
            'append' => true,
        ],
    ],
],
```

### 保留原始 SQL 与 bindings

```php
'sql_logger' => [
    'enabled' => true,
    'replace_bindings' => false,
    'entry_format' => "[{datetime}] [{connection}] {raw_sql}\n{bindings}\n{separator}\n",
],
```

### 忽略噪音查询

```php
'sql_logger' => [
    'enabled' => true,
    'ignore_connections' => ['metrics', 'debugbar'],
    'exclude_patterns' => [
        '/sqlite_master/i',
        '/^pragma /i',
    ],
],
```

### 仅记录写请求 SQL

```php
'sql_logger' => [
    'enabled' => true,
    'only_methods' => ['POST', 'PUT', 'PATCH', 'DELETE'],
],
```

### 精简输出格式

```php
'sql_logger' => [
    'enabled' => true,
    'include_raw_sql' => false,
    'include_bindings' => false,
    'entry_format' => "[{datetime}] [{duration}] {sql}\n{separator}\n",
],
```

### 截断超长 SQL

```php
'sql_logger' => [
    'enabled' => true,
    'max_query_length' => 500,
],
```

超出长度后会自动追加 `...[truncated]`。

### 截断超长 bindings

```php
'sql_logger' => [
    'enabled' => true,
    'max_binding_length' => 300,
],
```

这个配置只影响 `{bindings}` 的展示，不影响实际查询执行。

### 按作用域分组写日志

```php
'sql_logger' => [
    'enabled' => true,
    'group_by_scope' => true,
],
```

## 环境变量示例

```env
FELO_HELPER_SQL_LOGGER_ENABLED=false
FELO_HELPER_SQL_LOGGER_DIRECTORY=/path/to/storage/logs/sql
FELO_HELPER_SQL_LOGGER_REPLACE_BINDINGS=true
FELO_HELPER_SQL_LOGGER_COLLAPSE_WHITESPACE=true
FELO_HELPER_SQL_LOGGER_IGNORE_CONNECTIONS=metrics,debugbar
FELO_HELPER_SQL_LOGGER_EXCLUDE_PATTERNS='/sqlite_master/i,/^pragma /i'
FELO_HELPER_SQL_LOGGER_ONLY_METHODS=POST,PUT,PATCH,DELETE
FELO_HELPER_SQL_LOGGER_EXCLUDE_METHODS=HEAD,OPTIONS
FELO_HELPER_SQL_LOGGER_INCLUDE_RAW_SQL=true
FELO_HELPER_SQL_LOGGER_INCLUDE_BINDINGS=true
FELO_HELPER_SQL_LOGGER_MAX_QUERY_LENGTH=500
FELO_HELPER_SQL_LOGGER_MAX_BINDING_LENGTH=300
FELO_HELPER_SQL_LOGGER_GROUP_BY_SCOPE=true
FELO_HELPER_SQL_LOGGER_SCOPE_HEADER_FORMAT="================ SCOPE START ================\nscope: {scope_type}\nid: {scope_id}\nname: {scope_name}\ntime: {scope_started_at}\n{scope_context}\n"
FELO_HELPER_SQL_LOGGER_DATE_FORMAT="Y-m-d H:i:s.u"
FELO_HELPER_SQL_LOGGER_ENTRY_FORMAT="[{datetime}] [{channel}] [{origin}] [{connection}] [{duration}]\n{sql}\n{separator}\n"
FELO_HELPER_SQL_LOGGER_ALL_ENABLED=true
FELO_HELPER_SQL_LOGGER_ALL_PATTERN='/.+/s'
FELO_HELPER_SQL_LOGGER_ALL_FILE_NAME='{date:Y-m-d}-all.sql'
FELO_HELPER_SQL_LOGGER_ALL_APPEND=true
FELO_HELPER_SQL_LOGGER_SLOW_ENABLED=true
FELO_HELPER_SQL_LOGGER_SLOW_THRESHOLD_MS=100
FELO_HELPER_SQL_LOGGER_SLOW_PATTERN='/.+/s'
FELO_HELPER_SQL_LOGGER_SLOW_FILE_NAME='{date:Y-m-d}-slow.sql'
FELO_HELPER_SQL_LOGGER_SLOW_APPEND=true
```

## 输出示例

```text
[2026-04-28 14:23:45.123456] [all] [http: GET http://localhost/api/users] [mysql] [12.4 ms]
select * from `users` where `email` = 'demo@example.com'
/*==================================================*/
```

开启 `group_by_scope` 后，可能变成：

```text
================ SCOPE START ================
scope: http
id: http:123456
name: GET http://localhost/api/users
time: 2026-04-28 14:23:45.123456
method: GET
url: http://localhost/api/users

[2026-04-28 14:23:45.123456] [all] [http: GET http://localhost/api/users] [mysql] [12.4 ms]
select * from `users`
/*==================================================*/
```

## 生产环境建议

- 默认不要在生产环境长期开启全量 SQL 日志。
- 如果必须开启，优先只开 `slow` 通道，并提高 `threshold_ms`。
- `replace_bindings=true` 可能把账号、手机号、令牌等敏感数据写入文件，启用前请评估风险。
- SQL 日志目录建议与应用主日志分离，便于清理与归档。
- 如果 `pattern` 或 `exclude_patterns` 中的正则表达式无效，运行时会自动回退为宽松匹配，建议上线前先验证正则写法。
- 如果你使用集中式日志平台，建议后续扩展 `SqlLogWriter`，将日志转发到统一通道。
