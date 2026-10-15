<?php
if (!defined('__TYPECHO_ROOT_DIR__')) exit;
if (isset($_GET['morrow_calendar'])) {
    $requestedMonth = preg_match('/^\d{4}-\d{2}$/', (string) $_GET['morrow_calendar']) ? explode('-', (string) $_GET['morrow_calendar']) : [];
    $calendarYear = (int) ($requestedMonth[0] ?? 0);
    $calendarMonth = (int) ($requestedMonth[1] ?? 0);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: public, max-age=300');
    echo json_encode(['year' => $calendarYear, 'month' => $calendarMonth, 'days' => argonNoteCalendarDays($calendarYear, $calendarMonth)], JSON_UNESCAPED_UNICODE);
    exit;
}
argonNoteHandleOwnerConsoleAction();
argonNoteHandleDisplaySettingsSave();
$displaySettings = argonNoteDisplaySettings();
$siteName = $displaySettings['name'];
$siteTagline = $displaySettings['tagline'];
$isSiteOwner = argonNoteIsSiteOwner();
$feelUrl = argonNoteCategoryPermalink('feel');
$isFeelCategory = $this->is('category', 'feel');
$isFeelPost = $this->is('post') && argonNotePostHasCategory($this->cid, 'feel');
$isArticleNavActive = ($this->is('post') && !$isFeelPost) || ($this->is('archive') && !$isFeelCategory) || $this->is('page', 'archives') || $this->is('page', 'series') || $this->is('page', 'categories') || $this->is('page', 'tags') || $this->is('search') || ($this->is('category') && !$isFeelCategory) || $this->is('tag');
$isMyNavActive = $this->is('page', 'devices') || $this->is('page', 'skills') || $this->is('page', 'about') || $this->is('page', 'owner-console') || $this->is('page', 'owner-career-plan');
ob_start();
if ($this->is('post') || $this->is('page')) $this->excerpt(150, '…'); else $this->options->description();
$metaDescription = htmlspecialchars(trim(preg_replace('/\\s+/u', ' ', strip_tags(ob_get_clean()))), ENT_QUOTES, 'UTF-8');
ob_start();
if (!$this->is('index')) $this->archiveTitle(['category' => '%s', 'search' => '%s', 'tag' => '%s', 'author' => '%s'], '', '');
$pageTitle = trim(strip_tags(ob_get_clean()));
$onHome = $this->is('index');
$heroLightImages = array_map(static function ($index) { return argonNoteThemeAssetUrl(sprintf('assets/hero-light-%02d.webp', $index)); }, range(1, 10));
$heroDarkImages = array_map(static function ($index) { return argonNoteThemeAssetUrl(sprintf('assets/hero-dark-%02d.webp', $index)); }, range(1, 9));
$heroDarkImages[] = argonNoteThemeAssetUrl('assets/home-dusk.webp');
$heroCustomBackground = argonNoteIsSafeMediaUrl($displaySettings['hero_background'] ?? '') ? $displaySettings['hero_background'] : '';
$heroMode = isset($_COOKIE['morrow-theme']) && in_array($_COOKIE['morrow-theme'], ['light', 'dark'], true) ? $_COOKIE['morrow-theme'] : 'light';
$heroModeImages = $heroMode === 'dark' ? $heroDarkImages : $heroLightImages;
$heroImageIndex = random_int(0, count($heroModeImages) - 1);
$heroBackground = $heroCustomBackground !== '' ? $heroCustomBackground : $heroModeImages[$heroImageIndex];
$heroLightImagesJson = htmlspecialchars(json_encode($heroLightImages, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), ENT_QUOTES, 'UTF-8');
$heroDarkImagesJson = htmlspecialchars(json_encode($heroDarkImages, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), ENT_QUOTES, 'UTF-8');
$GLOBALS['argonNoteHeroMedia'] = ['light' => $heroLightImages, 'dark' => $heroDarkImages, 'background' => $heroBackground, 'custom' => $heroCustomBackground !== ''];
?>
<!doctype html>
<html lang="zh-CN" data-theme="light" data-home="<?php echo $onHome ? 'true' : 'false'; ?>">
<head>
    <meta charset="<?php $this->options->charset(); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#F5F1EA">
    <meta name="description" content="<?php echo htmlspecialchars('把正在发生的，留成一段回响。' . $siteTagline, ENT_QUOTES, 'UTF-8'); ?>">
    <meta property="og:type" content="<?php echo $this->is('post') ? 'article' : 'website'; ?>">
    <meta property="og:title" content="<?php if ($pageTitle) echo htmlspecialchars($pageTitle . ' · ', ENT_QUOTES, 'UTF-8'); ?><?php echo htmlspecialchars($siteName, ENT_QUOTES, 'UTF-8'); ?>">
    <meta property="og:description" content="<?php echo htmlspecialchars($siteTagline, ENT_QUOTES, 'UTF-8'); ?>">
    <meta property="og:url" content="<?php if ($this->is('post') || $this->is('page')) $this->permalink(); elseif ($this->is('category') || $this->is('tag') || $this->is('search')) echo htmlspecialchars($this->request->getRequestUrl(), ENT_QUOTES, 'UTF-8'); elseif ($this->is('archive')) echo htmlspecialchars(argonNoteArchivePermalink(), ENT_QUOTES, 'UTF-8'); else $this->options->siteUrl(); ?>">
    <link rel="icon" type="image/jpeg" href="<?php $this->options->themeUrl('assets/avatar.jpg'); ?>">
    <link rel="apple-touch-icon" href="<?php $this->options->themeUrl('assets/avatar.jpg'); ?>">
    <link rel="alternate" type="application/rss+xml" title="<?php echo htmlspecialchars($siteName, ENT_QUOTES, 'UTF-8'); ?> RSS" href="<?php $this->options->feedUrl(); ?>">
    <?php if ($onHome): ?><link rel="preload" as="image" href="<?php echo htmlspecialchars($heroBackground, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>" fetchpriority="high"><?php endif; ?>
    <script>document.documentElement.classList.add('has-js');try{var t=localStorage.getItem('morrow-theme')||localStorage.getItem('echo-theme');if(t==='light'||t==='dark'){document.documentElement.dataset.theme=t;document.cookie='morrow-theme='+t+'; Max-Age=31536000; Path=/; SameSite=Lax'}else if(window.matchMedia('(prefers-color-scheme: light)').matches)document.documentElement.dataset.theme='light'}catch(e){}</script>
    <title><?php if ($pageTitle) echo htmlspecialchars($pageTitle . ' · ', ENT_QUOTES, 'UTF-8'); ?><?php echo htmlspecialchars($siteName, ENT_QUOTES, 'UTF-8'); ?></title>
    <link rel="stylesheet" href="<?php $this->options->themeUrl('style.css'); ?>?v=20261015-player-rail-v2">
    <link rel="stylesheet" href="<?php $this->options->themeUrl('assets/prism.css'); ?>">
    <?php $this->header(); ?>
