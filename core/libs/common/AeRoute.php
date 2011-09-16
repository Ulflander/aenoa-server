<?php

/**
 * AeRoutes is the class that manage routes. It's used by <Dispatcher>.
 *
 * @see Dispatcher
 */
class AeRoute {

    
    private $_routes = array () ;
    
    function __construct ()
    {
	// Here will be APC process for routes
	$f = new File (AE_APP.'routes',true);
	$routes = explode("\n", $f->read() ) ;
	$f->close() ;
	
	foreach ( $routes as $route )
	{
	    $r = explode ('>',$route) ;
	    $this->_routes[trim($r[0])] = trim($r[1]) ;
	}
    }
    
    function get ( $route )
    {
	
	
	return $route ;
    }

}

?>
