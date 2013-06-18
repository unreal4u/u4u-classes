<?php

namespace u4u;

/**
 * If no class is available, fall back to this one
 *
 * This class doesn't implement anything, it just helps that the entire application doesn't fall apart in the case that
 * the class begins to throw exceptions at an early stage
 *
 * @package Cache Class
 * @since 2.3
 * @author Camilo Sperberg - http://unreal4u.com/
 * @license BSD License. Feel free to use and modify
 */
class defaultCache extends \u4u\cacheManager implements \u4u\cacheManagerInterface {
    /**
     * Constructor
     *
     * @param boolean $throwExceptionOnDisabled Whether to throw exceptions. Defaults to false
     */
    public function __construct($throwExceptions=false) {
        $this->throwExceptions($throwExceptions);
    }

    /**
     * Does the actual check
     *
     * @see cacheManager::checkIsEnabled()
     * @return boolean Returns always false
     */
    public function checkIsEnabled() {
        return false;
    }

    /**
     * Saves a cache into memory. Sets a time and the unique identifier
     *
     * @see cacheManager::save()
     * @param mixed $data The data we want to save
     * @param string $identifier A unique name to use
     * @param array $funcArgs Optional extra arguments to differentiate cache
     * @param int $ttl The time the cache will be valid
     */
    public function save($data=false, $identifier='', $funcArgs=null, $ttl=null) {
        return false;
    }

    /**
     * Rescues a cache from memory
     *
     * @see cacheManager::load()
     * @param string $identifier A unique name to use
     * @param array $funcArgs Optional extra arguments to differentiate cache
     * @return mixed Returns the data or false if no cache was found
     */
    public function load($identifier='', $funcArgs=null) {
        return false;
    }

    /**
     * Physically removes a cache from memory
     *
     * @see cacheManager::delete()
     * @param string $identifier A unique name to use
     * @param array $funcArgs Optional extra arguments to differentiate cache
     */
    public function delete($identifier='', $funcArgs=null) {
        return false;
    }

    /**
     * Deletes the entire cache
     *
     * @see cacheManager::purgeCache()
     * @param boolean $onlyUserSpace Whether to delete only user space. Defaults to false
     * @return boolean Returns always false
     */
    public function purgeCache($onlyUserSpace=false) {
        return false;
    }

    /**
     * Execute Garbage collector
     *
     * @see cacheManager::executeGarbageCollector()
     * @return boolean Returns always false
     */
    public function executeGarbageCollector() {
        return false;
    }
}