</head>
<body class="<?php echo $onHome ? 'is-home' : ''; ?>">
<a class="skip-link" href="#main">跳到正文</a>
<header class="site-header">
    <div class="site-header-inner">
        <div class="header-left">
            <a class="brand" href="<?php $this->options->siteUrl(); ?>" aria-label="回到首页">
                <img class="brand-mark" src="<?php $this->options->themeUrl('assets/avatar.jpg'); ?>" alt="">
                <span class="brand-name"><?php echo htmlspecialchars($siteName, ENT_QUOTES, 'UTF-8'); ?></span>
            </a>
        </div>
        <nav class="top-nav header-center" id="top-nav" aria-label="主导航">
            <a class="<?php echo $onHome ? 'active' : ''; ?>" href="<?php $this->options->siteUrl(); ?>">首页</a>
            <details class="nav-dropdown<?php echo $isArticleNavActive ? ' is-active' : ''; ?>">
                <summary class="nav-dropdown-toggle<?php echo $isArticleNavActive ? ' active' : ''; ?>">文章<span class="nav-dropdown-chevron" aria-hidden="true"></span></summary>
                <div class="nav-dropdown-menu" aria-label="文章栏目">
                    <a class="<?php echo ($this->is('post') && !$isFeelPost) || ($this->is('archive') && !$isFeelCategory) || $this->is('page', 'archives') || $this->is('search') ? 'active' : ''; ?>" href="<?php echo htmlspecialchars(argonNoteArchivePermalink(), ENT_QUOTES, 'UTF-8'); ?>">归档</a>
                    <a class="<?php echo $this->is('page', 'series') ? 'active' : ''; ?>" href="<?php $this->options->siteUrl('series'); ?>">系列</a>
                    <a class="<?php echo $this->is('page', 'categories') || ($this->is('category') && !$isFeelCategory) ? 'active' : ''; ?>" href="<?php $this->options->siteUrl('categories'); ?>">分类</a>
                    <a class="<?php echo $this->is('page', 'tags') || $this->is('tag') ? 'active' : ''; ?>" href="<?php $this->options->siteUrl('tags'); ?>">标签</a>
                </div>
            </details>
            <a class="<?php echo ($isFeelCategory || $isFeelPost) ? 'active' : ''; ?>" href="<?php echo htmlspecialchars($feelUrl, ENT_QUOTES, 'UTF-8'); ?>">说说</a>
            <a class="<?php echo $this->is('page', 'guestbook') ? 'active' : ''; ?>" href="<?php $this->options->siteUrl('guestbook'); ?>">留言板</a>
            <a class="<?php echo $this->is('page', 'links') ? 'active' : ''; ?>" href="<?php $this->options->siteUrl('links'); ?>">友链</a>
            <details class="nav-dropdown nav-my-dropdown<?php echo $isMyNavActive ? ' is-active' : ''; ?>">
                <summary class="nav-dropdown-toggle<?php echo $isMyNavActive ? ' active' : ''; ?>">我的<span class="nav-dropdown-chevron" aria-hidden="true"></span></summary>
                <div class="nav-dropdown-menu" aria-label="个人页面">
                    <a class="<?php echo $this->is('page', 'devices') ? 'active' : ''; ?>" href="<?php $this->options->siteUrl('devices'); ?>">我的设备</a>
                    <a class="<?php echo $this->is('page', 'skills') ? 'active' : ''; ?>" href="<?php $this->options->siteUrl('skills'); ?>">技能</a>
                    <a class="<?php echo $this->is('page', 'about') ? 'active' : ''; ?>" href="<?php $this->options->siteUrl('about'); ?>">关于</a>
            <?php if ($isSiteOwner): ?>
                    <a class="owner-login-link<?php echo $this->is('page', 'owner-console') ? ' active' : ''; ?>" href="<?php $this->options->siteUrl('owner-console'); ?>">站主工作台</a>
                    <a class="owner-private-link<?php echo $this->is('page', 'owner-career-plan') ? ' active' : ''; ?>" href="<?php $this->options->siteUrl('owner-career-plan'); ?>">私密规划</a>
            <?php else: ?>
                    <a class="owner-login-link" href="<?php $this->options->siteUrl('admin/'); ?>">站主登录</a>
            <?php endif; ?>
                </div>
            </details>
        </nav>
        <div class="header-right">
            <div class="header-tools">
                <button class="icon-button" type="button" data-search-toggle aria-label="打开搜索" aria-expanded="false"><span class="search-glyph" aria-hidden="true"></span></button>
                <button class="theme-button" type="button" data-theme-toggle aria-label="切换到暗色主题"><span class="theme-switch-track" aria-hidden="true"><span class="theme-switch-label" data-theme-label>暗</span><span class="theme-switch-knob"></span></span></button>
                <button class="icon-button menu-button" type="button" data-menu-toggle aria-label="打开导航" aria-expanded="false"><span></span><span></span></button>
            </div>
        </div>
    </div>
</header>
<form class="search-popover" method="post" action="<?php $this->options->siteUrl(); ?>" role="search" data-search-panel hidden>
    <div class="quick-search-head"><strong>搜索</strong><span>按 Esc 关闭</span><button type="button" data-search-close aria-label="关闭搜索">×</button></div>
    <label class="sr-only" for="site-search">搜索文章</label>
    <div class="quick-search-field"><input id="site-search" type="search" name="s" placeholder="输入关键词搜索文章…" value="<?php echo htmlspecialchars((string)($_POST['s'] ?? $_GET['s'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"><button type="submit" aria-label="提交搜索">↵</button></div>
</form>
<div class="quick-search-backdrop" data-search-backdrop hidden></div>
<main class="site-main" id="main" data-home="<?php echo $onHome ? 'true' : 'false'; ?>">
