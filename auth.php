<?php
/**
 * Access gate for the report area.
 *
 * require __DIR__ . '/auth.php';  at the top of any page that must be private.
 * If the visitor isn't signed in, this renders the branded sign-in screen and
 * stops — the protected page below never runs.
 *
 * Design decisions worth keeping:
 *  - Passwords are only ever stored as bcrypt hashes (password_hash/verify).
 *    No plaintext is written anywhere, including for pending requests.
 *  - New people can request access, but they cannot let themselves in: an
 *    account stays 'pending' and unusable until Pedro approves it by email.
 *  - Failed logins are throttled per session to make guessing impractical.
 */

require_once __DIR__ . '/auth-lib.php';

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax', 'secure' => true]);
    session_start();
}

function auth_user(): ?array {
    if (empty($_SESSION['lgc_user'])) return null;
    return auth_find($_SESSION['lgc_user']);
}

function auth_logout(): void {
    unset($_SESSION['lgc_user']);
    session_destroy();
}

/* ---------- Handle form posts ------------------------------------------- */
$authError  = '';
$authNotice = '';
$showTab    = 'login';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['lgc_action'])) {

    // Throttle guessing: 5 failures per session, then a cool-off.
    $fails = (int)($_SESSION['lgc_fails'] ?? 0);
    $until = (int)($_SESSION['lgc_lock'] ?? 0);

    if ($_POST['lgc_action'] === 'login') {
        if ($until > time()) {
            $authError = 'Too many attempts. Try again in ' . ($until - time()) . ' seconds.';
        } else {
            $email = trim((string)($_POST['email'] ?? ''));
            $pass  = (string)($_POST['password'] ?? '');
            $u     = auth_find($email);

            if ($u && ($u['status'] ?? '') === 'approved' && password_verify($pass, $u['hash'])) {
                session_regenerate_id(true);
                $_SESSION['lgc_user'] = $u['email'];
                unset($_SESSION['lgc_fails'], $_SESSION['lgc_lock']);
                header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?'));
                exit;
            }

            if ($u && ($u['status'] ?? '') === 'pending') {
                $authError = 'That account is still waiting for approval.';
            } else {
                $_SESSION['lgc_fails'] = $fails + 1;
                if ($fails + 1 >= 5) {
                    $_SESSION['lgc_lock']  = time() + 300;
                    $_SESSION['lgc_fails'] = 0;
                }
                $authError = 'Wrong email or password.';
            }
        }
    }

    if ($_POST['lgc_action'] === 'signup') {
        $showTab = 'signup';
        $name  = trim((string)($_POST['name'] ?? ''));
        $email = trim((string)($_POST['email'] ?? ''));
        $pass  = (string)($_POST['password'] ?? '');

        if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($pass) < 8) {
            $authError = 'Please give your name, a valid email, and a password of at least 8 characters.';
        } elseif (auth_find($email)) {
            $authNotice = 'There is already a request or an account for that email.';
        } else {
            $users = auth_users();
            $users[] = [
                'email'  => $email,
                'name'   => $name,
                'hash'   => password_hash($pass, PASSWORD_BCRYPT, ['cost' => 12]),
                'status' => 'pending',
                'added'  => gmdate('Y-m-d H:i'),
            ];
            auth_save_users($users);

            $token  = bin2hex(random_bytes(24));
            $tokens = auth_tokens();
            $tokens[$token] = ['email' => $email, 'expires' => time() + 7 * 86400];
            auth_save_tokens($tokens);

            auth_notify_request(['name' => $name, 'email' => $email], $token);

            $authNotice = 'Thanks — your request has been sent to Pedro. You will be able to sign in once he approves it.';
        }
    }
}

if (isset($_GET['logout'])) {
    auth_logout();
    header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?'));
    exit;
}

/* ---------- Let automation read the JSON with a key --------------------- */
$authApiOk = (($_GET['format'] ?? '') === 'json') && (($_GET['key'] ?? '') === AUTH_API_KEY);

