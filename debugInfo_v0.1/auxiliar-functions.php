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

if (!function_exists('_m')) {
    function _m($message='') {
        debugInfo::_m($message);
    }
}

if (!function_exists('debug')) {
    function debug($a, $print=true) {
        debugInfo::debug($a, $print);
    }
}

if (!function_exists('debugFile')) {
    function debugFile($message='', $filename='') {
        debugInfo::debugFile($message, $filename);
    }
}

if (!function_exists('exception_error_handler')) {
    function exception_error_handler($errno, $errstr, $errfile, $errline) {
        debugInfo::exception_error_handler($errno, $errstr, $errfile, $errline);
    }
}

if (!function_exists('throw_exceptions')) {
    function throw_exceptions() {
        debugInfo::throw_exceptions();
    }
}

if (!function_exists('redirect')) {
    function redirect($newUrl='', $redirectType=301) {
        debugInfo::redirect($newUrl, $redirectType);
    }
}
