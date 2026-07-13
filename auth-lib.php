<?php
/**
 * Accounts, tokens and approval email.
 *
 * WHY THE STORE IS JSON, NOT PHP
 * ------------------------------
 * The first version kept users in a .php file and read it back with include().
 * On this host opcache caches compiled PHP, so an include() immediately after a
 * write returned the OLD file — a password could be saved correctly and then
 * fail verification microseconds later. That single fact caused every login
 * failure. JSON is data, never compiled, so a read always sees the last write.
 *
 * Writes are atomic (write to .tmp, then rename) so a crash mid-write can never
 * leave a half-written user file that locks everyone out.
 *
 * Passwords are stored ONLY as bcrypt hashes. Plaintext is never written — not
 * to the store, not to a log, not for pending accounts.
 *
 * lgc-data/ is blocked from the web by .htaccess (403), so users.json is not
 * readable even though it isn't PHP.
 */

/* The admin inbox. Approval-request emails go here (best-effort — the admin page
   is the real source of truth, so a lost email never blocks anyone). */
const AUTH_ADMIN_EMAIL = 'pedro@kempandersen.dk';

/* Bootstrap admins. These addresses are ALWAYS admins and are approved the
   moment they sign up — otherwise the very first account would need an approver
   who doesn't exist yet, and the system would be locked from birth. Everyone
   else signs up as a normal user and waits for an admin to approve them.
   Roles beyond this are stored per-account and managed on /admin.php. */
const AUTH_BOOTSTRAP_ADMINS = ['pedro@kempandersen.dk', 'contact@lukegoulden.com'];

/* Mail From. On this host PHP mail() from an address on the site's own domain is
   the least likely to be spam-filtered — but delivery to Gmail is never
   guaranteed, which is exactly why approvals also work entirely from /admin.php. */
const AUTH_MAIL_FROM   = 'no-reply@lukegouldencoaching.com';
const AUTH_SITE        = 'https://lukegouldencoaching.com';
const AUTH_STORE       = __DIR__ . '/lgc-data/users.json';
const AUTH_TOKENS      = __DIR__ . '/lgc-data/tokens.json';
const AUTH_MAILLOG     = __DIR__ . '/lgc-data/mail.log';
const AUTH_API_KEY     = 'lgc-cron-2026';   // lets the Friday job read JSON without a login

/* ---------- storage ------------------------------------------------------ */

function auth_read_json(string $path): array {
    if (!is_file($path)) return [];
    $raw  = @file_get_contents($path);
    $data = json_decode((string)$raw, true);
    return is_array($data) ? $data : [];
}

/** Atomic: a reader never sees a half-written file. */
function auth_write_json(string $path, array $data): bool {
    $json = json_encode(
        array_values($data),
        JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
    );
    $tmp = $path . '.tmp';
    if (@file_put_contents($tmp, $json, LOCK_EX) === false) return false;
    if (!@rename($tmp, $path)) { @unlink($tmp); return false; }
    return true;
}

function auth_users(): array           { return auth_read_json(AUTH_STORE); }
function auth_save_users(array $u): bool { return auth_write_json(AUTH_STORE, $u); }

function auth_find(string $email): ?array {
    foreach (auth_users() as $u) {
        if (strcasecmp((string)($u['email'] ?? ''), $email) === 0) return $u;
    }
    return null;
}

/** Insert or replace one user, keyed on email. Returns false if the write failed. */
function auth_put(array $user): bool {
    $users = auth_users();
    $out   = [];
    $done  = false;
    foreach ($users as $u) {
        if (strcasecmp((string)($u['email'] ?? ''), $user['email']) === 0) {
            $out[] = $user;
            $done  = true;
        } else {
            $out[] = $u;
        }
    }
    if (!$done) $out[] = $user;
    return auth_save_users($out);
}

function auth_remove(string $email): bool {
    $out = [];
    foreach (auth_users() as $u) {
        if (strcasecmp((string)($u['email'] ?? ''), $email) !== 0) $out[] = $u;
    }
    return auth_save_users($out);
}

/** A bootstrap admin is always an admin, whatever the stored role says — this is
 *  the lock-out safety net (you can never demote your way out of the building). */
function auth_is_bootstrap_admin(string $email): bool {
    return in_array(strtolower($email), array_map('strtolower', AUTH_BOOTSTRAP_ADMINS), true);
}

function auth_is_admin(?array $u): bool {
    if (!$u) return false;
    if (auth_is_bootstrap_admin((string)($u['email'] ?? ''))) return true;
    return ($u['role'] ?? 'user') === 'admin';
}

