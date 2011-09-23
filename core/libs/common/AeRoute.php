<?php

/**
 * AeRoutes is the class that manage routes. It's used by <Dispatcher>.
 *
 * @see Dispatcher
 */
class AeRoute {

    private $_routes = array();

    function __construct() {
	// Here will be APC process for routes
	$f = new File(AE_APP . 'routes', true);
	$routes = explode("\n", $f->read());
	$f->close();

	foreach ($routes as $route) {
	    if (strpos($route, '>') === false) {
		continue;
	    }

	    $r = explode('>', $route);
	    $this->_routes[trim($r[0])] = trim($r[1]);
	}
    }

    function get($query) {
	foreach ( $this->_routes as $fromRoute => &$toRoute) {
	    preg_match_all('|^' . str_replace('\\*', '[a-z0-9\_]{1,}', preg_quote($fromRoute)) . '$|i', $query, $m);
	    if ($m && !empty($m[0])) {
		$tokenQuerys = explode('/', $query);
		$tokens = explode('/', $toRoute);
		$l = count($tokens) ;
		$m = count($tokenQuerys) ;
		$n = $l > $m ? $l : $m ;
		
		for ( $i = 0 ; $i < $n ; $i ++ )
		{
		    if ( $i < $l )
		    {
			if ( $tokens[$i] == '*' )
			{
			    $tokens[$i] = $tokenQuerys[$i] ;
			} else {
			    $tokens[$i] = $tokens[$i] ;
			}
			
		    } else {
			$tokens[$i] = $tokenQuerys[$i] ;
		    }
		    
		}
		return implode('/',$tokens) ;
	    }
	}

	return $query ;
    }

}

?>
