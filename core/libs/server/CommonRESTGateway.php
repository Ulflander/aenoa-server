<?php

/**
 * <p>The REST Service in Aenoa System lets you select, edit and delete data of the system.</p>
 * 
 * <h3>Getting started</h3>
 * 
 * <p>Here is an example of URL to access to a table in main database structure (assuming your application is located at http://example.com)</p>
 * 
 * <pre>http://example.com/rest/structure_id/table_id/identifier.format</pre>
 * 
 * <p>For example, to retrieve the user #1 of the core authentication system, formatted in JSON, we would do</p>
 * 
 * <pre>http://example.com/rest/main/ae_users/1.json</pre>
 * 
 * <h3>Let's see how Aenoa Server manage this query</h3>
 * <ul><li>REST Protocol will check for a database structure with id 'structure_id'</li>
 * <li>If found, it will check a table named 'tabled_id' in that structure</li>
 * <li>If found, it will delegate to DatabaseController the runtime of the query</li>
 * <li>Once DatabaseController has done the action, it returns data (for GET queries only)</li>
 * <li>Data is formatted into required format, and sent </li></ul>
 * 
 * <h3>Available formats</h3>
 * 
 * <p>For now, the only format is json. But implementation of new formats is easy: contact us if you require another format.</p>
 * 
 * 
 * <h3>Result codes</h3>
 * <p>Aenoa REST service uses HTTP response codes:</p>
 * 
 * 
 * <h3>Global codes</h3>
 * <ul><li>200: query is successful</li>
 * <li>401: Authentication failure</li>
 * <li>404: Required structure / table / element not found</li>
 * <li>500: System has triggered an error </li></ul>
 * 
 * <h3>POST</h3>
 * <ul><li>201: Element has been created - Successful query will redirect to the newly created resource using Location header</li></ul>
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