<?php

/**
 * Non-vital function of this class, I like to keep it because debugging is easier.
 *
 * This function applies htmlentities so you can print whatever you want and
 * display it nicely on-screen. This isn't a part of this class, so if you are
 * paranoid about possible RAM consumption, just delete it.
 * @param mixed $a Whatever you want to print.
 * @param bool $print Whether you should echo inmediatly or only return the string.
 * @return string The formatted what-so-ever you wanted to print.
 */
function debug($a, $print=true) {
    $output = true;

    if (!is_null($a)) {
      if (empty($_SERVER['argv'][0])) {
      	if (is_bool($a)) {
      		$a .= (string)$a;
      	}
      	$output = '<pre class="u4u-debug">'.htmlentities(print_r($a,true)).'</pre>';
      }
      else {
          $output = print_r($a,TRUE)."\n";
      }
    } else {
        $output = '<pre class="u4u-debug">(null)</pre>';
    }
    if ($print === true) {
        echo $output;
    }

    return $output;
}

/**
 * Prints a message with a HTML break (<br />) and a chr(13|10|13+10) break
 * @param string $message
 * @return boolean Returns always true
 */
function _m($message='') {
  	echo $message.'<br />'.PHP_EOL;
  	return true;
}

/**
 * Prints a message in a file
 *
 * @param string $message What we want to print
 * @param string $filename The filename to which we want to print. Can be overwritten with const DEBUGFILE
 * @return boolean Returns true if write was successfull, false otherwise
 */
function debugFile($message='', $filename='') {
    $success = false;

    if (empty($filename)) {
        $filename = 'u4u-log';
    }

    // We can easily write any output to the defined constant
    if (defined('DEBUGFILE')) {
        $filename = DEBUGFILE;
    }

    $filename = sys_get_temp_dir().'/'.$filename;

    $success = file_put_contents($filename, '['.strftime('%d-%m-%Y %T').'] '.print_r($message, true).PHP_EOL, FILE_APPEND);
    // file_put_contents can return number of bytes written or false in case of error, convert to boolean
    if ($success !== false) {
        $success = true;
    }

    return $success;
}

/**
 * Throws an exception
 *
 * @param int $errno
 * @param string $errstr
 * @param string $errfile
 * @param int $errline
 * @throws ErrorException
 */
function exception_error_handler($errno, $errstr, $errfile, $errline ) {
    // @TODO Do something with severity other than to pass just the errno
    throw new ErrorException($errstr, $errno, $errno, $errfile, $errline);
}

/**
 * Sets the error handler to throw exceptions only
 */
function throw_exceptions() {
    set_error_handler('exception_error_handler');
}
