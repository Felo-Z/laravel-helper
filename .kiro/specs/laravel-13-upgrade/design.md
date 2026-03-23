# 设计文档：laravel-13-upgrade

## 概述

本次升级的目标是将 `felo-z/laravel-helper` 扩展包的依赖从 Laravel 12 升级到 Laravel 13，同时保持对 Laravel 12 的向后兼容性。

升级范围包含四个方面：

1. **依赖版本更新**：`composer.json` 中的 `laravel/framework` 和 `orchestra/testbench` 版本约束升级。
2. **破坏性变更适配**：确认并处理 Laravel 13 中 CSRF 中间件重命名（`VerifyCsrfToken` → `PreventRequestForgery`）。
3. **测试基础设施验证**：确保 Testbench 11 + PHPUnit 12 环境下所有测试通过。
4. **文档更新**：README 和 CHANGELOG 反映 Laravel 13 支持。

由于本包是一个轻量级工具包（仅包含两个 Artisan 命令、一个 ServiceProvider 和若干辅助函数），升级复杂度较低，主要工作集中在依赖版本和兼容性验证上。

---

## 架构

本包采用标准 Laravel 包结构，无自定义框架层：

```
felo-z/laravel-helper
├── src/
│   ├── HelperServiceProvider.php   # 服务提供者（注册命令 + 配置）
│   ├── Commands/
│   │   ├── ClearCacheCommand.php   # felo:clear-cache 命令
│   │   └── ClearLogsCommand.php    # felo:clear-logs 命令
│   ├── Support/
│   │   └── helper.php              # 全局辅助函数（clear_cache, clear_logs 等）
│   └── publish/
│       └── felo-helper.php         # 可发布的配置文件
├── tests/
│   ├── TestCase.php                # 基于 Orchestra\Testbench\TestCase
│   ├── Commands/
│   │   ├── ClearCacheCommandTest.php
│   │   └── ClearLogsCommandTest.php
│   └── FeatureTest.php
├── composer.json
└── phpunit.xml.dist
```

升级不涉及架构变更，仅涉及依赖版本约束和潜在的 API 兼容性调整。

### Laravel 13 关键变更影响分析

| 变更项 | 影响级别 | 本包受影响 | 处理方式 |
|--------|---------|-----------|---------|
| `laravel/framework` `^13.0` | High | 是 | 更新 `composer.json` |
| `orchestra/testbench` `^11.0` | High | 是 | 更新 `composer.json` |
| CSRF 中间件重命名 | High | 否（包内无引用） | 确认并记录 |
| Cache `serializable_classes` 配置 | Medium | 否（不涉及缓存配置发布） | 无需处理 |
| Cache 前缀连字符变更 | Low | 否（不依赖缓存前缀） | 无需处理 |

---

## 组件与接口

### 1. composer.json（依赖声明）

**变更前：**
```json
{
  "require": {
    "laravel/framework": "^12.0"
  },
  "require-dev": {
    "orchestra/testbench": "^10.0"
  }
}
```

**变更后：**
```json
{
  "require": {
    "laravel/framework": "^13.0"
  },
  "require-dev": {
    "orchestra/testbench": "^11.0"
  }
}
```

`php: ^8.4`、`phpunit/phpunit: ^12.0`、`phpstan/phpstan: ^2.0`、`laravel/pint: ^1.24`、`mockery/mockery: ^1.6` 均保持不变。

### 2. HelperServiceProvider

当前实现使用 `protected bool $defer = true`。在 Laravel 13 中，`ServiceProvider` 的 `$defer` 属性已被废弃，延迟加载应通过实现 `DeferrableProvider` 接口来声明。

**变更前：**
```php
class HelperServiceProvider extends ServiceProvider
{
    protected bool $defer = true;
    // ...
}
```

**变更后：**
```php
use Illuminate\Contracts\Support\DeferrableProvider;

class HelperServiceProvider extends ServiceProvider implements DeferrableProvider
{
    // 移除 $defer 属性
    // 实现 provides() 方法
    public function provides(): array
    {
        return [];
    }
    // ...
}
```

> 设计决策：由于本包的 ServiceProvider 实际上不绑定任何服务容器绑定（仅注册命令和配置），`$defer = true` 的实际效果有限。升级时可选择：(a) 实现 `DeferrableProvider` 接口，或 (b) 直接移除 `$defer` 属性（不延迟加载）。推荐选择 (b)，因为命令注册本身开销极小，且避免引入不必要的接口实现。

### 3. ClearCacheCommand / ClearLogsCommand

两个命令类无需修改。`Illuminate\Console\Command` 的接口在 Laravel 13 中保持兼容。`#[AsCommand]` 属性在 Laravel 13 中继续支持。

### 4. phpunit.xml.dist

当前 schema 指向 `https://schema.phpunit.de/12.0/phpunit.xsd`，PHPUnit 12 在 Laravel 13 + Testbench 11 环境下仍然适用，无需修改。

### 5. CSRF 中间件（确认无引用）

经过全代码库搜索，本包中不存在对 `VerifyCsrfToken` 或 `PreventRequestForgery` 的任何引用。此变更对本包无影响，无需修改任何文件。

---

## 数据模型

本包不定义数据库模型，也不涉及数据持久化。唯一的"数据结构"是配置文件 `felo-helper.php`：

