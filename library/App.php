<?php

/**
 * <p>That's the main class of the Aenoa Server.</p>
 * 
 * <p>It initializes everything required to run a webapp.</p>
 * 
 * <p>It stores Session, Sanitizer and main tools.</p>
 * 
 * @see Session
 * 
 */
class App extends Object
{

	/*************************************************
	 * CONSTANTS THAT DEFINE CONFIGURATION
	 * 
	 * 
	 * 
	 *************************************************/
	
	/**
	 * Config identifier for session auto connect 
	 * 
	 * @var string
	 */
	const SESS_AUTO_CONNECT = 'Application.sessionAutoConnect' ;
	
	/**
	 * Security string. On webapp should have an unique session security string.
	 * 
	 * @var string
	 */
	const SESS_STRING = 'Application.sessionString' ;
	
	/**
	 * Should session regenrate session ID
	 * 
	 * @var string
	 */
	const SESS_REGENERATE_ID = 'Session.regenerateId' ;
	
	/**
	 * Enables some security enhancements
	 * 
	 * @var string
	 */
	const SESS_SECURITY = 'Session.highSecurity';
	
	/**
	 * Session save path
	 * 
	 * @var string
	 */
	const SESS_PATH = 'Session.savePath';
	
	/**
	 * Static aenoa-server repo : used to find ACF, AJSF, and others static files
	 * 
	 * @var string
	 */
	const STATIC_SERVER = 'Server.staticServerURI';
	
	/**
	 * Name of the application
	 * 
	 * @var string
	 */
	const APP_NAME = 'Application.name' ;
	
	/**
	 * Copyright of application
	 * 
	 * @var string
	 */
	const APP_COPY = 'Application.copyright' ;
	
	/**
	 * URL of application
	 * 
	 * @var string
	 */
	const APP_URL = 'Application.URI' ;
	
	/**
	 * Link for copyright
	 * 
	 * @var string
	 */
	const APP_COPY_LINK = 'Application.copyLink' ;
	
	/**
	 * Filesystem path to root of the application
	 * 
	 * @var string
	 */
	const APP_PATH = 'Application.path' ;
	
	/**
	 * App encoding
	 * 
	 * @var string
	 */
	const APP_ENCODING = 'Application.encoding' ;
	
	/**
	 * App language
	 * 
	 * @var string
	 */
	const APP_LANG = 'Application.lang' ;
	
	/**
	 * App default language
	 * 
	 * @var string
	 */
	const APP_DEFAULT_LANG = 'Application.defaultLang' ;
	
	/**
	 * Public folder for downloaded files
	 * 
	 * @var string
	 */
	const APP_PUBLIC_REPOSITORY = 'Application.publicRepository' ;
	
	/**
	 * Login cookie name
	 * 
	 * @var string
	 */
	const APP_COOKIE_NAME = 'Application.cookieName' ;
	
	/**
	 * Login cookie domain
	 * 
	 * @var string
	 */
	const APP_COOKIE_DOMAIN = 'Application.cookieDomain' ;
	
	/**
	 * Main FTP connection parameters
	 * 
	 * @var string
	 */
	const APP_FTP = 'Application.ftp' ;
	
	/**
	 * Tracker id
	 * 
	 * @var string
	 */
	const APP_GG_TRACKER = 'Application.googleTracker' ;
	
	/**
	 * Name of Server engine (should be Aenoa Systems or related)
	 * 
	 * @var string
	 */
	const SERVER_NAME = 'Application.serverName' ;
	
	/**
	 * Copyright of Server engine
	 * 
	 * @var string
	 */
	const SERVER_COPY = 'Application.serverCopyright' ;
	
	/**
	 * Main email of application
	 * 
	 * @var string
	 */
	const APP_EMAIL = 'Application.email' ;
	
	/**
	 * Contact email for application
	 * 
	 * @var string
	 */
	const APP_CONTACT_EMAIL = 'Application.contactEmail' ;
	
	/**
	 * Mailer From email
	 * 
	 * @var string
	 */
	const MAILER_EMAIL = 'Mailer.email' ;
	
