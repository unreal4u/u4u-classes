<?php
/*
$Rev: 274 $
$Date: 2010-12-03 16:22:40 -0300 (Fri, 03 Dec 2010) $
$Author: unreal4u $
*/

/**
 * Almacena la configuraci&oacute;n general del sistema
 * @package Internals
 * @version 2.0
 * @author Camilo Sperberg
 */

/**
 * La locale que se aplicará al sistema
 * @var string $r['locale']
 */
$r['locale'] = setlocale(LC_ALL,'es_CL','es_ES','es','ES');
date_default_timezone_set('America/Santiago');

/* ************************************************************************** */
/*                        MAIN CONFIGURATION                                  */
/* ************************************************************************** */
define('CHARSET','UTF-8');
define('DB_SHOW_ERRORS',FALSE); // Show DB connection error to users?
define('DB_DATASIZE',FALSE); // NOT recommended for large queries! Haves an significant impact on speed!!
define('DB_LOG_XML',FALSE); // Log all database activity to XML?
define('DB_URL_XML','/home/user/db-log.xml'); // Location of XML file, recommended place is outside the public_html directory!
define('DB_CACHE_LOCATION','cache/'); // Location of cache file(s), with trailing slash
define('DB_CACHE_EXPIRE','30'); // DB cache file expiricy, in seconds

define('MYSQL_HOST','localhost'); // your db's host
define('MYSQL_PORT',3306);        // your db's port
define('MYSQL_USER','sgp'); // your db's username
define('MYSQL_PASS','holamundo'); // your db's password
define('MYSQL_NAME','test');   // your db's database name
define('DBCHAR','utf8'); // The DB's charset
/* ************************************************************************** */
/*                        END MAIN CONFIGURATION                              */
/* ************************************************************************** */
