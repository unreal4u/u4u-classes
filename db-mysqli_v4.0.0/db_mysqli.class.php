<?php

include(dirname(__FILE__).'/auxiliar_classes.php');

/**
 * Extended MySQLi Parametrized DB Class
 *
 * db_mysqli.class.php, a MySQLi database access wrapper
 * Original idea from Mertol Kasanan, http://www.phpclasses.org/browse/package/5191.html
 * Optimized, tuned and fixed by unreal4u (Camilo Sperberg)
 *
 * @package db_mysqli
 * @version 4.0.0
 * @author Camilo Sperberg, http://unreal4u.com/
 * @author Mertol Kasanan
 * @license BSD License
 * @copyright 2009 - 2013 Camilo Sperberg
 *
 * @method int num_rows() num_rows(...) Returns the number of results from the query
 * @method mixed[] insert_id() insert_id(...) Returns the insert id of the query
 * @method mixed[] query() query(...) Returns false if query could not be executed, resultset otherwise
 * @method boolean begin_transaction() begin_transaction() Returns always true
 * @method boolean end_transaction() end_transaction() Commits the changes to the database. If rollback is needed, this will return false, otherwise true
 */
class db_mysqli {
    /**
     * The version of this class
     * @var string
     */
    private $classVersion = '4.0.0';

    /**
     * Keep an informational array with all executed queries. Defaults to false
     * @var boolean Defaults to false
     */
    public $keepLiveLog = false;

    /**
     * Maintains statistics of the executed queries
     * @var array
     */
    public $dbLiveStats = array();

    /**
     * Maintains statistics exclusively from the errors in SQL
     * @var array
     */
    public $dbErrors = array();

    /**
     * Whether to disable throwing exceptions
     * @var boolean Defaults to false
     */
    public $supressErrors = false;

    /**
     * Whether to throw errors on invalid queries
     * @var boolean Defaults to false
     */
    public $throwQueryExceptions = false;

    /**
     * The number of maximum failed attempts trying to connect to the database
     * @var int Defaults to 10
     */
    public $failedConnectionsTreshold = 10;

    /**
     * Contains the actual DB connection instance
     * @var object
     */
    private $db = null;

    /**
     * Contains the prepared statement
     * @var object
     */
    private $stmt = null;

    /**
     * Internal indicator indicating whether we are connected to the database or not
     * @var boolean
     */
    private $isConnected = false;

    /**
     * Internal statistics collector
     * @var array
     */
    private $stats = array();

    /**
     * Saves the last known error. Can be boolean false or string with error otherwise
     * @var mixed[]
     */
    private $error = false;

    /**
     * Internal indicator to know whether we are in a transaction or not
     * @var boolean
     */
    private $inTransaction = false;

    /**
     * Internal indicator to know whether we should rollback the current transaction or not
     * @var boolean
     */
    private $rollback = false;

    /**
     * Indicator for number of executed queries
     * @var int
     */
    public $executedQueries = 0;

    /**
     * Counter of failed connections to the database
     * @var int
     */
    private $failedConnectionsCount = 0;

    /**
     * When constructed we could enter transaction mode
     *
     * @param boolean $inTransaction Whether to begin a transaction, defaults to false
     */
    public function __construct($inTransaction=false) {
        if (version_compare(PHP_VERSION, '5.1.5', '<')) {
            $this->throwException('Sorry, class only valid for PHP &gt; 5.1.5, please consider upgrading to the latest version', __LINE__);
        }
        if ($inTransaction === true) {
            $this->begin_transaction();
        }
    }

    /**
     * Ends a transaction if needed committing remaining changes
     */
    public function __destruct() {
        if ($this->isConnected === true AND $this->inTransaction === true) {
            $this->end_transaction();
        }
    }

