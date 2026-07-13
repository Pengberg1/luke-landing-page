<?php
/**
 * /report/ — the shareable address. Same report, no key visible in the URL.
 *
 * Deliberately a real folder rather than only an .htaccess rewrite: this keeps
 * working even if the host resets .htaccess, and it survives the control
 * panel's habit of silently dropping uploads of dotfiles.
 */
$_GET['k'] = 'results';
require __DIR__ . '/../report.php';
