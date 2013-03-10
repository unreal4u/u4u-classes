<?php

/**
 * Common used functions when debugging applications
 *
 * @package debugInfo
 * @author Camilo Sperberg - http://unreal4u.com/
 */
class debugInfo {
    /**
     * Version of this class
     * @var string
     */
    private $version = '0.2';

    /**
     * Magic function
     *
     * @return string
     */
    public function __toString() {
        return 'debugInfo v'.$this->version.' by unreal4u - http://unreal4u.com/';
    }

    /**
     * Prints a message with a HTML break (<br />) and a chr(13|10|13+10) break
     *
     * @param string $message
     * @return boolean Returns always true
     */
    public static function _m($message = '') {
        echo $message.'<br />'.PHP_EOL;
        return true;
    }

    /**
     * Returns the current date and time to be used in the debug functions
     *
     * @return string
     */
    private static function getDateStamp() {
        return '[' . strftime('%d-%m-%Y %T') . '] ';
    }

    /**
     * Makes debugging a variable easier
     *
     * This function applies htmlentities so you can print whatever you want and
     * display it nicely on-screen.
     *
     * @param mixed $a Whatever you want to print.
     * @param bool $print Whether you should echo inmediatly or only return the string.
     * @return string The formatted what-so-ever you wanted to print.
     */
    public static function debug($a, $print = true) {
        $output = true;
        if (!is_null($a)) {
            if (PHP_SAPI != 'cli') {
                if (is_bool($a)) {
                    $a .= (string)$a;
                }
                $output = '<pre class="u4u-debug">' . htmlentities(print_r($a, true)) . '</pre>';
            } else {
                $output = self::getDateStamp() . print_r($a, TRUE) . "\n";
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
     * Prints a message in a file
     *
     * @param string $message What we want to print
     * @param string $filename The filename to which we want to print. Can be overwritten with const DEBUGFILE
     * @return boolean Returns true if write was successfull, false otherwise
     */
    public static function debugFile($message = '', $filename = '') {
        $success = false;
        if (empty($filename)) {
            $filename = 'u4u-log';
        }
        // We can easily write any output to the defined constant
        if (defined('DEBUGFILE')) {
            $filename = DEBUGFILE;
        }
        $filename = sys_get_temp_dir() . '/' . $filename;
        $success = file_put_contents($filename, self::getDateStamp() . print_r($message, true) . PHP_EOL, FILE_APPEND);
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
    public static function exception_error_handler($errno, $errstr, $errfile, $errline) {
        // @TODO Do something with severity other than to pass just the errno
        throw new ErrorException($errstr, $errno, $errno, $errfile, $errline);
    }

    /**
     * Sets the error handler to throw exceptions only
     */
    public static function throw_exceptions() {
        set_error_handler('debugInfo::exception_error_handler');
    }
}

include ('auxiliar-functions.php');
