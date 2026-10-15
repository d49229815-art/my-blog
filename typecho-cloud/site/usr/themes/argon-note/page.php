<?php
if (!defined('__TYPECHO_ROOT_DIR__')) exit;
$isOwnerConsolePage = $this->is('page', 'owner-console');
$isFriendLinksPage = $this->is('page', 'links');
$isGuestbookPage = $this->is('page', 'guestbook');
$friendLinks = $isFriendLinksPage ? argonNoteFriendLinks() : [];
$friendPageText = trim(strip_tags((string) $this->content));
$friendEscape = static function ($value) { return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); };
if ($isOwnerConsolePage && !argonNoteIsSiteOwner()) {
    header('Location: ' . $this->options->adminUrl, true, 302);
    exit;
}
$this->need('header.php');
?>
<?php if ($isOwnerConsolePage): ?>
<div class="owner-console-page">
    <?php $this->need('owner-panel.php'); ?>
</div>
<?php elseif ($this->is('page', 'archives')): ?>
<?php
$archiveDate = trim((string) ($_GET['date'] ?? ''));
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $archiveDate) || strtotime($archiveDate) === false || date('Y-m-d', strtotime($archiveDate)) !== $archiveDate) $archiveDate = '';
$archiveMode = (string) ($_GET['view'] ?? 'year');
if (!in_array($archiveMode, ['year', 'category', 'tag'], true)) $archiveMode = 'year';
if ($archiveDate !== '') $archiveMode = 'year';
$archiveGroups = [];
$archiveTerms = [];
foreach (argonNotePublishedPostRows(3000) as $archiveRow) {
    if (argonNotePostHasCategory($archiveRow['cid'], 'feel')) continue;
    $rowDate = date('Y-m-d', (int) $archiveRow['created']);
    if ($archiveDate !== '' && $rowDate !== $archiveDate) continue;
    $year = date('Y', (int) $archiveRow['created']);
    $postData = argonNotePostCardData($archiveRow);
    $archiveGroups[$year][] = $postData;
    foreach (array_merge($postData['categories'], $postData['tags']) as $taxonomy) {
        $kind = in_array($taxonomy['slug'] ?? '', array_column($postData['categories'], 'slug'), true) ? 'category' : 'tag';
        if ($kind !== $archiveMode) continue;
        $slug = (string) ($taxonomy['slug'] ?? '');
        if ($slug === '') continue;
        if (!isset($archiveTerms[$slug])) $archiveTerms[$slug] = ['name' => (string) $taxonomy['name'], 'slug' => $slug, 'mid' => (int) $taxonomy['mid'], 'posts' => []];
        $archiveTerms[$slug]['posts'][] = $postData;
    }
}
uasort($archiveTerms, static function ($left, $right) { return strnatcasecmp($left['name'], $right['name']); });
?>
<div class="archive-layout page-layout three-column-layout">
    <?php $this->need('sidebar.php'); ?>
    <section class="archive-column">
        <header class="archive-heading"><span class="section-kicker">ARTICLES / THE JOURNAL</span><h1><?php echo $archiveDate !== '' ? $friendEscape(date('Y年n月j日', strtotime($archiveDate))) : '文章归档'; ?></h1><p><?php echo $archiveDate !== '' ? '这一天的公开文章。' : '按年份、分类或标签浏览公开文章。'; ?><?php if ($archiveDate !== ''): ?> <a href="<?php $this->options->siteUrl('archives'); ?>">查看全部归档</a><?php endif; ?></p></header>
        <?php if (trim(strip_tags((string) $this->content)) !== ''): ?><div class="page-intro article-body"><?php $this->content(); ?></div><?php endif; ?>
        <?php if ($archiveDate === ''): ?><nav class="archive-view-tabs" aria-label="归档查看方式"><?php foreach (['year' => '按年份', 'category' => '按分类', 'tag' => '按标签'] as $modeKey => $modeLabel): ?><a href="<?php echo $friendEscape(argonNoteSiteUrl('archives?view=' . $modeKey)); ?>"<?php echo $archiveMode === $modeKey ? ' aria-current="page"' : ''; ?>><?php echo $archiveMode === $modeKey ? '<span aria-hidden="true">✓</span> ' : ''; ?><?php echo $modeLabel; ?></a><?php endforeach; ?></nav><?php endif; ?>
        <?php if ($archiveMode === 'year' && $archiveGroups): foreach ($archiveGroups as $archiveYear => $archivePosts): ?>
        <details class="archive-year-group" aria-labelledby="archive-year-<?php echo (int) $archiveYear; ?>"<?php echo $archiveDate !== '' ? ' open' : ''; ?>><summary><span><strong id="archive-year-<?php echo (int) $archiveYear; ?>"><?php echo (int) $archiveYear; ?></strong><small><?php echo count($archivePosts); ?> 篇文章</small></span><i aria-hidden="true"></i></summary><div class="feed-list archive-feed-list"><?php foreach ($archivePosts as $entryPost) include __DIR__ . '/entry-card.php'; ?></div></details>
        <?php endforeach; elseif ($archiveMode !== 'year' && $archiveTerms): ?><div class="archive-term-groups"><?php foreach ($archiveTerms as $archiveTerm): ?><details class="archive-year-group archive-term-group"><summary><a href="<?php echo $friendEscape($archiveMode === 'category' ? argonNoteCategoryPermalink($archiveTerm['slug'], $archiveTerm['mid']) : argonNoteTagPermalink($archiveTerm['slug'], $archiveTerm['mid'])); ?>"><?php echo $friendEscape(($archiveMode === 'tag' ? '# ' : '') . $archiveTerm['name']); ?></a><small><?php echo count($archiveTerm['posts']); ?> 篇文章</small><i aria-hidden="true"></i></summary><div class="feed-list archive-feed-list"><?php foreach ($archiveTerm['posts'] as $entryPost) include __DIR__ . '/entry-card.php'; ?></div></details><?php endforeach; ?></div><?php else: ?><div class="empty-note"><?php echo $archiveDate !== '' ? '这一天没有公开文章。' : ($archiveMode === 'year' ? '暂时还没有文章。' : '还没有可供归档的' . ($archiveMode === 'category' ? '分类。' : '标签。')); ?></div><?php endif; ?>
    </section>
    <?php $this->need('rail.php'); ?>
