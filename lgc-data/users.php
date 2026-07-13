<?php
/**
 * User store for the report area.
 *
 * Passwords are stored ONLY as bcrypt hashes ($2y$, cost 12) — the plaintext
 * exists nowhere on this server. Verified with password_verify().
 *
 * It lives inside lgc-data/, which .htaccess blocks from the web (403), and
 * it's a .php file, so even if that block ever failed it would execute and
 * print nothing rather than dump its contents.
 *
 * status: 'approved' → can log in.  'pending' → waiting for Pedro's approval.
 */
return [
    [
        'email'  => 'contact@lukegoulden.com',
        'name'   => 'Luke Goulden',
        'hash'   => '$2y$12$gkBg.l0qy3LoIPqmO2aehu4U3KfLk2SO1ZjOhVTkjTWLa8SBmFG8O',
        'status' => 'approved',
        'added'  => '2026-07-13',
    ],
    [
        'email'  => 'pedro@kempandersen.dk',
        'name'   => 'Pedro Engberg Andersen',
        'hash'   => '$2y$12$FP5f/rpA1NQAsnkz5NI26OBrPicCsIsFeS68q9VThJUux61HxAK/W',
        'status' => 'approved',
        'added'  => '2026-07-13',
    ],
];