    /**
     * Controls all the calls to the class
     *
     * @param string $method The method to call
     * @param array $arg_array The data, such as the query. Can also by empty
     */
    public function __call($method, array $arg_array) {
        // Sets our own error handler (Defined in config)
        set_error_handler(array('databaseErrorHandler', 'handleError'));

        $this->error = false;
        $logAction   = true;

        // Some custom statistics
        $this->stats = array(
            'time'   => microtime(true),
            'memory' => memory_get_usage(),
        );

        switch ($method) {
            case 'num_rows':
            case 'insert_id':
            case 'query':
                $this->executedQueries++;
                $this->execute_query($arg_array);

                if ($method == 'query') {
                    $result = $this->execute_result_array($arg_array);
                } else {
                    $resultInfo = $this->execute_result_info($arg_array);
                    $result = $resultInfo[$method];
                }
            break;
            case 'begin_transaction':
                $this->connect_to_db();
                if ($this->inTransaction === false) {
                    $this->inTransaction = true;
                    $this->db->autocommit(false);
                }
                $logAction = false;
                $result = true;
            break;
            case 'end_transaction':
                $result = true;
                if ($this->inTransaction === true) {
                    if ($this->rollback === false) {
                        $this->db->commit();
                    } else {
                        $this->db->rollback();
                        $this->rollback = false;
                        $result = false;
                    }
                    $this->db->autocommit(true);
                    $this->inTransaction = false;
                }
                $logAction = false;
            break;
            default:
                $result = 'Method not supported!';
            break;
        }

        if (!empty($logAction)) {
            $this->logStatistics($this->stats, $arg_array, $result, $this->error);
        }

        // Restore whatever error handler we had before calling this class
        restore_error_handler();

        // Finally, return our result
        return $result;
    }

    /**
     * Magic get method. Will always return the number of rows
     *
     * @param string $v Any identifier supported by @link $this->execute_result_info()
     * @return array Returns an array with the requested index (supported by execute_result_info)
     */
    public function __get($v='') {
        $resultInfo = $this->execute_result_info();

        if (!isset($resultInfo[$v])) {
            $resultInfo[$v] = 'Method not supported!';
        }

        return $resultInfo[$v];
    }

    /**
     * Magic toString method. Will return current version of this class
     *
     * @return string
     */
    public function __toString() {
        return 'db_mysqli.class.php v'.$this->classVersion.' by Camilo Sperberg - http://unreal4u.com/';
    }

    /**
     * Will return MySQL version or client version
     *
     * @param boolean $clientInformation Set to true to return client information. Defaults to false
     * @return string Returns a string with the client version
     */
    public function version($clientInformation=false) {
        if (empty($clientInformation)) {
            $result = $this->query('SELECT VERSION()');
            if (!empty($result)) {
                $result = $result[0]['VERSION()'];
            }
        } else {
            $this->connect_to_db();
            $temp = explode(' ', $this->db->client_info);
            $result = $temp[1];
        }

        return $result;
    }

    /**
     * This method will open a connection to the database
     *
     * @return boolean Returns value indicating whether we are connected or not
     */
    private function connect_to_db() {
        if ($this->isConnected === false) {
            if ($this->failedConnectionsCount < $this->failedConnectionsTreshold) {
                try {
                    // Always capture all errors from the singleton connection
                    $db_connect = mysql_connect::getInstance();
                    $this->db = $db_connect->db;
                    $this->isConnected = true;
                } catch (databaseException $e) {
                    // Log the error in our internal error collector and re-throw the exception
                    $this->failedConnectionsCount++;
                    $this->logError(null, 0, 'fatal', $e->getMessage());
                    $this->throwException($e->getMessage(), $e->getLine());
                }
            } else {
                $this->throwException('Too many attempts to connect to database, not trying anymore', __LINE__);
            }
        }

        return $this->isConnected;
    }

    /**
     * Function that checks what type is the data we are trying to insert
     *
     * Supported bind types (http://php.net/manual/en/mysqli-stmt.bind-param.php):
     * i 	corresponding variable has type integer
     * d 	corresponding variable has type double
     * s 	corresponding variable has type string
     * b 	corresponding variable is a blob and will be sent in packets
     *
     * @TODO Support for blob type data (will now go through string type)
     *
     * @param array $arg_array All values that the query will be handling
     * @return array Returns an array with a string of types and another one with the corrected values
     */
    protected function castValues(array $arg_array=null) {
        $types = '';
        if (!empty($arg_array)) {
            foreach ($arg_array as $v) {
                switch ($v) {
                    // All "integer" types
                    case is_null($v):
                    case is_bool($v):
                    case is_int($v):
                        $types .= 'i';
                    break;
                    // Save a float type data
                    case is_float($v):
                        $types .= 'd';
                    break;
                    // Save a string typed data
                    case is_string($v):
                    #default: // @FIXME Disabled until good testing of consequences
                        $types .= 's';
                    break;
                }
            }
        }

        $returnArray = array(
            'types' => $types,
            'arg_array' => $arg_array,
        );

        return $returnArray;
    }

