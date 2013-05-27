<?php

include('stable-versions.php');

$autoLoader = new \u4u\autoLoader();
$autoLoader->includeClass('cacheManager');
$definedConstants = get_defined_constants(true);
$userDefinedConstants = $definedConstants['user'];
unset($definedConstants);

try {
    // Initialize cacheManager without checks (assume apcCache file exists)
    $cacheManager = new \u4u\cacheManagerNoChecks('apc');
    $output = $cacheManager->load('definedU4UClasses', array('u4u-internals', $userDefinedConstants));
} catch (Exception $e) {
    printf('Error: <span style="color:red">%s</span><br />', $e->getMessage());
    $output = false;
}

$bench = $autoLoader->instantiateClass('benchmark');

$bench->beginCounter('outputGeneration');
if ($output === false) {
    $output = '';
    foreach($userDefinedConstants AS $definedConstantKey => $definedConstantValue) {
        $output .= '<p>Go to class: <a href="./'.dirname($definedConstantValue).'/">'.str_replace('u4u\\', '', strtolower($definedConstantKey)).'</a></p>';
    }

    if (!empty($cacheManager)) {
        // Silently fail if APC is not defined
        $cacheManager->save($output, 'definedU4UClasses', array('u4u-internals', $userDefinedConstants), 86400);
    }
}

printf('Total generation time: %f seconds', $bench->endCounter('outputGeneration'));

echo $output;
