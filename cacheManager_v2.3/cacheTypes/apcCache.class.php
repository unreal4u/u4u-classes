<?php

namespace u4u;

/**
 * Provides interaction with user space cache in APC
 *
 * @package Cache Class
 * @since 2.0
 * @author Camilo Sperberg - http://unreal4u.com/
 * @license BSD License. Feel free to use and modify
 */
class apcCache extends \u4u\cacheManager implements \u4u\cacheManagerInterface {
    /**
     * Constructor
     *
     * @param boolean $throwExceptionOnDisabled Whether to throw exception on disabled APC cache module. Defaults to false
     */
    public function __construct($throwExceptions=true) {
        $this->throwExceptions($throwExceptions);
    }

    /**
     * Does the actual check whether APC is enabled or not
     *
     * @see cacheManager::checkIsEnabled()
     * @throws Exception If APC module is not loaded or enabled, throws this exception
     * @return boolean Returns true if APC is enabled, false otherwise
     */
    public function checkIsEnabled() {
        // If already checked, return that value instead
        if (empty($this->isChecked)) {
            $this->isChecked = true;
            $this->isEnabled = (bool)ini_get('apc.enabled');
            if ($this->isEnabled === false or !extension_loaded('apc')) {
                $this->isEnabled = false;
                throw new \Exception('APC extension is not loaded or not enabled!');
            }
        }

        return $this->isEnabled;
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
        if (is_int($ttl)) {
            $this->_setTtl($ttl);
        }
        $return = apc_store($this->_cacheId($identifier, $funcArgs), $data, $this->_ttl);

        return $return;
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
        $return = false;
        $data = apc_fetch($this->_cacheId($identifier, $funcArgs), $return);
        if (!empty($return)) {
            $return = $data;
        }

        return $return;
    }

    /**
     * Physically removes a cache from memory
     *
     * @see cacheManager::delete()
     * @param string $identifier A unique name to use
     * @param array $funcArgs Optional extra arguments to differentiate cache
     */
    public function delete($identifier='', $funcArgs=null) {
        $return = apc_delete($this->_cacheId($identifier, $funcArgs));

        return $return;
    }

    /**
     * Deletes the entire cache
     *
     * @see cacheManager::purgeCache()
     * @param boolean $onlyUserSpace Whether to delete only user space. Defaults to false
     * @return boolean Returns true when cache could be deleted, false otherwise
     */
    public function purgeCache($onlyUserSpace=false) {
        if (!empty($onlyUserSpace)) {
            apc_clear_cache();
        }
        $return = apc_clear_cache('user');

        return $return;
    }

    /**
     * Garbage collector for APC: not enabled because APC's internal garbage collector will be far better
     *
     * @see cacheManager::executeGarbageCollector()
     * @return boolean Returns always true
     */
    public function executeGarbageCollector() {
        return true;
    }

    /**
     * Gets cache information
     * If $type is "user", it will return user space cache information. Otherwise, it will return the system space
     *
     * @param string $type Can be "user" or empty
     */
    public function getCacheInformation($type=null) {
        if (!empty($type)) {
            $type = 'user';
        } else {
            $type = null;
        }
        $return = apc_cache_info($type);

        return $return;
    }

    /**
     * Will purge all caches that haves the same identifier
     *
     * This can be of use on a multi-language website where the indexed content depends on the selected language.
     * Normally you will only have the choice to delete all cache or one specific entry, with this little function you
     * will delete all caches that have a certain identifier.
     *
     * @param string $identifier Which cache we want to delete
     * @return int The amount of caches deleted
     */
    public function purgeIdentifierCache($identifier='') {
        $deletedCount = 0;

        $cacheList = apc_cache_info("user");
        foreach ($cacheList['cache_list'] AS $deleteCandidate) {
            if (strpos($deleteCandidate['info'], $identifier) === 0 AND apc_delete($deleteCandidate['info'])) {
                $deletedCount++;
            }
        }

        return $deletedCount;
    }
}
