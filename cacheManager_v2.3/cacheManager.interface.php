<?php

namespace u4u;

/**
 * The interface that all childs must implement
 *
 * @package Cache manager
 * @since 2.0
 * @author Camilo Sperberg - http://unreal4u.com/
 * @license BSD License. Feel free to use and modify
 */
interface cacheManagerInterface {
    /**
     * Every interface MUST implement the constructor or else we'll have a infinite loop problem!
     */
    public function __construct();

    /**
     * Does the actual check whether the module can be enabled or not
     *
     * For APC it can be used to check if APC is installed and loaded, file based cache can check whether the cache dir
     * is writable, and so on.
     * Use this class to do whatever checks you need to do to determine whether the new cache extension can be used or
     * not. It must return a true if it can be done or a false otherwise
     *
     * @return boolean Returns true if cache module can be used, false otherwise
     */
    public function checkIsEnabled();

    /**
     * Saves data into a cache
     *
     * This function will do the actual saving of the data into the cache. Important note: you must check whether the
     * ttl is provided and call the parent @link cacheManager->_setTtl() with the $ttl of the cache in order to set it
     *
     * @param mixed $data The data we want to save
     * @param string $identifier A unique name to use
     * @param array $funcArgs Optional extra arguments to differentiate cache options
     * @param int $ttl The time the cache will be valid
     * @return boolean Returns true when the cache could be saved, false otherwise
     */
    public function save($data=false, $identifier='', $funcArgs=null, $ttl=null);

    /**
     * Rescues data from cache
     *
     * This function must return false if no cache could be loaded
     *
     * @param string $identifier A unique name to use
     * @param array $funcArgs Optional extra arguments to differentiate cache options
     * @return mixed Returns the data or false if no cache was found
     */
    public function load($identifier='', $funcArgs=null);

       /**
        * Physically removes a cache entry from memory
        *
        * @param string $identifier A unique name to use
        * @param array $funcArgs Optional extra arguments to differentiate cache options
        * @return boolean Returns true when the cache could be deleted successfully, false otherwise
        */
    public function delete($identifier='', $funcArgs=null);

       /**
        * Deletes the entire cache
        *
        * @return boolean Returns true when cache could be deleted, false otherwise
        */
    public function purgeCache();

    /**
     * Execute a garbage collector run
     *
     * @return boolean Returns true when the garbage collector is run successfully, false otherwise
     */
    public function executeGarbageCollector();
}
