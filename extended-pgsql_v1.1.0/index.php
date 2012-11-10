<?php 
/**
 * Index file for Extended PGsql class.
 * 
 * @package General
 * @version $Rev: 9 $
 * @copyright $Date: 2011-07-21 00:23:45 -0400 (Thu, 21 Jul 2011) $
 * @author $Author: unreal4u $
 * @license BSD License
 */

// Non-direct access protection
$proc = TRUE;

// Including our config and class
include('config.php');
include('extended_pgsql.class.php');

echo '<pre>';
$dbLink = new extended_pgsql();

echo 'Short version: '.$dbLink->version(TRUE).'<br />';
echo 'Long version: '.$dbLink->version().'<br />';

// Beginning a transaction, this one should fail to complete and it should do a rollback!
$dbLink->begin_transaction();
$dbLink->query('DROP TABLE IF EXISTS t');
$dbLink->query('CREATE TABLE t(i serial primary key);');
$dbLink->query('INSERT INTO t VALUES (?)',1);
$dbLink->query('INSERT INTO t VALUES (?)',1);
$dbLink->end_transaction();

// Beginning a second, totally different transaction. This one should be ok.
$dbLink->begin_transaction();
$dbLink->query('DROP TABLE IF EXISTS x');
$dbLink->query('CREATE TABLE x(i serial primary key)');
// If we want the last insert id, we should use native PostGreSQL function "RETURNING"
$id = $dbLink->insert_id('INSERT INTO x VALUES (?) RETURNING i',1);
$dbLink->query('INSERT INTO x VALUES (?)',2);
$dbLink->end_transaction();

// If machines are still machines, this should return an error (due to the fail on the first transaction)
$aRes = $dbLink->query('SELECT * FROM t');

// And this should be ok. Note we enabled the cache.
// NOTE: First time this runs, we generate the cache file.
//       Next time within DB_CACHE_EXPIRE's time, we should read THAT file instead of doing the actual query.
$dbLink->cache_query = TRUE;
$bRes = $dbLink->query('SELECT * FROM x');

// Let's see what is in our first query:
echo '<br />t is:<br />';
var_dump($aRes);
// BOOLEAN FALSE because there was an error in the query ()

// Let's see what we have in our second query:
echo '<br />x is:<br />';
var_dump($bRes);

// Do we have any errors from the previous queries?
echo '<br />Errors:<br />';
print_r($dbLink->errors);

// The other array, LiveStats which contains a lot of useful debug info
print_r($dbLink->LiveStats);

// Destroying the object.
unset($dbLink);
echo '</pre>';
