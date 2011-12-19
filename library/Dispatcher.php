<?php

/**
 * The dispatcher class will automatically try to dispatch the good url to the good action. It's fully automatic. However you can reroute some URLs to others URLs
 * 
 * For the following examples, we will assume a query coming to our application located at "http://www.example.com/". 
 * 
 * Pre-dispatch:
 * 
 * 
 * 	- Let's consider a query "http://www.example.com/token1/token2/token3"
 * 	- Using the .htaccess directives, the query is rewritten to index.php?query=token1/token2/token3
 * 	- index.php loads configuration and call App::start () ;
 * 	- <App::start> () initializes application depending on configuration, then call <Dispatcher::dispatch> ()
 * 	- <Dispatcher::dispatch> () breaks query into simple tokens, in this first example the tokens will be "token1", "token2", "token3"
 * 	- Dispatcher will dispatch depending on the first token:
 * 	- If token is "rest" then it will load the REST system
 * 	- If token is "api" then it will load the Aenoa API system
 * 	- If token is "dev" then it will load the Aenoa Dev Kit system
 * 	- If any other token, Dispatcher will check controllers and pages
 * 
 * 
 * <Controller> dispatch:
 * 
 * The full query is "http://www.example.com/catalog/product/23".
 * 
 * 
 * 	- For our query we will have these three tokens:
 * 	- "catalog" — the first one
 * 	- "product" — the second one
 * 	- "23" — the last one
 * 	- We found a CatalogController class in app/controllers folder. Let's try to dispatch it
 * 	- Dispatcher::dispatch calls Controller::requireController and give to this method our broken query : a controller name (catalog), an action name (product), and a parameter (23)
 * 	- Controller::requireController check in "CatalogController" if a method exists with the name "product", and if this method is public. (A method is considered to be public if it is really public in class, and if name does not start by an underscore (e.g. catalog/_product would not be dispatched, even is "_product" method is declared as public in "CatalogController")
 * 	- Controller::requireController () then checks for a corresponding model (should be "CatalogModel" class) in your custom models
 * 	- The controller method "product" is then executed, and a view is automatically or manually created depending on the will of the programmer
 * 	- The following controller names must not be used in any of app custom controllers, as they are system controllers: Database, Maintenance, File, UserCore, Do, Common. Consider extending these controllers to have the benefit of pre-packaged actions for data, users, system management.
 * 
 * <Webpage> dispatch:
 * 
 * 
 * 	- If first token is unknown and a webpage can be found, then this webpage will be loaded
 * 	- Let's assume a query "catalog/services/terms.html" that is a webpage
 * 	- if a file exists in folder "/app/webpages/catalog/services" and is named "terms.html", this webpage will be rendered
 * 	- If token is not found, a 404 error is sent
 * 
 *  
 * Special queries:
 * 
 * 
 * 	- The following queries: "index.html" and "" (empty) will always route to "HomeController::index" if existing, to a "app/webpages/index.html" webpage otherwise, and if this last one is not found, to a 404 error message
 * 	- The query "phpinfo" will display the <php_info> result (only in debuggin mode)
 * 
 * 
 * Manual dispatch:
 * 
 * If you want to manage yourself dispatching of urls, 
 * disable auto dispatching using Dispatcher::unactivate () ;
 * This operation is definitive, but if you have to use later the <Dispatcher::dispatch> method,
 * then use the <Dispatcher::forceDispatch> method or the <Dispatcher::dispatchThis> method.
 *
 * See Also:
 * <QueryString>, <App>, <App::getQueryString>, <Controller>, <Webpage>, <RESTGateway>, <Gateway>, <AenoaRights>
 */
class Dispatcher {

	static private $_more = array();
	static private $_done = false;
	static private $_activated = true;

	/**
	 * Unactivates dispatching using Dispatcher::dispatch method
	 */
	static public function unactivate() {
		self::$_activated = false;
	}

	/**
	 * Activates dispatching using Dispatcher::dispatch method
	 */
	static public function activate() {
		self::$_activated = true;
	}

	/**
	 * Force dispatch of the initial query, even if Dispatcher is unactivated
	 */
	static public function forceDispatch() {
		self::_dispatch(App::getQueryString());
	}