	/**
	 * Mailer ReturnTo email
	 * 
	 * @var string
	 */
	const MAILER_RETURN_TO = 'Mailer.returnTo' ;
	
	/**
	 * Should mailer send IP of server (if yes, less chances for emails to be considered as spam)
	 * 
	 * @var string
	 */
	const MAILER_SEND_IP = 'Mailer.sendIp' ;
	
	/**
	 * Mailer domain
	 * 
	 * @var string
	 */
	const MAILER_DOMAIN = 'Mailer.domain' ;
	
	/**
	 * Mailer abuse email
	 * 
	 * @var string
	 */
	const MAILER_ABUSE = 'Mailer.abuse' ;
	
	/**
	 * Mailer SMTP user if using SMTP
	 * 
	 * @var string
	 */
	const MAILER_SMTP_USER = 'Mailer.smtpUser' ;
	
	/**
	 * Should Aenoa Server use core user and authentification system
	 * 
	 * @var string
	 */
	const USER_CORE_SYSTEM = 'User.core' ;
	
	/**
	 * Authorize registration for user core system
	 * 
	 * @var string
	 */
	const USER_REGISTER_AUTH = 'User.registrationAuth' ;
	
	/**
	 * Default group on registration
	 * 
	 * @var string
	 */
	const USER_REGISTER_GROUP = 'User.registrationGroup' ;
	
	/**
	 * Unique ID for the application
	 * 
	 * @var string
	 */
	const TEMP_SESS_STRING = 'he2mqV9HS5nza3sdXv7gyuk9' ;
	
	/**
	 * Name of the hosting
	 * 
	 * @var string
	 */
	const HOSTING_NAME = 'Hosting.name' ;
	
	/**
	 * Contact of hosting
	 * 
	 * @var string
	 */
	const HOSTING_CONTACT = 'Hosting.contact' ;
	
	/**
	 * AJSF root
	 * 
	 * @var string
	 */
	const AJSF_ROOT = 'Server.ajsf.root' ;
	
	/**
	 * ACF root
	 * 
	 * @var string
	 */
	const ACF_ROOT = 'Server.acf.root' ;
	
	/**
	 * Dependencies root
	 * 
	 * @var string
	 */
	const DEPS_ROOT = 'Server.dependencies.root' ;
	
	/**
	 * Avoid using a main databse for system
	 * 
	 * @var string
	 */
	const NO_DB = 'Server.nodb' ;
	
	/**
	 * Does MySQLEngine auto expand database structure
	 * 
	 * @var string
	 */
	const DBS_AUTO_EXPAND = 'Server.dbAutoExpand' ;
	
	/**
	 * Does the API require a key
	 * 
	 * @var string
	 */
	const API_REQUIRE_KEY = 'API.requireKey' ;
	
	
	
	
	
	
	
	
	/*************************************************
	 * STATIC APP VARIABLES
	 * 
	 * 
	 * 
	 *************************************************/
	
	
	
	private static $headers = array (
		'100' => '100 Continue',
		'101' => '101 Switching Protocols',
		'102' => '102 Processing',
		'122' => '122 Request-URI too long',
		'200' => '200 OK',
		'201' => '201 Created',
		'202' => '202 Accepted',
		'204' => '204 No Content',
		'205' => '205 Reset Content',
		'206' => '206 Partial Content',
		'207' => '207 Multi-Status',
		'226' => '226 IM Used',
		'300' => '300 Multiple Choices',
		'301' => '301 Moved Permanently',
		'302' => '302 Found',
		'303' => '303 See Other',
		'304' => '304 Not Modified',
		'305' => '305 Use Proxy',
		'306' => '306 Switch Proxy',
		'307' => '307 Temporary Redirect',
		'400' => '400 Bad Request',
		'401' => '401 Unauthorized',
		'402' => '402 Payment Required',
		'403' => '403 Forbidden',
		'404' => '404 Not Found',
		'405' => '405 Method Not Allowed',
		'406' => '406 Not Acceptable',
		'407' => '407 Proxy Authentication Required',
		'408' => '408 Request Timeout',
		'409' => '409 Conflict',
		'410' => '410 Gone',
		'411' => '411 Length Required',
		'412' => '412 Precondition Failed',
		'413' => '413 Request Entity Too Large',
		'414' => '414 Request-URI Too Long',
		'415' => '415 Unsupported Media Type',
		'416' => '416 Requested Range Not Satisfiable',
		'417' => '417 Expectation Failed',
		'422' => '422 Unprocessable Entity',
		'423' => '423 Locked',
		'424' => '424 Failed Dependency',
		'425' => '425 Unordered Collection',
		'426' => '426 Upgrade Required',
		'500' => '500 Internal Server Error',
		'501' => '501 Not Implemented',
		'502' => '502 Bad Gateway',
		'503' => '503 Service Unavailable',
		'504' => '504 Gateway Time-out',
		'505' => '505 HTTP Version not supported',
		'507' => '507 Insufficient Storage',
		'509' => '509 Bandwidth Limit Exceeded'
	
	);
	
