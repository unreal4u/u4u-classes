<?php if(empty($proc)) header('Location: ../../');
/**
 * Module Description
 * 
 * @package General
 * @version $Rev: 9 $
 * @copyright $Date: 2011-07-21 00:23:45 -0400 (Thu, 21 Jul 2011) $
 * @author $Author: unreal4u $
 * @license BSD License
 */

$r['locale'] = setlocale(LC_ALL,'es_CL','es_ES','es','ES');
date_default_timezone_set('America/Santiago');

define('TIEMPO' ,TRUE); // Show page generation time in footer? NOT A PART OF THIS CLASS!
if (TIEMPO == true) $q = time() + microtime();
define('MEMORY' ,TRUE); // Show memory usage in footer? NOT A PART OF THIS CLASS!


/* ************************************************************************** */
/*                        MAIN CONFIGURATION                                  */
/* ************************************************************************** */
define('CHARSET','UTF-8');
define('DB_SHOW_ERRORS',TRUE); // Show DB connection error to users?
define('DB_LOG_XML',FALSE); // Log all database activity to XML?
define('DB_URL_XML','/var/www/html/Classes-Camilo/Extended PGsql/cache/db-log.xml'); // Location of XML file, recommended place is outside the public_html directory!
define('DB_CACHE_LOCATION','cache/'); // Location of cache file(s), with trailing slash
define('DB_CACHE_EXPIRE','60'); // DB cache file expiricy, in seconds

define('PGSQL_HOST','localhost'); // your db's host
define('PGSQL_PORT',5432);        // your db's port
define('PGSQL_USER','unreal4u'); // your db's username
define('PGSQL_PASS','QWmin129'); // your db's password
define('PGSQL_NAME','u4u');   // your db's database name
define('PGSQL_FETCH_MODE',PDO::FETCH_ASSOC);
//define('DBCHAR','utf8'); // The DB's charset
/* ************************************************************************** */
/*                        END MAIN CONFIGURATION                              */
/* ************************************************************************** */ 
