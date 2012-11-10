<?php

/**
 * Provides interaction with user space cache in APC
 *
 * @package Classes
 * @version 1.1
 * @copyright 2012 - strftime('Y')
 * @author Camilo Sperberg - http://unreal4u.com/
 * @license BSD License. Feel free to use and modify
 */
class apcCacheExtended {
	/**
	 * Time the cache is valid
	 *
	 * @var int
	 */
	protected $_ttl = 60;

	/**
	 * Whether APC is enabled and ready to be used or not
	 *
	 * @var boolean Defaults to true
	 */
	public $isEnabled = true;

	/**
	 * Stores whether we have already checked that APC is enabled or not
	 *
	 * @var boolean Defaults to false
	 */
	private $isChecked = false;

	/**
	 * Whether to throw exception on disabled APC cache module
	 *
	 * @var boolean Defaults to false
	 */
	private $throwExceptionOnDisabled = false;

	/**
	 * Constructor
	 *
	 * @param boolean $throwExceptionOnDisabled Whether to throw exception on disabled APC cache module. Defaults to false
	 */
	public function __construct($throwExceptionOnDisabled=false) {
		if(!empty($throwExceptionOnDisabled)) {
			$this->throwExceptionOnDisabled = true;
		}
	}

	/**
	 * Does the actual check whether APC is enabled or not
	 *
	 * @throws Exception If APC module is not loaded or enabled, throws this exception
	 * @return boolean Returns true if APC is enabled, false otherwise
	 */
	public function checkApcEnabled() {
		// If already checked, return that value instead
		if (empty($this->isChecked)) {
	        $this->isChecked = true;
			$this->isEnabled = (bool)ini_get('apc.enabled');

			// Throw exception if configured that way (And APC isn't enabled)
	        if ($this->throwExceptionOnDisabled === true AND ($this->isEnabled === false OR !extension_loaded('apc'))) {
	            throw new Exception('APC extension is not loaded or not enabled!');
	        }
		}

		return $this->isEnabled;
	}

	/**
	 * Function that creates an unique identifier based on optional arguments
	 *
	 * @param string $identifier A function name
	 * @param array $funcArgs Unique extra optional arguments
	 * @return string Returns an unique md5 string
	 */
	protected function _createIdentifier($identifier='', $funcArgs=array()) {
		// Any empty value (0, NULL, etc) will be converted to an array
		if (empty($funcArgs) OR !is_array($funcArgs)) {
			$funcArgs = array();
		}

		// Returning the unique hash
		return md5($identifier.serialize($funcArgs));
	}

	/**
	 * Sets the total time to live for a cache
	 *
	 * @param int $ttl
	 */
	protected function _setTtl($ttl=60) {
		return $this->_ttl = $ttl;
	}

	/**
	 * Saves a cache into memory. Sets a time and the unique identifier
	 *
	 * @param mixed $data The data we want to save
	 * @param string $identifier A unique name to use
	 * @param array $funcArgs Optional extra arguments to differentiate cache
	 * @param int $ttl The time the cache will be valid
	 */
	public function save($data=false, $identifier='', $funcArgs=array(), $ttl=60) {
		$return = false;
		// In every public function we have to check whether APC is enabled or not
		if ($this->checkApcEnabled()) {
			$this->_setTtl($ttl);
			$return = apc_store($this->_createIdentifier($identifier, $funcArgs), $data, $this->_ttl);
		}

		return $return;
	}

	/**
	 * Rescues a cache from memory
	 *
	 * @param string $identifier A unique name to use
	 * @param array $funcArgs Optional extra arguments to differentiate cache
	 * @return mixed Returns the data or false if no cache was found
	 */
	public function load($identifier='', $funcArgs=array()) {
		$return = false;
		if ($this->checkApcEnabled()) {
			$data = apc_fetch($this->_createIdentifier($identifier, $funcArgs), $return);
			if (!empty($return)) {
				$return = $data;
			}
		}

		return $return;
	}

	/**
	 * Physically removes a cache from memory
	 *
	 * @param string $identifier A unique name to use
	 * @param array $funcArgs Optional extra arguments to differentiate cache
	 */
	public function delete($identifier='', $funcArgs=array()) {
		$return = false;
		if ($this->checkApcEnabled()) {
			$return = apc_delete($this->_createIdentifier($identifier, $funcArgs));
		}

		return $return;
	}

	/**
	 * Deletes the entire cache
	 *
	 * @param boolean $onlyUserSpace Whether to delete only user space. Defaults to false
	 */
	public function purgeCache($onlyUserSpace=false) {
		$return = false;
		if ($this->checkApcEnabled()) {
			if (!empty($onlyUser)) {
				apc_clear_cache();
			}
			apc_clear_cache('user');
			$return = true;
		}

		return $return;
	}

	/**
	 * Gets cache information
	 *
	 * If $type is "user", it will return user space cache information. Otherwise, it will return the system space
	 *
	 * @param string $type Can be "user" or empty
	 */
	public function getCacheInformation($type=null) {
		$return = false;
		if ($this->checkApcEnabled()) {
			if (!empty($type)) {
				$type = 'user';
			} else {
				$type = null;
			}
			$return = apc_cache_info($type);
		}

		return $return;
	}
}
