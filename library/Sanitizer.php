<?php

/**
 * <p>Aenoa Server santizer</p>
 *
 * <p>Sanitizer</p>
 *
 * @see App
 * @see App::getSanitizer
 */
class Sanitizer extends AeObject {

	/**
	 * Sanitized POST parameters
	 *
	 * @var array
	 */
	public $POST = array () ;

	/**
	 * Sanitized GET parameters
	 *
	 * @var array
	 */
	public $GET = array () ;

	/**
	 * Does sanitizer supports ':' char in GET parameters
	 *
	 * @var boolean
	 */
	public $supportArrayKeys = false ;

	// Rest method
	private $_restMethod ;

	// Is rest method valid
	private $_restMethodValid ;

	/**
	 * <p>Creates a new Sanitizer</p>
	 *
	 * <p>At construct, GET and POST values are retrieved from PHP globals, then they are sanitized</p>
	 *
	 * <p>Then sanitized POST and GET value are available throw common methods</p>
	 */
	function __construct ()
	{
		
		// This is used for normal web HTML access
		$this->retrieveParams ( $_GET , $this->GET ) ;
		$this->retrieveParams ( $_POST , $this->POST ) ;
		
		// This is used for APIs
		$this->_restMethod = ake ('REQUEST_METHOD', $_SERVER) ? $_SERVER['REQUEST_METHOD'] : 'GET' ;
		
		$this->_restMethodValid = in_array($this->_restMethod, array ('PUT', 'POST', 'GET', 'HEAD', 'DELETE', 'OPTIONS' ) );
		
	}

	/**
	 * <p>Check if a param exists in given entry (GET or POST)</p>
	 *
	 * @param string $prop Entry to check (GET or POST)
	 * @param string $key Key of param to test
	 * @return boolean True if param exists, flase otherwise
	 */
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

	/**
	 * Alias of Sanitizer::exists
	 *
	 * @see Sanitizer::exists
	 */
	function has ( $prop , $key )
	{
		return $this->exists($prop, $key) ;
	}
	
	/**
	 * <p>Returns value of a param of an entry, if exists, false otherwise</p>
	 *
	 * @param string $prop Entry to check (GET or POST)
	 * @param string $key Key of param to test
	 * @return mixed Value of the param if exists, false otherwise
	 */
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