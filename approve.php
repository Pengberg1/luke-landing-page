<?php
/**
 * Approve / decline an access request from the email Pedro receives.
 *
 *   /approve.php?token=…&do=approve
 *   /approve.php?token=…&do=decline
 *
 * Deliberately NOT behind the sign-in gate: Pedro must be able to approve someone
 * from his phone without signing in first. The token is what authorises it — 24
 * random bytes, single use, seven-day life. Using it (either way) burns it, so a
 * forwarded email cannot be replayed.
 *
 * /admin.php does the same job for anyone signed in, so a lost email is never a
 * dead end.
 */

require_once __DIR__ . '/auth-lib.php';

$token  = (string)($_GET['token'] ?? '');
$action = ($_GET['do'] ?? '') === 'decline' ? 'decline' : 'approve';

$title = 'Link no longer valid';
$body  = 'It may have been used already, or it expired. Open the admin page to approve or decline by hand.';
$tone  = 'bad';

$row = $token !== '' ? auth_take_token($token) : null;

if ($row) {
    $user = auth_find((string)$row['email']);

    if (!$user) {
        $title = 'That account is gone';
        $body  = 'It was removed before you got here. Nothing to do.';
    } elseif ($action === 'approve') {
        $user['status']   = 'approved';
        $user['approved'] = gmdate('Y-m-d H:i');
        if (auth_put($user)) {
            $title = 'Approved';
            $body  = htmlspecialchars((string)$user['name']) . ' can now sign in.';
            $tone  = 'good';
        } else {
            $title = 'Could not save';
            $body  = 'The user file would not write. Nothing has changed.';
        }
    } else {
        $user['status']   = 'declined';
        $user['declined'] = gmdate('Y-m-d H:i');
        if (auth_put($user)) {
            $title = 'Declined';
            $body  = htmlspecialchars((string)$user['name']) . ' cannot sign in. You can remove the account entirely on the admin page.';
            $tone  = 'good';
        } else {
            $title = 'Could not save';
            $body  = 'The user file would not write. Nothing has changed.';
        }
    }
}
?><!DOCTYPE html>
<html lang="en-GB">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title><?= $title ?> — Luke Goulden</title>
<style>
  body{margin:0;min-height:100vh;display:grid;place-items:center;padding:2rem;background:#1A3C34;
       font:16px/1.6 system-ui,-apple-system,"Segoe UI",Helvetica,sans-serif;color:#1E1E1E}
  .card{background:#F7F5F0;border-radius:16px;padding:2.25rem;max-width:26rem;width:100%;
        box-shadow:0 24px 70px rgba(0,0,0,.28);text-align:center}
  .dot{width:2.5rem;height:2.5rem;border-radius:99px;margin:0 auto 1.1rem;display:grid;place-items:center;
       font-size:1.2rem;font-weight:700;color:#fff}
  .good .dot{background:#84B59F}
  .bad .dot{background:#E05A3A}
  h1{margin:0 0 .5rem;font-size:1.3rem;letter-spacing:-.015em}
  p{margin:0 0 1.5rem;color:#6b6b6b;font-size:.92rem}
  a{display:inline-block;padding:.75rem 1.4rem;border-radius:7px;background:#1A3C34;color:#F7F5F0;
    text-decoration:none;font-size:.74rem;font-weight:700;letter-spacing:.11em;text-transform:uppercase}
</style>
</head>
<body>
<div class="card <?= $tone ?>">
  <div class="dot"><?= $tone === 'good' ? '✓' : '!' ?></div>
  <h1><?= $title ?></h1>
  <p><?= $body ?></p>
  <a href="/admin.php">Open admin</a>
</div>
</body>
</html>
