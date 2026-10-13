# 阿德小屋

嵌入式开发、系统软件学习笔记与项目实践，使用 MkDocs Material 构建。

## 本地预览

安装 MkDocs Material 后运行：

```powershell
python -m pip install mkdocs-material
mkdocs serve
```

本地构建：

```powershell
mkdocs build
```

站点文件输出到 `site/`。课程示例代码保留在对应章节目录中。

## 路径说明

目录以中文主题名为主，便于浏览。若旧版编译器或脚本不支持中文路径，或遇到路径过长导致构建失败，可将仓库检出到较短的英文路径（如 `C:\src\my-blog`）后再构建。

## 职业成长页面

仓库公开的是 `docs/职业成长/嵌入式职业成长路线_公开版.html`。原始个人规划页如保存在本地 `docs/职业成长/embedded_career_plan_V3_2026_2029.html`，会被 `.gitignore` 和 MkDocs 排除，不要使用 `git add -f` 将其加入仓库。
