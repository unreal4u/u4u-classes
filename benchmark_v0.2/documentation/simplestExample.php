<?php

include('../benchmark.class.php');

function myProcess() {
    for ($i = 0; $i < 200000; $i++) {
        $j = round($i * 44 + 33 * $i - $i * sqrt(10), 6);
    }

    return true;
}

$benchmark = new benchmark('bigProcess');
myProcess();
print('Total time spent in bigProcess: '.$benchmark->endCounter('bigProcess'));
