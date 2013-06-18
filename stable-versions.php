<?php

namespace u4u;

/**
 * This file states the stable release of each class and defines the autoLoader
 *
 * @package u4u_classes
 * @author Camilo Sperberg - http://unreal4u.com/
 */

/**
 * Defines latest stable release of "benchmark" class
 *
 * @var string
 */
const BENCHMARK = 'benchmark_v0.3/benchmark.class.php';

/**
 * Defines latest stable release of "cacheManager" class
 *
 * @var string
 */
const CACHEMANAGER = 'cacheManager_v2.3/cacheManager.class.php';

/**
 * Defines latest stable release of "CSS Stacker" class
 *
 * @var string
 */
const CSSTACKER = 'CSStacker_v1.4/csstacker.class.php';

/**
 * Defines latest stable release of "Extended MySQLi" class
 *
 * @var string
 */
const DB_MYSQLI = 'db-mysqli_v4.0.1/db_mysqli.class.php';

/**
 * Defines latest stable release of "debugInfo" class
 *
 * @var string
 */
const DEBUGINFO = 'debugInfo_v1.0/debugInfo.class.php';

/**
 * Defines latest stable release of "Extended PGSQL" class
 *
 * @var string
 */
const EXTENDED_PGSQL = 'extended-pgsql_v1.1.0/extended_pgsql.class.php';

/**
 * Defines latest stable release of "HTML Utilities" class
 *
 * @var string
 */
const HTMLUTILS = 'HTMLUtils_v1.0/HTMLUtils.class.php';

/**
 * Defines latest stable release of "PID process identifier" class
 *
 * @var string
 */
const PID = 'pid_v1.3/pid.class.php';

/**
 * Defines latest stable release of "RUT Verifier" class
 *
 * @var string
 */
const RUTVERIFIER = 'rutverifier_v1.1/rutverifier.class.php';

/**
 * With this little class, you will be able to very easily load my classes
 *
 * If you want to instantiate my classes as:
 * <ul><li>$csstacker = new csstacker();</li>
 * <li>$cacheManager = new cacheManager();</li>
 * <li>etc...</li></ul>
 *
 * Then you must call this class as:
 * <code>$u4u_autoloader = new u4u\autoloader();</code>
 *
 * This will instantiate the autoLoader: no more worries for you!
 *
 * Additionally you can also include only the files, calling includeClass of this class
 *
 * @package u4u-classes
 * @author Camilo Sperberg - http://unreal4u.com/
 */
final class autoLoader {
    /**
     * Information about whether the autoloader is loaded or not
     *
     * @var boolean
     */
    private $autoLoaderLoaded = false;

    /**
     * Container of the already included classes
     *
     * @var array
     */
    private $includedClasses = array ();

    /**
     * Automatically load the code from the following classes on __construct
     *
     * @var array
     */
    private $includeOnLoad = array (
            'debugInfo'
    );

    /**
     * Registers a new autoload register
     *
     * @param boolean $registerAutoLoader
     *        	Whether we should register the autoloader on __construct or not. Defaults to true
     */
    public function __construct($registerAutoLoader = true) {
        if ($registerAutoLoader === true) {
            $this->registerAutoLoader ();
        }

        foreach ( $this->includeOnLoad as $includeClass ) {
            $this->includeClass ( $includeClass );
        }
    }

    /**
     * Destructor
     */
    public function __destruct() {
        $this->unregisterAutoLoader ();
    }

    /**
     * Registers the autoloader
     *
     * @return boolean Returns true if autoLoader could be loaded
     */
    final public function registerAutoLoader() {
        $this->autoLoaderLoaded = spl_autoload_register ( array (
                $this,
                'includeClass'
        ) );
        return $this->autoLoaderLoaded;
    }

    /**
     * Unsets the autoloader
     *
     * @return boolean Returns true if autoloader could not be unregistered, false otherwise
     */
    final public function unregisterAutoLoader() {
        if ($this->autoLoaderLoaded === true) {
            $this->autoLoaderLoaded = ! spl_autoload_unregister ( array (
                    $this,
                    'includeClass'
            ) );
        }
        return $this->autoLoaderLoaded;
    }

    /**
     * Includes the given class file if it exists and isn't already loaded
     *
     * @param string $class
     * @return boolean Returns false or true depending on failure or success of included file
     */
    final public function includeClass($class) {
        $return = false;

        $class = str_replace ( strtoupper ( __NAMESPACE__ ) . '\\', '', strtoupper ( $class ) );
        // Prevent double inclusion and check if file exists
        if (! in_array ( $class, $this->includedClasses ) and is_readable ( dirname ( __FILE__ ) . '/' . constant ( '\\' . __NAMESPACE__ . '\\' . $class ) )) {
            include (dirname ( __FILE__ ) . '/' . constant ( '\\' . __NAMESPACE__ . '\\' . $class ));
            $this->includedClasses [] = $class;
            $return = true;
        }

        return $return;
    }

    /**
     * Instantiates the class for us, will register the autoloader implicitly!
     *
     * The usage for this method is quite easy:
     * <code>$u4uLoader = new u4u\autoloader();
     * $cacheManager = $u4uLoader->instantiateClass('cacheManager', array('apc'));
     * $benchmark = $u4uLoader->instantiateClass('benchmark');</code>
     *
     * <code>$cacheManager</code> will now hold an instance of the cacheManager class
     * <code>$benchmark</code> will now hold an instance of the benchmark class
     *
     * @param string $class
     *        	The class name that we wish to instantiate
     * @param array $parameters
     *        	The parameters we want to pass to the constructor, in array form
     * @return object Returns the object that we want to initialize
     */
    final public function instantiateClass($class, array $parameters = null) {
        $this->registerAutoLoader ();
        $rc = new \ReflectionClass ( '\\' . __NAMESPACE__ . '\\' . $class );
        if (! is_array ( $parameters )) {
            $parameters = array ();
        }
        $this->unregisterAutoLoader ();
        return $rc->newInstanceArgs ( $parameters );
    }
}
