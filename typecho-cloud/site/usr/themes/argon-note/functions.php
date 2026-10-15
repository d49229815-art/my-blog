<?php
if (!defined('__TYPECHO_ROOT_DIR__')) {
    exit;
}

function argonNoteIsSiteOwner()
{
    try {
        $user = \Widget\User::alloc();
        return $user->hasLogin()
            && (int) $user->uid === 1
            && $user->group === 'administrator';
    } catch (\Throwable $error) {
        return false;
    }
}

// Typecho's siteUrl() and themeUrl() methods print directly to the response.
// These helpers return strings for headers, form actions, and data attributes.
function argonNoteSiteUrl($path = '')
{
    $options = \Widget\Options::alloc();
    return \Typecho\Common::url((string) $path, (string) $options->siteUrl);
}

function argonNoteThemeAssetUrl($path = '')
{
    $options = \Widget\Options::alloc();
    return \Typecho\Common::url((string) $path, (string) $options->themeUrl);
}

function argonNoteDefaultMusicTrackLines()
{
    $names = [
        '雨夜微光', '远山和风', '窗边细雨', '雾色森林', '夜航星河',
        '晨雾散步', '纸页回声', '海岸微潮', '暮色松林', '云间月色',
        '灯下慢读', '溪谷晚风', '星尘轻落', '薄雾花园', '远岸灯火',
        '秋日回廊', '雪线之外', '静夜钢琴', '微光序曲', '归途',
    ];
    $slugs = [
        'rain-glow', 'mountain-breeze', 'window-rain', 'mist-forest', 'night-voyage',
        'morning-mist', 'paper-echo', 'quiet-tide', 'dusk-pines', 'moon-cloud',
        'lamp-reading', 'valley-evening', 'soft-stardust', 'mist-garden', 'distant-lights',
        'autumn-corridor', 'beyond-snowline', 'night-piano', 'small-lights', 'way-home',
    ];
    $lines = [];
    foreach ($names as $index => $name) {
        $lines[] = $name . ' | /usr/themes/argon-note/assets/music/' . sprintf('%02d', $index + 1) . '-' . $slugs[$index] . '.wav';
    }
    return $lines;
}

function argonNoteCompleteMusicTrackText($raw)
{
    $lines = array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/u', (string) $raw))));
    $knownUrls = [];
    $validCount = 0;
    foreach ($lines as $line) {
        $parts = array_map('trim', preg_split('/\s*[|｜]\s*/u', $line, 2));
        if (count($parts) === 2 && $parts[0] !== '' && argonNoteIsSafeMediaUrl($parts[1])) {
            $knownUrls[$parts[1]] = true;
            $validCount++;
        }
    }
    foreach (argonNoteDefaultMusicTrackLines() as $defaultLine) {
        $parts = array_map('trim', preg_split('/\s*[|｜]\s*/u', $defaultLine, 2));
        if (!isset($knownUrls[$parts[1]]) && $validCount < 20 && count($lines) < 30) {
            $lines[] = $defaultLine;
            $knownUrls[$parts[1]] = true;
            $validCount++;
        }
    }
    return implode("\n", $lines);
}

function argonNoteDisplaySettings()
{
    static $settings = null;
    if ($settings !== null) {
        return $settings;
    }

    $settings = [
        'name' => 'Morrow',
        'tagline' => '写下日常，也收留那些未说完的话。',
        'announcement' => '把握住当下，才能开辟未来',
        'github' => 'https://github.com/d49229815-art',
        'pinned' => [],
        'skills' => '',
        'devices' => '',
        'social_links' => "GitHub | https://github.com/d49229815-art | github\nRSS 订阅 | @feed | rss",
        'hero_background' => '',
        'music_tracks' => implode("\n", argonNoteDefaultMusicTrackLines()),
    ];

    try {
        $options = \Widget\Options::alloc();
        $mapping = [
            'name' => 'argon_note_site_name',
            'tagline' => 'argon_note_tagline',
            'announcement' => 'argon_note_announcement',
            'github' => 'argon_note_github',
            'skills' => 'argon_note_skills',
            'devices' => 'argon_note_devices',
            'social_links' => 'argon_note_social_links',
            'hero_background' => 'argon_note_hero_background',
            'music_tracks' => 'argon_note_music_tracks',
        ];
        foreach ($mapping as $key => $optionName) {
            $value = $options->{$optionName};
            if (is_string($value) && (in_array($key, ['skills', 'devices', 'social_links', 'hero_background', 'music_tracks'], true) || trim($value) !== '')) {
                $settings[$key] = trim($value);
            }
        }
        if (preg_match('~/dusk-mountains\.svg(?:[?#].*)?$~i', $settings['hero_background'])) {
            $settings['hero_background'] = '';
        }
        $settings['music_tracks'] = argonNoteCompleteMusicTrackText($settings['music_tracks']);
        $settings['pinned'] = argonNoteNormalizePinnedPosts($options->argon_note_pinned_posts);
        if (!$settings['pinned']) {
            $settings['pinned'] = argonNoteNormalizePinnedPosts($options->argon_note_pinned_post);
        }
    } catch (\Throwable $error) {
        // Keep the built-in defaults if the settings table is temporarily unavailable.
    }

    return $settings;
}

