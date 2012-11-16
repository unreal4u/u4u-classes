<?php
/**
 * Config file for db_mysqli.class.php
 */

// Overwrite this if you are living in another zone
date_default_timezone_set('America/Santiago');

/* CLASS CONFIGURATION */
define('DB_LOG_XML',FALSE);                   // Log all database activity to XML?
define('DB_URL_XML','/home/user/db-log.xml'); // Location of XML file, recommended place is outside the public_html directory!
// @deprecate
define('DB_CACHE_LOCATION','cache/');         // Location of cache file(s), with trailing slash
// @deprecate
define('DB_CACHE_EXPIRE','30');               // DB cache file expiricy, in seconds

define('MYSQL_HOST','localhost');             // your db's host
define('MYSQL_PORT',3306);                    // your db's port
define('MYSQL_USER','sgp');                   // your db's username
define('MYSQL_PASS','holamundo');             // your db's password
define('MYSQL_NAME','test');                  // your db's database name
define('DBCHAR','utf8');                      // The DB's charset
