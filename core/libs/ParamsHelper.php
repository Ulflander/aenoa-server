<?php

class ParamsHelper
{
	function getSystemParams ()
	{
		
		$params = array () ;
		
		
		$sanitizer = new Sanitizer () ;
		
		$this->getParam ( 'theme' , &$sanitizer->GET , &$params ) ;
		$this->getParam ( 'apps' , &$sanitizer->GET , &$params ) ;
		
		return $params ;
	}
	
	function getSystemInlineParams ()
	{
		$params = $this->getSystemParams () ;
		$arr = array () ;
		
		foreach ( $params as $k => $v )
		{
			$arr[] = $k . '=' . $v ;
		}
		
		$arr[] = 'sessid=' . App::$session->getSID () ;	
		
		return implode( '&' , $arr ) ;
		
	}
	
	private function getParam ( $key , $origin , $dest )
	{
		if ( !is_array ( $origin ) )
		{
			return;
		}
		
		if ( array_key_exists ( $key , $origin ) )
		{
			$dest[$key] = $origin[$key] ;
		}
		
	}
}

?>