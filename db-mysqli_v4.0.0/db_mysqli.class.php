<?php
/**
 * Extended MySQLi Parametrized DB Class
 *
 * db_mysqli.class.php, a MySQLi database access wrapper
 * Original idea from Mertol Kasanan, http://www.phpclasses.org/browse/package/5191.html
 * Optimized, tuned and fixed by unreal4u (Camilo Sperberg)
 *
 * @package Database
 * @version 3.1.1
 * @author Camilo Sperberg, http://unreal4u.com/
 * @author Mertol Kasanan
 * @license BSD License
 * @copyright 2009 - strftime('Y') Camilo Sperberg
 */
class db_mysqli {
    /**
     * Whether to cache the query or not
     *
     * @var boolean $cache_query Defaults to FALSE
     */
    public $cache_query = false;
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
     * Contains the actual DB connection
     *
     * @var object $db
     */
    private $db = null;
    private $stmt = null;
    private $connected = false;
    private static $stats = array();
    private $error = false;
    private $xmllog = array();
    private $cache_recreate = false;
    private $load_from_cache = false;
    private $rows_from_cache = -1;
    private $num_rows = 0;
    private $in_transaction = false;
    private $rollback = false;
    public $createRuntimeLog = false;

    /**
     * When constructed we could enter transaction mode
     *
     * @param $in_transaction boolean Defaults to FALSE
     */
    public function __construct($in_transaction = false) {
        if (version_compare(PHP_VERSION, '5.1.5', '<')) {
            die('Sorry, class only valid for PHP &gt; 5.1.5, please consider upgrading to the latest version');
        }

        if ($in_transaction === true) {
            $this->begin_transaction();
        }
    }

    /**
     * Ends a transaction if needed and logs (when it should) everything into a XML file.
     */
    public function __destruct() {
        if ($this->in_transaction === true and $this->connected === true) {
            $this->end_transaction();
        }
        if (DB_LOG_XML) {
            $this->db_log($this->xmllog);
        }
    }

