<?php

include('../debugInfo.class.php');

debug('hello!');

debug(str_repeat('-', 80));
$a = new debugInfo();

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
$array = array('<strong>Special <em>HTML tags</em> are automatically escaped!</strong>', '<script type="text/javascript">alert(\'hello!\');</script>');

print('HTML strings with var_dump: ');
var_dump($array);

print('HTML strings with debug function: ');
debug($array);
