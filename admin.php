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

$me = auth_user();
if (!auth_is_admin($me)) {
    http_response_code(403);
    exit('Not allowed.');
}

$notice      = '';
$uploadError = '';
$LPS         = lp_variants();

/**
 * Accept one uploaded photo and return its public path, or '' if none was sent.
 *
 * Rules that matter: we trust the file's real image type (getimagesize), not its
 * name or the browser's content-type header, and we write it under a name WE
 * generate. A visitor-supplied filename is how a .php lands in a web root.
 */
function lp_take_upload(string $field): string {
    global $uploadError;

    if (empty($_FILES[$field]['name']) || ($_FILES[$field]['error'] ?? 4) === UPLOAD_ERR_NO_FILE) {
        return '';
    }
    $f = $_FILES[$field];

    if ($f['error'] !== UPLOAD_ERR_OK) { $uploadError = 'That image did not upload — try again.'; return ''; }
    if ($f['size'] > 8 * 1024 * 1024)  { $uploadError = 'That image is over 8 MB. Shrink it first.'; return ''; }

    $info = @getimagesize($f['tmp_name']);          // real content, not the filename
    $ext  = [IMAGETYPE_JPEG => 'jpg', IMAGETYPE_PNG => 'png', IMAGETYPE_WEBP => 'webp'][$info[2] ?? 0] ?? '';
    if (!$ext) { $uploadError = 'Only JPG, PNG or WebP images, please.'; return ''; }

    $dir = __DIR__ . '/assets/uploads';
    if (!is_dir($dir) && !@mkdir($dir, 0755, true)) {
        $uploadError = 'Could not create /assets/uploads on the server.';
        return '';
    }

    $name = date('Ymd-His') . '-' . bin2hex(random_bytes(4)) . '.' . $ext;
    if (!@move_uploaded_file($f['tmp_name'], $dir . '/' . $name)) {
        $uploadError = 'Could not save the image on the server.';
        return '';
    }
    return '/assets/uploads/' . $name;
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

    /* ---- People ---- */
    if ($what === 'user') {
        $email  = (string)($_POST['email'] ?? '');
        $action = (string)($_POST['action'] ?? '');
        $u      = auth_find($email);

        if ($u) {
            if ($action === 'approve') {
                $u['status']   = 'approved';
                $u['approved'] = gmdate('Y-m-d H:i');
                auth_put($u);
                $notice = htmlspecialchars($u['name']) . ' can now sign in.';
            } elseif ($action === 'remove') {
                if (strcasecmp($u['email'], $me['email']) === 0) {
                    $notice = 'You cannot remove your own account.';   // never lock yourself out
                } else {
                    auth_remove($u['email']);
                    $notice = htmlspecialchars($email) . ' has been removed.';
                }
            }
        }
    }

    /* ---- Flip one page live / paused, without opening the editor ---- */
    if ($what === 'toggle') {
        $id = (string)($_POST['id'] ?? '');
        if (isset($LPS[$id])) {
            $LPS[$id]['live'] = empty($LPS[$id]['live']);
            lp_save_variants($LPS);
            $LPS    = lp_variants();
            $notice = '“' . htmlspecialchars($LPS[$id]['name']) . '” is now '
                    . ($LPS[$id]['live'] ? 'live to the public.' : 'paused — only you can see it.');
        }
    }

    /* ---- Save a page ---- */
    if ($what === 'page') {
        $id = (string)($_POST['id'] ?? '');
        if (isset($LPS[$id])) {
            $LPS[$id]['live'] = !empty($_POST['live']);
            $LPS[$id]['name'] = trim((string)($_POST['name'] ?? '')) ?: $LPS[$id]['name'];
            $LPS[$id]['note'] = trim((string)($_POST['note'] ?? ''));

            /* Video: empty means the block does not exist at all. An empty player
               is worse than no player, so we never render a placeholder. */
            $vid = trim((string)($_POST['video'] ?? ''));
            $LPS[$id]['video'] = (filter_var($vid, FILTER_VALIDATE_URL) || $vid === '') ? $vid : ($LPS[$id]['video'] ?? '');

            /* Photos. Two ways to change one: upload a file, or point at a path
               that is already on the server. Uploads land in /assets/uploads/ with
               a safe generated name — never the visitor's filename. */
            foreach (['hero', 'lifestyle', 'closing'] as $slot) {
                $up = lp_take_upload('img_' . $slot);
                if ($up)                             $LPS[$id]['images'][$slot] = $up;
                elseif (isset($_POST['imgpath_' . $slot])) {
                    $p = trim((string)$_POST['imgpath_' . $slot]);
                    if ($p !== '') $LPS[$id]['images'][$slot] = $p;
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
                        $LPS[$id]['results'][$i][$f] = trim(strip_tags((string)$_POST['res_' . $f . '_' . $i]));
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
                    /* Copy is written by hand, not pasted from the web: strip tags. */
                    $LPS[$id]['text'][$k] = trim(strip_tags((string)$_POST['t_' . $k]));
                }
            }
            lp_save_variants($LPS);
            $LPS    = lp_variants();
            $notice = 'Saved “' . htmlspecialchars($LPS[$id]['name']) . '”.'
                    . ($LPS[$id]['live'] ? ' It is live.' : ' It is paused — only you can see it.');
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
  .pic label{font-size:.62rem;letter-spacing:.09em;text-transform:uppercase;color:var(--muted);font-weight:800}
  .pic input[type=file]{font-size:.68rem;width:100%}
  .pic input[type=text],.pic select{width:100%;padding:.35rem .45rem;border:1px solid var(--line);
                                    border-radius:6px;font:inherit;font-size:.76rem;background:#fff}
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

  @media(max-width:40em){ .texts{grid-template-columns:1fr} .thumb{height:12rem} }
</style></head><body>

<?php lgc_nav('admin'); ?>

<div class="wrap">
  <h1>Landing pages</h1>
  <p class="sub">
    Four versions of the same page. Point a campaign at whichever one suits it, and pause the rest.
    A paused page returns “not found” to the public — you can still open it yourself to check the work.
  </p>

  <?php if ($notice): ?><div class="notice"><?= $notice ?></div><?php endif; ?>

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
        <button class="primary" type="button" data-edit="<?= htmlspecialchars($id) ?>">Edit</button>
        <form method="post" style="display:contents">
          <input type="hidden" name="do" value="toggle">
          <input type="hidden" name="id" value="<?= htmlspecialchars($id) ?>">
          <button class="ghost" type="submit"><?= $v['live'] ? 'Pause' : 'Go live' ?></button>
        </form>
        <a class="btn ghost" href="/report/?lp=<?= htmlspecialchars($id) ?>">Numbers</a>
      </div>
    </div>

    <!-- Editor for this page -->
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
              <span class="b" style="background:<?= $C['accent'] ?>;color:#fff"><?= htmlspecialchars($v['text']['cta_label']) ?></span>
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

          <!-- Photos. Every image on the page, swappable here — upload a new one
               or pick one already on the server. -->
          <h4 class="edh">Photos</h4>
          <div class="pics">
            <?php foreach (['hero' => 'Hero portrait', 'lifestyle' => 'Lifestyle photo', 'closing' => 'Closing photo'] as $slot => $slabel):
                  $cur = $v['images'][$slot] ?? ''; ?>
              <div class="pic">
                <div class="pic-img"><img src="<?= htmlspecialchars($cur) ?>" alt="" loading="lazy"></div>
                <label><?= $slabel ?></label>
                <input type="file" name="img_<?= $slot ?>" accept="image/jpeg,image/png,image/webp">
                <select name="imgpath_<?= $slot ?>">
                  <?php foreach ($library as $p): ?>
                    <option value="<?= htmlspecialchars($p) ?>" <?= $p === $cur ? 'selected' : '' ?>>
                      <?= htmlspecialchars(basename($p)) ?>
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
                <input type="text" name="res_name_<?= $i ?>"   value="<?= htmlspecialchars($r['name']) ?>"   placeholder="Name">
                <input type="text" name="res_result_<?= $i ?>" value="<?= htmlspecialchars($r['result']) ?>" placeholder="Result">
                <input type="text" name="res_weeks_<?= $i ?>"  value="<?= htmlspecialchars($r['weeks']) ?>"  placeholder="Timeframe">
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

            <?php foreach ($v['text'] as $k => $val):
                  $wide = in_array($k, ['sub','band_body','closing_body','headline'], true); ?>
              <span class="fld <?= $wide ? 'wide' : '' ?>">
                <label for="<?= $id . $k ?>"><?= $labels[$k] ?? $k ?></label>
                <?php if ($wide): ?>
                  <textarea id="<?= $id . $k ?>" name="t_<?= $k ?>" data-t="<?= $k ?>"><?= htmlspecialchars($val) ?></textarea>
                <?php else: ?>
                  <input id="<?= $id . $k ?>" type="text" name="t_<?= $k ?>" data-t="<?= $k ?>" value="<?= htmlspecialchars($val) ?>">
                <?php endif; ?>
              </span>
            <?php endforeach; ?>
          </div>
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
  <?php endforeach; ?>
  </div>

  <h2>Waiting for you<?= $pending ? ' (' . count($pending) . ')' : '' ?></h2>
  <?php if (!$pending): ?>
    <div class="empty">Nobody is waiting. New requests appear here — you don’t need the email to arrive.</div>
  <?php else: foreach ($pending as $u): ?>
    <div class="row">
      <div class="who">
        <b><?= htmlspecialchars($u['name']) ?></b>
        <span><?= htmlspecialchars($u['email']) ?> · asked <?= htmlspecialchars((string)($u['added'] ?? '')) ?></span>
        <?php if (isset($u['mailed']) && $u['mailed'] === false): ?>
          <span class="mail">The email to you failed — approve here instead.</span>
        <?php endif; ?>
      </div>
      <form method="post" style="display:flex;gap:.5rem">
        <input type="hidden" name="do" value="user">
        <input type="hidden" name="email" value="<?= htmlspecialchars($u['email']) ?>">
        <button class="primary" name="action" value="approve" type="submit">Approve</button>
        <button class="ghost" name="action" value="remove" type="submit">Decline</button>
      </form>
    </div>
  <?php endforeach; endif; ?>

  <h2>Has access (<?= count($active) ?>)</h2>
  <?php foreach ($active as $u): ?>
    <div class="row">
      <div class="who">
        <b><?= htmlspecialchars($u['name']) ?></b>
        <span><?= htmlspecialchars($u['email']) ?></span>
      </div>
      <?php if (strcasecmp($u['email'], $me['email']) === 0): ?>
        <span class="pill">You</span>
      <?php else: ?>
        <form method="post">
          <input type="hidden" name="do" value="user">
          <input type="hidden" name="email" value="<?= htmlspecialchars($u['email']) ?>">
          <button class="ghost" name="action" value="remove" type="submit">Remove access</button>
        </form>
      <?php endif; ?>
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
        <form method="post" style="display:flex;gap:.5rem">
          <input type="hidden" name="do" value="user">
          <input type="hidden" name="email" value="<?= htmlspecialchars($u['email']) ?>">
          <button class="ghost" name="action" value="approve" type="submit">Let them in after all</button>
          <button class="ghost" name="action" value="remove" type="submit">Remove</button>
        </form>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>
</div>

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
