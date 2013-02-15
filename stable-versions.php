<?php

/**
 * This file states the stable release of each class and defines the autoLoader
 *
 * @package u4u_classes
 * @author Camilo Sperberg - http://unreal4u.com/
 */

/**
 * Defines latest stable release of "Automatic database updater" class
 * @var string
 */
define('U4U_DB_UPDATER', 'Automatic-DB-Updater_v0.1/db_updater.class.php');

/**
 * Defines latest stable release of "benchmark" class
 * @var string
 */
define('U4U_BENCHMARK', 'benchmark_v0.2/benchmark.class.php');

/**
 * Defines latest stable release of "cacheManager" class
 * @var string
 */
define('U4U_CACHEMANAGER', 'cacheManager_v2.2/cacheManager.class.php');

/**
 * Defines latest stable release of "CSS Stacker" class
 * @var string
 */
define('U4U_CSSTACKER', 'CSStacker_v1.4/csstacker.class.php');

/**
 * Defines latest stable release of "Extended MySQLi" class
 * @var string
 */
define('U4U_DB_MYSQLI', 'db-mysqli_v4.0.1/db_mysqli.class.php');

/**
 * Defines latest stable release of "debugInfo" class
 * @var string
 */
define('U4U_DEBUGINFO', 'debugInfo_v0.2/debugInfo.class.php');
/**
 * Defines latest stable release of "Extended PGSQL" class
 * @var string
 */
define('U4U_EXTENDED_PGSQL', 'extended-pgsql_v1.1.0/extended_pgsql.class.php');

/**
 * Defines latest stable release of "HTML Utilities" class
 * @var string
 */
define('U4U_HTMLUTILS', 'HTMLUtils_v1.0/HTMLUtils.class.php');

/**
 * Defines latest stable release of "Message Stacker" class
 * @var string
 */
define('U4U_MESSAGESTACK', 'messageStack_v1.0.2/messageStack.class.php');

/**
 * Defines latest stable release of "MySQL paginator" class
 * @var string
 */
define('U4U_PAGINATOR', 'mysql-paginator_v1.1/paginator.class.php');

/**
 * Defines latest stable release of "PID process identifier" class
 * @var string
 */
define('U4U_PID', 'pid_v1.3/pid.class.php');

/**
 * Defines latest stable release of "RUT Verifier" class
 * @var string
 */
define('U4U_RUTVERIFIER', 'rutverifier_v1.1/rutverifier.class.php');

/**
 * With this little class, you will be able to very easily load my classes
 *
 * If you want to instantiate my classes as:
 * <ul><li>$csstacker = new csstacker();</li>
 * <li>$cacheManager = new cacheManager();</li>
 * <li>etc...</li></ul>
 *
 * Then you must call this class as:
 * <code>$u4u_autoloader = new u4u_autoloader();</code>
 *
 * This will instantiate the autoLoader: no more worries for you!
 *
 * Additionally you can also include only the files, calling includeClass of this class
 *
 * @package u4u-classes
 * @author Camilo Sperberg - http://unreal4u.com/
 */
final class u4u_autoloader {
    /**
     * Container of the already included classes
     * @var array
     */
    private $includedClasses = array();

    /**
     * Automatically load the code from the following classes on __construct
     * @var array
     */
    private $includeOnLoad = array('debugInfo');

    /**
     * Registers a new autoload register
     *
     * @param boolean $registerAutoLoader Whether we should register the autoloader on __construct or not. Defaults to true
     */
    function __construct($registerAutoLoader=true) {
        if ($registerAutoLoader === true) {
            $this->registerAutoLoader();
        }

        foreach($this->includeOnLoad AS $includeClass) {
            $this->includeClass($includeClass);
        }
    }

    /**
     * Registers the autoloader
     *
     * @return boolean Returns always true
     */
    final public function registerAutoLoader() {
        return spl_autoload_register(array($this, 'includeClass'));
    }
    /**
     * Includes the given class file if it exists and isn't already loaded
     *
     * @param string $class
     * @return boolean Returns false or true depending on failure or success of included file
     */
    final public function includeClass($class) {
        $return = false;

        $class = strtoupper($class);
        // Prevent double inclusion and check if file exists
        if (!in_array($class, $this->includedClasses) AND is_readable(dirname(__FILE__).'/'.constant('U4U_'.$class))) {
            include(dirname(__FILE__).'/'.constant('U4U_'.$class));
            $this->includedClasses[] = $class;
            $return = true;
        }

        return $return;
    }

    /**
     * Instantiates the class for us
     *
     * The usage for this method is quite easy:
     * <code>$u4uLoader = new u4u_autoloader();
     * $cacheManager = $u4uLoader->instantiateClass('cacheManager', array('apc'));</code>
     *
     * <code>$cacheManager</code> will now hold an instance of the cacheManager class
     *
     * @param string $class The class name that we wish to instantiate
     * @param array $parameters The parameters we want to pass to the constructor, in array form
     * @return object Returns the object that we want to initialize
     */
    final public function instantiateClass($class, array $parameters=null) {
        $rc = new ReflectionClass($class);
        return $rc->newInstanceArgs($parameters);
    }
}
