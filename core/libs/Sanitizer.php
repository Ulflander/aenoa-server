<?php


class Sanitizer {
	
	public $POST = array () ;
	
	public $GET = array () ;
	
	public $supportArrayKeys = false ;

	private $_restMethod ;
	
	private $_restMethodValid ;
	
	function __construct ()
	{
		
		// This is used for normal web HTML access
		$this->retrieveParams ( $_GET , $this->GET ) ;
		$this->retrieveParams ( $_POST , $this->POST ) ;
		
		// This is used for APIs
		$this->_restMethod = $_SERVER['REQUEST_METHOD'];
		
		$this->_restMethodValid = in_array($this->_restMethod, array ('PUT', 'POST', 'GET', 'HEAD', 'DELETE', 'OPTIONS' ) );
		
	}

	
	function exists ( $prop , $key )
	{
		if(!property_exists($this, $prop))
		{
			return false;
		}
		
		if ( is_array($key) )
		{
			foreach($key as $k )
			{
				if ( !@array_key_exists($k , $this->$prop ) ) return false ;
			}
			return true ;
		}
		
		return @array_key_exists($key , $this->$prop );
	}
	
	
	function get ( $prop , $key )
	{
		if ( $this->exists ( $prop , $key ) == true )
		{
			$arr = $this->$prop ;
			return $arr[$key] ;
		}
		
		return false ;
	}
	
	
	function reset ( $key, $arr )
	{
		$this->retrieveParams( $arr, $this->{$key} ) ;
	}
	
	function addTo ( $key, $arr )
	{
		$this->{$key} = array_merge( $this->{$key}, $arr );
	}
	
	private function retrieveParams ( $origin = null , &$dest = null )
	{
		if ( is_null($origin) )
		{
			return ;
		}
		
		foreach ( $origin as $k => $v )
		{
			$dest[$k] = dec2n($v) ;
			
			if ( $this->supportArrayKeys && strpos( $k , ':' ) !== false )
			{
				$keys = explode ( ':' , $k ) ;
				$arr = &$dest ;
				while ( !empty($keys) ) 
				{
					$key = array_shift ( $keys ) ;
					if ( !array_key_exists ( $key , $arr ) )
					{
						$arr[$key] = array () ;
					}
					if ( !is_array($arr[$key]) )
					{
						$arr = html_entity_decode($arr[$key],ENT_COMPAT,'UTF-8') ;
						break;
					} else {
						$arr = &$arr[$key] ;
					}
				}
				$arr = dec2n($v) ;
			}
		}
	}
	
	
	function getAll ( $prop )
	{
		if ( property_exists($this, $prop) )
		{
			return $this->$prop ;
		}
		
		return false ;
	}
	
	
	
	
	function isRESTMethodValid ()
	{
		return $this->_restMethodValid ;
	}
	
	function getRESTMethod ()
	{
		return $this->_restMethod ;
	}
	
	
	function setPUTasPOST ( $structure, $table ,  $format = null )
	{
		parse_str(file_get_contents('php://input'), $data);
		
		switch( $format )
		{
			case 'json':
				if ( isset($data['data'] ) )
				{
					$data = keysToFormKeys($structure, $table ,json_decode($data['data'], true)) ;
				}
				break;
				
		}
		
		$this->POST = array_merge($this->POST, $data);
	}
	
	function decodePOST ( $structure, $table , $format = null )
	{
		$data = array () ;
		switch( $format )
		{
			case 'json':
				if ( isset($this->POST['data'] ) )
				{
					$data = keysToFormKeys($structure, $table ,json_decode($this->POST['data'], true) ) ;
				}
				break;
				
		}
		$this->POST = array_merge($this->POST, $data);
	}
	
	
}








?>