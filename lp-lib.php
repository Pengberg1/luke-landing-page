<?php
/**
 * Variant helpers — shared by the landing pages (lp.php), the admin editor and
 * the reports. Kept separate so the report can read the list of variants
 * without accidentally rendering a landing page.
 */

const LP_VARIANTS_FILE = __DIR__ . '/lgc-data/variants.php';

function lp_variants(): array {
    return file_exists(LP_VARIANTS_FILE) ? (include LP_VARIANTS_FILE) : [];
}

function lp_save_variants(array $v): bool {
    $php = "<?php\n/* Landing page variants — edited via /admin.php */\nreturn " . var_export($v, true) . ";\n";
    return (bool) @file_put_contents(LP_VARIANTS_FILE, $php, LOCK_EX);
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
