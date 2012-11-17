<?php

include('../../functions.php');

$languageIds = array('en_US', 'en_UK', 'nl_NL', 'es_ES', 'es_CL');

include('../cacheManager.class.php');

try {
	$cache = new cacheManagerClass('apc', true);
} catch(CacheException $e) {
	print('Exception caught! Message: "'.$e->getMessage().'"<br />');
}

try {
    $deletedCount = $cache->purgeIdentifierCache('random-data');
    print('Deleted '.$deletedCount.' caches of type "random-data"');
} catch (CacheException $e) {
    print('Exception caught! Message: "'.$e->getMessage().'"<br />');
}

$result = $cache->delete('languageMessages', $languageIds);
print('<br />Deleted cache languageMessages');
var_dump($result);

$result = $cache->purgeCache();
print('<br />Purged entire cache');
var_dump($result);

echo '<a href="create-cache-apc.php">Create all APC caches again</a>';
