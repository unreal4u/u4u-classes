<?php

namespace u4u;

/**
 * If there is a problem that disrupts normal operation, a cacheException will be thrown
 *
 * @package Cache manager
 * @since 2.0
 * @author Camilo Sperberg - http://unreal4u.com/
 * @license BSD License. Feel free to use and modify
 */
class cacheException extends \Exception {}

/**
 * If the minimum PHP version isn't run, a versionException will be thrown
 *
 * @package Cache manager
 * @since 2.0
 * @author Camilo Sperberg - http://unreal4u.com/
 * @license BSD License. Feel free to use and modify
 */
class versionException extends \Exception {}
