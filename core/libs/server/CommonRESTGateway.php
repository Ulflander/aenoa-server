<?php

/**
 * <p>The REST Service in Aenoa System lets you select, edit and delete data of the system.</p>
 * 
 * Check out class CommonRESTProtocol for detailed documentation about REST Service features. 
 * 
 * <h3>Authentication</h3>
 * 
 * <p>Most of the time, the REST service requires a public key and a private signature.</p>
 * 
 * <p>For now, you have to contact administrator of the system and ask for your two keys :</p>
 * 
 * <p>The first one is public, there is no need to hide it. The second one is private. You should NEVER show it anywhere but in your server-side code, and you must not send this second key, but only a sha1 checksum of the key.</p>
 * 
 * <p>Once you have your keys, you can use them as this example:</p>
 * 
 * <pre>http://example.com/rest/main/ae_users/1.json?key=yourPublicKey&hash=sha1OfYourPrivateKey</pre>
 * 
 * @see CommonRESTProtocol
 */
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
		
		$this->data = App::$sanitizer->getAll('GET') ;
		
		if ( $struct != '' && $table != '' )
		{
			$this->data = array_merge( array (
				'structure' => $struct,
				'table' => $table ,
				'element' => $element ,
				'format' => $format
			), $this->data ) ;
			
			
		} else {
			$this->data = null ;
		}
		
		
		
		$this->protocol = $this->getProtocol () ;
		
		$this->callService ();
		
	}
	
	/**
	 * Concrete implementation of Gateway::getProtocol 
	 * 
	 * @see Gateway::getProtocol()
	 * @return CommonRESTProtocol A new instance of CommonRESTProtocol class
	 */
	protected function getProtocol ()
	{
		return new CommonRESTProtocol() ;
	}
	
	/**
	 * Concrete implementation of Gateway::callService
	 *  
	 * @see Gateway::callService()
	 */
	protected function callService ()
	{
		$this->protocol->setMode( $this->mode ) ;
		
		$this->protocol->setService( $this->data ) ;
		
		$this->protocol->callService() ;
	}
	
	
}


?>