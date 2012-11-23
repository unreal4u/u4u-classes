<?php

include('../config.php');

include(dirname(__FILE__).'/..'.CACHEMANAGER);

$cache = new cacheManager(CACHEMANAGER_TYPE);
var_dump($cache->purgeIdentifierCache('db_mysqli_query'));
