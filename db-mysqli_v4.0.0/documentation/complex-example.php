<?php

include('../../functions.php');

include('../config.php');
include('../db_mysqli.class.php');

try {
    $db = new db_mysqli();
} catch (databaseException $e) {
    debug($e->getMessage());
}

try {
$db->query('INSERT INTO a (string_valued, int_valued, bool_valued, string_null_valued, int_null_valued, bool_null_valued, float_valued, float_null_valued) VALUES (?,?,?,?,?,?,?,?)',
                'a',
                11,
                true,
                null,
                null,
                null,
                11.22,
                null);
} catch (databaseException $e) {
    debug($e->getMessage());
}

try {
$db->query('INSERT INTO a (string_valued, int_valued, bool_valued, string_null_valued, int_null_valued, bool_null_valued, float_valued, float_null_valued) VALUES (?,?,?,?,?,?,?,?)',
                'b',
                0,
                false,
                null,
                null,
                null,
                11.22,
                null);
} catch (databaseException $e) {
    debug($e->getMessage());
}