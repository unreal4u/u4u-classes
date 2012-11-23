<?php
/**
 * Config file for db_mysqli.class.php
 *
 * @package db_mysqli
 * @author Camilo Sperberg - http://unreal4u.com/
 */

// Overwrite this if you are living in another zone
date_default_timezone_set('America/Santiago');

/* CLASS CONFIGURATION */
define('CACHEMANAGER', '../cacheManager_v2.0/cacheManager.class.php'); // Location of cacheManager class
define('CACHEMANAGER_TYPE', 'apc');           // Until now, "apc" or "file". Leave empty to not use any. If you don't know what APC is, choose "file"

define('DB_LOG_XML', false);                  // Log all database activity to XML?
define('DB_URL_XML','/home/user/db-log.xml'); // Location of XML file, recommended place is outside the public_html directory!
// @deprecated
define('DB_CACHE_LOCATION','cache/');         // Location of cache file(s), with trailing slash
// @deprecated
define('DB_CACHE_EXPIRE','30');               // DB cache file expiricy, in seconds

define('MYSQL_HOST','localhost');             // your db's host
define('MYSQL_PORT',3306);                    // your db's port
define('MYSQL_USER','db_mysqli_v4');          // your db's username
define('MYSQL_PASS','db_mysqli_v4');          // your db's password
define('MYSQL_NAME','db_mysqli_v42');          // your db's database name
define('DB_CHAR','utf8');                     // The DB's charset
