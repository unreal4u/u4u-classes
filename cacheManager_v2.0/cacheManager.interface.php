<?php

/**
 * The interface that all childs must implement
 *
 * @package Cache manager
 * @version 2.0
 * @author Camilo Sperberg - http://unreal4u.com/
 * @license BSD License. Feel free to use and modify
 */
interface cacheManagerInterface {
    /**
     * Does the actual check whether the module can be enabled or not
     *
     * For APC it can be used to check if APC is installed and loaded, file based cache can check whether the cache dir
     * is writable, and so on
     *
     * @return boolean Returns true if cache module can be used, false otherwise
     */
    public function checkIsEnabled();

    /**
     * Saves data into a cache
     *
     * @param mixed $data The data we want to save
     * @param string $identifier A unique name to use
     * @param array $funcArgs Optional extra arguments to differentiate cache options
     * @param int $ttl The time the cache will be valid
     */
    public function save($data=false, $identifier='', $funcArgs=array(), $ttl=60);

    /**
     * Rescues data from cache
     *
     * @param string $identifier A unique name to use
     * @param array $funcArgs Optional extra arguments to differentiate cache options
     * @return mixed Returns the data or false if no cache was found
     */
    public function load($identifier='', $funcArgs=array());

   	/**
   	 * Physically removes a cache entry from memory
   	 *
   	 * @param string $identifier A unique name to use
   	 * @param array $funcArgs Optional extra arguments to differentiate cache options
   	 */
    public function delete($identifier='', $funcArgs=array());

   	/**
   	 * Deletes the entire cache
   	 */
    public function purgeCache();

    /**
     * Execute a garbage collector run
     */
    public function executeGarbageCollector();
}
