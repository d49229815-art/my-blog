<?php
if (!defined('__TYPECHO_ROOT_DIR__') || !isset($entryPost) || !is_array($entryPost)) return;
$entryEscape = static function ($value) { return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); };
$entryDate = (int) ($entryPost['created'] ?? 0);
$entryCover = (string) ($entryPost['cover'] ?? '');
$entrySeries = (string) ($entryPost['series'] ?? '');
?>
<article class="home-stream-entry home-stream-entry-post home-entry-card<?php echo $entryCover !== '' ? ' has-cover' : ''; ?><?php echo !empty($entryPost['_pinned']) ? ' is-pinned' : ''; ?>" itemscope itemtype="https://schema.org/BlogPosting">
    <div class="entry-copy">
        <header class="home-stream-entry-head">
            <p class="home-stream-kind">ARTICLE<?php if ($entrySeries !== ''): ?> <span aria-hidden="true">·</span> <a href="<?php echo $entryEscape(argonNoteSiteUrl('series#' . argonNoteSeriesAnchor($entrySeries))); ?>"><?php echo $entryEscape($entrySeries); ?></a><?php endif; ?></p>
            <time datetime="<?php echo $entryEscape(date('c', $entryDate)); ?>" itemprop="datePublished"><span class="home-stream-post-year"><?php echo date('Y', $entryDate); ?></span><span class="home-stream-post-date"><?php echo strtoupper(date('d M', $entryDate)); ?></span></time>
        </header>
        <h2 class="home-stream-title" itemprop="headline"><a href="<?php echo $entryEscape($entryPost['url']); ?>" itemprop="url"><?php echo $entryEscape($entryPost['title']); ?></a></h2>
        <div class="entry-meta" aria-label="文章信息">
            <?php foreach ($entryPost['categories'] as $entryCategory): ?><a class="entry-meta-category" href="<?php echo $entryEscape(argonNoteCategoryPermalink($entryCategory['slug'], $entryCategory['mid'])); ?>"><?php echo $entryEscape($entryCategory['name']); ?></a><?php endforeach; ?>
            <span><?php echo number_format((int) $entryPost['length']); ?> 字</span>
            <span><?php echo (int) $entryPost['comments']; ?> 条评论</span>
        </div>
        <p class="home-stream-excerpt" itemprop="description"><?php echo $entryEscape($entryPost['excerpt']); ?></p>
        <footer class="home-stream-entry-foot">
            <div class="entry-tag-list"><?php foreach (array_slice($entryPost['tags'], 0, 4) as $entryTag): ?><span># <?php echo $entryEscape($entryTag['name']); ?></span><?php endforeach; ?></div>
            <a href="<?php echo $entryEscape($entryPost['url']); ?>">继续阅读 <span aria-hidden="true">↗</span></a>
        </footer>
    </div>
    <?php if ($entryCover !== ''): ?><a class="entry-cover" href="<?php echo $entryEscape($entryPost['url']); ?>" tabindex="-1" aria-hidden="true"><img src="<?php echo $entryEscape($entryCover); ?>" alt="" loading="lazy" decoding="async"></a><?php endif; ?>
</article>
