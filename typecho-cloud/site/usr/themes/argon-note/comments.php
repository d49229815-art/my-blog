<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit; ?>
<?php $isGuestbookComments = $this->is('page', 'guestbook'); ?>
<section class="comments" id="comments">
    <?php $this->comments()->to($comments); ?>
    <h2><?php $this->commentsNum($isGuestbookComments ? '暂无公开留言' : '留言', '1 条留言', '%d 条留言'); ?></h2>
    <?php if ($comments->have()): ?><?php $comments->listComments(); ?><?php $comments->pageNav(); ?><?php endif; ?>
    <?php if ($this->allow('comment')): ?>
    <div class="respond" id="<?php $this->respondId(); ?>"><div class="cancel-comment-reply"><?php $comments->cancelReply(); ?></div><h3 id="response">写下留言</h3>
        <?php if ($isGuestbookComments): ?><p class="guestbook-anonymous-note">无需注册或登录。默认称呼为“匿名访客”，也可以改成你喜欢的名字；不收集邮箱和网址。</p><?php endif; ?>
        <form method="post" action="<?php $this->commentUrl(); ?>" id="comment-form" role="form">
            <?php if ($this->user->hasLogin()): ?><p>以 <strong><?php $this->user->screenName(); ?></strong> 的身份留言。</p><?php else: ?>
            <p><label for="author"><?php echo $isGuestbookComments ? '显示称呼' : '称呼'; ?></label><input type="text" name="author" id="author" value="<?php if ($isGuestbookComments) echo '匿名访客'; else $this->remember('author'); ?>" maxlength="150" required></p>
            <?php if (!$isGuestbookComments): ?><p><label for="mail">邮箱（选填）</label><input type="email" name="mail" id="mail" value="<?php $this->remember('mail'); ?>"></p>
            <p><label for="url">网站（选填）</label><input type="url" name="url" id="url" value="<?php $this->remember('url'); ?>"></p><?php endif; ?><?php endif; ?>
            <p><label for="textarea">内容</label><textarea rows="6" name="text" id="textarea" required><?php $this->remember('text'); ?></textarea></p>
            <p><button type="submit">提交留言</button></p>
        </form>
    </div><?php else: ?><p class="comment-closed">这篇文章暂不接受新留言。</p><?php endif; ?>
</section>
