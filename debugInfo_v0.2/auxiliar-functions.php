<?php

/* try to do something like this:
$rc = new ReflectionClass('debugInfo');
$methods = $rc->getMethods();
foreach($methods AS $method) {
    var_dump($method);
    if (!function_exists($method->name)) {
        function $method->name() {
            $arguments = array_shift(func_get_args());
            debugInfo::$method($arguments);
        }
        print($method->name);

    }
}
unset($rc, $methods, $method);
*/

if (!function_exists('debug')) {
    function debug($a, $print=true, $message='') {
        return u4u\debugInfo::debug($a, $print, $message);
    }
}

if (!function_exists('debugFile')) {
    function debugFile($message='', $filename='', $directory='') {
        return u4u\debugInfo::debugFile($message, $filename, $directory);
    }
}

if (!function_exists('throw_exceptions')) {
    function throwExceptions() {
        return u4u\debugInfo::throwExceptions();
    }
}
