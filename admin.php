<?php
/**
 * Admin — landing pages you can SEE, and who can see the numbers.
 *
 * The first version of this page listed the landing pages as text: a name, a
 * path, six hex fields. Nobody picks a page that way. People recognise a page by
 * looking at it. So every page is now a card with a live thumbnail of the real
 * thing (an iframe of the page itself, scaled down — not a screenshot that can
 * go stale), a LIVE / PAUSED badge, and its own click-through rate. Editing
 * colours redraws a preview as you drag the picker, so you never save blind.
 *
 * Two jobs, still:
 *   1. Which landing page is running, what colour it is, what it says.
 *   2. Who can get in (approve / remove people).
 */

require __DIR__ . '/auth.php';          // must be signed in
require_once __DIR__ . '/lp-lib.php';
require_once __DIR__ . '/nav.php';

/* Any approved account can open this page. What they can DO depends on their
   role: a normal user can only set pages live or paused; an admin can edit
   pages, create pages, and manage who has access. Every admin-only action is
   checked again below — hiding a button is not security. */
$me      = auth_user();
$isAdmin = auth_is_admin($me);

$notice      = '';
$uploadError = '';
$resetLink   = '';               // set when an admin generates a reset link to copy
$LPS         = lp_variants();

/**
 * Show stored copy as plain text in an editor field.
 *
 * The copy is stored HTML-ready (the templates print it unescaped, so an "&" is
 * stored as "&amp;" and a non-breaking space as "&nbsp;"). A human editing the
 * text should never see those codes — so we decode to plain text first, then
 * escape once for safe embedding in the form field. The reverse (plain text →
 * HTML-ready) happens on save, in ed_store().
 */
function ed_show($v): string {
    return htmlspecialchars(html_entity_decode((string)$v, ENT_QUOTES | ENT_HTML5), ENT_QUOTES);
}

/** Turn what a human typed back into HTML-ready copy for storage. */
function ed_store($v): string {
    return trim(htmlspecialchars(strip_tags((string)$v), ENT_QUOTES));
}

/** The editable fields for ONE custom section. $i is the form index (a real
 *  index for existing sections, or "__IDX__" for the JS "add" template). */
function sec_editor_fields(string $i, array $s = []): string {
    $type  = $s['type']    ?? 'text';
    $at    = $s['at']      ?? 'after_hero';
    $order = (int)($s['order'] ?? 0);
    $bg    = $s['bg']      ?? 'page';
    $align = $s['align']   ?? 'left';
    $eye   = htmlspecialchars(html_entity_decode((string)($s['eyebrow']   ?? ''), ENT_QUOTES|ENT_HTML5), ENT_QUOTES);
    $head  = htmlspecialchars(html_entity_decode((string)($s['heading']   ?? ''), ENT_QUOTES|ENT_HTML5), ENT_QUOTES);
    $body  = htmlspecialchars((string)($s['body']      ?? ''), ENT_QUOTES);
    $media = htmlspecialchars((string)($s['media']     ?? ''), ENT_QUOTES);
    $cta   = htmlspecialchars((string)($s['cta_label'] ?? ''), ENT_QUOTES);

    $opt = function(array $choices, string $sel) {
        $h = '';
        foreach ($choices as $k => $label) {
            $h .= '<option value="' . htmlspecialchars($k) . '" ' . ($k === $sel ? 'selected' : '') . '>' . htmlspecialchars($label) . '</option>';
        }
        return $h;
    };

    ob_start(); ?>
    <div class="secgrid">
      <span class="fld"><label>Block</label>
        <select name="sec_type[<?= $i ?>]"><?= $opt(lp_section_types(), $type) ?></select></span>
      <span class="fld"><label>Position</label>
        <select name="sec_at[<?= $i ?>]"><?= $opt(lp_section_anchors(), $at) ?></select></span>
      <span class="fld"><label>Order</label>
        <input type="number" name="sec_order[<?= $i ?>]" value="<?= $order ?>" style="width:4rem"></span>
      <span class="fld"><label>Background</label>
        <select name="sec_bg[<?= $i ?>]"><?= $opt(['page'=>'Page','tint'=>'Tinted','dark'=>'Dark'], $bg) ?></select></span>
      <span class="fld"><label>Media side</label>
        <select name="sec_align[<?= $i ?>]"><?= $opt(['left'=>'Media left','right'=>'Media right'], $align) ?></select></span>
    </div>
    <div class="secgrid">
      <span class="fld"><label>Eyebrow (optional)</label><input type="text" name="sec_eyebrow[<?= $i ?>]" value="<?= $eye ?>"></span>
      <span class="fld"><label>Heading (optional)</label><input type="text" name="sec_heading[<?= $i ?>]" value="<?= $head ?>"></span>
    </div>
    <span class="fld"><label>Body text (optional)</label><textarea name="sec_body[<?= $i ?>]"><?= $body ?></textarea></span>
    <div class="secgrid">
      <span class="fld"><label>Media — upload image / MP4</label>
        <input type="file" name="sec_mediafile_<?= $i ?>" accept="image/jpeg,image/png,image/webp,video/mp4"></span>
      <span class="fld"><label>…or paste a YouTube / Vimeo / image URL</label>
        <input type="text" name="sec_media[<?= $i ?>]" value="<?= $media ?>" placeholder="https://…"></span>
    </div>
    <div class="secgrid">
      <span class="fld"><label>Button text (optional — blank = no button)</label><input type="text" name="sec_cta[<?= $i ?>]" value="<?= $cta ?>"></span>
      <label class="secdel"><input type="checkbox" name="sec_del[<?= $i ?>]" value="1"> Remove this section</label>
    </div>
    <?php
    return ob_get_clean();
}

/** Refuse anything an admin-only action was asked to do by a non-admin. */
function lp_admin_guard(bool $isAdmin): void {
    if (!$isAdmin) {
        http_response_code(403);
        exit('That action needs an admin account.');
    }
}

/**
 * Accept one uploaded file (a photo, or — when $allowVideo — an MP4) and return
 * its public path, or '' if none was sent.
 *
 * Rules that matter: we trust the file's real content (getimagesize / an MP4
 * signature), not its name or the browser's content-type, and we write it under
 * a name WE generate. A visitor-supplied filename is how a .php lands in a web
 * root. Photos are downscaled + recompressed so a 12MP phone shot doesn't ship
 * full-size; the server can't transcode video, so MP4s are size-capped instead.
 */
