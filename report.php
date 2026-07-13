<?php
/**
 * Luke Goulden Coaching — branded performance report.
 *
 * Two audiences, one file:
 *   /report.php?k=lgc-live-2026   → live link, safe to send Luke. Always current.
 *   /report.php?k=...&format=json → machine-readable, for the Friday PDF job.
 *
 * Print styles are tuned so Cmd/Ctrl+P → "Save as PDF" produces a clean,
 * brand-correct document with no dashboard chrome.
 */

/* Private. auth.php renders the sign-in screen and stops here if the visitor
   isn't signed in. The Friday job still reads ?format=json&key=… without a
   login, because automation can't type a password. */
require __DIR__ . '/auth.php';
require_once __DIR__ . '/nav.php';
require_once __DIR__ . '/lp-lib.php';

/* Absolute, so the fonts still resolve when this is served from /report/. */
const DS = '/_ds/luke-goulden-design-system-c14a0f1f-c08a-4904-9234-32785b9e3ab9';

$ctaLabels = [
    'hero'           => 'Hero — above the fold',
    'after_results'  => 'Mid-page band — after the proof',
    'after_included' => 'After “What’s included”',
    'closing'        => 'Closing section',
    'sticky_mobile'  => 'Mobile sticky bar',
    'header'         => 'Header button',
];

/** Read events between two UTC dates (inclusive start, exclusive end). */
function readWindow(DateTime $from, DateTime $to, string $onlyLp = ''): array {
    $out = [
        'views' => 0, 'clicks' => 0,
        'cta' => [], 'device' => ['m' => ['v'=>0,'c'=>0], 'd' => ['v'=>0,'c'=>0]],
        'ref' => [], 'days' => [], 'lp' => [],
    ];
    foreach (glob(__DIR__ . '/lgc-data/events-*.csv') ?: [] as $file) {
        $fh = @fopen($file, 'r');
        if (!$fh) continue;
        while (($row = fgetcsv($fh)) !== false) {
            if (count($row) < 6) continue;
            [$ts, $event, $cta, $device, $ref] = $row;
            $lp = $row[6] ?? '01';          // events logged before variants existed = '01'
            try { $when = new DateTime($ts, new DateTimeZone('UTC')); } catch (Exception $e) { continue; }
            if ($when < $from || $when >= $to) continue;
            if ($onlyLp !== '' && $lp !== $onlyLp) continue;

            if (!isset($out['lp'][$lp])) $out['lp'][$lp] = ['v' => 0, 'c' => 0];
            $out['lp'][$lp][$event === 'view' ? 'v' : 'c']++;

            $day = substr($ts, 0, 10);
            if (!isset($out['days'][$day])) $out['days'][$day] = ['v' => 0, 'c' => 0];

            if ($event === 'view') {
                $out['views']++;
                $out['days'][$day]['v']++;
                if (isset($out['device'][$device])) $out['device'][$device]['v']++;
                if ($ref !== '-') $out['ref'][$ref] = ($out['ref'][$ref] ?? 0) + 1;
            } elseif ($event === 'cta') {
                $out['clicks']++;
                $out['days'][$day]['c']++;
                $out['cta'][$cta] = ($out['cta'][$cta] ?? 0) + 1;
                if (isset($out['device'][$device])) $out['device'][$device]['c']++;
            }
        }
        fclose($fh);
    }
    ksort($out['days']);
    arsort($out['cta']);
    arsort($out['ref']);
    return $out;
}

function ctr(array $w): float { return $w['views'] > 0 ? ($w['clicks'] / $w['views']) * 100 : 0.0; }
function pct(int $n, int $d): float { return $d > 0 ? round(($n / $d) * 100, 1) : 0.0; }

// Reporting window: the last 7 complete days, compared with the 7 before that.
$tz     = new DateTimeZone('UTC');
$end    = new DateTime('tomorrow', $tz);              // exclusive
$start  = (clone $end)->modify('-7 days');
$prevEnd   = clone $start;
$prevStart = (clone $prevEnd)->modify('-7 days');

$lpFilter = preg_match('/^[0-9]{2}$/', (string)($_GET['lp'] ?? '')) ? (string)$_GET['lp'] : '';
$this7 = readWindow($start, $end, $lpFilter);
$prev7 = readWindow($prevStart, $prevEnd, $lpFilter);
$LPS   = lp_variants();

