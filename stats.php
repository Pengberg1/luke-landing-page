<?php
/**
 * Luke Goulden Coaching — landing page conversion dashboard.
 * Access: /stats.php?key=lgc-2026-nJ7xQ4  (change ACCESS_KEY to rotate)
 *
 * The single number that matters: CLICK-THROUGH RATE — of everyone who saw
 * the page, how many went to the application form. Everything else is
 * diagnostic: which CTA earned the click, mobile vs desktop, where they
 * came from.
 */

require __DIR__ . '/auth.php';   // private — same sign-in as the report
require_once __DIR__ . '/nav.php';

$dir  = __DIR__ . '/lgc-data';
$days = max(1, min(365, (int)($_GET['days'] ?? 30)));
$since = new DateTime("-{$days} days", new DateTimeZone('UTC'));

$views = 0; $clicks = 0;
$byCta = []; $byDevice = ['m' => ['v'=>0,'c'=>0], 'd' => ['v'=>0,'c'=>0]];
$byRef = []; $bySrc = []; $byDay = [];

foreach (glob($dir . '/events-*.csv') ?: [] as $file) {
    $fh = fopen($file, 'r');
    if (!$fh) continue;
    while (($row = fgetcsv($fh)) !== false) {
        if (count($row) < 6) continue;
        [$ts, $event, $cta, $device, $ref, $src] = $row;
        try { $when = new DateTime($ts, new DateTimeZone('UTC')); } catch (Exception $e) { continue; }
        if ($when < $since) continue;

        $day = substr($ts, 0, 10);
        if (!isset($byDay[$day])) $byDay[$day] = ['v' => 0, 'c' => 0];

        if ($event === 'view') {
            $views++;
            $byDay[$day]['v']++;
            if (isset($byDevice[$device])) $byDevice[$device]['v']++;
            if ($ref !== '-') $byRef[$ref] = ($byRef[$ref] ?? 0) + 1;
            if ($src !== '-') $bySrc[$src] = ($bySrc[$src] ?? 0) + 1;
        } elseif ($event === 'cta') {
            $clicks++;
            $byDay[$day]['c']++;
            $byCta[$cta] = ($byCta[$cta] ?? 0) + 1;
            if (isset($byDevice[$device])) $byDevice[$device]['c']++;
        }
    }
    fclose($fh);
}

