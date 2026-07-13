<?php
/**
 * Shared navigation for the signed-in area. One login covers all of it —
 * report, landing pages and admin sit in the same session.
 *
 * Usage:  lgc_nav('report');   // 'report' | 'pages' | 'admin'
 */
function lgc_nav(string $here = ''): void {
    $me     = function_exists('auth_user') ? auth_user() : null;
    $isBoss = $me && defined('AUTH_ADMINS')
        && in_array(strtolower($me['email']), array_map('strtolower', AUTH_ADMINS), true);

    $items = [
        'report' => ['Report',        '/report/'],
        'pages'  => ['Landing pages', '/admin.php#pages'],
        'admin'  => ['Admin',         '/admin.php'],
    ];
    ?>
    <style>
      .lgc-nav{display:flex;align-items:center;justify-content:space-between;gap:1rem;flex-wrap:wrap;
               background:#1A3C34;padding:.6rem 1.25rem;font:14px/1.5 system-ui,-apple-system,sans-serif}
      .lgc-nav .l{display:flex;align-items:center;gap:.35rem;flex-wrap:wrap}
      .lgc-nav a{color:rgba(247,245,240,.72);text-decoration:none;font-size:.72rem;font-weight:700;
                 letter-spacing:.11em;text-transform:uppercase;padding:.45rem .8rem;border-radius:99px}
      .lgc-nav a:hover{color:#F7F5F0;background:rgba(255,255,255,.07)}
      .lgc-nav a.on{color:#1A3C34;background:#84B59F}
      .lgc-nav .r{display:flex;align-items:center;gap:.75rem;color:rgba(247,245,240,.55);font-size:.72rem}
      .lgc-nav .r a{padding:.3rem .6rem;border:1px solid rgba(255,255,255,.18);border-radius:4px}
      .lgc-nav svg{height:1rem;color:#F7F5F0}
    </style>
    <nav class="lgc-nav">
      <div class="l">
        <svg viewBox="76 78 298 237" fill="currentColor" aria-hidden="true" style="margin-right:.5rem">
          <path d="M305.5 155A118.5 118.5 0 1 0 305.5 238L264.6 238A81.5 81.5 0 1 1 264.6 155Z"/>
          <rect x="171" y="179" width="203" height="36"/>
        </svg>
        <?php foreach ($items as $key => [$label, $href]):
            if ($key === 'admin' && !$isBoss) continue; ?>
          <a class="<?= $here === $key ? 'on' : '' ?>" href="<?= $href ?>"><?= $label ?></a>
        <?php endforeach; ?>
        <a href="/" target="_blank" rel="noopener">View site ↗</a>
      </div>
      <div class="r">
        <?php if ($me): ?>
          <span><?= htmlspecialchars($me['name']) ?></span>
          <a href="?logout=1">Sign out</a>
        <?php endif; ?>
      </div>
    </nav>
    <?php
}
