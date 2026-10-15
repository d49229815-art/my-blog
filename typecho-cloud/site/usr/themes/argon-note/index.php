<?php
if (!defined('__TYPECHO_ROOT_DIR__')) exit;
$this->need('header.php');
$displaySettings = argonNoteDisplaySettings();
$contentCounts = argonNoteContentCounts();
$feelUrl = argonNoteCategoryPermalink('feel');
$heroLyricPool = [
    '风从旧窗边经过',
    '把未说完的话收好',
    '日子沿着街道向前',
    '我们在微光里认出彼此',
    '再把此刻留给明天',
    '雨停之后，路面映着远灯',
    '夜色把喧闹慢慢收拢',
    '有些答案，适合交给时间',
    '沿途的风景都有回声',
    '愿每一次出发都不慌张',
    '把寻常日子写成诗',
    '山海之间仍有归途',
    '星光落下时，故事还在继续',
    '旧梦不必回答，清晨自会开门',
    '每一次相逢，都是时间的赠礼',
    '我们向前，也记得来时的光',
    '待春风经过，再和昨天道别',
    '远方很远，脚下也有花开',
];
shuffle($heroLyricPool);
$heroLyrics = array_slice($heroLyricPool, 0, 5);
$heroHeadlinePool = [
    ['把正在发生的，', '留成一段回响。'],
    ['把寻常日子写下来，', '让微光有迹可循。'],
    ['在时间缓慢经过时，', '收藏此刻的回声。'],
    ['记下沿途的风景，', '也记下心里的远方。'],
    ['让日常有处安放，', '让故事继续生长。'],
    ['把未说完的话，', '交给明天慢慢续写。'],
];
$heroHeadline = $heroHeadlinePool[random_int(0, count($heroHeadlinePool) - 1)];
$heroHeadlineJson = htmlspecialchars(json_encode($heroHeadlinePool, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), ENT_QUOTES, 'UTF-8');
$heroMedia = $GLOBALS['argonNoteHeroMedia'] ?? ['light' => [], 'dark' => [], 'background' => argonNoteThemeAssetUrl('assets/home-dusk.webp'), 'custom' => false];
$heroLightImagesJson = htmlspecialchars(json_encode($heroMedia['light'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), ENT_QUOTES, 'UTF-8');
$heroDarkImagesJson = htmlspecialchars(json_encode($heroMedia['dark'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), ENT_QUOTES, 'UTF-8');
$heroBackground = (string) $heroMedia['background'];
$currentHomePage = isset($this->currentPage) ? (int) $this->currentPage : 1;
?>
<?php if ($this->is('index')): ?>
<section class="hero hero-media-pending" aria-labelledby="hero-title" aria-busy="true" data-hero-light-images="<?php echo $heroLightImagesJson; ?>" data-hero-dark-images="<?php echo $heroDarkImagesJson; ?>" data-hero-custom="<?php echo $heroMedia['custom'] ? 'true' : 'false'; ?>">
    <img class="hero-background" src="<?php echo htmlspecialchars($heroBackground, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>" alt="" fetchpriority="high" loading="eager" decoding="async" data-hero-background>
    <div class="hero-inner">
        <div class="hero-copy">
            <h1 id="hero-title" data-typewriter data-hero-phrases="<?php echo $heroHeadlineJson; ?>" aria-label="<?php echo htmlspecialchars(implode('', $heroHeadline), ENT_QUOTES, 'UTF-8'); ?>"><span data-typewriter-line="<?php echo htmlspecialchars($heroHeadline[0], ENT_QUOTES, 'UTF-8'); ?>" aria-hidden="true"><?php echo htmlspecialchars($heroHeadline[0], ENT_QUOTES, 'UTF-8'); ?></span><em data-typewriter-line="<?php echo htmlspecialchars($heroHeadline[1], ENT_QUOTES, 'UTF-8'); ?>" aria-hidden="true"><?php echo htmlspecialchars($heroHeadline[1], ENT_QUOTES, 'UTF-8'); ?><span class="typewriter-caret" aria-hidden="true"></span></em></h1>
            <p class="hero-subtitle"><?php echo htmlspecialchars($displaySettings['tagline'], ENT_QUOTES, 'UTF-8'); ?></p>
        </div>
        <aside class="hero-lyrics" aria-label="首页封面短句">
            <div class="lyrics-viewport"><ol class="lyrics-list" data-rotating-lyrics>
                <?php foreach ($heroLyrics as $lyricIndex => $heroLyric): ?>
                <li<?php echo (int) $lyricIndex === 0 ? ' class="is-current"' : ''; ?>><?php echo htmlspecialchars($heroLyric, ENT_QUOTES, 'UTF-8'); ?></li>
                <?php endforeach; ?>
            </ol></div>
            <p class="lyrics-source"><span aria-hidden="true">—</span><cite>写给时间的一页</cite><span>· <?php echo htmlspecialchars($displaySettings['name'], ENT_QUOTES, 'UTF-8'); ?></span></p>
        </aside>
        <a class="scroll-cue" href="#home-content" aria-label="向下滚动到首页内容区"><span aria-hidden="true">⌄</span></a>
    </div>
</section>
<div class="home-layout" id="home-content">
    <?php $this->need('sidebar.php'); ?>
    <section class="home-stream feed-column" aria-labelledby="updates-title">
        <div class="home-stream-header section-heading">
            <div><span class="section-kicker">FIELD NOTES &amp; ARTICLES</span><h2 id="updates-title">最近更新</h2></div>
            <div class="feed-count"><span><?php echo (int) $contentCounts['articles']; ?></span><small>ARTICLES</small><i></i><span><?php echo (int) $contentCounts['feels']; ?></span><small>NOTES</small></div>
        </div>
        <div class="home-stream-list feed-list">
            <?php
            $feelPosts = argonNoteRecentFeelPosts(3);
            foreach ($feelPosts as $feelIndex => $feelPost):
                $feelTitle = trim((string) $feelPost['title']) ?: '今天的记录';
                $feelSummary = argonNotePlainExcerpt($feelPost['text'], 210);
                $feelDate = (int) $feelPost['created'];
                $feelPermalink = argonNotePostPermalink($feelPost['cid']);
            ?>
            <article class="home-stream-entry home-stream-entry-feel<?php echo (int) $feelIndex === 0 ? ' is-latest' : ''; ?>">
                <time class="home-stream-feel-date" datetime="<?php echo htmlspecialchars(date('c', $feelDate), ENT_QUOTES, 'UTF-8'); ?>"><strong><?php echo date('d', $feelDate); ?></strong><span class="home-stream-feel-month"><small><?php echo strtoupper(date('M', $feelDate)); ?></small><b><?php echo (int) date('n', $feelDate); ?>月</b></span></time>
                <div class="home-stream-feel-copy"><header class="home-stream-entry-head"><p class="home-stream-kind">说说 / FIELD NOTE<?php echo (int) $feelIndex === 0 ? ' · 最新' : ''; ?></p></header><h3 class="home-stream-title"><a href="<?php echo htmlspecialchars($feelPermalink, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($feelTitle, ENT_QUOTES, 'UTF-8'); ?></a></h3><p class="home-stream-note"><?php echo htmlspecialchars($feelSummary, ENT_QUOTES, 'UTF-8'); ?></p></div>
            </article>
            <?php endforeach; if (!$feelPosts): ?><p class="empty-note empty-note-inline">还没有说说记录。</p><?php endif; ?>

            <?php
            $pinnedPosts = [];
            $pinnedIds = $currentHomePage === 1 ? $displaySettings['pinned'] : [];
            foreach ($pinnedIds as $pinnedId) {
                $candidate = argonNoteFindPublishedPost($pinnedId);
                if ($candidate && !argonNotePostHasCategory($candidate['cid'], 'feel')) $pinnedPosts[] = $candidate;
            }
            $pinnedIdSet = array_fill_keys(array_map('intval', $pinnedIds), true);
            ?>
            <div class="home-article-feed" data-home-articles>
            <?php foreach ($pinnedPosts as $pinnedPost): $entryPost = argonNotePostCardData($pinnedPost); $entryPost['_pinned'] = true; include __DIR__ . '/entry-card.php'; endforeach; ?>
            <?php $homeArticleCount = 0; if ($this->have()): while ($this->next()): if (argonNotePostHasCategory($this->cid, 'feel') || ($currentHomePage === 1 && isset($pinnedIdSet[(int) $this->cid]))) continue; $homeArticleCount++; $entryPost = argonNotePostCardData(['cid' => $this->cid, 'title' => $this->title, 'slug' => $this->slug, 'created' => $this->created, 'text' => $this->text, 'commentsNum' => $this->commentsNum]); ?>
            <?php include __DIR__ . '/entry-card.php'; ?>
            <?php endwhile; endif; if ($homeArticleCount === 0 && !$pinnedPosts): ?><div class="empty-note">这里还没有文章，稍后再来看看。</div><?php endif; ?>
            </div>
        </div>
        <nav class="pagination-wrap" aria-label="文章分页"><?php $this->pageNav('← 更新', '更早 →', 1, '…'); ?></nav>
    </section>
    <?php $this->need('rail.php'); ?>
</div>
<?php else: ?>
<?php $isFeelArchive = $this->is('category', 'feel'); ?>
<div class="archive-layout three-column-layout<?php echo $isFeelArchive ? ' feel-archive-layout' : ''; ?>">
    <?php $this->need('sidebar.php'); ?>
    <section class="archive-column">
        <header class="archive-heading"><span class="section-kicker"><?php echo $isFeelArchive ? 'FIELD NOTES / THE STREAM' : 'BROWSE THE JOURNAL'; ?></span><h1><?php echo $isFeelArchive ? '说说' : ''; ?><?php if (!$isFeelArchive) $this->archiveTitle(['category' => '分类：%s', 'search' => '搜索：%s', 'tag' => '标签：%s', 'author' => '作者：%s'], '', '文章'); ?></h1><p><?php echo $isFeelArchive ? '短一点的念头，也值得被好好记下。' : '每一篇记录，都是某个时刻的切片。'; ?></p></header>
        <div class="feed-list archive-feed-list">
        <?php $archiveEntryCount = 0; if ($this->have()): while ($this->next()): if ($isFeelArchive && !argonNotePostHasCategory($this->cid, 'feel')) continue; if (!$isFeelArchive && argonNotePostHasCategory($this->cid, 'feel')) continue; $archiveEntryCount++; ?>
            <?php if ($isFeelArchive): ?>
            <article class="home-stream-entry home-stream-entry-feel archive-feel-entry">
                <time class="home-stream-feel-date" datetime="<?php $this->date('c'); ?>"><strong><?php $this->date('d'); ?></strong><span class="home-stream-feel-month"><small><?php echo strtoupper(date('M', (int) $this->created)); ?></small><b><?php $this->date('n月'); ?></b></span></time>
                <div class="home-stream-feel-copy"><header class="home-stream-entry-head"><p class="home-stream-kind">说说 / FIELD NOTE</p></header><h2 class="home-stream-title"><a href="<?php $this->permalink(); ?>"><?php $this->title(); ?></a></h2><div class="home-stream-note archive-feel-excerpt"><?php $this->excerpt(250, '…'); ?></div><footer class="home-stream-entry-foot"><span><?php $this->date('Y年m月d日'); ?></span><a href="<?php $this->permalink(); ?>">查看记录 <span aria-hidden="true">↗</span></a></footer></div>
            </article>
            <?php else: ?>
            <?php $entryPost = argonNotePostCardData(['cid' => $this->cid, 'title' => $this->title, 'slug' => $this->slug, 'created' => $this->created, 'text' => $this->text, 'commentsNum' => $this->commentsNum]); include __DIR__ . '/entry-card.php'; ?>
            <?php endif; ?>
        <?php endwhile; endif; if ($archiveEntryCount === 0): ?><div class="empty-note"><?php echo $isFeelArchive ? '还没有说说。站主可以从工作台发布第一条。' : '没有找到内容，试试其他关键词。'; ?></div><?php endif; ?>
        </div>
        <nav class="pagination-wrap" aria-label="内容分页"><?php $this->pageNav('← 更新', '更早 →', 1, '…'); ?></nav>
    </section>
    <?php $this->need('rail.php'); ?>
</div>
<?php endif; ?>
<?php $this->need('footer.php'); ?>
