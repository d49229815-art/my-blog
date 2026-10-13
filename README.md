# 阿德小屋 · Astro

使用 Astro 5 构建的静态嵌入式开发博客，可部署到 GitHub Pages，也可将静态产物上传到 Serv00。站点沿用 Argon 的主色、卡片、侧栏与信息密度，内容由 Astro 静态生成。

## 本地预览

需要 Node.js 20 或更新版本。首次安装依赖后运行：

    npm install
    npm run dev

本地构建与预览：

    npm run build
    npm run preview

构建结果位于 dist/。GitHub Actions 会在 main 更新后构建，并将结果发布到 gh-pages 分支。

## 内容位置

- docs/ 保留迁移前的 Markdown、代码示例和 HTML 项目页面。
- src/content/posts/ 是构建时从 docs/**/*.md 自动生成的 Astro 内容集合，不要直接编辑；下次构建会重新生成。
- Python 示例、项目 HTML、职业规划页面及其他非 Markdown 附件会复制到 public/资料/，可从页面链接打开或下载。
- src/data/feels.json 用于说说数据，字段为 id、title、content、date，可选 type。原资料中没有说说导出，因此当前为空数组。
- 新文章先放入对应的 docs 中文主题目录，再构建。目录和标题会生成文章路由、分类与搜索索引。

## 页面与功能

首页、归档、分类、标签、文章详情、说说详情、关于、友链和 404 均为预渲染静态页面。搜索使用构建生成的 JSON 索引和 Fuse.js；代码高亮使用 Shiki，数学公式使用 KaTeX；文章页生成目录并高亮当前章节；RSS 输出到 rss.xml，站点地图由 Astro 集成生成。

评论由 Utterances 关联 d49229815-art/my-blog 的 GitHub Issues。首次启用时需在 GitHub 安装 Utterances 应用，并确认仓库开放 Issues。友链列表目前为空，避免代填未经确认的站点关系。

## GitHub Pages 与路径

仓库页面部署使用 /my-blog/ 基础路径。配置自定义域名时，在仓库 Actions Variables 中设置 SITE_DOMAIN，构建会生成 CNAME。SITE_URL 与 SITE_BASE 也可作为构建环境变量覆盖默认值。

Serv00 部署只需静态文件，不需要安装 Typecho、配置 PHP 或创建数据库。先设置站点根网址，再运行 `npm run build:serv00`，将 dist/ 内的全部文件上传至 Serv00 域名的 public_html/。详细步骤见 [Serv00 静态部署说明](DEPLOY_SERV00.md)。内容仍由 docs/ 中的 Markdown 管理，更新后需重新构建并上传。

仓库检出路径建议使用短英文目录，例如 C:\\src\\my-blog。部分旧编译器、脚本或烧录工具对中文路径兼容有限；Astro 页面支持中文路由，遇到工具链问题时可在短英文路径构建工程。

## 内容来源

现有资料没有文章发布日期、说说导出或头像图片，因此不会补造日期或动态。docs/ 原文件保持不变；迁移产物在构建前由脚本生成。