	/**
	 * Main session object
	 * @var string
	 */
	public static $session ;
	
	/**
	 * Main sanitizer object
	 * @var string
	 */
	public static $sanitizer ;
	
	/**
	 * Base URL of the application
	 * @var string
	 */
	public static $appURL ;
	
	/**
	 * If a main class is given to App::start, the class instance will be $main
	 * @var string
	 */
	public static $main ;
	
	/**
	 * FileSystem Util tool
	 * @var FSUtil
	 */
	public static $futil ;
	
	/**
	 * The query as string
	 * @var string
	 */
	public static $query ;

	/**
	 * The query as QueryString instance
	 * @var QueryString
	 */
	public static $queryStr ;
	
	/**
	 * Are we in ajax context
	 * @var string
	 */
	private static $ajaxQuery = false ;
	
	private static $localContext = null ;
	
	/**
	 * One instance of App is allowed
	 * @var string
	 * @private
	 */
	private static $_instance ;
	
	/**
	 * If a main class is given to App::start, the class instance will be $main
	 * @var string
	 */
	private static $_options = array () ;
	
	/**
	 * Are we in install mode
	 * @var string
	 */
	private static $_install_mode = false ;
	
	/**
	 * Did App initialize
	 * @var string
	 */
	private static $_initialized = false ;
	
	/**
	 * References to registered databases
	 * @var string
	 */
	private static $_dbs = array () ;
	
	/**
	 * I18n instance for the application
	 */
	private static $_i18n = null ;
	
	
	
	
	
	
	
	/*************************************************
	 * GLOBAL APPLICATION RUNTIME
	 * - initializes
	 * - start
	 * - end
	 * 
	 * 
	 * 
	 *************************************************/

	/**
	 * This function should be used only for big problems that may make unstable the system.
	 */
	static function fail ( $reason )
	{
		
		if ( !headers_sent() )
		{
			header('X-Aenoa-Failure-Reason: ' .$reason ) ;
		}
		if ( defined('ROOT') && is_file(ROOT.'templates'.DS.'failure.html') )
		{
			include(ROOT.'templates'.DS.'failure.html');
		} else if ( defined('AE_TEMPLATES') )
		{
			include(AE_TEMPLATES.'failure.html');
		} else {
			echo '<h1>Core failure</h1>' ;
		}
		
		
		die () ;
	}
	/**
	 * Initialize app context method
	 */
	static function initialize ()
	{
		if ( self::$_initialized == true )
		{
			return;
		}
		
		Maintenance::check () ;
		
		self::$_initialized = true ;
	
		// Init sanitizer		
		self::$sanitizer = new Sanitizer () ;
		
		// Check if HTTP authentication needed
		HTTPAuth::check () ;
		
		// Init session
		self::$session = new Session () ;
		
		// Init session
		if ( Config::get ( self::SESS_AUTO_CONNECT ) == true )
		{
			if  ( self::$session->connect () == false )
			{
				self::do403 ( 'Session failure: ' . self::$session->lastError () ) ;
			}
			
		}
		
		if ( is_null ( self::$_i18n ) )
		{
			self::$_i18n = new I18n () ;
		}
		
		// Get the query
		if ( self::$sanitizer->exists ( 'GET' , 'query' )) 
		{
			$query = self::$sanitizer->get ( 'GET' , 'query' ) ;
		} else {
			$query = '' ;
		}

		$route = new Route () ;

		self::$query =  $route->get( $query ) ;
		
		self::$queryStr = new QueryString( self::$query ) ;
		
		// TODO: refactor this
		// Check for install mode
		if ( self::$query == 'maintenance/check-context' )
		{
			self::$_install_mode = true ;
		}
		
		
		new Log () ;
		
		// Detect if we try to get page in ajax mode (by sending an http header from JS for example
		if ( array_key_exists('HTTP_AENOA_AJAX_CONNECTION', $_SERVER ) )
		{
			self::$ajaxQuery = true ;
		// This 
		} else if ( self::$session->get('isAjax') == true )
		{
			self::$session->uset('isAjax') ;
			self::$ajaxQuery = true ;
		}
		
		// Set the APP URL
		self::$appURL = Config::get(App::APP_URL) ;
		
		// Configure local file system tool
		if ( defined ( 'ROOT' ) && self::$futil == null )
		{
			self::$futil = new FSUtil ( ROOT ) ;
		}
		
		
		if ( self::$session->has('confirmData') && strpos(strtolower(self::$query), 'common/confirm') === false )
		{
			self::cleanBotCheck () ;
		}
		
		
		if ( debuggin() )
		{
			self::noCache() ;
		}
		
		
	}
	
