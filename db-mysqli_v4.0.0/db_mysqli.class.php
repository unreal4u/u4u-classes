<?php

include(dirname(__FILE__).'/auxiliar_classes.php');
include(dirname(__FILE__).CACHEMANAGER);

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
 */
class db_mysqli {
    /**
     * Whether to cache the following query or not
     *
     * @var boolean $cache_query Defaults to FALSE
     */
    public $cache_query = false;

    /**
     * Keep an informational array with all executed queries. Defaults to false
     *
     * @var boolean $keepLiveLog
     */
    public $keepLiveLog = false;
    /**
     * Maintains statistics of the executed queries
     *
     * @var array $dbLiveStats
     */
    public $dbLiveStats = array();

    /**
     * Maintains statistics exclusively from the errors in SQL
     *
     * @var array $dbErrors
     */
    public $dbErrors = array();

    /**
     * Contains the actual DB connection instance
     *
     * @var object $db
     */
    private $db = null;
    private $stmt = null;
    private $isConnected = false;
    private $stats = array();
    private $error = false;
    private $xmllog = array();
    private $cache_recreate = false;
    private $load_from_cache = false;
    private $rows_from_cache = -1;
    private $num_rows = 0;
    private $in_transaction = false;
    private $rollback = false;

    private $cache;
    private $cacheEnabled = false;

    public $createRuntimeLog = false;

    /**
     * Whether to disable throwing exceptions
     *
     * @var boolean $supressErrors Defaults to false
     */
    public $supressErrors = false;

    /**
     * The default expiration time of the cache
     *
     * @var int $cacheExpirationTime Defaults to 60 seconds
     */
    public $cacheExpirationTime = 60;

    /**
     * When constructed we could enter transaction mode
     *
     * @param boolean $in_transaction Whether to begin a transaction, defaults to false
     */
    public function __construct($in_transaction=false) {
        if (version_compare(PHP_VERSION, '5.1.5', '<')) {
            $this->throwException('Sorry, class only valid for PHP &gt; 5.1.5, please consider upgrading to the latest version', __LINE__);
        }
        if ($in_transaction === true) {
            $this->begin_transaction();
        }

        $this->setCacheClass();
    }

    /**
     * Ends a transaction if needed and logs (when it should) everything into a XML file.
     */
    public function __destruct() {
        if ($this->in_transaction === true and $this->isConnected === true) {
            $this->end_transaction();
        }
        if (DB_LOG_XML === true) {
            $this->db_log($this->xmllog);
        }
    }

    /**
     * Controls all the calls to the class
     *
     * @param string $method The method to call
     * @param array $arg_array The data, such as the query. Can also by empty
     */
    public function __call($method, $arg_array) {
        // Sets our own error handler (Defined in config)
        set_error_handler(array('databaseErrorHandler', 'handleError'));

        // Some custom statistics
        $this->stats = array(
            'time'   => microtime(true),
            'memory' => memory_get_usage(),
        );

        $this->error = false;
        $this->load_from_cache = false;

        if ($this->cache_query === false) {
            $this->cache_recreate = false;
        } elseif (!$this->valid_cache($arg_array)) {
            $this->cache_recreate = true;
        } else {
            $this->cache_recreate = false;
            $this->load_from_cache = true;
        }
        $logAction = true;

        switch ($method) {
            case 'num_rows':
                if (!is_null($arg_array)) {
                    $this->execute_query($arg_array);
                    $num_rows = $this->execute_result_info($arg_array);
                    $result = $num_rows['num_rows'];
                }
            break;
            case 'insert_id':
                if (!is_null($arg_array)) {
                    $this->execute_query($arg_array);
                    $num_rows = $this->execute_result_info();
                    $result = $num_rows['insert_id'];
                }
            break;
            case 'query':
                $this->execute_query($arg_array);
                if (!$result = $this->execute_result_array($arg_array)) {
                    $result = false;
                }
            break;
            case 'begin_transaction':
                $this->connect_to_db();
                if ($this->in_transaction === false) {
                    $this->in_transaction = true;
                    $this->db->autocommit(false);
                }
                $logAction = false;
                $result = true;
            break;
            case 'end_transaction':
                $result = true;
                if ($this->in_transaction) {
                    if ($this->rollback === false) {
                        $this->db->commit();
                    } else {
                        $this->db->rollback();
                        $this->rollback = false;
                        $result = false;
                    }
                    $this->db->autocommit(true);
                    $this->in_transaction = false;
                }
                $logAction = false;
            break;
            case 'version':
                $result = 'Not connected yet';
                $this->connect_to_db();
                if (!empty($arg_array[0])) {
                    $temp = explode(' ', $this->db->client_info);
                    $result = $temp[1];
                } else {
                    $result = $this->db->client_info;
                }
            break;
            default:
                $result = 'Method not supported!';
            break;
        }

        if (!empty($logAction)) {
            $this->logMe($this->stats, $arg_array, $result, $this->error, $this->load_from_cache);
        }

        // Restore whatever error handler we had before calling this class
        restore_error_handler();

        // Finally, return our result
        return $result;
    }

