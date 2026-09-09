# DevHub · 系统导航

一个使用 PHP 8 + SQLite 开发的项目入口门户，包含玻璃科技风前台和独立管理后台。

## 启动

在当前目录打开 PowerShell，运行：

```powershell
.\start.ps1
```

然后访问 <http://localhost:8080>。首次运行会自动创建 `data/devhub.sqlite` 并写入演示项目。

## 功能

- 按分类浏览，按名称、描述和标签实时搜索
- 收藏项目并自动置顶（收藏和主题保存在浏览器）
- iframe 页内预览，以及新标签页直达
- 在页面中添加、编辑和删除项目，数据保存到 SQLite
- 根据真实点击次数自动生成常用项目
- 后台拖拽排序、图标选择器和点击次数统计
- Session 登录、CSRF 防护及管理员密码修改
- 浅色/深色主题、键盘快捷键 `/` 和 `Esc`、移动端适配

## 运行要求

PHP 8.1+，并启用 `pdo_sqlite` 和 `sqlite3` 扩展。项目自带的 `start.ps1` 会为当前 WinGet PHP 临时加载这两个扩展，不会修改系统配置。

## 安全提示

首次启动后的默认后台账号为 `admin`，默认密码为 `DevHub@2026`。公开部署前请登录 `/password.php` 修改密码，并确保 Web 服务器禁止直接访问 `data` 目录。SQLite 数据库已被 `.gitignore` 排除，不会提交到 Git。
