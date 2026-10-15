<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit; $this->need('header.php'); ?>
<section class="page-card"><span class="section-kicker">404</span><h1>这个页面没有找到</h1><p>地址可能已经变更，试试站内搜索或回到首页。</p><a class="button-link" href="<?php $this->options->siteUrl(); ?>">返回首页</a></section>
<?php $this->need('footer.php'); ?>
