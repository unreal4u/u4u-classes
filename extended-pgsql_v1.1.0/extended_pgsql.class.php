<?php
/** 
 * Extended PGsql, an database wrapper for PostGreSQL using PDO-pgsql
 * 
 * @package Database
 * @version 1.1.0 $Rev: 9 $
 * @copyright 2009 - 2011 Camilo Sperberg -- $Date: 2011-07-21 00:23:45 -0400 (Thu, 21 Jul 2011) $
 * @author Camilo Sperberg, http://unreal4u.com/ $Author: unreal4u $
 * @license BSD License
 */

if (empty($proc)) die('No direct access allowed');

class extended_pgsql {
  /**
   * Whether to cache the query or not
   * @var boolean $cache_query Defaults to FALSE
   */
  public  $cache_query = FALSE;
  /**
   * Maintains statistics of the executed queries
   * @var array $LiveStats 
   */
  public  $LiveStats = array();
  /**
   * Maintains statistics exclusively from the errors in SQL
   * @var array $errors
   */
  public  $errors = array();
  /**
   * Contains the actual DB connection
   * @var object $db
   */
  private $db = null;
  private $stmt = null;
  private $connected = FALSE;
  private $stats = array();
  private $error = FALSE;
  private $cache_recreate = FALSE;
  private $load_from_cache = FALSE;
  private $rows_from_cache = -1;
  private $num_rows = 0;
  private $in_transaction = FALSE;
  private $rollback = FALSE;
  private $insert_id = NULL;
  private $db_version = '';
  
	public function __construct($in_transaction = FALSE) {
	  if (version_compare(PHP_VERSION,'5.1.5','<')) die('Sorry, class only valid for PHP &gt; 5.1.5, please consider upgrading to the latest version');
	  if ($in_transaction === TRUE) $this->begin_transaction();
  }
	
	public function __destruct() {
    if ($this->in_transaction === TRUE AND $this->connected === TRUE) $this->end_transaction();
    if (DB_LOG_XML) $this->db_log();
	}
	
	public function __call($func,$arg_array) {
	  $this->stats = array('time' => time() + microtime(), 'memory' => memory_get_usage());
	  $this->error = FALSE;
    $this->load_from_cache = FALSE;

    if ($this->cache_query === FALSE) $this->cache_recreate = FALSE;
    elseif (!$this->valid_cache($arg_array)) $this->cache_recreate = TRUE;
    else {
      $this->cache_recreate  = FALSE;
      $this->load_from_cache = TRUE;
    }

    $log = TRUE;
    switch ($func) {
      case 'num_rows':
        $this->execute_query($arg_array);
        $num_rows = $this->execute_result_info($arg_array);
        $result   = $num_rows['num_rows'];
        break;
      case 'insert_id':
        $this->execute_query($arg_array);
        $num_rows = $this->execute_result_info();
        $result   = $num_rows['insert_id'];
        break;
      case 'query':
        $this->execute_query($arg_array);
        if (!$result = $this->execute_result_array($arg_array)) $result = FALSE;
        break;
      case 'begin_transaction':
        $this->connect_to_db();
        if ($this->in_transaction === FALSE) {
          $this->in_transaction = TRUE;
          $this->db->beginTransaction();
        }
        $log = FALSE;
        $result = TRUE;
        break;
      case 'end_transaction':
        $result = TRUE;
        if ($this->in_transaction) {
          $this->in_transaction = FALSE;
          if ($this->rollback === FALSE) $this->db->commit();
          else {
            $this->db->rollBack();
            $this->rollback = FALSE;
            $result = FALSE;
          }
        }
        $log = FALSE;
        break;
      case 'version':
        if (empty($this->db_version)) {
          $temp = $this->query('SELECT VERSION() AS v');
          $this->db_version = $temp[0]['v'];
          unset($temp);
        }
        if (!empty($arg_array[0])) {
          $temp = explode(' ',$this->db_version);
          $result = $temp[1];
        }
        else $result = $this->db_version;
        return $result;
      default:
        return 'Method not supported!';
        break;
    }
    if ($log) $this->logMe($arg_array,$result,$this->error,$this->load_from_cache);
    return $result;
  }
  