$ctrNow  = ctr($this7);
$ctrPrev = ctr($prev7);

/** Percentage change, or null when there's no baseline to compare against. */
function delta(float $now, float $before): ?float {
    if ($before <= 0) return null;
    return (($now - $before) / $before) * 100;
}
$dViews  = delta($this7['views'],  $prev7['views']);
$dClicks = delta($this7['clicks'], $prev7['clicks']);
$dCtr    = delta($ctrNow, $ctrPrev);

$hasData = ($this7['views'] + $prev7['views']) > 0;
$periodLabel = $start->format('j M') . ' – ' . (clone $end)->modify('-1 day')->format('j M Y');

// --- JSON mode: the Friday job reads this ---
if (($_GET['format'] ?? '') === 'json') {
    header('Content-Type: application/json');
    echo json_encode([
        'period'      => ['from' => $start->format('Y-m-d'), 'to' => (clone $end)->modify('-1 day')->format('Y-m-d')],
        'this_week'   => ['views' => $this7['views'], 'clicks' => $this7['clicks'], 'ctr' => round($ctrNow, 2)],
        'prev_week'   => ['views' => $prev7['views'], 'clicks' => $prev7['clicks'], 'ctr' => round($ctrPrev, 2)],
        'change_pct'  => ['views' => $dViews, 'clicks' => $dClicks, 'ctr' => $dCtr],
        'by_cta'      => $this7['cta'],
        'by_device'   => $this7['device'],
        'referrers'   => array_slice($this7['ref'], 0, 10, true),
        'by_day'      => $this7['days'],
        'by_landing_page' => $this7['lp'],
    ], JSON_PRETTY_PRINT);
    exit;
}

