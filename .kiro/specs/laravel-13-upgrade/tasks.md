# 实现计划：laravel-13-upgrade

## 概述

将 `felo-z/laravel-helper` 包从 Laravel 12 升级到 Laravel 13，涉及依赖版本更新、ServiceProvider 兼容性修复、属性测试补充及文档更新。

## 任务

- [x] 1. 更新 composer.json 依赖版本
  - 将 `require.laravel/framework` 从 `^12.0` 改为 `^13.0`
  - 将 `require-dev.orchestra/testbench` 从 `^10.0` 改为 `^11.0`
  - _需求：1.1, 1.2, 1.4_

- [x] 2. 修复 HelperServiceProvider 兼容性
  - 移除 `protected bool $defer = true` 属性（Laravel 13 中已废弃）
  - _需求：4.5_

- [x] 3. 补充属性测试到 tests/Helpers/HelperFunctionsTest.php
  - [x] 3.1 实现属性 1：clear_logs 按配置精确清理文件
    - 使用 `@dataProvider` 生成 ≥ 100 组随机输入（任意目录、文件集合、扩展名列表、排除列表）
    - 验证扩展名匹配且不在排除列表中的文件被删除，其余文件保留
    - 添加注释 `// Feature: laravel-13-upgrade, Property 1: clear_logs 按配置精确清理文件`
    - _需求：4.1_

  - [ ]* 3.2 实现属性 2：clear_cache 清理 Laravel 缓存
    - 使用 `@dataProvider` 生成 ≥ 100 组随机键值对写入缓存
    - 调用 `clear_cache()` 后验证所有键均返回 `null`
    - 添加注释 `// Feature: laravel-13-upgrade, Property 2: clear_cache 清理 Laravel 缓存`
    - _需求：4.2_

  - [ ]* 3.3 实现属性 3：felo:clear-cache 命令对任意有效配置均成功退出
    - 使用 `@dataProvider` 生成 ≥ 100 组有效配置（`clear_laravel_cache` 为 true/false，`redis_connections` 为空数组）
    - 验证命令退出码为 `0` 且输出包含 `Cache cleared successfully.`
    - 添加注释 `// Feature: laravel-13-upgrade, Property 3: felo:clear-cache 命令对任意有效配置均成功退出`
    - _需求：4.3_

  - [ ]* 3.4 实现属性 4：felo:clear-logs 命令对任意有效配置均成功退出
    - 使用 `@dataProvider` 生成 ≥ 100 组有效配置（任意目录列表、扩展名列表、排除列表）
    - 验证命令退出码为 `0` 且输出包含 `Log files cleared successfully.`
    - 添加注释 `// Feature: laravel-13-upgrade, Property 4: felo:clear-logs 命令对任意有效配置均成功退出`
    - _需求：4.4_

- [x] 4. 检查点 - 确保所有测试通过
  - 确保所有测试通过，如有问题请向用户反馈。

- [x] 5. 更新 README.md 版本支持说明
  - 在 README.md 中将支持的 Laravel 版本更新为包含 Laravel 13
  - _需求：5.1_

- [x] 6. 更新 CHANGELOG.md
  - 新增 Laravel 13 升级记录，描述依赖版本变更和 ServiceProvider 修复内容
  - _需求：5.2_

- [x] 7. 最终检查点 - 确保所有测试通过
  - 确保所有测试通过，如有问题请向用户反馈。

## 备注

- 标有 `*` 的子任务为可选项，可跳过以加快 MVP 进度
- 每个任务均引用具体需求条款以保证可追溯性
- 属性测试使用 PHPUnit DataProvider 实现，无需引入额外依赖
- CSRF 中间件重命名（需求 2.2）已确认包内无相关引用，无需修改代码
