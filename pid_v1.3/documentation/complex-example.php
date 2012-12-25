<?php

include('../pid.class.php');

// for how many time this script should be running
$timeout = 15;

// Calling the pid class without it checking on load if we are running
$pid = new pid(null, null, null, false);

$pid->checkPid('','',($timeout * 2));

if (!$pid->already_running) {
    for ($i = 1; $i != $timeout; $i++) {
        echo 'Pausing execution: '.$i.'/'.$timeout.PHP_EOL;
        sleep(1);
    }
} else {
    die('Already running!'.PHP_EOL);
}
