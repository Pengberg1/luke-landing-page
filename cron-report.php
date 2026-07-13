<?php
/**
 * Weekly email report — fired by a Simply.com cron job every Friday.
 *
 * Cron URL (set in control panel → Website → Cronjobs):
 *   https://lukegouldencoaching.com/cron-report.php?key=lgc-cron-2026
 *
 * Sends a branded HTML summary to Pedro with the week's numbers and a link
 * to the always-current live report. Deliberately no PDF attachment: the
 * live report has a "Save as PDF" button, and attachments from shared
 * hosting are the fastest way into a spam folder.
 */

const CRON_KEY  = 'lgc-cron-2026';
const MAIL_TO   = 'kempandersen@gmail.com';
const MAIL_FROM = 'rapport@lukegouldencoaching.com';
const LIVE_URL  = 'https://lukegouldencoaching.com/report/results';

if (($_GET['key'] ?? '') !== CRON_KEY) {
    header('HTTP/1.1 404 Not Found');
    exit('Not found');
}

/* Read the event log directly. An earlier version included report.php to reuse
   its JSON — but report.php exits after printing, which killed the mailer before
   it ever ran. Self-contained beats clever. */
function readWindow(DateTime $from, DateTime $to): array {
    $out = ['views' => 0, 'clicks' => 0, 'cta' => []];
    foreach (glob(__DIR__ . '/lgc-data/events-*.csv') ?: [] as $file) {
        $fh = @fopen($file, 'r');
        if (!$fh) continue;
        while (($row = fgetcsv($fh)) !== false) {
            if (count($row) < 6) continue;
            [$ts, $event, $cta] = $row;
            try { $when = new DateTime($ts, new DateTimeZone('UTC')); } catch (Exception $e) { continue; }
            if ($when < $from || $when >= $to) continue;
            if ($event === 'view') {
                $out['views']++;
            } elseif ($event === 'cta') {
                $out['clicks']++;
                $out['cta'][$cta] = ($out['cta'][$cta] ?? 0) + 1;
            }
        }
        fclose($fh);
    }
    arsort($out['cta']);
    return $out;
}

$tz        = new DateTimeZone('UTC');
$end       = new DateTime('tomorrow', $tz);
$start     = (clone $end)->modify('-7 days');
$prevEnd   = clone $start;
$prevStart = (clone $prevEnd)->modify('-7 days');

$w  = readWindow($start, $end);
$pw = readWindow($prevStart, $prevEnd);

$ctrNow  = $w['views']  > 0 ? ($w['clicks']  / $w['views'])  * 100 : 0;
$ctrPrev = $pw['views'] > 0 ? ($pw['clicks'] / $pw['views']) * 100 : 0;
$change  = function (float $now, float $before): ?float {
    return $before > 0 ? (($now - $before) / $before) * 100 : null;
};

$d = [
    'period'     => ['from' => $start->format('Y-m-d'), 'to' => (clone $end)->modify('-1 day')->format('Y-m-d')],
    'this_week'  => ['views' => $w['views'], 'clicks' => $w['clicks'], 'ctr' => round($ctrNow, 2)],
    'change_pct' => [
        'views'  => $change($w['views'],  $pw['views']),
        'clicks' => $change($w['clicks'], $pw['clicks']),
        'ctr'    => $change($ctrNow, $ctrPrev),
    ],
    'by_cta' => $w['cta'],
];

$now = $d['this_week'];
$chg = $d['change_pct'];

function arrow(?float $v): string {
    if ($v === null) return '<span style="color:#6b6b6b">no prior week</span>';
    if ($v > 0)  return '<span style="color:#2f6a55">&#9650; ' . number_format(abs($v), 0) . '%</span>';
    if ($v < 0)  return '<span style="color:#a33d24">&#9660; ' . number_format(abs($v), 0) . '%</span>';
    return '<span style="color:#6b6b6b">no change</span>';
}

$ctaLabels = [
    'hero' => 'Hero (above the fold)',
    'after_results' => 'Mid-page band (after the proof)',
    'after_included' => 'After “What’s included”',
    'closing' => 'Closing section',
    'sticky_mobile' => 'Mobile sticky bar',
    'header' => 'Header button',
];
$ctaRows = '';
foreach (($d['by_cta'] ?? []) as $k => $n) {
    $ctaRows .= '<tr><td style="padding:8px 0;border-bottom:1px solid #eee;color:#1E1E1E">'
        . htmlspecialchars($ctaLabels[$k] ?? $k)
        . '</td><td align="right" style="padding:8px 0;border-bottom:1px solid #eee;font-weight:700;color:#1A3C34">'
        . (int)$n . '</td></tr>';
}
if ($ctaRows === '') {
    $ctaRows = '<tr><td colspan="2" style="padding:12px 0;color:#6b6b6b">No clicks recorded this week.</td></tr>';
}