$ctr = $views > 0 ? ($clicks / $views) * 100 : 0;
arsort($byCta); arsort($byRef); arsort($bySrc); krsort($byDay);
$ctaLabels = [
    'hero' => 'Hero (above fold)',
    'after_results' => 'Mid-page band (after proof)',
    'after_included' => 'After "What\'s included"',
    'closing' => 'Closing CTA',
    'sticky_mobile' => 'Mobile sticky bar',
    'header' => 'Header button',
];
function pct($n, $d) { return $d > 0 ? round(($n / $d) * 100, 1) : 0; }
?><!DOCTYPE html>
<html lang="en-GB"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title>LP performance — Luke Goulden Coaching</title>
<style>
  :root{--teal:#1A3C34;--coral:#E05A3A;--sage:#84B59F;--off:#F7F5F0;--ink:#1E1E1E;--line:rgba(30,30,30,.12)}
  *{box-sizing:border-box}
  body{margin:0;padding:2rem 1.25rem 4rem;background:var(--off);color:var(--ink);
       font:16px/1.6 system-ui,-apple-system,"Segoe UI",sans-serif}
  .wrap{max-width:60rem;margin:0 auto}
  h1{font-size:1.6rem;color:var(--teal);margin:0 0 .25rem}
  .sub{color:#666;font-size:.9rem;margin:0 0 2rem}
  .kpis{display:grid;grid-template-columns:repeat(auto-fit,minmax(11rem,1fr));gap:1rem;margin-bottom:2.5rem}
  .kpi{background:#fff;border:1px solid var(--line);border-radius:12px;padding:1.25rem}
  .kpi .n{font-size:2.1rem;font-weight:800;color:var(--teal);line-height:1.1;letter-spacing:-.02em}
  .kpi .l{font-size:.72rem;text-transform:uppercase;letter-spacing:.12em;color:#777;margin-top:.35rem}
  .kpi--hero{background:var(--teal)}
  .kpi--hero .n{color:#fff}.kpi--hero .l{color:var(--sage)}
  h2{font-size:1rem;text-transform:uppercase;letter-spacing:.1em;color:var(--teal);margin:2rem 0 .75rem}
  table{width:100%;border-collapse:collapse;background:#fff;border:1px solid var(--line);border-radius:12px;overflow:hidden}
  th,td{text-align:left;padding:.7rem .9rem;border-bottom:1px solid var(--line);font-size:.9rem}
  th{background:#fbfaf8;font-size:.7rem;text-transform:uppercase;letter-spacing:.1em;color:#777}
  tr:last-child td{border-bottom:0}
  td.num{text-align:right;font-variant-numeric:tabular-nums;font-weight:600}
  .bar{height:6px;background:var(--coral);border-radius:99px;min-width:2px}
  .empty{background:#fff;border:1px dashed var(--line);border-radius:12px;padding:2rem;text-align:center;color:#777}
  .note{margin-top:2.5rem;padding:1rem 1.25rem;background:#fff;border-left:3px solid var(--sage);
        border-radius:0 8px 8px 0;font-size:.85rem;color:#555}
  .filters a{display:inline-block;margin-right:.5rem;padding:.3rem .7rem;border:1px solid var(--line);
             border-radius:99px;text-decoration:none;color:var(--teal);font-size:.8rem}
  .filters a.on{background:var(--teal);color:#fff;border-color:var(--teal)}
</style></head><body>
<?php lgc_nav('report'); ?>
<div class="wrap">

<h1>Landing page performance</h1>
<p class="sub">lukegouldencoaching.com → lukegoulden.com/contact/ · last <?= $days ?> days · times UTC</p>

<p class="filters">
  <?php foreach ([7, 30, 90] as $d): ?>
    <a class="<?= $d === $days ? 'on' : '' ?>" href="?days=<?= $d ?>"><?= $d ?> days</a>
  <?php endforeach; ?>
</p>

<div class="kpis">
  <div class="kpi"><div class="n"><?= number_format($views) ?></div><div class="l">Page views</div></div>
  <div class="kpi"><div class="n"><?= number_format($clicks) ?></div><div class="l">Clicks to form</div></div>
  <div class="kpi kpi--hero"><div class="n"><?= number_format($ctr, 1) ?>%</div><div class="l">Click-through rate</div></div>
  <div class="kpi"><div class="n"><?= pct($byDevice['m']['v'], $views) ?>%</div><div class="l">On mobile</div></div>
</div>

<h2>Which CTA earns the click</h2>
<?php if ($clicks === 0): ?>
  <div class="empty">No clicks recorded yet. Numbers appear as soon as real visitors arrive.</div>
<?php else: $top = max($byCta); ?>
  <table>
    <tr><th>CTA position</th><th style="width:35%">Share</th><th class="num">Clicks</th><th class="num">% of clicks</th></tr>
    <?php foreach ($byCta as $cta => $n): ?>
      <tr>
        <td><?= htmlspecialchars($ctaLabels[$cta] ?? $cta) ?></td>
        <td><div class="bar" style="width:<?= max(2, round(($n / $top) * 100)) ?>%"></div></td>
        <td class="num"><?= number_format($n) ?></td>
        <td class="num"><?= pct($n, $clicks) ?>%</td>
      </tr>
    <?php endforeach; ?>
  </table>
<?php endif; ?>

<h2>Mobile vs desktop</h2>
<table>
  <tr><th>Device</th><th class="num">Views</th><th class="num">Clicks</th><th class="num">CTR</th></tr>
  <tr><td>Mobile</td><td class="num"><?= number_format($byDevice['m']['v']) ?></td><td class="num"><?= number_format($byDevice['m']['c']) ?></td><td class="num"><?= pct($byDevice['m']['c'], $byDevice['m']['v']) ?>%</td></tr>
  <tr><td>Desktop</td><td class="num"><?= number_format($byDevice['d']['v']) ?></td><td class="num"><?= number_format($byDevice['d']['c']) ?></td><td class="num"><?= pct($byDevice['d']['c'], $byDevice['d']['v']) ?>%</td></tr>
</table>

<?php if ($byRef): ?>
<h2>Where visitors come from</h2>
<table>
  <tr><th>Referrer</th><th class="num">Views</th></tr>
  <?php foreach (array_slice($byRef, 0, 10, true) as $r => $n): ?>
    <tr><td><?= htmlspecialchars($r) ?></td><td class="num"><?= number_format($n) ?></td></tr>
  <?php endforeach; ?>
</table>
<?php endif; ?>

<h2>Day by day</h2>
<?php if (!$byDay): ?>
  <div class="empty">No traffic recorded yet.</div>
<?php else: ?>
  <table>
    <tr><th>Date</th><th class="num">Views</th><th class="num">Clicks</th><th class="num">CTR</th></tr>
    <?php foreach (array_slice($byDay, 0, 30, true) as $d => $x): ?>
      <tr><td><?= htmlspecialchars($d) ?></td><td class="num"><?= number_format($x['v']) ?></td><td class="num"><?= number_format($x['c']) ?></td><td class="num"><?= pct($x['c'], $x['v']) ?>%</td></tr>
    <?php endforeach; ?>
  </table>
<?php endif; ?>

<p class="note">
  <b>How to read this.</b> Clicks-to-form is the number this page controls. What happens next — how many
  of those clicks become applications — only Luke can see, from his Gravity Forms entries. Clicks here ÷ applications
  he receives = the form's conversion rate, and that tells us whether the next fix belongs on this page or on his.
  <br><br>
  <b>Privacy:</b> no cookies, no IP addresses, no user-agent strings. Nothing stored here is personal data, which is
  why this page needs no consent banner.
</p>

</div></body></html>
