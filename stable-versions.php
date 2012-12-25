<?php

define('U4U_DB_UPDATER',     'Automatic-DB-Updater_v0.1/db_updater.class.php');
define('U4U_CACHEMANAGER',   'cacheManager_v2.1/cacheManager.class.php');
define('U4U_CSSTACKER',      'CSStacker_v1.4/csstacker.class.php');
define('U4U_DB_MYSQLI',      'db-mysqli_v4.0.0/db_mysqli.class.php');
define('U4U_EXTENDED_PGSQL', 'extended-pgsql_v1.1.0/extended_pgsql.class.php');
define('U4U_HTMLUTILS',      'HTMLUtils_v1.0/HTMLUtils.class.php');
define('U4U_MESSAGESTACK',   'messageStack_v1.0.2/messageStack.class.php');
define('U4U_PAGINATOR',      'mysql-paginator_v1.1/paginator.class.php');
define('U4U_PID',            'pid_v1.3/pid.class.php');
define('U4U_RUTVERIFIER',    'rutverifier_v1.1/rutverifier.class.php');

/**
 * With this little class, you will be able to easily load my classes
 *
 * If you want to instantiate my classes as:
 * $css = new csstacker();
 * $cache = new cacheManager();
 * etc...
 *
 * Then you must call this class as:
 * $u4u_autoloader = new u4u_autoloader();
 *
 * @package u4u-classes
 * @author Camilo Sperberg - http://unreal4u.com/
 */
class u4u_autoloader {
    /**
     * Container of the already included classes
     * @var array
     */
    private $includedClasses = array();

    /**
     * Registers a new autoload register
     */
    function __construct() {
        spl_autoload_register(array($this, 'includeClass'));
    }

    /**
     * Includes the actual class file
     * @param string $class
     * @return boolean Returns false or true depending on failure or success of included file
     */
    private function includeClass($class) {
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
}