/** Renders a coloured change chip, e.g. "▲ 24%". */
function chip(?float $d): string {
    if ($d === null) return '<span class="chip chip--flat">no prior week</span>';
    $cls = $d > 0 ? 'chip--up' : ($d < 0 ? 'chip--down' : 'chip--flat');
    $arw = $d > 0 ? '▲' : ($d < 0 ? '▼' : '—');
    return '<span class="chip ' . $cls . '">' . $arw . ' ' . number_format(abs($d), 0) . '%</span>';
}
$maxDay = 1;
foreach ($this7['days'] as $d) { $maxDay = max($maxDay, $d['v']); }
?><!DOCTYPE html>
<html lang="en-GB"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title>Weekly performance — Luke Goulden Coaching</title>
<style>
  @font-face{font-family:Manrope;src:url('<?= DS ?>/assets/fonts/Manrope-Regular.ttf') format('truetype');font-weight:400;font-display:swap}
  @font-face{font-family:Manrope;src:url('<?= DS ?>/assets/fonts/Manrope-SemiBold.ttf') format('truetype');font-weight:600;font-display:swap}
  @font-face{font-family:Manrope;src:url('<?= DS ?>/assets/fonts/Manrope-Bold.ttf') format('truetype');font-weight:700;font-display:swap}
  @font-face{font-family:Manrope;src:url('<?= DS ?>/assets/fonts/Manrope-ExtraBold.ttf') format('truetype');font-weight:800;font-display:swap}

  :root{
    --teal:#1A3C34; --teal-900:#0F2621; --coral:#E05A3A; --sage:#84B59F;
    --off:#F7F5F0; --ink:#1E1E1E; --muted:#6b6b6b; --line:rgba(30,30,30,.12);
  }
  *{box-sizing:border-box}
  body{margin:0;background:var(--off);color:var(--ink);
       font:16px/1.6 Manrope,system-ui,-apple-system,sans-serif;
       -webkit-font-smoothing:antialiased}
  .sheet{max-width:56rem;margin:0 auto;padding:2.5rem 1.5rem 5rem}

  /* Masthead */
  .mast{display:flex;align-items:center;justify-content:space-between;gap:1rem;
        padding-bottom:1.25rem;border-bottom:2px solid var(--teal);margin-bottom:2rem}
  .brand{display:inline-flex;align-items:center;gap:.6rem;color:var(--teal)}
  .brand svg{height:1.5rem;width:auto}
  .wordmark{font-weight:700;letter-spacing:.22em;text-transform:uppercase;font-size:.9rem}
  .mast .meta{text-align:right;font-size:.78rem;color:var(--muted);line-height:1.35}
  .mast .meta b{display:block;color:var(--teal);font-size:.85rem;letter-spacing:.1em;text-transform:uppercase}

  h1{font-size:1.9rem;font-weight:800;letter-spacing:-.015em;color:var(--teal);margin:0 0 .35rem}
  .lede{color:var(--muted);margin:0 0 2rem;max-width:46rem}

  /* KPI row */
  .kpis{display:grid;grid-template-columns:repeat(3,1fr);gap:1rem;margin-bottom:1rem}
  .kpi{background:#fff;border:1px solid var(--line);border-radius:12px;padding:1.35rem}
  .kpi--hero{background:var(--teal);border-color:var(--teal)}
  .kpi .n{font-size:2.4rem;font-weight:800;line-height:1;letter-spacing:-.03em;color:var(--teal)}
  .kpi--hero .n{color:#fff}
  .kpi .l{font-size:.68rem;letter-spacing:.14em;text-transform:uppercase;color:var(--muted);margin-top:.5rem}
  .kpi--hero .l{color:var(--sage)}
  .chip{display:inline-block;margin-top:.6rem;font-size:.72rem;font-weight:700;
        padding:.15rem .5rem;border-radius:99px}
  .chip--up{background:rgba(132,181,159,.22);color:#2f6a55}
  .chip--down{background:rgba(224,90,58,.14);color:#a33d24}
  .chip--flat{background:rgba(30,30,30,.06);color:var(--muted)}
  .kpi--hero .chip--up{background:rgba(132,181,159,.25);color:#cfe8dc}
  .kpi--hero .chip--down{background:rgba(224,90,58,.3);color:#ffd9cd}
  .kpi--hero .chip--flat{background:rgba(255,255,255,.12);color:#cfe8dc}

  h2{font-size:.78rem;letter-spacing:.14em;text-transform:uppercase;color:var(--teal);
     margin:2.5rem 0 .85rem;font-weight:700}
  table{width:100%;border-collapse:collapse;background:#fff;border:1px solid var(--line);
        border-radius:12px;overflow:hidden}
  th,td{text-align:left;padding:.75rem 1rem;border-bottom:1px solid var(--line);font-size:.92rem}
  th{background:#fbfaf8;font-size:.66rem;letter-spacing:.12em;text-transform:uppercase;color:var(--muted);font-weight:700}
  tr:last-child td{border-bottom:0}
  td.num{text-align:right;font-variant-numeric:tabular-nums;font-weight:700}
  .bar{height:7px;background:var(--coral);border-radius:99px;min-width:3px}
  .bar--sage{background:var(--sage)}
  .empty{background:#fff;border:1px dashed var(--line);border-radius:12px;padding:2.5rem 1.5rem;
         text-align:center;color:var(--muted)}

  .read{margin-top:2.5rem;background:#fff;border-left:3px solid var(--sage);
        border-radius:0 12px 12px 0;padding:1.25rem 1.5rem}
  .read h3{margin:0 0 .5rem;font-size:.95rem;color:var(--teal)}
  .read p{margin:0 0 .6rem;font-size:.9rem;color:#444}
  .read p:last-child{margin-bottom:0}

  .foot{margin-top:3rem;padding-top:1.25rem;border-top:1px solid var(--line);
        font-size:.75rem;color:var(--muted);display:flex;justify-content:space-between;gap:1rem;flex-wrap:wrap}

  .actions{display:flex;gap:.6rem;margin-bottom:1rem;flex-wrap:wrap}
  .lpfilter{display:flex;gap:.4rem;flex-wrap:wrap;margin-bottom:2rem}
  .lpfilter a{font-size:.72rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;
              text-decoration:none;color:var(--teal);border:1px solid var(--line);
              padding:.4rem .8rem;border-radius:99px;background:#fff}
  .lpfilter a.on{background:var(--teal);color:#fff;border-color:var(--teal)}
  .lpfilter a.off{opacity:.55}
  .btn{display:inline-flex;align-items:center;gap:.4rem;border:0;cursor:pointer;
       background:var(--coral);color:#fff;font:inherit;font-weight:700;font-size:.78rem;
       letter-spacing:.1em;text-transform:uppercase;padding:.7rem 1.15rem;border-radius:4px}
  .btn--ghost{background:transparent;color:var(--teal);border:1px solid var(--line)}

  @media(max-width:40em){ .kpis{grid-template-columns:1fr} .mast{flex-direction:column;align-items:flex-start} .mast .meta{text-align:left} }

  /* Print / Save-as-PDF: strip the chrome, keep the brand. */
  @media print{
    @page{margin:14mm}
    body{background:#fff}
    .sheet{max-width:none;padding:0}
    .actions{display:none}
    .kpi,table,.read,.empty{break-inside:avoid}
    h2{break-after:avoid}
  }
</style></head><body>
<?php lgc_nav('report'); ?>
<div class="sheet">

  <div class="mast">
    <div class="brand">
      <svg viewBox="76 78 298 237" role="img" aria-label="Luke Goulden">
        <path fill="currentColor" d="M305.5 155A118.5 118.5 0 1 0 305.5 238L264.6 238A81.5 81.5 0 1 1 264.6 155Z"/>
        <rect x="171" y="179" width="203" height="36" fill="currentColor"/>
      </svg>
      <span class="wordmark">Luke Goulden</span>
    </div>
    <div class="meta">
      <b>Weekly report</b>
      <?= htmlspecialchars($periodLabel) ?><br>
      Generated <?= gmdate('j M Y, H:i') ?> UTC
    </div>
  </div>

  <h1>Landing page performance</h1>
  <p class="lede">
    How <b>lukegouldencoaching.com</b> performed at its one job this week: turning visitors into
    booked calls on Calendly.
  </p>

  <div class="actions">
    <button class="btn" onclick="window.print()">Save as PDF</button>
    <a class="btn btn--ghost" href="https://lukegouldencoaching.com/" target="_blank" rel="noopener">View the page</a>
    <a class="btn btn--ghost" href="?logout=1">Sign out</a>
  </div>

  <div class="lpfilter">
    <a class="<?= $lpFilter === '' ? 'on' : '' ?>" href="?">All pages</a>
    <?php foreach ($LPS as $id => $v): ?>
      <a class="<?= $lpFilter === $id ? 'on' : '' ?><?= empty($v['live']) ? ' off' : '' ?>"
         href="?lp=<?= $id ?>"><?= htmlspecialchars($v['name']) ?><?= empty($v['live']) ? ' · paused' : '' ?></a>
    <?php endforeach; ?>
  </div>
  <?php $me = auth_user(); if ($me): ?>
    <p style="margin:-1.25rem 0 2rem;font-size:.78rem;color:var(--muted)">
      Signed in as <?= htmlspecialchars($me['name']) ?> · <?= htmlspecialchars($me['email']) ?>
    </p>
  <?php endif; ?>

  <div class="kpis">
    <div class="kpi">
      <div class="n"><?= number_format($this7['views']) ?></div>
      <div class="l">People who saw the page</div>
      <?= chip($dViews) ?>
    </div>
    <div class="kpi">
      <div class="n"><?= number_format($this7['clicks']) ?></div>
      <div class="l">Clicked through to book</div>
      <?= chip($dClicks) ?>
    </div>
    <div class="kpi kpi--hero">
      <div class="n"><?= number_format($ctrNow, 1) ?>%</div>
      <div class="l">Click-through rate</div>
      <?= chip($dCtr) ?>
    </div>
  </div>

  <?php if (!$hasData): ?>
    <div class="empty">
      <b>No traffic yet.</b><br>
      The page is live and tracking. Numbers will appear here as soon as visitors start arriving.
    </div>
  <?php else: ?>

    <h2>Which button earns the click</h2>
    <?php if (!$this7['cta']): ?>
      <div class="empty">Visitors arrived, but nobody clicked through this week.</div>
    <?php else: $top = max($this7['cta']); ?>
      <table>
        <tr><th>Position on the page</th><th style="width:32%">Share</th><th class="num">Clicks</th><th class="num">% of all</th></tr>
        <?php foreach ($this7['cta'] as $cta => $n): ?>
          <tr>
            <td><?= htmlspecialchars($ctaLabels[$cta] ?? $cta) ?></td>
            <td><div class="bar" style="width:<?= max(3, round(($n / $top) * 100)) ?>%"></div></td>
            <td class="num"><?= number_format($n) ?></td>
            <td class="num"><?= pct($n, $this7['clicks']) ?>%</td>
          </tr>
        <?php endforeach; ?>
      </table>
    <?php endif; ?>

    <h2>Which landing page converts</h2>
    <?php if (!$this7['lp']): ?>
      <div class="empty">No traffic on any page yet.</div>
    <?php else: ?>
      <table>
        <tr><th>Landing page</th><th>Address</th><th class="num">Saw</th><th class="num">Clicked</th><th class="num">Click-through rate</th></tr>
        <?php foreach ($this7['lp'] as $id => $x):
              $v = $LPS[$id] ?? ['name' => 'Page ' . $id, 'path' => '/']; ?>
          <tr>
            <td><b><?= htmlspecialchars($v['name']) ?></b><?= empty($v['live']) ? ' <span style="color:var(--muted);font-size:.8em">(paused)</span>' : '' ?></td>
            <td style="color:var(--muted);font-size:.85em"><?= htmlspecialchars($v['path'] ?? '/') ?></td>
            <td class="num"><?= number_format($x['v']) ?></td>
            <td class="num"><?= number_format($x['c']) ?></td>
            <td class="num"><?= pct($x['c'], $x['v']) ?>%</td>
          </tr>
        <?php endforeach; ?>
      </table>
      <p style="font-size:.82rem;color:var(--muted);margin-top:.6rem">
        Compare the rate, not the totals — the page with more visitors isn’t necessarily the better page.
      </p>
    <?php endif; ?>

    <h2>Mobile vs desktop</h2>
    <table>
      <tr><th>Device</th><th class="num">Saw the page</th><th class="num">Clicked</th><th class="num">Click-through rate</th></tr>
      <tr>
        <td>Mobile</td>
        <td class="num"><?= number_format($this7['device']['m']['v']) ?></td>
        <td class="num"><?= number_format($this7['device']['m']['c']) ?></td>
        <td class="num"><?= pct($this7['device']['m']['c'], $this7['device']['m']['v']) ?>%</td>
      </tr>
      <tr>
        <td>Desktop</td>
        <td class="num"><?= number_format($this7['device']['d']['v']) ?></td>
        <td class="num"><?= number_format($this7['device']['d']['c']) ?></td>
        <td class="num"><?= pct($this7['device']['d']['c'], $this7['device']['d']['v']) ?>%</td>
      </tr>
    </table>

    <?php if ($this7['ref']): ?>
      <h2>Where the visitors came from</h2>
      <table>
        <tr><th>Source</th><th class="num">Visits</th></tr>
        <?php foreach (array_slice($this7['ref'], 0, 8, true) as $r => $n): ?>
          <tr><td><?= htmlspecialchars($r) ?></td><td class="num"><?= number_format($n) ?></td></tr>
        <?php endforeach; ?>
      </table>
    <?php endif; ?>

    <?php if ($this7['days']): ?>
      <h2>Day by day</h2>
      <table>
        <tr><th>Day</th><th style="width:32%">Visitors</th><th class="num">Saw</th><th class="num">Clicked</th><th class="num">CTR</th></tr>
        <?php foreach ($this7['days'] as $d => $x): ?>
          <tr>
            <td><?= date('D j M', strtotime($d)) ?></td>
            <td><div class="bar bar--sage" style="width:<?= max(3, round(($x['v'] / $maxDay) * 100)) ?>%"></div></td>
            <td class="num"><?= number_format($x['v']) ?></td>
            <td class="num"><?= number_format($x['c']) ?></td>
            <td class="num"><?= pct($x['c'], $x['v']) ?>%</td>
          </tr>
        <?php endforeach; ?>
      </table>
    <?php endif; ?>

  <?php endif; ?>

  <div class="read">
    <h3>How to read this</h3>
    <p><b>Click-through rate is the number this page controls.</b> Of everyone who landed here, this is the share
      who went on to the booking page. If it falls, the page needs work. If visits fall but the rate holds,
      the page is fine — the traffic dried up.</p>
    <p><b>What happens after the click is on Luke's side.</b> Compare the clicks above with the calls that
      actually land in his Calendly: clicks in, calls booked out. A big gap means the booking page — not this
      page — is where people are dropping off.</p>
    <p><b>Privacy:</b> no cookies, no IP addresses, no personal data. Nothing here can identify a visitor, which is
      why the page carries no consent banner.</p>
  </div>

  <div class="foot">
    <span>Luke Goulden Coaching — landing page report</span>
    <span>lukegouldencoaching.com → Calendly booking</span>
  </div>

</div></body></html>