/** The role we should treat an account as having (bootstrap admins always win). */
function auth_role(array $u): string {
    return auth_is_admin($u) ? 'admin' : 'user';
}

/**
 * Make sure the bootstrap admins exist, are approved, and are admins.
 *
 * Called on every load. It is cheap (one read; a write only when something is
 * actually wrong) and it means the site can never end up with no admin — even if
 * the user store is wiped, the first time a bootstrap admin visits they are put
 * back in charge. It also fixes the account that signed up before roles existed.
 */
function auth_ensure_admins(): void {
    $users   = auth_users();
    $changed = false;

    foreach (AUTH_BOOTSTRAP_ADMINS as $email) {
        $found = null;
        foreach ($users as $i => $u) {
            if (strcasecmp((string)($u['email'] ?? ''), $email) === 0) { $found = $i; break; }
        }
        if ($found === null) continue;   // not signed up yet — nothing to fix

        if (($users[$found]['status'] ?? '') !== 'approved' || ($users[$found]['role'] ?? '') !== 'admin') {
            $users[$found]['status'] = 'approved';
            $users[$found]['role']   = 'admin';
            $changed = true;
        }
    }
    if ($changed) auth_save_users($users);
}

/* ---------- approval tokens ---------------------------------------------- */
/* Single-use, 7-day, one per request. The token is the only thing that can
   flip an account to approved from the email — knowing someone's address is
   not enough. */

function auth_tokens(): array             { return auth_read_json(AUTH_TOKENS); }
function auth_save_tokens(array $t): bool { return auth_write_json(AUTH_TOKENS, $t); }

/**
 * Mint a single-use token. $kind is 'approve' (grant access) or 'reset' (set a
 * new password). A password reset lives 2 hours; an access request, 7 days.
 * Any earlier token of the same kind for the same address is dropped, so an old
 * link can never be replayed after a newer one is issued.
 */
function auth_new_token(string $email, string $kind = 'approve'): string {
    $token = bin2hex(random_bytes(24));
    $life  = $kind === 'reset' ? 2 * 3600 : 7 * 86400;

    $keep = [];
    foreach (auth_tokens() as $t) {
        if (($t['expires'] ?? 0) < time()) continue;                       // expired
        if (strcasecmp((string)($t['email'] ?? ''), $email) === 0
            && ($t['kind'] ?? 'approve') === $kind) continue;               // superseded
        $keep[] = $t;
    }
    $keep[] = ['token' => $token, 'email' => $email, 'kind' => $kind, 'expires' => time() + $life];
    auth_save_tokens($keep);

    return $token;
}

/** Consume a token. Returns its row (with 'kind'), or null if unknown/expired. */
function auth_take_token(string $token): ?array {
    $hit  = null;
    $keep = [];
    foreach (auth_tokens() as $t) {
        if (hash_equals((string)($t['token'] ?? ''), $token) && ($t['expires'] ?? 0) >= time()) {
            $hit = $t;           // consumed — not written back
            continue;
        }
        if (($t['expires'] ?? 0) >= time()) $keep[] = $t;
    }
    auth_save_tokens($keep);
    return $hit;
}

/** Email a password-reset link. Returns whether mail was handed off (logged). */
function auth_notify_reset(array $user, string $token): bool {
    $link = AUTH_SITE . '/reset.php?token=' . urlencode($token);
    $name = htmlspecialchars((string)$user['name'], ENT_QUOTES, 'UTF-8');

    $body = '<!DOCTYPE html><html><body style="margin:0;background:#F7F5F0">
    <table width="100%" cellpadding="0" cellspacing="0" style="background:#F7F5F0;padding:28px 12px"><tr><td align="center">
    <table width="560" cellpadding="0" cellspacing="0" style="max-width:560px;background:#fff;border-radius:14px;overflow:hidden;font-family:Helvetica,Arial,sans-serif">
      <tr><td style="background:#1A3C34;padding:24px 30px">
        <div style="color:#F7F5F0;font-size:12px;letter-spacing:.22em;text-transform:uppercase;font-weight:700">Luke Goulden</div>
        <div style="color:#84B59F;font-size:13px;margin-top:6px">Reset your password</div>
      </td></tr>
      <tr><td style="padding:30px">
        <p style="margin:0 0 18px;color:#1E1E1E;font-size:15px;line-height:1.6">Hi ' . $name . ',</p>
        <p style="margin:0 0 24px;color:#444;font-size:14px;line-height:1.6">
          Click below to set a new password. The link works once and expires in two hours.
          If you didn’t ask for this, ignore it — nothing changes.
        </p>
        <a href="' . $link . '" style="display:inline-block;background:#E05A3A;color:#fff;text-decoration:none;
           font-weight:700;font-size:12px;letter-spacing:.1em;text-transform:uppercase;padding:14px 22px;border-radius:4px">Set a new password</a>
      </td></tr>
    </table></td></tr></table></body></html>';

    $headers  = "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    $headers .= 'From: Luke Goulden <' . AUTH_MAIL_FROM . ">\r\n";
    $headers .= 'Reply-To: ' . AUTH_MAIL_FROM . "\r\n";

    $ok = @mail($user['email'], 'Reset your Luke Goulden password', $body, $headers, '-f' . AUTH_MAIL_FROM);
    @file_put_contents(AUTH_MAILLOG,
        gmdate('Y-m-d H:i:s') . '  reset    ' . $user['email'] . '  mail=' . ($ok ? 'ok' : 'FAILED') . "\n",
        FILE_APPEND | LOCK_EX);
    return (bool) $ok;
}

