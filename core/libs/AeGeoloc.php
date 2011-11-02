<?php
class AeGeoloc {

	/**
	 * Theses zones does NOT represent geography, but should represent culture..
	 * @todo: ... documentation...
	 * @var unknown_type
	 * 
	 * 
	public static $AE_ZONES = array (
		'north-america' => array ('us','ca','is'),
		'south-america' => array ('br','ar'),
		'europe' => array ('fr','ir','al','de','ad'),
		'eastern-europe' => array ( '' ) ,
		'middle-east' => array ( 'af' , 'dz' , '' )  ,
		'russia' => array ( 'ru' ) ,
		'china' => array ( 'cn'	,'tw' ),
		'korea' => array ( 'kr' ),
		'japan' => array ( 'jp' ),
		'asia' => array (''),
		'africa' => array ( 'za','ao' , ''),
	);
	
	public static $AE_GLOBAL_REGIONS = array (
		'west' => array ( 'north-america', 'europe', 'eastern-europe' ) ,
		'north-east' => array ( 'russia' ),
		'china' => array ('china'),
		'asia' => array ( 'korea', 'japan' , 'asia' ) ,
		'south-america' => array ( 'south-america' ) ,
	) ;
	
	 */
	
	static private $_data = array () ;
	
	static private $_usable = false ;
	
	static private $_propablyValid = false ;
	
	function __construct ()
	{
		// If data not empty, another instance of AeGeoloc yet created data, so we don't have to reru it
		if ( empty ( self::$_data ) )
		{
			// Let's try using globals (Apache server)
			// 
			if ( array_key_exists ( 'GEOIP_COUNTRY_CODE' , $_SERVER ) )
			{
				$this->__getGeoFromGlobals () ;
			// Let's try using GeoIp PHP extension
			} else if ( function_exists ( 'geoip_country_code_by_name' ) )
			{
				$this->__getGeoFromExt () ;
			}
			
			// Try to check validity of country by comparing with browser accepted languages
			// If we don't have any geoloc data (using methods above: global and PHP extension) 
			// then we'll try to deduct a country based on the browser language
			$this->__getValidity () ;
			
			if ( array_key_exists ('country', self::$_data ) )
			{
				$this->__getZone () ;
				self::$_usable = true ;
			}
		}
	}
	
	function isProbablyValid ()
	{
		return self::$_probablyValid ;
	}
	
	function getZone ()
	{
		if ( self::$_usable )
		{
			return self::$_data['zone'] ;
		}
		return false ;
	}
	
	function getCountry ()
	{
		if ( debuggin() && empty(self::$_data) )
		{
			return 'fr';
		} else if (empty(self::$_data))
		{
			return 'en';
		}
		return self::$_data['country'] ;
	}
	
	function getCoords ()
	{
		if ( self::$_usable && array_keys_exists(array('latitude','longitude'),self::$_data))
		{
			return array (
				'latitude' => $this->$_data['latitude'],
				'longitude' => $this->$_data['longitude'],
			);
		}
		return false ;
	}
	
	function getCity ()
	{
		if ( self::$_usable && array_key_exists('city',self::$_data))
		{
			return self::$_data['city'];
		}
		return false ;
	}	
	
	private function __getGeoFromGlobals ()
	{
		self::$_data['country'] = strtolower($_SERVER['GEOIP_COUNTRY_CODE']) ;
		self::$_data['city'] = @$_SERVER['GEOIP_CITY'] ;
		self::$_data['country_name'] = @$_SERVER['GEOIP_COUNTRY_NAME'] ;
		self::$_data['latitude'] = @$_SERVER['GEOIP_LATITUDE'] ;
		self::$_data['longitude'] = @$_SERVER['GEOIP_LONGITUDE'] ;
	}
	
	private function __getGeoFromExt ()
	{
		$ip = $_SERVER['REMOTE_ADDR'] ;
		if ( geoip_country_code_by_name($ip) !== false )
		{
			self::$_data['country'] = strtolower(geoip_country_code_by_name($ip)) ;
			self::$_data['country_name'] = @geoip_country_name_by_name ($ip) ;
		}
	}
	
	private function __getValidity ()
	{
		$lang = array_shift ( explode ( ';' , $_SERVER['HTTP_ACCEPT_LANGUAGE'] ) ) ;
		
		if ( strpos ( $lang,  ',' ) !== false )
		{
			$lang = substr ( $lang , 0 , strpos($lang,',') ) ;
		}
		
		$country =  CountryHelper::langToCountry($lang) ;
		
		if ( $country !== false )
		{
			if ( ! empty ( self::$_data ) && $country == self::$_data['country'] )
			{
				self::$_propablyValid = true ;
			} else if ( empty ( self::$_data ) )
			{
				self::$_data['country'] = $country ;
			}
		}
	}
	
	private function __getZone ()
	{
	}
	
}


?>