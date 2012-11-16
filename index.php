<?php

include('stable-versions.php');

$definedConstants = get_defined_constants(true);
foreach($definedConstants['user'] AS $definedConstantKey => $definedConstantValue) {
    echo '<a href="./'.dirname($definedConstantValue).'/">'.strtolower($definedConstantKey).'</a><br />';
}
