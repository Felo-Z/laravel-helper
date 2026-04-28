# 更新日志

本文档记录了 Laravel Helper 包的所有重要变更。

## [Unreleased]

### 新增
- 新增 API 响应能力：`ap()` helper、`Ap` Facade、统一响应结构与全量 HTTP 快捷方法
- 新增异常响应链路：`exception_pipes`、`render_using` 自动接管 API/JSON 请求异常
- 新增项目扩展能力：`ApiResponse` 支持 Macro、自定义业务码与分组常量（`ApiCodes/*`）
- 新增文档体系：接入示例、前后端约定模板、前端精简版、项目扩展指南、升级说明
- 新增 `error()` 方法作为 `failed()` 别名

### 变更
- `api_response` 新增 `status_code_strategy` 配置，默认 `smart`
- `smart` 策略下业务码失败默认映射 HTTP 400（避免误判系统异常 500）
- README 版本要求更新为 `PHP >= 8.4` 与 `Laravel ^13.0`

### 测试
- 新增 API 响应端到端异常接管 Feature 测试
- 补充 `error()` 别名与状态码策略相关测试

### 历史变更
- 将 `orchestra/testbench` 从 `^10.0` 升级到 `^11.0`

### 移除
- 移除 `HelperServiceProvider` 中已废弃的 `$defer` 属性（Laravel 13 不再支持）

### 新增
- 添加 `clear_logs()` 辅助函数，用于清理日志文件
- 添加 `clear_cache()` 辅助函数，用于清理 Laravel 缓存和 Redis 缓存
- 添加 `felo:clear-logs` Artisan 命令，用于清理日志文件
- 添加 `felo:clear-cache` Artisan 命令，用于清理缓存
- 添加配置文件 `config/felo-helper.php`，支持自定义日志和缓存清理行为
- 支持通过环境变量配置日志目录、文件扩展名、排除文件等
- 支持通过环境变量配置是否清理 Laravel 缓存以及 Redis 连接

### 功能特性
- 日志清理支持多个目录配置
- 日志清理支持自定义文件扩展名
- 日志清理支持排除特定文件
- 缓存清理支持多个 Redis 连接
- 缓存清理可选择是否清理 Laravel 缓存

### 配置项
- `clear_logs.directories` - 日志文件目录
- `clear_logs.extensions` - 需要清理的日志文件扩展名
- `clear_logs.exclude` - 排除的文件名
- `clear_cache.clear_laravel_cache` - 是否清理 Laravel 缓存
- `clear_cache.redis_connections` - Redis 连接名称

### 环境变量
- `FELO_HELPER_LOG_DIRECTORIES` - 日志文件目录（逗号分隔）
- `FELO_HELPER_LOG_EXTENSIONS` - 日志文件扩展名（逗号分隔）
- `FELO_HELPER_LOG_EXCLUDE` - 排除的文件名（逗号分隔）
- `FELO_HELPER_CLEAR_LARAVEL_CACHE` - 是否清理 Laravel 缓存
- `FELO_HELPER_REDIS_CONNECTIONS` - Redis 连接名称（逗号分隔）

## [1.0.0] - 2026-01-24

### 新增
- 初始版本发布
- 基础 Laravel 包结构
- 服务提供者配置
