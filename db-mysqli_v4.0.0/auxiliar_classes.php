<?php
/**
 * This class will throw this type of exceptions
 *
 * @package db_mysqli
 * @author Camilo Sperberg - http://unreal4u.com/
 */
class databaseException extends Exception {}

/**
 * This class will handle all errors for us
 *
 * @package db_mysqli
 * @author unreal4u
 */
class databaseErrorHandler {
    public static function handleError($errno, $errstr, $errfile='', $errline=0, $errcontext=array()) {
        throw new DatabaseException($errstr, $errno);
    }
}
