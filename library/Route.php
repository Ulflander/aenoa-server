<?php

/**
 *
 * Class: Route
 *
 * Route is the class that manage routes. It's used by <App> for <Dispatcher>.
 *
 * How to configure routes:
 *
 * First you have to create an empty file in your app folder, named "routes", without extension.
 *
 * Then write routing rules in this conf file, like the following one.
 *
 * (start code)
 * # Here is an example of routes
 * some/route/* > controller/action/*
 * something/* > controller/action/*
 * myfile.png > controller/image/myfile.png
 * (end)
 *
 * These routes will be automatically applied by <Dispatcher> on dispatch.
 * 
 * See also:
 * <App>, <App::initialize>, <Dispatcher>, <ConfDriven>
 */
class Route extends ConfDriven {

	/**
	 * Create a new Route instance
	 *
	 * Conf file is located by default in AE_APP folder and is named "routes"
	 *
	 *
	 * @param string $file Optional, only if Route instance doesn't use default conf file 
	 */
	function __construct( $file = null ) {
		parent::__construct( AE_APP . 'routes' ) ;
	}
	
	
	/**
	 * Get a route given a query
	 * 
	 * @param string $query Original query
	 * @return string Routed query
	 */
	function get($query) {
		foreach ($this->conf as $fromRoute => &$toRoute) {
			preg_match_all('|^' . str_replace('\\*', '[a-z\.0-9\-\'\*\(\)\_]{1,}', preg_quote($fromRoute)) . '$|i', $query, $m);
			if ($m && !empty($m[0])) {
				
				$tokenQuerys = explode('/', $query);
				
				$tokens = explode('/', $toRoute);
				
				$_tokens = array () ;
				
				while ( $tok = array_pop( $tokens ) )
				{
					if ( $tok === '*' )
					{
						array_unshift($_tokens, array_pop($tokenQuerys) ) ;
					} else {
						array_unshift($_tokens, $tok);
					}
				}
				
				return implode('/', $_tokens);
			}
		}
		
		return $query;
	}
	
	/**
	 * Set a new route
	 * 
	 * @param string $from Original query
	 * @param string $to Routed query
	 * @return Route Current instance for chained command on this element
	 */
	function set ( $from, $to )
	{
		$this->conf[$from] = $to ;
		
		return $this ;
	}
	
	/**
	 * Check if a custom route exists for a query
	 * 
	 * @param string $query
	 * @return boolean True if a custom route has been defined for the query, false if generated query is equal to given query 
	 */
	function has ( $query )
	{
		return $this->get($query) !== $query ;
	}

}

?>