	/**
	 * Start the application
	 * 
	 * @param string $mainClass [optional] The main class to load. If no main class is given, then App will try to dispatch URL to corresponding webpages or controllers. Check out the Dispatcher class.
	 * 
	 * 
	 * @return 
	 */
	static final function start ( $mainClass = null )
	{
		if ( debuggin () )
		{
			new Initializer () ;
		}

		// Trigger error if App has been called yet
		if ( is_null ( self::$_instance ) )
		{
			self::$_instance = new App () ;
		} else {
			trigger_error( 'You cannot call Application::start method twice' ) ;
		}
		
		// Initialize session and databases
		self::initialize () ;
		
		// If debuggin, we avoid browser cache
		if ( debuggin () && !headers_sent() )
		{
			header('Cache-Control: no-cache');
			header('Pragma: no-cache');
		}
		
		if ( !is_null($mainClass) && class_exists( $mainClass ) && $mainClass !== false )
		{
			try {
				self::$main = new $mainClass () ;
			} catch ( Exception $error ) {
				if ( debuggin() )
				{
					die ( $error->message ) ;
				}
				die('Unable to start application.');
			}
			
		}
		
		if ( is_null(self::$main) && $mainClass !== false )
		{
			Dispatcher::dispatch () ;
		}
		
		if ( !is_null ( self::$session ) )
		{
			self::$session->close () ;
		}
	}
	
	/**
	 * Ends the application (session...), then die.
	 */
	static function end ( $die = true )
	{
		
		if ( !is_null( self::$session ) )
		{
			self::$session->close () ;
		}
		
		foreach ( self::$_dbs as &$db )
		{
			$db['engine']->close () ;
		}
		
		if ( $die )
		{
			die () ;
		}
	}
	
	
	
	
	
	/*************************************************
	 * REDIRECTS AND SPECIAL RESPONSE CODES
	 * 
	 * 
	 * 
	 *************************************************/
	
	/**
	 * Redirects to the given internal or external URL, IF HEADERS HAS NOT BEEN SENT
	 * If ajax, returns a header that should redirect the HTML page
	 */
	static function redirectGlobal ( $to = null )
	{
		Controller::shutdown () ;
		
		self::getSession()->close();
	
		sleep(1);
		
		if ( self::isAjax() && headers_sent() == false )
		{
			// If we are in ajax context, transmit it
			header ( 'X-AeServer-redirection: '.$to ) ;
		} else {
			header("Location: " . $to );
		}
		
		
		self::end(true);
	}
	
