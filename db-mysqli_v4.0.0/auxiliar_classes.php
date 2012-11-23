<?php
/**
 * This class will throw this type of exceptions
 *
 * @package db_mysqli
 * @author Camilo Sperberg - http://unreal4u.com/
 */
class databaseException extends ErrorException {
    public function __construct($errstr, $errline=0, $errfile='') {
        parent::__construct($errstr, 0, 0, $errfile, $errline);
    }
}

/**
 * This class will handle all errors for us
 *
 * @package db_mysqli
 * @author unreal4u
 */
class databaseErrorHandler {
    public static function handleError($errno, $errstr, $errfile='', $errline=0, $errcontext=array()) {
        throw new DatabaseException($errstr, $errline, $errfile);
    }
}
