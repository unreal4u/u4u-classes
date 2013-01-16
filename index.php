<?php

include('stable-versions.php');

$definedConstants = get_defined_constants(true);
foreach($definedConstants['user'] AS $definedConstantKey => $definedConstantValue) {
    echo '<p>Go to class: <a href="./'.dirname($definedConstantValue).'/">'.str_replace('u4u_', '', strtolower($definedConstantKey)).'</a></p>';
}
