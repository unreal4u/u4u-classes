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
    public static function debug($a, $print=true, $message='') {
        $output = true;

        // If array, add to printing stack
        if (is_array($a)) {
            $copyOriginalArray = $a;
            $a = array();
            foreach($copyOriginalArray AS $index => $value) {
                $a[$index] = self::debug($value, false, $message);
            }
        }

        // Beginning of special cases: if boolean false, indicate so
        if ($a === false) {
            $a = '(boolean) false';
        }

        // Same with empty string
        if ($a === '') {
            $a = "(empty string) ''";
        }

        // Finally, check NULL and mark boolean true also
        switch ($a) {
            case is_bool($a):
                if (is_null($a)) {
                    $a = '(null)';
                } else {
                    $a = '(boolean) true';
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
        throw new ErrorException($errstr, $errno, $errno, $errfile, $errline);
    }

    /**
     * Sets the error handler to throw exceptions only
     */
    public static function throwExceptions() {
        set_error_handler(get_class().'::exceptionErrorHandler');
    }
}

include ('auxiliar-functions.php');
