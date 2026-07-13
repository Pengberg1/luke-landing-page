<?php
/**
 * The access gate.
 *
 *   require __DIR__ . '/auth.php';   at the top of any page that must be private.
 *
 * If the visitor isn't signed in, this renders the sign-in / sign-up screen and
 * stops — the page below it never runs, so a protected page cannot leak by
 * accident.
 *
 * The rules, in one place:
 *   1. Everyone signs up. There are no pre-made accounts and no shared password.
 *   2. Nobody signs themselves in. A new account is 'pending' and cannot log in,
 *      whatever password they type.
 *   3. Pedro approves — by email (approve/decline links) or on /admin.php.
 *      The one exception is Pedro's own address: the first signup from
 *      AUTH_ADMIN_EMAIL is approved on the spot, because otherwise the very
 *      first account would need an approver who doesn't exist yet.
 *   4. Passwords exist only as bcrypt hashes. Nothing else is ever stored.
 *   5. Five wrong guesses buys a five-minute cool-off.
 */

require_once __DIR__ . '/auth-lib.php';

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax', 'secure' => true]);
    session_start();
}

function auth_user(): ?array {
    if (empty($_SESSION['lgc_user'])) return null;
    $u = auth_find($_SESSION['lgc_user']);
    /* Approval can be revoked while someone is signed in — re-check every hit. */
    return ($u && ($u['status'] ?? '') === 'approved') ? $u : null;
}

function auth_logout(): void {
    unset($_SESSION['lgc_user']);
    session_destroy();
}

/* Keep the bootstrap admins in charge on every load. Cheap, and it means the
   site can never be left with no admin — and it upgrades the account that signed
   up before roles existed (e.g. contact@lukegoulden.com) to an approved admin. */
auth_ensure_admins();

/* ---------- form posts ---------------------------------------------------- */

