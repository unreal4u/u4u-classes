<?php

function myProcess() {
    for ($i = 0; $i < 200000; $i++) {
        $j = round($i * 44 + 33 * $i - $i * sqrt(10), 6);
    }

    return true;
}

include('../debugInfo.class.php');

debug('hello!');

debug(str_repeat('-', 80));
$a = new u4u\debugInfo();

print('object printing with var_dump: ');
var_dump($a);

print('object printing with debug function: ');
debug($a);

debug(str_repeat('-', 80));
$array = array(1, 3 => array('a', 'b'), 'another' => 'c');

print('array printing with var_dump: ');
var_dump($array);

print('array printing with debug function: ');
debug($array);

debug(str_repeat('-', 80));
$array = array(
    '<strong>Special <em>HTML tags</em> are automatically escaped!</strong>',
    'Even if <em>they are incomplete!',
    '<script type="text/javascript">alert(\'hello!\');</script>',
);

print('HTML strings with var_dump: ');
var_dump($array);

print('HTML strings with debug function: ');
debug($array);

printf('<a href="%s">Click here</a> to go to complex examples<br />', 'complex-example.php');


$a->decimals = 10;
$a->beginCounter('theProcess');
myProcess();
$endTime = $a->endCounter('theProcess');

printf('Total time: %.'.$a->decimals.'f<br />', $endTime);
printf('Total memory: %d<br />', $a->getDiff('theProcess', 'memory'));
printf('Total memory (Peak): %d<br />', $a->getDiff('theProcess', 'peakmemory'));

print($a::convertTimestamp());
