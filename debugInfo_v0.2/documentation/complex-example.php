<?php

include('../debugInfo.class.php');

throwExceptions();

function a() {
    trigger_error("If you type google... into google... ", E_USER_ERROR);
}

function b() {
    throw new Exception('You can break the internet!');
}

try {
    a();
} catch (Exception $e) {
    debug('Captured exception 1: "'.$e->getMessage().'"');

    try {
        b();
    } catch (Exception $e) {
        debug('Captured exception 2: "'.$e->getMessage().'"');
    }
}

$array = array(
    null,
    false,
    true,
    1,
    3.1415,
    'hello',
    '',
);

foreach($array AS $value) {
    debug($value, true, 'VALUE:: ');
    $debugString = debug($value, false, 'VAL:: ');
    print $debugString;
}

debug($array);
$writtenToFile = debugFile($array, '', '/tmp/');
debug($writtenToFile, true, 'Written to file: ');

$debugInfo = new debugInfo();
print($debugInfo);

