<?php

/**
 * ServerAuthCheck checks for get 
 */
class ServerAuthCheck {
	
	
	function __construct ()
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
	
}

?>