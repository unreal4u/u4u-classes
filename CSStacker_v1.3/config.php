<?php if(!isset($proc)) die('Sorry, direct access not allowed!');

define('CHARSET','UTF-8');             // The charset to use
define('OPTIMIZE_CSS',TRUE);           // Whether to strip the most common byte-eaters
define('USE_CSS_CACHE',TRUE);          // Whether to use internal cache
define('GZIP_CONTENTS',TRUE);          // Use TRUE only when the server doesn't compress CSS natively
define('GZIP_LEVEL',6);                // GZIP compression level, range from 1 to 9
define('CACHE_LOCATION','examples/cache/');     // Cache location, WITH trailing slash, should be writable
define('USE_BROWSER_CACHE',TRUE);      // Whether to instruct the browser to save the CSS in cache
define('TIME_BROWSER_CACHE','3600');   // Time in seconds the browser caches our CSS
