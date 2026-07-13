<?php
/**
 * Approve or decline an access request — the links in Pedro's email land here.
 *
 * The token is the authorisation: it is 48 random hex characters, single-use,
 * and expires after 7 days. Nothing else can flip an account to 'approved'.
 */

require __DIR__ . '/auth-lib.php';

$token  = (string)($_GET['token'] ?? '');
$do     = (string)($_GET['do'] ?? '');
$tokens = auth_tokens();

$title = 'Link not valid';
$msg   = 'This approval link has already been used, or it has expired. Ask the person to request access again.';
$tone  = 'bad';

if (isset($tokens[$token]) && $tokens[$token]['expires'] > time() && in_array($do, ['approve', 'decline'], true)) {
    $email = $tokens[$token]['email'];
    $users = auth_users();
    $found = null;

    foreach ($users as $i => $u) {
        if (strcasecmp($u['email'], $email) === 0) { $found = $i; break; }
    }

    if ($found !== null) {
        if ($do === 'approve') {
            $users[$found]['status']   = 'approved';
            $users[$found]['approved'] = gmdate('Y-m-d H:i');
            $title = 'Access approved';
            $msg   = htmlspecialchars($users[$found]['name']) . ' (' . htmlspecialchars($email) . ') can now sign in to the report.';
            $tone  = 'ok';
        } else {
            array_splice($users, $found, 1);   // declined requests are removed outright
            $title = 'Request declined';
            $msg   = htmlspecialchars($email) . ' has been removed. They cannot sign in.';
            $tone  = 'ok';
        }
        auth_save_users($users);
    }

    unset($tokens[$token]);          // single use, whatever the outcome
    auth_save_tokens($tokens);
}
?><!DOCTYPE html>
<html lang="en-GB"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title><?= htmlspecialchars($title) ?> — Luke Goulden</title>
<style>
  body{margin:0;min-height:100vh;display:grid;place-items:center;background:#1A3C34;padding:2rem 1.25rem;
       font:16px/1.6 system-ui,-apple-system,"Segoe UI",sans-serif;color:#1E1E1E}
  .card{max-width:26rem;background:#F7F5F0;border-radius:16px;padding:2rem;text-align:center;
        box-shadow:0 24px 60px rgba(0,0,0,.25)}
  svg{height:1.4rem;color:#1A3C34;margin-bottom:1.25rem}
  h1{font-size:1.3rem;font-weight:800;color:#1A3C34;margin:0 0 .5rem;letter-spacing:-.015em}
  p{color:#555;font-size:.92rem;margin:0 0 1.5rem}
  .dot{display:inline-block;width:.5rem;height:.5rem;border-radius:99px;margin-right:.4rem}
  .ok .dot{background:#84B59F} .bad .dot{background:#E05A3A}
  a{display:inline-block;background:#E05A3A;color:#fff;text-decoration:none;font-weight:700;font-size:.75rem;
    letter-spacing:.1em;text-transform:uppercase;padding:.85rem 1.35rem;border-radius:4px}
</style></head><body>
  <div class="card <?= $tone ?>">
    <svg viewBox="76 78 298 237" fill="currentColor" role="img" aria-label="Luke Goulden">
      <path d="M305.5 155A118.5 118.5 0 1 0 305.5 238L264.6 238A81.5 81.5 0 1 1 264.6 155Z"/>
      <rect x="171" y="179" width="203" height="36"/>
    </svg>
    <h1><span class="dot"></span><?= htmlspecialchars($title) ?></h1>
    <p><?= $msg ?></p>
    <a href="/report/">Open the report</a>
  </div>
</body></html>
