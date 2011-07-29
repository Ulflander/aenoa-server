<?php


/**
 * File: Bootstrap
 * 
 * Here is Aenoa Server bootstrap
 * 
 * It only set a few default configuration values,
 * include function library,
 * define base paths as constants.
 * 
 * 
 */



/**
 * Constant: DS
 *
 * Convenient constant for DIRECTORY_SEPARATOR core constant
 */
define ( 'DS', DIRECTORY_SEPARATOR ) ;

// Define basepaths

/**
 * Constant: AE_SERVER
 * 
 * Aenoa Server root folder path
 */
define ( 'AE_SERVER', dirname(__FILE__) . DS ) ;

/**
 * Constant: AE_CORE
 *
 * Aenoa Server "core" folder path
 */
define ( 'AE_CORE', AE_SERVER . DS . 'core' . DS ) ;

/**
 * Constant: AE_LIBS
 *
 * Aenoa Server "libs" folder path
 */
define ( 'AE_LIBS', AE_SERVER . 'core' . DS . 'libs' . DS ) ;

/**
 * Constant: AE_STRUCTURES
 *
 * Aenoa Server core structures folder path (users, api keys...)
 */
define ( 'AE_STRUCTURES', AE_SERVER . 'core' . DS . 'structures' . DS ) ;

/**
 * Constant: AE_CORE_SERVICES
 *
 * Aenoa Server core services folder path
 */
define ( 'AE_CORE_SERVICES', AE_SERVER . 'core' . DS . 'services' . DS ) ;

/**
 * Constant: AE_TEMPLATES
 *
 * Aenoa Server core templates folder path
 */
define ( 'AE_TEMPLATES', AE_SERVER . 'core' . DS . 'templates' . DS ) ;

/**
 * Constant: AE_BEHAVIORS
 *
 * Aenoa Server core behaviors folder path
 */
define ( 'AE_BEHAVIORS', AE_TEMPLATES . 'behaviors' . DS ) ;

/**
 * Constant: AE_CONTROLLERS
 *
 * Aenoa Server core controllers folder path
 */
define ( 'AE_CONTROLLERS', AE_SERVER . 'core' . DS . 'controllers' . DS ) ;

/**
 * Constant: AE_PRIVATE
 *
 * Aenoa Server application private folder path (used for caches, sessions...)
 */
define ( 'AE_PRIVATE', ROOT . '.private' . DS ) ;

/**
 * Constant: AE_TMP
 *
 * Aenoa Server application temp folder path (in private folder, so not public)
 */
define ( 'AE_TMP', ROOT . '.private' . DS . 'tmp' . DS ) ;

/**
 * Constant: AE_SESS
 *
 * Aenoa Server application sessions folder path (in private folder, so not public)
 */
define ( 'AE_SESS', ROOT . '.private' . DS . 'sessions' . DS ) ;

/**
 * Constant: AE_LOGS
 *
 * Aenoa Server application logs folder path (in private folder, so not public)
 */
define ( 'AE_LOGS', ROOT . '.private' . DS . 'logs' . DS ) ;

/**
 * Constant: AE_APP_CACHE
 *
 * Aenoa Server application cache folder path (in private folder, so not public)
 */
define ( 'AE_APP_CACHE', ROOT . '.private' . DS . 'cache' . DS ) ;

/**
 * Constant: AE_APP
 *
 * Application files folder path
 */
define ( 'AE_APP', ROOT . 'app' . DS ) ;

/**
 * Constant: AE_APP_STRUCTURES
 *
 * Application dedicated structures folder path
 */
define ( 'AE_APP_STRUCTURES', AE_APP . 'structures' . DS ) ;

/**
 * Constant: AE_APP_LIBS
 *
 * Application dedicated classes folder path
 */
define ( 'AE_APP_LIBS', AE_APP . 'libs' . DS ) ;

/**
 * Constant: AE_APP_HOOKS
 *
 * Application dedicated hooks folder path
 */
define ( 'AE_APP_HOOKS', AE_APP . 'hooks' . DS ) ;

/**
 * Constant: AE_APP_PLUGINS
 *
 * Application plugin folder path
 */
define ( 'AE_APP_PLUGINS', AE_APP . 'plugins' . DS ) ;

/**
 * Constant: AE_APP_WEBPAGES
 *
 * Application webpages folder path
 */
define ( 'AE_APP_WEBPAGES' , AE_APP . 'webpages' . DS ) ;