    /**
     * Magic get method. Will always return the number of rows
     *
     * @param mixed $v
     */
    public function __get($v) {
        $num_rows = $this->execute_result_info();
        if (!isset($num_rows[$v])) {
            $num_rows[$v] = 'Method not supported!';
        }

        return $num_rows[$v];
    }

    /**
     * This method will open a connection to the database
     */
    private function connect_to_db() {
        if ($this->isConnected === false) {
            try {
                // Always capture all errors from the singleton connection
                $db_connect = mysql_connect::getInstance();
                $this->db = $db_connect->db;
                $this->isConnected = true;
            } catch (databaseException $e) {
                // Log the error in our internal error collector and re-throw the exception
                $this->logError(null, 0, 'fatal', $e->getMessage());
                $this->throwException($e->getMessage(), $e->getLine());
            }
        }
        return $this->isConnected;
    }

    /**
     * Function that prepares and binds the query, or does nothing if an valid cache file is found.
     *
     * @param $arg_array array Contains the binded values
     * @return boolean Whether we could execute the query or not
     */
    protected function execute_query($arg_array = NULL) {
        $execute_query = false;
        if ($this->cache_query === false or $this->cache_recreate === true) {
            if ($this->connect_to_db()) {
                $sql_query = array_shift($arg_array);
                $types = '';
                foreach ($arg_array as $v) {
                    switch ($v) {
                        // note: boolean(false) is also considered by this function as null
                        case is_null($v):
                            $types .= 'i';
                            $v = null;
                        break;
                        // Cast to int type and save it that way
                        case is_bool($v):
                            $types .= 'i';
                            $v = (int)(bool)$v;
                        break;
                        // Save an int
                        case is_int($v):
                            $types .= 'i';
                        break;
                        // Save a float type data
                        case is_float($v):
                            $types .= 'd';
                        break;
                        // Save a string typed data
                        case is_string($v):
                            $types .= 's';
                        break;
                        /*
                         * Objects or arrays could also be saved as a string by serializing them, however, that is
                         * beyond the scope of this class and should be implemented by an extending class that
                         * overwrites this method.
                         * If you want, uncomment the following lines but keep in mind that you will have to do the
                         * same everytime an update for this class is released
                         */
                        #default:
                        #    $types .= 's';
                        #    $v = serialize($v);
                        #break;
                    }
                }
                if (isset($this->stmt)) {
                    unset($this->stmt);
                }
                $this->stmt = $this->db->prepare($sql_query);
                if (!is_object($this->stmt)) {
                    $this->logError($sql_query, $this->db->errno, 'fatal', $this->db->error);
                }
                if (isset($arg_array[0])) {
                    array_unshift($arg_array, $types);
                    if (!$this->error) {
                        if (!$execute_query = @call_user_func_array(array(
                            $this->stmt,
                            'bind_param'
                        ), $this->makeValuesReferenced($arg_array))) {
                            $this->logError($sql_query, $this->stmt->errno, 'fatal', 'Failed to bind. Do you have equal parameters for all the \'?\'?');
                            $execute_query = false;
                        }
                    } else {
                        $execute_query = false;
                    }
                } else {
                    $execute_query = true;
                    if (empty($sql_query)) {
                        $execute_query = false;
                    }
                }
                if ($execute_query and is_object($this->stmt)) {
                    $this->stmt->execute();
                    $this->stmt->store_result();
                } elseif (!$this->error) {
                    $this->logError($sql_query, 0, 'non-fatal', 'General error: Bad query or no query at all');
                }
            }
        }
        return $execute_query;
    }