  public function __get($v) {
    $num_rows = $this->execute_result_info();
    if (!isset($num_rows[$v])) $num_rows[$v] = 'Method not supported';
    return $num_rows[$v];
  }
  
/**************************************************************************/
/*              DIRECT DATABASE RELATED                                   */
/**************************************************************************/
  /**
   * Connect with a singleton pattern to the DB
   */
  private function connect_to_db() {
    if ($this->connected === FALSE) {
      $db_connect   = pgsql_connect::singleton();
      $this->db     = $db_connect->db;
      $this->connected = TRUE;
    }
    return $this->connected;
  }
    
  private function execute_query($arg_array) {
    $execute_query = FALSE;
    $output = TRUE;
    if ($this->cache_query === FALSE OR $this->cache_recreate === TRUE) {
      $sql_query = array_shift($arg_array);
      $this->connect_to_db();

      if (isset($this->stmt)) unset($this->stmt);
      $this->stmt = $this->db->prepare($sql_query);
      if (!is_object($this->stmt)) $this->logError($sql_query,$this->stmt->errorCode(),'fatal',$this->stmt->errorInfo());
      
      if (!$this->error) {
        try {
          if (!$this->stmt->execute($arg_array)) throw new Exception('Problems with the query',E_USER_WARNING);
          else $this->insert_id = $this->get_last_insert_id($sql_query);
        }
        catch (Exception $e) {
          $this->logError($sql_query, $this->stmt->errorCode(), 'fatal', $this->stmt->errorInfo());
          $output = FALSE;
        }
      }
    }  
    return $output;
  }
  
  private function get_last_insert_id($query) {
    $id[0] = NULL;
    if(preg_match("/^INSERT[\t\n ]+INTO[\t\n ]+([a-z0-9\_\-]+)/is", $query, $tablename) AND stripos($query,' returning ') !== FALSE) $id = $this->stmt->fetch(PDO::FETCH_NUM);
    return $id[0];
  }
  
  private function execute_result_array($arg_array) {
    if (!$this->error) {
      if ($this->cache_query === FALSE OR $this->cache_recreate === TRUE) {
        $result = $this->stmt->fetchAll();
        if ($this->cache_recreate === TRUE AND !empty($result)) $this->create_cache($arg_array,$result);
      }
      else $result = $this->get_cache($arg_array);
    }
    else $result = 0;

    return $result;    
  }
  
  private function execute_result_info($arg_array = NULL) {
    return array('num_rows' => $this->stmt->rowCount(),'insert_id' => $this->insert_id);
  }
  
/**************************************************************************/
/*                     DATABASE CACHE RELATED                             */
/**************************************************************************/
  /**
   * Function that establish the cache filename
   * @param array $arg_array Used to create the filename
   * @return string Returns the filename of the cache file
   */
  private function filename($arg_array = NULL) {
    $filename = '0';
    if (!empty($arg_array)) {
      foreach ($arg_array AS $a) $filename .= $a;
      $filename = 'db_'.md5($filename);
    }
    return DB_CACHE_LOCATION.$filename.'.xml';
  }

  /**
   * Checks whether our cache file is still valid.
   * @param array $arg_array Used to create the filename
   * @return boolean TRUE if cache file is still valid, FALSE otherwise
   */
  private function valid_cache($arg_array = NULL) {
    $filename = '';
    $is_valid = FALSE;
    if (is_array($arg_array)) {
      $filename = $this->filename($arg_array);
      if (file_exists($filename)) {
        if (filemtime($filename) > time() - DB_CACHE_EXPIRE) $is_valid = TRUE;
        else if (!@unlink($filename)) $this->logError($arg_array[0],0,'non-fatal','Couldn\'t delete old cache file! Check permissions');
      }
    }
    return $is_valid;
  }