    /**
     * Controls all the calls to the class
     *
     * @param $func string The method to call
     * @param $arg_array array The data, such as the query. Can also by empty
     */
    public function __call($func, $arg_array) {
        self::$stats = array(
            'time' => time() + microtime(),
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
        $log = true;
        switch ($func) {
            case 'num_rows':
                if ($arg_array != NULL) {
                    $this->execute_query($arg_array);
                    $num_rows = $this->execute_result_info($arg_array);
                    $result = $num_rows['num_rows'];
                }
                //else $result = $this->execute_num_rows();
                break;
            case 'insert_id':
                if ($arg_array != NULL) {
                    $this->execute_query($arg_array);
                    $num_rows = $this->execute_result_info();
                    $result = $num_rows['insert_id'];
                }
                //else $result = $this->execute_num_rows();
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
                $log = false;
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
                $log = false;
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

                return $result;
            default:
                return 'Method not supported!';
                break;
        }
        if ($log) {
            $this->logMe(self::$stats, $arg_array, $result, $this->error, $this->load_from_cache);
        }

        return $result;
    }

    public function __get($v) {
        $num_rows = $this->execute_result_info();
        if (!isset($num_rows[$v])) {
            $num_rows[$v] = 'Method not supported!';
        }

        return $num_rows[$v];
    }

    /**
     * As the connection is no longer established when the class initializes itself, we must do it our way
     */
    private function connect_to_db() {
        if ($this->connected === false) {
            $db_connect = mysql_connect::singleton();
            $this->db = $db_connect->db;
            $this->connected = true;
        }

        return $this->connected;
    }

    /**
     * Function that prepares and binds the query, or does nothing if an valid cache file is found.
     *
     * @param $arg_array array Contains the binded values
     * @return boolean Whether we could execute the query or not
     */
    private function execute_query($arg_array = NULL) {
        $execute_query = false;
        if ($this->cache_query === false or $this->cache_recreate === true) {
            $this->connect_to_db();
            $sql_query = array_shift($arg_array);
            $types = '';
            foreach ($arg_array as $v) {
                // @TODO Add is_boolean() and others
                switch ($v) {
                    case is_string($v):
                        $types .= 's';
                        break;
                    case is_int($v):
                        $types .= 'i';
                        break;
                    case is_double($v):
                        $types .= 'd';
                        break;
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
                    if (!$execute_query = @call_user_func_array(array($this->stmt, 'bind_param'), $this->makeValuesReferenced($arg_array))) {
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
            if ($this->cache_query === false or $this->cache_recreate === true) {
                if ($this->stmt->error) {
                    $this->logError(NULL, $this->stmt->errno, 'fatal', $this->stmt->error);
                    return false;
                }
                $result_metadata = $this->stmt->result_metadata();
                if (is_object($result_metadata)) {
                    $result_fields = array();
                    while ($field = $result_metadata->fetch_field()) {
                        array_unshift($result_fields, $field->name);
                        $params[] = & $row[$field->name];
                    }
                    call_user_func_array(array($this->stmt, 'bind_result'), $params);

                    while ($this->stmt->fetch()) {
                        foreach ($row as $key => $val) {
                            $c[$key] = $val;
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

    /**
     * Function that establish the cache filename
     *
     * @param $arg_array array Used to create the filename
     * @return string Returns the filename of the cache file
     */
    private function filename($arg_array = NULL) {
        $filename = '0';
        if (!empty($arg_array)) {
            foreach ($arg_array AS $a) {
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
     * Function that creates a valid XML file.
     * I'm not using SimpleXML here because of speed. Using SimpleXML, with 100.000 records, it takes 26 seconds, this
     * way, only 1 second (on my test server)
     *
     * @todo Don't return TRUE when cache creation fails
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
            $bTemp = NULL;
        }
        $this->rows_from_cache = $i;
        unset($xml, $i, $bTemp, $value, $x, $v, $s);
        return $r;
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
    private function logError($query, $errno, $type = 'non-fatal', $error) {
        $query_num = count($this->dbLiveStats);
        if (empty($error)) {
            $error = '(not specified)';
        } else if ($type == 'non-fatal') {
            $complete_error = '[NOTICE] ' . $error;
        } else {
            $complete_error = '[ERROR] ' . $error;
            $this->rollback = true;
        }

        $this->dbErrors[$query_num] = array(
            'query_number' => $query_num, 'query' => $query, 'errno' => $errno, 'type' => $type, 'error' => $complete_error
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
        if (!DB_DATASIZE) {
            $datasize = 0;
        } else {
            $datasize = $this->array_size($result);
        }

        $stats = array(
            'memory' => memory_get_usage() - $stats['memory'], 'time' => number_format((time() + microtime()) - $stats['time'], 5, ',', '.')
        );

        $this->liveStats($arg_array, $stats, $datasize, $error, $from_cache);

        if (isset($arg_array[0])) {
            $query = $arg_array[0];
        } else {
            $query = '';
        }
        if (DB_LOG_XML) {
            $this->xmllog[] = array(
                'query' => $query, 'memory' => $stats['memory'], 'time' => $stats['time'], 'datasize' => $datasize, 'error' => $error
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
    private function liveStats($query, $stats = NULL, $data = 0, $error = false, $from_cache = false) {
        if ($error == false) {
            $error = 'FALSE';
        }
        if (!is_array($stats) or empty($stats)) {
            $stats = array(
                'time' => 0, 'memory' => 0
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
            'query' => $query, 'number_results' => $results, 'time' => $stats['time'] . ' (seg)', 'memory' => $stats['memory'] . ' (bytes)', 'datasize' => $data . ' (bytes)', 'error' => $error, 'from_cache' => $valid_cache, 'within_transaction' => $in_trans
        );

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
                    if (DB_DATASIZE) {
                        $detalle[$i]->addChild('iDataSize', $q['datasize'] . ' (bytes)');
                    }
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

    /**
     * Function that sums the total length of the data array
     *
     * @param $a array The array to get the size of
     * @return int The length in bytes of the array.
     */
    private function array_size($a = NULL) {
        $size = 0;
        if (is_array($a)) {
            while (list($k, $v) = each($a)) {
                if (is_array($v)) {
                    $size = $size + $this->array_size($v);
                } else {
                    $size = $size + strlen($v);
                }
            }
        } else {
            $size = strlen($a);
        }
        return $size;
    }
}

/**
 * Singleton class to connect to DB
 *
 * @author Mertol Kasanan
 * @author Camilo Sperberg
 */
class mysql_connect {
    private static $instance;
    private $connected = false;

    public static function singleton() {
        if (!isset(self::$instance)) {
            $c = __CLASS__;
            self::$instance = new $c();
        }
        return self::$instance;
    }

    public function __clone() {
        if (DB_SHOW_ERRORS === true) {
            trigger_error('We can only declare this once!', E_USER_ERROR);
        } else {
            die();
        }
    }

    public function __construct() {
        try {
            $this->db = new mysqli(MYSQL_HOST, MYSQL_USER, MYSQL_PASS, MYSQL_NAME, MYSQL_PORT);
            if (mysqli_connect_error()) {
                throw new Exception('Sorry, no DB connection could be made, please run in circles while an administrator checks the system: ' . mysqli_connect_error());
            } else {
                $this->connected = true;
            }
        } catch (Exception $e) {
            if (DB_SHOW_ERRORS === true) {
                trigger_error($e->getMessage(), E_USER_ERROR);
            } else {
                die();
            }
        }

        if ($this->connected === true) {
            $this->db->set_charset(DBCHAR);
        }
    }

    public function __destruct() {
        if ($this->connected === true) {
            $this->db->close();
        }
    }
}
