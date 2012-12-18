<?php

define('U4U_DB_UPDATER', 'Automatic-DB-Updater_v0.1/db_updater.class.php');
define('U4U_CACHEMANAGER', 'cacheManager_v2.1/cacheManager.class.php');
define('U4U_CSSTACKER', 'CSStacker_v1.4/csstacker.class.php');
define('U4U_DB_MYSQLI', 'db-mysqli_v4.0.0/db_mysqli.class.php');
define('U4U_EXTENDED_PGSQL', 'extended-pgsql_v1.1.0/extended_pgsql.class.php');
define('U4U_HTML_UTILS', 'HTMLUtils_v1.0/html_utils.class.php');
define('U4U_MESSAGESTACK', 'messageStack_v1.0.2/messageStack.class.php');
define('U4U_PAGINATOR', 'mysql-paginator_v1.1/paginator.class.php');
define('U4U_PID', 'pid_v1.2/pid.class.php');
define('U4U_RUTVERIFIER', 'rutverifier_v1.1/rutverifier.class.php');

/*
 * With this function, you will be able to load my classes as:
 *
 * $css = new csstacker();
 */
function u4u_autoload_handler($class) {
    $return = false;
    if (is_readable(dirname(__FILE__).'/'.constant('U4U_'.strtoupper($class)))) {
        include(dirname(__FILE__).'/'.constant('U4U_'.strtoupper($class)));
        $return = true;
    }

    return $return;
}
spl_autoload_register('u4u_autoload_handler');
