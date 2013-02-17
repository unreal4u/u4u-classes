<?php

include('../benchmark.class.php');

function myProcess() {
    $aTmp = array();
    for ($i = 0; $i < 100000; $i++) {
        $aTmp[] = round($i * 44 + 33 * $i - $i * sqrt(10), 6);
    }

    return $aTmp;
}

$benchmark = new benchmark();
$benchmark->decimals = 9;

$benchmark->beginCounter('bigProcess1');
$a = myProcess();
$benchmark->beginCounter('bigProcess2');
$b = myProcess();
$benchmark->endCounter('bigProcess1');

$benchmark->beginCounter('bigProcess3');
$c = myProcess();
$benchmark->endCounter('bigProcess2');
$benchmark->endCounter('bigProcess3');

$benchmark->beginCounter(array('print1', 'print2', 'print3'));
for($i = 1; $i < 4; $i++) {
    printf('The total time for process #%d was %.'.$benchmark->decimals.'f<br />', $i, $benchmark->getDiff('bigProcess'.$i));
}
$benchmark->endCounter('print1');

print('Results should be: 1 very close to 2 and 3 only half of the time of 1<br /><br />');

printf('Memory usage before unsetting: %d Megabytes (%d Mebibytes)<br />', $benchmark->getMemoryUsage('MB'), $benchmark->getMemoryUsage('MiB'));
unset($a);
printf('Memory usage after unsetting $a: %d Megabytes (%d Mebibytes)<br />', $benchmark->getMemoryUsage('MB'), $benchmark->getMemoryUsage('MiB'));
unset($b);
printf('Memory usage after unsetting $b: %d Kilobytes (%d Kibibytes)<br />', $benchmark->getMemoryUsage('KB'), $benchmark->getMemoryUsage('KiB'));
unset($c);
printf('Memory usage after unsetting $c: %d Kibibytes. Peak usage: %d Mebibytes<br />', $benchmark->getMemoryUsage('KiB'), $benchmark->getPeakMemoryUsage('MiB'));

printf('Important: Difference between mebibytes and megabytes explained <a href="http://en.wikipedia.org/wiki/Units_of_information">in this article</a><br />');
$benchmark->endCounter('print2');
$benchmark->endCounter('print3');

printf('Time print1: '.$benchmark->getDiff('print1').'<br />');
printf('Time print2: '.$benchmark->getDiff('print2').'<br />');
printf('Time print3: '.$benchmark->getDiff('print3').'<br />');
