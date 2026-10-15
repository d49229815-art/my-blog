<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit; ?>
<?php
$stationSettings = argonNoteDisplaySettings();
$stationCounts = argonNoteContentCounts();
$isArticleSidebar = $this->is('post') && !argonNotePostHasCategory($this->cid, 'feel');
?>
<aside class="station-sidebar" id="secondary" role="complementary" aria-label="站点信息">
    <section class="home-station<?php echo $isArticleSidebar ? ' has-mode-tabs' : ''; ?>"<?php echo $isArticleSidebar ? ' aria-label="文章目录与站点概况"' : ' aria-labelledby="home-station-title"'; ?>>
        <?php if ($isArticleSidebar): ?>
        <nav class="sidebar-mode-tabs" role="tablist" aria-label="侧栏内容选择">
            <button type="button" role="tab" aria-selected="true" aria-controls="sidebar-outline-panel" data-sidebar-mode-tab="outline">文章目录</button>
            <button type="button" role="tab" aria-selected="false" aria-controls="sidebar-station-panel" data-sidebar-mode-tab="station" tabindex="-1">站点概况</button>
        </nav>
        <section class="sidebar-outline-panel" id="sidebar-outline-panel" role="tabpanel" data-sidebar-mode-panel="outline" aria-label="文章目录">
            <p class="sidebar-outline-title">本文章节</p>
            <p class="sidebar-outline-empty" data-outline-empty>正在读取文章章节…</p>
            <ol class="sidebar-outline-list" id="outline-list" aria-label="文章章节"></ol>
        </section>
        <div class="sidebar-station-panel" id="sidebar-station-panel" role="tabpanel" data-sidebar-mode-panel="station" hidden>
        <?php endif; ?>
        <div class="home-station-content">
            <header class="home-station-header"><p id="home-station-title">SIDE NOTES / STATION</p></header>
            <div class="home-station-profile">
                <img src="<?php $this->options->themeUrl('assets/avatar.jpg'); ?>" alt="站主头像" width="68" height="68" loading="lazy">
                <div><h2><?php echo htmlspecialchars($stationSettings['name'], ENT_QUOTES, 'UTF-8'); ?></h2><p>个人博客 · 文章与日常记录</p></div>
            </div>
            <div class="home-station-notice"><strong>公告</strong><p><?php echo htmlspecialchars($stationSettings['announcement'], ENT_QUOTES, 'UTF-8'); ?></p></div>
            <dl class="home-station-stats">
                <div><dt>文章</dt><dd><?php echo (int) $stationCounts['articles']; ?></dd></div>
                <div><dt>说说</dt><dd><?php echo (int) $stationCounts['feels']; ?></dd></div>
            </dl>
            <section class="home-station-personal" aria-labelledby="home-station-links-title">
                <p id="home-station-links-title">社交链接</p>
                <ul class="home-station-social-list">
                    <?php foreach (argonNoteSocialLinks() as $stationSocial): $stationSocialExternal = preg_match('/^https?:\/\//i', $stationSocial['url']); ?>
                    <li><a class="home-station-social-link" data-social-icon="<?php echo htmlspecialchars($stationSocial['icon'], ENT_QUOTES, 'UTF-8'); ?>" href="<?php echo htmlspecialchars($stationSocial['url'], ENT_QUOTES, 'UTF-8'); ?>" aria-label="<?php echo htmlspecialchars($stationSocial['name'], ENT_QUOTES, 'UTF-8'); ?>" title="<?php echo htmlspecialchars($stationSocial['name'], ENT_QUOTES, 'UTF-8'); ?>"<?php echo $stationSocialExternal ? ' target="_blank" rel="noopener noreferrer"' : ''; ?>><svg class="station-social-icon" aria-hidden="true" focusable="false"><use href="<?php echo htmlspecialchars(argonNoteThemeAssetUrl('assets/social-icons.svg#icon-' . $stationSocial['icon']), ENT_QUOTES, 'UTF-8'); ?>"></use></svg></a></li>
                    <?php endforeach; ?>
                </ul>
            </section>
        </div>
        <?php if ($isArticleSidebar): ?></div><?php endif; ?>
    </section>
</aside>
