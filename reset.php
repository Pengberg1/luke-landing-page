<?php
/**
 * Set a new password from a reset link.
 *
 *   /reset.php?token=…
 *
 * The link comes either from the self-service "Forgot password?" email, or from
 * an admin who generated it on /admin.php and handed it over (the reliable path,
 * since email delivery on this host isn't guaranteed).
 *
 * The token is the authorisation: 24 random bytes, single use, two-hour life.
 * It is consumed only when a valid new password is actually set — so merely
 * landing on the page (e.g. a mail scanner following the link) doesn't burn it.
 */

require_once __DIR__ . '/auth-lib.php';

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax', 'secure' => true]);
    session_start();
}

$token = (string)($_GET['token'] ?? ($_POST['token'] ?? ''));
$error = '';
$done  = false;

/* Peek at the token without consuming it, so the form can be shown. It is only
   consumed on a successful password change (below). */
function reset_peek(string $token): ?array {
    if ($token === '') return null;
    foreach (auth_tokens() as $t) {
        if (hash_equals((string)($t['token'] ?? ''), $token)
            && ($t['kind'] ?? '') === 'reset'
            && ($t['expires'] ?? 0) >= time()) {
            return $t;
        }
    }
    return null;
}

$row  = reset_peek($token);
$user = $row ? auth_find((string)$row['email']) : null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $row && $user) {
    $p1 = (string)($_POST['password'] ?? '');
    $p2 = (string)($_POST['password2'] ?? '');

    if (strlen($p1) < 8) {
        $error = 'Choose a password of at least 8 characters.';
    } elseif ($p1 !== $p2) {
        $error = 'The two passwords do not match.';
    } else {
        $user['hash'] = password_hash($p1, PASSWORD_BCRYPT, ['cost' => 12]);
        if (auth_put($user) && password_verify($p1, (string)(auth_find($user['email'])['hash'] ?? ''))) {
            auth_take_token($token);      // burn it only now that it worked
            $done = true;
        } else {
            $error = 'Could not save the new password. Please try the link again.';
        }
    }
}

$valid = (bool)($row && $user);
?><!DOCTYPE html>
<html lang="en-GB">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title>Reset password — Luke Goulden</title>
<style>
  *{box-sizing:border-box}
  body{margin:0;min-height:100vh;display:grid;place-items:center;padding:2rem;background:#1A3C34;
       color:#1E1E1E;font:16px/1.6 system-ui,-apple-system,"Segoe UI",Helvetica,sans-serif}
  .card{background:#F7F5F0;border-radius:16px;padding:2.25rem;width:100%;max-width:27rem;
        box-shadow:0 24px 70px rgba(0,0,0,.28)}
  .brand{display:flex;align-items:center;gap:.6rem;color:#1A3C34;margin-bottom:1.5rem}
  .brand svg{height:1.3rem;width:auto}
  .brand span{font-size:.75rem;font-weight:700;letter-spacing:.2em;text-transform:uppercase}
  h1{margin:0 0 .3rem;font-size:1.4rem;font-weight:800;letter-spacing:-.015em}
  p.sub{margin:0 0 1.5rem;color:#6b6b6b;font-size:.9rem}
  label{display:block;font-size:.68rem;font-weight:700;letter-spacing:.12em;text-transform:uppercase;
        color:#6b6b6b;margin:0 0 .4rem}
  input{width:100%;padding:.78rem .85rem;border:1px solid rgba(30,30,30,.18);border-radius:7px;
        font-size:1rem;background:#fff;margin-bottom:1.1rem;font-family:inherit}
  input:focus{outline:2px solid #E05A3A;outline-offset:1px;border-color:transparent}
  button{width:100%;padding:.9rem;border:0;border-radius:7px;background:#E05A3A;color:#fff;
         font:inherit;font-weight:700;font-size:.78rem;letter-spacing:.11em;text-transform:uppercase;cursor:pointer}
  .msg{padding:.8rem .95rem;border-radius:7px;font-size:.88rem;margin-bottom:1.25rem;line-height:1.5}
  .bad{background:#fdecea;color:#8a2318}
  .good{background:#e8f3ec;color:#1A3C34}
  a.back{display:inline-block;margin-top:1rem;color:#1A3C34;font-weight:700;font-size:.8rem}
</style>
</head>
<body>
<div class="card">
  <div class="brand">
    <svg viewBox="76 78 298 237" fill="currentColor" aria-hidden="true">
      <path d="M305.5 155A118.5 118.5 0 1 0 305.5 238L264.6 238A81.5 81.5 0 1 1 264.6 155Z"/>
      <rect x="171" y="179" width="203" height="36"/>
    </svg>
    <span>Luke Goulden</span>
  </div>

  <?php if ($done): ?>
    <h1>Password set</h1>
    <div class="msg good">Your new password is saved. You can sign in with it now.</div>
    <a class="back" href="/admin.php">Go to sign in →</a>

  <?php elseif (!$valid): ?>
    <h1>Link expired</h1>
    <p class="sub">This reset link has already been used or has expired. Reset links last two hours.</p>
    <a class="back" href="/admin.php">Back to sign in</a>
    <p style="margin-top:.4rem;font-size:.82rem;color:#6b6b6b">Use “Forgot your password?” there to get a fresh one.</p>

  <?php else: ?>
    <h1>Set a new password</h1>
    <p class="sub">For <b><?= htmlspecialchars((string)$user['email']) ?></b>.</p>
    <?php if ($error): ?><div class="msg bad"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <form method="post">
      <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
      <label for="p1">New password</label>
      <input id="p1" type="password" name="password" minlength="8" autocomplete="new-password" required>
      <label for="p2">Repeat it</label>
      <input id="p2" type="password" name="password2" minlength="8" autocomplete="new-password" required>
      <button type="submit">Save new password</button>
    </form>
  <?php endif; ?>
</div>
</body>
</html>
