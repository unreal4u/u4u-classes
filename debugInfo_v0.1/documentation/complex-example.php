<?php

include('../debugInfo.class.php');

throw_exceptions();

function hello() {
    throw new Exception("Don't type google into google!");
}

try {
    hello();
} catch (Exception $e) {
    debug('hello!');
}