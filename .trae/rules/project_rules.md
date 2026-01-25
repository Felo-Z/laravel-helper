# Project Rules

## 命名规范

### 配置文件命名
- 配置文件中的函数/功能键名必须使用蛇形命名（snake_case）
- 示例：`clear_logs` 而不是 `clear-logs` 或 `clearLogs`

### PHP 命名
- 类名使用 PascalCase：`HelperServiceProvider`
- 函数名使用 camelCase：`clearLogs()`
- 配置键使用 snake_case：`clear_logs`
- 环境变量使用 SCREAMING_SNAKE_CASE：`FELO_HELPER_LOG_DIRECTORIES`

### Composer 包名
- 包名使用小写和连字符（kebab-case）：`felo-z/laravel-helper`
- PHP 命名空间使用 PascalCase：`FeloZ\LaravelHelper`

## 代码规范

### 代码格式化
- 生成或修改代码后，必须自动执行 `vendor/bin/pint` 进行代码格式化
- 确保代码符合 Laravel 的代码风格规范

### 注释规范
- 配置文件必须为每个配置项添加注释说明其用途
- 函数必须添加注释说明其功能和参数
- 关键代码逻辑必须添加注释说明
- 注释使用中文
