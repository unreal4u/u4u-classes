<?php

namespace u4u;

/**
 * Common used functions when debugging applications
 *
 * @package debugInfo
 * @author Camilo Sperberg - http://unreal4u.com/
 * @version 0.3
 */
class debugInfo {
    /**
     * Version of this class
     * @var string
     */
    private $version = '0.3';

    /**
     * The format of the timestamp that will be printed, based on strftime
     * @link http://php.net/manual/en/function.strftime.php
     * @var string
     */
    public static $timeFormat = '%F %T';

    /**
     * Magic function
     *
     * @return string
     */
    public function __toString() {
        if (PHP_SAPI == 'cli') {
            $eol = PHP_EOL;
        } else {
            $eol = '<br />';
        }
        return 'debugInfo v'.$this->version.' by unreal4u - http://unreal4u.com/'.$eol;
    }

    /**
     * Returns the current date and time to be used in the debug functions
     *
     * @see self::$timeFormat
     * @return string
     */
    private static function getDateStamp() {
        return '[' . strftime(self::$timeFormat) . '] ';
    }

    /**
     * Makes debugging a variable easier
     *
     * This function applies htmlentities so you can print whatever you want and
     * display it nicely on-screen.
     *
     * @param mixed $a Whatever you want to print
     * @param bool $print Whether you should echo inmediatly or only return the string
     * @param string $message The message to print before the variable printing
     * @return string The formatted what-so-ever you wanted to print
     */
    public static function debug($a=null, $print=true, $message='') {
        $output = true;
        $type = gettype($a);

        // Check what action to take depending on type of data
        switch($type) {
            // Overwrite variable with string to indicate clearly what type of data we're dealing with
            case 'NULL':
                $a = '(null)';
                break;
            // Overwrite variable with string to indicate clearly what type of data we're dealing with
            case 'boolean':
                if ($a === true) {
                    $a = '('.$type.') true';
                } else {
                    $a = '('.$type.') false';
                }
                break;
            // Indicate also empty string
            case 'string':
                if ($a === '') {
                    $a = "(empty string) ''";
                }
                break;
            // In case we're printing out an array, check out what for types each component of that array is
            case 'array':
                $copyOriginalArray = $a;
                $a = array();
                foreach($copyOriginalArray AS $index => $value) {
                    $a[$index] = self::debug($value, false, $message);
                }
                break;
        }

        if (PHP_SAPI != 'cli') {
            // If outputting to browser, escape the contents
            $output = $message . htmlentities(print_r($a, true));
        } else {
            // If in CLI mode, always add current timestamp and don't escape htmlentities
            $output = self::getDateStamp() . $message . print_r($a, true) . PHP_EOL;
        }

        if ($print === true) {
            // If we aren't working in CLI mode, add <pre> and custom class name
            if (PHP_SAPI != 'cli') {
                $output = '<pre class="u4u-debug">' . $output . '</pre>';
            }
            echo $output;
        }

        // Return the output
        return $output;
    }

    /**
     * This function will debug through FirePHP
     *
     * This function will assume that PEAR is installed and up and running correctly. The steps to install FirePHP
     * through PEAR are:
     * <code>
     * pear channel-discover pear.firephp.org
     * pear install firephp/FirePHPCore
     * </code>
     * After that, you can include the FirePHP library (already done in the class) and finally print to it.
     *
     * @throws Exception Will throw an exception if FirePHP class isn't found
     * @throws Exception Will throw an exception if headers are already sent
     * @param mixed $a Whatever we want to print
     * @param boolean $print Whether to print immediatly or not. Ignored for this function
     * @param string $message What message to append to
     */
    public static function debugFirePHP($a=null, $print=false, $message='') {
        if (!headers_sent()) {
            include_once('FirePHPCore/FirePHP.class.php');

            if (!class_exists('FirePHP')) {
                throw new \Exception('FirePHP is not installed or its main file isn\'t being included');
            }

            $firePHP = \FirePHP::getInstance(true);
            $firePHP->log($a, $message);
        } else {
            throw new \Exception('Headers already sent, can not send FirePHP\'s messages');
        }

        return self::debug($a, false, $message);
    }

    /**
     * Prints a message in a file
     *
     * Don't use this function for intensive file writing because for each message it prints, it will use some expensive
     * system calls
     *
     * @param string $message What we want to print
     * @param string $filename The filename to which we want to print
     * @param string $directory The directory in which we want to save the file. Defaults to sys_get_temp_dir()
     * @return boolean Returns true if write was successfull, false otherwise
     */
    public static function debugFile($message='', $filename='', $directory='') {
        $success = false;
        if (empty($filename)) {
            $filename = 'u4u-log';
        }

        if (empty($directory)) {
            // Trailing slash always needed, check http://www.php.net/manual/en/function.sys-get-temp-dir.php#80690
            $directory = realpath(sys_get_temp_dir()) . '/';
        }

        $filename = $directory . $filename;
        if (is_writable($filename)) {
            $success = file_put_contents(
                $filename, // Where to write
                self::debug($message, false) . PHP_EOL, // Write the message
                FILE_APPEND // Writing mode
            );
        }

        // file_put_contents can return number of bytes written or false in case of error, convert to boolean
        if ($success !== false) {
            $success = true;
        }

        return $success;
    }

    /**
     * Throws an ErrorException
     *
     * @param int $errno
     * @param string $errstr
     * @param string $errfile
     * @param int $errline
     * @throws ErrorException
     */
    public static function exceptionErrorHandler($errno=null, $errstr=null, $errfile=null, $errline=null) {
        // @TODO Do something with severity other than to pass just the errno
        throw new \ErrorException($errstr, $errno, $errno, $errfile, $errline);
    }

    /**
     * Sets the error handler to throw exceptions only
     */
    public static function throwExceptions() {
        set_error_handler(get_class().'::exceptionErrorHandler');
    }
}

include ('auxiliar-functions.php');