$period = $d['period']['from'] . ' → ' . $d['period']['to'];
$noTraffic = ((int)$now['views'] === 0);

$body = '<!DOCTYPE html><html><body style="margin:0;padding:0;background:#F7F5F0">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#F7F5F0;padding:28px 12px">
<tr><td align="center">
<table width="600" cellpadding="0" cellspacing="0" style="max-width:600px;background:#fff;border-radius:14px;overflow:hidden;font-family:Helvetica,Arial,sans-serif">

  <tr><td style="background:#1A3C34;padding:26px 30px">
    <div style="color:#F7F5F0;font-size:12px;letter-spacing:.22em;text-transform:uppercase;font-weight:700">Luke Goulden</div>
    <div style="color:#84B59F;font-size:13px;margin-top:6px">Weekly landing page report · ' . htmlspecialchars($period) . '</div>
  </td></tr>

  <tr><td style="padding:30px">
    <p style="margin:0 0 22px;color:#1E1E1E;font-size:15px;line-height:1.6">
      How <b>lukegouldencoaching.com</b> performed at its one job this week: turning visitors into
      applications on lukegoulden.com/contact/.
    </p>

    <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:26px">
      <tr>
        <td width="33%" style="padding:14px;background:#F7F5F0;border-radius:10px" align="center">
          <div style="font-size:26px;font-weight:800;color:#1A3C34">' . number_format($now['views']) . '</div>
          <div style="font-size:10px;letter-spacing:.1em;text-transform:uppercase;color:#6b6b6b;margin-top:4px">Saw the page</div>
          <div style="font-size:11px;margin-top:6px">' . arrow($chg['views']) . '</div>
        </td>
        <td width="8"></td>
        <td width="33%" style="padding:14px;background:#F7F5F0;border-radius:10px" align="center">
          <div style="font-size:26px;font-weight:800;color:#1A3C34">' . number_format($now['clicks']) . '</div>
          <div style="font-size:10px;letter-spacing:.1em;text-transform:uppercase;color:#6b6b6b;margin-top:4px">Clicked to apply</div>
          <div style="font-size:11px;margin-top:6px">' . arrow($chg['clicks']) . '</div>
        </td>
        <td width="8"></td>
        <td width="33%" style="padding:14px;background:#1A3C34;border-radius:10px" align="center">
          <div style="font-size:26px;font-weight:800;color:#fff">' . number_format($now['ctr'], 1) . '%</div>
          <div style="font-size:10px;letter-spacing:.1em;text-transform:uppercase;color:#84B59F;margin-top:4px">Click-through</div>
          <div style="font-size:11px;margin-top:6px;color:#cfe8dc">' . arrow($chg['ctr']) . '</div>
        </td>
      </tr>
    </table>

    <div style="font-size:11px;letter-spacing:.12em;text-transform:uppercase;color:#1A3C34;font-weight:700;margin-bottom:8px">Which button earned the clicks</div>
    <table width="100%" cellpadding="0" cellspacing="0" style="font-size:14px;margin-bottom:26px">' . $ctaRows . '</table>'
    . ($noTraffic
        ? '<p style="margin:0 0 22px;padding:14px 16px;background:#F7F5F0;border-left:3px solid #84B59F;color:#444;font-size:14px;line-height:1.6">
             No traffic reached the page this week. The page is live and tracking correctly — this is a traffic problem, not a page problem.
           </p>'
        : '')
    . '<a href="' . LIVE_URL . '" style="display:inline-block;background:#E05A3A;color:#fff;text-decoration:none;
          font-weight:700;font-size:12px;letter-spacing:.1em;text-transform:uppercase;padding:14px 22px;border-radius:4px">
       Open the full report</a>

    <p style="margin:22px 0 0;color:#6b6b6b;font-size:12px;line-height:1.6">
      The live report is always current — you can forward that same link to Luke, and it has a “Save as PDF” button.
    </p>
  </td></tr>

  <tr><td style="padding:18px 30px;background:#F7F5F0;color:#6b6b6b;font-size:11px">
    lukegouldencoaching.com → lukegoulden.com/contact/ · no cookies, no personal data collected
  </td></tr>

</table></td></tr></table></body></html>';

$subject = sprintf(
    'Luke LP — %s views, %s clicks, %s%% CTR (%s)',
    number_format($now['views']),
    number_format($now['clicks']),
    number_format($now['ctr'], 1),
    $d['period']['to']
);

$headers  = "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: text/html; charset=UTF-8\r\n";
$headers .= 'From: Luke Goulden Report <' . MAIL_FROM . ">\r\n";
$headers .= 'Reply-To: ' . MAIL_TO . "\r\n";

$sent = @mail(MAIL_TO, $subject, $body, $headers, '-f' . MAIL_FROM);

echo $sent
    ? 'Sent to ' . htmlspecialchars(MAIL_TO) . ' — ' . htmlspecialchars($subject)
    : 'mail() returned false. Check the webspace mail settings.';
