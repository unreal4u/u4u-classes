<?php

// To be able to print strange characters
header('Content-Type: text/html; charset=UTF-8');

$languageIds = array('en_US', 'en_UK', 'nl_NL', 'es_ES', 'es_CL');

include('../cacheManager.class.php');

try {
    $cache = new u4u\cacheManager('apc', true);
    if (!$cache->checkIsEnabled()) {
        $cache = new cacheManager('file', '/tmp/myCacheDirectory/');
    }
} catch(u4u\cacheException $e) {
    print('Exception caught! Message: "'.$e->getMessage().'"<br />');
    $cache = new u4u\cacheManagerNoChecks('default');
    print('Going back to "'.$cache->cacheName.'" base class! This equals no cache at all!<br />');
} catch (u4u\versionException $e) {
    die($e->getMessage());
}

// Even if APC module isn't loaded, this call will work (and return false)
$languageMessages = $cache->load('languageMessages', $languageIds);
// If that didn't deliver any results, process it and save it into cache
if (empty($languageMessages)) {
    foreach($languageIds AS $languageId) {
        switch($languageId) {
            case 'en_US':
                $message = 'Good morning';
                break;
            case 'en_UK':
                $message = 'All hail the queen on this beautiful morning';
                break;
            case 'nl_NL':
                $message = 'Goede morgen allemaal';
                break;
            case 'es_ES':
                $message = 'Buenos días tío!';
                break;
            case 'es_CL':
                $message = 'Buenos días a todos! - El matinal de Chile';
                break;
        }

        $languageMessages[$languageId] = $message;
    }

    print('Build cache from scratch');
    // Saving the cache
    $cache->save($languageMessages, 'languageMessages', $languageIds, 120);
} else {
    print('Rescued from cache');
}

print('<br />var_dump of newly created (or rescued) cache below: ');
var_dump($languageMessages);

// Also object are supported
$secondExample = $cache->load('secondExample', array('en_US'));
if (empty($secondExample)) {
    $secondExample = new stdClass();
    $secondExample->data1 = 'Hello world!';
    $secondExample->data2 = false;
    $secondExample->data3 = true;
    $secondExample->data4 = null;
    $secondExample->data5 = array();

    $cache->save($secondExample, 'secondExample', array('en_US'), 120);
    print('Second example build from scratch');
} else {
    print('Second example rescued from cache');
}

print('<br />Second example cache: ');
var_dump($secondExample);

print('<br />Saving 10 random data caches');
for ($i = 0; $i < 10; $i++) {
    $cache->save('number: _'.$i, 'random-data', $i, 120);
}

print('<br /><a href="purge-cache-apc.php">Delete all APC caches</a>');