/* ---------- approval email ------------------------------------------------ */

/**
 * Emails the admin an access request with one-click approve / decline links.
 * Returns whether the mail was handed to the MTA — recorded on the account so
 * the admin page can show "email sent" or "email failed" instead of leaving the
 * admin guessing. Email is the convenience path; /admin.php is the backstop.
 */
function auth_notify_request(array $user, string $token): bool {
    $approve = AUTH_SITE . '/approve.php?token=' . urlencode($token) . '&do=approve';
    $decline = AUTH_SITE . '/approve.php?token=' . urlencode($token) . '&do=decline';

    $name  = htmlspecialchars((string)$user['name'],  ENT_QUOTES, 'UTF-8');
    $email = htmlspecialchars((string)$user['email'], ENT_QUOTES, 'UTF-8');

    $body = '<!DOCTYPE html><html><body style="margin:0;background:#F7F5F0">
    <table width="100%" cellpadding="0" cellspacing="0" style="background:#F7F5F0;padding:28px 12px"><tr><td align="center">
    <table width="560" cellpadding="0" cellspacing="0" style="max-width:560px;background:#fff;border-radius:14px;overflow:hidden;font-family:Helvetica,Arial,sans-serif">
      <tr><td style="background:#1A3C34;padding:24px 30px">
        <div style="color:#F7F5F0;font-size:12px;letter-spacing:.22em;text-transform:uppercase;font-weight:700">Luke Goulden</div>
        <div style="color:#84B59F;font-size:13px;margin-top:6px">Someone is asking for access to the report</div>
      </td></tr>
      <tr><td style="padding:30px">
        <p style="margin:0 0 18px;color:#1E1E1E;font-size:15px;line-height:1.6">
          <b>' . $name . '</b><br><span style="color:#6b6b6b">' . $email . '</span>
        </p>
        <p style="margin:0 0 24px;color:#444;font-size:14px;line-height:1.6">
          They have set their own password. They cannot sign in until you approve.
        </p>
        <a href="' . $approve . '" style="display:inline-block;background:#E05A3A;color:#fff;text-decoration:none;
           font-weight:700;font-size:12px;letter-spacing:.1em;text-transform:uppercase;padding:14px 22px;border-radius:4px">Approve access</a>
        <a href="' . $decline . '" style="display:inline-block;margin-left:8px;color:#1A3C34;text-decoration:none;
           border:1px solid rgba(30,30,30,.15);font-weight:700;font-size:12px;letter-spacing:.1em;text-transform:uppercase;padding:13px 22px;border-radius:4px">Decline</a>
        <p style="margin:24px 0 0;color:#6b6b6b;font-size:12px;line-height:1.6">
          If you did not expect this, decline it. You can also approve or decline
          from the admin page — nothing depends on this email arriving.
        </p>
      </td></tr>
    </table></td></tr></table></body></html>';

    $headers  = "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    $headers .= 'From: Luke Goulden Report <' . AUTH_MAIL_FROM . ">\r\n";
    $headers .= 'Reply-To: ' . AUTH_MAIL_FROM . "\r\n";

    $ok = @mail(
        AUTH_ADMIN_EMAIL,
        'Access request: ' . $user['email'],
        $body,
        $headers,
        '-f' . AUTH_MAIL_FROM
    );

    @file_put_contents(
        AUTH_MAILLOG,
        gmdate('Y-m-d H:i:s') . "  request  " . $user['email'] . '  mail=' . ($ok ? 'ok' : 'FAILED') . "\n",
        FILE_APPEND | LOCK_EX
    );

    return (bool) $ok;
}
