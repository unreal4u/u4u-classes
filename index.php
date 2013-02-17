<?php

include('stable-versions.php');

$definedConstants = get_defined_constants(true);

$autoLoader = new u4u\u4u_autoloader();
// Either way is valid, I prefer second method as it is simpler
//$cacheManager = $autoLoader->instantiateClass('cacheManager', array('apc'));
$cacheManager = new cacheManager('apc');

$output = $cacheManager->load('definedU4UClasses', $definedConstants['user']);
if ($output === false) {
    $output = '';
    foreach($definedConstants['user'] AS $definedConstantKey => $definedConstantValue) {
        $output .= '<p>Go to class: <a href="./'.dirname($definedConstantValue).'/">'.str_replace('u4u\\', '', strtolower($definedConstantKey)).'</a></p>';
    }

    $cacheManager->save($output, 'definedU4UClasses', $definedConstants['user'], 3600);
}

echo $output;