    /**
     * Returns data like the number of rows or the last insert id. If a valid cache file is found, it rescues the number
     * of rows from the XML file.
     *
     * @param $arg_array array Contains the binded values
     * @return array $result Can return affected rows, number of rows or last id inserted.
     */
    private function execute_result_info($arg_array = NULL) {
        if (!$this->error) {
            if ($this->cache_query === false and $this->load_from_cache === false) {
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
            } else {
                $result['num_rows'] = $this->get_cache_meta();
            }
            return $result;
        }
    }

    /**
     * Establishes the $result array: the data itself. If we have a valid cache file, it rescues it from there.
     */
    private function execute_result_array($arg_array) {
        $result = 0;
        if (!$this->error) {
            $result = array();
            if ($this->cache_query === false or $this->cache_recreate === true) {
                if ($this->stmt->error) {
                    $this->logError(NULL, $this->stmt->errno, 'fatal', $this->stmt->error);
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
                    if ($this->cache_recreate === true and !empty($result)) {
                        $this->create_cache($arg_array, $result);
                    }
                    if (!isset($result)) {
                        $result = 0;
                    }
                } elseif ($this->stmt->errno == 0) {
                    $result = true;
                } else {
                    $result = $this->stmt->errno;
                }
            } else {
                $result = $this->get_cache($arg_array);
            }
        }
        return $result;
    }

    /*
     * Cache functionality, implements mostly cache module
     */
    private function setCacheClass() {
        if (CACHEMANAGER_TYPE != '') {
            try {
                $this->cache = new cacheManager(CACHEMANAGER_TYPE);
                $this->cacheEnabled = true;
            } catch (cacheException $e) {
                $this->throwException($e->getMessage(), __LINE__);
            }
        }


        return true;
    }

    #private function get_cache($type='query', $arg_array=null) {
    #    $cache = new cacheManager(CACHEMANAGER_TYPE);
    #}

    /**
     * Function that establish the cache filename
     *
     * @param $arg_array array Used to create the filename
     * @return string Returns the filename of the cache file
     */
    private function filename($arg_array = NULL) {
        $filename = '0';
        if (!empty($arg_array)) {
            foreach ($arg_array as $a) {
                $filename .= $a;
            }
            $filename = 'db_' . md5($filename);
        }
        return DB_CACHE_LOCATION . $filename . '.xml';
    }

    /**
     * Checks whether our cache file is still valid.
     *
     * @param $arg_array array Used to create the filename
     * @return boolean TRUE if cache file is still valid, FALSE otherwise
     */
    private function valid_cache($arg_array = NULL) {
        $filename = '';
        $is_valid = false;
        if (is_array($arg_array)) {
            $filename = $this->filename($arg_array);
            if (file_exists($filename)) {
                if (filemtime($filename) > time() - DB_CACHE_EXPIRE) {
                    $is_valid = true;
                } else if (!@unlink($filename)) {
                    $this->logError($arg_array[0], 0, 'non-fatal', 'Couldn\'t delete old cache file! Check permissions');
                }
            }
        }
        return $is_valid;
    }

