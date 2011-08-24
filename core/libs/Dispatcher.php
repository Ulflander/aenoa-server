<?php

/**
 * <p>The dispatcher class will automatically try to dispatch the good url to the good action.</p>
 * 
 * <p>For the following examples, we will assume a query coming to our application located at "http://www.example.com/".</p> 
 * 
 * <h3>Pre-dispatch</h3>
 * 
 * <ul>
 * <li>Let's consider a query "http://www.example.com/token1/token2/token3"</li>
 * <li>Using the .htaccess directives, the query is rewritten to index.php?query=token1/token2/token3</li>
 * <li>index.php loads configuration and call App::start () ;</li>
 * <li>App::start () initializes application depending on configuration, then call Dispatcher::dispatch ()</li>
 * <li>Dispatcher::dispatch () breaks query into simple tokens, in this first example the tokens will be "token1", "token2", "token3"</li>
 * <li>Dispatcher will dispatch depending on the first token:</li>
 * <li>If token is "rest" then it will load the REST system</li>
 * <li>If token is "api" then it will load the Aenoa API system</li>
 * <li>If token is "dev" then it will load the Aenoa Dev Kit system</li>
 * <li>If any other token, Dispatcher will check controllers and pages</li>
 * </ul>
 * 
 * <h3>Controller dispatch</h3>
 * 
 * <p>The full query is "http://www.example.com/catalog/product/23".</p>
 * 
 * <ul>
 * <li>For our query we will have these three tokens:</li>
 * <li>"catalog" — the first one</li>
 * <li>"product" — the second one</li>
 * <li>"23" — the last one</li>
 * <li>We found a CatalogController class in app/controllers folder. Let's try to dispatch it</li>
 * <li>Dispatcher::dispatch calls Controller::requireController and give to this method our broken query : a controller name (catalog), an action name (product), and a parameter (23)</li>
 * <li>Controller::requireController check in "CatalogController" if a method exists with the name "product", and if this method is public. (A method is considered to be public if it is really public in class, and if name does not start by an underscore (e.g. catalog/_product would not be dispatched, even is "_product" method is declared as public in "CatalogController")</li>
 * <li>Controller::requireController () then checks for a corresponding model (should be "CatalogModel" class) in your custom models</li>
 * <li>The controller method "product" is then executed, and a view is automatically or manually created depending on the will of the programmer</li>
 * <li>The following controller names must not be used in any of app custom controllers, as they are system controllers : </li>
 * </ul>
 * 
 * <p>Database, Maintenance, File, UserCore, Do, Common. Consider extending these controllers to have the benefit of pre-packaged actions for data, users, system management.</p>
 * 
 * <h3>Webpage dispatch</h3>
 * 
 * <ul>
 * <li>If first token is unknown and a webpage can be found, then this webpage will be loaded</li>
 * <li>Let's assume a query "catalog/services/terms.html" that is a webpage</li>
 * <li>if a file exists in folder "/app/webpages/catalog/services" and is named "terms.html", this webpage will be rendered</li>
 * <li>If token is not found, a 404 error is sent</li>
 * </ul>
 *  
 * <h3>Special queries</h3>
 * 
 * <ul>
 * <li>The following queries: "index.html" and "" (empty) will always route to "HomeController::index" if existing, to a "app/webpages/index.html" webpage otherwise, and if this last one is not found, to a 404 error message</li>
 * <li>The query "phpinfo" will display the php_info result (only in debuggin mode)</li>
 * </ul>
 * 
 * <h3>Manual dispatch</h3>
 * 
 * <p>If you want to manage yourself dispatching of urls, 
 * disable auto dispatching using Dispatcher::unactivate () ;
 * This operation is definitive, but if you have to use later the Dispatcher::dispatch method,
 * then use the forceDispatch method or the dispatchThis method.</p>
 * 
 * 
 * @see App
 * @see Controller
 * @see RESTGateway
 * @see Gateway
 * @see Webpage
 * @see AenoaRights
 */
class Dispatcher {
	
	
	/**
	 * First token in URLs to access to core DatabaseController
	 * 
	 * Value:
	 * 'database'
	 */
	const DB_TOKEN = 'database' ;
	
	/**
	 * First token in URLs to access to Server/Services system
	 * 
	 * Value:
	 * 'api'
	 */
	const SERVICES_TOKEN = 'api' ;
	
	/**
	 * First token in URLs to access to REST service
	 * 
	 * Value:
	 * 'rest'
	 */
	const REST_TOKEN = 'rest' ;
	
	/**
	 * First token in URLs to access Dev Kit features
	 * 
	 * Value:
	 * 'dev'
	 */
	const DEV_TOKEN = 'dev' ;
	
	static private $_more = array () ;
	
	/**
	 * The query splitted by / char in dispatch
	 * @var array
	 */
	static private $_q ;
	
	static private $_done = false ;
	
	static private $_activated = true ;
	
	static private $_urls = array () ;
	
	/**
	 * First token aliases
	 */
	static private $_routes = array () ;
	
	/**
	 * Reroute an alias to a given token
	 * 
	 * @param string $alias
	 * @param string $token
	 */
	static public function route ( $alias, $token )
	{
		self::$_routes[$alias] = $token ;
	}
	
	
	/**
	 * Returns unidentified tokens in url
	 */
	static public function getMore ()
	{
		return self::$_more ;
	}
	
	/**
	 * Unactivates dispatching using Dispatcher::dispatch method
	 */
	static public function unactivate ()
	{
		self::$_activated = false ;
	}

