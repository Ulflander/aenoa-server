<?php



class CommonRESTGateway extends Gateway {
	
	
	// REST mode : GET, POST, PUT or DELETE, HEAD, OPTIONS
	protected $mode ;
		
	
	function __construct ()
	{
		if ( Config::get(App::API_REQUIRE_KEY) === true )
		{
			$result = false ;
			if ( App::$sanitizer->exists( 'GET' , 'key' ) && App::$sanitizer->exists( 'GET' , 'hash' ) )
			{
				$db = App::getDatabase('main');
				$key = $db->findFirst( 'ae_api_keys' , array('public'=>App::$sanitizer->get('GET','key') ) ) ;
				
				if ( !empty($key) && App::$sanitizer->get('GET','hash') == sha1( $key['private'] ) )
				{
					$result = true ;
				}
			}
			
			if ( $result == false )
			{
				App::do401('REST API Authentication failure') ;
			}
		}
		
		if ( !App::$sanitizer->isRESTMethodValid ())
		{
			App::do403 ( 'REST Method is not valid' ) ;
		}	
		
		$this->mode = App::$sanitizer->getRESTMethod() ;
		
		// A request should be api/structure/table.format or api/structure/table/element.format
		$request = explode('/',App::getQuery()) ;
		
		// We remove api/
		array_shift($request) ;
		
		$table = '' ;
		$struct = '' ;
		$format = 'json' ;
		$element = '' ;
		
		$c = count($request);
		switch ($c)
		{
			case $c == 2 && strpos($request[1],'.') !== false:
				$struct = $request[0] ;
				list($table, $format) = explode('.',$request[1]);
				$element = '' ;
				break;
				
			case $c == 3 && strpos($request[2],'.') !== false:
				$struct = $request[0] ;
				$table = $request[1] ;
				list($element, $format) = explode('.',$request[2]);
				break;
		}
		
		if ( $struct != '' && $table != '' )
		{
			$this->data = array (
				'structure' => $struct,
				'table' => $table ,
				'element' => $element ,
				'format' => $format
			);
		} else {
			$this->data = null ;
		}
		
		$this->protocol = $this->getProtocol () ;
		
		$this->callService ();
		
	}
	
	protected function getProtocol ()
	{
		return new CommonRESTProtocol() ;
	}
	
	
	protected function callService ()
	{
		$this->protocol->setMode($this->mode) ;
		
		$this->protocol->setService( $this->data ) ;
		
		$this->protocol->callService() ;
	}
	
	
}


?>