    /**
     * Function that creates a valid XML file. I'm not using SimpleXML here because of speed. Using SimpleXML, with
     * 100.000 records, it takes 26 seconds, this way, only 1 second (on my test server)
     *
     * @TODO Don't return TRUE when cache creation fails
     * @param $arg_array array Used to create the filename
     * @param $result array Used to replicate the result in the XML file.
     * @return boolean Returns always TRUE.
     */
    private function create_cache($arg_array, $result) {
        $i = 0;
        $xml = '';
        foreach ($result as $r) {
            $xml .= '<r' . $i . '>';
            foreach ($r as $key => $value) {
                $xml .= '<' . $key . '>' . $value . '</' . $key . '>';
            }
            $xml .= '</r' . $i . '>';
            $i++;
        }
        if (!@file_put_contents($this->filename($arg_array), '<?xml version="1.0" encoding="UTF-8"?>' . "\n" . '<db>' . $xml . '</db>')) {
            $this->logError($arg_array[0], 0, 'non-fatal', 'Couldn\'t create cache file!');
            $this->cache_query = false;
            $this->cache_recreate = false;
        }
        unset($xml);
        return true;
    }

    /**
     * Returns number of rows in the XML.
     *
     * @return int Number of rows
     */
    private function get_cache_meta() {
        return $this->rows_from_cache;
    }