	/**
	 * Activates dispatching using Dispatcher::dispatch method
	 */
	static public function activate ()
	{
		self::$_activated = true ;
	}
	
	/**
	 * Force dispatch of the initial query, even if Dispatcher is unactivated
	 */
	static public function forceDispatch ()
	{
		self::_dispatch ( App::$query ) ;
	}
	
	/**
	 * Dispatch the query
	 * 
	 * Will trigger an error if headers has been already sent
	 * 
	 * 
	 */
	static public function dispatch ()
	{
		if ( headers_sent () )
		{
			trigger_error ( 'Dispatcher::dispatch cannot be called after headers sent.' , E_USER_ERROR ) ;
		}
		if ( self::$_activated )
		{
			self::_dispatch ( App::$query ) ;
		}
	}
	
	/**
	 * Dispatch the given $query query
	 * 
	 * Will NOT trigger any error if headers has been already sent
	 * 
	 * 
	 */
	static public function dispatchThis ( $query )
	{
		self::_dispatch ($query , true ) ;
	}
	
	
	/**
	 * Concrete method to dispatch the query
	 * 
	 * @param $query The query to dispatch
	 * @param $redispatch Dispatch even if yet dispatched another query
	 */
	static private function _dispatch ($query = '', $redispatch = false )
	{
		// If dispatch yet done, we return
		if ( self::$_done == false )
		{
			self::$_done = true ;
		} else if ( $redispatch == false )
		{
			return false ;
		}
		
		$c = 0 ;
		if ( strlen($query) == 0 )
		{
			$q = array () ;
		} else {
			$q = explode('/',$query) ;
		}
		self::$_q = $q ;
		
		// Number of parameters in query
		$c = count($q) ;
		
		// For now, no controller neither action
		$controller = null ;
		$action = null ;
		
		// No query, then try to check home
		if ( $c == 0 || $query == 'index.html' )
		{
			if ( Controller::requireController ( 'Home' , 'index' ) == true )
			{
				self::_launchController ( 'Home' , 'index' ) ;
			} else {
				self::_applyWebpage(array('index',Config::get(App::APP_NAME))) ;
			}
			return;
		}
		
		if ( ake($q[0], self::$_routes ) )
		{
			$q[0] = self::$_routes[$q[0]] ; 
		}
		
		if ( $q[0] == 'phpinfo' && debuggin () )
		{
			phpinfo() ;
			App::end () ;
		}
	
		// Check rights for this query
		if ( AenoaRights::hasRightsOnQuery() == false )
		{
			App::do401 ('Permission denied') ;
		}
		
		// And dispatch
		switch ( true )
		{
			// For DB access
			case $q[0]==self::DB_TOKEN:
				if ( $c == 3 ) {
					$q[3] = 'index'  ;
					$c = 4 ;
				}
				if ( $c >= 4
				&& App::hasDatabase($q[1])
				&& Controller::requireController ('Database' , $q[3]))
				{
					Controller::launchController('Database', $q[3], @$q[4], array(
						'databaseID'=>$q[1],
						'table'=>$q[2]) , array_slice($q,5)
					) ;
				}
				break;
				
			// For REST API access
			case $q[0]==self::REST_TOKEN:
				if ( $c > 2 )
				{
					$gateway = new RESTGateway () ;
				} else {
					App::do404 ( 'No such service available' ) ;
				}
				break;
				
			// For Services access
			case $q[0]==self::DEV_TOKEN:
				if(App::getUser()->isGod() && debuggin() )
				{
					if ( file_exists(AE_SERVER.'dev-kit'.DS.'devkit-bootstrap.php') )
					{
						require_once(AE_SERVER.'dev-kit'.DS.'devkit-bootstrap.php');
					} else {
						App::do404 ( _('Dev Kit not installed') ) ;
					}
					break;
				}
				App::do401 ( _('Attempt to access dev kit in Production mode') ) ;
				break;
				
			// For Services access
			case $q[0]==self::SERVICES_TOKEN:
				$gateway = new Gateway () ;
				break;
				
			// Controller access
			case $c >= 1 && Controller::requireController ( $q[0] , ($c > 1 ? $q[1] : 'index' ) )== true:
				self::_launchController ( $q[0], $q[1], (@$q[2] ? $q[2] : null ) , array () ,@array_slice($q, 3) );
				break;
				
			// Webpages access
			case Webpage::webpageExists(str_replace('/',DS,$query))==true:
				self::_applyWebpage(array($query)) ;
				break;
			
			// Default : no pattern found, run 404
			default:
				App::do404 ( 'No dispatch available' ) ;
		}
		
		// All done, web app can end
		App::end () ;
	}
	
	/**
	 * 
	 * TODO: merger $mainParameter, $controllerParams, $othersParams into one or two arrays max... This is a mess !!
	 * 
	 * @param string $controller
	 * @param string $action
	 * @param string $mainParameter
	 * @param array() $controllerParams
	 * @param array() $othersParams
	 */
	static private function _launchController ( $controller, $action, $mainParameter = null , $controllerParams = array() , $othersParams = array () )
	{
		Controller::launchController($controller, $action, $mainParameter, $controllerParams, $othersParams) ;
	}

	/**
	 * 
	 * @param $parameters
	 */
	static private function _applyWebpage ( $parameters )
	{
		$page = new Webpage ( $parameters[0] , @$parameters[1] , false );
		if ( $page )
		{
			$page->render () ;
		}
		App::end () ;
	}
}

?>