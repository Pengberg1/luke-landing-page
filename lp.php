<?php
/**
 * Landing page engine.
 *
 * Every variant of the page — /, /_02/, /_03/, /_04/ — is this one file with a
 * different colour scheme and different copy. There is deliberately no second
 * copy of the HTML: four copies would mean every fix has to be made four times,
 * and they would drift apart within a week.
 *
 * A loader sets $LP_ID and includes this:
 *     $LP_ID = '02';  require __DIR__ . '/../lp.php';
 */

require_once __DIR__ . '/auth-lib.php';

require_once __DIR__ . '/lp-lib.php';

/* ---------------------------------------------------------------- */

$LP_ID    = $LP_ID ?? '01';
$variants = lp_variants();

if (!isset($variants[$LP_ID])) {
    http_response_code(404);
    exit('Not found');
}

$V = $variants[$LP_ID];
$C = $V['colors'];
$T = $V['text'];
$IMG = $V['images']  ?? [];
$RES = $V['results'] ?? [];
$VID = trim((string)($V['video'] ?? ''));
$DARK_UI = lp_is_dark($C['page_bg']);

/* Not live? The public gets a 404 — a paused campaign page must not be
   reachable. Signed-in users still see it, so a page can be built and
   reviewed before it goes out. */
if (empty($V['live'])) {
    if (session_status() === PHP_SESSION_NONE) {
        session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax', 'secure' => true]);
        session_start();
    }
    $signedIn = !empty($_SESSION['lgc_user']) && auth_find($_SESSION['lgc_user']);
    if (!$signedIn) {
        http_response_code(404);
        ?><!DOCTYPE html><html lang="en-GB"><head><meta charset="utf-8">
        <meta name="robots" content="noindex, nofollow"><title>Not found</title>
        <style>body{margin:0;min-height:100vh;display:grid;place-items:center;background:#1A3C34;color:#F7F5F0;
        font:16px/1.6 system-ui,sans-serif;text-align:center;padding:2rem}</style></head>
        <body><div><h1 style="font-weight:800;letter-spacing:-.02em">This page isn’t running.</h1>
        <p style="opacity:.75">The campaign it belongs to isn’t live right now.</p>
        <p><a href="/" style="color:#84B59F">Go to the main page</a></p></div></body></html><?php
        exit;
    }
}

/* Two builds, not one. A light page written for women cannot be the dark page
   with different hex codes — the layout, the rhythm and the register all differ.
   Everything else (colours, copy, images, tracking, the live switch) is shared. */
$tpl = ($V['template'] ?? 'classic') === 'light' ? 'lp-body-light.php' : 'lp-body.php';

require __DIR__ . '/' . $tpl;