    /**
     * Function that prepares and binds the query
     *
     * @param $arg_array array Contains the binded values
     * @return boolean Whether we could execute the query or not
     */
    private function execute_query(array $arg_array=null) {
        $executeQuery = false;

        if ($this->connect_to_db()) {
            $sqlQuery = array_shift($arg_array);

            $tempArray = $this->castValues($arg_array);
            $types     = $tempArray['types'];
            $arg_array = $tempArray['arg_array'];
            unset($tempArray);

            if (isset($this->stmt)) {
                unset($this->stmt);
            }

            $this->stmt = $this->db->prepare($sqlQuery);
            if (!is_object($this->stmt)) {
                $this->logError($sqlQuery, $this->db->errno, 'fatal', $this->db->error);
            }

            if (isset($arg_array[0])) {
                array_unshift($arg_array, $types);
                if (empty($this->error)) {
                    if (!$executeQuery = @call_user_func_array(array($this->stmt, 'bind_param'), $this->makeValuesReferenced($arg_array))) {
                        $this->logError($sqlQuery, $this->stmt->errno, 'fatal', 'Failed to bind. Do you have equal parameters for all the \'?\'?');
                        $executeQuery = false;
                    }
                }
            } else {
                if (!empty($sqlQuery)) {
                    $executeQuery = true;
                }
            }

            if ($executeQuery AND is_object($this->stmt)) {
                $this->stmt->execute();
                $this->stmt->store_result();
            } elseif (!$this->error) {
                $this->logError($sqlQuery, 0, 'non-fatal', 'General error: Bad query or no query at all');
            }
        }

        return $executeQuery;
    }

    /**
     * Returns data like the number of rows and last insert id
     *
     * @param array $arg_array Contains the binded values
     * @return array Can return affected rows, number of rows or last id inserted.
     */
    private function execute_result_info(array $arg_array=null) {
        $result = array();

        if (!$this->error) {
            if ($this->db->affected_rows > 0)
                $num_rows = $this->db->affected_rows;
            else {
                if (isset($this->db->num_rows)) {
                    $num_rows = $this->db->num_rows;
                } else {
                    $num_rows = 0;
                }
            }
            $result['num_rows'] = $num_rows;
            $result['insert_id'] = $this->db->insert_id;
        }

        return $result;
    }

    /**
     * Establishes the $result array: the data itself
     *
     * @param array $arg_array
     * @return boolean
     */
    private function execute_result_array(array $arg_array) {
        $result = false;

        if (!$this->error) {
            $result = array();
            if ($this->stmt->error) {
                $this->logError(null, $this->stmt->errno, 'fatal', $this->stmt->error);
                return false;
            }

            $result_metadata = $this->stmt->result_metadata();
            if (is_object($result_metadata)) {
                $rows = array();
                $fields = $result_metadata->fetch_fields();
                foreach($fields AS $field) {
                    $rows[$field->name] = null;
                    $dataTypes[$field->name] = $field->type;
                    $params[] =& $rows[$field->name];
                }

                call_user_func_array(array(
                    $this->stmt,
                    'bind_result'
                ), $params);

                while ($this->stmt->fetch()) {
                    foreach ($rows as $key => $val) {
                        $c[$key] = $val;
                        // Fix for boolean data types: hard-detect these and set them explicitely as boolean
                        if ($dataTypes[$key] == 16) {
                            $c[$key] = (bool)$val;
                        }
                    }
                    $result[] = $c;
                }
            } elseif ($this->stmt->errno == 0) {
                $result = true;
            } else {
                $result = $this->stmt->errno;
            }
        }

        return $result;
    }

    /*
     * All functionality that handles with logs, exceptions and other stuff
     */
    /**
     * Throws an exception if these are enabled
     *
     * @param string $msg The string to print within the exception
     * @param int $line The line in which the exception ocurred
     * @throws databaseException
     * @return boolean Returns always false (only when supressErrors is active)
     */
    protected function throwException($msg='', $line=0) {
        if (empty($this->supressErrors)) {
            throw new databaseException($msg, $line, __FILE__);
        }

        return false;
    }

    /**
     * Throws exception on query error
     *
     * @param string $query
     * @param string $mysqlErrorString
     * @param int $mysqlErrno
     * @throws queryException
     */
    protected function throwQueryException($query='', $mysqlErrorString='', $mysqlErrno=0) {
        if (!empty($this->throwQueryExceptions)) {
            throw new queryException($query, $mysqlErrorString, $mysqlErrno);
        }

        return false;
    }

