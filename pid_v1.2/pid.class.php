<?php
/**
 * Determines in Windows or other OS whether the script is already running or not
 * 
 * @package CLI - process check
 * @author unreal4u - Camilo Sperberg
 * @author http://www.electrictoolbox.com/check-php-script-already-running/
 * @version 1.3
 * @license BSD
 */
class pid {
	/**
	 * The filename of the PID
	 * 
	 * @var string $filename
	 */
	protected $_filename = '';

	/**
	 * After how many time we consider the pid file to be stalled
	 * 
	 * @var int $timeout
	 */
	protected $_timeout = 30;

	/** 
	 * Value of script already running or not
	 * 
	 * @var boolean $already_running
	 */
	public $already_running = false;

	/**
	 * Contains the PID of the script
	 * 
	 * @var integer $pid
	 */
	public $pid = 0;

	/**
	 * The main function that does it all
	 * 
	 * @param string $directory The directory where the PID file goes to, without trailing slash
	 * @param string $filename The filename of the PID file
	 * @param int $timeout If we want to add a timeout
	 */
	public function __construct($directory='', $filename='', $timeout=null, $checkOnConstructor=true) {
		if ($checkOnConstructor) {
			$this->_checkPid();
		}
	}
	
	/**
	 * Does the actual check
	 * 
	 * @param string $directory The directory where the pid file is stored
	 * @param string $filename Name of the pid file
	 * @param int $timeout The time after which a pid file is considered "stalled"
	 */
	protected function _checkPid($directory='', $filename='', $timeout=null) {
		if (empty($directory)) {
			$directory = sys_get_temp_dir();
		}
		if (empty($filename)) {
			$filename = basename($_SERVER['PHP_SELF']);
		}
		$this->setTimeout($timeout);
		$this->_filename = $directory . '/' . $filename . '.pid';

		if(is_writable($this->_filename) || is_writable($directory)) {
			if(file_exists($this->_filename)) {
				$this->pid = (int)trim(file_get_contents($this->_filename));
				if (strtolower(substr(PHP_OS,0,3)) == 'win') {
					$wmi = new COM('winmgmts://');
					$processes = $wmi->ExecQuery('SELECT ProcessId FROM Win32_Process WHERE ProcessId = \''.$this->pid.'\'');
					if (count($processes) > 0) {
						$i = 0;
						foreach($processes AS $a) $i++;
						if ($i > 0) {
							$this->already_running = true;
						}
					}
				} else {
					if(posix_kill($this->pid,0)) {
						$this->already_running = true;
						if (!is_null($this->_timeout)) {
							$fileModificationTime = $this->getTSpidFile();
							if ($fileModificationTime + $this->_timeout < time()) {
								$this->already_running = false;
								unlink($this->_filename);
							}
						}
					}
				}
			}
		} else {
			//throw new Exception('Cannot write to pid file "'.$this->_filename.'". Program execution halted.'."\n");
			return 1;
		}
	
		if(!$this->already_running) {
		  $this->pid = getmypid();
		  file_put_contents($this->_filename, $this->pid);
		}
		return $this->pid;
	}

	/**
	 * Gets the last modified date of the pid file
	 * 
	 * @return int Returns the timestamp
	 */
	public function getTSpidFile() {
		return filemtime($this->_filename);
	}
	
	/**
	 * Sets a timeout
	 * 
	 * @param int $ttl
	 * @return int Returns the timeout to what is was set
	 */
	public function setTimeout($ttl=30) {
		if (is_numeric($ttl)) {
			$this->_timeout = $ttl;
		}
		
		return $this->_timeout;
	}
	
	
	/**
	 * Destroys the file
	 */
	public function __destruct() {
		if (is_writable($this->_filename) AND !$this->already_running) {
			unlink($this->_filename);
		}
		return true;
	}
}