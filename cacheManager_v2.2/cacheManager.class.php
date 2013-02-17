<?php

include('cacheManager.interface.php');
include('exceptions.class.php');

/**
 * The main cache manager class which will call the child specified cache module
 *
 * @package Cache manager
 * @version 2.2
 * @author Camilo Sperberg - http://unreal4u.com/
 * @license BSD License. Feel free to use and modify
 */
class cacheManager {
    /**
     * The version of this class
     * @var string
     */
    private $version = '2.2';

    /**
     * Holds the child object
     * @var object
     */
    private $object = null;

    /**
     * Container of all the public child methods
     * @var array
     */
    private $methods = array();

    /**
     * Time the cache is valid
     * @var int
     */
    protected $_ttl = 60;

	/**
	 * Whether to throw exceptions or not
	 * @var boolean Defaults to false
	 */
	protected $throwExceptions = true;

	/**
	 * Stores whether we have already checked that APC is enabled or not
	 * @var boolean Defaults to false
	 */
	protected $isChecked = false;

	/**
	 * Whether APC is enabled and ready to be used or not
	 * @var boolean Defaults to true
	 */
	public $isEnabled = true;

	/**
	 * Whether the class is in debug mode or not
	 * @var boolean
	 */
	public $debugMode = false;

    /**
     * Constructor, initializes the object
     *
     * @throws versionException If minimum PHP version is not met, this exception will be thrown
     * @throws cacheException If some functional problem ocurred, a cacheException will be thrown
     */
    public function __construct() {
        if (version_compare(PHP_VERSION, '5.3.0', '<=')) {
            throw new versionException('This class will only work with PHP &gt;= 5.3.0');
        }

        $args       = func_get_args();
        $objectName = array_shift($args).'Cache';
        $route      = dirname(__FILE__).'/cacheTypes/'.$objectName.'.class.php';

        // If you want speed, ensure that the cache you've selected exists and delete the is_readable call
        if (is_readable($route)) {
            include_once($route);

            $rc = new \ReflectionClass($objectName);
            $this->object = $rc->newInstanceArgs($args);

            if ((!$rc->implementsInterface('cacheManagerInterface')) OR !$rc->isSubclassOf('cacheManager')) {
                $errorMessage = 'Class doesn\'t implements cacheManager and/or don\'t extends cacheManager, aborting creation';
                if ($this->throwExceptions === true) {
                    throw new cacheException($errorMessage);
                }
                trigger_error($errorMessage, E_USER_ERROR);
            }

            $rcMethods = $rc->getMethods(\ReflectionMethod::IS_PUBLIC);
            foreach($rcMethods AS $rcMethod) {
                $this->methods[] = $rcMethod->getName();
            }

            try {
                $this->object->checkIsEnabled();
            } catch (\Exception $e) {
                if ($this->throwExceptions === true) {
                    throw new cacheException($e->getMessage());
                }
            }
        } else {
            if ($this->throwExceptions === true) {
                throw new cacheException('Cache type "'.$objectName.'" does not exist');
            }
        }
    }

    /**
     * Executes the child method name
     *
     * @param string $methodName The name of the method
     * @param mixed $args The arguments to pass on to the function
     * @throws cacheException If the called method doesn't exist
     * @return mixed Returns whatever response the child object gives
     */
    public function __call($methodName, $args) {
        $return = null;

        if (in_array($methodName, $this->methods)) {
            $return = false;
            if (!$this->debugMode) {
                try {
                    if (empty($this->isChecked) OR (!empty($this->isChecked) AND !empty($this->isEnabled))) {
                        $return = call_user_func_array(array($this->object, $methodName), $args);
                    }
                } catch (\Exception $e) {
                    if ($this->throwExceptions) {
                        throw new cacheException($e->getMessage());
                    }
                }
            }
        } else {
            if ($this->throwExceptions) {
                throw new cacheException('The method "'.$methodName.'" does not exist or is not public');
            }
        }

        return $return;
    }

	/**
	 * Enabled throwing exceptions
	 *
	 * @param boolean $throwExceptionOnDisabled Pass true to enable exceptions, false otherwise
	 */
	public function throwExceptions($throwExceptions=true) {
	    $this->throwExceptions = (bool)$throwExceptions;
	}

    /**
	 * Function that creates an unique identifier based on optional arguments
	 *
	 * @param string $identifier A function name
	 * @param array $funcArgs Unique extra optional arguments
	 * @return string Returns an unique md5 string
	 */
	protected function _cacheId($identifier='', $funcArgs=null) {
		// Any empty value (0, NULL, false) will be converted to an empty array
		if (empty($funcArgs)) {
			$funcArgs = array();
		}

		// If we have a non-array object, convert it to a serializable array
		if (!is_array($funcArgs)) {
		    $funcArgs = array($funcArgs);
		}

		// Returning the unique hash
		return $identifier.'-'.md5($identifier.serialize($funcArgs));
	}

	/**
	 * Enabled debug mode (make this class a bit useless, but useful for testing)
	 *
	 * @return boolean Returns always true
	 */
	public function enableDebugMode() {
	    $this->debugMode = true;
	    return $this->debugMode;
	}

	/**
	 * Disables debug mode and make this class work again
	 *
	 * @return boolean Returns always false
	 */
	public function disableDebugMode() {
	    $this->debugMode = false;
	    return $this->debugMode;
	}

	/**
	 * Sets the total time to live for a cache
	 *
	 * @param int $ttl The total time to live setting
	 * @return int Returns what is just set
	 */
	protected function _setTtl($ttl=60) {
		return $this->_ttl = $ttl;
	}

	/**
	 * Returns the current version
	 */
	public function getVersion() {
	    return $this->version;
	}
}
