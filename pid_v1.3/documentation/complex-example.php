<?php

include('../pid.class.php');

// for how many time this script should be running
$timeout = 15;

// Calling the pid class without it checking on load if we are running
$pid = new pid(null, null, null, false);

// Manual call to a PID check
$pid->checkPid('','',($timeout * 2));

if (!$pid->already_running) {
    for ($i = 1; $i != $timeout; $i++) {
        echo 'Pausing execution: '.$i.'/'.$timeout.PHP_EOL;
        sleep(1);
    }
} else {
    // Process is already running, that means we must terminate this one
    die('Already running!'.PHP_EOL);
}