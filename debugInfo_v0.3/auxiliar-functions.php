<?php

use \u4u;

/**
 * Implements debugInfo::debug as a function
 *
 * @see debugInfo::debug()
 * @param unknown $a
 * @param string $print
 * @param string $message
 * @return Ambigous <string, boolean>
 */
function debug($a, $print=true, $message='') {
    return u4u\debugInfo::debug($a, $print, $message);
}

/**
 * Implements debugInfo::debugFile as a function
 *
 * @see debugInfo::debugFile()
 * @param string $message
 * @param string $filename
 * @param string $directory
 * @return Ambigous <boolean, number>
 */
function debugFile($message='', $filename='', $directory='') {
    return u4u\debugInfo::debugFile($message, $filename, $directory);
}

/**
 * Implements debugInfo::debugFirePHP as a function
 *
 * @see debugInfo::debugFirePHP
 * @param unknown $a
 * @param string $print
 * @param string $message
 * @return Ambigous <string, boolean>
 */
function debugFirePHP($a, $print=false, $message='') {
    return u4u\debugInfo::debugFirePHP($a, $print, $message);
}

/**
 * Implements debugInfo::throwExceptions as a function
 *
 * @see debugInfo::throwExceptions()
 */
function throwExceptions() {
    return u4u\debugInfo::throwExceptions();
}