/* ---------- Gate --------------------------------------------------------- */
if (!auth_user() && !$authApiOk) {
    http_response_code(401);
    $ds = '/_ds/luke-goulden-design-system-c14a0f1f-c08a-4904-9234-32785b9e3ab9';
    ?><!DOCTYPE html>
<html lang="en-GB"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title>Sign in — Luke Goulden</title>
<style>
  @font-face{font-family:Manrope;src:url('<?= $ds ?>/assets/fonts/Manrope-Regular.ttf') format('truetype');font-weight:400;font-display:swap}
  @font-face{font-family:Manrope;src:url('<?= $ds ?>/assets/fonts/Manrope-Bold.ttf') format('truetype');font-weight:700;font-display:swap}
  @font-face{font-family:Manrope;src:url('<?= $ds ?>/assets/fonts/Manrope-ExtraBold.ttf') format('truetype');font-weight:800;font-display:swap}
  :root{--teal:#1A3C34;--coral:#E05A3A;--sage:#84B59F;--off:#F7F5F0;--ink:#1E1E1E;--muted:#6b6b6b;--line:rgba(30,30,30,.12)}
  *{box-sizing:border-box}
  body{margin:0;min-height:100vh;display:grid;place-items:center;background:var(--teal);
       font:16px/1.6 Manrope,system-ui,-apple-system,sans-serif;color:var(--ink);padding:2rem 1.25rem}
  .card{width:100%;max-width:26rem;background:var(--off);border-radius:16px;overflow:hidden;
        box-shadow:0 24px 60px rgba(0,0,0,.25)}
  .head{padding:1.75rem 1.75rem 0}
  .brand{display:inline-flex;align-items:center;gap:.55rem;color:var(--teal)}
  .brand svg{height:1.3rem;width:auto}
  .wordmark{font-weight:700;letter-spacing:.22em;text-transform:uppercase;font-size:.78rem}
  h1{font-size:1.35rem;font-weight:800;letter-spacing:-.015em;color:var(--teal);margin:1.25rem 0 .25rem}
  .sub{color:var(--muted);font-size:.88rem;margin:0}
  .tabs{display:flex;gap:.25rem;margin:1.5rem 1.75rem 0;border-bottom:1px solid var(--line)}
  .tab{flex:1;background:none;border:0;cursor:pointer;font:inherit;font-size:.72rem;font-weight:700;
       letter-spacing:.12em;text-transform:uppercase;color:var(--muted);padding:.75rem 0;border-bottom:2px solid transparent}
  .tab.on{color:var(--teal);border-bottom-color:var(--coral)}
  form{padding:1.5rem 1.75rem 1.75rem}
  form[hidden]{display:none}
  label{display:block;font-size:.68rem;letter-spacing:.12em;text-transform:uppercase;
        color:var(--muted);font-weight:700;margin:0 0 .35rem}
  input{width:100%;padding:.8rem .9rem;border:1px solid var(--line);border-radius:8px;background:#fff;
        font:inherit;font-size:.95rem;margin-bottom:1rem}
  input:focus{outline:2px solid var(--coral);outline-offset:1px;border-color:transparent}
  button.go{width:100%;border:0;cursor:pointer;background:var(--coral);color:#fff;font:inherit;font-weight:700;
            font-size:.78rem;letter-spacing:.1em;text-transform:uppercase;padding:.9rem;border-radius:4px}
  button.go:hover{background:#c94e31}
  .msg{padding:.75rem .9rem;border-radius:8px;font-size:.85rem;margin-bottom:1rem;line-height:1.5}
  .msg--bad{background:rgba(224,90,58,.1);color:#a33d24}
  .msg--ok{background:rgba(132,181,159,.18);color:#2f6a55}
  .foot{padding:0 1.75rem 1.5rem;color:var(--muted);font-size:.75rem;line-height:1.5}
</style></head><body>
  <div class="card">
    <div class="head">
      <div class="brand">
        <svg viewBox="76 78 298 237" role="img" aria-label="Luke Goulden">
          <path fill="currentColor" d="M305.5 155A118.5 118.5 0 1 0 305.5 238L264.6 238A81.5 81.5 0 1 1 264.6 155Z"/>
          <rect x="171" y="179" width="203" height="36" fill="currentColor"/>
        </svg>
        <span class="wordmark">Luke Goulden</span>
      </div>
      <h1>Performance report</h1>
      <p class="sub">Private. Sign in to view the numbers.</p>
    </div>

    <div class="tabs">
      <button class="tab <?= $showTab === 'login' ? 'on' : '' ?>" type="button" onclick="show('login')" id="t-login">Sign in</button>
      <button class="tab <?= $showTab === 'signup' ? 'on' : '' ?>" type="button" onclick="show('signup')" id="t-signup">Request access</button>
    </div>

    <form method="post" id="f-login" <?= $showTab === 'signup' ? 'hidden' : '' ?>>
      <?php if ($authError): ?><div class="msg msg--bad"><?= htmlspecialchars($authError) ?></div><?php endif; ?>
      <input type="hidden" name="lgc_action" value="login">
      <label for="l-email">Email</label>
      <input id="l-email" type="email" name="email" autocomplete="username" required>
      <label for="l-pass">Password</label>
      <input id="l-pass" type="password" name="password" autocomplete="current-password" required>
      <button class="go" type="submit">Sign in</button>
    </form>

    <form method="post" id="f-signup" <?= $showTab === 'signup' ? '' : 'hidden' ?>>
      <?php if ($authNotice): ?><div class="msg msg--ok"><?= htmlspecialchars($authNotice) ?></div><?php endif; ?>
      <input type="hidden" name="lgc_action" value="signup">
      <label for="s-name">Your name</label>
      <input id="s-name" type="text" name="name" required>
      <label for="s-email">Email</label>
      <input id="s-email" type="email" name="email" autocomplete="username" required>
      <label for="s-pass">Choose a password</label>
      <input id="s-pass" type="password" name="password" autocomplete="new-password" minlength="8" required>
      <button class="go" type="submit">Request access</button>
    </form>

    <p class="foot">Access is approved by Pedro. Requests are reviewed by hand — you will not be able to sign in until then.</p>
  </div>
<script>
  function show(which){
    document.getElementById('f-login').hidden  = which !== 'login';
    document.getElementById('f-signup').hidden = which !== 'signup';
    document.getElementById('t-login').classList.toggle('on', which === 'login');
    document.getElementById('t-signup').classList.toggle('on', which === 'signup');
  }
</script>
</body></html><?php
    exit;
}
