<?php
// Including our config file.
include('config.php');
// Including the class.
include('db_mysqli.class.php');


// Let's make a new object with our class.
$dbLink = new db_mysqli();
// And run a small and simple query
$aRes = $dbLink->query('SELECT * FROM t LIMIT 2');
echo '<pre>';
echo '--------------- FIRST QUERY ------------------'."\n";
// Printing our data.
print_r($aRes);
echo '</pre><strong>Query N° 1 executed (Simple query)</strong><hr><pre>';





// Now let's do a more complicated query, with cache enabled:
$dbLink->cache_query = TRUE;
// We will first enable the cache for _THIS_ query.
$aRes = $dbLink->query('SELECT a.id,a.nombre AS name1,a.nombre2 AS name2,b.nombre AS name3,b.nombre2 AS name4 FROM t100000 AS a,t500000 AS b WHERE a.id=b.id AND a.id=? LIMIT ?',29137,3);
// As you can see, we are parametrizing our query
echo "\n\n".'--------------- SECOND QUERY ------------------'."\n";
if ($dbLink->num_rows > 0) {
  echo 'Number of rows: '.$dbLink->num_rows."\n";
  foreach($aRes AS $a) {
    echo 'id = '.$a['id'].'; name1 = '.$a['name1'].'; name2 = '.$a['name2'].';'."\n".
         '            name3 = '.$a['name3'].'; name4 = '.$a['name4']."\n";
  }
}
// Printing our data, note the use of num_rows and the foreach.
echo '</pre><strong>Query N° 2 executed (Query with parameters and stored to cache)</strong><hr><pre>';





// Now, we will execute an invalid query:
$aRes = $dbLink->query('SELECT * FROM t100000,t500000 WHERE id = ? LIMIT ?',291,10);
echo "\n\n".'--------------- THIRD QUERY ------------------'."\n";
print_r($aRes);
// Printing our data.
echo '(no results)';
echo '</pre><strong>Query N° 3 executed (Invalid query: id is ambiguous)</strong><hr><pre>';





// And another invalid query:
$aRes = $dbLink->query();
echo "\n\n".'--------------- FOURTH QUERY ------------------'."\n";
print_r($aRes);
// Printing our data.
echo '(no results)';
echo '</pre><strong>Query N° 4 executed (Invalid query: empty query)</strong><hr><pre>';





// And of course can we execute other type of queries apart from selects:
$aRes = $dbLink->query('INSERT INTO tcustom(nombre) VALUES (?)','asdf');
echo 'Last Insert ID = '.$dbLink->insert_id."\n";// Last insert id
echo 'Number of inserted rows = '.$dbLink->num_rows."\n"; // Number of inserted rows

$dbLink->cache_query = TRUE;
// Enabling the cache will have no effect on this query since there are no results to display.
$aRes = $dbLink->query('INSERT INTO tcustom(nombre) VALUES (?)','queryfvg');
echo 'Last Insert ID = '.$dbLink->insert_id."\n";// Last insert id
echo 'Number of inserted rows = '.$dbLink->num_rows."\n"; // Number of inserted rows

$aRes = $dbLink->query('UPDATE tcustom SET nombre = ? WHERE id = ?','wasd',1);
echo 'Last Insert ID = '.$dbLink->insert_id."\n";// This will be 0, because we are not inserting a new record
echo 'Number of affected rows = '.$dbLink->num_rows."\n"; // Number of affected rows

$dbLink->cache_query = TRUE;
// Enabling the cache will have no effect on this query since there are no results to display.
$aRes = $dbLink->query('UPDATE tcustom SET nombre = ? WHERE id = ?','iops',55313556);
echo 'Number of affected rows = '.$dbLink->num_rows."\n"; // Record N° 55313556 doesn't exist, but it doesn't raise an error because query is valid for MySQL.





// BUT! Oh no, we have a few invalid queries, let's take a look at our first auxiliar array:
if (count($dbLink->dbErrors) > 0) {
  $num_errors = count($dbLink->dbErrors);
  echo '</pre><h3>All the queries executed, but we have some errors!</h3><pre>';
  print_r($dbLink->dbErrors);
  echo '</pre><hr><pre>';
}
else $num_errors = 0;

// Now we unset our results array, so that you can do other things with your memory.
unset($aRes);

// Finally, let's print out the LiveStats
echo '</pre><h3>The LiveStats<sup>&reg;</sup> of the queries are: </h3><pre>';
print_r($dbLink->dbLiveStats);
?>
Executed <strong><?php
  echo count($dbLink->dbLiveStats); ?></strong> queries which presented a total of <strong><?php
  echo $num_errors; ?></strong> errors.<hr><?php
// Last but not least, we'll destroy the object, letting the XML logger do its thing
unset($dbLink); // This is optional, because when the script ends, PHP will execute this as well, but... be kind :)

// Some final statistics
echo '</pre><hr><h3>General statistics (NOT a part of this class):</h3>';
if (MEMORY == TRUE) {
  printf(('Typical memory: <strong>%s</strong>KiB'),round(memory_get_usage() / 1024));
  if (version_compare(PHP_VERSION, '5.2.0', '>')) printf((' / Total: <strong>%s</strong>KiB'),round(memory_get_peak_usage() / 1024));
}
?><br>
Total time: <strong><?php
  echo number_format((time()+microtime()) - $q,7,'.',',');
?></strong> seconds<br>