// TODO: remove odd constants
define ( 'AE_ASSETS', AE_SERVER . DS . 'assets' . DS ) ;


/**
 * Define Aenoa copyright constants
 */
define ('AENOA_SERVER_NAME' , 'Aenoa Server Engine 1.0' ) ;
define ('AENOA_SERVER_COPY' , 'Copyright 2010-2011 Aenoa Systems' ) ;
define ('AENOA_SERVER_COPY_LINK' , '<a href="http://www.aenoa-systems.com/" title="Aenoa Systems">Copyright 2010-2011 Aenoa Systems</a>' ) ;


/**
 * Require functions library
 */
require_once ( AE_LIBS . 'functions.php' ) ;

if ( debuggin() )
{
	error_reporting(E_ALL);
} else {
	error_reporting(0);
}

/**
 * If function addAutoLoadPath does not exists, the functions.php is not valid
 * @access private
 * @return 
 */
if ( !function_exists( 'addAutoloadPath' ) )
{
	trigger_error ( 'File basics.php could not access required function addAutoLoadPath' , E_USER_ERROR ) ;
}


/**
 * TODO: refactor autoload init, $FILE_UTIL init
 * Define base paths
 */
// create Autoload directories array 
addAutoloadPath ( AE_LIBS ) ;
addAutoloadPath ( AE_CONTROLLERS ) ;

$FILE_UTIL = new FSUtil ( AE_LIBS ) ;
$folders = $FILE_UTIL->getDirsList(AE_LIBS,false);

foreach ( $folders as $v )
{
	addAutoloadPath ( $v['path'] ) ;
} 

$FILE_UTIL = new FSUtil ( ROOT ) ;
$includePaths = array ( 'controllers', 'libs' ) ;

foreach ( $includePaths as $p )
{
	if ( $FILE_UTIL->dirExists(ROOT.'app'.DS.$p) )
	{
		addAutoloadPath(ROOT.'app'.DS.$p.DS) ;
	// Required for retro compatibility / #v2remove
	} else if ( $FILE_UTIL->dirExists(ROOT.$p) )
	{
		addAutoloadPath(ROOT.$p.DS) ;
	}
}

$broker = new Broker () ;
$broker->futil = &$FILE_UTIL ;






/**
 * TODO: reduce default configuration
 */
Config::set ( App::APP_URL , retrieveContextURL () ) ;
Config::set ( App::SESS_PATH , AE_SESS ) ;
Config::set ( App::SESS_AUTO_CONNECT , true ) ;
Config::set ( App::SESS_STRING , App::TEMP_SESS_STRING ) ;
Config::set ( App::SESS_REGENERATE_ID , true ) ;
Config::set ( App::SESS_SECURITY , true ) ;
Config::set ( App::APP_ENCODING , 'utf-8' ) ;
Config::set ( App::APP_NAME , 'New Application' ) ;
Config::set ( App::APP_COPY_LINK , AENOA_SERVER_COPY_LINK ) ;
Config::set ( App::APP_PATH , ROOT ) ;
Config::set ( App::SERVER_NAME , AENOA_SERVER_NAME ) ;
Config::set ( App::SERVER_COPY , AENOA_SERVER_COPY ) ;
Config::set ( App::APP_PUBLIC_REPOSITORY , ROOT . 'public' ) ; 
Config::set ( App::APP_DEFAULT_LANG , 'en_US' ) ;
Config::set ( App::APP_LANG , 'en_US' ) ;
Config::set ( App::API_REQUIRE_KEY , true ) ;

Config::set ( App::USER_REGISTER_AUTH , false ) ;
Config::set ( App::MAILER_RETURN_TO , 'xlaumonier@gmail.com') ;
Config::set ( App::MAILER_ABUSE , 'abuse@aenoa-systems.com') ;
Config::set ( Maintenance::DUMP_CMD , '/usr/bin/mysqldump' ) ;

if ( strpos( url(), 'localhost' ) !== false || strpos( url(), '192.168.0.42' ) !== false )
{
	Config::set ( App::STATIC_SERVER , 'http://192.168.0.42/aenoa-desk/' ) ;
} else {
	Config::set ( App::STATIC_SERVER , 'http://static.aenoa-systems.com/' ) ;
}

if ( debuggin () )
{
	Config::set(App::DBS_AUTO_EXPAND , true );
} else {
	Config::set(App::DBS_AUTO_EXPAND , false );
}


?>