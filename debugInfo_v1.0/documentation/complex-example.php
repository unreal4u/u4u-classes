<?php

use \u4u\debugInfo;
include('../debugInfo.class.php');

throwExceptions();

$debugInfo = new \u4u\debugInfo('beginScript');
printf('The exact timestamp at which this script started is %.4f (%s) and we are using %s of memory',
    getExactTime(),
    $debugInfo::convertTimestamp(getExactTime()),
    $debugInfo->getMemoryUsage('kib', true)
);

ob_start();

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
    debugInfo::debugFirePHP($value);
    print $debugString;
}

$nestedArray = $array;
$nestedArray['more'] = $array;
$nestedArray['more']['more'] = $array;

debug($array);
debug($nestedArray);

$writtenToFile = debugFile($array, '', '/tmp/');
debug($writtenToFile, true, 'Written to file: ');

print($debugInfo);

debugInfo::debugFirePHP($array);

$debugInfo->endCounter('beginScript');

printf('Terminating the script... Total time: ');
debug($debugInfo->getDiff('beginScript', 'all'));

printf('The exact timestamp at which this script ended is %.4f (%s) and we are using %s of memory',
    getExactTime(),
    $debugInfo::convertTimestamp(getExactTime()),
    $debugInfo->getMemoryUsage('kib', true)
);


ob_end_flush();