    /**
     * Parses and returns the XML in an array.
     *
     * @param $arg_array array Used to create the filename
     * @return array The result set rescued from the cache file
     */
    private function get_cache($arg_array = NULL) {
        $i = 0;
        $xml = simplexml_load_file($this->filename($arg_array));
        foreach ($xml as $x => $value) {
            foreach ($value as $v => $s) {
                $bTemp[$v] = (string)$s;
            }
            $r[$i] = $bTemp;
            $i++;
            $bTemp = null;
        }
        $this->rows_from_cache = $i;
        unset($xml, $i, $bTemp, $value, $x, $v, $s);
        return $r;
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
     * Function that logs all errors
     *
     * @param $query string The query to log
     * @param $errno int The error number to log
     * @param $type string Whether the error is fatal or non-fatal
     * @param $error string The error description
     * @return boolean Always returns TRUE.
     */
    private function logError($query, $errno, $type='non-fatal', $error=null) {
        $query_num = count($this->dbLiveStats);

        if (empty($error)) {
            $complete_error = '(not specified)';
        } else if ($type == 'non-fatal') {
            $complete_error = '[NOTICE] ' . $error;
        } else {
            $complete_error = '[ERROR] ' . $error;
            $this->rollback = true;
        }
        $this->dbErrors[$query_num] = array(
            'query_number' => $query_num,
            'query'        => $query,
            'errno'        => $errno,
            'type'         => $type,
            'error'        => $complete_error
        );
        if ($type == 'fatal') {
            $this->error = '[' . $errno . '] ' . $error;
            $this->results = 0;
        }

        return true;
    }

    /**
     * Function that executes after each query and also acumulates data for the XML log
     *
     * @param $stats array
     * @param $arg_array array
     * @param $result array
     * @param $error boolean
     * @param $from_cache boolean
     * @return boolean Always returns TRUE.
     */
    private function logMe($stats, $arg_array, $result, $error, $from_cache) {
        $this->cache_query = false;
        $stats = array(
            'memory' => memory_get_usage() - $stats['memory'],
            'time'   => number_format(microtime(true) - $stats['time'], 5, ',', '.'),
        );
        $this->liveStats($arg_array, $stats, $error, $from_cache);
        if (isset($arg_array[0])) {
            $query = $arg_array[0];
        } else {
            $query = '';
        }
        if (DB_LOG_XML) {
            $this->xmllog[] = array(
                'query'  => $query,
                'memory' => $stats['memory'],
                'time'   => $stats['time'],
                'error'  => $error,
            );
        }
        return true;
    }

    /**
     * Live Statistics, can be embedded in source code to quickly check some things
     *
     * @param $query string
     * @param $stats array
     * @param $data int
     * @param $error boolean
     * @param $from_cache boolean
     * @return boolean Always returns TRUE.
     */
    private function liveStats($query, $stats=null, $data=0, $error=false, $from_cache=false) {
        if ($this->keepLiveLog === true) {
            if ($error == false) {
                $error = 'FALSE';
            } else {
                $error = 'TRUE';
            }

            if (!is_array($stats) or empty($stats)) {
                $stats = array(
                    'time'     => 0,
                    'memory'   => 0,
                );
            }

            if ($from_cache === true) {
                $valid_cache = 'TRUE';
            } else {
                $valid_cache = 'FALSE';
            }

            if ($this->in_transaction === true) {
                $in_trans = 'TRUE';
            } else {
                $in_trans = 'FALSE';
            }

            $results = $this->num_rows;
            if ($this->cache_query === true) {
                $this->rows_from_cache = $results;
            }

            $this->dbLiveStats[] = array(
                'query'              => $query,
                'number_results'     => $results,
                'time'               => $stats['time'] . ' (seg)',
                'memory'             => $stats['memory'] . ' (bytes)',
                'datasize'           => $data . ' (bytes)',
                'error'              => $error,
                'from_cache'         => $valid_cache,
                'within_transaction' => $in_trans,
            );
        }

        return true;
    }

    /**
     * Function that creates a log in XML format
     *
     * @param $query_arr array
     * @return boolean Returns always TRUE.
     */
    private function db_log($query_arr) {
        static $i = 0;
        $num_queries = count($query_arr);
        if ($num_queries > 0) {
            if (isset($_SERVER['HTTP_REFERER'])) {
                $referer = $_SERVER['HTTP_REFERER'];
            } else {
                $referer = 'Unknown';
            }
            if (!is_writable(dirname(DB_URL_XML))) {
                $this->logError('[all]', 0, 'fatal', 'I can\'t write to "' . DB_URL_XML . '". Permission problems?');
            } else {
                if (!is_readable(DB_URL_XML)) {
                    if (file_exists(DB_URL_XML)) {
                        $this->logError('[all]', 0, 'fatal', 'I cant\'t append data to "' . DB_URL_XML . '". Will try to replace it. Please check your permissions.');
                    } else {
                        $this->logError('[all]', 0, 'non-fatal', 'File "' . DB_URL_XML . '" doesn\'t exist. Creating it.');
                    }
                    $xml = simplexml_load_string('<?xml version="1.0" encoding="UTF-8"?><db_log></db_log>');
                } else {
                    $xml = simplexml_load_file(DB_URL_XML);
                }
            }
            if (!$this->error) {
                $final = $xml->addChild('pageview');
                $final->addChild('nQueries', $num_queries);
                $final->addChild('dDateTime', date('d-m-Y, h:i'));
                $final->addChild('sIP', $_SERVER['REMOTE_ADDR']);
                $final->addChild('sBrowser', $_SERVER['HTTP_USER_AGENT']);
                $final->addChild('sUrl', htmlentities($_SERVER['REQUEST_URI']));
                $final->addChild('sRef', htmlentities($referer));
                $consultas = $final->addChild('myquery');
                foreach ($query_arr as $k => $q) {
                    if ($q['error'] == false) {
                        $q['error'] = 'FALSE';
                    }
                    $detalle[$i] = $consultas->addChild('query_' . $i);
                    $detalle[$i]->addChild('sSql', $q['query']);
                    $detalle[$i]->addChild('nResults', $this->num_rows);
                    $detalle[$i]->addChild('fTime', $q['time'] . ' (seg)');
                    $detalle[$i]->addChild('iMemory', $q['memory'] . ' (bytes)');
                    $detalle[$i]->addChild('iError', $q['error']);
                    $i++;
                }
                if (!@$xml->asXML(DB_URL_XML)) {
                    $this->logError('[all]', 0, 'non-fatal', 'Couldn\'t create or replace xml file, please check permissions');
                }
            }
            unset($referer, $detalle, $final, $consultas, $xml, $q, $k, $i);
        }

        return true;
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
