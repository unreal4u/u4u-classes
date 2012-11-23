<?php
/**
 * More complex example of class usage
 *
 * @package db_mysqli
 * @author Camilo Sperberg - http://unreal4u.com/
 */
include('../../functions.php');

include('../config.php');
include('../db_mysqli.class.php');

try {
    $db = new db_mysqli();
    $db->keepLiveLog = false;
    //$db->supressErrors = true;

    $db->query('INSERT INTO a (string_valued, int_valued, bool_valued, string_null_valued, int_null_valued, bool_null_valued, float_valued, float_null_valued, datetime_valued,       datetime_null_valued, timestamp_valued, timestamp_null_valued) VALUES (?,?,?,?,?,?,?,?,?,?,NOW(),?)',
                               'a',           11,         true,        null,               null,            null,             11.22,        null,              '2001-02-03 00:00:00', null,                                   null
    );
    $db->query('INSERT INTO a (string_valued, int_valued, bool_valued, string_null_valued, int_null_valued, bool_null_valued, float_valued, float_null_valued, datetime_valued,       datetime_null_valued, timestamp_valued, timestamp_null_valued) VALUES (?,?,?,?,?,?,?,?,?,?,NOW(),?)',
                               'b',           0,          false,       null,               null,            null,             11.22,        null,              '2001-02-03 00:00:00', null,                                   null
    );
    $db->query('INSERT INTO a (string_valued, int_valued, bool_valued, string_null_valued, int_null_valued, bool_null_valued, float_valued, float_null_valued, datetime_valued,       datetime_null_valued, timestamp_valued, timestamp_null_valued) VALUES (?,?,?,?,?,?,?,?,?,?,NOW(),?)',
                               'c',           -1,         true,        'null',             0,               false,            0,            null,              '2001-02-03 00:00:00', null,                                   time()
    );
    $db->query('INSERT INTO a (string_valued, int_valued, bool_valued, string_null_valued, int_null_valued, bool_null_valued, float_valued, float_null_valued, datetime_valued,       datetime_null_valued, timestamp_valued, timestamp_null_valued) VALUES (?,?,?,?,?,?,?,?,?,?,NOW(),?)',
                               'd',           0,          false,       null,               null,            null,             11.22,        null,              '2001-02-03 00:00:00', null,                                   null
    );

    $db->setTtl(5);
    $db->cache_query = true;
    $resultSet = $db->query('SELECT * FROM a');
    var_dump($resultSet);

    $resultSet = $db->query('SELECT * FROM a WHERE string_valued = ?', 'b');
    var_dump($resultSet);

    $db->query('TRUNCATE TABLE a');

    //var_dump($db->dbErrors);
} catch (databaseException $e) {
    print('Error: <strong>'.$e->getMessage().'</strong><br />File: '.$e->getFile().':'.$e->getLine());
}
