<?php
declare(strict_types=1);

require_once __DIR__ . '/config.inc.php';
\Widget\Init::alloc();

header('Cache-Control: private, no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('X-Robots-Tag: noindex, nofollow, noarchive');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: no-referrer');
header("Content-Security-Policy: sandbox allow-scripts; base-uri 'none'; form-action 'none'; frame-ancestors 'self'");
header('X-Frame-Options: SAMEORIGIN');

$user = \Widget\User::alloc();
$asset = $_GET['asset'] ?? '';
$assets = [
    'career-plan-v3' => '/var/lib/typecho-private/embedded_career_plan_V3_2026_2029.html',
];

if (!$user->hasLogin() || (int) $user->uid !== 1 || !$user->pass('administrator', true)) {
    \Typecho\Response::getInstance()->setStatus(404);
    http_response_code(404);
    exit;
}

$path = $assets[$asset] ?? null;
if ($path === null || !is_file($path) || !is_readable($path)) {
    \Typecho\Response::getInstance()->setStatus(404);
    http_response_code(404);
    exit;
}

header('Content-Type: text/html; charset=UTF-8');
header('Content-Disposition: inline; filename="embedded_career_plan_V3_2026_2029.html"');
readfile($path);