	/**
	 * Redirects to the given internal URL, IF HEADERS HAS NOT BEEN SENT
	 */
	static function redirect ( $to = null )
	{
		if ( headers_sent() == false )
		{
			if ( self::isAjax() )
			{
				// If we are in ajax context, transmit it
				self::$session->set('isAjax', true) ;
			}
			
			Controller::shutdown () ;
			
			self::$session->close(true);
		
			sleep(1);
		
			if ( strpos($to, Config::get(self::APP_URL)) === false )
			{
				$to = Config::get(self::APP_URL) . $to ;
			}
			
			self::end(false);
			
		
			// And redirect
			header( 'Location: '. $to );
			
			
			exit;
		}
			
		self::do403('Internal redirection after headers sent');
	}
	
	/**
	 * Respond given code
	 * @param int $code
	 */
	static function doRespond ( $code = '200' , $headerResponse = null , $doRender = true , $title = null, $text = null )
	{
		$code = strval($code) ;
		if ( !ake($code, self::$headers ) ) $code = '200' ;
		
		self::sendHeaderCode ( $code ) ;
		
		if ( is_null( $title ) )
		{
			$title = self::$headers[$code] ;
		}
		
		if ( is_null ( $text ) )
		{
			$text = sprintf( _('Server response: <strong>%s</strong>.'), self::$headers[$code] ) ;
		}
		
		if ( debuggin () && !is_null($headerResponse) )
		{
			$title .= ' | ' . $headerResponse ;
		}
		
		if ( $doRender )
		{
			
			$html = new Template ( AE_TEMPLATES . 'html/message.thtml' , $title ) ;
			
			if ( intval($code) > 399 )
			{
				$log = '[ERROR] Code: ' . self::$headers[$code] .' / Private response: ' . $headerResponse . ' / Public response: ' . $text   ;
				
				if ( self::getUser() && self::getUser()->isLogged() )
				{
					$log .= ' / User: ' . self::getUser()->getIdentifier() ;
				}
				
				$log .= ' / Query: ' . self::getQuery() ;
				
				$log .= ' / IP: ' . @$_SERVER['REMOTE_ADDR'] ;
				
				Log::wlog( $log ) ;
				
				$html->set ( 'message_class' , 'error' ) ;
			} else {
				$html->set ( 'message_class' , 'notice' ) ;
			}
			
			$html->useLayout = true ;
			
			$html->layoutName = 'layout' ;
			
			if ( !debuggin () || is_null($headerResponse) )
			{
				$html->set ( 'message' , $text ) ;
			} else {
				$html->set ( 'message' , $text . ' / ' . sprintf(_('Server returned: <strong>%s</strong> with code <strong>%s</strong>.'), $headerResponse, self::$headers[$code] ) ) ;
			}
			$html->render () ;
			
			self::end();
		}
	}
	
	static function sendHeaderCode ( $code, $headerResponse = null )
	{
	
		if ( headers_sent() == false )
		{
			header( 'Status: ' . self::$headers[strval($code)], false, $code ) ;
			
			if ( !is_null ( $headerResponse ) )
			{
				header ( 'X-AeServer-returned: '.$headerResponse ) ;
			}
		}
	}
	
	/**
	 * Send a 301 Moved Permanently status and redirect to URI
	 */
	static function do301 ( $uri )
	{
		if ( headers_sent() == false )
		{
			header("Status: 301 Moved Permanently", false, 301);
			
			self::end(false);

			sleep(1);
		
			header("Location: " . $uri );
		} else if ( debuggin () )
		{
			throw new ErrorException ( '301 redirection to '.$uri.' failed cause headers yet sent' ) ;
		}
		
		self::end();
		
	}
	
	/**
	 * Send a 401 Forbidden status and show a 401 page, then die.
	 */
	static function do401 ( $headerResponse = null)
	{
		if ( !is_null (self::$_instance ) )
		{
			self::$_instance->beforeError ( 401 ) ;
		}
		
		if ( !self::getUser()->isLogged() )
		{
			App::getSession()->set('Controller.responses',array ( Controller::RESPONSE_ERROR => array(_('You have to be logged to run this action'))));
			
			if (!self::isAjax())
			{
				App::getSession()->set('redirect',self::getQuery());
			}
			if ( Config::get(self::USER_CORE_SYSTEM) === true)
			{
			    self::redirectGlobal( url().'user-core/login' );
			} else {
			    self::redirectGlobal( url() ) ;
			}
			
		} else {
			self::doRespond(401, $headerResponse , true , _('Unauthorized action'), _('Server has triggered an unauthorized action.') ) ;
		}
	}
	
