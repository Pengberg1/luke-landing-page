<?php
/**
 * Shared user-store helpers.
 *
 * Kept separate from auth.php on purpose: approve.php needs to read and write
 * users, but must NOT be behind the sign-in gate — otherwise Pedro would have
 * to log in before he could approve anyone, which defeats the point.
 *
 * Passwords live here only as bcrypt hashes. Plaintext is never written.
 */

/* Pedro's working inbox. NOT kempandersen@gmail.com — he doesn't read that one,
   it's only a login identity. An approval he never sees is the same as no
   approval at all. /admin.php is the backstop: approvals work with no email. */
const AUTH_ADMIN_EMAIL = 'pedro@kempandersen.dk';

/* Only these accounts can approve people. */
const AUTH_ADMINS      = ['pedro@kempandersen.dk'];

const AUTH_MAIL_FROM   = 'rapport@lukegouldencoaching.com';
const AUTH_SITE        = 'https://lukegouldencoaching.com';
const AUTH_STORE       = __DIR__ . '/lgc-data/users.php';
const AUTH_TOKENS      = __DIR__ . '/lgc-data/tokens.php';
const AUTH_API_KEY     = 'lgc-cron-2026';   // lets the Friday job read JSON without a login

function auth_users(): array {
    return file_exists(AUTH_STORE) ? (include AUTH_STORE) : [];
}

function auth_save_users(array $users): bool {
    $php = "<?php\n/* User store — bcrypt hashes only, never plaintext. */\nreturn " . var_export($users, true) . ";\n";
    return (bool) @file_put_contents(AUTH_STORE, $php, LOCK_EX);
}

function auth_find(string $email): ?array {
    foreach (auth_users() as $u) {
        if (strcasecmp($u['email'], $email) === 0) return $u;
    }
    return null;
}

function auth_tokens(): array {
    $t = file_exists(AUTH_TOKENS) ? (include AUTH_TOKENS) : [];
    return is_array($t) ? $t : [];
}

function auth_save_tokens(array $t): bool {
    $php = "<?php\nreturn " . var_export($t, true) . ";\n";
    return (bool) @file_put_contents(AUTH_TOKENS, $php, LOCK_EX);
}

/** Emails Pedro an access request with one-click approve / decline links. */
function auth_notify_request(array $user, string $token): void {
    $approve = AUTH_SITE . '/approve.php?token=' . urlencode($token) . '&do=approve';
    $decline = AUTH_SITE . '/approve.php?token=' . urlencode($token) . '&do=decline';

    $body = '<!DOCTYPE html><html><body style="margin:0;background:#F7F5F0">
    <table width="100%" cellpadding="0" cellspacing="0" style="background:#F7F5F0;padding:28px 12px"><tr><td align="center">
    <table width="560" cellpadding="0" cellspacing="0" style="max-width:560px;background:#fff;border-radius:14px;overflow:hidden;font-family:Helvetica,Arial,sans-serif">
      <tr><td style="background:#1A3C34;padding:24px 30px">
        <div style="color:#F7F5F0;font-size:12px;letter-spacing:.22em;text-transform:uppercase;font-weight:700">Luke Goulden</div>
        <div style="color:#84B59F;font-size:13px;margin-top:6px">Someone is asking for access to the report</div>
      </td></tr>
      <tr><td style="padding:30px">
        <p style="margin:0 0 18px;color:#1E1E1E;font-size:15px;line-height:1.6">
          <b>' . htmlspecialchars($user['name']) . '</b><br>
          <span style="color:#6b6b6b">' . htmlspecialchars($user['email']) . '</span>
        </p>
        <p style="margin:0 0 24px;color:#444;font-size:14px;line-height:1.6">
          They have set their own password. They cannot sign in until you approve.
        </p>
        <a href="' . $approve . '" style="display:inline-block;background:#E05A3A;color:#fff;text-decoration:none;
           font-weight:700;font-size:12px;letter-spacing:.1em;text-transform:uppercase;padding:14px 22px;border-radius:4px">Approve access</a>
        <a href="' . $decline . '" style="display:inline-block;margin-left:8px;color:#1A3C34;text-decoration:none;
           border:1px solid rgba(30,30,30,.15);font-weight:700;font-size:12px;letter-spacing:.1em;text-transform:uppercase;padding:13px 22px;border-radius:4px">Decline</a>
        <p style="margin:24px 0 0;color:#6b6b6b;font-size:12px;line-height:1.6">
          If you did not expect this, decline it — nothing happens until you click.
        </p>
      </td></tr>
    </table></td></tr></table></body></html>';

    $headers  = "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    $headers .= 'From: Luke Goulden Report <' . AUTH_MAIL_FROM . ">\r\n";
    @mail(AUTH_ADMIN_EMAIL, 'Access request: ' . $user['email'], $body, $headers, '-f' . AUTH_MAIL_FROM);
}