    /**
     * Function that logs all errors
     *
     * @param $query string The query to log
     * @param $errno int The error number to log
     * @param $type string Whether the error is fatal or non-fatal
     * @param $error string The error description
     * @return boolean Always returns TRUE.
     */
    private function logError($query, $errno, $type='non-fatal', $error=null) {
        if (empty($error)) {
            $complete_error = '(not specified)';
        } else if ($type == 'non-fatal') {
            $complete_error = '[NOTICE] ' . $error;
        } else {
            $complete_error = '[ERROR] ' . $error;
            $this->rollback = true;
        }

        $this->dbErrors[] = array(
            'query'        => $query,
            'query_number' => $this->executedQueries,
            'errno'        => $errno,
            'type'         => $type,
            'error'        => $complete_error
        );

        if ($type == 'fatal') {
            $this->error = '[' . $errno . '] ' . $error;
            $this->throwQueryException($query, $error, $errno);
        }

        return true;
    }

    /**
     * Function that executes after each query
     *
     * @param array $stats
     * @param array $arg_array
     * @param array $result
     * @param mixed[] $error
     * @return boolean Always returns TRUE.
     */
    private function logStatistics(array $stats, array $arg_array, $result, $error) {
        $return = false;
        if ($this->keepLiveLog === true) {
            $stats = array(
                'memory' => memory_get_usage() - $stats['memory'],
                'time'   => number_format(microtime(true) - $stats['time'], 5, ',', '.'),
            );

            if ($error == false) {
                $errorString = 'FALSE';
            } else {
                $errorString = 'TRUE';
            }

            $inTransaction = 'FALSE';
            if ($this->inTransaction === true) {
                $inTransaction = 'TRUE';
            }

            $resultInfo = $this->execute_result_info($arg_array);

            $this->dbLiveStats[] = array(
                'query'              => $query,
                'number_results'     => $resultInfo['num_rows'],
                'time'               => $stats['time'] . ' (seg)',
                'memory'             => $stats['memory'] . ' (bytes)',
                'error'              => $error,
                'within_transaction' => $inTransaction,
            );

            $return = true;
        }

        return $return;
    }

    /*
     * Misc functions
     */
    /**
     * Creates an referenced representation of an array
     *
     * @author Hugo Simon http://www.phpclasses.org/discuss/package/5812/thread/5/
     * @param $arr array The array that creates a referenced copy
     * @return array A referenced copy of the original array
     */
    private function makeValuesReferenced($arr) {
        $refs = array();
        foreach ($arr as $key => $value) {
            $refs[$key] = &$arr[$key];
        }
        return $refs;
    }
}

/**
 * Singleton class that holds the connection to MySQL. Do not manually call this class!
 *
 * @author Mertol Kasanan
 * @author Camilo Sperberg
 * @package db_mysqli
 */
class mysql_connect {
    private static $instance;
    private $isConnected = false;
    private $supressErrors = false;

    /**
     * Get a singleton instance
     */
    public static function getInstance() {
        if (!isset(self::$instance)) {
            $c = __CLASS__;
            self::$instance = new $c();
        }

        return self::$instance;
    }

    /**
     * Don't allow cloning
     *
     * @throws Exception If trying to clone
     */
    public function __clone() {
        $this->throwException('We can only declare this class once! Do not try to clone it', __LINE__);
    }

    /**
     * Tries to make the connection
     *
     * @throws Exception If any problem with the database
     */
    public function __construct() {
        $this->db = new mysqli(MYSQL_HOST, MYSQL_USER, MYSQL_PASS, MYSQL_NAME, MYSQL_PORT);
        if (mysqli_connect_error()) {
            $this->throwException(mysqli_connect_error(), __LINE__);
        }

        $this->isConnected = true;
        $this->db->set_charset(DB_CHAR);
    }

    /**
     * Throws an exception if these are enabled
     *
     * @param string $msg The string to print within the exception
     * @throws databaseException
     * @return boolean Returns always false (only when supressErrors is active)
     */
    private function throwException($msg, $line=0) {
        throw new databaseException($msg, $line, __FILE__);

        return false;
    }

    /**
     * Gracefully closes the connection (if there is an open one)
     */
    public function __destruct() {
        if ($this->isConnected === true) {
            $this->db->close();
        }
    }
}
