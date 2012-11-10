<?php if(!isset($proc)) header('Location: ../../');
/**
 * Module description
 * 
 * @package General
 * @version 0.1 (Will never see the light)
 * @copyright $Date$
 * @author $Author$
 */

class db_updater {
  private $rev = 0;
  private $db;
  
  public function __construct($dbLink) {
    if (!is_null($dbLink)) $db = $dbLink;
    $this->rev = $this->get_current_revision();
    $aLast = $this->db->query('SELECT the_last_rev FROM sist_db_versions ORDER BY insert_date DESC LIMIT 1');
    if ($this->db->num_rows == 0 OR $aLast === FALSE) {
      $this->db->query('CREATE TABLE sist_db_versions (the_last_rev INT(4) UNSIGNED NOT NULL,insert_date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,PRIMARY KEY(the_last_rev))');
      $this->db->query('CREATE TABLE sist_db_details (version INT(4) UNSIGNED NOT NULL,file VARCHAR(15) NOT NULL,success TINYINT(1) UNSIGNED NOT NULL,details MEDIUMTEXT NULL,PRIMARY KEY(version,file))');
      $last = $this->rev - 1;
    }
    else $last = $aLast[0]['the_last_rev'];
    
    $vss = array(); $file = '';
    if (version_compare($this->rev,$last) != 0 AND version_compare($this->rev,$last) == 1) {
      $dir = opendir(INCL.'db-changes/');
      while($file = readdir($dir)) $results[] = $file;
      closedir($dir);
      foreach ($results AS $rs) {
        $file = (int)substr($rs,2,strpos($rs,'.') - 2);
        if (is_numeric($file) AND $file >= $this->rev) $vss[] = $rs;
      }
      unset($results,$dir,$file);
      if (count($vss) > 0) {
        asort($vss);
        foreach($vss AS $v) {
          $output = $this->parse(INCL.'db-changes/'.$v);
          $this->db->query('INSERT INTO sist_db_details (version,file,success,details) VALUES (?,?,?,?)',$this->rev,$v,$output['success'],$output['message']);
        }
      }
      $this->db->query('INSERT INTO sist_db_versions(the_last_rev) VALUES (?)',$this->rev);
    }
    return $this->rev;
  }
  
  private final function file_get_contents_curl($url) {
    $fallback = TRUE;
    if (strpos($url,'http://') !== FALSE) {
      if (function_exists('curl_init')) {
        $fallback = FALSE;
        $fc = curl_init();
        curl_setopt($fc, CURLOPT_URL,$url);
        curl_setopt($fc, CURLOPT_RETURNTRANSFER,1);
        curl_setopt($fc, CURLOPT_HEADER,0);
        curl_setopt($fc, CURLOPT_VERBOSE,0);
        curl_setopt($fc, CURLOPT_SSL_VERIFYPEER,FALSE);
        curl_setopt($fc, CURLOPT_TIMEOUT,30);
        $res = curl_exec($fc);
        curl_close($fc);
      }
    }
    if ($fallback === TRUE) $res = file_get_contents($url);
    return $res;
  }
  
  public function get_current_revision($carpeta = '') {
    if (empty($carpeta)) $carpeta = ROUT;
    $archivo_completo = $this->file_get_contents_curl($carpeta.'.svn/entries');
    $por_partes = explode(PHP_EOL,$archivo_completo);
    return (int)$por_partes[3];
  }
  
  private final function parse($file) {
    $output = array('success' => 1, 'message' => '');
    
    $file_content = explode("\n",$this->file_get_contents_curl($file));            
    $query = "";
    foreach($file_content as $sql_line) {        
      if(trim($sql_line) != "" && strpos($sql_line, "--") === false) {
        $query .= $sql_line;
        if(preg_match("/(.*);/", $sql_line)) {
          $query = substr($query, 0, strlen($query)-1);
          echo $query;
          $asd=$this->db->query($query);
          if ($asd === FALSE) {
            $output['success'] = 0;
            $output['message'] .= 'Houston, we have a problem: '.$query;
          }
          $query = "";
        }
      }
    }
    return $output; 
  }
}