</div>
<?php else: ?>
<?php
$isCategoriesPage = $this->is('page', 'categories');
$isTagsPage = $this->is('page', 'tags');
$isSeriesPage = $this->is('page', 'series');
$isSkillsPage = $this->is('page', 'skills');
$isDevicesPage = $this->is('page', 'devices');
$isSpecialPage = $isCategoriesPage || $isTagsPage || $isSeriesPage || $isSkillsPage || $isDevicesPage || $isGuestbookPage;
$pageKicker = 'A PAGE FROM THE JOURNAL';
if ($isCategoriesPage) $pageKicker = 'JOURNAL / CATEGORIES';
elseif ($isTagsPage) $pageKicker = 'JOURNAL / TAGS';
elseif ($isSeriesPage) $pageKicker = 'JOURNAL / SERIES';
elseif ($isSkillsPage) $pageKicker = 'PROFILE / SKILLS';
elseif ($isDevicesPage) $pageKicker = 'PROFILE / DEVICES';
elseif ($isGuestbookPage) $pageKicker = 'NOTES / GUESTBOOK';
?>
<div class="archive-layout page-layout three-column-layout<?php echo $isGuestbookPage ? ' guestbook-layout' : ''; ?>">
    <?php $this->need('sidebar.php'); ?>
    <?php if ($isGuestbookPage): ?><div class="guestbook-main-column"><?php endif; ?>
    <article class="page-card<?php echo $isSpecialPage ? ' directory-page-card' : ''; ?>" itemscope itemtype="https://schema.org/Article">
        <header class="article-header"><span class="section-kicker"><?php echo $friendEscape($pageKicker); ?></span><h1 itemprop="headline"><?php $this->title(); ?></h1></header>

        <?php if ($isSeriesPage): $seriesGroups = argonNoteSeriesIndex(); ?>
            <?php if (trim(strip_tags((string) $this->content)) !== ''): ?><div class="article-body page-intro" itemprop="articleBody"><?php $this->content(); ?></div><?php else: ?><p class="directory-intro">按主题串起相关记录；在站主工作台编辑文章时填写“所属系列”，该文章就会出现在这里。</p><?php endif; ?>
            <?php if ($seriesGroups): ?><div class="series-index"><?php foreach ($seriesGroups as $seriesGroup): ?><details class="series-group" id="<?php echo $friendEscape($seriesGroup['anchor']); ?>" open><summary><span><?php echo $friendEscape($seriesGroup['name']); ?></span><small><?php echo count($seriesGroup['posts']); ?> 篇文章</small></summary><div class="feed-list series-post-list"><?php foreach ($seriesGroup['posts'] as $entryPost) include __DIR__ . '/entry-card.php'; ?></div></details><?php endforeach; ?></div><?php else: ?><div class="empty-note">还没有建立文章系列。编辑文章并填写系列名称后，这里会自动汇总。</div><?php endif; ?>

        <?php elseif ($isSkillsPage): $skillEntries = argonNoteProfileEntries('skills'); ?>
            <?php if (trim(strip_tags((string) $this->content)) !== ''): ?><div class="article-body page-intro" itemprop="articleBody"><?php $this->content(); ?></div><?php endif; ?>
            <?php if ($skillEntries): ?><div class="profile-entry-grid"><?php foreach ($skillEntries as $skill): if ($skill['name'] === '') continue; ?><article class="profile-entry-card"><span class="profile-entry-mark" aria-hidden="true">✳</span><h2><?php echo $friendEscape($skill['name']); ?></h2><?php if ($skill['type'] !== ''): ?><span class="profile-entry-type"><?php echo $friendEscape($skill['type']); ?></span><?php endif; ?><?php if ($skill['level'] !== ''): ?><span class="profile-entry-level"><?php echo $friendEscape($skill['level']); ?></span><?php endif; ?><?php if ($skill['detail'] !== ''): ?><p><?php echo nl2br($friendEscape($skill['detail'])); ?></p><?php endif; ?></article><?php endforeach; ?></div><?php else: ?><div class="empty-note">站主还没有填写技能资料。登录后前往“站主工作台 → 站点设置”，每组填写技能名称、领域、熟悉程度和掌握情况。</div><?php endif; ?>

        <?php elseif ($isDevicesPage): $deviceEntries = argonNoteProfileEntries('devices'); $deviceGroups = []; foreach ($deviceEntries as $deviceEntry) { if ($deviceEntry['name'] !== '') $deviceGroups[$deviceEntry['category'] ?: '其他'][] = $deviceEntry; } ?>
            <?php if (trim(strip_tags((string) $this->content)) !== ''): ?><div class="article-body page-intro" itemprop="articleBody"><?php $this->content(); ?></div><?php endif; ?>
            <?php if ($deviceGroups): ?>
            <section class="device-browser" data-device-browser aria-label="设备目录">
                <label class="device-search"><span aria-hidden="true">⌕</span><input type="search" data-device-search placeholder="搜索名称、品牌、规格或用途…" aria-label="搜索设备"></label>
                <div class="device-filter-row"><div class="device-filter-list" role="group" aria-label="按设备种类筛选"><button type="button" data-device-filter="all" aria-pressed="true">全部</button><?php foreach ($deviceGroups as $deviceCategory => $categoryDevices): ?><button type="button" data-device-filter="<?php echo $friendEscape($deviceCategory); ?>" aria-pressed="false"><?php echo $friendEscape($deviceCategory); ?></button><?php endforeach; ?></div><span class="device-result-count" data-device-count><?php echo count($deviceEntries); ?> 款设备</span></div>
                <div class="device-category-groups">
                    <?php $deviceGroupIndex = 0; foreach ($deviceGroups as $deviceCategory => $categoryDevices): ?>
                    <details class="device-category-group" data-device-category="<?php echo $friendEscape($deviceCategory); ?>"<?php echo $deviceGroupIndex === 0 ? ' open' : ''; ?>>
                        <summary><span><?php echo $friendEscape($deviceCategory); ?><small><?php echo count($categoryDevices); ?> 项</small></span><i aria-hidden="true"></i></summary>
                        <div class="profile-entry-grid device-entry-grid">
                            <?php foreach ($categoryDevices as $device): $deviceSearchText = strtolower($deviceCategory . ' ' . $device['name'] . ' ' . $device['detail']); ?>
                            <article class="profile-entry-card device-entry-card" data-device-searchable data-device-search="<?php echo $friendEscape($deviceSearchText); ?>"><span class="device-entry-icon" aria-hidden="true"><?php echo $deviceCategory === '开发板' ? '▤' : ($deviceCategory === '移动设备' ? '▯' : ($deviceCategory === '桌面设备' ? '▣' : '⌘')); ?></span><div><h3><?php echo $friendEscape($device['name']); ?></h3><?php if ($device['detail'] !== ''): ?><p><?php echo nl2br($friendEscape($device['detail'])); ?></p><?php endif; ?></div></article>
                            <?php endforeach; ?>
                        </div>
                    </details>
                    <?php $deviceGroupIndex++; endforeach; ?>
                </div>
                <p class="device-empty-note" data-device-empty hidden>没有找到符合条件的设备。</p>
            </section>
            <?php else: ?><div class="empty-note">站主还没有填写设备清单。编辑格式：设备名称 | 种类，例如“BingPi-M2 | 开发板”；其余行写用途、规格和使用情况，设备种类可用调试工具、开发板、桌面设备、移动设备、外设。</div><?php endif; ?>

        <?php elseif ($isCategoriesPage): ?>
            <?php if (trim(strip_tags((string) $this->content)) !== ''): ?><div class="article-body page-intro" itemprop="articleBody"><?php $this->content(); ?></div><?php endif; ?>
            <?php
            $categoryList = [];
            try { $categoryList = \Typecho\Db::get()->fetchAll(\Typecho\Db::get()->select('mid', 'name', 'slug', 'description', 'count', 'parent')->from('table.metas')->where('type = ?', 'category')->where('slug <> ?', 'feel')->order('order', \Typecho\Db::SORT_ASC)); } catch (\Throwable $error) { $categoryList = []; }
            ?>
            <?php if ($categoryList): ?><div class="category-directory-grid"><?php foreach ($categoryList as $category): ?><a class="category-directory-card" href="<?php echo $friendEscape(argonNoteCategoryPermalink($category['slug'], $category['mid'])); ?>"><span class="directory-card-icon" aria-hidden="true">▤</span><span class="category-directory-copy"><strong><?php echo $friendEscape($category['name']); ?></strong><small><?php echo $friendEscape($category['description'] ?: '浏览此分类中的文章'); ?></small></span><span class="directory-card-count"><?php echo (int) $category['count']; ?></span></a><?php endforeach; ?></div><?php else: ?><div class="empty-note">还没有创建文章分类。</div><?php endif; ?>

        <?php elseif ($isTagsPage): ?>
            <?php if (trim(strip_tags((string) $this->content)) !== ''): ?><div class="article-body page-intro" itemprop="articleBody"><?php $this->content(); ?></div><?php endif; ?>
            <?php
            $tagList = [];
            try { $tagList = \Typecho\Db::get()->fetchAll(\Typecho\Db::get()->select('mid', 'name', 'slug', 'count')->from('table.metas')->where('type = ?', 'tag')->order('count', \Typecho\Db::SORT_DESC)->limit(500)); } catch (\Throwable $error) { $tagList = []; }
            ?>
            <?php if ($tagList): ?><div class="tag-directory-cloud"><?php foreach ($tagList as $tag): ?><a href="<?php echo $friendEscape(argonNoteTagPermalink($tag['slug'], $tag['mid'])); ?>"><span># <?php echo $friendEscape($tag['name']); ?></span><small><?php echo (int) $tag['count']; ?></small></a><?php endforeach; ?></div><?php else: ?><div class="empty-note">还没有文章标签。</div><?php endif; ?>

        <?php elseif ($isGuestbookPage): ?>
            <?php if (trim(strip_tags((string) $this->content)) !== ''): ?><div class="article-body page-intro" itemprop="articleBody"><?php $this->content(); ?></div><?php else: ?><p class="directory-intro">欢迎留下建议、交流或打个招呼。请友善留言，评论通过审核后会显示。</p><?php endif; ?>
            <div class="guestbook-callout"><span aria-hidden="true">✎</span><p>无需登录即可留言，默认以“匿名访客”显示。称呼可以修改，不需要填写邮箱或网址；留言经审核后公开。</p></div>

        <?php else: ?>
            <?php if (!$friendLinks || strpos($friendPageText, '目前还没有确认要展示的友情链接。') === false): ?><div class="article-body" id="article-body" itemprop="articleBody"><?php $this->content(); ?></div><?php endif; ?>
            <?php if ($friendLinks): ?>
            <section class="friend-links-grid" aria-label="友情链接">
                <?php foreach ($friendLinks as $friendLink): ?>
                <a class="friend-link-card<?php echo !empty($friendLink['avatar']) ? ' has-avatar' : ''; ?>" href="<?php echo $friendEscape($friendLink['url']); ?>" target="_blank" rel="noopener noreferrer"><?php if (!empty($friendLink['avatar'])): ?><img class="friend-link-avatar" src="<?php echo $friendEscape($friendLink['avatar']); ?>" alt="" loading="lazy" decoding="async"><?php else: ?><span class="friend-link-mark" aria-hidden="true">↗</span><?php endif; ?><div class="friend-link-copy"><h2><?php echo $friendEscape($friendLink['name']); ?></h2><?php if (!empty($friendLink['description'])): ?><p><?php echo $friendEscape($friendLink['description']); ?></p><?php endif; ?><small><?php echo $friendEscape(parse_url($friendLink['url'], PHP_URL_HOST) ?: $friendLink['url']); ?></small></div></a>
                <?php endforeach; ?>
            </section>
            <?php endif; ?>
        <?php endif; ?>
    </article>
    <?php if ($isGuestbookPage): ?>
        <?php if ($this->allow('comment')): ?><div class="guestbook-comments"><?php $this->need('comments.php'); ?></div><?php else: ?><div class="guestbook-comments"><section class="comments"><h2>留言暂时关闭</h2><p class="comment-closed">站主暂时关闭了新留言。</p></section></div><?php endif; ?>
    </div><?php endif; ?>
    <?php $this->need('rail.php'); ?>
</div>
<?php endif; ?>
<?php if (!$isOwnerConsolePage && !$isGuestbookPage && $this->allow('comment')): ?>
<div class="guestbook-comments"><?php $this->need('comments.php'); ?></div>
<?php endif; ?>
<?php $this->need('footer.php'); ?>