```php
// src/publish/felo-helper.php
return [
    'clear_logs' => [
        'directories' => env('FELO_HELPER_LOG_DIRECTORIES', [storage_path('logs')]),
        'extensions'  => env('FELO_HELPER_LOG_EXTENSIONS', 'log,sql,json'),
        'exclude'     => env('FELO_HELPER_LOG_EXCLUDE', 'laravel.log'),
        // 'recursive' 字段在配置文件中未声明，但 helper.php 中有默认值 true
    ],
    'clear_cache' => [
        'clear_laravel_cache' => env('FELO_HELPER_CLEAR_LARAVEL_CACHE', true),
        'redis_connections'   => env('FELO_HELPER_REDIS_CONNECTIONS', 'default'),
    ],
];
```

此配置结构在 Laravel 13 下无需变更。

---

## 正确性属性

*属性（Property）是在系统所有有效执行中都应成立的特征或行为——本质上是对系统应做什么的形式化陈述。属性是人类可读规范与机器可验证正确性保证之间的桥梁。*

### 属性 1：clear_logs 按配置精确清理文件

*对于任意* 日志目录、文件集合、扩展名列表和排除列表的组合，调用 `clear_logs()` 后：扩展名在 `extensions` 中且文件名不在 `exclude` 中的文件应被删除；其余文件（扩展名不匹配，或文件名在排除列表中）应保持不变。

**验证：需求 4.1**

### 属性 2：clear_cache 清理 Laravel 缓存

*对于任意* 已写入 Laravel 缓存的键值对集合，当 `clear_laravel_cache = true` 时，调用 `clear_cache()` 后，所有这些键都应从缓存中消失（`Cache::get($key)` 返回 `null`）。

**验证：需求 4.2**

### 属性 3：felo:clear-cache 命令对任意有效配置均成功退出

*对于任意* 有效的 `felo-helper.clear_cache` 配置（`clear_laravel_cache` 为 `true` 或 `false`，`redis_connections` 为空数组），执行 `felo:clear-cache` 命令应始终返回退出码 `0` 并输出 `Cache cleared successfully.`。

**验证：需求 4.3**

### 属性 4：felo:clear-logs 命令对任意有效配置均成功退出

*对于任意* 有效的 `felo-helper.clear_logs` 配置（任意目录列表、扩展名列表、排除列表），执行 `felo:clear-logs` 命令应始终返回退出码 `0` 并输出 `Log files cleared successfully.`。

**验证：需求 4.4**

---

## 错误处理

### 依赖冲突

若 `composer install` 因版本约束冲突失败，应检查 `composer.lock` 并手动解析冲突包版本。本包依赖链简单，预计不会出现此问题。

### clear_logs 目录不存在

`clear_logs()` 中已有 `if (! is_dir($directory)) { continue; }` 保护，目录不存在时静默跳过，不抛出异常。此行为在 Laravel 13 下保持不变。

### clear_cache Redis 连接不存在

当 `redis_connections` 包含不存在的连接名时，`Redis::connection($connection)` 会抛出异常。测试环境通过将 `redis_connections` 设为空数组 `[]` 来规避此问题，生产环境需确保配置正确。

### $defer 属性废弃警告

Laravel 13 中 `protected bool $defer = true` 会触发废弃警告（deprecation notice）。升级时应移除此属性，避免在测试输出中产生噪音。

---

## 测试策略

### 双重测试方法

本包采用单元测试 + 属性测试的组合方式：

- **单元测试（PHPUnit 12）**：验证具体示例、边界条件和错误场景
- **属性测试（PHPUnit DataProvider，最少 100 次迭代）**：验证跨多种输入的通用属性

由于本包是 PHP Laravel 包，属性测试使用 **PHPUnit 的 `@dataProvider`** 配合随机输入生成器来实现属性测试行为，无需引入额外依赖。

### 单元测试覆盖（具体示例）

| 测试文件 | 测试场景 |
|---------|---------|
| `ClearCacheCommandTest` | 清理缓存后键消失；`clear_laravel_cache=false` 时缓存保留 |
| `ClearLogsCommandTest` | 删除指定扩展名文件；排除文件不被删除；自定义扩展名 |
| `FeatureTest` | 服务提供者注册验证（需求 4.5）；composer.json 版本约束验证（需求 1.1、1.2、1.4、3.1）；代码库无 VerifyCsrfToken 引用（需求 2.2） |

### 属性测试配置

每个属性测试通过 DataProvider 提供最少 100 组随机输入，每组输入独立验证属性成立。

每个属性测试必须包含注释标记：
```php
// Feature: laravel-13-upgrade, Property {N}: {property_text}
```

| 属性 | 对应测试方法 | 迭代次数 |
|-----|------------|---------|
| 属性 1：clear_logs 按配置精确清理文件 | `test_clear_logs_respects_config_property` | ≥ 100 |
| 属性 2：clear_cache 清理 Laravel 缓存 | `test_clear_cache_flushes_all_keys_property` | ≥ 100 |
| 属性 3：felo:clear-cache 命令成功退出 | `test_clear_cache_command_exits_successfully_property` | ≥ 100 |
| 属性 4：felo:clear-logs 命令成功退出 | `test_clear_logs_command_exits_successfully_property` | ≥ 100 |

### 升级验证检查清单

1. `composer update` 成功，无版本冲突
2. `vendor/bin/phpunit --colors` 全部通过
3. `vendor/bin/phpstan analyse --memory-limit=512M` 无新增错误
4. `vendor/bin/pint --test` 代码风格检查通过
