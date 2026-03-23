# 需求文档

## 简介

将 `felo-z/laravel-helper` 扩展包从支持 Laravel 12 升级为支持 Laravel 13，同时保持与 Laravel 12 的向后兼容性。升级涉及依赖版本更新、破坏性变更适配（CSRF 中间件重命名）、测试基础设施升级，以及确保所有现有功能在 Laravel 13 环境下正常运行。

## 词汇表

- **Package**：`felo-z/laravel-helper` 扩展包本身
- **HelperServiceProvider**：包的服务提供者，负责注册命令和配置
- **ClearCacheCommand**：Artisan 命令 `felo:clear-cache`，用于清理 Laravel 缓存和 Redis 缓存
- **ClearLogsCommand**：Artisan 命令 `felo:clear-logs`，用于清理日志文件
- **Testbench**：`orchestra/testbench`，用于在 Laravel 包中运行测试的工具
- **Laravel_13**：Laravel 框架 13.x 版本
- **Laravel_12**：Laravel 框架 12.x 版本

---

## 需求

### 需求 1：升级核心框架依赖

**用户故事：** 作为包维护者，我希望将 `laravel/framework` 依赖升级到支持 Laravel 13，以便用户可以在 Laravel 13 项目中使用此包。

#### 验收标准

1. THE Package SHALL 在 `composer.json` 的 `require` 中将 `laravel/framework` 版本约束更新为 `^13.0`。
2. THE Package SHALL 在 `composer.json` 的 `require-dev` 中将 `orchestra/testbench` 版本约束更新为 `^11.0`。
3. WHEN 执行 `composer install` 时，THE Package SHALL 成功解析所有依赖而不产生版本冲突。
4. THE Package SHALL 保持 `php` 版本约束为 `^8.4` 不变。

---

### 需求 2：适配 CSRF 中间件重命名

**用户故事：** 作为包维护者，我希望包代码适配 Laravel 13 中 CSRF 中间件的重命名，以便包在 Laravel 13 环境下不会因引用已废弃的类名而报错。

#### 验收标准

1. WHEN 代码中存在对 `Illuminate\Foundation\Http\Middleware\VerifyCsrfToken` 的引用时，THE Package SHALL 将其替换为 `Illuminate\Foundation\Http\Middleware\PreventRequestForgery`。
2. IF 包代码中不存在对 `VerifyCsrfToken` 的直接引用，THEN THE Package SHALL 确认无需修改并记录此结论。
3. THE Package SHALL 在升级后通过静态分析（phpstan）检查，不产生与中间件类名相关的错误。

---

### 需求 3：验证并更新测试基础设施

**用户故事：** 作为包维护者，我希望测试套件在 Laravel 13 + Testbench 11 环境下正常运行，以便持续验证包的功能正确性。

#### 验收标准

1. THE Testbench SHALL 升级到 `^11.0` 以匹配 Laravel 13 的兼容矩阵。
2. WHEN 执行 `phpunit` 时，THE Package SHALL 所有现有测试通过，不产生新的失败。
3. IF `TestCase` 基类中存在与 Testbench 11 不兼容的 API 调用，THEN THE Package SHALL 更新相关代码以使用兼容的 API。
4. THE Package SHALL 在 `phpunit.xml.dist` 中保持现有测试配置结构不变，除非 PHPUnit 12 要求强制修改。

---

### 需求 4：确保现有功能在 Laravel 13 下正常运行

**用户故事：** 作为包用户，我希望 `clear_cache`、`clear_logs` 等辅助函数以及 Artisan 命令在 Laravel 13 下行为与 Laravel 12 一致，以便升级框架后无需修改业务代码。

#### 验收标准

1. WHEN 调用 `clear_logs()` 时，THE Package SHALL 按照 `felo-helper.clear_logs` 配置清理指定目录下的日志文件。
2. WHEN 调用 `clear_cache()` 时，THE Package SHALL 按照 `felo-helper.clear_cache` 配置清理 Laravel 缓存及指定 Redis 连接。
3. WHEN 执行 `php artisan felo:clear-cache` 时，THE ClearCacheCommand SHALL 返回退出码 `0` 并输出成功信息。
4. WHEN 执行 `php artisan felo:clear-logs` 时，THE ClearLogsCommand SHALL 返回退出码 `0` 并输出成功信息。
5. THE HelperServiceProvider SHALL 在 Laravel 13 的服务容器中正确注册配置和命令。

---

### 需求 5：更新文档与版本声明

**用户故事：** 作为包用户，我希望 README 和 CHANGELOG 反映 Laravel 13 的支持情况，以便在选择包版本时做出正确判断。

#### 验收标准

1. THE Package SHALL 在 `README.md` 中将支持的 Laravel 版本更新为包含 Laravel 13。
2. THE Package SHALL 在 `CHANGELOG.md` 中新增一条记录，描述 Laravel 13 升级的变更内容。
3. IF `composer.json` 中存在 `keywords` 字段，THEN THE Package SHALL 确认关键词仍然准确，无需因版本升级而修改。
