<?php

include('../benchmark.class.php');

function myProcess() {
    for ($i = 0; $i < 200000; $i++) {
        $j = round($i * 44 + 33 * $i - $i * sqrt(10), 6);
    }

    return true;
}

$benchmark = new benchmark();
$benchmark->decimals = 10;

$benchmark->beginCounter('theProcess');
myProcess();
$endTime = $benchmark->endCounter('theProcess');

printf('Total time: %.'.$benchmark->decimals.'f<br />', $endTime);
printf('Total memory: %d<br />', $benchmark->getDiff('theProcess', 'memory'));
printf('Total memory (Peak): %d<br />', $benchmark->getDiff('theProcess', 'peakmemory'));
