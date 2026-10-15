<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit; ?>
</main>
<footer class="site-footer">
    <?php $footerSettings = argonNoteDisplaySettings(); ?>
    <div class="footer-inner"><a class="footer-brand" href="<?php $this->options->siteUrl(); ?>"><img class="brand-mark" src="<?php $this->options->themeUrl('assets/avatar.jpg'); ?>" alt=""><?php echo htmlspecialchars($footerSettings['name'], ENT_QUOTES, 'UTF-8'); ?></a><span><?php echo htmlspecialchars($footerSettings['tagline'], ENT_QUOTES, 'UTF-8'); ?></span><span class="footer-credit">POWERED BY <a href="https://typecho.org">TYPECHO</a> <i>·</i> © <?php echo date('Y'); ?></span></div>
</footer>
<button class="icon-button back-top" type="button" data-back-top aria-label="返回顶部">↑</button>
<script src="<?php $this->options->themeUrl('assets/prism.js'); ?>?v=20261015-prism-local" defer></script>
<script src="<?php $this->options->themeUrl('assets/main.js'); ?>?v=20261015-player-rail-v2" defer></script>
<?php $this->footer(); ?>
</body>
</html>
