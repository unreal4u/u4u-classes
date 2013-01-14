<?php
/**
 * Example of transactions
 *
 * @package db_mysqli
 * @author Camilo Sperberg - http://unreal4u.com/
 */

include('../config.php');
include('../db_mysqli.class.php');

include('../../functions.php');

$bench = new benchmarks('databaseException');
$db = new db_mysqli();

echo '<pre>';

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
print_r('<em>First transaction ended, starting with second (which is the one to fail)</em><br />');

try {
    #debug($db);
    $db->begin_transaction();
    $db->query('INSERT INTO t VALUES (?)',3);
    $db->query('INSERT INTO t VALUES (?)',4);
    $db->query('INSERT INTO t VALUES (?)',2);
    $db->query('INSERT INTO t VALUES (?)',5);
    $db->end_transaction();
} catch (queryException $e) {
    print_r('Transaction failed! The message delivered by the database is: '.$e->getMessage().'<br />');
}
print_r('<em>Second transaction ended, error check:</em><br />');

print_r($db->dbErrors);

print('<br /><em>Database version:</em><br />');
try {
    echo $db->version();
    print('<br />');
} catch (Exception $e) {
    print_r($e->getMessage().'<br />');
}

print_r('End of execution<br />');
print('Total time: '.$bench->endCounter('databaseException'));
echo '</pre>';