	/**
	 * Dispatch the query
	 * 
	 * Will trigger an error if headers has been already sent
	 * 
	 * @see App::getQueryString
	 */
	static public function dispatch() {
		if (headers_sent() ) {
			if ( debuggin () )
			{
				new ErrorException( 'Dispatcher::dispatch cannot be called after headers sent' ) ;
			} else {
				App::do500( _('Dispatch unavailable because of a previous error probably') ) ;
			}
		}
		
		if (self::$_activated) {
			self::_dispatch(App::getQueryString());
		}
	}

	/**
	 * Dispatch the given $query query
	 * 
	 * Will NOT trigger any error if headers has been already sent
	 * 
	 * @param QueryString The query to dispatch, as query string
	 */
	static public function dispatchThis(QueryString &$query) {
		self::_dispatch($query, true);
	}

	/**
	 * Concrete method to dispatch the query
	 * 
	 * @param $query QueryString The query to dispatch
	 * @param $redispatch Dispatch even if yet dispatched another query
	 */
	static private function _dispatch(QueryString &$query = null, $redispatch = false) {
		// If dispatch yet done, we return
		if (self::$_done == false) {
			self::$_done = true;
		} else if ($redispatch == false) {
			return false;
		}

		if ($query == null) {
			$query = new QueryString('index.html');
		}
		
		// No query, then try to check home
		if ($query->count() == 0 || $query->getAt(0) == 'index.html') {
			if (Controller::requireController('Home', 'index') == true) {
				$query->reset('home/index');
			} else {
				$query->reset('index.html');
			}
			// Simple PHP info
		} else if ($query->getAt(0) == 'phpinfo' && debuggin()) {
			phpinfo();
			App::end();
		}


		// Get main token
		$token = $query->getAt(0);

		$raw = $query->raw();

		// Number of parameters in query
		$c = $query->count();

		// Check rights for this query
		if (AenoaRights::hasRightsOnQuery($query->raw()) == false) {
			App::do401('Permission denied');
		}

		// And dispatch
		switch (true) {
			// For DB access
			case $token == QueryString::DB_TOKEN:
				if ($c == 3) {
					$query->setAt(3, 'index');
				}
				if ($query->count() >= 4
					&& App::hasDatabase($query->getAt(1))
					&& Controller::requireController('Database', $query->getAt(3))) {
					Controller::launchController(
						'Database', $query->getAt(3), $query->getAt(4), array(
						'databaseID' => $query->getAt(1),
						'table' => $query->getAt(2)
						), $query->getFrom(5)
					);
				}
				break;

			// For REST API access
			case $token == QueryString::REST_TOKEN:
				if ($c > 2) {
					$gateway = new RESTGateway ();
				} else {
					App::do404('No such service available');
				}
				break;

			// For dev kit access
			case $token == QueryString::DEV_TOKEN:
				if (App::getUser()->isGod() && debuggin()) {
					if (file_exists(AE_SERVER . 'dev-kit' . DS . 'devkit-bootstrap.php')) {
						require_once(AE_SERVER . 'dev-kit' . DS . 'devkit-bootstrap.php');
					} else {
						App::do404(_('Dev Kit not installed'));
					}
					break;
				}
				App::do401(_('Attempt to access dev kit in Production mode'));
				break;

			// For Services access
			case $token == QueryString::SERVICES_TOKEN:
				$gateway = new Gateway ();
				break;

			// Controller access
			case $c >= 1 && Controller::requireController($token, $query->getAt(1, 'index')) == true:
				self::_launchController($token, $query->getAt(1, 'index'), $query->getAt(2), array(), $query->getFrom(3));
				break;

			// Webpages access
			case Webpage::webpageExists(str_replace('/', DS, $query->raw())) == true:
				self::_applyWebpage(array($query->raw()));
				break;

			// Default : no pattern found, run 404
			default:
				App::do404(_('No dispatch available'));
		}

		// All done, web app can end
		App::end();
	}

	static private function _launchController($controller, $action, $mainParameter = null, $controllerParams = array(), $othersParams = array()) {
		Controller::launchController($controller, $action, $mainParameter, $controllerParams, $othersParams);
	}

	static private function _applyWebpage($parameters) {
		$page = new Webpage($parameters[0], @$parameters[1], false);
		if ($page) {
			$page->render();
		}
		App::end();
	}

}

?>