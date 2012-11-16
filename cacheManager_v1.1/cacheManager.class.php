<?php
/**
 * The interface that all childs must implement
 *
 * @author unreal4u
 */
interface cacheManager {
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
     * Saves a cache into memory
     *
     * @param mixed $data The data we want to save
     * @param string $identifier A unique name to use
     * @param array $funcArgs Optional extra arguments to differentiate cache options
     * @param int $ttl The time the cache will be valid
     */
    public function save($data=false, $identifier='', $funcArgs=array(), $ttl=60);

	/**
	 * Rescues a cache from memory
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
	 *
	 * @param boolean $onlyUserSpace Whether to delete only user space. Defaults to false
	 */
    public function purgeCache($onlyUserSpace=false);
}

/**
 * The main cache manager class which will call the child specified cache module
 *
 * @author unreal4u
 */
class cacheManagerClass {
    private $object  = null;
    private $methods = array();

    /**
     * Time the cache is valid
     *
     * @var int
     */
    protected $_ttl = 60;

	/**
	 * Whether to throw exception on disabled APC cache module
	 *
	 * @var boolean Defaults to false
	 */
	protected $throwExceptionOnDisabled = false;

    /**
	 * Whether APC is enabled and ready to be used or not
	 *
	 * @var boolean Defaults to true
	 */
	public $isEnabled = true;

    /**
     * Constructor, initializes the object
     */
    public function __construct() {
        $args       = func_get_args();
        $objectName = array_shift($args).'Cache';
        $route      = dirname(__FILE__).'/cacheTypes/'.$objectName.'.class.php';

        if (is_readable($route)) {
            include($route);
            $this->object = new $objectName($args);
            if (!class_implements($this->object, 'cacheManager') OR !in_array('cacheManagerClass',class_parents($this->object))) {
                throw new \CacheException('Class could not comply with minimum functionality, aborting creation');
            }
            $this->methods = get_class_methods($this->object);
        } else {
            throw new \CacheException('Class does not exist');
        }
    }

    /**
     * Executes the child method name
     *
     * @param string $methodName The name of the method
     * @param mixed $args The arguments to pass on to the function
     * @throws CacheException If the called method doesn't exist
     * @return mixed Returns whatever response the child object gives
     */
    public function __call($methodName, $args) {
        $return = null;

        if (in_array($methodName, $this->methods)) {
            try {
                $return = call_user_func_array(array($this->object, $methodName), $args);
            } catch (Exception $e) {
                throw new \CacheException($e->getMessage());
            }
        } else {
            throw new \CacheException('The method "'.$methodName.'" does not exist');
        }

        return $return;
    }

	/**
	 * Enabled throwing exceptions
	 *
	 * @param boolean $throwExceptionOnDisabled Pass true to enable exceptions, false otherwise
	 */
	public function setThrowExceptionOnDisabled($throwExceptionOnDisabled=false) {
	    $this->throwExceptionOnDisabled = (bool)$throwExceptionOnDisabled;
	}

    /**
	 * Function that creates an unique identifier based on optional arguments
	 *
	 * @param string $identifier A function name
	 * @param array $funcArgs Unique extra optional arguments
	 * @return string Returns an unique md5 string
	 */
	protected function _cacheId($identifier='', $funcArgs=array()) {
		// Any empty value (0, NULL, etc) will be converted to an array
		if (empty($funcArgs) OR !is_array($funcArgs)) {
			$funcArgs = array();
		}

		// Returning the unique hash
		return $identifier.'-'.md5($identifier.serialize($funcArgs));
	}

	/**
	 * Sets the total time to live for a cache
	 *
	 * @param int $ttl
	 */
	protected function _setTtl($ttl=60) {
		return $this->_ttl = $ttl;
	}
}
