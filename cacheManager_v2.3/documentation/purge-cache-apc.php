<?php

$languageIds = array('en_US', 'en_UK', 'nl_NL', 'es_ES', 'es_CL');

include('../cacheManager.class.php');

try {
    $cache = new u4u\cacheManager('apc', true);
} catch(u4u\cacheException $e) {
    print('Exception caught! Message: "'.$e->getMessage().'"<br />');
    $cache = new u4u\cacheManagerNoChecks('default');
    print('Going back to "'.$cache->cacheName.'" base class! This equals no cache at all!<br />');
} catch (u4u\versionException $e) {
    die($e->getMessage());
}

try {
    $deletedCount = $cache->purgeIdentifierCache('random-data');
    print('Deleted '.$deletedCount.' caches of type "random-data"');
} catch (u4u\cacheException $e) {
    print('Exception caught! Message: "'.$e->getMessage().'"<br />');
}

$result = $cache->delete('languageMessages', $languageIds);
print('<br />Deleted cache languageMessages');
var_dump($result);

$result = $cache->purgeCache();
print('<br />Purged entire cache');
var_dump($result);

echo '<a href="create-cache-apc.php">Create all APC caches again</a>';
