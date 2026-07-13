<?php
/**
 * Access approvals — /admin.php
 *
 * The email notification is a convenience; THIS is the source of truth. Pedro
 * can approve or remove people here without any email arriving at all, which
 * matters because a missed mail would otherwise mean a person is stuck forever.
 *
 * Only accounts listed in AUTH_ADMINS can open it.
 */

require __DIR__ . '/auth.php';          // must be signed in

$me = auth_user();
if (!$me || !in_array(strtolower($me['email']), array_map('strtolower', AUTH_ADMINS), true)) {
    http_response_code(403);
    exit('Not allowed.');
}

$notice = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
            // Guard: never let the last admin lock themselves out.
            if (strcasecmp($u['email'], $me['email']) === 0) {
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

$users   = auth_users();
$pending = array_filter($users, fn($u) => ($u['status'] ?? '') === 'pending');
$active  = array_filter($users, fn($u) => ($u['status'] ?? '') === 'approved');
?><!DOCTYPE html>
<html lang="en-GB"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title>Access — Luke Goulden</title>
<style>
  :root{--teal:#1A3C34;--coral:#E05A3A;--sage:#84B59F;--off:#F7F5F0;--ink:#1E1E1E;--muted:#6b6b6b;--line:rgba(30,30,30,.12)}
  *{box-sizing:border-box}
  body{margin:0;padding:2.5rem 1.25rem 5rem;background:var(--off);color:var(--ink);
       font:16px/1.6 system-ui,-apple-system,"Segoe UI",sans-serif}
  .wrap{max-width:46rem;margin:0 auto}
  .mast{display:flex;align-items:center;justify-content:space-between;gap:1rem;
        padding-bottom:1.25rem;border-bottom:2px solid var(--teal);margin-bottom:2rem}
  .brand{display:inline-flex;align-items:center;gap:.55rem;color:var(--teal)}
  .brand svg{height:1.3rem}
  .wordmark{font-weight:700;letter-spacing:.22em;text-transform:uppercase;font-size:.8rem}
  .mast a{color:var(--teal);font-size:.78rem;text-decoration:none;border-bottom:1px solid var(--line)}
  h1{font-size:1.6rem;font-weight:800;color:var(--teal);letter-spacing:-.015em;margin:0 0 .25rem}
  .sub{color:var(--muted);font-size:.9rem;margin:0 0 2rem}
  h2{font-size:.75rem;letter-spacing:.14em;text-transform:uppercase;color:var(--teal);margin:2rem 0 .75rem}
  .row{display:flex;align-items:center;gap:1rem;background:#fff;border:1px solid var(--line);
       border-radius:12px;padding:1rem 1.15rem;margin-bottom:.6rem;flex-wrap:wrap}
  .row .who{flex:1;min-width:12rem}
  .row .who b{display:block;color:var(--teal)}
  .row .who span{font-size:.82rem;color:var(--muted)}
  .row .when{font-size:.75rem;color:var(--muted)}
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
</style></head><body><div class="wrap">

  <div class="mast">
    <div class="brand">
      <svg viewBox="76 78 298 237" fill="currentColor" role="img" aria-label="Luke Goulden">
        <path d="M305.5 155A118.5 118.5 0 1 0 305.5 238L264.6 238A81.5 81.5 0 1 1 264.6 155Z"/>
        <rect x="171" y="179" width="203" height="36"/>
      </svg>
      <span class="wordmark">Luke Goulden</span>
    </div>
    <div><a href="/report/">Report</a> &nbsp;·&nbsp; <a href="/report/?logout=1">Sign out</a></div>
  </div>

  <h1>Who can see the report</h1>
  <p class="sub">Approve or remove people here. You do not need the email — this page is the source of truth.</p>

  <?php if ($notice): ?><div class="notice"><?= $notice ?></div><?php endif; ?>

  <h2>Waiting for you<?= $pending ? ' (' . count($pending) . ')' : '' ?></h2>
  <?php if (!$pending): ?>
    <div class="empty">Nobody is waiting. New requests will appear here.</div>
  <?php else: foreach ($pending as $u): ?>
    <div class="row">
      <div class="who">
        <b><?= htmlspecialchars($u['name']) ?></b>
        <span><?= htmlspecialchars($u['email']) ?></span>
      </div>
      <span class="when">asked <?= htmlspecialchars($u['added'] ?? '') ?></span>
      <form method="post" style="display:flex;gap:.5rem">
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
          <input type="hidden" name="email" value="<?= htmlspecialchars($u['email']) ?>">
          <button class="no" name="action" value="remove" type="submit">Remove access</button>
        </form>
      <?php endif; ?>
    </div>
  <?php endforeach; ?>

</div></body></html>
