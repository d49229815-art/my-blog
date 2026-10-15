<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit; $this->need('header.php'); $isFeelPost = argonNotePostHasCategory($this->cid, 'feel'); ?>
<div class="article-layout three-column-layout<?php echo $isFeelPost ? ' feel-article-layout' : ''; ?>">
    <?php $this->need('sidebar.php'); ?>
    <div class="article-column">
        <article class="article-card<?php echo $isFeelPost ? ' article-card-feel' : ''; ?>" itemscope itemtype="https://schema.org/BlogPosting">
            <header class="article-header"><span class="section-kicker"><?php echo $isFeelPost ? 'FIELD NOTE' : 'ARTICLE'; ?> / <?php $this->date('Y'); ?></span><h1 itemprop="headline"><?php $this->title(); ?></h1><div class="feed-meta"><time datetime="<?php $this->date('c'); ?>" itemprop="datePublished"><?php $this->date('Y年m月d日'); ?></time><span class="feed-meta-dot"></span><span><?php $this->category(', ', true, '未分类'); ?></span><?php $postSeries = argonNotePostSeries($this->cid); if (!$isFeelPost && $postSeries !== ''): ?><span class="feed-meta-dot"></span><a href="<?php echo htmlspecialchars(argonNoteSiteUrl('series#' . argonNoteSeriesAnchor($postSeries)), ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($postSeries, ENT_QUOTES, 'UTF-8'); ?></a><?php endif; ?><span class="feed-meta-dot"></span><span><?php $this->commentsNum('暂无评论', '1 条评论', '%d 条评论'); ?></span></div></header>
            <div class="article-body" id="article-body" itemprop="articleBody"><?php $this->content(); ?></div>
            <div class="article-tags"><?php $this->tags('', true, ''); ?></div>
        </article>
        <nav class="article-nav" aria-label="相邻文章"><div><small>上一篇</small><strong><?php $this->thePrev('%s', '没有了'); ?></strong></div><div><small>下一篇</small><strong><?php $this->theNext('%s', '没有了'); ?></strong></div></nav>
        <?php $this->need('comments.php'); ?>
    </div>
    <?php $this->need('rail.php'); ?>
</div>
<?php $this->need('footer.php'); ?>