function argonNoteNormalizePinnedPosts($value)
{
    if (is_string($value)) {
        $value = trim($value);
        if ($value === '') return [];
        if (ctype_digit($value)) {
            $value = [(int) $value];
        } else {
            $decoded = json_decode($value, true);
            $value = is_array($decoded) ? $decoded : [];
        }
    } elseif (is_numeric($value)) {
        $value = [(int) $value];
    }
    if (!is_array($value)) return [];

    $ids = [];
    foreach ($value as $candidate) {
        $id = filter_var($candidate, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if ($id !== false && !in_array((int) $id, $ids, true)) {
            $ids[] = (int) $id;
            if (count($ids) >= 200) break;
        }
    }
    return $ids;
}

function argonNoteWriteOption($name, $value)
{
    $db = \Typecho\Db::get();
    $condition = $db->select('name')->from('table.options')
        ->where('name = ?', $name)->where('user = ?', 0)->limit(1);
    $existing = $db->fetchObject($condition);

    if ($existing) {
        $db->query($db->update('table.options')->rows(['value' => (string) $value])
            ->where('name = ?', $name)->where('user = ?', 0));
    } else {
        $db->query($db->insert('table.options')->rows([
            'name' => $name,
            'user' => 0,
            'value' => (string) $value,
        ]));
    }
}

function argonNotePublishedPostExists($cid)
{
    if ((int) $cid < 1) {
        return false;
    }

    $db = \Typecho\Db::get();
    return (bool) $db->fetchObject($db->select('cid')->from('table.contents')
        ->where('cid = ?', (int) $cid)
        ->where('type = ?', 'post')
        ->where('status = ?', 'publish')->limit(1));
}

function argonNoteFindPublishedPost($cid)
{
    if (!argonNotePublishedPostExists($cid)) {
        return null;
    }
    $db = \Typecho\Db::get();
    return $db->fetchRow($db->select('cid', 'title', 'slug', 'created', 'text', 'commentsNum')
        ->from('table.contents')->where('cid = ?', (int) $cid)->limit(1));
}

function argonNotePlainExcerpt($text, $length = 220)
{
    $text = argonNoteFilterOwnerSections((string) $text);
    $text = preg_replace('/<!--\s*markdown\s*-->/i', ' ', $text);
    $text = preg_replace('/```.*?```/s', ' ', $text);
    $text = preg_replace('/`[^`]*`/u', ' ', $text);
    $text = html_entity_decode(strip_tags($text), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $text = preg_replace('/[>#*_\[\]()`~|]+/u', ' ', $text);
    $text = trim(preg_replace('/[\s\p{Z}]+/u', ' ', $text));
    if (function_exists('mb_strlen') && mb_strlen($text, 'UTF-8') > $length) {
        return mb_substr($text, 0, $length, 'UTF-8') . '…';
    }
    return strlen($text) > $length * 3 ? substr($text, 0, $length * 3) . '…' : $text;
}

function argonNoteHandleDisplaySettingsSave()
{
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST'
        || ($_POST['argon_note_action'] ?? '') !== 'save_display_settings') {
        return;
    }

    if (!argonNoteIsSiteOwner()) {
        http_response_code(403);
        exit('仅站主可以修改显示设置。');
    }

    // Typecho binds this token to the page that submitted the form.
    \Widget\Security::alloc()->protect();

    $name = trim((string) ($_POST['site_name'] ?? 'Morrow'));
    $tagline = trim((string) ($_POST['site_tagline'] ?? ''));
    $announcement = trim((string) ($_POST['announcement'] ?? ''));
    $github = trim((string) ($_POST['github_url'] ?? ''));
    $skills = trim((string) ($_POST['skills'] ?? ''));
    $devices = trim((string) ($_POST['devices'] ?? ''));
    $socialLinks = trim((string) ($_POST['social_links'] ?? ''));
    $heroBackground = trim((string) ($_POST['hero_background'] ?? ''));
    $musicTracks = trim((string) ($_POST['music_tracks'] ?? ''));
    $pinned = argonNoteNormalizePinnedPosts($_POST['pinned_posts'] ?? []);

    if (function_exists('mb_substr')) {
        $name = mb_substr($name, 0, 28, 'UTF-8');
        $tagline = mb_substr($tagline, 0, 120, 'UTF-8');
        $announcement = mb_substr($announcement, 0, 240, 'UTF-8');
        $skills = mb_substr($skills, 0, 20000, 'UTF-8');
        $devices = mb_substr($devices, 0, 20000, 'UTF-8');
        $socialLinks = mb_substr($socialLinks, 0, 10000, 'UTF-8');
        $heroBackground = mb_substr($heroBackground, 0, 2048, 'UTF-8');
        $musicTracks = mb_substr($musicTracks, 0, 10000, 'UTF-8');
    } else {
        $name = substr($name, 0, 80);
        $tagline = substr($tagline, 0, 360);
        $announcement = substr($announcement, 0, 720);
        $skills = substr($skills, 0, 60000);
        $devices = substr($devices, 0, 60000);
        $socialLinks = substr($socialLinks, 0, 30000);
        $heroBackground = substr($heroBackground, 0, 2048);
        $musicTracks = substr($musicTracks, 0, 30000);
    }

    if ($name === '') {
        $name = 'Morrow';
    }
    if ($tagline === '') {
        $tagline = '写下日常，也收留那些未说完的话。';
    }

    $githubParts = parse_url($github);
    if (!filter_var($github, FILTER_VALIDATE_URL)
        || strtolower((string) ($githubParts['scheme'] ?? '')) !== 'https'
        || strtolower((string) ($githubParts['host'] ?? '')) !== 'github.com') {
        $github = 'https://github.com/d49229815-art';
    }

    $validPinned = [];
    foreach ($pinned as $pinnedId) {
        if (argonNotePublishedPostExists($pinnedId) && !argonNotePostHasCategory($pinnedId, 'feel')) {
            $validPinned[] = $pinnedId;
        }
    }
    $pinned = $validPinned;

    try {
        $uploadedBackground = argonNoteUploadImage('background_image', 'hero');
        if ($uploadedBackground !== '') $heroBackground = $uploadedBackground;
        if (!argonNoteIsSafeMediaUrl($heroBackground) || preg_match('~/dusk-mountains\.svg(?:[?#].*)?$~i', $heroBackground)) $heroBackground = argonNoteThemeAssetUrl('assets/home-dusk.webp');
        $expectedOptions = [
            'argon_note_site_name' => $name,
            'title' => $name,
            'argon_note_tagline' => $tagline,
            'argon_note_announcement' => $announcement,
            'argon_note_github' => $github,
            'argon_note_pinned_posts' => json_encode($pinned),
            'argon_note_pinned_post' => (string) ($pinned[0] ?? 0),
            'argon_note_skills' => $skills,
            'argon_note_devices' => $devices,
            'argon_note_social_links' => $socialLinks,
            'argon_note_hero_background' => $heroBackground,
            'argon_note_music_tracks' => $musicTracks,
        ];
        $settingsDb = \Typecho\Db::get();
        $settingsDb->query('START TRANSACTION');
        argonNoteWriteOption('argon_note_site_name', $name);
        argonNoteWriteOption('title', $name);
        argonNoteWriteOption('argon_note_tagline', $tagline);
        argonNoteWriteOption('argon_note_announcement', $announcement);
        argonNoteWriteOption('argon_note_github', $github);
        argonNoteWriteOption('argon_note_pinned_posts', json_encode($pinned));
        argonNoteWriteOption('argon_note_pinned_post', (string) ($pinned[0] ?? 0));
        argonNoteWriteOption('argon_note_skills', $skills);
        argonNoteWriteOption('argon_note_devices', $devices);
        argonNoteWriteOption('argon_note_social_links', $socialLinks);
        argonNoteWriteOption('argon_note_hero_background', $heroBackground);
        argonNoteWriteOption('argon_note_music_tracks', $musicTracks);
        $settingsDb->query('COMMIT');
    } catch (\Throwable $error) {
        try { \Typecho\Db::get()->query('ROLLBACK'); } catch (\Throwable $rollbackError) { /* Preserve the original save error. */ }
        error_log('[Morrow settings] ' . $error->getMessage());
        argonNoteOwnerNoticeRedirect('settings', 'action_error');
    }

    // Verify after commit so a readback uses the same persisted state as the next page view.
    foreach ($expectedOptions as $optionName => $expectedValue) {
        $savedOption = $settingsDb->fetchRow($settingsDb->select('value')->from('table.options')
            ->where('name = ?', $optionName)->where('user = ?', 0)->limit(1));
        $normalizeText = static function ($value) {
            return str_replace(["\r\n", "\r"], "\n", (string) $value);
        };
        if (!$savedOption || $normalizeText($savedOption['value']) !== $normalizeText($expectedValue)) {
            error_log('[Morrow settings] Post-commit verification failed: ' . $optionName);
            argonNoteOwnerNoticeRedirect('settings', 'action_error');
        }
    }

    argonNoteOwnerNoticeRedirect('settings', 'settings_saved');
}

/**
 * Remove or reveal owner-only sections before Typecho builds excerpts and feeds.
 * In the editor, wrap private Markdown with:
 * <!-- owner-only:start -->
 * private content
 * <!-- owner-only:end -->
 */
function argonNoteFilterOwnerSections($content, $widget = null)
{
    if (!is_string($content) || strpos($content, 'owner-only:') === false) {
        return $content;
    }

    $isOwner = argonNoteIsSiteOwner();
    $pattern = '/<!--\s*owner-only:start\s*-->(.*?)<!--\s*owner-only:end\s*-->/is';
    if ($isOwner) {
        $content = preg_replace($pattern, '$1', $content);
        return preg_replace('/<!--\s*owner-only:(?:start|end)\s*-->/i', '', $content);
    }

    $placeholder = '<aside class="owner-private-note" role="note">这部分内容仅站主登录后可见</aside>';
    $content = preg_replace($pattern, $placeholder, $content);
    $content = preg_replace('/<!--\s*owner-only:start\s*-->.*$/is', $placeholder, $content);
    return preg_replace('/<!--\s*owner-only:end\s*-->/i', '', $content);
}

function argonNotePostHasCategory($cid, $slug)
{
    static $cache = [];
    $key = (int) $cid . ':' . (string) $slug;
    if (array_key_exists($key, $cache)) {
        return $cache[$key];
    }

    try {
        $db = \Typecho\Db::get();
        $query = $db->select('table.metas.mid')->from('table.relationships')
            ->join('table.metas', 'table.relationships.mid = table.metas.mid')
            ->where('table.relationships.cid = ?', (int) $cid)
            ->where('table.metas.type = ?', 'category')
            ->where('table.metas.slug = ?', (string) $slug)->limit(1);
        $cache[$key] = (bool) $db->fetchObject($query);
    } catch (\Throwable $error) {
        $cache[$key] = false;
    }

    return $cache[$key];
}

function argonNoteRecentFeelPosts($limit = 4)
{
    try {
        $db = \Typecho\Db::get();
        return $db->fetchAll($db->select(
            'table.contents.cid',
            'table.contents.title',
            'table.contents.slug',
            'table.contents.created',
            'table.contents.text',
            'table.contents.commentsNum'
        )->from('table.contents')
            ->join('table.relationships', 'table.contents.cid = table.relationships.cid')
            ->join('table.metas', 'table.relationships.mid = table.metas.mid')
            ->where('table.contents.type = ?', 'post')
            ->where('table.contents.status = ?', 'publish')
            ->where('table.metas.type = ?', 'category')
            ->where('table.metas.slug = ?', 'feel')
            ->order('table.contents.created', \Typecho\Db::SORT_DESC)
            ->limit(max(1, min(12, (int) $limit))));
    } catch (\Throwable $error) {
        return [];
    }
}

function argonNotePublishedPosts($limit = 100)
{
    try {
        $db = \Typecho\Db::get();
        return $db->fetchAll($db->select('cid', 'title')->from('table.contents')
            ->where('type = ?', 'post')->where('status = ?', 'publish')
            ->order('created', \Typecho\Db::SORT_DESC)
            ->limit(max(1, min(200, (int) $limit))));
    } catch (\Throwable $error) {
        return [];
    }
}

function argonNoteContentCounts()
{
    try {
        $db = \Typecho\Db::get();
        $all = $db->fetchObject($db->select(['COUNT(cid)' => 'count'])->from('table.contents')
            ->where('type = ?', 'post')->where('status = ?', 'publish'));
        $feel = $db->fetchObject($db->select(['COUNT(DISTINCT table.contents.cid)' => 'count'])
            ->from('table.contents')
            ->join('table.relationships', 'table.contents.cid = table.relationships.cid')
            ->join('table.metas', 'table.relationships.mid = table.metas.mid')
            ->where('table.contents.type = ?', 'post')->where('table.contents.status = ?', 'publish')
            ->where('table.metas.type = ?', 'category')->where('table.metas.slug = ?', 'feel'));
        $feelCount = (int) ($feel->count ?? 0);
        return ['articles' => max(0, (int) ($all->count ?? 0) - $feelCount), 'feels' => $feelCount];
    } catch (\Throwable $error) {
        return ['articles' => 0, 'feels' => 0];
    }
}

function argonNoteSeriesAssignments()
{
    static $assignments = null;
    if ($assignments !== null) return $assignments;
    $assignments = [];
    try {
        $raw = \Widget\Options::alloc()->argon_note_series_assignments;
        $decoded = json_decode((string) $raw, true);
        if (is_array($decoded)) {
            foreach ($decoded as $cid => $series) {
                $series = trim(strip_tags((string) $series));
                if ((int) $cid > 0 && $series !== '') $assignments[(string) (int) $cid] = $series;
            }
        }
    } catch (\Throwable $error) {
        $assignments = [];
    }
    return $assignments;
}

function argonNotePostSeries($cid)
{
    $assignments = argonNoteSeriesAssignments();
    return $assignments[(string) (int) $cid] ?? '';
}

function argonNoteSavePostSeries($cid, $series)
{
    $cid = (int) $cid;
    if ($cid < 1) return;
    $series = trim(strip_tags((string) $series));
    if (function_exists('mb_substr')) $series = mb_substr($series, 0, 80, 'UTF-8');
    else $series = substr($series, 0, 240);
    $assignments = argonNoteSeriesAssignments();
    if ($series === '') unset($assignments[(string) $cid]);
    else $assignments[(string) $cid] = $series;
    argonNoteWriteOption('argon_note_series_assignments', json_encode($assignments, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
}

function argonNoteSeriesAnchor($series)
{
    return 'series-' . substr(hash('sha256', trim((string) $series)), 0, 12);
}

function argonNoteSeriesIndex($limit = 1500)
{
    $groups = [];
    $assignments = argonNoteSeriesAssignments();
    if (!$assignments) return $groups;
    $assignedIds = array_slice(array_map('intval', array_keys($assignments)), 0, max(1, min(3000, (int) $limit)));
    if (!$assignedIds) return $groups;
    try {
        $db = \Typecho\Db::get();
        $rows = $db->fetchAll($db->select('cid', 'title', 'slug', 'created', 'text', 'commentsNum')
            ->from('table.contents')->where('cid IN ?', $assignedIds)->where('type = ?', 'post')
            ->where('status = ?', 'publish')->order('created', \Typecho\Db::SORT_DESC));
    } catch (\Throwable $error) {
        return $groups;
    }
    foreach ($rows as $row) {
        $series = $assignments[(string) (int) $row['cid']] ?? '';
        if ($series === '' || argonNotePostHasCategory($row['cid'], 'feel')) continue;
        if (!isset($groups[$series])) $groups[$series] = ['name' => $series, 'anchor' => argonNoteSeriesAnchor($series), 'posts' => []];
        $groups[$series]['posts'][] = argonNotePostCardData($row);
    }
    uksort($groups, 'strnatcasecmp');
    return $groups;
}

function argonNotePublishedPostRows($limit = 1500)
{
    try {
        $db = \Typecho\Db::get();
        return $db->fetchAll($db->select('cid', 'title', 'slug', 'created', 'text', 'commentsNum')
            ->from('table.contents')->where('type = ?', 'post')->where('status = ?', 'publish')
            ->order('created', \Typecho\Db::SORT_DESC)->limit(max(1, min(3000, (int) $limit))));
    } catch (\Throwable $error) {
        return [];
    }
}

function argonNotePostTaxonomies($cid, $type)
{
    if (!in_array($type, ['category', 'tag'], true)) return [];
    try {
        $db = \Typecho\Db::get();
        return $db->fetchAll($db->select('table.metas.mid', 'table.metas.name', 'table.metas.slug', 'table.metas.count')
            ->from('table.relationships')->join('table.metas', 'table.relationships.mid = table.metas.mid')
            ->where('table.relationships.cid = ?', (int) $cid)->where('table.metas.type = ?', $type)
            ->order('table.metas.order', \Typecho\Db::SORT_ASC));
    } catch (\Throwable $error) {
        return [];
    }
}

function argonNotePostCover($text)
{
    $text = (string) $text;
    $url = '';
    if (preg_match('/<img\b[^>]*\bsrc\s*=\s*["\']([^"\']+)["\']/i', $text, $match)) $url = trim($match[1]);
    elseif (preg_match('/!\[[^\]]*\]\(<?([^\s)>]+)>?(?:\s+["\'][^"\']*["\'])?\)/u', $text, $match)) $url = trim($match[1]);
    if ($url === '' || preg_match('/^(?:javascript|data|vbscript):/i', $url)) return '';
    if (strpos($url, '//') === 0) return '';
    if (strpos($url, '/') !== 0 && !preg_match('/^https?:\/\//i', $url)) return '';
    return $url;
}

function argonNotePostCardData(array $row)
{
    $cid = (int) ($row['cid'] ?? 0);
    $text = (string) ($row['text'] ?? '');
    $categories = array_values(array_filter(argonNotePostTaxonomies($cid, 'category'), static function ($item) {
        return ($item['slug'] ?? '') !== 'feel';
    }));
    $tags = argonNotePostTaxonomies($cid, 'tag');
    $plain = trim(preg_replace('/[\s\p{Z}]+/u', ' ', strip_tags(preg_replace('/<!--\s*markdown\s*-->/i', '', $text))));
    $length = function_exists('mb_strlen') ? mb_strlen($plain, 'UTF-8') : strlen($plain);
    return [
        'cid' => $cid,
        'title' => (string) ($row['title'] ?? '未命名文章'),
        'created' => (int) ($row['created'] ?? 0),
        'comments' => (int) ($row['commentsNum'] ?? 0),
        'url' => argonNotePostPermalink($cid),
        'excerpt' => argonNotePlainExcerpt($text, 180),
        'cover' => argonNotePostCover($text),
        'length' => $length,
        'categories' => $categories,
        'tags' => $tags,
        'series' => argonNotePostSeries($cid),
    ];
}

function argonNoteProfileEntries($kind)
{
    $settings = argonNoteDisplaySettings();
    $raw = (string) ($settings[$kind] ?? '');
    $entries = [];
    $blocks = preg_split('/^\s*---+\s*$/mu', trim($raw));
    if (count($blocks) === 1) {
        $legacyLines = array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/u', trim($raw)))));
        $isLegacyList = count($legacyLines) > 1;
        foreach ($legacyLines as $legacyLine) {
            if (!preg_match('/\s*[|｜]\s*/u', $legacyLine)) { $isLegacyList = false; break; }
        }
        if ($isLegacyList) $blocks = $legacyLines;
    }
    foreach ($blocks as $block) {
        $lines = preg_split('/\r\n|\r|\n/u', trim(strip_tags($block)));
        $lines = array_values(array_filter(array_map('trim', $lines), static function ($line) { return $line !== ''; }));
        if (!$lines) continue;
        $parts = preg_split('/\s*[|｜]\s*/u', array_shift($lines));
        $extraDetail = trim(implode("\n", $lines));
        $thirdColumn = trim($parts[2] ?? '');
        if ($kind === 'devices' && $thirdColumn !== '') {
            $extraDetail = $extraDetail === '' ? $thirdColumn : $thirdColumn . "\n" . $extraDetail;
        }
        $entries[] = [
            'name' => trim($parts[0] ?? ''),
            'type' => trim($parts[1] ?? ''),
            'category' => $kind === 'devices' ? (trim($parts[1] ?? '') ?: '其他') : '',
            'level' => $kind === 'skills' ? $thirdColumn : '',
            'detail' => $extraDetail,
        ];
    }
    return $entries;
}

function argonNoteSidebarStats()
{
    $counts = argonNoteContentCounts();
    try {
        $db = \Typecho\Db::get();
        $category = $db->fetchObject($db->select(['COUNT(mid)' => 'count'])->from('table.metas')
            ->where('type = ?', 'category')->where('slug <> ?', 'feel'));
        $tags = $db->fetchObject($db->select(['COUNT(mid)' => 'count'])->from('table.metas')->where('type = ?', 'tag'));
        return ['articles' => $counts['articles'], 'feels' => $counts['feels'], 'categories' => (int) ($category->count ?? 0), 'tags' => (int) ($tags->count ?? 0), 'series' => count(argonNoteSeriesIndex())];
    } catch (\Throwable $error) {
        return ['articles' => $counts['articles'], 'feels' => $counts['feels'], 'categories' => 0, 'tags' => 0, 'series' => count(argonNoteSeriesIndex())];
    }
}

function argonNoteCalendarDays($year, $month)
{
    $year = (int) $year;
    $month = (int) $month;
    if ($year < 2000 || $year > 2100 || $month < 1 || $month > 12) return [];
    $start = strtotime(sprintf('%04d-%02d-01 00:00:00', $year, $month));
    $end = strtotime('+1 month', $start);
    try {
        $db = \Typecho\Db::get();
        $rows = $db->fetchAll($db->select('created')->from('table.contents')
            ->where('type = ?', 'post')->where('status = ?', 'publish')
            ->where('created >= ?', $start)->where('created < ?', $end));
        $days = [];
        foreach ($rows as $row) $days[(int) date('j', (int) $row['created'])] = true;
        return array_map('intval', array_keys($days));
    } catch (\Throwable $error) {
        return [];
    }
}

function argonNotePostPermalink($cid)
{
    $options = \Widget\Options::alloc();
    $cid = (int) $cid;
    $url = \Typecho\Router::url('post', ['cid' => $cid], $options->index);
    return $url === '#' ? argonNoteSiteUrl('post/' . $cid . '/') : $url;
}

function argonNoteArchivePermalink()
{
    $options = \Widget\Options::alloc();
    return argonNoteSiteUrl('archives');
}

function argonNoteCategoryPermalink($slug, $mid = 0)
{
    $options = \Widget\Options::alloc();
    $url = \Typecho\Router::url('category', ['slug' => (string) $slug, 'mid' => (int) $mid], $options->index);
    return $url === '#' ? argonNoteSiteUrl('category/' . rawurlencode((string) $slug)) : $url;
}

function argonNoteTagPermalink($slug, $mid = 0)
{
    $options = \Widget\Options::alloc();
    $url = \Typecho\Router::url('tag', ['slug' => (string) $slug, 'mid' => (int) $mid], $options->index);
    return $url === '#' ? argonNoteSiteUrl('tag/' . rawurlencode((string) $slug)) : $url;
}

function argonNoteOwnerNoticeRedirect($tab, $notice)
{
    $allowedTabs = ['overview', 'write', 'manage', 'comments', 'taxonomy', 'friends', 'settings'];
    $allowedNotices = [
        'post_saved', 'post_deleted', 'post_trashed', 'post_restored', 'status_saved',
        'comment_approved', 'comment_waiting', 'comment_spam', 'comment_deleted',
        'category_added', 'category_updated', 'category_deleted', 'tag_added', 'tag_updated', 'tag_deleted',
        'series_updated', 'series_deleted', 'friend_added', 'friend_updated', 'friend_deleted',
        'settings_saved', 'invalid_input', 'action_error'
    ];
    // Put notice before tab so HTML's named-entity parsing cannot turn &notice into ¬ice.
    $safeNotice = in_array($notice, $allowedNotices, true) ? $notice : 'action_error';
    $safeTab = in_array($tab, $allowedTabs, true) ? $tab : 'overview';
    $query = http_build_query(['notice' => $safeNotice, 'tab' => $safeTab]);
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    if (strtolower((string) ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '')) === 'xmlhttprequest'
        || strpos((string) ($_SERVER['HTTP_ACCEPT'] ?? ''), 'application/json') !== false) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'ok' => !in_array($safeNotice, ['invalid_input', 'action_error'], true),
            'notice' => $safeNotice,
            'tab' => $safeTab,
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
    $target = argonNoteSiteUrl('owner-console?' . $query);
    header('Location: ' . $target, true, 303);
    echo '<!doctype html><html lang="zh-CN"><meta charset="utf-8"><title>正在返回</title><p>操作已处理，正在返回站主工作台。如果页面没有自动返回，请<a href="' . htmlspecialchars($target, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '">点这里继续</a>。</p><meta http-equiv="refresh" content="0;url=' . htmlspecialchars($target, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '">';
    exit;
}

function argonNoteOwnerCategories()
{
    try {
        return \Typecho\Db::get()->fetchAll(\Typecho\Db::get()->select('mid', 'name', 'slug', 'description', 'count', 'parent')
            ->from('table.metas')->where('type = ?', 'category')->order('order', \Typecho\Db::SORT_ASC));
    } catch (\Throwable $error) {
        return [];
    }
}

function argonNoteOwnerTags()
{
    try {
        return \Typecho\Db::get()->fetchAll(\Typecho\Db::get()->select('mid', 'name', 'slug', 'count')
            ->from('table.metas')->where('type = ?', 'tag')->order('name', \Typecho\Db::SORT_ASC)->limit(300));
    } catch (\Throwable $error) {
        return [];
    }
}

function argonNoteOwnerSeriesRows()
{
    $rows = [];
    foreach (argonNoteSeriesAssignments() as $series) {
        if (!isset($rows[$series])) $rows[$series] = ['name' => $series, 'count' => 0];
        $rows[$series]['count']++;
    }
    uksort($rows, 'strnatcasecmp');
    return array_values($rows);
}

function argonNoteOwnerPosts($status = 'all', $search = '', $limit = 200)
{
    try {
        $db = \Typecho\Db::get();
        $query = $db->select('cid', 'title', 'slug', 'created', 'modified', 'status', 'type', 'authorId', 'commentsNum')
            ->from('table.contents')->where('type IN ?', ['post', 'post_draft']);
        if ($status === 'trash') {
            $query->where('status = ?', 'deleted');
        } else {
            $query->where('status <> ?', 'deleted');
        }
        if ($status === 'draft') {
            $query->where('type = ?', 'post_draft');
        } elseif ($status === 'published') {
            $query->where('type = ?', 'post')->where('status = ?', 'publish');
        } elseif ($status === 'hidden') {
            $query->where('type = ?', 'post')->where('status IN ?', ['hidden', 'private', 'waiting']);
        }
        $search = trim((string) $search);
        if ($search !== '') {
            $needle = '%' . str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $search) . '%';
            $query->where('(title LIKE ? OR text LIKE ?)', $needle, $needle);
        }
        $rows = $db->fetchAll($query->order('modified', \Typecho\Db::SORT_DESC)->limit(max(1, min(300, (int) $limit))));
        foreach ($rows as &$row) {
            $metaRows = $db->fetchAll($db->select('table.metas.name', 'table.metas.type', 'table.metas.mid')
                ->from('table.relationships')->join('table.metas', 'table.relationships.mid = table.metas.mid')
                ->where('table.relationships.cid = ?', (int) $row['cid']));
            $row['categories'] = [];
            $row['tags'] = [];
            foreach ($metaRows as $meta) {
                if ($meta['type'] === 'category') $row['categories'][] = $meta['name'];
                if ($meta['type'] === 'tag') $row['tags'][] = $meta['name'];
            }
            $row['is_feel'] = argonNotePostHasCategory($row['cid'], 'feel');
        }
        unset($row);
        return $rows;
    } catch (\Throwable $error) {
        return [];
    }
}

function argonNoteOwnerCommentRows($status = 'waiting', $search = '', $limit = 120)
{
    try {
        $db = \Typecho\Db::get();
        $query = $db->select(
            'table.comments.coid', 'table.comments.cid', 'table.comments.parent',
            'table.comments.author', 'table.comments.text', 'table.comments.status',
            'table.comments.created', 'table.comments.type', 'table.contents.title',
            ['table.contents.type' => 'contentType'], ['table.contents.slug' => 'contentSlug']
        )->from('table.comments')
            ->join('table.contents', 'table.comments.cid = table.contents.cid')
            ->where('table.comments.type = ?', 'comment');
        if (in_array($status, ['waiting', 'approved', 'spam'], true)) {
            $query->where('table.comments.status = ?', $status);
        }
        $search = trim((string) $search);
        if ($search !== '') {
            $needle = '%' . str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $search) . '%';
            $query->where('(table.comments.author LIKE ? OR table.comments.text LIKE ? OR table.contents.title LIKE ?)', $needle, $needle, $needle);
        }
        return $db->fetchAll($query->order('table.comments.created', \Typecho\Db::SORT_DESC)
            ->limit(max(1, min(300, (int) $limit))));
    } catch (\Throwable $error) {
        return [];
    }
}

function argonNoteOwnerTrashMap()
{
    try {
        $decoded = json_decode((string) \Widget\Options::alloc()->argon_note_owner_trash, true);
        if (!is_array($decoded)) return [];
        $map = [];
        foreach ($decoded as $cid => $status) {
            $cid = (int) $cid;
            if ($cid > 0 && in_array((string) $status, ['publish', 'hidden', 'private', 'waiting'], true)) {
                $map[(string) $cid] = (string) $status;
            }
        }
        return $map;
    } catch (\Throwable $error) {
        return [];
    }
}

function argonNoteOwnerSaveTrashMap(array $map)
{
    argonNoteWriteOption('argon_note_owner_trash', json_encode($map, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
}

function argonNoteOwnerMovePostToTrash($cid)
{
    $cid = (int) $cid;
    if ($cid < 1) return false;
    $db = \Typecho\Db::get();
    $row = $db->fetchRow($db->select('cid', 'status', 'type')->from('table.contents')
        ->where('cid = ?', $cid)->where('type IN ?', ['post', 'post_draft'])->limit(1));
    if (!$row || $row['status'] === 'deleted') return false;
    $map = argonNoteOwnerTrashMap();
    $map[(string) $cid] = in_array($row['status'], ['publish', 'hidden', 'private', 'waiting'], true)
        ? $row['status'] : 'hidden';
    $db->query($db->update('table.contents')->rows([
        'status' => 'deleted', 'modified' => \Widget\Options::alloc()->time,
    ])->where('cid = ?', $cid)->where('type IN ?', ['post', 'post_draft']));
    $metaIds = array_column($db->fetchAll($db->select('table.relationships.mid')->from('table.relationships')
        ->join('table.metas', 'table.relationships.mid = table.metas.mid')
        ->where('table.relationships.cid = ?', $cid)->where('table.metas.type IN ?', ['category', 'tag'])), 'mid');
    argonNoteOwnerRefreshMetaCounts($metaIds);
    argonNoteOwnerSaveTrashMap($map);
    return true;
}

function argonNoteOwnerRestorePost($cid)
{
    $cid = (int) $cid;
    if ($cid < 1) return false;
    $db = \Typecho\Db::get();
    $row = $db->fetchRow($db->select('cid', 'type')->from('table.contents')
        ->where('cid = ?', $cid)->where('status = ?', 'deleted')->where('type IN ?', ['post', 'post_draft'])->limit(1));
    if (!$row) return false;
    $map = argonNoteOwnerTrashMap();
    $status = $map[(string) $cid] ?? 'hidden';
    unset($map[(string) $cid]);
    $db->query($db->update('table.contents')->rows([
        'status' => $row['type'] === 'post_draft' ? 'publish' : $status,
        'modified' => \Widget\Options::alloc()->time,
    ])->where('cid = ?', $cid)->where('status = ?', 'deleted'));
    $metaIds = array_column($db->fetchAll($db->select('table.relationships.mid')->from('table.relationships')
        ->join('table.metas', 'table.relationships.mid = table.metas.mid')
        ->where('table.relationships.cid = ?', $cid)->where('table.metas.type IN ?', ['category', 'tag'])), 'mid');
    argonNoteOwnerRefreshMetaCounts($metaIds);
    argonNoteOwnerSaveTrashMap($map);
    return true;
}

function argonNoteOwnerChangeCommentStatus($coid, $status)
{
    $coid = (int) $coid;
    if ($coid < 1 || !in_array($status, ['approved', 'waiting', 'spam'], true)) return false;
    $db = \Typecho\Db::get();
    $comment = $db->fetchRow($db->select('coid', 'cid', 'status')->from('table.comments')
        ->where('coid = ?', $coid)->where('type = ?', 'comment')->limit(1));
    if (!$comment) return false;
    if ($comment['status'] === $status) return true;
    $db->query($db->update('table.comments')->rows(['status' => $status])
        ->where('coid = ?', $coid)->where('type = ?', 'comment'));
    if ($comment['status'] === 'approved' && $status !== 'approved') {
        $db->query($db->update('table.contents')->expression('commentsNum', 'commentsNum - 1')
            ->where('cid = ? AND commentsNum > 0', (int) $comment['cid']));
    } elseif ($comment['status'] !== 'approved' && $status === 'approved') {
        $db->query($db->update('table.contents')->expression('commentsNum', 'commentsNum + 1')
            ->where('cid = ?', (int) $comment['cid']));
    }
    return true;
}

function argonNoteOwnerDeleteComment($coid)
{
    $coid = (int) $coid;
    if ($coid < 1) return false;
    $db = \Typecho\Db::get();
    $comment = $db->fetchRow($db->select('coid', 'cid', 'parent', 'status')->from('table.comments')
        ->where('coid = ?', $coid)->where('type = ?', 'comment')->limit(1));
    if (!$comment) return false;
    $db->query($db->update('table.comments')->rows(['parent' => (int) $comment['parent']])
        ->where('parent = ?', $coid)->where('cid = ?', (int) $comment['cid']));
    $db->query($db->delete('table.comments')->where('coid = ?', $coid)->where('type = ?', 'comment'));
    if ($comment['status'] === 'approved') {
        $db->query($db->update('table.contents')->expression('commentsNum', 'commentsNum - 1')
            ->where('cid = ? AND commentsNum > 0', (int) $comment['cid']));
    }
    return true;
}

function argonNoteOwnerFindPost($cid)
{
    $cid = (int) $cid;
    if ($cid < 1) return null;
    try {
        $db = \Typecho\Db::get();
        $row = $db->fetchRow($db->select('cid', 'title', 'slug', 'text', 'created', 'modified', 'status', 'type', 'authorId', 'allowComment')
            ->from('table.contents')->where('cid = ?', $cid)->where('type IN ?', ['post', 'post_draft'])->limit(1));
        if (!$row || $row['status'] === 'deleted') return null;
        $metas = $db->fetchAll($db->select('table.metas.mid', 'table.metas.name', 'table.metas.type')
            ->from('table.relationships')->join('table.metas', 'table.relationships.mid = table.metas.mid')
            ->where('table.relationships.cid = ?', $cid));
        $row['category_ids'] = [];
        $row['tags'] = [];
        foreach ($metas as $meta) {
            if ($meta['type'] === 'category') $row['category_ids'][] = (int) $meta['mid'];
            if ($meta['type'] === 'tag') $row['tags'][] = $meta['name'];
        }
        $row['is_feel'] = argonNotePostHasCategory($cid, 'feel');
        $row['series'] = argonNotePostSeries($cid);
        $row['markdown'] = strpos((string) $row['text'], '<!--markdown-->') === 0;
        if ($row['markdown']) $row['text'] = substr($row['text'], 15);
        return $row;
    } catch (\Throwable $error) {
        return null;
    }
}

function argonNoteOwnerRefreshMetaCounts(array $metaIds)
{
    $metaIds = array_values(array_unique(array_filter(array_map('intval', $metaIds))));
    if (!$metaIds) return;
    $db = \Typecho\Db::get();
    foreach ($metaIds as $mid) {
        $count = $db->fetchObject($db->select(['COUNT(DISTINCT table.contents.cid)' => 'num'])
            ->from('table.contents')->join('table.relationships', 'table.contents.cid = table.relationships.cid')
            ->where('table.relationships.mid = ?', $mid)->where('table.contents.type = ?', 'post')
            ->where('table.contents.status = ?', 'publish'));
        $db->query($db->update('table.metas')->rows(['count' => (int) ($count->num ?? 0)])->where('mid = ?', $mid));
    }
}

function argonNoteOwnerSyncPostMetas($cid, array $categoryIds, $tagText)
{
    $db = \Typecho\Db::get();
    $oldIds = array_column($db->fetchAll($db->select('table.metas.mid')->from('table.relationships')
        ->join('table.metas', 'table.relationships.mid = table.metas.mid')
        ->where('table.relationships.cid = ?', (int) $cid)->where('table.metas.type IN ?', ['category', 'tag'])), 'mid');
    if ($oldIds) {
        $db->query($db->delete('table.relationships')->where('cid = ?', (int) $cid)->where('mid IN ?', $oldIds));
    }

    $categoryIds = array_values(array_unique(array_filter(array_map('intval', $categoryIds))));
    if (!$categoryIds) {
        $options = \Widget\Options::alloc();
        if ((int) $options->defaultCategory > 0) $categoryIds = [(int) $options->defaultCategory];
    }
    foreach ($categoryIds as $mid) {
        $category = $db->fetchRow($db->select('mid')->from('table.metas')->where('mid = ?', $mid)->where('type = ?', 'category')->limit(1));
        if ($category) $db->query($db->insert('table.relationships')->rows(['cid' => (int) $cid, 'mid' => $mid]));
    }

    $tagNames = preg_split('/[,，、;；\r\n]+/u', (string) $tagText, -1, PREG_SPLIT_NO_EMPTY);
    $tagNames = array_values(array_unique(array_filter(array_map(static function ($tag) {
        return trim(strip_tags((string) $tag));
    }, $tagNames))));
    $tagIds = \Widget\Metas::alloc()->scanTags($tagNames);
    if (!is_array($tagIds)) $tagIds = $tagIds ? [$tagIds] : [];
    foreach (array_unique(array_map('intval', $tagIds)) as $mid) {
        if ($mid > 0) $db->query($db->insert('table.relationships')->rows(['cid' => (int) $cid, 'mid' => $mid]));
    }
    argonNoteOwnerRefreshMetaCounts(array_merge($oldIds, $categoryIds, $tagIds));
}

function argonNoteOwnerDeletePost($cid)
{
    $cid = (int) $cid;
    $db = \Typecho\Db::get();
    $content = $db->fetchRow($db->select('cid', 'type', 'status')->from('table.contents')
        ->where('cid = ?', $cid)->where('type IN ?', ['post', 'post_draft'])->limit(1));
    if (!$content || $content['status'] !== 'deleted') return false;

    $revisions = array_column($db->fetchAll($db->select('cid')->from('table.contents')
        ->where('parent = ?', $cid)->where('type = ?', 'revision')), 'cid');
    $cids = array_values(array_unique(array_merge([$cid], array_map('intval', $revisions))));
    $metaIds = array_column($db->fetchAll($db->select('table.metas.mid')->from('table.relationships')
        ->join('table.metas', 'table.relationships.mid = table.metas.mid')
        ->where('table.relationships.cid IN ?', $cids)->where('table.metas.type IN ?', ['category', 'tag'])), 'mid');

    $db->query($db->update('table.contents')->rows(['parent' => 0, 'status' => 'publish'])
        ->where('parent = ?', $cid)->where('type = ?', 'attachment'));
    $db->query($db->delete('table.comments')->where('cid IN ?', $cids));
    $db->query($db->delete('table.fields')->where('cid IN ?', $cids));
    $db->query($db->delete('table.relationships')->where('cid IN ?', $cids));
    $db->query($db->delete('table.contents')->where('cid IN ?', $cids));
    $series = argonNoteSeriesAssignments();
    foreach ($cids as $deletedCid) unset($series[(string) $deletedCid]);
    argonNoteWriteOption('argon_note_series_assignments', json_encode($series, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    $trashMap = argonNoteOwnerTrashMap();
    unset($trashMap[(string) $cid]);
    argonNoteOwnerSaveTrashMap($trashMap);
    argonNoteOwnerRefreshMetaCounts($metaIds);
    return true;
}

function argonNoteIsSafeMediaUrl($value)
{
    $value = trim((string) $value);
    if ($value === '' || preg_match('/[\x00-\x20]/', $value) || preg_match('/^(?:javascript|data|vbscript):/i', $value)) return false;
    if (strpos($value, '/') === 0 && strpos($value, '//') !== 0) return true;
    if (!filter_var($value, FILTER_VALIDATE_URL)) return false;
    $parts = parse_url($value);
    return is_array($parts) && in_array(strtolower((string) ($parts['scheme'] ?? '')), ['http', 'https'], true) && !empty($parts['host']);
}

function argonNoteUploadImage($field, $prefix = 'image')
{
    if (!isset($_FILES[$field]) || !is_array($_FILES[$field])) return '';
    $file = $_FILES[$field];
    $error = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);
    if ($error === UPLOAD_ERR_NO_FILE) return '';
    if ($error !== UPLOAD_ERR_OK || empty($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
        throw new \RuntimeException('图片上传失败，请重新选择图片。');
    }
    if ((int) ($file['size'] ?? 0) < 1 || (int) $file['size'] > 5 * 1024 * 1024) {
        throw new \RuntimeException('图片不能大于 5 MB。');
    }
    $image = @getimagesize($file['tmp_name']);
    $mime = is_array($image) ? (string) ($image['mime'] ?? '') : '';
    $extensions = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif'];
    if (!isset($extensions[$mime]) || (int) ($image[0] ?? 0) > 7000 || (int) ($image[1] ?? 0) > 7000) {
        throw new \RuntimeException('仅支持有效的 JPG、PNG、WebP 或 GIF 图片。');
    }
    $directory = rtrim(__TYPECHO_ROOT_DIR__, '/\\') . DIRECTORY_SEPARATOR . 'usr' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'argon-note';
    if (!is_dir($directory) && !@mkdir($directory, 0755, true) && !is_dir($directory)) {
        throw new \RuntimeException('无法创建图片上传目录。');
    }
    $prefix = preg_replace('/[^a-z0-9-]/i', '', (string) $prefix) ?: 'image';
    $filename = $prefix . '-' . bin2hex(random_bytes(12)) . '.' . $extensions[$mime];
    if (!move_uploaded_file($file['tmp_name'], $directory . DIRECTORY_SEPARATOR . $filename)) {
        throw new \RuntimeException('无法保存图片文件。');
    }
    return argonNoteSiteUrl('usr/uploads/argon-note/' . $filename);
}

function argonNoteSocialLinks()
{
    $raw = (string) (argonNoteDisplaySettings()['social_links'] ?? '');
    $links = [];
    $allowedIcons = ['github', 'rss', 'gitee', 'gitlab', 'bilibili', 'csdn', 'mail', 'website'];
    foreach (preg_split('/\r\n|\r|\n/u', $raw) as $line) {
        $parts = array_map('trim', preg_split('/\s*[|｜]\s*/u', trim($line), 3));
        if (count($parts) < 2 || $parts[0] === '' || $parts[1] === '') continue;
        $name = function_exists('mb_substr') ? mb_substr($parts[0], 0, 32, 'UTF-8') : substr($parts[0], 0, 96);
        $url = $parts[1] === '@feed' ? argonNoteSiteUrl('feed/') : (string) $parts[1];
        $url = (string) $url;
        $icon = strtolower($parts[2] ?? 'website');
        if (!in_array($icon, $allowedIcons, true)) $icon = 'website';
        if ($url !== '' && ($url[0] === '/' || filter_var($url, FILTER_VALIDATE_URL))) {
            $partsUrl = parse_url($url);
            if ($url[0] === '/' || (is_array($partsUrl) && in_array(strtolower((string) ($partsUrl['scheme'] ?? '')), ['http', 'https', 'mailto'], true))) {
                $links[] = ['name' => $name, 'url' => $url, 'icon' => $icon];
            }
        }
        if (count($links) >= 24) break;
    }
    return $links;
}

function argonNoteMusicTracks()
{
    $raw = (string) (argonNoteDisplaySettings()['music_tracks'] ?? '');
    $tracks = [];
    foreach (preg_split('/\r\n|\r|\n/u', $raw) as $line) {
        $parts = array_map('trim', preg_split('/\s*[|｜]\s*/u', trim($line), 2));
        if (count($parts) !== 2 || $parts[0] === '' || !argonNoteIsSafeMediaUrl($parts[1])) continue;
        $tracks[] = ['title' => function_exists('mb_substr') ? mb_substr($parts[0], 0, 80, 'UTF-8') : substr($parts[0], 0, 240), 'url' => $parts[1]];
        if (count($tracks) >= 30) break;
    }
    return $tracks;
}

function argonNoteFriendLinks()
{
    try {
        $raw = \Widget\Options::alloc()->argon_note_friend_links;
        $links = json_decode((string) $raw, true);
        if (!is_array($links)) return [];
        return array_values(array_filter($links, static function ($link) {
            if (!is_array($link) || !is_string($link['id'] ?? null) || !is_string($link['name'] ?? null)
                || !is_string($link['url'] ?? null) || $link['id'] === '' || $link['name'] === '' || $link['url'] === '') return false;
            if (!filter_var($link['url'], FILTER_VALIDATE_URL)) return false;
            $parts = parse_url($link['url']);
            $validUrl = is_array($parts)
                && in_array(strtolower((string) ($parts['scheme'] ?? '')), ['http', 'https'], true)
                && !empty($parts['host']);
            if (!$validUrl) return false;
            if (!empty($link['avatar']) && !argonNoteIsSafeMediaUrl($link['avatar'])) $link['avatar'] = '';
            return true;
        }));
    } catch (\Throwable $error) {
        return [];
    }
}

function argonNoteHandleOwnerConsoleAction()
{
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') return;
    $action = (string) ($_POST['owner_action'] ?? '');
    $actionTabs = [
        'owner_save_post' => 'manage', 'owner_change_post_status' => 'manage',
        'owner_move_post_to_trash' => 'manage', 'owner_restore_post' => 'manage', 'owner_delete_post' => 'manage',
        'owner_change_comment_status' => 'comments', 'owner_delete_comment' => 'comments',
        'owner_add_category' => 'taxonomy', 'owner_update_category' => 'taxonomy', 'owner_delete_category' => 'taxonomy',
        'owner_add_tag' => 'taxonomy', 'owner_update_tag' => 'taxonomy', 'owner_delete_tag' => 'taxonomy',
        'owner_update_series' => 'taxonomy', 'owner_delete_series' => 'taxonomy',
        'owner_add_friend' => 'friends', 'owner_update_friend' => 'friends', 'owner_delete_friend' => 'friends',
    ];
    if (!isset($actionTabs[$action])) return;
    if (!argonNoteIsSiteOwner()) { http_response_code(403); exit('仅站主可以执行此操作。'); }
    \Widget\Security::alloc()->protect();
    $tab = $actionTabs[$action];

    try {
        $db = \Typecho\Db::get();
        if ($action === 'owner_save_post') {
            $cid = max(0, (int) ($_POST['cid'] ?? 0));
            $existing = $cid ? argonNoteOwnerFindPost($cid) : null;
            if ($cid && !$existing) { argonNoteOwnerNoticeRedirect('manage', 'invalid_input'); }
            $title = trim((string) ($_POST['title'] ?? ''));
            $text = (string) ($_POST['text'] ?? '');
            if ($title === '' || trim($text) === '') argonNoteOwnerNoticeRedirect($cid ? 'write' : 'write', 'invalid_input');
            $title = function_exists('mb_substr') ? mb_substr($title, 0, 180, 'UTF-8') : substr($title, 0, 500);
            $slug = trim((string) ($_POST['slug'] ?? ''));
            $markdown = !empty($_POST['markdown']) && \Widget\Options::alloc()->markdown;
            $text = $markdown ? '<!--markdown-->' . preg_replace('/^<!--\s*markdown\s*-->/i', '', $text) : $text;
            $isFeel = ($_POST['kind'] ?? 'article') === 'feel';
            $intent = ($_POST['intent'] ?? 'publish') === 'draft' ? 'draft' : 'publish';
            if ($existing && $existing['type'] === 'post' && $intent === 'draft') $intent = 'publish';
            $status = in_array($_POST['visibility'] ?? '', ['publish', 'hidden', 'private', 'waiting'], true)
                ? (string) $_POST['visibility'] : 'publish';
            $type = $intent === 'draft' && (!$existing || $existing['type'] === 'post_draft') ? 'post_draft' : 'post';
            $rows = [
                'title' => $title, 'slug' => $slug, 'text' => $text, 'type' => $type,
                'status' => $type === 'post_draft' ? 'publish' : $status,
                'allowComment' => !empty($_POST['allow_comment']) ? 1 : 0,
                'allowPing' => 0, 'allowFeed' => 1,
            ];
            $writer = \Widget\Contents\Post\Edit::alloc();
            if ($existing) {
                $saved = $writer->update($rows, $db->sql()->where('cid = ?', $cid)
                    ->where('type IN ?', ['post', 'post_draft']));
                if (!$saved) argonNoteOwnerNoticeRedirect('manage', 'action_error');
            } else {
                $rows['authorId'] = (int) \Widget\User::alloc()->uid;
                $rows['created'] = \Widget\Options::alloc()->time;
                $cid = $writer->insert($rows);
            }
            $categoryIds = isset($_POST['category']) && is_array($_POST['category']) ? $_POST['category'] : [];
            if ($isFeel) {
                $feel = $db->fetchRow($db->select('mid')->from('table.metas')->where('type = ?', 'category')->where('slug = ?', 'feel')->limit(1));
                $categoryIds = $feel ? [(int) $feel['mid']] : [];
            }
            argonNoteOwnerSyncPostMetas($cid, $categoryIds, (string) ($_POST['tags'] ?? ''));
            argonNoteSavePostSeries($cid, $isFeel ? '' : (string) ($_POST['series'] ?? ''));
            argonNoteOwnerNoticeRedirect('manage', 'post_saved');
        }

        if ($action === 'owner_change_post_status') {
            $cid = max(0, (int) ($_POST['cid'] ?? 0));
            $status = (string) ($_POST['status'] ?? '');
            if (!in_array($status, ['publish', 'hidden', 'private'], true)) argonNoteOwnerNoticeRedirect($tab, 'invalid_input');
            $row = $db->fetchRow($db->select('cid', 'type')->from('table.contents')->where('cid = ?', $cid)
                ->where('type = ?', 'post')->where('status <> ?', 'deleted')->limit(1));
            if (!$row) argonNoteOwnerNoticeRedirect($tab, 'invalid_input');
            $db->query($db->update('table.contents')->rows(['status' => $status, 'modified' => \Widget\Options::alloc()->time])
                ->where('cid = ?', $cid)->where('type = ?', 'post'));
            $metaIds = array_column($db->fetchAll($db->select('table.relationships.mid')->from('table.relationships')
                ->join('table.metas', 'table.relationships.mid = table.metas.mid')
                ->where('table.relationships.cid = ?', $cid)->where('table.metas.type IN ?', ['category', 'tag'])), 'mid');
            argonNoteOwnerRefreshMetaCounts($metaIds);
            argonNoteOwnerNoticeRedirect($tab, 'status_saved');
        }

        if ($action === 'owner_move_post_to_trash') {
            $moved = argonNoteOwnerMovePostToTrash((int) ($_POST['cid'] ?? 0));
            argonNoteOwnerNoticeRedirect($tab, $moved ? 'post_trashed' : 'invalid_input');
        }

        if ($action === 'owner_restore_post') {
            $restored = argonNoteOwnerRestorePost((int) ($_POST['cid'] ?? 0));
            argonNoteOwnerNoticeRedirect($tab, $restored ? 'post_restored' : 'invalid_input');
        }

        if ($action === 'owner_delete_post') {
            $deleted = argonNoteOwnerDeletePost((int) ($_POST['cid'] ?? 0));
            argonNoteOwnerNoticeRedirect($tab, $deleted ? 'post_deleted' : 'invalid_input');
        }

        if ($action === 'owner_change_comment_status') {
            $status = (string) ($_POST['status'] ?? '');
            $changed = argonNoteOwnerChangeCommentStatus((int) ($_POST['coid'] ?? 0), $status);
            $notice = ['approved' => 'comment_approved', 'waiting' => 'comment_waiting', 'spam' => 'comment_spam'][$status] ?? 'invalid_input';
            argonNoteOwnerNoticeRedirect($tab, $changed ? $notice : 'invalid_input');
        }

        if ($action === 'owner_delete_comment') {
            $deleted = argonNoteOwnerDeleteComment((int) ($_POST['coid'] ?? 0));
            argonNoteOwnerNoticeRedirect($tab, $deleted ? 'comment_deleted' : 'invalid_input');
        }

        if ($action === 'owner_add_category') {
            $name = trim(strip_tags((string) ($_POST['name'] ?? '')));
            $slugInput = trim(strip_tags((string) ($_POST['slug'] ?? '')));
            $description = trim(strip_tags((string) ($_POST['description'] ?? '')));
            if ($name === '') argonNoteOwnerNoticeRedirect($tab, 'invalid_input');
            $existing = $db->fetchRow($db->select('mid')->from('table.metas')->where('type = ?', 'category')->where('name = ?', $name)->limit(1));
            if ($existing) argonNoteOwnerNoticeRedirect($tab, 'invalid_input');
            $slug = \Typecho\Common::slugName($slugInput !== '' ? $slugInput : $name);
            if (!$slug) $slug = 'fenlei-' . substr(hash('sha256', $name), 0, 10);
            $baseSlug = $slug; $suffix = 1;
            while ($db->fetchRow($db->select('mid')->from('table.metas')->where('type = ?', 'category')->where('slug = ?', $slug)->limit(1))) $slug = $baseSlug . '-' . $suffix++;
            $order = $db->fetchObject($db->select(['MAX(order)' => 'maxOrder'])->from('table.metas')->where('type = ?', 'category')->where('parent = ?', 0));
            $db->query($db->insert('table.metas')->rows(['name' => function_exists('mb_substr') ? mb_substr($name, 0, 64, 'UTF-8') : substr($name, 0, 180), 'slug' => $slug, 'type' => 'category', 'description' => function_exists('mb_substr') ? mb_substr($description, 0, 200, 'UTF-8') : substr($description, 0, 600), 'count' => 0, 'parent' => 0, 'order' => (int) ($order->maxOrder ?? 0) + 1]));
            argonNoteOwnerNoticeRedirect($tab, 'category_added');
        }

        if ($action === 'owner_update_category') {
            $mid = max(0, (int) ($_POST['mid'] ?? 0));
            $category = $db->fetchRow($db->select('mid', 'slug')->from('table.metas')
                ->where('mid = ?', $mid)->where('type = ?', 'category')->limit(1));
            $name = trim(strip_tags((string) ($_POST['name'] ?? '')));
            $description = trim(strip_tags((string) ($_POST['description'] ?? '')));
            $slugInput = trim(strip_tags((string) ($_POST['slug'] ?? '')));
            if (!$category || $name === '') argonNoteOwnerNoticeRedirect($tab, 'invalid_input');
            $duplicateName = $db->fetchRow($db->select('mid')->from('table.metas')->where('type = ?', 'category')
                ->where('name = ?', $name)->where('mid <> ?', $mid)->limit(1));
            if ($duplicateName) argonNoteOwnerNoticeRedirect($tab, 'invalid_input');
            $slug = \Typecho\Common::slugName($slugInput !== '' ? $slugInput : $name);
            if (!$slug) $slug = 'category-' . $mid;
            if ($category['slug'] === 'feel') $slug = 'feel';
            $baseSlug = $slug; $suffix = 1;
            while ($db->fetchRow($db->select('mid')->from('table.metas')->where('type = ?', 'category')
                ->where('slug = ?', $slug)->where('mid <> ?', $mid)->limit(1))) $slug = $baseSlug . '-' . $suffix++;
            $db->query($db->update('table.metas')->rows([
                'name' => function_exists('mb_substr') ? mb_substr($name, 0, 64, 'UTF-8') : substr($name, 0, 180),
                'slug' => $slug,
                'description' => function_exists('mb_substr') ? mb_substr($description, 0, 200, 'UTF-8') : substr($description, 0, 600),
            ])->where('mid = ?', $mid)->where('type = ?', 'category'));
            argonNoteOwnerNoticeRedirect($tab, 'category_updated');
        }

        if ($action === 'owner_delete_category') {
            $mid = max(0, (int) ($_POST['mid'] ?? 0));
            $category = $db->fetchRow($db->select('mid', 'slug', 'parent')->from('table.metas')
                ->where('mid = ?', $mid)->where('type = ?', 'category')->limit(1));
            $defaultMid = (int) \Widget\Options::alloc()->defaultCategory;
            $defaultCategory = $db->fetchRow($db->select('mid')->from('table.metas')
                ->where('mid = ?', $defaultMid)->where('type = ?', 'category')->limit(1));
            if (!$defaultCategory) $defaultMid = 0;
            if (!$category || $category['slug'] === 'feel' || $mid === $defaultMid) argonNoteOwnerNoticeRedirect($tab, 'invalid_input');
            $affected = $db->fetchAll($db->select('table.contents.cid', 'table.contents.type')->from('table.relationships')
                ->join('table.contents', 'table.relationships.cid = table.contents.cid')
                ->where('table.relationships.mid = ?', $mid));
            $db->query($db->update('table.metas')->rows(['parent' => (int) $category['parent']])
                ->where('type = ?', 'category')->where('parent = ?', $mid));
            $db->query($db->delete('table.relationships')->where('mid = ?', $mid));
            foreach ($affected as $item) {
                if (!in_array($item['type'], ['post', 'post_draft'], true) || $defaultMid < 1) continue;
                $remaining = $db->fetchRow($db->select('table.relationships.cid')->from('table.relationships')
                    ->join('table.metas', 'table.relationships.mid = table.metas.mid')
                    ->where('table.relationships.cid = ?', (int) $item['cid'])->where('table.metas.type = ?', 'category')->limit(1));
                if (!$remaining) $db->query($db->insert('table.relationships')->rows(['cid' => (int) $item['cid'], 'mid' => $defaultMid]));
            }
            $db->query($db->delete('table.metas')->where('mid = ?', $mid)->where('type = ?', 'category'));
            argonNoteOwnerRefreshMetaCounts([$defaultMid]);
            argonNoteOwnerNoticeRedirect($tab, 'category_deleted');
        }

        if ($action === 'owner_add_tag') {
            $name = trim(strip_tags((string) ($_POST['name'] ?? '')));
            if ($name === '') argonNoteOwnerNoticeRedirect($tab, 'invalid_input');
            $existing = $db->fetchRow($db->select('mid')->from('table.metas')->where('type = ?', 'tag')->where('name = ?', $name)->limit(1));
            if ($existing) argonNoteOwnerNoticeRedirect($tab, 'invalid_input');
            $slug = \Typecho\Common::slugName($name);
            if (!$slug) $slug = 'tag-' . substr(hash('sha256', $name), 0, 10);
            $baseSlug = $slug; $suffix = 1;
            while ($db->fetchRow($db->select('mid')->from('table.metas')->where('type = ?', 'tag')->where('slug = ?', $slug)->limit(1))) $slug = $baseSlug . '-' . $suffix++;
            $db->query($db->insert('table.metas')->rows(['name' => function_exists('mb_substr') ? mb_substr($name, 0, 64, 'UTF-8') : substr($name, 0, 180), 'slug' => $slug, 'type' => 'tag', 'description' => '', 'count' => 0, 'parent' => 0, 'order' => 0]));
            argonNoteOwnerNoticeRedirect($tab, 'tag_added');
        }

        if ($action === 'owner_update_tag') {
            $mid = max(0, (int) ($_POST['mid'] ?? 0));
            $tag = $db->fetchRow($db->select('mid')->from('table.metas')->where('mid = ?', $mid)->where('type = ?', 'tag')->limit(1));
            $name = trim(strip_tags((string) ($_POST['name'] ?? '')));
            $slugInput = trim(strip_tags((string) ($_POST['slug'] ?? '')));
            if (!$tag || $name === '') argonNoteOwnerNoticeRedirect($tab, 'invalid_input');
            $duplicateName = $db->fetchRow($db->select('mid')->from('table.metas')->where('type = ?', 'tag')
                ->where('name = ?', $name)->where('mid <> ?', $mid)->limit(1));
            if ($duplicateName) argonNoteOwnerNoticeRedirect($tab, 'invalid_input');
            $slug = \Typecho\Common::slugName($slugInput !== '' ? $slugInput : $name);
            if (!$slug) $slug = 'tag-' . $mid;
            $baseSlug = $slug; $suffix = 1;
            while ($db->fetchRow($db->select('mid')->from('table.metas')->where('type = ?', 'tag')
                ->where('slug = ?', $slug)->where('mid <> ?', $mid)->limit(1))) $slug = $baseSlug . '-' . $suffix++;
            $db->query($db->update('table.metas')->rows([
                'name' => function_exists('mb_substr') ? mb_substr($name, 0, 64, 'UTF-8') : substr($name, 0, 180),
                'slug' => $slug,
            ])->where('mid = ?', $mid)->where('type = ?', 'tag'));
            argonNoteOwnerNoticeRedirect($tab, 'tag_updated');
        }

        if ($action === 'owner_delete_tag') {
            $mid = max(0, (int) ($_POST['mid'] ?? 0));
            $tag = $db->fetchRow($db->select('mid')->from('table.metas')->where('mid = ?', $mid)->where('type = ?', 'tag')->limit(1));
            if (!$tag) argonNoteOwnerNoticeRedirect($tab, 'invalid_input');
            $db->query($db->delete('table.relationships')->where('mid = ?', $mid));
            $db->query($db->delete('table.metas')->where('mid = ?', $mid)->where('type = ?', 'tag'));
            argonNoteOwnerNoticeRedirect($tab, 'tag_deleted');
        }

        if ($action === 'owner_update_series') {
            $oldName = trim(strip_tags((string) ($_POST['series_name'] ?? '')));
            $newName = trim(strip_tags((string) ($_POST['new_name'] ?? '')));
            if ($oldName === '' || $newName === '') argonNoteOwnerNoticeRedirect($tab, 'invalid_input');
            $newName = function_exists('mb_substr') ? mb_substr($newName, 0, 80, 'UTF-8') : substr($newName, 0, 240);
            $assignments = argonNoteSeriesAssignments();
            $changed = false;
            foreach ($assignments as $cid => $name) {
                if ($name === $oldName) { $assignments[$cid] = $newName; $changed = true; }
            }
            if (!$changed) argonNoteOwnerNoticeRedirect($tab, 'invalid_input');
            argonNoteWriteOption('argon_note_series_assignments', json_encode($assignments, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
            argonNoteOwnerNoticeRedirect($tab, 'series_updated');
        }

        if ($action === 'owner_delete_series') {
            $seriesName = trim(strip_tags((string) ($_POST['series_name'] ?? '')));
            if ($seriesName === '') argonNoteOwnerNoticeRedirect($tab, 'invalid_input');
            $assignments = argonNoteSeriesAssignments();
            $before = count($assignments);
            foreach ($assignments as $cid => $name) if ($name === $seriesName) unset($assignments[$cid]);
            if (count($assignments) === $before) argonNoteOwnerNoticeRedirect($tab, 'invalid_input');
            argonNoteWriteOption('argon_note_series_assignments', json_encode($assignments, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
            argonNoteOwnerNoticeRedirect($tab, 'series_deleted');
        }

        if ($action === 'owner_add_friend') {
            $name = trim(strip_tags((string) ($_POST['name'] ?? '')));
            $url = trim((string) ($_POST['url'] ?? ''));
            $description = trim(strip_tags((string) ($_POST['description'] ?? '')));
            $avatar = trim((string) ($_POST['avatar_url'] ?? ''));
            $uploadedAvatar = argonNoteUploadImage('avatar_image', 'friend');
            if ($uploadedAvatar !== '') $avatar = $uploadedAvatar;
            $parts = parse_url($url);
            if ($name === '' || !filter_var($url, FILTER_VALIDATE_URL)
                || !in_array(strtolower((string) ($parts['scheme'] ?? '')), ['http', 'https'], true)
                || empty($parts['host']) || ($avatar !== '' && !argonNoteIsSafeMediaUrl($avatar))) argonNoteOwnerNoticeRedirect('friends', 'invalid_input');
            $links = argonNoteFriendLinks();
            if (count($links) >= 100) argonNoteOwnerNoticeRedirect('friends', 'action_error');
            foreach ($links as $link) if (strcasecmp($link['url'], $url) === 0) argonNoteOwnerNoticeRedirect('friends', 'invalid_input');
            $links[] = ['id' => bin2hex(random_bytes(6)), 'name' => function_exists('mb_substr') ? mb_substr($name, 0, 64, 'UTF-8') : substr($name, 0, 180), 'url' => $url, 'description' => function_exists('mb_substr') ? mb_substr($description, 0, 180, 'UTF-8') : substr($description, 0, 540), 'avatar' => $avatar];
            argonNoteWriteOption('argon_note_friend_links', json_encode($links, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
            argonNoteOwnerNoticeRedirect('friends', 'friend_added');
        }

        if ($action === 'owner_update_friend') {
            $id = (string) ($_POST['id'] ?? '');
            $name = trim(strip_tags((string) ($_POST['name'] ?? '')));
            $url = trim((string) ($_POST['url'] ?? ''));
            $description = trim(strip_tags((string) ($_POST['description'] ?? '')));
            $avatar = trim((string) ($_POST['avatar_url'] ?? ''));
            $clearAvatar = !empty($_POST['remove_avatar']);
            $uploadedAvatar = argonNoteUploadImage('avatar_image', 'friend');
            if ($uploadedAvatar !== '') $avatar = $uploadedAvatar;
            $parts = parse_url($url);
            if ($id === '' || $name === '' || !filter_var($url, FILTER_VALIDATE_URL)
                || !in_array(strtolower((string) ($parts['scheme'] ?? '')), ['http', 'https'], true)
                || empty($parts['host']) || ($avatar !== '' && !argonNoteIsSafeMediaUrl($avatar))) argonNoteOwnerNoticeRedirect('friends', 'invalid_input');
            $links = argonNoteFriendLinks();
            $found = false;
            foreach ($links as &$link) {
                if ($link['id'] !== $id) {
                    if (strcasecmp($link['url'], $url) === 0) { unset($link); argonNoteOwnerNoticeRedirect('friends', 'invalid_input'); }
                    continue;
                }
                $link['name'] = function_exists('mb_substr') ? mb_substr($name, 0, 64, 'UTF-8') : substr($name, 0, 180);
                $link['url'] = $url;
                $link['description'] = function_exists('mb_substr') ? mb_substr($description, 0, 180, 'UTF-8') : substr($description, 0, 540);
                $link['avatar'] = $uploadedAvatar !== '' ? $uploadedAvatar : ($clearAvatar ? '' : ($avatar !== '' ? $avatar : (string) ($link['avatar'] ?? '')));
                $found = true;
            }
            unset($link);
            if (!$found) argonNoteOwnerNoticeRedirect('friends', 'invalid_input');
            argonNoteWriteOption('argon_note_friend_links', json_encode($links, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
            argonNoteOwnerNoticeRedirect('friends', 'friend_updated');
        }

        if ($action === 'owner_delete_friend') {
            $id = (string) ($_POST['id'] ?? '');
            $links = array_values(array_filter(argonNoteFriendLinks(), static function ($link) use ($id) { return $link['id'] !== $id; }));
            argonNoteWriteOption('argon_note_friend_links', json_encode($links, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
            argonNoteOwnerNoticeRedirect('friends', 'friend_deleted');
        }
    } catch (\Throwable $error) {
        error_log('[Morrow owner workspace] ' . $error->getMessage());
        argonNoteOwnerNoticeRedirect($tab, 'action_error');
    }
}

function argonNoteFriendPageEditUrl()
{
    $options = \Widget\Options::alloc();
    try {
        $db = \Typecho\Db::get();
        $page = $db->fetchObject($db->select('cid')->from('table.contents')
            ->where('type = ?', 'page')->where('slug = ?', 'links')->limit(1));
        if ($page) {
            return $options->adminUrl . 'write-page.php?cid=' . (int) $page->cid;
        }
    } catch (\Throwable $error) {
        // Fall through to the page editor.
    }
    return $options->adminUrl . 'write-page.php';
}

\Typecho\Plugin::factory('Widget_Abstract_Contents')->contentEx = 'argonNoteFilterOwnerSections';
