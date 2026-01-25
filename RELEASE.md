# 发布及更新流程

本文档详细说明了 Laravel Helper 包的发布和版本更新流程。

## 📋 目录

- [首次发布流程](#首次发布流程)
- [版本更新流程](#版本更新流程)
- [发布前检查清单](#发布前检查清单)
- [版本号规范](#版本号规范)
- [常见问题](#常见问题)

---

## 首次发布流程

### 1. 代码质量检查

在发布前，确保所有代码质量检查都通过：

```bash
# 1. 代码格式化
composer run fix-style

# 2. 静态分析
composer run phpstan

# 3. 运行测试
composer run test
```

### 2. 初始化 Git 仓库

```bash
# 初始化 Git
git init

# 添加所有文件
git add .

# 提交初始版本
git commit -m "Initial commit: Laravel Helper v1.0.0"
```

### 3. 创建 GitHub 仓库

1. 访问 [GitHub](https://github.com/new) 创建新仓库
2. 仓库名称：`laravel-helper`
3. 设置为 Public（公开）
4. 不要初始化 README、.gitignore 或 license（因为我们已经有了）

### 4. 推送代码到 GitHub

```bash
# 添加远程仓库（替换为您的仓库地址）
git remote add origin https://github.com/felo-z/laravel-helper.git

# 设置主分支
git branch -M main

# 推送代码
git push -u origin main
```

### 5. 在 Packagist 注册包

1. 访问 [Packagist.org](https://packagist.org/)
2. 登录或注册账号
3. 点击右上角的 "Submit" 按钮
4. 在 "Repository URL" 输入框中输入：`https://github.com/felo-z/laravel-helper`
5. 点击 "Check" 按钮验证仓库
6. 点击 "Submit" 按钮提交包

### 6. 配置 GitHub 自动更新（推荐）

配置 Webhook 后，每次推送到 GitHub 时 Packagist 会自动更新：

1. 在 Packagist 包页面获取 API Token：
   - 访问您的包页面：https://packagist.org/packages/felo-z/laravel-helper
   - 点击 "API Token" 按钮
   - 复制显示的 API Token

2. 在 GitHub 仓库添加 Webhook：
   - 进入 GitHub 仓库设置：Settings → Webhooks → Add webhook
   - Payload URL：`https://packagist.org/api/github?username=felo-z&apiToken=YOUR_API_TOKEN`
   - Content type：选择 `application/json`
   - Secret：留空
   - 点击 "Add webhook"

### 7. 创建 GitHub Release

1. 在 GitHub 仓库页面点击 "Releases"
2. 点击 "Create a new release"
3. Tag version：`v1.0.0`
4. Release title：`v1.0.0`
5. Description：从 CHANGELOG.md 复制 v1.0.0 的内容
6. 点击 "Publish release"

### 8. 验证发布

```bash
# 测试安装包
composer require felo-z/laravel-helper
```

---

## 版本更新流程

### 1. 更新版本号

编辑 `composer.json` 文件，更新 `version` 字段：

```json
{
  "version": "1.1.0"
}
```

### 2. 更新 CHANGELOG.md

在 CHANGELOG.md 中添加新版本的变更记录：

```markdown
## [1.1.0] - 2026-01-25

### 新增
- 新功能描述

### 变更
- 变更内容描述

### 修复
- 修复的问题描述

### 移除
- 移除的功能描述
```

### 3. 代码质量检查

```bash
# 1. 代码格式化
composer run fix-style

# 2. 静态分析
composer run phpstan

# 3. 运行测试
composer run test
```

### 4. 提交代码

```bash
# 添加所有变更
git add .

# 提交变更
git commit -m "Release v1.1.0: 更新内容描述"

# 推送到 GitHub
git push origin main
```

### 5. 创建 Git Tag

```bash
# 创建标签
git tag v1.1.0

# 推送标签到 GitHub
git push origin v1.1.0
```

### 6. 创建 GitHub Release

1. 在 GitHub 仓库页面点击 "Releases"
2. 点击 "Create a new release"
3. Tag version：`v1.1.0`
4. Release title：`v1.1.0`
5. Description：从 CHANGELOG.md 复制 v1.1.0 的内容
6. 点击 "Publish release"

### 7. 验证更新

```bash
# 更新包到最新版本
composer update felo-z/laravel-helper
```

---

## 发布前检查清单

在发布任何版本前，请确保完成以下检查：

### 代码质量
- [ ] 运行 `composer run fix-style` 并通过
- [ ] 运行 `composer run phpstan` 并无错误
- [ ] 运行 `composer run test` 并全部通过

### 文档更新
- [ ] 更新 `composer.json` 中的版本号
- [ ] 更新 `CHANGELOG.md` 添加新版本记录
- [ ] 如有新功能，更新 `README.md`
- [ ] 检查所有文档的准确性

### 配置检查
- [ ] 检查 `composer.json` 中的依赖版本是否正确
- [ ] 检查 `composer.json` 中的脚本是否正确
- [ ] 检查配置文件是否完整

### Git 操作
- [ ] 所有变更已提交
- [ ] 提交信息清晰明确
- [ ] 代码已推送到 GitHub

### Packagist
- [ ] 包已在 Packagist 注册
- [ ] Webhook 已配置（可选但推荐）

### 测试
- [ ] 在干净的环境中测试安装
- [ ] 测试所有主要功能
- [ ] 测试配置文件发布
- [ ] 测试 Artisan 命令

---

## 版本号规范

本项目遵循 [语义化版本 (SemVer)](https://semver.org/lang/zh-CN/) 规范：

### 版本号格式：`MAJOR.MINOR.PATCH`

- **MAJOR（主版本号）**：不兼容的 API 修改
- **MINOR（次版本号）**：向下兼容的功能性新增
- **PATCH（修订号）**：向下兼容的问题修正

### 示例

- `1.0.0` → `1.0.1`：修复 Bug
- `1.0.1` → `1.1.0`：新增功能
- `1.1.0` → `2.0.0`：破坏性变更

### 预发布版本

如需发布预发布版本，可以使用以下标识：

- `1.0.0-alpha.1`：Alpha 版本
- `1.0.0-beta.1`：Beta 版本
- `1.0.0-rc.1`：Release Candidate 版本

---

## 常见问题

### Q1: Packagist 没有自动更新怎么办？

**A:** 检查以下几点：
1. Webhook 是否正确配置
2. GitHub 仓库是否为 Public
3. Packagist API Token 是否正确
4. 手动触发更新：访问 Packagist 包页面，点击 "Update" 按钮

### Q2: 如何回滚到之前的版本？

**A:** 可以通过以下方式：

```bash
# 安装特定版本
composer require felo-z/laravel-helper:1.0.0

# 或在 composer.json 中指定版本
"require": {
  "felo-z/laravel-helper": "1.0.0"
}
```

### Q3: 如何删除已发布的版本？

**A:** 
- GitHub Release：在 Releases 页面删除对应的 Release
- Git Tag：`git tag -d v1.0.0` 和 `git push origin :refs/tags/v1.0.0`
- Packagist：无法删除已发布的版本，只能发布新版本覆盖

### Q4: 如何重命名包？

**A:** 
1. 在 Packagist 上创建新包名
2. 更新 `composer.json` 中的 `name` 字段
3. 发布新版本
4. 在旧包的 README 中添加迁移说明

### Q5: 发布后发现严重 Bug 怎么办？

**A:** 
1. 立即修复 Bug
2. 发布 PATCH 版本（如 `1.0.1`）
3. 在 CHANGELOG 中标注 "紧急修复"
4. 在 GitHub Release 中标注 "Critical Fix"

---

## 相关资源

- [Packagist 官方文档](https://packagist.org/about)
- [语义化版本规范](https://semver.org/lang/zh-CN/)
- [Composer 文档](https://getcomposer.org/doc/)
- [GitHub Releases 文档](https://docs.github.com/en/repositories/releasing-projects-on-github/managing-releases-in-a-repository)

---

## 快速参考

### 首次发布命令

```bash
# 代码检查
composer run fix-style && composer run phpstan && composer run test

# Git 初始化
git init
git add .
git commit -m "Initial commit: Laravel Helper v1.0.0"

# 推送到 GitHub
git remote add origin https://github.com/felo-z/laravel-helper.git
git branch -M main
git push -u origin main

# 创建标签
git tag v1.0.0
git push origin v1.0.0
```

### 版本更新命令

```bash
# 代码检查
composer run fix-style && composer run phpstan && composer run test

# 提交变更
git add .
git commit -m "Release v1.1.0: 更新内容描述"
git push origin main

# 创建标签
git tag v1.1.0
git push origin v1.1.0
```

---

**最后更新：2026-01-25**
