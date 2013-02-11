<?php

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
