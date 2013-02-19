<?php

#namespace u4u\debugInfo;

/* try to do something like this:
$rc = new ReflectionClass('debugInfo');
$methods = $rc->getMethods();
foreach($methods AS $method) {
    function $method->name() {
        $arguments = array_shift(func_get_args());
        debugInfo::$method($arguments);
    }
}
unset($rc, $methods, $method);
*/

function _m($message='') {
    u4u\debugInfo::_m($message);
}

function debug($a, $print=true) {
    u4u\debugInfo::debug($a, $print);
}

function debugFile($message='', $filename='') {
    u4u\debugInfo::debugFile($message, $filename);
}

function exception_error_handler($errno, $errstr, $errfile, $errline) {
    u4u\debugInfo::exception_error_handler($errno, $errstr, $errfile, $errline);
}

function throw_exceptions() {
    u4u\debugInfo::throw_exceptions();
}

function redirect($newUrl='', $redirectType=301) {
    u4u\debugInfo::redirect($newUrl, $redirectType);
}
