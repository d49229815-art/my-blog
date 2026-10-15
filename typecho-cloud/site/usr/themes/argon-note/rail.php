<?php
if (!defined('__TYPECHO_ROOT_DIR__')) exit;
$railStats = argonNoteSidebarStats();
$railOptions = \Widget\Options::alloc();
$railNow = time();
$railYear = (int) date('Y', $railNow);
$railMonth = (int) date('n', $railNow);
$railMonthStart = strtotime(sprintf('%04d-%02d-01', $railYear, $railMonth));
$railOffset = (int) date('N', $railMonthStart) - 1;
$railDayCount = (int) date('t', $railMonthStart);
$railToday = (int) date('j', $railNow);
$railDays = array_flip(argonNoteCalendarDays($railYear, $railMonth));
$railMusicTracks = argonNoteMusicTracks();
$railEscape = static function ($value) { return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); };
?>
<aside class="site-rail" aria-label="站点统计与日历">
    <section class="rail-card rail-stats" aria-labelledby="rail-stats-title">
        <h2 id="rail-stats-title"><span aria-hidden="true"></span>统计</h2>
        <dl>
            <div><dt><span aria-hidden="true">▤</span><a href="<?php $this->options->siteUrl('archives'); ?>">文章</a></dt><dd><?php echo (int) $railStats['articles']; ?></dd></div>
            <div><dt><span aria-hidden="true">▱</span><a href="<?php echo $railEscape(argonNoteCategoryPermalink('feel')); ?>">动态</a></dt><dd><?php echo (int) $railStats['feels']; ?></dd></div>
            <div><dt><span aria-hidden="true">▱</span><a href="<?php $this->options->siteUrl('categories'); ?>">分类</a></dt><dd><?php echo (int) $railStats['categories']; ?></dd></div>
            <div><dt><span aria-hidden="true">#</span><a href="<?php $this->options->siteUrl('tags'); ?>">标签</a></dt><dd><?php echo (int) $railStats['tags']; ?></dd></div>
            <div><dt><span aria-hidden="true">▣</span><a href="<?php $this->options->siteUrl('series'); ?>">系列</a></dt><dd><?php echo (int) $railStats['series']; ?></dd></div>
        </dl>
    </section>

    <section class="rail-card rail-calendar" data-calendar data-calendar-url="<?php echo $railEscape(argonNoteSiteUrl()); ?>" data-calendar-year="<?php echo $railYear; ?>" data-calendar-month="<?php echo $railMonth; ?>" aria-labelledby="rail-calendar-title">
        <h2 id="rail-calendar-title"><span aria-hidden="true"></span>日历</h2>
        <div class="calendar-month"><button type="button" data-calendar-shift="-1" aria-label="上个月">‹</button><strong data-calendar-label><?php echo date('Y年n月', $railMonthStart); ?></strong><button type="button" data-calendar-shift="1" aria-label="下个月">›</button></div>
        <div class="calendar-grid" data-calendar-grid aria-label="发布日历">
            <?php foreach (['一', '二', '三', '四', '五', '六', '日'] as $railWeekday): ?><span class="calendar-weekday"><?php echo $railWeekday; ?></span><?php endforeach; ?>
            <?php for ($i = 0; $i < $railOffset; $i++): ?><span class="calendar-blank" aria-hidden="true"></span><?php endfor; ?>
            <?php for ($day = 1; $day <= $railDayCount; $day++): $railDate = sprintf('%04d-%02d-%02d', $railYear, $railMonth, $day); $railHasPosts = isset($railDays[$day]); ?>
            <a class="calendar-day<?php echo $railHasPosts ? ' has-post' : ''; ?><?php echo $day === $railToday ? ' is-today' : ''; ?>" href="<?php echo $railEscape(argonNoteSiteUrl('archives?date=' . $railDate)); ?>" data-calendar-day="<?php echo $day; ?>" aria-label="<?php echo $railEscape($railDate . ($railHasPosts ? ' 有文章' : '')); ?>"><?php echo $day; ?></a>
            <?php endfor; ?>
        </div>
        <p class="calendar-hint">有文章的日期会标出圆点。</p>
    </section>


    <?php if ($railMusicTracks): ?><section class="rail-card rail-music-player" data-reading-player aria-labelledby="rail-music-title">
        <h2 id="rail-music-title"><span aria-hidden="true"></span>音乐播放器</h2>
        <audio data-reading-audio preload="none"></audio>
        <div class="reading-track-info"><span class="reading-track-note" aria-hidden="true">♫</span><div><strong data-reading-title><?php echo $railEscape($railMusicTracks[0]['title']); ?></strong><small data-reading-time>0:00 / 0:00</small></div></div>
        <input class="reading-seek" type="range" min="0" max="1000" value="0" step="1" data-reading-seek aria-label="播放进度">
        <div class="reading-player-controls"><button type="button" data-reading-prev aria-label="上一首">|◀</button><button class="reading-play-button" type="button" data-reading-toggle aria-label="播放">▶</button><button type="button" data-reading-next aria-label="下一首">▶|</button><label class="reading-volume" aria-label="音量">♪ <input type="range" min="0" max="1" step="0.05" value="0.65" data-reading-volume aria-label="音量"></label><button type="button" data-reading-list-toggle aria-expanded="false" aria-controls="reading-playlist" aria-label="显示播放列表">☷</button></div>
        <ol class="reading-playlist" id="reading-playlist" data-reading-playlist hidden><?php foreach ($railMusicTracks as $trackIndex => $track): ?><li><button type="button" data-reading-track="<?php echo (int) $trackIndex; ?>" data-reading-url="<?php echo $railEscape($track['url']); ?>"<?php echo $trackIndex === 0 ? ' aria-current="true"' : ''; ?>><?php echo $railEscape($track['title']); ?></button></li><?php endforeach; ?></ol>
    </section><?php endif; ?>
</aside>
