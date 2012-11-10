<?php
/**
 * Descripcion del modulo
 *
 * @package General
 * @version $Rev$
 * @copyright $Date$
 * @author $Author$
 */

define('INCL','.');

function _m($msg = '') {
  if (!empty($msg)) echo $msg."\n";
  return true;
}

include('config.php');
include('db_mysqli.class.php');

$db = new db_mysqli();

echo '<pre>';

$db->begin_transaction();
$db->query('DROP TABLE IF EXISTS t');
$db->query('CREATE TABLE t(i int(11) PRIMARY KEY) engine=InnoDB');
$db->query('INSERT INTO t VALUES (?)',1);
$db->query('INSERT INTO t VALUES (?)',2);
$db->end_transaction();
_m('<em>Se termino el primer periodo de transa, empieza segundo</em>');

$db->begin_transaction();
$db->query('INSERT INTO t VALUES (?)',3);
$db->query('INSERT INTO t VALUES (?)',4);
$db->query('INSERT INTO t VALUES (?)',2);
$db->end_transaction();
_m('<em>Termina segundo periodo de transa.</em>');

print_r($db->dbErrors);
//print_r($db->dbLiveStats);
_m('Termina ejecucion');

echo $db->version(true);

//print_r($db);

echo '</pre>';



