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
    //$db->keepLiveLog = true;
    //$db->supressErrors = true;

    $db->query("CREATE TABLE `t1` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `string_valued` varchar(255) NOT NULL,
  `int_valued` int(11) NOT NULL,
  `bool_valued` bit(1) NOT NULL,
  `string_null_valued` varchar(255) DEFAULT NULL,
  `int_null_valued` int(11) DEFAULT NULL,
  `bool_null_valued` bit(1) DEFAULT NULL,
  `float_valued` decimal(6,2) NOT NULL,
  `float_null_valued` decimal(6,2) DEFAULT NULL,
  `datetime_valued` datetime NOT NULL,
  `datetime_null_valued` datetime DEFAULT NULL,
  `timestamp_valued` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `timestamp_null_valued` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8");

    $db->query('INSERT INTO t1 (string_valued, int_valued, bool_valued, string_null_valued, int_null_valued, bool_null_valued, float_valued, float_null_valued, datetime_valued,       datetime_null_valued, timestamp_valued, timestamp_null_valued) VALUES (?,?,?,?,?,?,?,?,?,?,NOW(),?)',
                                'a',           11,         true,        null,               null,            null,             11.22,        null,              '2001-02-03 00:00:00', null,                                   null
    );
    $db->query('INSERT INTO t1 (string_valued, int_valued, bool_valued, string_null_valued, int_null_valued, bool_null_valued, float_valued, float_null_valued, datetime_valued,       datetime_null_valued, timestamp_valued, timestamp_null_valued) VALUES (?,?,?,?,?,?,?,?,?,?,NOW(),?)',
                                'b',           0,          false,       null,               null,            null,             11.22,        null,              '2001-02-03 00:00:00', null,                                   null
    );
    $db->query('INSERT INTO t1 (string_valued, int_valued, bool_valued, string_null_valued, int_null_valued, bool_null_valued, float_valued, float_null_valued, datetime_valued,       datetime_null_valued, timestamp_valued, timestamp_null_valued) VALUES (?,?,?,?,?,?,?,?,?,?,NOW(),?)',
                                'c',           -1,         true,        'null',             0,               false,            0,            null,              '2001-02-03 00:00:00', null,                                   time()
    );
    $db->query('INSERT INTO t1 (string_valued, int_valued, bool_valued, string_null_valued, int_null_valued, bool_null_valued, float_valued, float_null_valued, datetime_valued,       datetime_null_valued, timestamp_valued, timestamp_null_valued) VALUES (?,?,?,?,?,?,?,?,?,?,NOW(),?)',
                                'd',           0,          false,       null,               null,            null,             11.22,        null,              '2001-02-03 00:00:00', null,                                   null
    );

    print('-------------------------- SELECT * FROM t1 --------------------------');
    $resultSet = $db->query('SELECT * FROM t1');
    if ($db->num_rows > 0) {
        var_dump($resultSet);
    }

    print("-------------------------- SELECT * FROM t1 WHERE string_valued = 'b' --------------------------");
    $resultSet = $db->query('SELECT * FROM t1 WHERE string_valued = ?', 'b');
    var_dump($resultSet);

    print("-------------------------- SELECT * FROM t1 WHERE string_valued = 'z' --------------------------");
    $resultSet = $db->query('SELECT * FROM t1 WHERE string_valued = ?', 'z');
    if ($db->num_rows > 0) {
        var_dump($resultSet);
    } else {
        var_dump($resultSet);
    }

    $db->query('DROP TABLE t1');

    print("-------------------------- dbLiveStats --------------------------");
    var_dump($db->dbLiveStats);

    print("-------------------------- dbErrors --------------------------");
    var_dump($db->dbErrors);

} catch (databaseException $e) {
    print('Error: <strong>'.$e->getMessage().'</strong><br />File: '.$e->getFile().':'.$e->getLine());
}
