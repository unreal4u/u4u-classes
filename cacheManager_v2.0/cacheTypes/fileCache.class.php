<?php

class fileCache extends cacheManager implements cacheManager {
    public function setThrowExceptionOnDisabled($throwExceptionOnDisabled=false) {}

    public function checkIsEnabled() {}

    public function save($data=false, $identifier='', $funcArgs=array(), $ttl=60) {}

    public function load($identifier='', $funcArgs=array()) {}

    public function delete($identifier='', $funcArgs=array()) {}

    public function purgeCache($onlyUserSpace=false) {}
}