  /**
   * Function that creates a valid XML file.
   * 
   * I'm not using SimpleXML here because of speed.
   * Using SimpleXML, with 100.000 records, it takes 26 seconds, this 
   * way, only 1 second (on my test server)
   * 
   * @param array $arg_array Used to create the filename
   * @param array $result Used to replicate the result in the XML file.
   * @return boolean Returns always TRUE.
   */
  private function create_cache($arg_array,$result) {
    $i = 0;
    $xml = ''; $done = TRUE;
    foreach($result AS $r) {
      $xml .= '<r'.$i.'>';
      foreach($r AS $key => $value) $xml .= '<'.$key.'>'.$value.'</'.$key.'>';
      $xml .= '</r'.$i.'>';
      $i++;
    }
    if (!@file_put_contents($this->filename($arg_array),'<?xml version="1.0" encoding="UTF-8"?>'."\n".'<db>'.$xml.'</db>')) {
      echo $this->filename($arg_array);
      $this->logError($arg_array[0],0,'non-fatal','Couldn\'t create cache file!');
      $this->cache_query = FALSE;
      $this->cache_recreate = FALSE;
      $done = FALSE;
    }
    unset($xml);
    return $done;
  }

  /**
   * Returns number of rows in the XML.
   * @return int Number of rows
   */
  private function get_cache_meta() {
    return $this->rows_from_cache;
  }

  /**
   * Parses and returns the XML in an array.
   * @param array $arg_array Used to create the filename
   * @return array The result set rescued from the cache file
   */
  private function get_cache($arg_array = NULL) {
    $i = 0;
    $xml = simplexml_load_file($this->filename($arg_array));
    foreach($xml AS $x => $value) {
      foreach($value AS $v => $s) $bTemp[$v] = (string)$s;
      $r[$i] = $bTemp;
      $i++;
      $bTemp = NULL;
    }
    $this->rows_from_cache = $i;
    unset($xml,$i,$bTemp,$value,$x,$v,$s);
    return $r;
  }
  
/**************************************************************************/
/*             LOGGING AND DEBUGGING                                      */
/**************************************************************************/
  /**
   * Function that logs all errors
   * @param string $query The query to log
   * @param int $errno The error number to log
   * @param string $type Whether the error is fatal or non-fatal
   * @param string $error The error description
   * @return boolean Always returns TRUE.
   */
  private function logError($query,$errno,$type='non-fatal',$error) {
    $query_num = count($this->LiveStats);
    if (is_array($error)) $error_string = $error[2];
    else $error_string = $error;
    if (empty($error_string)) $error_string = '(not specified)';
    else if ($type == 'non-fatal') $complete_error = '[NOTICE] '.$error_string;
         else {
           $complete_error = '[ERROR] '.$error_string;
           $this->rollback = TRUE;
         }
    if ($this->in_transaction === TRUE) $in_transaction = 'TRUE';
    else $in_transaction = 'FALSE';
    $this->errors[$query_num] = array('query_number' => $query_num,'query' => $query,'in_transaction' => $in_transaction, 'errno' => $errno,'type'  => $type,'error' => $complete_error);
    if ($type == 'fatal') { 
      $this->error = '['.$errno.'] '.$error[2];
      $this->results = 0;
    }
    return TRUE;
  }

  /**
   * Function that executes after each query and also acumulates data for the XML log
   * @param array $stats
   * @param array $arg_array
   * @param array $result
   * @param boolean $error
   * @param boolean $from_cache
   * @return boolean Always returns TRUE.
   */
  private function logMe($arg_array,$result,$error,$from_cache) {
    $this->cache_query = FALSE;

    $this->stats = array('memory' => memory_get_usage() - $this->stats['memory'], 'time' => number_format((time()+microtime()) - $this->stats['time'],5,',','.'));

    $this->liveStats($arg_array,$this->stats,$error,$from_cache);
    if (isset($arg_array[0])) $query = $arg_array[0];
    else $query = '';
    return TRUE;
  }

  /**
   * Live Statistics, can be embedded in source code to quickly check some things
   * @param string $query
   * @param array $stats
   * @param boolean $error
   * @param boolean $from_cache
   * @return boolean Always returns TRUE.
   */
  private function liveStats ($query, $stats = NULL, $error = FALSE, $from_cache = FALSE) {
    if ($error == FALSE) $error = 'FALSE';
    if (!is_array($stats) OR empty($stats)) $stats = array('time' => 0,'memory' =>0);
    if ($from_cache === TRUE) $valid_cache = 'TRUE';
    else $valid_cache = 'FALSE';
    if ($this->in_transaction === TRUE) $in_trans = 'TRUE';
    else $in_trans = 'FALSE';

    $results = $this->num_rows;
    if ($this->cache_query === TRUE) $this->rows_from_cache = $results;

    $this->LiveStats[] = array('query' => $query,'number_results' => $results,'time'  => $stats['time'].' (seg)','memory' => $stats['memory'].' (bytes)','error' => $error,'from_cache' => $valid_cache,'within_transaction' => $in_trans);
    return TRUE;
  }
  
