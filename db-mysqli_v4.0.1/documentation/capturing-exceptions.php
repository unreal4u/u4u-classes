<?php
/**
 * Capturing exceptions of the class
 *
 * @package db_mysqli
 * @author Camilo Sperberg - http://unreal4u.com/
 */
include('../config.php');
include('../db_mysqli.class.php');

try {
    $db = new db_mysqli();
    $db->throwQueryExceptions = true;
} catch (databaseException $e) {
    exit($e->getMessage());
}


try {
    $insertId = $db->insert_id('INSERT INTO a (string_valued, int_valued, bool_valued, string_null_valued, int_null_valued, bool_null_valued, float_valued, float_null_valued, datetime_valued,       datetime_null_valued, timestamp_valued, timestamp_null_valued) VALUES (?,?,?,?,?,?,?,?,?,?,NOW(),?)',
                                               'd',           0,          false,       null,               null,            null,             11.22,        null,              '2001-02-03 00:00:00', null,                                   null
    );

    var_dump('The insertId is: '.$insertId);
} catch (databaseException $e) {
    var_dump($e->getMessage());
}

try {
    $db->query();
} catch (databaseException $e) {
    var_dump($e->getMessage());
} catch (queryException $e) {
    var_dump($e->getMessage());
}

try {
    $db->query('INSERT INTO b (nonexistant) VALUES (?)', 22);
} catch (databaseException $e) {
    var_dump($e->getMessage());
} catch (queryException $e) {
    var_dump($e->getMessage());
}

var_dump($db->dbErrors);
