<?php

namespace u4u;

include('../arrayOperations.class.php');

$arrayOperations = new arrayOperations();
$testArray = array(1, 3, 5, 6, 8, 11);

$result = $arrayOperations->getNextAndPrevious(11, $testArray);
var_dump($testArray);
var_dump($result);

