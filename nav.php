<?php
/**
 * Shared navigation for the signed-in area. One login covers all of it —
 * report, landing pages and admin sit in the same session.
 *
 * Usage:  lgc_nav('report');   // 'report' | 'pages' | 'admin'
 */
function lgc_nav(string $here = ''): void {
    $me = function_exists('auth_user') ? auth_user() : null;

    /* Both links are visible to any signed-in account. What you can DO on the
       Landing pages screen depends on your role, which that page enforces —
       there is no separate "admin" tab to hide. */
    $items = [
        'report' => ['Report',        '/report/'],
        'admin'  => ['Landing pages', '/admin.php'],
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
        <span style="margin-right:.5rem;display:inline-flex"><?= function_exists('lp_logo_mark') ? lp_logo_mark('1rem') : '' ?></span>
        <?php foreach ($items as $key => [$label, $href]): ?>
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
