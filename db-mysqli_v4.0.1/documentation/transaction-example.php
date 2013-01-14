<?php
/**
 * Example of transactions
 *
 * @package db_mysqli
 * @author Camilo Sperberg - http://unreal4u.com/
 */

include('../config.php');
include('../db_mysqli.class.php');

include('../../stable-versions.php');
$u4uLoader = new u4u_autoloader();

$bench = new benchmark('databaseCalls');
$db = new db_mysqli();

echo '<pre>';

$bench->beginCounter('newTable');
try {
    $db->begin_transaction();
    $db->query('DROP TABLE IF EXISTS t');
    $db->query('CREATE TABLE t(i int(11) PRIMARY KEY) engine=InnoDB');
    $db->query('INSERT INTO t VALUES (?)',1);
    $db->query('INSERT INTO t VALUES (?)',2);
    $db->end_transaction();
} catch (queryException $e) {
    print_r($e->getMessage().'<br />');
}
$bench->endCounter('newTable');
print_r('<em>First transaction ended, starting with second (which is the one to fail)</em><br />');

$bench->beginCounter('databaseException');
try {
    $db->begin_transaction();
    $db->query('INSERT INTO t VALUES (?)',3);
    $db->query('INSERT INTO t VALUES (?)',4);
    $db->query('INSERT INTO t VALUES (?)',2);
    $db->query('INSERT INTO t VALUES (?)',5);
    $db->end_transaction();
} catch (queryException $e) {
    print_r('Transaction failed! The message delivered by the database is: '.$e->getMessage().'<br />');
}
$bench->endCounter('databaseException');
print_r('<em>Second transaction ended, error check:</em><br />');

print_r($db->dbErrors);

print('<br /><em>Database version:</em><br />');
$bench->beginCounter('databaseVersion');
try {
    echo $db->version();
    print('<br />');
} catch (Exception $e) {
    print_r($e->getMessage().'<br />');
}
$bench->endCounter('databaseVersion');
$bench->endCounter('databaseCalls');

print_r('End of execution<br />');
print('Total time: '.$bench->getDiff('databaseCalls').'<br />');
print('New table creation: '.$bench->getDiff('newTable').'<br />');
print('Exception time: '.$bench->getDiff('databaseException').'<br />');
print('Version time: '.$bench->getDiff('databaseVersion').'<br />');
echo '</pre>';
