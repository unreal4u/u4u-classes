<?php

include('../benchmark.class.php');

$benchmark = new benchmark();
$benchmark->decimals = 10;

$benchmark->beginCounter('theProcess');

for($i = 0; $i < 200000; $i++) {
    // Random calculation to kill some time
    $j = round($i * 44 + 33 * $i - $i * sqrt(10), 6);
}

$endTime = $benchmark->endCounter('theProcess');
printf('Total time: %.'.$benchmark->decimals.'f<br />', $endTime);
printf('Total memory: %d<br />', $benchmark->memoryDiff('theProcess'));
printf('Total memory (Peak): %d<br />', $benchmark->memoryDiff('theProcess', 'peak'));
