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
define('MYSQL_HOST','localhost');             // your db's host
define('MYSQL_PORT',3306);                    // your db's port
define('MYSQL_USER','db_mysqli_v4');          // your db's username
define('MYSQL_PASS','db_mysqli_v4');          // your db's password
define('MYSQL_NAME','db_mysqli_v4');          // your db's database name

define('DB_CHAR','utf8');                     // The DB's charset
