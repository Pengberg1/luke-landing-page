<?php
/**
 * Admin — access approvals + landing page control.
 *
 * Two jobs in one place:
 *   1. Who can see the report (approve / remove people).
 *   2. Which landing page is running, what colours it uses, and what it says.
 *
 * The email notification for access requests is a convenience; THIS page is the
 * source of truth, so a lost email can never leave someone locked out.
 */

require __DIR__ . '/auth.php';          // must be signed in
require_once __DIR__ . '/lp-lib.php';
require_once __DIR__ . '/nav.php';

$me = auth_user();
if (!$me || !in_array(strtolower($me['email']), array_map('strtolower', AUTH_ADMINS), true)) {
    http_response_code(403);
    exit('Not allowed.');
}

$notice = '';
$LPS    = lp_variants();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $what = (string)($_POST['do'] ?? '');

    /* ---- People ---- */
    if ($what === 'user') {
        $email  = (string)($_POST['email'] ?? '');
        $action = (string)($_POST['action'] ?? '');
        $users  = auth_users();

        foreach ($users as $i => $u) {
            if (strcasecmp($u['email'], $email) !== 0) continue;

            if ($action === 'approve') {
                $users[$i]['status']   = 'approved';
                $users[$i]['approved'] = gmdate('Y-m-d H:i');
                $notice = htmlspecialchars($u['name']) . ' can now sign in.';
            } elseif ($action === 'remove') {
                if (strcasecmp($u['email'], $me['email']) === 0) {   // never lock yourself out
                    $notice = 'You cannot remove your own account.';
                    break;
                }
                array_splice($users, $i, 1);
                $notice = htmlspecialchars($email) . ' has been removed.';
            }
            auth_save_users($users);
            break;
        }
    }

    /* ---- A landing page ---- */
    if ($what === 'page') {
        $id = (string)($_POST['id'] ?? '');
        if (isset($LPS[$id])) {
            $LPS[$id]['live'] = !empty($_POST['live']);
            $LPS[$id]['name'] = trim((string)($_POST['name'] ?? $LPS[$id]['name'])) ?: $LPS[$id]['name'];
            $LPS[$id]['note'] = trim((string)($_POST['note'] ?? ''));

            foreach (['hero_bg','hero_text','accent','accent_2','page_bg','ink'] as $k) {
                $val = (string)($_POST['c_' . $k] ?? '');
                if (preg_match('/^#[0-9a-f]{6}$/i', $val)) {
                    $LPS[$id]['colors'][$k] = strtoupper($val);
                }
            }
            foreach (array_keys($LPS[$id]['text']) as $k) {
                if (isset($_POST['t_' . $k])) {
                    // Copy is written by humans, not pasted from the web: strip tags,
                    // but keep the few entities the design relies on (&nbsp;, &amp;).
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

$users   = auth_users();
$pending = array_filter($users, fn($u) => ($u['status'] ?? '') === 'pending');
$active  = array_filter($users, fn($u) => ($u['status'] ?? '') === 'approved');

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
    'accent'    => 'Buttons (accent)',
    'accent_2'  => 'Secondary accent',
    'page_bg'   => 'Page background',
    'ink'       => 'Body text',
];
?><!DOCTYPE html>
<html lang="en-GB"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title>Admin — Luke Goulden</title>
<style>
  :root{--teal:#1A3C34;--coral:#E05A3A;--sage:#84B59F;--off:#F7F5F0;--ink:#1E1E1E;--muted:#6b6b6b;--line:rgba(30,30,30,.12)}
  *{box-sizing:border-box}
  body{margin:0;background:var(--off);color:var(--ink);font:16px/1.6 system-ui,-apple-system,"Segoe UI",sans-serif}
  .wrap{max-width:60rem;margin:0 auto;padding:2.5rem 1.25rem 6rem}
  h1{font-size:1.7rem;font-weight:800;color:var(--teal);letter-spacing:-.015em;margin:0 0 .25rem}
  .sub{color:var(--muted);font-size:.9rem;margin:0 0 2.5rem}
  h2{font-size:.75rem;letter-spacing:.14em;text-transform:uppercase;color:var(--teal);
     margin:2.5rem 0 .9rem;padding-top:1.5rem;border-top:1px solid var(--line)}
  h2:first-of-type{border-top:0;padding-top:0}

  .row{display:flex;align-items:center;gap:1rem;background:#fff;border:1px solid var(--line);
       border-radius:12px;padding:1rem 1.15rem;margin-bottom:.6rem;flex-wrap:wrap}
  .row .who{flex:1;min-width:12rem}
  .row .who b{display:block;color:var(--teal)}
  .row .who span{font-size:.82rem;color:var(--muted)}
  button{border:0;cursor:pointer;font:inherit;font-weight:700;font-size:.7rem;letter-spacing:.1em;
         text-transform:uppercase;padding:.6rem 1rem;border-radius:4px}
  .ok{background:var(--coral);color:#fff}
  .no{background:transparent;color:var(--teal);border:1px solid var(--line)}
  .pill{font-size:.65rem;letter-spacing:.1em;text-transform:uppercase;font-weight:700;
        padding:.2rem .5rem;border-radius:99px;background:rgba(132,181,159,.2);color:#2f6a55}
  .empty{background:#fff;border:1px dashed var(--line);border-radius:12px;padding:1.75rem;
         text-align:center;color:var(--muted);font-size:.9rem}
  .notice{background:rgba(132,181,159,.18);color:#2f6a55;padding:.8rem 1rem;border-radius:8px;
          font-size:.88rem;margin-bottom:1.5rem}

  /* Landing pages */
  .page{background:#fff;border:1px solid var(--line);border-radius:14px;margin-bottom:1rem;overflow:hidden}
  .page > summary{list-style:none;cursor:pointer;display:flex;align-items:center;gap:1rem;
                  padding:1.1rem 1.25rem;flex-wrap:wrap}
  .page > summary::-webkit-details-marker{display:none}
  .page .title{flex:1;min-width:14rem}
  .page .title b{display:block;color:var(--teal);font-size:1rem}
  .page .title span{font-size:.8rem;color:var(--muted)}
  .dots{display:flex;gap:.25rem}
  .dot{width:1.1rem;height:1.1rem;border-radius:99px;border:1px solid rgba(0,0,0,.12)}
  .state{font-size:.62rem;font-weight:800;letter-spacing:.12em;text-transform:uppercase;
         padding:.28rem .6rem;border-radius:99px}
  .state.on{background:rgba(132,181,159,.25);color:#2f6a55}
  .state.off{background:rgba(30,30,30,.07);color:var(--muted)}
  .body{padding:0 1.25rem 1.5rem;border-top:1px solid var(--line)}
  .grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(9rem,1fr));gap:.9rem;margin:1.25rem 0}
  .fld{display:block}
  .fld label{display:block;font-size:.66rem;letter-spacing:.1em;text-transform:uppercase;
             color:var(--muted);font-weight:700;margin-bottom:.3rem}
  .fld input[type=color]{width:100%;height:2.6rem;border:1px solid var(--line);border-radius:8px;
                         background:#fff;padding:.2rem;cursor:pointer}
  .fld input[type=text], .fld textarea{width:100%;padding:.6rem .7rem;border:1px solid var(--line);
                                       border-radius:8px;font:inherit;font-size:.9rem;background:#fff}
  .fld textarea{min-height:4.5rem;resize:vertical}
  .texts{display:grid;gap:.9rem;grid-template-columns:1fr 1fr}
  .texts .wide{grid-column:1/-1}
  .acts{display:flex;align-items:center;gap:.75rem;flex-wrap:wrap;margin-top:1.25rem}
  .acts a{font-size:.72rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;
          color:var(--teal);text-decoration:none;border-bottom:1px solid var(--line)}
  .live-toggle{display:inline-flex;align-items:center;gap:.5rem;font-size:.75rem;font-weight:700;
               letter-spacing:.08em;text-transform:uppercase;color:var(--teal)}
  @media(max-width:40em){ .texts{grid-template-columns:1fr} }
</style></head><body>

<?php lgc_nav('admin'); ?>

<div class="wrap">
  <h1>Admin</h1>
  <p class="sub">Landing pages and who can see the numbers. One login covers the report, the pages and this page.</p>

  <?php if ($notice): ?><div class="notice"><?= $notice ?></div><?php endif; ?>

  <h2 id="pages">Landing pages</h2>
  <p style="margin:-.4rem 0 1.25rem;color:var(--muted);font-size:.86rem">
    Paused pages return “not found” to the public, but you can still open them yourself to check the work.
    Every page reports its own click-through rate, so you can see which one actually wins.
  </p>

  <?php foreach ($LPS as $id => $v): $C = $v['colors']; ?>
    <details class="page" <?= $v['live'] ? 'open' : '' ?>>
      <summary>
        <span class="dots">
          <?php foreach (['hero_bg','accent','accent_2','page_bg'] as $k): ?>
            <span class="dot" style="background:<?= htmlspecialchars($C[$k]) ?>"></span>
          <?php endforeach; ?>
        </span>
        <span class="title">
          <b><?= htmlspecialchars($v['name']) ?></b>
          <span><?= htmlspecialchars($v['path']) ?> · <?= htmlspecialchars($v['note'] ?? '') ?></span>
        </span>
        <span class="state <?= $v['live'] ? 'on' : 'off' ?>"><?= $v['live'] ? 'Live' : 'Paused' ?></span>
      </summary>

      <form class="body" method="post">
        <input type="hidden" name="do" value="page">
        <input type="hidden" name="id" value="<?= htmlspecialchars($id) ?>">

        <div class="grid">
          <?php foreach ($swatches as $k => $label): ?>
            <span class="fld">
              <label for="<?= $id . $k ?>"><?= $label ?></label>
              <input id="<?= $id . $k ?>" type="color" name="c_<?= $k ?>" value="<?= htmlspecialchars(strtolower($C[$k])) ?>">
            </span>
          <?php endforeach; ?>
        </div>

        <div class="texts">
          <span class="fld">
            <label for="<?= $id ?>name">Name (only you see this)</label>
            <input id="<?= $id ?>name" type="text" name="name" value="<?= htmlspecialchars($v['name']) ?>">
          </span>
          <span class="fld">
            <label for="<?= $id ?>note">When to use it</label>
            <input id="<?= $id ?>note" type="text" name="note" value="<?= htmlspecialchars($v['note'] ?? '') ?>">
          </span>

          <?php foreach ($v['text'] as $k => $val):
                $wide = in_array($k, ['sub','band_body','closing_body','headline'], true); ?>
            <span class="fld <?= $wide ? 'wide' : '' ?>">
              <label for="<?= $id . $k ?>"><?= $labels[$k] ?? $k ?></label>
              <?php if ($wide): ?>
                <textarea id="<?= $id . $k ?>" name="t_<?= $k ?>"><?= htmlspecialchars($val) ?></textarea>
              <?php else: ?>
                <input id="<?= $id . $k ?>" type="text" name="t_<?= $k ?>" value="<?= htmlspecialchars($val) ?>">
              <?php endif; ?>
            </span>
          <?php endforeach; ?>
        </div>

        <div class="acts">
          <label class="live-toggle">
            <input type="checkbox" name="live" value="1" <?= $v['live'] ? 'checked' : '' ?>>
            Live to the public
          </label>
          <button class="ok" type="submit">Save page</button>
          <a href="<?= htmlspecialchars($v['path']) ?>" target="_blank" rel="noopener">Open page ↗</a>
          <a href="/report/?lp=<?= htmlspecialchars($id) ?>">See its numbers</a>
        </div>
      </form>
    </details>
  <?php endforeach; ?>

  <h2>Waiting for you<?= $pending ? ' (' . count($pending) . ')' : '' ?></h2>
  <?php if (!$pending): ?>
    <div class="empty">Nobody is waiting. New requests appear here — you don’t need the email.</div>
  <?php else: foreach ($pending as $u): ?>
    <div class="row">
      <div class="who">
        <b><?= htmlspecialchars($u['name']) ?></b>
        <span><?= htmlspecialchars($u['email']) ?></span>
      </div>
      <form method="post" style="display:flex;gap:.5rem">
        <input type="hidden" name="do" value="user">
        <input type="hidden" name="email" value="<?= htmlspecialchars($u['email']) ?>">
        <button class="ok" name="action" value="approve" type="submit">Approve</button>
        <button class="no" name="action" value="remove" type="submit">Decline</button>
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
          <button class="no" name="action" value="remove" type="submit">Remove access</button>
        </form>
      <?php endif; ?>
    </div>
  <?php endforeach; ?>
</div>
</body></html>
