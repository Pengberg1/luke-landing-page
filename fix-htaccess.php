<?php
/**
 * Spent. This was a one-shot maintenance script used to rewrite .htaccess
 * (Simply's file manager silently refuses dotfile uploads, and its zip extractor
 * never overwrites an existing file, so PHP had to write it).
 *
 * It has run. Its body is deliberately gone: the key that guarded it lives in a
 * public repo, so leaving a working .htaccess writer on the server would be a
 * standing invitation. The file stays only because the control panel's delete
 * control does not fire reliably — it now does nothing.
 */
http_response_code(404);
exit('Not found');
