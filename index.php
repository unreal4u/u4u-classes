<?php

include('stable-versions.php');

$output = false;
$autoLoader = new \u4u\autoLoader();
$definedConstants = get_defined_constants(true);

try {
    // Either way is valid, I prefer second method as it is simpler
    #$cacheManager = $autoLoader->instantiateClass('cacheManager', array('apc'));
    $cacheManager = new u4u\cacheManager('apc');
    $output = $cacheManager->load('definedU4UClasses', $definedConstants['user']);
} catch (Exception $e) {
    print('APC not installed? '.$e->getMessage());
}

if ($output === false) {
    $output = '';
    foreach($definedConstants['user'] AS $definedConstantKey => $definedConstantValue) {
        $output .= '<p>Go to class: <a href="./'.dirname($definedConstantValue).'/">'.str_replace('u4u\\', '', strtolower($definedConstantKey)).'</a></p>';
    }

    if (!empty($cacheManager)) {
        // Silently fail
        $cacheManager->save($output, 'definedU4UClasses', $definedConstants['user'], 3600);
    }

}

echo $output;
