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
