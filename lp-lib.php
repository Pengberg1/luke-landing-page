<?php
/**
 * Variant helpers — shared by the landing pages (lp.php), the admin editor and
 * the report. Kept separate so the report can read the list of variants without
 * accidentally rendering a landing page.
 *
 * WHY THE STORE IS JSON, NOT PHP
 * ------------------------------
 * It used to be a .php file read back with include(). This host caches compiled
 * PHP (opcache), so a read straight after a write returned the OLD file: the
 * admin saved your change, then immediately re-read the previous version and
 * showed it back to you. Pause did nothing. Go-live did nothing. Colour changes
 * "didn't save". The write was never the problem — the read was.
 *
 * JSON is data. It is never compiled, so a read always sees the last write.
 * Writes are atomic (temp file, then rename) so a crash mid-write cannot leave a
 * half-written file that takes every landing page down.
 *
 * lgc-data/ is blocked from the web by .htaccess, so the file isn't public.
 */

const LP_STORE  = __DIR__ . '/lgc-data/variants.json';
const LP_LEGACY = __DIR__ . '/lgc-data/variants.php';   // migrated from, once

function lp_variants(): array {
    if (is_file(LP_STORE)) {
        $data = json_decode((string) @file_get_contents(LP_STORE), true);
        if (is_array($data) && $data) return $data;
    }

    /* First run after the fix: carry the old PHP store over, then never look at
       it again. Whatever was live stays live. */
    if (is_file(LP_LEGACY)) {
        $data = include LP_LEGACY;
        if (is_array($data) && $data) {
            lp_save_variants($data);
            return $data;
        }
    }
    return [];
}

