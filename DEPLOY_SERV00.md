# Serv00 静态部署说明

本项目使用 Astro 预先生成 HTML、CSS、JavaScript 和数据文件。部署到 Serv00 时只上传 `dist/` 的内容，不运行 Typecho，也不需要 PHP、MySQL 或服务器端 Node.js 进程。

## 需要准备

- 已启用的 Serv00 账号，以及账号实际可绑定的域名。
- 本机安装 Node.js 20+、npm 和支持 SFTP 的客户端（例如 WinSCP）。
- 域名 DNS 已按 Serv00 面板显示的地址配置。

Serv00 官方当前列出的免费空间为 3 GB。注册申请需要人工审核，官方说明通常需几小时到 72 小时。服务条款还写明：DevilWEB 或 SSH 连续 90 天未登录，账号会被自动删除；账号和数据也可能因平台规则被停用或删除。因此 Git 仓库和本地副本要保留，不能只留 Serv00 上的一份。

## 生成站点文件

在项目根目录打开 PowerShell，替换成实际访问网址后构建：

```powershell
$env:SITE_URL = 'https://你的实际域名'
npm ci
npm run build:serv00
```

构建会将 Astro 的基础路径设为 `/`，产物放在 `dist/`。不要上传项目源码、`node_modules/` 或 `docs/`；只上传 `dist/` 里面的全部文件和子目录。

## 配置 Serv00 网站

在 DevilWEB 的 WWW websites 页面添加已绑定的域名；也可按 Serv00 当前文档使用：

```sh
devil www add 你的实际域名
```

官方文档示例中的网站目录为：

```text
~/domains/你的实际域名/public_html/
```

通过 SFTP 登录后，把 `dist/` 内的全部内容上传到这个 `public_html/` 目录，保留目录结构并允许覆盖同名旧构建文件。不要把 `dist` 这个目录本身再套一层上传，否则首页会落在 `public_html/dist/` 而不是站点根目录。

Astro 已按目录生成 `index.html` 路由；正常情况下不需要 Typecho 的 PHP 伪静态规则，也不应使用 `chmod -R 777`。如果要强制 HTTPS，可在 DevilWEB 网站详情中打开 Force SSL；Serv00 官方支持从 SSL 面板申请 Let's Encrypt 证书，域名 DNS 需先指向该站点。

## 更新与回滚

每次内容更新后重新运行构建，再将新的 `dist/` 内容同步到 `public_html/`。更新前可在 SFTP 客户端下载一份当前站点文件，或保留上一版 `dist/` 作为回滚副本；Git 仓库是源码和内容的主要备份。

## 与 Typecho 方案的差异

- 这里沿用 Astro 5 和 npm，访问时直接返回静态文件；Serv00 只负责文件托管。
- 搜索、目录高亮、主题切换和短动态由浏览器端代码完成；评论由现有 GitHub Issues 评论组件承载。
- 文章仍在 `docs/` 的 Markdown 文件维护，更新后需要重新构建、上传。此方案没有 Typecho 后台编辑器和服务器数据库。
- 若改成 Typecho，必须另行迁移主题和内容模型；这不是当前 Astro 项目的同一套构建产物。

## 附件建议中的命令修正

- Serv00 官方列出的站点命令为 `devil www add DOMAIN [type]`；PHP 版本通过站点目录的 `.htaccess` 中 `AddType application/x-httpd-phpXX .php` 选择，并不存在附件示例所写的通用 `devil php version 8.2` 用法。静态博客本身不需要选择 PHP 版本。
- Let's Encrypt 可通过 DevilWEB 的 SSL 管理页面申请；官方 CLI 命令形式为 `devil ssl www add IP_ADDRESS le le DOMAIN_NAME`，不是 `devil ssl yourdomain.com`。
- `chmod -R 777` 会把文件设为所有用户可写，既不需要也不安全。静态页面应使用账号默认权限，不给访客写权限。
- Typecho 官方仓库目前列出的最新正式版为 v1.3.0，最低 PHP 版本为 7.4，并支持 MariaDB/MySQL、SQLite、PostgreSQL 等数据库。附件只提 MySQL 和 PHP 8.0+，且列出的扩展应当先依据实际安装检查，不应当全部视作 Typecho 的硬性前置条件。

## 官方资料

- [Serv00 网站与目录配置](https://docs.serv00.com/WWW/)
- [Serv00 SSL 证书配置](https://docs.serv00.com/SSL/)
- [Serv00 PHP 版本配置](https://docs.serv00.com/PHP/)
- [Serv00 服务条款](https://www.serv00.com/terms-of-service/)
- [Typecho 官方仓库与安装要求](https://github.com/typecho/typecho)
