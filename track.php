<?php
/**
 * Luke Goulden Coaching — first-party landing page tracker.
 *
 * Why this exists: lukegoulden.com has no analytics of any kind (verified
 * 2026-07-13), so nothing measures whether this page actually drives
 * applications. This is the smallest thing that answers the only questions
 * that matter: how many people see the page, how many click through to the
 * application form, and WHICH cta produced the click.
 *
 * Privacy by design — this is why no consent banner is needed:
 *   - no cookies, no localStorage, no fingerprinting
 *   - no IP addresses stored, ever
 *   - no user-agent strings stored (only "m" or "d")
 *   - referrer reduced to a bare hostname, no query strings
 * Nothing written here is personal data under GDPR.
 */

header('Content-Type: image/gif');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

// Always return a 1x1 transparent GIF, whatever happens — the page must never
// be affected by tracking failing.
function done() {
    echo base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7');
    exit;
}

// --- Bot filter (rough, but keeps the numbers honest) ---
$ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
if ($ua === '' || preg_match('/bot|crawl|spider|slurp|bing|preview|monitor|curl|wget|headless|lighthouse|pingdom|uptime/i', $ua)) {
    done();
}

// --- Validate input strictly: this endpoint accepts a fixed vocabulary only ---
$event = $_GET['e'] ?? '';
if (!in_array($event, ['view', 'cta'], true)) {
    done();
}

$allowedCtas = ['header', 'hero', 'after_results', 'after_included', 'closing', 'sticky_mobile'];
$cta = $_GET['c'] ?? '';
if ($event === 'cta' && !in_array($cta, $allowedCtas, true)) {
    done();
}
if ($event === 'view') {
    $cta = '-';
}

// Device class only — never the full UA string.
$device = preg_match('/Mobile|Android|iPhone|iPad|iPod/i', $ua) ? 'm' : 'd';

// Referrer reduced to a hostname. Strips query strings (which can carry PII).
$ref = '-';
if (!empty($_GET['r'])) {
    $host = parse_url($_GET['r'], PHP_URL_HOST);
    if ($host) {
        $ref = substr(preg_replace('/[^a-z0-9.\-]/i', '', $host), 0, 60);
    }
}

// Campaign source, so paid traffic can be told apart from organic/direct.
$src = '-';
if (!empty($_GET['s'])) {
    $src = substr(preg_replace('/[^a-z0-9_\-]/i', '', $_GET['s']), 0, 40);
}

// WHICH landing page produced this event. Without it, running several campaign
// variants at once would blend into one meaningless average.
$lp = '01';
if (!empty($_GET['lp']) && preg_match('/^[0-9]{2}$/', (string)$_GET['lp'])) {
    $lp = (string)$_GET['lp'];
}

// --- Append the event ---
$dir = __DIR__ . '/lgc-data';
if (!is_dir($dir)) {
    @mkdir($dir, 0755, true);
}

$file = $dir . '/events-' . gmdate('Y-m') . '.csv';

// Safety valve: never let a runaway loop fill the webspace.
if (file_exists($file) && filesize($file) > 8 * 1024 * 1024) {
    done();
}

/* Column 7 (lp) was added later; readers default to '01' when it's absent, so
   the events logged before variants existed still make sense. */
$row = sprintf(
    "%s,%s,%s,%s,%s,%s,%s\n",
    gmdate('Y-m-d H:i:s'),
    $event,
    $cta,
    $device,
    $ref,
    $src,
    $lp
);

@file_put_contents($file, $row, FILE_APPEND | LOCK_EX);

done();
