<?php
include('pid.class.php');

// This is used to simulate the script will be running for 30 seconds.
$howmany = 30;

$pid = new pid();
if ($pid->already_running) die('Already running!'."\n");

for ($i = 0; $i < $howmany; $i++) {
	echo $i." of ".$howmany."\n";
	sleep(1);
}