  /**
   * Function that creates a log in XML format
   * @param array $query_arr
   * @return boolean Returns always TRUE.
   */
  private function db_log() {
    $error = FALSE;
    $num_queries = count($this->LiveStats);
    if ($num_queries > 0) {
      if (!empty($_SERVER['HTTP_REFERER'])) $referer = $_SERVER['HTTP_REFERER'];
      else $referer = 'None';

      if (!is_writable(dirname(DB_URL_XML))) $error = TRUE;
      else {
        if (!is_readable(DB_URL_XML)) {
          if (file_exists(DB_URL_XML)) $errors = TRUE;
          $xml = simplexml_load_string('<?xml version="1.0" encoding="UTF-8"?><db_log></db_log>');
        }
        else $xml = simplexml_load_file(DB_URL_XML);
      }
      if (!$error) {
        $i = 0;
        $final = $xml->addChild('pageview');
        $final->addChild('nQueries',$num_queries);
        $final->addChild('dDateTime',date('d-m-Y, h:i'));
        $final->addChild('sIP',$_SERVER['REMOTE_ADDR']);
        $final->addChild('sBrowser',$_SERVER['HTTP_USER_AGENT']);
        $final->addChild('sUrl',htmlentities($_SERVER['REQUEST_URI']));
        $final->addChild('sRef',htmlentities($referer));
        $consultas = $final->addChild('myquery');
        foreach($this->LiveStats AS $k => $q) {
          if ($q['error'] == FALSE) $q['error'] = 'FALSE';
          $detalle[$i] = $consultas->addChild('query_'.$i);
          $detalle[$i]->addChild('sSql',$q['query'][0]);
          $detalle[$i]->addChild('nResults',$q['number_results']);
          $detalle[$i]->addChild('fTime',$q['time'].' (seg)');
          $detalle[$i]->addChild('iMemory',$q['memory'].' (bytes)');
          $detalle[$i]->addChild('iError',$q['error']);
          $i++;
        }
        if (!$xml->asXML(DB_URL_XML)) $error = TRUE;
      }
      unset($referer,$detalle,$final,$consultas,$xml,$q,$k,$i,$num_queries);
    }
  }
}

/**************************************************************************/
/*                      SECONDARY CLASS                                   */
/**************************************************************************/
/**
 * Singleton class to connect to DB
 * 
 * @author Camilo Sperberg
 */
class pgsql_connect {
  private static $instance;
  private $connected = FALSE;

  public static function singleton() {
    if (!isset(self::$instance)) {
       $c              = __CLASS__;
       self::$instance = new $c;
    }
    return self::$instance;
  }

  public function __clone() {
    if (DB_SHOW_ERRORS === TRUE) trigger_error('We can only declare this once!', E_USER_ERROR);
    else die();
  }

  public function __construct() {
    $this->connected = TRUE;
    try {
      if (PGSQL_HOST == 'localhost') $host = '';
      else $host = 'host='.PGSQL_HOST;
      if (PGSQL_PORT == 5432) $port = '';
      else $port = ';port='.PGSQL_PORT.';';
      $this->db = new PDO('pgsql:'.$host.'dbname='.PGSQL_NAME.$port,PGSQL_USER,PGSQL_PASS);
      $this->db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PGSQL_FETCH_MODE); 
    }
    catch(Exception $e) {
      $this->connected = FALSE;
      if (DB_SHOW_ERRORS === TRUE) trigger_error($e->getMessage(), E_USER_ERROR);
      else die();
    }
  }

  public function __destruct() {
    if ($this->connected === TRUE) $this->db = NULL;
    $this->connected = FALSE;
  }
}
