<?php

include('../debugInfo.class.php');

function myProcess() {
    for ($i = 0; $i < 200000; $i++) {
        $j = round($i * 44 + 33 * $i - $i * sqrt(10), 6);
    }

    return true;
}

$benchmark = new \u4u\debugInfo('bigProcess');
myProcess();
printf('Total time spent in %s: %.6f', 'bigProcess', $benchmark->endCounter('bigProcess', 'time'));