	/**
	 * Send a 403 Forbidden status and show a 403 page, then die.
	 */
	static function do403 ( $headerResponse = null )
	{
		if ( !is_null (self::$_instance ) )
		{
			self::$_instance->beforeError ( 403 ) ;
		}
		
		self::doRespond(403, $headerResponse , true , _('Forbidden action'), _('Server has triggered a forbidden action.') ) ;
	}

	/**
	 * Send a 503 Service Unavailable status and show a 503 page, send an email to the administrator if required, then die.
	 * 
	 * @see App::alert
	 */
	static function do500 ( $headerResponse = null , $file = null , $line = null, $info = null )
	{
		if ( !is_null (self::$_instance ) )
		{
			self::$_instance->beforeError ( 500 ) ;
		}
		
		self::alert ( $headerResponse , $file , $line , $info ) ;
		
		self::doRespond(500, $headerResponse , true , _('System error'), _('This service has emitted an error. An email has been sent to administrators. We are sorry for any inconvenience.') ) ;
	}
	
	
	
	

	/**
	 * Send a 404 Not Found status and show a 404 page, then die
	 */
	static function do404 ( $headerResponse = null )
	{
		if ( !is_null (self::$_instance ) )
		{
			self::$_instance->beforeError ( 404 ) ;
		}
		
		self::doRespond(404, $headerResponse , true , _('Page not found'), _('This page has not been found on this server.') ) ;
	}
	
	
	/**
	 * Alerts App administrator about a critic failure
	 * 
	 * @param string $message Message of failure
	 * @param string $file File in which failure has been seen
	 * @param int $line Line of file
	 * @param array $info More info, optional
	 */
	static function alert ( $message  = null , $file = null , $line = null, $info = null )
	{
		
		$mailer = new AeMail () ;
		$mailer->sendThis (
			array ( 
				'to' => Config::get(App::APP_EMAIL)  ,
				'subject' => sprintf(_('[%s] System ERROR report'), Config::get(App::APP_NAME)),
				'template' => array (
					'file'=>'email'.DS.'system-error.thtml',
					'vars'=> array (
						'filename' => $file,
						'line' => $line,
						'info' => $info,
						'response' => $message
					)
				) ,
			)
		);
		
	}
	
	
	/*************************************************
	 * DATABASES REGISTRATION
	 * 
	 * 
	 * 
	 *************************************************/
	
	
	/**
	 * [DEPRECATED] Tests if a database exists - You should now use DatabaseManager API.
	 * 
	 * @see DatabaseManager::has
	 * @param string $id
	 * @return AbstractDBEngine
	 */
	static public function hasDatabase ( $id = 'main' )
	{
		return DatabaseManager::has($id) ;
	}
	
	/**
	 * [DEPRECATED] Returns all databases - You should now use DatabaseManager API.
	 * 
	 * @see DatabaseManager::getAll
	 * @return array
	 */
	static public function getAllDBs ()
	{
		return DatabaseManager::getAll() ;
	}
	
	/**
	 * [DEPRECATED] A part of this process should be integrated into db engine classes   - You should now use DatabaseManager API.
	 * 
	 * @see DatabaseManager::connect
	 */
	static public function declareDatabase ( $id , $engine , $source, $structureFile= null, $connect = true ) 
	{
		return DatabaseManager::connect($id, $engine, $source, $structureFile, $connect) ;
	}

	/**
	 * [DEPRECATED] Returns a database by ID - You should now use DatabaseManager API.
	 *
	 * @see DatabaseManager::get
	 */
	static public function getDatabase ( $id = 'main' )
	{
		
		return DatabaseManager::get($id) ;
	}
	
	
	static public function setInitErrorCallback ( $method )
	{
		
	}
	
	static private function _onError ( $error )
	{
		
	}
	
	static function beforeError ( $error )
	{
		
	}
	
	function beforeEnd () {}
	
	
	
	
	/*************************************************
	 * COMMON GETTERS
	 * 
	 * 
	 * 
	 *************************************************/
	

