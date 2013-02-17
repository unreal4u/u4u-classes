<?php
/**
 * A very simple usage example
 *
 * @package db_mysqli
 * @author Camilo Sperberg - http://unreal4u.com/
 */
include('../config.php');
include('../db_mysqli.class.php');

$db = new db_mysqli();
$db->supressErrors = true;
$db->keepLiveLog = true;
echo $db->version();

$db->query('SELECT * FROM a');

debug($db->dbLiveStats);