function lp_save_variants(array $v): bool {
    $json = json_encode($v, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    if ($json === false) return false;

    $tmp = LP_STORE . '.tmp';
    if (@file_put_contents($tmp, $json, LOCK_EX) === false) return false;
    if (!@rename($tmp, LP_STORE)) { @unlink($tmp); return false; }
    return true;
}

/* --------------------------------------------------------------------------
 * Templates and page creation.
 *
 * A "template" is a base layout — a PHP file that renders the page (lp-body.php,
 * lp-body-light.php). Each has a canonical example variant whose content is used
 * to seed a fresh page. Adding a genuinely new layout is a code change (a new
 * lp-body-*.php plus one line here); once added, it appears in the admin's
 * "New page" menu and pages can be spun up on it without touching code again.
 * ------------------------------------------------------------------------ */
function lp_templates(): array {
    return [
        'classic' => ['label' => 'Classic — bold, dark, high-contrast', 'seed' => '01'],
        'light'   => ['label' => 'Light — airy, modern, warm',          'seed' => '05'],
    ];
}

/** Next free two-digit id, e.g. '06'. */
function lp_next_id(array $LPS): string {
    $max = 0;
    foreach (array_keys($LPS) as $id) {
        if (preg_match('/^\d+$/', (string)$id)) $max = max($max, (int)$id);
    }
    return str_pad((string)($max + 1), 2, '0', STR_PAD_LEFT);
}

/**
 * Create a new landing page and return [bool ok, string message].
 *
 * $source is either:
 *   'copy:NN'   — an exact duplicate of an existing page
 *   'tpl:key'   — a fresh page seeded from a base template's example
 *
 * The page is created PAUSED (never surprise the public with a half-built page),
 * added to the store, and given its own /_NN/ folder + loader on disk so the URL
 * works immediately. $LPS is updated in place.
 */
function lp_create_page(array &$LPS, string $source, string $name): array {
    $seed = null;

    if (strncmp($source, 'copy:', 5) === 0) {
        $srcId = substr($source, 5);
        if (isset($LPS[$srcId])) $seed = $LPS[$srcId];
    } elseif (strncmp($source, 'tpl:', 4) === 0) {
        $key = substr($source, 4);
        $tpl = lp_templates()[$key] ?? null;
        if ($tpl && isset($LPS[$tpl['seed']])) {
            $seed = $LPS[$tpl['seed']];
            $seed['template'] = $key;
        }
    }
    if (!$seed) return [false, 'Could not create the page — unknown source.'];

    $id   = lp_next_id($LPS);
    $path = '/_' . $id . '/';

    $seed['name'] = $name;
    $seed['note'] = 'New page — edit me.';
    $seed['live'] = false;      // always born paused
    $seed['path'] = $path;
    unset($seed['video']);       // a copied video URL rarely belongs on the new page
    $seed['video'] = '';

    $LPS[$id] = $seed;
    if (!lp_save_variants($LPS)) {
        unset($LPS[$id]);
        return [false, 'Could not save the new page — the settings file is not writable.'];
    }

    /* The URL only works if /_NN/index.php exists. PHP can write it — the same
       way it writes uploads and the JSON store. */
    $dir = __DIR__ . '/_' . $id;
    if (!is_dir($dir)) @mkdir($dir, 0755, true);
    $loader = "<?php\n/* Landing page {$id} — created in /admin.php. All logic lives in lp.php. */\n"
            . "\$LP_ID = '{$id}';\nrequire __DIR__ . '/../lp.php';\n";
    @file_put_contents($dir . '/index.php', $loader, LOCK_EX);

    if (!is_file($dir . '/index.php')) {
        return [true, 'Page saved, but its folder could not be written — tell your developer. It shows here but the URL ' . $path . ' may 404.'];
    }
    return [true, 'Created “' . $name . '” at ' . $path . ' — paused. Edit it, then set it live.'];
}

/* --------------------------------------------------------------------------
 * Media slots — a hero/lifestyle/closing slot can be a photo, an uploaded MP4,
 * or a YouTube/Vimeo link. All three fill the same container the photo did.
 *
 * A slot is stored as either a plain string (a path, the legacy + image case)
 * or ['type' => 'image'|'mp4'|'embed', 'src' => '…']. lp_media() normalises both
 * and is forgiving: it sniffs a bare URL so a pasted link still works even if it
 * was stored as a string.
 * ------------------------------------------------------------------------ */
function lp_media($val): array {
    if (is_array($val)) {
        $t = in_array(($val['type'] ?? ''), ['image','mp4','embed'], true) ? $val['type'] : 'image';
        return ['type' => $t, 'src' => (string)($val['src'] ?? '')];
    }
    $s = trim((string)$val);
    if ($s === '')                                             return ['type' => 'image', 'src' => ''];
    if (preg_match('~youtube\.com|youtu\.be|vimeo\.com~i', $s)) return ['type' => 'embed', 'src' => $s];
    if (preg_match('~\.mp4($|\?)~i', $s))                      return ['type' => 'mp4',   'src' => $s];
    return ['type' => 'image', 'src' => $s];
}

/** YouTube/Vimeo watch URL → embeddable src (keeps a &t=/start= timestamp). */
function lp_embed_src(string $url): string {
    if (preg_match('~(?:youtube\.com/watch\?[^ ]*v=|youtu\.be/|youtube\.com/embed/)([A-Za-z0-9_-]{6,})~i', $url, $m)) {
        $start = '';
        if (preg_match('~[?&](?:t|start)=(\d+)~', $url, $mm)) $start = '?start=' . $mm[1];
        return 'https://www.youtube.com/embed/' . $m[1] . $start;
    }
    if (preg_match('~vimeo\.com/(?:video/)?(\d+)~i', $url, $m)) {
        return 'https://player.vimeo.com/video/' . $m[1];
    }
    return $url;   // already an embed URL, or something we don't recognise
}

/**
 * Render a media slot so it fills its container exactly like the photo it
 * replaces. The container must be position:relative; the returned element is
 * absolutely positioned to cover it (see the .lp-fill rule in each template).
 *
 * $o: ['alt' => …, 'pos' => object-position for image/mp4, 'class' => extra].
 * Video (mp4 or embed) is interactive — the visitor presses play.
 */
function lp_media_fill($val, array $o = []): string {
    $m   = lp_media($val);
    $cls = trim('lp-fill ' . ($o['class'] ?? ''));
    $pos = $o['pos'] ?? 'center';
    $alt = htmlspecialchars((string)($o['alt'] ?? ''), ENT_QUOTES);

    if ($m['type'] === 'embed') {
        $src = htmlspecialchars(lp_embed_src($m['src']), ENT_QUOTES);
        return '<iframe class="' . $cls . ' lp-fill--v" src="' . $src . '" title="' . $alt . '" loading="lazy" '
             . 'allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" '
             . 'allowfullscreen></iframe>';
    }
    if ($m['type'] === 'mp4') {
        $src = htmlspecialchars($m['src'], ENT_QUOTES);
        return '<video class="' . $cls . ' lp-fill--v" controls playsinline preload="metadata" '
             . 'style="object-position:' . htmlspecialchars($pos, ENT_QUOTES) . '">'
             . '<source src="' . $src . '" type="video/mp4"></video>';
    }
    $src = htmlspecialchars($m['src'] ?: '/assets/luke-hero.jpg', ENT_QUOTES);
    return '<img class="' . $cls . '" src="' . $src . '" alt="' . $alt . '" loading="lazy" '
         . 'style="object-position:' . htmlspecialchars($pos, ENT_QUOTES) . '">';
}

/** True when the slot is a video (mp4/embed) — templates drop photo overlays then. */
function lp_is_video($val): bool {
    return in_array(lp_media($val)['type'], ['mp4', 'embed'], true);
}

/** Lighten (+) or darken (-) a hex colour by a percentage. */
function lp_shade(string $hex, float $pct): string {
    $hex = ltrim($hex, '#');
    if (strlen($hex) === 3) {
        $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
    }
    $out = '#';
    for ($i = 0; $i < 3; $i++) {
        $c = hexdec(substr($hex, $i * 2, 2));
        $c = $pct >= 0
            ? $c + (255 - $c) * ($pct / 100)          // toward white
            : $c + $c * ($pct / 100);                  // toward black
        $out .= str_pad(dechex((int) max(0, min(255, round($c)))), 2, '0', STR_PAD_LEFT);
    }
    return $out;
}

/** Hex → rgba(), for the translucent surfaces the design relies on. */
function lp_alpha(string $hex, float $a): string {
    $hex = ltrim($hex, '#');
    if (strlen($hex) === 3) {
        $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
    }
    return sprintf(
        'rgba(%d,%d,%d,%.2f)',
        hexdec(substr($hex, 0, 2)),
        hexdec(substr($hex, 2, 2)),
        hexdec(substr($hex, 4, 2)),
        $a
    );
}

/** Is this scheme dark? Decides which way surfaces step (lighter vs darker). */
function lp_is_dark(string $hex): bool {
    $hex = ltrim($hex, '#');
    $r = hexdec(substr($hex, 0, 2));
    $g = hexdec(substr($hex, 2, 2));
    $b = hexdec(substr($hex, 4, 2));
    return (0.299 * $r + 0.587 * $g + 0.114 * $b) < 128;   // perceived brightness
}