function lp_take_upload(string $field, bool $allowVideo = false): string {
    global $uploadError;

    if (empty($_FILES[$field]['name']) || ($_FILES[$field]['error'] ?? 4) === UPLOAD_ERR_NO_FILE) {
        return '';
    }
    $f = $_FILES[$field];
    if ($f['error'] !== UPLOAD_ERR_OK) { $uploadError = 'That file did not upload — try again.'; return ''; }

    $dir = __DIR__ . '/assets/uploads';
    if (!is_dir($dir) && !@mkdir($dir, 0755, true)) {
        $uploadError = 'Could not create /assets/uploads on the server.';
        return '';
    }

    /* Is it a real image? */
    $info = @getimagesize($f['tmp_name']);
    $imgExt = [IMAGETYPE_JPEG => 'jpg', IMAGETYPE_PNG => 'png', IMAGETYPE_WEBP => 'webp'][$info[2] ?? 0] ?? '';

    if ($imgExt) {
        if ($f['size'] > 12 * 1024 * 1024) { $uploadError = 'That image is over 12 MB — shrink it first.'; return ''; }
        $name = date('Ymd-His') . '-' . bin2hex(random_bytes(4)) . '.' . $imgExt;
        $dest = $dir . '/' . $name;
        if (!@move_uploaded_file($f['tmp_name'], $dest)) { $uploadError = 'Could not save the image.'; return ''; }
        lp_resize_image($dest);                      // downscale + recompress if large
        return '/assets/uploads/' . $name;
    }

    /* Otherwise, an MP4 — only where video is allowed (the media slots). */
    if ($allowVideo) {
        $sig = (string)@file_get_contents($f['tmp_name'], false, null, 0, 12);
        $isMp4 = strpos($sig, 'ftyp') !== false;     // MP4/MOV box signature
        if ($isMp4) {
            if ($f['size'] > 25 * 1024 * 1024) {
                $uploadError = 'That video is over 25 MB. Compress it (or use a YouTube/Vimeo link instead).';
                return '';
            }
            $name = date('Ymd-His') . '-' . bin2hex(random_bytes(4)) . '.mp4';
            $dest = $dir . '/' . $name;
            if (!@move_uploaded_file($f['tmp_name'], $dest)) { $uploadError = 'Could not save the video.'; return ''; }
            return '/assets/uploads/' . $name;
        }
        $uploadError = 'That file type isn’t supported — use a JPG/PNG/WebP image or an MP4 video.';
        return '';
    }

    $uploadError = 'Only JPG, PNG or WebP images, please.';
    return '';
}

/**
 * Downscale + recompress a large photo in place, using GD if it's available.
 * No-op when GD is missing or the image is already within bounds — so it never
 * breaks an upload, it just makes big ones smaller.
 */
function lp_resize_image(string $path, int $maxW = 1600, int $maxH = 2000): void {
    if (!function_exists('imagecreatetruecolor')) return;      // GD not installed — leave as-is
    $info = @getimagesize($path);
    if (!$info) return;
    [$w, $h] = $info;
    if ($w <= $maxW && $h <= $maxH) return;                    // already small enough

    $scale = min($maxW / $w, $maxH / $h);
    $nw = max(1, (int) round($w * $scale));
    $nh = max(1, (int) round($h * $scale));

    switch ($info[2]) {
        case IMAGETYPE_JPEG: $src = @imagecreatefromjpeg($path); break;
        case IMAGETYPE_PNG:  $src = @imagecreatefrompng($path);  break;
        case IMAGETYPE_WEBP: $src = @imagecreatefromwebp($path); break;
        default: return;
    }
    if (!$src) return;

    $dst = imagecreatetruecolor($nw, $nh);
    if (in_array($info[2], [IMAGETYPE_PNG, IMAGETYPE_WEBP], true)) {
        imagealphablending($dst, false);
        imagesavealpha($dst, true);
    }
    imagecopyresampled($dst, $src, 0, 0, 0, 0, $nw, $nh, $w, $h);

    switch ($info[2]) {
        case IMAGETYPE_JPEG: imagejpeg($dst, $path, 82); break;
        case IMAGETYPE_PNG:  imagepng($dst, $path, 6);   break;
        case IMAGETYPE_WEBP: imagewebp($dst, $path, 82); break;
    }
    imagedestroy($src);
    imagedestroy($dst);
}

/** Every photo already on the server — so you can reuse one without re-uploading. */
function lp_library(): array {
    $out = [];
    foreach (['assets', 'assets/uploads'] as $d) {
        foreach (glob(__DIR__ . '/' . $d . '/*.{jpg,jpeg,png,webp}', GLOB_BRACE) ?: [] as $p) {
            $out[] = '/' . $d . '/' . basename($p);
        }
    }
    return $out;
}

/** Views and clicks per landing page over the last 7 days — so each card can
 *  show whether it is actually working, not just what colour it is. */
