<?php
/**
 * Module description
 *
 * @package General
 * @version $Rev$
 * @copyright $Date$
 * @author $Author$
 * @license BSD License. Feel free to use and modify
 */

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
function debug($a, $preString='', $print=true) {
    $output = true;
    /*if (!empty($preString)) {
    	$preString = ' style=\'content:"'.$preString.'"\'';
    }*/

    if (!is_null($a)) {
      if (empty($_SERVER['argv'][0])) {
      	if (is_bool($a)) {
      		$a .= (string)$a;
      	}
      	$output = '<pre'.$preString.'>'.htmlentities(print_r($a,true)).'</pre>';
      }
      else $output = print_r($a,TRUE)."\n";
    }
    else $output = '<pre class="debug">(null)</pre>';
    if ($print === true) echo $output;
    return $output;
}

function _m($message='') {
  	echo $message.'<br />'."\n";
  	return true;
}

function _f($message, $where, $print=false) {
	$message = '['.strftime('%d-%m-%y %T').'] '.$message."\n";
	file_put_contents(print_r($where, true), $message, FILE_APPEND);
	if ($print == true) {
		echo $message;
	}
	return true;
}

function getExactTime() {
	return microtime(true);
}

function getMemoryFootprint($print=false) {
	$return = 'Typ. '.round(memory_get_usage() / 1024).'KiB / Peak: '.round(memory_get_peak_usage() / 1024).'KiB<br />'."\n";
	if (!empty($print)) {
		echo $return;
	}

	return $return;
}

function exception_error_handler($errno, $errstr, $errfile, $errline ) {
    // Do something with severity
    throw new ErrorException($errstr, $errno, $errno, $errfile, $errline);
}

function throw_exceptions() {
    set_error_handler('exception_error_handler');
}