$authError  = '';
$authNotice = '';
$showTab    = 'login';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['lgc_action'])) {

    $fails = (int)($_SESSION['lgc_fails'] ?? 0);
    $until = (int)($_SESSION['lgc_lock']  ?? 0);

    /* ---- sign in ---- */
    if ($_POST['lgc_action'] === 'login') {
        if ($until > time()) {
            $authError = 'Too many attempts. Try again in ' . ($until - time()) . ' seconds.';
        } else {
            $email = trim((string)($_POST['email'] ?? ''));
            $pass  = (string)($_POST['password'] ?? '');
            $u     = auth_find($email);

            if ($u && ($u['status'] ?? '') === 'approved' && password_verify($pass, (string)$u['hash'])) {
                session_regenerate_id(true);
                $_SESSION['lgc_user'] = $u['email'];
                unset($_SESSION['lgc_fails'], $_SESSION['lgc_lock']);
                header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?'));
                exit;
            }

            if ($u && ($u['status'] ?? '') === 'pending') {
                $authError = 'That account is still waiting to be approved.';
            } elseif ($u && ($u['status'] ?? '') === 'declined') {
                $authError = 'That account was declined.';
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

    /* ---- sign up ---- */
    /* ---- forgot password ---- */
    if ($_POST['lgc_action'] === 'forgot') {
        $showTab = 'forgot';
        $email   = strtolower(trim((string)($_POST['email'] ?? '')));
        $u       = $email !== '' ? auth_find($email) : null;

        /* Only approved accounts can reset — a pending/declined one has nothing
           to sign into yet. We still show the SAME message either way, so this
           form can't be used to discover which emails have accounts. */
        if ($u && ($u['status'] ?? '') === 'approved') {
            auth_notify_reset($u, auth_new_token($u['email'], 'reset'));
        }
        $authNotice = 'If there’s an account for that email, a reset link is on its way. '
                    . 'The link works once and expires in two hours.';
    }

    if ($_POST['lgc_action'] === 'signup') {
        $showTab = 'signup';
        $name    = trim((string)($_POST['name'] ?? ''));
        $email   = strtolower(trim((string)($_POST['email'] ?? '')));
        $pass    = (string)($_POST['password'] ?? '');
        $pass2   = (string)($_POST['password2'] ?? '');

        if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $authError = 'Please give your name and a valid email address.';
        } elseif (strlen($pass) < 8) {
            $authError = 'Choose a password of at least 8 characters.';
        } elseif ($pass !== $pass2) {
            $authError = 'The two passwords do not match.';
        } elseif (auth_find($email)) {
            $authError = 'There is already an account or a pending request for that email.';
        } else {
            /* Bootstrap admins (you and Luke) are approved on the spot and made
               admins — see auth-lib.php. Everyone else waits for an admin. */
            $isBoss = auth_is_bootstrap_admin($email);

            $user = [
                'email'  => $email,
                'name'   => $name,
                'hash'   => password_hash($pass, PASSWORD_BCRYPT, ['cost' => 12]),
                'status' => $isBoss ? 'approved' : 'pending',
                'role'   => $isBoss ? 'admin' : 'user',
                'added'  => gmdate('Y-m-d H:i'),
                'mailed' => null,
            ];

            if (!auth_put($user)) {
                $authError = 'Could not save the account — please try again shortly.';
            } elseif ($isBoss) {
                $authNotice = 'Your account is ready. Sign in above.';
                $showTab    = 'login';
            } else {
                $user['mailed'] = auth_notify_request($user, auth_new_token($email));
                auth_put($user);   // remember whether the email actually went out

                $authNotice = 'Thank you for signing up. You will get access once approved.';
            }
        }
    }
}

if (isset($_GET['logout'])) {
    auth_logout();
    header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?'));
    exit;
}

/* ---------- automation reads the JSON with a key, not a password ---------- */
$authApiOk = (($_GET['format'] ?? '') === 'json') && (($_GET['key'] ?? '') === AUTH_API_KEY);

/* ---------- signed in? then get out of the way and let the page render ---- */
if (auth_user() || $authApiOk) {
    return;
}

/* ---------- otherwise: the gate ------------------------------------------ */
http_response_code(401);
$ds = '/_ds/luke-goulden-design-system-c14a0f1f-c08a-4904-9234-32785b9e3ab9';
?><!DOCTYPE html>
<html lang="en-GB">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title>Sign in — Luke Goulden</title>
<style>
  @font-face{font-family:Manrope;src:url('<?= $ds ?>/assets/fonts/Manrope-Regular.ttf') format('truetype');font-weight:400;font-display:swap}
  @font-face{font-family:Manrope;src:url('<?= $ds ?>/assets/fonts/Manrope-Bold.ttf') format('truetype');font-weight:700;font-display:swap}
  @font-face{font-family:Manrope;src:url('<?= $ds ?>/assets/fonts/Manrope-ExtraBold.ttf') format('truetype');font-weight:800;font-display:swap}
  *{box-sizing:border-box}
  body{margin:0;min-height:100vh;display:grid;place-items:center;padding:2rem;
       background:#1A3C34;color:#1E1E1E;
       font:16px/1.6 Manrope,system-ui,-apple-system,"Segoe UI",Helvetica,sans-serif}
  .card{background:#F7F5F0;border-radius:16px;padding:2.25rem;width:100%;max-width:27rem;
        box-shadow:0 24px 70px rgba(0,0,0,.28)}
  .brand{display:flex;align-items:center;gap:.6rem;color:#1A3C34;margin-bottom:1.5rem}
  .brand svg{height:1.3rem;width:auto}
  .brand span{font-size:.75rem;font-weight:700;letter-spacing:.2em;text-transform:uppercase}
  h1{margin:0 0 .3rem;font-size:1.4rem;letter-spacing:-.015em;font-weight:800}
  p.sub{margin:0 0 1.5rem;color:#6b6b6b;font-size:.9rem}
  .tabs{display:flex;gap:1.5rem;border-bottom:1px solid rgba(30,30,30,.12);margin-bottom:1.5rem}
  .tabs button{background:none;border:0;padding:0 0 .75rem;cursor:pointer;
        font:inherit;font-size:.72rem;font-weight:700;letter-spacing:.12em;text-transform:uppercase;
        color:#9a9a9a;border-bottom:2px solid transparent;margin-bottom:-1px}
  .tabs button.on{color:#1A3C34;border-bottom-color:#E05A3A}
  label{display:block;font-size:.68rem;font-weight:700;letter-spacing:.12em;text-transform:uppercase;
        color:#6b6b6b;margin:0 0 .4rem}
  input{width:100%;padding:.78rem .85rem;border:1px solid rgba(30,30,30,.18);border-radius:7px;
        font-size:1rem;background:#fff;margin-bottom:1.1rem;font-family:inherit}
  input:focus{outline:2px solid #E05A3A;outline-offset:1px;border-color:transparent}
  button.go{width:100%;padding:.9rem;border:0;border-radius:7px;background:#E05A3A;color:#fff;
        font:inherit;font-weight:700;font-size:.78rem;letter-spacing:.11em;text-transform:uppercase;cursor:pointer}
  button.go:hover{background:#c94d30}
  .msg{padding:.8rem .95rem;border-radius:7px;font-size:.88rem;margin-bottom:1.25rem;line-height:1.5}
  .bad{background:#fdecea;color:#8a2318}
  .good{background:#e8f3ec;color:#1A3C34}
  .note{margin:1.4rem 0 0;font-size:.76rem;color:#8a8a8a;line-height:1.55}
  form{display:none}
  form.on{display:block}
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

  <h1>Report &amp; admin</h1>
  <p class="sub">Private. One account gets you both.</p>

  <div class="tabs">
    <button type="button" id="tab-login"  class="<?= $showTab === 'login'  ? 'on' : '' ?>">Sign in</button>
    <button type="button" id="tab-signup" class="<?= $showTab === 'signup' ? 'on' : '' ?>">Create account</button>
  </div>

  <?php if ($authError): ?>  <div class="msg bad"><?= htmlspecialchars($authError) ?></div>  <?php endif; ?>
  <?php if ($authNotice): ?> <div class="msg good"><?= htmlspecialchars($authNotice) ?></div> <?php endif; ?>

  <form method="post" id="form-login" class="<?= $showTab === 'login' ? 'on' : '' ?>">
    <input type="hidden" name="lgc_action" value="login">
    <label for="l-email">Email</label>
    <input id="l-email" type="email" name="email" autocomplete="username" required>
    <label for="l-pass">Password</label>
    <input id="l-pass" type="password" name="password" autocomplete="current-password" required>
    <button class="go" type="submit">Sign in</button>
    <p class="note"><a href="#" id="link-forgot" style="color:#c94d30;font-weight:700">Forgot your password?</a></p>
  </form>

  <form method="post" id="form-forgot" class="<?= $showTab === 'forgot' ? 'on' : '' ?>">
    <input type="hidden" name="lgc_action" value="forgot">
    <label for="f-email">Your email</label>
    <input id="f-email" type="email" name="email" autocomplete="username" required>
    <button class="go" type="submit">Email me a reset link</button>
    <p class="note">We’ll send a one-time link to set a new password.
       <a href="#" id="link-back" style="color:#c94d30;font-weight:700">Back to sign in</a></p>
  </form>

  <form method="post" id="form-signup" class="<?= $showTab === 'signup' ? 'on' : '' ?>">
    <input type="hidden" name="lgc_action" value="signup">
    <label for="s-name">Your name</label>
    <input id="s-name" type="text" name="name" autocomplete="name" required>
    <label for="s-email">Email</label>
    <input id="s-email" type="email" name="email" autocomplete="username" required>
    <label for="s-pass">Choose a password</label>
    <input id="s-pass" type="password" name="password" minlength="8" autocomplete="new-password" required>
    <label for="s-pass2">Repeat it</label>
    <input id="s-pass2" type="password" name="password2" minlength="8" autocomplete="new-password" required>
    <button class="go" type="submit">Request access</button>
    <p class="note">Every account is approved by hand. You will not be able to
       sign in until your request has been approved.</p>
  </form>
</div>

<script>
  var tl = document.getElementById('tab-login'),
      ts = document.getElementById('tab-signup'),
      fl = document.getElementById('form-login'),
      fs = document.getElementById('form-signup');
  var ff = document.getElementById('form-forgot');
  function show(which) {
    var login = which === 'login', signup = which === 'signup', forgot = which === 'forgot';
    tl.classList.toggle('on', login || forgot);  ts.classList.toggle('on', signup);
    fl.classList.toggle('on', login);  fs.classList.toggle('on', signup);  ff.classList.toggle('on', forgot);
  }
  tl.addEventListener('click', function () { show('login'); });
  ts.addEventListener('click', function () { show('signup'); });
  var lf = document.getElementById('link-forgot'), lb = document.getElementById('link-back');
  if (lf) lf.addEventListener('click', function (e) { e.preventDefault(); show('forgot'); });
  if (lb) lb.addEventListener('click', function (e) { e.preventDefault(); show('login'); });
</script>
</body>
</html>
<?php
exit;