function lp_stats7(): array {
    $out  = [];
    $from = (new DateTime('now', new DateTimeZone('UTC')))->modify('-6 days')->format('Y-m-d');

    foreach (glob(__DIR__ . '/lgc-data/events-*.csv') ?: [] as $file) {
        if (!($fh = @fopen($file, 'r'))) continue;
        while (($row = fgetcsv($fh)) !== false) {
            if (count($row) < 3) continue;
            if (substr((string)$row[0], 0, 10) < $from) continue;
            $lp = $row[6] ?? '01';                       // 7th column, added with the variants
            if (!isset($out[$lp])) $out[$lp] = ['v' => 0, 'c' => 0];
            if ($row[1] === 'view') $out[$lp]['v']++;
            if ($row[1] === 'cta')  $out[$lp]['c']++;
        }
        fclose($fh);
    }
    return $out;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $what = (string)($_POST['do'] ?? '');

    /* ---- People (admin only) ---- */
    if ($what === 'user') {
        lp_admin_guard($isAdmin);
        $email  = (string)($_POST['email'] ?? '');
        $action = (string)($_POST['action'] ?? '');
        $u      = auth_find($email);

        if ($u) {
            $isSelf = strcasecmp($u['email'], $me['email']) === 0;

            if ($action === 'approve' || $action === 'approve_admin') {
                $u['status']   = 'approved';
                $u['approved'] = gmdate('Y-m-d H:i');
                $u['role']     = $action === 'approve_admin' ? 'admin' : 'user';
                auth_put($u);
                $notice = htmlspecialchars($u['name']) . ' can now sign in'
                        . ($u['role'] === 'admin' ? ' as an admin.' : ' as a user.');

            } elseif ($action === 'make_admin' || $action === 'make_user') {
                if ($isSelf) {
                    $notice = 'You cannot change your own role.';   // never lock the last admin out
                } elseif (auth_is_bootstrap_admin($u['email']) && $action === 'make_user') {
                    $notice = htmlspecialchars($u['name']) . ' is a permanent admin and cannot be demoted.';
                } else {
                    $u['role'] = $action === 'make_admin' ? 'admin' : 'user';
                    auth_put($u);
                    $notice = htmlspecialchars($u['name']) . ' is now an ' . $u['role'] . '.';
                }

            } elseif ($action === 'remove') {
                if ($isSelf) {
                    $notice = 'You cannot remove your own account.';
                } elseif (auth_is_bootstrap_admin($u['email'])) {
                    $notice = htmlspecialchars($u['name']) . ' is a permanent admin and cannot be removed.';
                } else {
                    auth_remove($u['email']);
                    $notice = htmlspecialchars($email) . ' has been removed.';
                }

            } elseif ($action === 'resetlink') {
                /* The reliable password-reset path: generate the link here and
                   show it, so you can hand it over directly — no dependence on
                   email actually arriving. Also emailed, as a convenience. */
                $tok = auth_new_token($u['email'], 'reset');
                auth_notify_reset($u, $tok);
                $resetLink = AUTH_SITE . '/reset.php?token=' . $tok;
                $notice = 'Reset link for ' . htmlspecialchars($u['name'])
                        . ' (valid 2 hours) — copy and send it, or they’ll also get it by email:';
            }
        }
    }

    /* ---- Create a landing page: a copy of one, or a fresh page on a base
            template (admin only) ---- */
    if ($what === 'newpage') {
        lp_admin_guard($isAdmin);
        $name   = trim((string)($_POST['name'] ?? '')) ?: 'Untitled page';
        $source = (string)($_POST['source'] ?? '');

        [$ok, $msg] = lp_create_page($LPS, $source, $name);
        $LPS    = lp_variants();
        $notice = $msg;
    }

    /* ---- Flip one page live / paused (any approved user) ---- */
    if ($what === 'toggle') {
        $id = (string)($_POST['id'] ?? '');
        if (isset($LPS[$id])) {
            $LPS[$id]['live'] = empty($LPS[$id]['live']);

            if (!lp_save_variants($LPS)) {
                /* Never claim a save that didn't happen — that is exactly how the
                   old store lied about pausing pages. */
                $notice = 'Could not save — the settings file is not writable. Try again shortly.';
            } else {
                $LPS    = lp_variants();
                $notice = '“' . htmlspecialchars($LPS[$id]['name']) . '” is now '
                        . ($LPS[$id]['live'] ? 'live to the public.' : 'paused — only you can see it.');
            }
        }
    }

    /* ---- Save a page (admin only) ---- */
    if ($what === 'page') {
        lp_admin_guard($isAdmin);
        $id = (string)($_POST['id'] ?? '');
        if (isset($LPS[$id])) {
            $LPS[$id]['live'] = !empty($_POST['live']);
            $LPS[$id]['name'] = trim((string)($_POST['name'] ?? '')) ?: $LPS[$id]['name'];
            $LPS[$id]['note'] = trim((string)($_POST['note'] ?? ''));

            /* Video: empty means the block does not exist at all. An empty player
               is worse than no player, so we never render a placeholder. */
            $vid = trim((string)($_POST['video'] ?? ''));
            $LPS[$id]['video'] = (filter_var($vid, FILTER_VALIDATE_URL) || $vid === '') ? $vid : ($LPS[$id]['video'] ?? '');

            /* CTA link — where every button on the page goes. Empty falls back to
               Calendly in the template, so clearing it is safe. */
            $cta = trim((string)($_POST['cta_url'] ?? ''));
            $LPS[$id]['cta_url'] = ($cta === '' || filter_var($cta, FILTER_VALIDATE_URL)) ? $cta : ($LPS[$id]['cta_url'] ?? '');

            /* Custom sections. The form sends parallel arrays keyed by a stable
               index; we walk the indices present in sec_type and skip any the
               admin ticked for removal. Each section can carry one media item
               (uploaded file wins over a pasted URL). */
            $newSecs = [];
            foreach ((array)($_POST['sec_type'] ?? []) as $i => $stype) {
                if (!empty($_POST['sec_del'][$i])) continue;                  // removed
                $stype = in_array($stype, array_keys(lp_section_types()), true) ? $stype : 'text';
                $at    = in_array(($_POST['sec_at'][$i] ?? ''), array_keys(lp_section_anchors()), true) ? $_POST['sec_at'][$i] : 'after_hero';

                $mediaUp  = lp_take_upload('sec_mediafile_' . $i, true);
                $mediaUrl = trim((string)($_POST['sec_media'][$i] ?? ''));
                $media    = $mediaUp !== '' ? $mediaUp
                          : (filter_var($mediaUrl, FILTER_VALIDATE_URL) ? $mediaUrl : $mediaUrl);

                $newSecs[] = [
                    'type'      => $stype,
                    'at'        => $at,
                    'order'     => (int)($_POST['sec_order'][$i] ?? 0),
                    'bg'        => in_array(($_POST['sec_bg'][$i] ?? ''), ['page','tint','dark'], true) ? $_POST['sec_bg'][$i] : 'page',
                    'align'     => ($_POST['sec_align'][$i] ?? '') === 'right' ? 'right' : 'left',
                    'eyebrow'   => ed_store($_POST['sec_eyebrow'][$i] ?? ''),
                    'heading'   => ed_store($_POST['sec_heading'][$i] ?? ''),
                    'body'      => trim(strip_tags((string)($_POST['sec_body'][$i] ?? ''))),
                    'media'     => $media,
                    'cta_label' => ed_store($_POST['sec_cta'][$i] ?? ''),
                ];
            }
            $LPS[$id]['sections'] = $newSecs;

            /* Photos. Two ways to change one: upload a file, or point at a path
               that is already on the server. Uploads land in /assets/uploads/ with
               a safe generated name — never the visitor's filename. */
            foreach (['hero', 'lifestyle', 'closing'] as $slot) {
                /* Precedence: a newly uploaded file wins, then a pasted URL
                   (YouTube/Vimeo/MP4/image), then a pick from the library. The
                   library <select> defaults to a blank "keep current" option, so
                   leaving the slot untouched never clobbers a video with an image.
                   lp_media() sniffs the stored string, so we just store the raw
                   path or URL — no type flag needed. */
                $up  = lp_take_upload('img_' . $slot, true);
                $url = trim((string)($_POST['imgurl_' . $slot] ?? ''));
                $lib = trim((string)($_POST['imgpath_' . $slot] ?? ''));

                if ($up !== '') {
                    $LPS[$id]['images'][$slot] = $up;
                } elseif ($url !== '') {
                    if (filter_var($url, FILTER_VALIDATE_URL)) {
                        $LPS[$id]['images'][$slot] = $url;
                    } else {
                        $uploadError = 'That media link isn’t a valid URL.';
                    }
                } elseif ($lib !== '') {
                    $LPS[$id]['images'][$slot] = $lib;
                }
            }
            foreach (($LPS[$id]['results'] ?? []) as $i => $_r) {
                $up = lp_take_upload('res_img_' . $i);
                if ($up)                                 $LPS[$id]['results'][$i]['img'] = $up;
                elseif (isset($_POST['res_path_' . $i])) {
                    $p = trim((string)$_POST['res_path_' . $i]);
                    if ($p !== '') $LPS[$id]['results'][$i]['img'] = $p;
                }
                foreach (['name', 'result', 'weeks'] as $f) {
                    if (isset($_POST['res_' . $f . '_' . $i])) {
                        $LPS[$id]['results'][$i][$f] = ed_store($_POST['res_' . $f . '_' . $i]);
                    }
                }
            }
            if ($uploadError) $notice = $uploadError;

            foreach (['hero_bg','hero_text','accent','accent_2','page_bg','ink'] as $k) {
                $val = (string)($_POST['c_' . $k] ?? '');
                if (preg_match('/^#[0-9a-f]{6}$/i', $val)) {
                    $LPS[$id]['colors'][$k] = strtoupper($val);
                }
            }
            foreach (array_keys($LPS[$id]['text']) as $k) {
                if (isset($_POST['t_' . $k])) {
                    /* Store HTML-ready copy from the plain text the human typed. */
                    $LPS[$id]['text'][$k] = ed_store($_POST['t_' . $k]);
                }
            }
            if (!lp_save_variants($LPS)) {
                $notice = 'Could not save — the settings file is not writable. Try again shortly.';
            } else {
                $LPS    = lp_variants();
                $notice = $uploadError ?: 'Saved “' . htmlspecialchars($LPS[$id]['name']) . '”.'
                        . ($LPS[$id]['live'] ? ' It is live.' : ' It is paused — only you can see it.');
            }
        }
    }
}

