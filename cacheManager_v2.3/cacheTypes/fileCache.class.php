<?php

namespace u4u;

/**
 * Provides a file-based cache manager
 *
 * @package Cache Class
 * @since 2.0
 * @author Camilo Sperberg - http://unreal4u.com/
 * @license BSD License. Feel free to use and modify
 */
class fileCache extends \u4u\cacheManager implements \u4u\cacheManagerInterface {
    /**
     * Contains the actual directory
     *
     * @var string
     */
    public $cacheDirectory='';

    /**
     * Constructor
     *
     * @param string $cacheDirectory The directory in which to save the cache entries
     */
    public function __construct($throwExceptions=true, $cacheDirectory='') {
        $this->throwExceptions($throwExceptions);
        $this->setSaveDirectory($cacheDirectory);
    }

    /**
     * Sets a directory in which to save the cache entries
     *
     * @param string $directory
     * @return string
     */
    public function setSaveDirectory($directory='') {
        if (empty($directory)) {
            $this->cacheDirectory = '/tmp/php-cacheManager/';
        } else {
            $this->cacheDirectory = $directory;
        }

        // Ensure use of slash at the end of directory
        $this->cacheDirectory = rtrim($this->cacheDirectory, '/').'/';

        return $this->cacheDirectory;
    }

    /**
     * Creates the cache directory
     */
    public function createCacheDirectory() {
        $return = false;
        if (!file_exists($this->cacheDirectory)) {
            $return = mkdir($this->cacheDirectory, 0777, true);
        }
        return $return;
    }

    /**
     * Deletes an actual file from the hard disk
     *
     * @param string $filename
     * @return bool
     */
    private function deleteCacheFile($filename) {
        $result = false;

        if (is_writable($filename)) {
            $result = unlink($filename);
        }

        return $result;
    }

    /**
     * Gets the cache filename
     *
     * @param string $cacheId
     * @return string Returns the actual filename of the cache
     */
    private function getCacheFilename($cacheId) {
        return $this->cacheDirectory.$cacheId.'.cache';
    }

    /**
     * Gets all dir contents from the cache directory
     *
     * @return array A list with filenames
     */
    private function getCacheDirContents() {
        $ignoreArray = array('.', '..', '.svn');
        $array = array();

        $rootResource = opendir($this->cacheDirectory);
        while (($child = readdir($rootResource)) !== false) {
            if(!in_array($child, $ignoreArray)) {
                $array[] = $child;
            }
        }

        return $array;
    }

    /**
     * Checks whether we can save and retrieve caches
     *
     * @see cacheManager::checkIsEnabled()
     * @param string $saveDirectory The directory that we want to use as cache
     */
    public function checkIsEnabled($saveDirectory='') {
        if (empty($this->isChecked)) {
            $this->isChecked = true;
            if (!empty($saveDirectory)) {
                $this->setSaveDirectory($saveDirectory);
            }

            $this->createCacheDirectory();

            if (is_writable($this->cacheDirectory)) {
                $this->isEnabled = true;
            }

            if ($this->isEnabled !== true) {
                throw new \Exception('Directory is not writable!');
            }
        }

        return $this->isEnabled;
    }

    /**
     * Saves data into the file cache
     *
     * @see cacheManager::save()
     */
    public function save($data=false, $identifier='', $funcArgs=null, $ttl=null) {
        if (is_int($ttl)) {
            $this->_setTtl($ttl);
        }

        $cacheId = $this->_cacheId($identifier, $funcArgs);
        $writeData = serialize(array('ttl' => $this->_ttl, 'data' => $data));

        $result = file_put_contents($this->getCacheFilename($cacheId), $writeData);
        if (!empty($result)) {
            $result = true;
        }

        return $result;
    }

    /**
     * Loads (and validates) a cache entry to be loaded
     *
     * @see cacheManager::load()
     */
    public function load($identifier='', $funcArgs=null) {
        $return = false;
        $filename = $this->getCacheFilename($this->_cacheId($identifier, $funcArgs));
        if (is_readable($filename)) {
            $readData = unserialize(file_get_contents($filename));

            // Check whether the rescued cache is still valid, if not, delete it
            if (filemtime($filename) + $readData['ttl'] >= time()) {
                $return = $readData['data'];
            } else {
                $this->deleteCacheFile($filename);
            }
        }

        return $return;
    }

    /**
     * Deletes an actual cache entry
     *
     * @see cacheManager::delete()
     */
    public function delete($identifier='', $funcArgs=null) {
        $result = false;
        $filename = $this->getCacheFilename($this->_cacheId($identifier, $funcArgs));
        $result = $this->deleteCacheFile($filename);

        return $result;
    }

    /**
     * Deletes all the cache
     *
     * @see cacheManager::purgeCache()
     */
    public function purgeCache() {
        $result = false;
        $dirContents = $this->getCacheDirContents();
        if (!empty($dirContents)) {
            $result = true;
            foreach($dirContents AS $filename) {
                $this->deleteCacheFile($this->cacheDirectory.$filename);
            }
        }

        return $result;
    }

    /**
     * Executes the garbage collector
     *
     * @see cacheManager::executeGarbageCollector()
     */
    public function executeGarbageCollector() {
        return true;
    }

    /**
     * Deletes all entries that have a common identifier
     */
    public function purgeIdentifierCache($identifier='') {
        return 0;
    }
}
