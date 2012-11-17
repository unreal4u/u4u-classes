<?php

include('../../functions.php');

$languageIds = array('en_US', 'en_UK', 'nl_NL', 'es_ES', 'es_CL');

include('../cacheManager.class.php');

try {
	$cache = new cacheManager('file', true, '/tmp/myCacheDirectory/');
	$cache->throwExceptions(true);
} catch(cacheException $e) {
	print('Exception caught! Message: "'.$e->getMessage().'"<br />');
} catch (versionException $e) {
    die($e->getMessage());
}

try {
    $deletedCount = $cache->purgeIdentifierCache('random-data');
    if ($deletedCount > 0) {
        print('Deleted '.$deletedCount.' caches of type "random-data"');
    }
} catch (cacheException $e) {
    debug($e->getMessage());
}

$result = $cache->delete('languageMessages', $languageIds);
if ($result === true ) {
    print('<br />Deleted cache languageMessages');
    var_dump($result);
}

$result = $cache->purgeCache();
if ($result === true) {
    print('<br />Purged entire cache');
    var_dump($result);
}

echo '<a href="create-cache-file.php">Create all file caches again</a>';