$stats   = lp_stats7();
$library = lp_library();
$users   = auth_users();
$pending = array_values(array_filter($users, fn($u) => ($u['status'] ?? '') === 'pending'));
$active  = array_values(array_filter($users, fn($u) => ($u['status'] ?? '') === 'approved'));
$refused = array_values(array_filter($users, fn($u) => ($u['status'] ?? '') === 'declined'));

$labels = [
    'eyebrow'         => 'Eyebrow (small line above the headline)',
    'headline'        => 'Headline',
    'headline_accent' => 'Headline — accent line',
    'sub'             => 'Sub-headline',
    'cta_label'       => 'Button text',
    'cta_label_short' => 'Button text (mobile bar)',
    'cta_note'        => 'Note under the button',
    'band_title'      => 'Mid-page band — title',
    'band_accent'     => 'Mid-page band — accent',
    'band_body'       => 'Mid-page band — body',
    'closing_title'   => 'Closing — title',
    'closing_accent'  => 'Closing — accent',
    'closing_body'    => 'Closing — body',
];
$swatches = [
    'hero_bg'   => 'Hero background',
    'hero_text' => 'Hero text',
    'accent'    => 'Buttons',
    'accent_2'  => 'Second accent',
    'page_bg'   => 'Page background',
    'ink'       => 'Body text',
];
?><!DOCTYPE html>
<html lang="en-GB"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title>Landing pages — Luke Goulden</title>
<style>
  :root{--teal:#1A3C34;--coral:#E05A3A;--sage:#84B59F;--off:#F7F5F0;--ink:#1E1E1E;
        --muted:#6b6b6b;--line:rgba(30,30,30,.12)}
  *{box-sizing:border-box}
  body{margin:0;background:var(--off);color:var(--ink);
       font:16px/1.6 system-ui,-apple-system,"Segoe UI",sans-serif}
  .wrap{max-width:74rem;margin:0 auto;padding:2.5rem 1.25rem 6rem}
  h1{font-size:1.8rem;font-weight:800;color:var(--teal);letter-spacing:-.015em;margin:0 0 .25rem}
  .sub{color:var(--muted);font-size:.92rem;margin:0 0 2rem;max-width:44rem}
  h2{font-size:.75rem;letter-spacing:.14em;text-transform:uppercase;color:var(--teal);
     margin:3rem 0 1rem;padding-top:1.75rem;border-top:1px solid var(--line);font-weight:800}
  h2:first-of-type{border-top:0;padding-top:0;margin-top:0}
  .notice{background:rgba(132,181,159,.2);color:#2f6a55;padding:.85rem 1.1rem;border-radius:10px;
          font-size:.9rem;margin-bottom:1.75rem;font-weight:600}

  /* ---- Landing page cards ---------------------------------------------- */
  .cards{display:grid;grid-template-columns:repeat(auto-fill,minmax(21rem,1fr));gap:1.5rem}
  .card{background:#fff;border:1px solid var(--line);border-radius:16px;overflow:hidden;
        display:flex;flex-direction:column;box-shadow:0 1px 2px rgba(0,0,0,.03)}
  .card.paused{opacity:.82}

  /* A live iframe of the real page, scaled down. Not a screenshot — it cannot
     go stale, and a paused page still previews for you because you're signed in. */
  .thumb{position:relative;display:block;height:15rem;overflow:hidden;background:#e9e6e0;
         border-bottom:1px solid var(--line);text-decoration:none}
  .thumb iframe{width:1440px;height:1500px;border:0;
                transform:scale(.26);transform-origin:top left;pointer-events:none}
  .thumb .veil{position:absolute;inset:0;background:transparent;transition:background .15s}
  .thumb:hover .veil{background:rgba(26,60,52,.14)}
  .thumb .open{position:absolute;left:50%;top:50%;transform:translate(-50%,-50%) scale(.94);
        background:rgba(255,255,255,.96);color:var(--teal);border-radius:99px;
        padding:.55rem 1rem;font-size:.68rem;font-weight:800;letter-spacing:.1em;
        text-transform:uppercase;opacity:0;transition:.15s;white-space:nowrap}
  .thumb:hover .open{opacity:1;transform:translate(-50%,-50%) scale(1)}
  .badge{position:absolute;top:.75rem;left:.75rem;z-index:2;font-size:.6rem;font-weight:800;
         letter-spacing:.12em;text-transform:uppercase;padding:.3rem .6rem;border-radius:99px;
         box-shadow:0 1px 3px rgba(0,0,0,.15)}
  .badge.on{background:#84B59F;color:#10241f}
  .badge.off{background:#1E1E1E;color:#fff}
  .badge.home{background:#fff;color:var(--teal);left:auto;right:.75rem}

  .meta{padding:1.1rem 1.15rem;display:flex;flex-direction:column;gap:.75rem;flex:1}
  .meta .top{display:flex;align-items:flex-start;gap:.75rem}
  .meta b{color:var(--teal);font-size:1.02rem;line-height:1.3}
  .meta .path{font-size:.75rem;color:var(--muted);font-family:ui-monospace,SFMono-Regular,Menlo,monospace}
  .meta .note{font-size:.84rem;color:var(--muted);margin:0}
  .dots{display:flex;gap:.2rem;margin-left:auto;flex:none}
  .dot{width:.85rem;height:.85rem;border-radius:99px;border:1px solid rgba(0,0,0,.12)}

  .nums{display:flex;gap:1.25rem;padding-top:.5rem;border-top:1px solid var(--line)}
  .nums div{line-height:1.2}
  .nums .n{font-size:1.1rem;font-weight:800;color:var(--teal);font-variant-numeric:tabular-nums}
  .nums .l{font-size:.62rem;letter-spacing:.1em;text-transform:uppercase;color:var(--muted);font-weight:700}
  .nums .n.zero{color:#b9b6b0}

  .acts{display:flex;gap:.5rem;padding:0 1.15rem 1.15rem;flex-wrap:wrap}
  button,.btn{border:0;cursor:pointer;font:inherit;font-weight:800;font-size:.68rem;letter-spacing:.1em;
              text-transform:uppercase;padding:.62rem .95rem;border-radius:7px;text-decoration:none;
              display:inline-flex;align-items:center;gap:.35rem}
  .primary{background:var(--coral);color:#fff}
  .ghost{background:transparent;color:var(--teal);border:1px solid var(--line)}
  .ghost:hover{background:rgba(26,60,52,.05)}

  /* ---- Editor ----------------------------------------------------------- */
  dialog::backdrop{background:rgba(15,38,33,.55)}
  dialog{border:0;border-radius:18px;padding:0;width:min(52rem,94vw);max-height:92vh;
         box-shadow:0 30px 90px rgba(0,0,0,.35)}
  .ed{display:flex;flex-direction:column;max-height:92vh}
  .ed header{display:flex;align-items:center;gap:1rem;padding:1.25rem 1.5rem;
             border-bottom:1px solid var(--line);position:sticky;top:0;background:#fff;z-index:3}
  .ed header b{color:var(--teal);font-size:1.05rem;flex:1}
  .ed .close{background:transparent;border:1px solid var(--line);color:var(--muted);
             border-radius:99px;width:2rem;height:2rem;padding:0;justify-content:center;font-size:1rem}
  .ed .scroll{overflow:auto;padding:1.5rem}

  /* Live preview — redraws as you drag a colour picker, so nothing is saved blind. */
  .prev{border-radius:12px;overflow:hidden;border:1px solid var(--line);margin-bottom:1.5rem}
  .prev .hero{padding:1.5rem 1.4rem}
  .prev .eyebrow{font-size:.6rem;font-weight:800;letter-spacing:.16em;text-transform:uppercase;margin-bottom:.5rem}
  .prev .h{font-size:1.5rem;font-weight:800;letter-spacing:-.02em;line-height:1.15;margin:0}
  .prev .h em{display:block;font-style:normal;font-size:.66em;font-weight:600;margin-top:.25rem}
  .prev .s{font-size:.82rem;margin:.6rem 0 1rem;opacity:.78}
  .prev .b{display:inline-block;padding:.6rem 1rem;border-radius:6px;font-size:.66rem;
           font-weight:800;letter-spacing:.1em;text-transform:uppercase}
  .prev .page{padding:1rem 1.4rem;font-size:.8rem}

  .fields{display:grid;grid-template-columns:repeat(auto-fit,minmax(8.5rem,1fr));gap:.85rem;margin-bottom:1.5rem}
  .fld label{display:block;font-size:.63rem;letter-spacing:.1em;text-transform:uppercase;
             color:var(--muted);font-weight:800;margin-bottom:.35rem}
  .fld input[type=color]{width:100%;height:2.5rem;border:1px solid var(--line);border-radius:8px;
                         background:#fff;padding:.18rem;cursor:pointer}
  .fld input[type=text],.fld textarea{width:100%;padding:.62rem .7rem;border:1px solid var(--line);
                                      border-radius:8px;font:inherit;font-size:.9rem;background:#fff}
  .fld textarea{min-height:4.5rem;resize:vertical}
  .texts{display:grid;gap:.9rem;grid-template-columns:1fr 1fr}
  .texts .wide{grid-column:1/-1}

  /* Photos in the editor. Each slot shows the picture that is actually on the
     page right now — a filename tells you nothing about whether it's the right
     photo. Upload a new one, or reuse one already on the server. */
  .edh{font-size:.68rem;letter-spacing:.13em;text-transform:uppercase;color:var(--muted);
       font-weight:800;margin:1.75rem 0 .85rem;padding-top:1.25rem;border-top:1px solid var(--line)}
  .pics{display:grid;grid-template-columns:repeat(auto-fill,minmax(9.5rem,1fr));gap:.9rem;margin-bottom:1rem}
  .pic{border:1px solid var(--line);border-radius:12px;padding:.6rem;display:grid;gap:.4rem;background:#fbfaf8}
  .pic-img{aspect-ratio:1/1;border-radius:8px;overflow:hidden;background:#eee}
  .pic-img img{width:100%;height:100%;object-fit:cover;display:block}
  .pic-vid{width:100%;height:100%;display:grid;place-items:center;background:var(--teal);
           color:#fff;font-size:.7rem;font-weight:800;letter-spacing:.08em;text-transform:uppercase}
  .pic label{font-size:.62rem;letter-spacing:.09em;text-transform:uppercase;color:var(--muted);font-weight:800}
  .pic input[type=file]{font-size:.68rem;width:100%}
  .pic input[type=text],.pic select{width:100%;padding:.35rem .45rem;border:1px solid var(--line);
                                    border-radius:6px;font:inherit;font-size:.76rem;background:#fff}

  /* Sections editor */
  .secrow{border:1px solid var(--line);border-radius:12px;padding:1rem;margin-bottom:.9rem;background:#fbfaf8}
  .secrow-h{display:flex;align-items:center;gap:.6rem;margin-bottom:.75rem}
  .secrow-h b{font-size:.72rem;letter-spacing:.1em;text-transform:uppercase;color:var(--teal)}
  .secrow-h .sectype{font-size:.68rem;color:var(--muted)}
  .secgrid{display:grid;grid-template-columns:repeat(auto-fit,minmax(9rem,1fr));gap:.7rem;margin-bottom:.7rem}
  .secrow .fld{display:block;margin-bottom:.7rem}
  .secrow label{display:block;font-size:.62rem;letter-spacing:.08em;text-transform:uppercase;color:var(--muted);font-weight:800;margin-bottom:.3rem}
  .secrow input[type=text],.secrow input[type=number],.secrow select,.secrow textarea{
    width:100%;padding:.5rem .6rem;border:1px solid var(--line);border-radius:7px;font:inherit;font-size:.86rem;background:#fff}
  .secrow textarea{min-height:4rem;resize:vertical}
  .secrow input[type=file]{font-size:.72rem}
  .secdel{display:inline-flex;align-items:center;gap:.4rem;font-size:.76rem!important;letter-spacing:0!important;
          text-transform:none!important;color:#a33d24;font-weight:700;align-self:end;margin-bottom:.5rem}
  .add-sec{margin-top:.25rem}
  .ed footer{display:flex;align-items:center;gap:.75rem;padding:1.1rem 1.5rem;
             border-top:1px solid var(--line);position:sticky;bottom:0;background:#fff;flex-wrap:wrap}
  .switch{display:inline-flex;align-items:center;gap:.5rem;font-size:.7rem;font-weight:800;
          letter-spacing:.1em;text-transform:uppercase;color:var(--teal);margin-right:auto}

  /* ---- People ----------------------------------------------------------- */
  .row{display:flex;align-items:center;gap:1rem;background:#fff;border:1px solid var(--line);
       border-radius:12px;padding:.95rem 1.15rem;margin-bottom:.6rem;flex-wrap:wrap}
  .row .who{flex:1;min-width:12rem}
  .row .who b{display:block;color:var(--teal)}
  .row .who span{font-size:.82rem;color:var(--muted)}
  .row .who .mail{font-size:.68rem;color:#a33d24;font-weight:700}
  .pill{font-size:.62rem;letter-spacing:.1em;text-transform:uppercase;font-weight:800;
        padding:.25rem .55rem;border-radius:99px;background:rgba(132,181,159,.22);color:#2f6a55}
  .empty{background:#fff;border:1px dashed var(--line);border-radius:12px;padding:1.75rem;
         text-align:center;color:var(--muted);font-size:.9rem}

  /* Page header with the New-page button */
  .pagehead{display:flex;align-items:flex-start;justify-content:space-between;gap:1.5rem;margin-bottom:2rem}
  .primary.big{padding:.8rem 1.3rem;font-size:.74rem;flex:none;white-space:nowrap}

  /* Role management */
  .urow{display:flex;gap:.5rem;flex-wrap:wrap;align-items:center}
  .ok2{background:var(--teal);color:#fff}
  .ghost.danger{color:#a33d24;border-color:rgba(163,61,36,.3)}
  .ghost.danger:hover{background:rgba(163,61,36,.06)}
  .rolepill{display:inline-block;font-size:.56rem;letter-spacing:.1em;text-transform:uppercase;
            font-weight:800;padding:.2rem .5rem;border-radius:99px;margin-left:.5rem;vertical-align:middle}
  .rolepill.admin{background:rgba(224,90,58,.14);color:#a33d24}
  .rolepill.user{background:rgba(30,30,30,.08);color:var(--muted)}

  @media(max-width:40em){ .texts{grid-template-columns:1fr} .thumb{height:12rem} .pagehead{flex-direction:column} }
</style></head><body>

<?php lgc_nav('admin'); ?>

<div class="wrap">
  <div class="pagehead">
    <div>
      <h1>Landing pages</h1>
      <p class="sub">
        Point a campaign at whichever page suits it, and pause the rest. A paused page returns
        “not found” to the public — you can still open it yourself to check the work.
        <?php if (!$isAdmin): ?>
          <br><b>You’re signed in as a user</b> — you can set pages live or paused. Ask an admin for anything more.
        <?php endif; ?>
      </p>
    </div>
    <?php if ($isAdmin): ?>
      <button class="primary big" type="button" id="newpage-open">+ New page</button>
    <?php endif; ?>
  </div>

  <?php if ($notice): ?><div class="notice"><?= $notice ?></div><?php endif; ?>
  <?php if ($resetLink): ?>
    <div class="notice" style="background:rgba(224,90,58,.1);color:#8a2318">
      <input type="text" readonly value="<?= htmlspecialchars($resetLink) ?>"
             onclick="this.select()"
             style="width:100%;padding:.6rem .7rem;border:1px solid rgba(30,30,30,.2);border-radius:6px;
                    font:inherit;font-size:.82rem;background:#fff">
    </div>
  <?php endif; ?>

  <div class="cards">
  <?php foreach ($LPS as $id => $v):
      $C  = $v['colors'];
      $s  = $stats[$id] ?? ['v' => 0, 'c' => 0];
      $ct = $s['v'] > 0 ? round($s['c'] / $s['v'] * 100, 1) : 0;
  ?>
    <div class="card <?= $v['live'] ? '' : 'paused' ?>">
      <a class="thumb" href="<?= htmlspecialchars($v['path']) ?>" target="_blank" rel="noopener"
         aria-label="Open <?= htmlspecialchars($v['name']) ?> in a new tab">
        <span class="badge <?= $v['live'] ? 'on' : 'off' ?>"><?= $v['live'] ? 'Live' : 'Paused' ?></span>
        <?php if ($v['path'] === '/'): ?><span class="badge home">Homepage</span><?php endif; ?>
        <iframe src="<?= htmlspecialchars($v['path']) ?>?preview=1" loading="lazy" scrolling="no"
                title="Preview of <?= htmlspecialchars($v['name']) ?>" tabindex="-1"></iframe>
        <span class="veil"></span>
        <span class="open">Open the real page ↗</span>
      </a>

      <div class="meta">
        <div class="top">
          <div>
            <b><?= htmlspecialchars($v['name']) ?></b>
            <div class="path"><?= htmlspecialchars($v['path']) ?></div>
          </div>
          <span class="dots">
            <?php foreach (['hero_bg','accent','accent_2','page_bg'] as $k): ?>
              <span class="dot" style="background:<?= htmlspecialchars($C[$k]) ?>"></span>
            <?php endforeach; ?>
          </span>
        </div>

        <p class="note"><?= htmlspecialchars($v['note'] ?? '') ?></p>

        <div class="nums">
          <div><div class="n <?= $s['v'] ? '' : 'zero' ?>"><?= number_format($s['v']) ?></div><div class="l">Views · 7d</div></div>
          <div><div class="n <?= $s['c'] ? '' : 'zero' ?>"><?= number_format($s['c']) ?></div><div class="l">Clicks</div></div>
          <div><div class="n <?= $s['c'] ? '' : 'zero' ?>"><?= $ct ?>%</div><div class="l">Click rate</div></div>
        </div>
      </div>

      <div class="acts">
        <?php if ($isAdmin): ?>
          <button class="primary" type="button" data-edit="<?= htmlspecialchars($id) ?>">Edit</button>
        <?php endif; ?>
        <form method="post" style="display:contents">
          <input type="hidden" name="do" value="toggle">
          <input type="hidden" name="id" value="<?= htmlspecialchars($id) ?>">
          <button class="<?= $isAdmin ? 'ghost' : 'primary' ?>" type="submit"><?= $v['live'] ? 'Pause' : 'Go live' ?></button>
        </form>
        <a class="btn ghost" href="/report/?lp=<?= htmlspecialchars($id) ?>">Numbers</a>
      </div>
    </div>

    <?php if ($isAdmin): ?>
    <!-- Editor for this page (admin only) -->
    <dialog id="ed-<?= htmlspecialchars($id) ?>">
      <form class="ed" method="post" enctype="multipart/form-data">
        <input type="hidden" name="do" value="page">
        <input type="hidden" name="id" value="<?= htmlspecialchars($id) ?>">

        <header>
          <b>Edit — <?= htmlspecialchars($v['name']) ?></b>
          <button class="close" type="button" data-close="<?= htmlspecialchars($id) ?>" aria-label="Close">✕</button>
        </header>

        <div class="scroll">
          <div class="prev" data-prev="<?= htmlspecialchars($id) ?>">
            <div class="hero" style="background:<?= $C['hero_bg'] ?>;color:<?= $C['hero_text'] ?>">
              <div class="eyebrow" style="color:<?= $C['accent_2'] ?>"><?= htmlspecialchars(html_entity_decode($v['text']['eyebrow'])) ?></div>
              <p class="h">
                <?= htmlspecialchars(html_entity_decode($v['text']['headline'])) ?>
                <em style="color:<?= $C['accent_2'] ?>"><?= htmlspecialchars(html_entity_decode($v['text']['headline_accent'])) ?></em>
              </p>
              <p class="s"><?= htmlspecialchars(html_entity_decode($v['text']['sub'])) ?></p>
              <span class="b" style="background:<?= $C['accent'] ?>;color:#fff"><?= htmlspecialchars(html_entity_decode($v['text']['cta_label'])) ?></span>
            </div>
            <div class="page" style="background:<?= $C['page_bg'] ?>;color:<?= $C['ink'] ?>">
              Body text sits on the page background — check it stays readable.
            </div>
          </div>

          <div class="fields">
            <?php foreach ($swatches as $k => $label): ?>
              <span class="fld">
                <label for="<?= $id . $k ?>"><?= $label ?></label>
                <input id="<?= $id . $k ?>" type="color" name="c_<?= $k ?>"
                       data-c="<?= $k ?>" value="<?= htmlspecialchars(strtolower($C[$k])) ?>">
              </span>
            <?php endforeach; ?>
          </div>

          <!-- Media. Each slot can be a photo, an uploaded MP4, or a YouTube /
               Vimeo link. Photos are auto-resized on upload. -->
          <h4 class="edh">Photos &amp; video</h4>
          <p style="margin:-.4rem 0 .9rem;color:var(--muted);font-size:.78rem">
            Upload a photo or an MP4, or paste a YouTube/Vimeo link — it fills the same spot.
            Big photos are shrunk automatically; keep MP4s under 25&nbsp;MB (or use a link).
          </p>
          <div class="pics">
            <?php foreach (['hero' => 'Hero', 'lifestyle' => 'Lifestyle', 'closing' => 'Closing'] as $slot => $slabel):
                  $cur = $v['images'][$slot] ?? '';
                  $curType = lp_media($cur)['type'];
                  $inLib   = in_array($cur, $library, true); ?>
              <div class="pic">
                <div class="pic-img">
                  <?php if ($curType === 'image'): ?>
                    <img src="<?= htmlspecialchars(is_string($cur) ? $cur : '') ?>" alt="" loading="lazy">
                  <?php else: ?>
                    <div class="pic-vid"><span><?= $curType === 'embed' ? '▶ Video link' : '▶ MP4 video' ?></span></div>
                  <?php endif; ?>
                </div>
                <label><?= $slabel ?></label>
                <input type="file" name="img_<?= $slot ?>" accept="image/jpeg,image/png,image/webp,video/mp4">
                <input type="text" name="imgurl_<?= $slot ?>" placeholder="…or paste a YouTube/Vimeo link">
                <select name="imgpath_<?= $slot ?>">
                  <option value="">— keep current —</option>
                  <?php foreach ($library as $p): ?>
                    <option value="<?= htmlspecialchars($p) ?>" <?= ($inLib && $p === $cur) ? 'selected' : '' ?>>
                      reuse: <?= htmlspecialchars(basename($p)) ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>
            <?php endforeach; ?>
          </div>

          <h4 class="edh">Before &amp; after grid</h4>
          <div class="pics">
            <?php foreach (($v['results'] ?? []) as $i => $r): ?>
              <div class="pic">
                <div class="pic-img"><img src="<?= htmlspecialchars($r['img']) ?>" alt="" loading="lazy"></div>
                <input type="file" name="res_img_<?= $i ?>" accept="image/jpeg,image/png,image/webp">
                <select name="res_path_<?= $i ?>">
                  <?php foreach ($library as $p): ?>
                    <option value="<?= htmlspecialchars($p) ?>" <?= $p === $r['img'] ? 'selected' : '' ?>>
                      <?= htmlspecialchars(basename($p)) ?>
                    </option>
                  <?php endforeach; ?>
                </select>
                <input type="text" name="res_name_<?= $i ?>"   value="<?= ed_show($r['name']) ?>"   placeholder="Name">
                <input type="text" name="res_result_<?= $i ?>" value="<?= ed_show($r['result']) ?>" placeholder="Result">
                <input type="text" name="res_weeks_<?= $i ?>"  value="<?= ed_show($r['weeks']) ?>"  placeholder="Timeframe">
              </div>
            <?php endforeach; ?>
          </div>

          <h4 class="edh">Words</h4>
          <div class="texts">
            <span class="fld">
              <label for="<?= $id ?>name">Name (only you see this)</label>
              <input id="<?= $id ?>name" type="text" name="name" value="<?= htmlspecialchars($v['name']) ?>">
            </span>
            <span class="fld">
              <label for="<?= $id ?>note">When to use it</label>
              <input id="<?= $id ?>note" type="text" name="note" value="<?= htmlspecialchars($v['note'] ?? '') ?>">
            </span>
            <span class="fld wide">
              <label for="<?= $id ?>video">Video URL — leave empty and no video block appears</label>
              <input id="<?= $id ?>video" type="text" name="video" value="<?= htmlspecialchars($v['video'] ?? '') ?>"
                     placeholder="https://player.vimeo.com/video/… or https://www.youtube.com/embed/…">
            </span>
            <span class="fld wide">
              <label for="<?= $id ?>cta">Button link — where every button on this page goes (blank = Luke’s Calendly)</label>
              <input id="<?= $id ?>cta" type="text" name="cta_url" value="<?= htmlspecialchars($v['cta_url'] ?? '') ?>"
                     placeholder="https://calendly.com/lukegouldenpt/coachingcall">
            </span>

            <?php foreach ($v['text'] as $k => $val):
                  $wide = in_array($k, ['sub','band_body','closing_body','headline'], true); ?>
              <span class="fld <?= $wide ? 'wide' : '' ?>">
                <label for="<?= $id . $k ?>"><?= $labels[$k] ?? $k ?></label>
                <?php if ($wide): ?>
                  <textarea id="<?= $id . $k ?>" name="t_<?= $k ?>" data-t="<?= $k ?>"><?= ed_show($val) ?></textarea>
                <?php else: ?>
                  <input id="<?= $id . $k ?>" type="text" name="t_<?= $k ?>" data-t="<?= $k ?>" value="<?= ed_show($val) ?>">
                <?php endif; ?>
              </span>
            <?php endforeach; ?>
          </div>

          <h4 class="edh">Extra sections</h4>
          <p style="margin:-.4rem 0 .9rem;color:var(--muted);font-size:.78rem">
            Drop in your own blocks between the built-in sections — a full-width video, a text block,
            an image, or media beside text. Choose where each one sits and in what order.
          </p>
          <div class="secs" data-secs="<?= htmlspecialchars($id) ?>">
            <?php foreach (($v['sections'] ?? []) as $si => $sec): ?>
              <div class="secrow">
                <div class="secrow-h"><b>Section</b><span class="sectype"><?= htmlspecialchars(lp_section_types()[$sec['type'] ?? 'text'] ?? 'Block') ?></span></div>
                <?= sec_editor_fields((string)$si, $sec) ?>
              </div>
            <?php endforeach; ?>
          </div>
          <button class="ghost add-sec" type="button" data-add="<?= htmlspecialchars($id) ?>">+ Add section</button>

          <!-- Blank section, cloned by JS when you click "Add section". -->
          <template id="sectpl-<?= htmlspecialchars($id) ?>">
            <div class="secrow">
              <div class="secrow-h"><b>Section</b><span class="sectype">New</span></div>
              <?= sec_editor_fields('__IDX__') ?>
            </div>
          </template>
        </div>

        <footer>
          <label class="switch">
            <input type="checkbox" name="live" value="1" <?= $v['live'] ? 'checked' : '' ?>>
            Live to the public
          </label>
          <button class="ghost" type="button" data-close="<?= htmlspecialchars($id) ?>">Cancel</button>
          <button class="primary" type="submit">Save page</button>
        </footer>
      </form>
    </dialog>
    <?php endif; /* $isAdmin — editor */ ?>
  <?php endforeach; ?>
  </div>

  <?php if ($isAdmin): ?>
  <!-- ============ PEOPLE (admin only) ============ -->

  <h2>Waiting for you<?= $pending ? ' (' . count($pending) . ')' : '' ?></h2>
  <?php if (!$pending): ?>
    <div class="empty">Nobody is waiting. New requests appear here — you don’t need the email to arrive.</div>
  <?php else: foreach ($pending as $u): ?>
    <div class="row">
      <div class="who">
        <b><?= htmlspecialchars($u['name']) ?></b>
        <span><?= htmlspecialchars($u['email']) ?> · asked <?= htmlspecialchars((string)($u['added'] ?? '')) ?></span>
        <?php if (isset($u['mailed']) && $u['mailed'] === false): ?>
          <span class="mail">Heads-up email to you didn’t send — approve right here.</span>
        <?php endif; ?>
      </div>
      <form method="post" class="urow">
        <input type="hidden" name="do" value="user">
        <input type="hidden" name="email" value="<?= htmlspecialchars($u['email']) ?>">
        <button class="primary" name="action" value="approve" type="submit" title="Can only pause/unpause pages">Approve as user</button>
        <button class="ok2" name="action" value="approve_admin" type="submit" title="Full control">Approve as admin</button>
        <button class="ghost" name="action" value="remove" type="submit">Decline</button>
      </form>
    </div>
  <?php endforeach; endif; ?>

  <h2>Has access (<?= count($active) ?>)</h2>
  <p style="margin:-.5rem 0 1rem;color:var(--muted);font-size:.84rem">
    <b>Users</b> can only set pages live or paused. <b>Admins</b> can edit pages, create pages and manage people.
  </p>
  <?php foreach ($active as $u):
        $urole   = auth_role($u);
        $isSelf  = strcasecmp($u['email'], $me['email']) === 0;
        $isBoot  = auth_is_bootstrap_admin($u['email']); ?>
    <div class="row">
      <div class="who">
        <b><?= htmlspecialchars($u['name']) ?>
          <span class="rolepill <?= $urole ?>"><?= $urole === 'admin' ? 'Admin' : 'User' ?></span>
          <?php if ($isSelf): ?><span class="pill">You</span><?php endif; ?>
        </b>
        <span><?= htmlspecialchars($u['email']) ?><?= $isBoot ? ' · permanent admin' : '' ?></span>
      </div>
      <form method="post" class="urow">
        <input type="hidden" name="do" value="user">
        <input type="hidden" name="email" value="<?= htmlspecialchars($u['email']) ?>">
        <button class="ghost" name="action" value="resetlink" type="submit"
                title="Generate a one-time link to set a new password">Reset link</button>
        <?php if (!$isSelf && !$isBoot): ?>
          <?php if ($urole === 'admin'): ?>
            <button class="ghost" name="action" value="make_user" type="submit">Make user</button>
          <?php else: ?>
            <button class="ghost" name="action" value="make_admin" type="submit">Make admin</button>
          <?php endif; ?>
          <button class="ghost danger" name="action" value="remove" type="submit">Remove</button>
        <?php endif; ?>
      </form>
    </div>
  <?php endforeach; ?>

  <?php if ($refused): ?>
    <h2>Declined (<?= count($refused) ?>)</h2>
    <?php foreach ($refused as $u): ?>
      <div class="row">
        <div class="who">
          <b><?= htmlspecialchars($u['name']) ?></b>
          <span><?= htmlspecialchars($u['email']) ?> · cannot sign in</span>
        </div>
        <form method="post" class="urow">
          <input type="hidden" name="do" value="user">
          <input type="hidden" name="email" value="<?= htmlspecialchars($u['email']) ?>">
          <button class="ghost" name="action" value="approve" type="submit">Let them in as user</button>
          <button class="ghost" name="action" value="remove" type="submit">Remove</button>
        </form>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>
  <?php endif; /* $isAdmin — people */ ?>
</div>

<?php if ($isAdmin): ?>
<!-- New-page dialog (admin only) -->
<dialog id="newpage">
  <form class="ed" method="post">
    <input type="hidden" name="do" value="newpage">
    <header>
      <b>New landing page</b>
      <button class="close" type="button" id="newpage-close" aria-label="Close">✕</button>
    </header>
    <div class="scroll">
      <p style="margin:0 0 1.25rem;color:var(--muted);font-size:.9rem">
        A new page is created <b>paused</b>, so nothing goes public by accident. Edit it, then set it live.
      </p>
      <span class="fld" style="display:block;margin-bottom:1rem">
        <label for="np-name">Name (only you see this)</label>
        <input id="np-name" type="text" name="name" placeholder="e.g. Spring campaign — women" required>
      </span>
      <span class="fld" style="display:block">
        <label for="np-source">Start from</label>
        <select id="np-source" name="source">
          <optgroup label="A fresh page on a base template">
            <?php foreach (lp_templates() as $key => $t): ?>
              <option value="tpl:<?= htmlspecialchars($key) ?>"><?= htmlspecialchars($t['label']) ?></option>
            <?php endforeach; ?>
          </optgroup>
          <optgroup label="A copy of an existing page">
            <?php foreach ($LPS as $id => $v): ?>
              <option value="copy:<?= htmlspecialchars($id) ?>">Copy of <?= htmlspecialchars($v['name']) ?> (<?= htmlspecialchars($v['path']) ?>)</option>
            <?php endforeach; ?>
          </optgroup>
        </select>
      </span>
      <p style="margin:1.25rem 0 0;color:var(--muted);font-size:.8rem">
        Want a brand-new <em>layout</em> that none of the base templates offers? That’s a code change —
        ask your developer to add it, and it will appear in this list.
      </p>
    </div>
    <footer>
      <span style="margin-right:auto"></span>
      <button class="ghost" type="button" id="newpage-cancel">Cancel</button>
      <button class="primary" type="submit">Create page</button>
    </footer>
  </form>
</dialog>
<?php endif; ?>

<script>
  /* Open / close the editors. <dialog> gives us the focus trap and Esc for free. */
  document.querySelectorAll('[data-edit]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      document.getElementById('ed-' + btn.dataset.edit).showModal();
    });
  });
  document.querySelectorAll('[data-close]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      document.getElementById('ed-' + btn.dataset.close).close();
    });
  });

  /* Add a section: clone the page's blank template, give every field a fresh
     unique index so nothing collides on save, and append it to the list. */
  var secSeq = Date.now();
  document.querySelectorAll('[data-add]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var pageId = btn.dataset.add;
      var tpl    = document.getElementById('sectpl-' + pageId);
      var list   = document.querySelector('[data-secs="' + pageId + '"]');
      if (!tpl || !list) return;
      var idx  = 'n' + (secSeq++);
      var html = tpl.innerHTML.replace(/__IDX__/g, idx);
      var wrap = document.createElement('div');
      wrap.innerHTML = html.trim();
      list.appendChild(wrap.firstChild);
    });
  });

  /* New-page dialog. */
  var np = document.getElementById('newpage');
  var npOpen = document.getElementById('newpage-open');
  if (np && npOpen) {
    npOpen.addEventListener('click', function () { np.showModal(); });
    ['newpage-close', 'newpage-cancel'].forEach(function (id) {
      var el = document.getElementById(id);
      if (el) el.addEventListener('click', function () { np.close(); });
    });
  }

  /* Redraw the preview as the colours and copy change. The point is that nobody
     saves a scheme they haven't seen — the old page made you guess from six hex
     values and then reload the site to find out. */
  document.querySelectorAll('.ed').forEach(function (form) {
    var prev = form.querySelector('[data-prev]');
    if (!prev) return;

    var hero    = prev.querySelector('.hero'),
        page    = prev.querySelector('.page'),
        eyebrow = prev.querySelector('.eyebrow'),
        head    = prev.querySelector('.h'),
        accent  = prev.querySelector('.h em'),
        sub     = prev.querySelector('.s'),
        button  = prev.querySelector('.b');

    function colour(name) {
      var el = form.querySelector('[data-c="' + name + '"]');
      return el ? el.value : '#000000';
    }
    function text(name) {
      var el = form.querySelector('[data-t="' + name + '"]');
      return el ? el.value : '';
    }
    function redraw() {
      hero.style.background   = colour('hero_bg');
      hero.style.color        = colour('hero_text');
      eyebrow.style.color     = colour('accent_2');
      accent.style.color      = colour('accent_2');
      button.style.background = colour('accent');
      page.style.background   = colour('page_bg');
      page.style.color        = colour('ink');

      eyebrow.textContent = text('eyebrow');
      head.childNodes[0].nodeValue = text('headline') + ' ';
      accent.textContent  = text('headline_accent');
      sub.textContent     = text('sub');
      button.textContent  = text('cta_label');
    }

    form.addEventListener('input', redraw);
  });
</script>
</body></html>
