<?php
if (!defined('__TYPECHO_ROOT_DIR__') || !argonNoteIsSiteOwner()) exit;

$ownerSettings = argonNoteDisplaySettings();
$ownerSecurity = \Widget\Security::alloc();
$ownerActionUrl = $ownerSecurity->getTokenUrl($this->request->getRequestUrl());
$ownerEscape = static function ($value) { return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); };
$ownerTabUrl = function ($tab, array $extra = []) {
    $query = array_merge(['tab' => $tab], $extra);
    return argonNoteSiteUrl('owner-console?' . http_build_query($query));
};
$ownerTabs = ['overview', 'write', 'manage', 'comments', 'taxonomy', 'friends', 'settings'];
$ownerTab = (string) ($_GET['tab'] ?? 'overview');
$ownerEdit = !empty($_GET['edit']) ? argonNoteOwnerFindPost((int) $_GET['edit']) : null;
if ($ownerEdit) $ownerTab = 'write';
if (!in_array($ownerTab, $ownerTabs, true)) $ownerTab = 'overview';
$ownerCategories = argonNoteOwnerCategories();
$ownerTags = argonNoteOwnerTags();
$ownerSeriesRows = argonNoteOwnerSeriesRows();
$ownerFeelMid = 0;
foreach ($ownerCategories as $ownerCategory) if ($ownerCategory['slug'] === 'feel') $ownerFeelMid = (int) $ownerCategory['mid'];
$ownerStatusFilter = (string) ($_GET['status'] ?? 'all');
if (!in_array($ownerStatusFilter, ['all', 'published', 'draft', 'hidden', 'trash'], true)) $ownerStatusFilter = 'all';
$ownerSearch = trim((string) ($_GET['q'] ?? ''));
$ownerPostRows = argonNoteOwnerPosts($ownerStatusFilter, $ownerSearch, 200);
$ownerCommentStatus = (string) ($_GET['comment_status'] ?? 'waiting');
if (!in_array($ownerCommentStatus, ['all', 'waiting', 'approved', 'spam'], true)) $ownerCommentStatus = 'waiting';
$ownerCommentSearch = trim((string) ($_GET['comment_q'] ?? ''));
$ownerCommentRows = argonNoteOwnerCommentRows($ownerCommentStatus, $ownerCommentSearch, 200);
$ownerLatestPosts = argonNoteOwnerPosts('all', '', 6);
$ownerCounts = argonNoteContentCounts();
$ownerFriendLinks = argonNoteFriendLinks();
$ownerEditCategories = $ownerEdit ? $ownerEdit['category_ids'] : [(int) \Widget\Options::alloc()->defaultCategory];
$ownerEditTags = $ownerEdit ? implode(', ', $ownerEdit['tags']) : '';
$ownerEditText = $ownerEdit ? $ownerEdit['text'] : '';
$ownerEditMarkdown = $ownerEdit ? $ownerEdit['markdown'] : (bool) \Widget\Options::alloc()->markdown;
$ownerIsFeel = $ownerEdit ? $ownerEdit['is_feel'] : false;
$ownerNoticeMessages = [
    'post_saved' => '文章内容已保存。',
    'post_deleted' => '内容已从回收站永久删除。',
    'post_trashed' => '内容已移入回收站，可以随时恢复。',
    'post_restored' => '内容已恢复。',
    'status_saved' => '内容状态已更新。',
    'comment_approved' => '留言已通过。',
    'comment_waiting' => '留言已移回待审核。',
    'comment_spam' => '留言已标记为垃圾。',
    'comment_deleted' => '留言已删除。',
    'category_added' => '分类已添加。',
    'category_updated' => '分类资料已保存。',
    'category_deleted' => '分类已删除，文章内容保留并已重新归类。',
    'tag_added' => '标签已添加。',
    'tag_updated' => '标签资料已保存。',
    'tag_deleted' => '标签已删除，文章内容保留。',
    'series_updated' => '系列名称已更新，相关内容归属同步调整。',
    'series_deleted' => '系列已删除，文章内容本身保留。',
    'friend_added' => '友情链接已添加。',
    'friend_updated' => '友情链接已更新。',
    'friend_deleted' => '友情链接已移除。',
    'settings_saved' => '站点设置已保存。',
    'invalid_input' => '没有保存：请检查必填项、重复名称或提交内容。',
    'action_error' => '操作没有完成，请刷新后重试。',
];
$ownerNotice = $ownerNoticeMessages[(string) ($_GET['notice'] ?? '')] ?? '';
$ownerDate = static function ($timestamp) { return date('Y-m-d H:i', (int) $timestamp); };
$ownerStatusLabels = ['publish' => '已发布', 'hidden' => '隐藏', 'private' => '私密', 'waiting' => '待审核'];
?>
<section class="owner-console" id="owner-console" data-owner-console data-initial-tab="<?php echo $ownerEscape($ownerTab); ?>">
    <div class="owner-console-card" aria-labelledby="owner-console-title">
        <header class="owner-console-heading">
            <div><span class="section-kicker">MORROW / OWNER DESK</span><h2 id="owner-console-title">站主工作台</h2><p>内容、分类、友链和首页展示都在这里完成。</p></div>
            <a class="owner-console-close" href="<?php $this->options->siteUrl(); ?>" aria-label="返回博客首页">×</a>
        </header>

        <?php if ($ownerNotice): ?><p class="owner-console-save-success" role="status"><?php echo $ownerEscape($ownerNotice); ?></p><?php endif; ?>

        <nav class="owner-console-tabs" role="tablist" aria-label="站主管理功能">
            <?php foreach (['overview' => '工作台', 'write' => '写作', 'manage' => '内容管理', 'comments' => '留言管理', 'taxonomy' => '分类与标签', 'friends' => '友情链接', 'settings' => '站点设置'] as $ownerTabKey => $ownerTabLabel): ?>
            <button class="owner-console-tab" type="button" role="tab" id="owner-tab-<?php echo $ownerEscape($ownerTabKey); ?>" aria-controls="owner-view-<?php echo $ownerEscape($ownerTabKey); ?>" aria-selected="<?php echo $ownerTab === $ownerTabKey ? 'true' : 'false'; ?>" tabindex="<?php echo $ownerTab === $ownerTabKey ? '0' : '-1'; ?>" data-owner-console-tab="<?php echo $ownerEscape($ownerTabKey); ?>"><?php echo $ownerEscape($ownerTabLabel); ?></button>
            <?php endforeach; ?>
        </nav>

        <section class="owner-console-view" id="owner-view-overview" role="tabpanel" aria-labelledby="owner-tab-overview" data-owner-console-view="overview"<?php if ($ownerTab !== 'overview') echo ' hidden'; ?>>
            <div class="owner-dashboard-stats">
                <article><span>文章</span><strong><?php echo (int) $ownerCounts['articles']; ?></strong><small>公开文章</small></article>
                <article><span>说说</span><strong><?php echo (int) $ownerCounts['feels']; ?></strong><small>日常记录</small></article>
                <article><span>分类</span><strong><?php echo count($ownerCategories); ?></strong><small>内容归档</small></article>
                <article><span>标签</span><strong><?php echo count($ownerTags); ?></strong><small>主题索引</small></article>
            </div>
            <div class="owner-dashboard-grid">
                <section class="owner-dashboard-card">
                    <div class="owner-tools-heading"><span class="section-kicker">QUICK START</span><h3>快速开始</h3></div>
                    <div class="owner-dashboard-actions">
                        <button type="button" data-owner-console-jump="write" data-owner-write-kind="article"><strong>写一篇文章</strong><span>记录一段完整内容</span></button>
                        <button type="button" data-owner-console-jump="write" data-owner-write-kind="feel"><strong>发布说说</strong><span>记录日常片段</span></button>
                        <button type="button" data-owner-console-jump="manage"><strong>管理已有内容</strong><span>搜索、编辑、隐藏或删除</span></button>
                        <button type="button" data-owner-console-jump="settings"><strong>调整首页展示</strong><span>名称、公告、GitHub 和置顶</span></button>
                    </div>
                </section>
                <section class="owner-dashboard-card">
                    <div class="owner-tools-heading"><span class="section-kicker">RECENT / JOURNAL</span><h3>最近更新</h3></div>
                    <?php if ($ownerLatestPosts): ?>
                    <ul class="owner-recent-list">
                        <?php foreach ($ownerLatestPosts as $ownerRecent): ?>
                        <li><a href="<?php echo $ownerEscape($ownerTabUrl('write', ['edit' => (int) $ownerRecent['cid']])); ?>"><span><?php echo $ownerEscape($ownerRecent['title'] ?: '未命名内容'); ?></span><time><?php echo $ownerEscape($ownerDate($ownerRecent['modified'])); ?></time></a><small><?php echo !empty($ownerRecent['is_feel']) ? '说说' : ($ownerRecent['type'] === 'post_draft' ? '草稿' : ($ownerStatusLabels[$ownerRecent['status']] ?? '内容')); ?></small></li>
                        <?php endforeach; ?>
                    </ul>
                    <?php else: ?><p class="owner-empty-note">还没有文章，写下第一篇吧。</p><?php endif; ?>
                </section>
            </div>
        </section>

        <section class="owner-console-view" id="owner-view-write" role="tabpanel" aria-labelledby="owner-tab-write" data-owner-console-view="write"<?php if ($ownerTab !== 'write') echo ' hidden'; ?>>
            <div class="owner-panel-intro"><span class="section-kicker">WRITE / EDIT</span><h3><?php echo $ownerEdit ? '编辑内容' : '写一篇新内容'; ?></h3><p>使用 Markdown 编写正文；发布说说时会自动归入“说说”分类。</p></div>
            <form class="owner-native-form owner-write-form" method="post" action="<?php echo $ownerEscape($ownerActionUrl); ?>">
                <input type="hidden" name="owner_action" value="owner_save_post">
                <?php if ($ownerEdit): ?><input type="hidden" name="cid" value="<?php echo (int) $ownerEdit['cid']; ?>"><?php endif; ?>
                <div class="owner-form-grid">
                    <label class="owner-form-wide">标题<input type="text" name="title" maxlength="180" value="<?php echo $ownerEscape($ownerEdit['title'] ?? ''); ?>" placeholder="给这篇内容起个标题" required></label>
                    <label>内容类型<select name="kind" data-owner-write-kind-select><option value="article"<?php echo !$ownerIsFeel ? ' selected' : ''; ?>>文章</option><option value="feel"<?php echo $ownerIsFeel ? ' selected' : ''; ?>>说说</option></select></label>
                    <label>网址缩略名<input type="text" name="slug" value="<?php echo $ownerEscape($ownerEdit['slug'] ?? ''); ?>" placeholder="留空则自动生成"></label>
                    <div class="owner-form-wide owner-editor-field"><span>正文</span><div class="owner-markdown-toolbar" role="toolbar" aria-label="Markdown 快捷格式"><button type="button" data-markdown-insert data-insert-before="# " title="插入二级标题">标题</button><button type="button" data-markdown-insert data-insert-before="**" data-insert-after="**" title="加粗">加粗</button><button type="button" data-markdown-insert data-insert-before="[" data-insert-after="](https://)" title="插入链接">链接</button><button type="button" data-markdown-insert data-insert-before="`" data-insert-after="`" title="插入行内代码">代码</button><button type="button" data-markdown-insert data-insert-before="- " title="插入列表项">列表</button></div><textarea name="text" rows="18" data-owner-markdown-editor placeholder="从这里开始写……" required><?php echo $ownerEscape($ownerEditText); ?></textarea></div>
                    <fieldset class="owner-form-wide owner-category-picker" data-owner-category-picker><legend>分类</legend><p class="owner-category-help">修改已有文章时，取消原分类并勾选正确分类，再点“保存修改”；说说固定归入“说说”分类。</p><div class="owner-check-grid">
                        <?php foreach ($ownerCategories as $ownerCategory): $ownerCategoryId = (int) $ownerCategory['mid']; $ownerIsFeelCategory = $ownerCategory['slug'] === 'feel'; ?>
                        <label data-owner-category-item data-feel-category="<?php echo $ownerIsFeelCategory ? 'true' : 'false'; ?>"<?php if ($ownerIsFeelCategory && !$ownerIsFeel) echo ' hidden'; ?>><input type="checkbox" name="category[]" value="<?php echo $ownerCategoryId; ?>"<?php echo in_array($ownerCategoryId, $ownerEditCategories, true) ? ' checked' : ''; ?>><span><?php echo $ownerEscape($ownerCategory['name']); ?></span></label>
                        <?php endforeach; ?>
                    </div></fieldset>
                    <label>所属系列<input type="text" name="series" maxlength="80" value="<?php echo $ownerEscape($ownerEdit['series'] ?? ''); ?>" placeholder="例如：嵌入式入门系列"><small>同名系列会自动汇总到“系列”页面。</small></label>
                    <label>标签<input type="text" name="tags" value="<?php echo $ownerEscape($ownerEditTags); ?>" placeholder="用逗号分隔，例如：嵌入式, Linux"></label>
                    <label>发布状态<select name="visibility"><option value="publish"<?php echo ($ownerEdit['status'] ?? '') === 'publish' ? ' selected' : ''; ?>>公开</option><option value="hidden"<?php echo ($ownerEdit['status'] ?? '') === 'hidden' ? ' selected' : ''; ?>>隐藏</option><option value="private"<?php echo ($ownerEdit['status'] ?? '') === 'private' ? ' selected' : ''; ?>>私密</option><option value="waiting"<?php echo ($ownerEdit['status'] ?? '') === 'waiting' ? ' selected' : ''; ?>>待审核</option></select></label>
                    <label class="owner-checkbox-label"><input type="checkbox" name="allow_comment" value="1"<?php echo ($ownerEdit['allowComment'] ?? \Widget\Options::alloc()->defaultAllowComment) ? ' checked' : ''; ?>>允许评论</label>
                    <label class="owner-checkbox-label"><input type="checkbox" name="markdown" value="1"<?php echo $ownerEditMarkdown ? ' checked' : ''; ?>>按 Markdown 渲染</label>
                </div>
                <div class="owner-form-actions"><a class="owner-secondary-button" href="<?php echo $ownerEscape($ownerTabUrl('manage')); ?>">返回内容管理</a><?php if (!$ownerEdit || $ownerEdit['type'] === 'post_draft'): ?><button class="owner-secondary-button" type="submit" name="intent" value="draft">保存草稿</button><?php endif; ?><button class="owner-save-button" type="submit" name="intent" value="publish"><?php echo $ownerEdit ? '保存修改' : '发布内容'; ?></button></div>
            </form>
        </section>

        <section class="owner-console-view" id="owner-view-manage" role="tabpanel" aria-labelledby="owner-tab-manage" data-owner-console-view="manage"<?php if ($ownerTab !== 'manage') echo ' hidden'; ?>>
            <div class="owner-panel-intro"><span class="section-kicker">CONTENT / LIBRARY</span><h3>内容管理</h3><p>在一处查找文章、说说和草稿。</p></div>
            <nav class="owner-filter-tabs" aria-label="内容状态">
                <?php foreach (['all' => '全部', 'published' => '已发布', 'draft' => '草稿', 'hidden' => '隐藏与私密', 'trash' => '回收站'] as $ownerFilter => $ownerFilterLabel): ?>
                <a class="<?php echo $ownerStatusFilter === $ownerFilter ? 'active' : ''; ?>" href="<?php echo $ownerEscape($ownerTabUrl('manage', ['status' => $ownerFilter, 'q' => $ownerSearch])); ?>"><?php echo $ownerEscape($ownerFilterLabel); ?></a>
                <?php endforeach; ?>
            </nav>
            <form class="owner-search-form" method="get" action="<?php echo $ownerEscape(argonNoteSiteUrl('owner-console')); ?>"><input type="hidden" name="tab" value="manage"><input type="hidden" name="status" value="<?php echo $ownerEscape($ownerStatusFilter); ?>"><input type="search" name="q" value="<?php echo $ownerEscape($ownerSearch); ?>" placeholder="搜索标题或正文"><button class="owner-secondary-button" type="submit">搜索</button></form>
            <?php if ($ownerPostRows): ?>
            <div class="owner-content-list">
                <?php foreach ($ownerPostRows as $ownerRow): $ownerIsDraft = $ownerRow['type'] === 'post_draft'; $ownerLabel = $ownerStatusFilter === 'trash' ? '回收站' : ($ownerIsDraft ? '草稿' : (!empty($ownerRow['is_feel']) ? '说说' : ($ownerStatusLabels[$ownerRow['status']] ?? '内容'))); ?>
                <article class="owner-content-card">
                    <div class="owner-content-main"><div class="owner-content-meta"><span class="owner-content-badge<?php echo !empty($ownerRow['is_feel']) ? ' is-feel' : ''; ?>"><?php echo $ownerEscape($ownerLabel); ?></span><time><?php echo $ownerEscape($ownerDate($ownerRow['modified'])); ?></time></div><h4><?php echo $ownerEscape($ownerRow['title'] ?: '未命名内容'); ?></h4><p><?php echo $ownerEscape(implode(' · ', $ownerRow['categories']) ?: '未分类'); ?><?php if ($ownerRow['tags']): ?>　/　<?php echo $ownerEscape(implode('、', $ownerRow['tags'])); ?><?php endif; ?><?php if (!$ownerIsDraft): ?>　/　评论 <?php echo (int) $ownerRow['commentsNum']; ?><?php endif; ?></p></div>
                    <div class="owner-content-actions">
                        <?php if ($ownerStatusFilter === 'trash'): ?>
                        <form method="post" action="<?php echo $ownerEscape($ownerActionUrl); ?>"><input type="hidden" name="owner_action" value="owner_restore_post"><input type="hidden" name="cid" value="<?php echo (int) $ownerRow['cid']; ?>"><button class="owner-secondary-button" type="submit">恢复</button></form>
                        <form method="post" action="<?php echo $ownerEscape($ownerActionUrl); ?>" data-owner-confirm="永久删除后无法恢复，文章正文、评论和关联记录都会删除；附件文件会保留。确定继续？"><input type="hidden" name="owner_action" value="owner_delete_post"><input type="hidden" name="cid" value="<?php echo (int) $ownerRow['cid']; ?>"><button class="owner-danger-button" type="submit">永久删除</button></form>
                        <?php else: ?>
                        <a class="owner-secondary-button" href="<?php echo $ownerEscape($ownerTabUrl('write', ['edit' => (int) $ownerRow['cid']])); ?>">编辑</a>
                        <?php if (!$ownerIsDraft): ?><form method="post" action="<?php echo $ownerEscape($ownerActionUrl); ?>"><input type="hidden" name="owner_action" value="owner_change_post_status"><input type="hidden" name="cid" value="<?php echo (int) $ownerRow['cid']; ?>"><input type="hidden" name="status" value="<?php echo $ownerRow['status'] === 'publish' ? 'hidden' : 'publish'; ?>"><button class="owner-secondary-button" type="submit"><?php echo $ownerRow['status'] === 'publish' ? '隐藏' : '公开'; ?></button></form><?php endif; ?>
                        <form method="post" action="<?php echo $ownerEscape($ownerActionUrl); ?>" data-owner-confirm="移入回收站后仍可恢复，确认继续？"><input type="hidden" name="owner_action" value="owner_move_post_to_trash"><input type="hidden" name="cid" value="<?php echo (int) $ownerRow['cid']; ?>"><button class="owner-danger-button" type="submit">移入回收站</button></form>
                        <?php endif; ?>
                    </div>
                </article>
                <?php endforeach; ?>
            </div>
            <?php else: ?><p class="owner-empty-note">没有找到符合条件的内容。</p><?php endif; ?>
        </section>

        <section class="owner-console-view" id="owner-view-comments" role="tabpanel" aria-labelledby="owner-tab-comments" data-owner-console-view="comments"<?php if ($ownerTab !== 'comments') echo ' hidden'; ?>>
            <div class="owner-panel-intro"><span class="section-kicker">COMMUNITY / MODERATION</span><h3>留言管理</h3><p>审核公开留言、处理垃圾内容，也可以打开原文继续回复。</p></div>
            <nav class="owner-filter-tabs" aria-label="留言状态">
                <?php foreach (['waiting' => '待审核', 'approved' => '已通过', 'spam' => '垃圾', 'all' => '全部'] as $ownerCommentFilter => $ownerCommentFilterLabel): ?>
                <a class="<?php echo $ownerCommentStatus === $ownerCommentFilter ? 'active' : ''; ?>" href="<?php echo $ownerEscape($ownerTabUrl('comments', ['comment_status' => $ownerCommentFilter, 'comment_q' => $ownerCommentSearch])); ?>"><?php echo $ownerEscape($ownerCommentFilterLabel); ?></a>
                <?php endforeach; ?>
            </nav>
            <form class="owner-search-form" method="get" action="<?php echo $ownerEscape(argonNoteSiteUrl('owner-console')); ?>"><input type="hidden" name="tab" value="comments"><input type="hidden" name="comment_status" value="<?php echo $ownerEscape($ownerCommentStatus); ?>"><input type="search" name="comment_q" value="<?php echo $ownerEscape($ownerCommentSearch); ?>" placeholder="搜索留言、称呼或文章标题"><button class="owner-secondary-button" type="submit">搜索</button></form>
            <?php if ($ownerCommentRows): ?>
            <div class="owner-comment-list">
                <?php foreach ($ownerCommentRows as $ownerComment):
                    $ownerCommentStatusLabels = ['approved' => '已通过', 'waiting' => '待审核', 'spam' => '垃圾'];
                    $ownerCommentUrl = $ownerComment['contentType'] === 'post'
                        ? argonNotePostPermalink((int) $ownerComment['cid'])
                        : \Typecho\Router::url('page', ['slug' => (string) $ownerComment['contentSlug'], 'cid' => (int) $ownerComment['cid']], $this->options->index);
                    if ($ownerCommentUrl === '#') $ownerCommentUrl = argonNoteSiteUrl(rawurlencode((string) $ownerComment['contentSlug']));
                    $ownerCommentText = (string) $ownerComment['text'];
                    if (function_exists('mb_strlen') && mb_strlen($ownerCommentText, 'UTF-8') > 1200) $ownerCommentText = mb_substr($ownerCommentText, 0, 1200, 'UTF-8') . '…';
                ?>
                <article class="owner-comment-card">
                    <header><div><strong><?php echo $ownerEscape($ownerComment['author'] ?: '匿名访客'); ?></strong><span class="owner-comment-status is-<?php echo $ownerEscape($ownerComment['status']); ?>"><?php echo $ownerEscape($ownerCommentStatusLabels[$ownerComment['status']] ?? $ownerComment['status']); ?></span><?php if ((int) $ownerComment['parent'] > 0): ?><small>回复留言 #<?php echo (int) $ownerComment['parent']; ?></small><?php endif; ?></div><time><?php echo $ownerEscape($ownerDate($ownerComment['created'])); ?></time></header>
                    <p class="owner-comment-source">来自：<a href="<?php echo $ownerEscape($ownerCommentUrl . '#comments'); ?>" target="_blank" rel="noopener noreferrer"><?php echo $ownerEscape($ownerComment['title'] ?: '未命名内容'); ?> ↗</a></p>
                    <div class="owner-comment-text"><?php echo nl2br($ownerEscape($ownerCommentText)); ?></div>
                    <div class="owner-comment-actions">
                        <?php if ($ownerComment['status'] !== 'approved'): ?><form method="post" action="<?php echo $ownerEscape($ownerActionUrl); ?>"><input type="hidden" name="owner_action" value="owner_change_comment_status"><input type="hidden" name="coid" value="<?php echo (int) $ownerComment['coid']; ?>"><input type="hidden" name="status" value="approved"><button class="owner-secondary-button" type="submit">通过</button></form><?php endif; ?>
                        <?php if ($ownerComment['status'] !== 'waiting'): ?><form method="post" action="<?php echo $ownerEscape($ownerActionUrl); ?>"><input type="hidden" name="owner_action" value="owner_change_comment_status"><input type="hidden" name="coid" value="<?php echo (int) $ownerComment['coid']; ?>"><input type="hidden" name="status" value="waiting"><button class="owner-secondary-button" type="submit">待审核</button></form><?php endif; ?>
                        <?php if ($ownerComment['status'] !== 'spam'): ?><form method="post" action="<?php echo $ownerEscape($ownerActionUrl); ?>"><input type="hidden" name="owner_action" value="owner_change_comment_status"><input type="hidden" name="coid" value="<?php echo (int) $ownerComment['coid']; ?>"><input type="hidden" name="status" value="spam"><button class="owner-secondary-button" type="submit">标记垃圾</button></form><?php endif; ?>
                        <form method="post" action="<?php echo $ownerEscape($ownerActionUrl); ?>" data-owner-confirm="删除后无法恢复，确定删除这条留言？"><input type="hidden" name="owner_action" value="owner_delete_comment"><input type="hidden" name="coid" value="<?php echo (int) $ownerComment['coid']; ?>"><button class="owner-danger-button" type="submit">删除</button></form>
                    </div>
                </article>
                <?php endforeach; ?>
            </div>
            <?php else: ?><p class="owner-empty-note">这个状态下没有留言。</p><?php endif; ?>
        </section>

        <section class="owner-console-view" id="owner-view-taxonomy" role="tabpanel" aria-labelledby="owner-tab-taxonomy" data-owner-console-view="taxonomy"<?php if ($ownerTab !== 'taxonomy') echo ' hidden'; ?>>
            <div class="owner-panel-intro"><span class="section-kicker">CONTENT / ORGANIZE</span><h3>分类与标签</h3><p>新建内容时可以直接使用这些分类和标签。</p></div>
            <div class="owner-management-grid">
                <section class="owner-management-card">
                    <h4>新增分类</h4>
                    <form class="owner-native-form" method="post" action="<?php echo $ownerEscape($ownerActionUrl); ?>"><input type="hidden" name="owner_action" value="owner_add_category"><label>分类名称<input type="text" name="name" maxlength="64" required></label><label>网址缩略名<input type="text" name="slug" placeholder="留空自动生成"></label><label>分类说明<textarea name="description" rows="2"></textarea></label><button class="owner-save-button" type="submit">添加分类</button></form>
                    <h4 class="owner-list-heading">现有分类 <small><?php echo count($ownerCategories); ?></small></h4>
                    <div class="owner-taxonomy-admin-list">
                    <?php foreach ($ownerCategories as $ownerCategory): $ownerCategoryId = (int) $ownerCategory['mid']; $ownerCategoryProtected = $ownerCategory['slug'] === 'feel' || $ownerCategoryId === (int) \Widget\Options::alloc()->defaultCategory; ?>
                        <article class="owner-taxonomy-admin-row">
                            <form class="owner-taxonomy-edit-form" method="post" action="<?php echo $ownerEscape($ownerActionUrl); ?>">
                                <input type="hidden" name="owner_action" value="owner_update_category"><input type="hidden" name="mid" value="<?php echo $ownerCategoryId; ?>">
                                <div class="owner-taxonomy-edit-fields"><label>名称<input type="text" name="name" maxlength="64" value="<?php echo $ownerEscape($ownerCategory['name']); ?>" required></label><label>网址缩略名<input type="text" name="slug" value="<?php echo $ownerEscape($ownerCategory['slug']); ?>"<?php echo $ownerCategory['slug'] === 'feel' ? ' readonly' : ' required'; ?>></label><label class="owner-form-wide">说明<input type="text" name="description" maxlength="200" value="<?php echo $ownerEscape($ownerCategory['description']); ?>"></label></div>
                                <div class="owner-taxonomy-row-actions"><span><?php echo (int) $ownerCategory['count']; ?> 篇<?php if ($ownerCategory['slug'] === 'feel'): ?> · 说说专用<?php endif; ?></span><button class="owner-secondary-button" type="submit">保存</button></div>
                            </form>
                            <?php if ($ownerCategoryProtected): ?><small class="owner-protected-label"><?php echo $ownerCategory['slug'] === 'feel' ? '系统分类' : '默认分类'; ?></small><?php else: ?><form method="post" action="<?php echo $ownerEscape($ownerActionUrl); ?>" data-owner-confirm="删除分类不会删除文章；若文章没有其他分类，会转入默认分类。确定删除“<?php echo $ownerEscape($ownerCategory['name']); ?>”？"><input type="hidden" name="owner_action" value="owner_delete_category"><input type="hidden" name="mid" value="<?php echo $ownerCategoryId; ?>"><button class="owner-danger-button" type="submit">删除</button></form><?php endif; ?>
                        </article>
                    <?php endforeach; ?>
                    </div>
                </section>
                <section class="owner-management-card">
                    <h4>新增标签</h4>
                    <form class="owner-native-form" method="post" action="<?php echo $ownerEscape($ownerActionUrl); ?>"><input type="hidden" name="owner_action" value="owner_add_tag"><label>标签名称<input type="text" name="name" maxlength="64" required></label><button class="owner-save-button" type="submit">添加标签</button></form>
                    <h4 class="owner-list-heading">现有标签 <small><?php echo count($ownerTags); ?></small></h4>
                    <div class="owner-taxonomy-admin-list owner-tag-admin-list">
                    <?php foreach ($ownerTags as $ownerTag): $ownerTagId = (int) $ownerTag['mid']; ?>
                        <article class="owner-taxonomy-admin-row">
                            <form class="owner-taxonomy-edit-form" method="post" action="<?php echo $ownerEscape($ownerActionUrl); ?>">
                                <input type="hidden" name="owner_action" value="owner_update_tag"><input type="hidden" name="mid" value="<?php echo $ownerTagId; ?>">
                                <div class="owner-taxonomy-edit-fields"><label>名称<input type="text" name="name" maxlength="64" value="<?php echo $ownerEscape($ownerTag['name']); ?>" required></label><label>网址缩略名<input type="text" name="slug" value="<?php echo $ownerEscape($ownerTag['slug']); ?>" required></label></div>
                                <div class="owner-taxonomy-row-actions"><span><?php echo (int) $ownerTag['count']; ?> 篇文章</span><button class="owner-secondary-button" type="submit">保存</button></div>
                            </form>
                            <form method="post" action="<?php echo $ownerEscape($ownerActionUrl); ?>" data-owner-confirm="删除标签会清除文章上的这个标签，但不会删除文章。确定删除“<?php echo $ownerEscape($ownerTag['name']); ?>”？"><input type="hidden" name="owner_action" value="owner_delete_tag"><input type="hidden" name="mid" value="<?php echo $ownerTagId; ?>"><button class="owner-danger-button" type="submit">删除</button></form>
                        </article>
                    <?php endforeach; ?>
                    </div>
                </section>
                <section class="owner-management-card">
                    <h4>系列管理</h4><p class="owner-form-hint">系列由文章中的“所属系列”自动建立。这里可以统一改名或移除系列归属，文章正文不会受影响。</p>
                    <?php if ($ownerSeriesRows): ?><div class="owner-taxonomy-admin-list owner-series-admin-list">
                    <?php foreach ($ownerSeriesRows as $ownerSeries): ?>
                        <article class="owner-taxonomy-admin-row">
                            <form class="owner-taxonomy-edit-form" method="post" action="<?php echo $ownerEscape($ownerActionUrl); ?>">
                                <input type="hidden" name="owner_action" value="owner_update_series"><input type="hidden" name="series_name" value="<?php echo $ownerEscape($ownerSeries['name']); ?>">
                                <div class="owner-taxonomy-edit-fields"><label class="owner-form-wide">系列名称<input type="text" name="new_name" maxlength="80" value="<?php echo $ownerEscape($ownerSeries['name']); ?>" required></label></div>
                                <div class="owner-taxonomy-row-actions"><span><?php echo (int) $ownerSeries['count']; ?> 篇内容</span><button class="owner-secondary-button" type="submit">改名</button></div>
                            </form>
                            <form method="post" action="<?php echo $ownerEscape($ownerActionUrl); ?>" data-owner-confirm="移除系列只会清除文章的系列归属，不会删除文章。确定移除“<?php echo $ownerEscape($ownerSeries['name']); ?>”？"><input type="hidden" name="owner_action" value="owner_delete_series"><input type="hidden" name="series_name" value="<?php echo $ownerEscape($ownerSeries['name']); ?>"><button class="owner-danger-button" type="submit">移除</button></form>
                        </article>
                    <?php endforeach; ?></div><?php else: ?><p class="owner-empty-note">还没有系列。编辑文章并填写系列名称后，会自动显示在这里。</p><?php endif; ?>
                </section>
            </div>
        </section>

        <section class="owner-console-view" id="owner-view-friends" role="tabpanel" aria-labelledby="owner-tab-friends" data-owner-console-view="friends"<?php if ($ownerTab !== 'friends') echo ' hidden'; ?>>
            <div class="owner-panel-intro"><span class="section-kicker">PEOPLE / LINKS</span><h3>友情链接</h3><p>添加后会显示在博客的“友链”页面。</p></div>
            <section class="owner-management-card owner-friend-form-card"><h4>添加友链</h4><form class="owner-native-form" method="post" enctype="multipart/form-data" action="<?php echo $ownerEscape($ownerActionUrl); ?>"><input type="hidden" name="owner_action" value="owner_add_friend"><div class="owner-form-grid"><label>站点名称<input type="text" name="name" maxlength="64" required></label><label>站点网址<input type="url" name="url" placeholder="https://example.com" required></label><label class="owner-form-wide">一句介绍<input type="text" name="description" maxlength="180"></label><label>头像图片链接<input type="url" name="avatar_url" placeholder="可留空"></label><label>上传头像<input type="file" name="avatar_image" accept="image/jpeg,image/png,image/webp,image/gif"><small>JPG、PNG、WebP 或 GIF，最多 5 MB</small></label></div><button class="owner-save-button" type="submit">添加友情链接</button></form></section>
            <?php if ($ownerFriendLinks): ?><div class="owner-friend-admin-list"><?php foreach ($ownerFriendLinks as $ownerLink): ?><article class="owner-friend-admin-card"><form class="owner-friend-edit-form" method="post" enctype="multipart/form-data" action="<?php echo $ownerEscape($ownerActionUrl); ?>"><input type="hidden" name="owner_action" value="owner_update_friend"><input type="hidden" name="id" value="<?php echo $ownerEscape($ownerLink['id']); ?>"><div class="owner-friend-edit-fields"><label>站点名称<input type="text" name="name" maxlength="64" value="<?php echo $ownerEscape($ownerLink['name']); ?>" required></label><label>站点网址<input type="url" name="url" value="<?php echo $ownerEscape($ownerLink['url']); ?>" required></label><label class="owner-form-wide">一句介绍<input type="text" name="description" maxlength="180" value="<?php echo $ownerEscape($ownerLink['description'] ?? ''); ?>"></label><label>头像图片链接<input type="url" name="avatar_url" value="<?php echo $ownerEscape($ownerLink['avatar'] ?? ''); ?>" placeholder="留空保留现有头像"></label><label>更换头像<input type="file" name="avatar_image" accept="image/jpeg,image/png,image/webp,image/gif"><small>选择新图片会覆盖链接字段</small></label><label class="owner-avatar-remove"><span>移除现有头像</span><input type="checkbox" name="remove_avatar" value="1"></label></div><div class="owner-friend-edit-actions"><a href="<?php echo $ownerEscape($ownerLink['url']); ?>" target="_blank" rel="noopener noreferrer">打开链接 ↗</a><button class="owner-secondary-button" type="submit">保存</button></div></form><form method="post" action="<?php echo $ownerEscape($ownerActionUrl); ?>" data-owner-confirm="移除这个友情链接？"><input type="hidden" name="owner_action" value="owner_delete_friend"><input type="hidden" name="id" value="<?php echo $ownerEscape($ownerLink['id']); ?>"><button class="owner-danger-button" type="submit">移除</button></form></article><?php endforeach; ?></div><?php else: ?><p class="owner-empty-note">还没有添加友情链接。</p><?php endif; ?>
        </section>

        <section class="owner-console-view" id="owner-view-settings" role="tabpanel" aria-labelledby="owner-tab-settings" data-owner-console-view="settings"<?php if ($ownerTab !== 'settings') echo ' hidden'; ?>>
            <div class="owner-panel-intro"><span class="section-kicker">SITE / APPEARANCE</span><h3>站点设置</h3><p>这些设置会同步到博客标题、首页介绍和侧栏。</p></div>
            <form class="owner-native-form owner-display-form" method="post" enctype="multipart/form-data" action="<?php echo $ownerEscape($ownerActionUrl); ?>">
                <div class="owner-form-grid">
                    <label>网站名称<input name="site_name" maxlength="28" value="<?php echo $ownerEscape($ownerSettings['name']); ?>" required></label>
                    <label>首页副标题<input name="site_tagline" maxlength="120" value="<?php echo $ownerEscape($ownerSettings['tagline']); ?>" required></label>
                    <input type="hidden" name="github_url" value="<?php echo $ownerEscape($ownerSettings['github']); ?>">
                    <label class="owner-form-wide">侧栏社交链接<textarea name="social_links" maxlength="10000" rows="5" placeholder="每行：名称 | 链接 | 图标；图标可用 github、rss、gitee、gitlab、bilibili、csdn、mail、website。RSS 使用 @feed。&#10;GitHub | https://github.com/用户名 | github&#10;RSS 订阅 | @feed | rss&#10;Gitee | https://gitee.com/用户名 | gitee"><?php echo $ownerEscape($ownerSettings['social_links']); ?></textarea><small>会以紧凑图标显示；名称会作为悬停提示和屏幕阅读器说明。</small></label>
                    <label class="owner-form-wide">首页公告<textarea name="announcement" maxlength="240" rows="3"><?php echo $ownerEscape($ownerSettings['announcement']); ?></textarea></label>
                    <fieldset class="owner-form-wide owner-pin-fieldset"><legend>置顶文章</legend><p class="owner-form-hint">可多选；所选文章会显示在首页文章列表最前方，顺序按此列表由新到旧。置顶标记也会出现在文章卡片上。</p><div class="owner-pin-list"><?php $ownerPinnedIds = $ownerSettings['pinned']; foreach (argonNotePublishedPosts(200) as $ownerPost): if (argonNotePostHasCategory($ownerPost['cid'], 'feel')) continue; ?><label><input type="checkbox" name="pinned_posts[]" value="<?php echo (int) $ownerPost['cid']; ?>"<?php echo in_array((int) $ownerPost['cid'], $ownerPinnedIds, true) ? ' checked' : ''; ?>><span><?php echo $ownerEscape($ownerPost['title'] . ' · #' . $ownerPost['cid']); ?></span></label><?php endforeach; ?></div></fieldset>
                    <label>技能清单<textarea name="skills" maxlength="20000" rows="10" placeholder="每组一项：名称 | 领域 | 熟悉程度，然后填写多行掌握情况；用 --- 分隔不同技能。&#10;C 语言 | 嵌入式 | 熟练使用基础语法&#10;已掌握：循环、if/else、数组、枚举、结构体。&#10;继续学习：指针、内存管理。&#10;---"><?php echo $ownerEscape($ownerSettings['skills']); ?></textarea></label>
                    <label>设备清单<textarea name="devices" maxlength="20000" rows="10" placeholder="每组一项：设备名称 | 种类；下方写用途、规格和使用情况；用 --- 分隔。种类可用调试工具、开发板、桌面设备、移动设备、外设。&#10;BingPi-M2 | 开发板&#10;用途：嵌入式 Linux 学习&#10;主控：全志 T113-S3，双核 Cortex-A7@1.2GHz…&#10;---"><?php echo $ownerEscape($ownerSettings['devices']); ?></textarea></label>
                    <label>首页背景图片地址<input type="url" name="hero_background" value="<?php echo $ownerEscape($ownerSettings['hero_background']); ?>" placeholder="留空使用主题默认背景"><small>可以填写 HTTPS 图片地址，或在这里上传图片。</small></label>
                    <label>上传首页背景图片<input type="file" name="background_image" accept="image/jpeg,image/png,image/webp,image/gif"><small>JPG、PNG、WebP 或 GIF，最多 5 MB；上传后自动替换图片地址。</small></label>
                    <label class="owner-form-wide">阅读音乐播放列表<textarea name="music_tracks" maxlength="10000" rows="8" placeholder="每行：曲名 | HTTPS 音频地址或站点路径。少于 20 首时会自动补齐内置曲目。&#10;雨夜微光 | /usr/themes/argon-note/assets/music/01-rain-glow.wav"><?php echo $ownerEscape($ownerSettings['music_tracks']); ?></textarea><small>内置 20 首原创轻音乐。播放器不会自动播放；支持 MP3、OGG、WAV 等格式，远程音频请使用 HTTPS。</small></label>
                </div>
                <p class="owner-form-hint">设置保存在 Typecho 数据库中；图片会保存到站点 uploads 目录。保存后会在本页显示成功或失败状态。</p>
                <input type="hidden" name="argon_note_action" value="save_display_settings">
                <button class="owner-save-button" type="submit">保存站点设置</button>
            </form>
        </section>
    </div>
</section>
