<?php

define('CHARSET','UTF-8');             // The charset to use
define('OPTIMIZE_CSS',true);           // Whether to strip the most common byte-eaters
define('USE_CSS_CACHE',true);          // Whether to use internal cache
define('GZIP_CONTENTS',true);          // Use TRUE only when the server doesn't compress CSS natively
define('GZIP_LEVEL',6);                // GZIP compression level, range from 1 to 9
define('CACHE_LOCATION', dirname(__FILE__).'/examples/cache/');     // Cache location, WITH trailing slash, should be writable
define('EXTERNAL_ROUTE', 'cache/');
define('USE_BROWSER_CACHE',true);      // Whether to instruct the browser to save the CSS in cache
define('TIME_BROWSER_CACHE','3600');   // Time in seconds the browser caches our CSS