	/**
	 * Set HTTP Cache Control header to no-cache value
	 */
	static function noCache ()
	{
		if ( headers_sent() == false )
		{
			header("Cache-Control: no-cache"); 
		}
	}
	
	/**
	 * Require memory limit (in Mb)
	 */
	public static function requireMemory ( $mem )
	{
		return set_memory_limit ( $mem ) ;
	}
	
	/**
	 * @return User
	 */
	public static function getUser()
	{
		return self::$session->getUser () ;
	}

	/**
	 * @return Session
	 */
	public static function getSession()
	{
		return self::$session ;
	}

	/**
	 * @return boolean
	 */
	static function getInitialized ()
	{
		return self::$_initialized ;
	}

	/**
	 * @return boolean
	 */
	static function isAjax ()
	{
		return self::$ajaxQuery ;
	}
	
	static function setAjax ( $val )
	{
		self::$ajaxQuery = $val ;
	}

	/**
	 * @return boolean
	 */
	static function isLocal ()
	{
		if ( is_null(self::$localContext) )
		{
			self::$localContext = ( strpos( url(), '://localhost' ) !== false || strpos( url(), '://192.168' ) !== false ) ;
		}
		return self::$localContext ;
	}
	
	/**
	 * @return string
	 */
	static function getQuery ()
	{
		return self::$query ;
	}

	/**
	 * @return QueryString
	 */
	static function getQueryString ()
	{
		return self::$queryStr ;
	}

	/**
	 * @return I18n
	 */
	static function getI18n ()
	{
		return self::$_i18n ;
	}
	
	/**
	 * @return Sanitizer
	 */
	static function getSanitizer ()
	{
		return self::$sanitizer;
	}
	
	
	/*************************************************
	 * BOT CHECK
	 * 
	 * 
	 * 
	 *************************************************/
	
	
	/**
	 * Static method to check if user is a bot. Init a confirm process.
	 * 
	 * @param Controller $controller The controller object where checkForBot has been called
	 * @param string $action Action to execute (url query) after check
	 */
	static public function checkForBot ( Controller &$controller , $action )
	{
		return;
		if ( self::$session->has('botChecked') )
		{
			return;
		} else {

			self::$session->set( 'confirmData' , $controller->getData () ) ;
			
			self::$session->set( 'confirmAction' , $action ) ;
			
			self::redirect( 'common/confirm' ) ;
			
		}
	}
	
	/**
	 * Static method to set that a visitor is not a bot.
	 */
	static public function isNotBot ()
	{
		self::$session->set( 'botChecked' , true ) ;
		
		self::redirect(  self::$session->get( 'confirmAction' ) ) ;
	}
	
	/**
	 * Static method to set that a visitor is a bot.
	 */
	static public function isBot ()
	{
		self::$session->reset() ;
		
		self::do403 ('I do not like bots, even mine.') ;
	}
	
	static public function cleanBotCheck ()
	{
		$post = self::$session->get( 'confirmData' ) ;
		self::$sanitizer->addTo('POST', $post );
		
		self::$session->uset( 'confirmData') ;
		self::$session->uset( 'confirmAction') ;
		self::$session->uset( 'confirmStep') ;
		self::$session->uset( 'confirmCode') ;
		self::$session->uset( 'confirmCaptcha') ;
	}
	
	
	
	
	/*************************************************
	 * MAIN APP FTP
	 * 
	 * 
	 * 
	 *************************************************/
	
	
	static private $_ftp ;
	
	/**
	 * @return Ftp
	 */
	static public function getMainFTP ()
	{
		if ( !is_null(self::$_ftp) )
		{
			return self::$_ftp ;
		}
		
		if ( Config::has(App::APP_FTP) )
		{
			$conf = Config::get(App::APP_FTP) ;
			
			if ( ake(array('host','port','user','password'),$conf) )
			{
				$ftp = new Ftp ( $conf['host'], $conf['user'], $conf['password'], $conf['port'], (ake('autoconnect',$conf) ? $conf['autoconnect'] : true ) ) ;
				
				if ( $ftp->isUsable () )
				{
					self::$_ftp = $ftp ;
					return self::$_ftp ;
				}
			}
			
		}
		
		return false ;
	}
}
?>