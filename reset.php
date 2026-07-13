<?php
/**
 * One-shot utility: wipe the self-test events so Luke's numbers start clean.
 * Delete this file after running it once.
 */
if (($_GET['key'] ?? '') !== 'lgc-2026-nJ7xQ4') { header('HTTP/1.1 404 Not Found'); exit('Not found'); }
$n = 0;
foreach (glob(__DIR__ . '/lgc-data/events-*.csv') ?: [] as $f) { unlink($f); $n++; }
echo "Cleared {$n} event file(s). Counters now start from zero. Delete reset.php.";
