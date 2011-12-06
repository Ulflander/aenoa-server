<?php

/**
 * Rout is the class that manage routes. It's used by <App> for <Dispatcher>.
 *
 * @see App
 * @see App::initialize
 * @see Dispatcher
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
			preg_match_all('|^' . str_replace('\\*', '[a-z0-9\_]{1,}', preg_quote($fromRoute)) . '$|i', $query, $m);
			if ($m && !empty($m[0])) {
				$tokenQuerys = explode('/', $query);
				$tokens = explode('/', $toRoute);
				$l = count($tokens);
				$m = count($tokenQuerys);
				$n = $l > $m ? $l : $m;

				for ($i = 0; $i < $n; $i++) {
					if ($i < $l) {
						if ($tokens[$i] == '*') {
							$tokens[$i] = $tokenQuerys[$i];
						} else {
							$tokens[$i] = $tokens[$i];
						}
					} else {
						$tokens[$i] = $tokenQuerys[$i];
					}
				}
				return implode('/', $tokens);
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
