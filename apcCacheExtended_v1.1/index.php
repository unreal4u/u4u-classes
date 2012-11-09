<?php

set_time_limit(0);
// To be able to print strange characters
header('Content-Type: text/html; charset=UTF-8');

$languageIds = array('en_US', 'en_UK', 'nl_NL', 'es_ES', 'es_CL');

include('apcCacheExtended.class.php');

try {
	$cache = new apcCacheExtended(true);
	// Call this method or execute any other public method to get the same result
	$cache->checkApcEnabled();
} catch(Exception $e) {
	print('Exception caught! Message: "'.$e->getMessage().'"<br />');
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
	$cache->save($languageMessages, 'languageMessages', $languageIds, 3);
} else {
	print('Rescued from cache');
}

var_dump($languageMessages);
