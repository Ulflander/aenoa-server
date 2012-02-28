<?php

/*
	Class: CacheBase

	Base class for all caches concrete classes.

	CacheBase extends <FlushableItem> but autoflush is set to false.
	Instead, a PHP shutdown function reference is created, calling the <CacheBase::flush> method.
	See http://php.net/manual/en/function.register-shutdown-function.php for more details about PHP shutdown functions.

 */
abstract class CacheBase extends FlushableItem {

	protected $_identifier ;
	
	protected $_tl = 0 ;

	/**
	 * Creates a new CacheBase instance
	 * 
	 * @param string $identifier [Optional] Identifier is used by concrete cache for storage identification.
	 * @param boolean $localized [Optional]
	 * @param float $tl
	 */
	function __construct ( $identifier = null , $localized = false , $tl = 10 )
	{
		$this->setIdentifier($identifier, $localized ) ;

		$this->autoflush = false ;

		register_shutdown_function(array($this,'flush'));

		$this->restore() ;

		parent::__construct();
	}

	/**
	 * Set the identifier in concrete storage engine
	 *
	 * Calling this method without any paramater will generate a brand new unique identifier
	 *
	 * Using identifiers are not mandatory for concrete engines, so in some case using identifiers method may be useless.
	 * In some others cases (such as view caches) identifiers may be altered to fit to storage API.
	 *
	 * See concrete engines documentation to have more details of particular behaviors about identifiers.
	 *
	 * @param string $identifier Identifier is used by concrete cache for storage identification.
	 * @param boolean $localized
	 * @return CacheBase Current instance for chained command on this element
	 */
	function setIdentifier ( $identifier = null , $localized = false )
	{
		if ( $localized === true )
		{
			$identifier .= App::getI18n()->getCurrent() ;
		}

		$this->_identifier = $identifier ;

		return $this ;
	}

	/**
	 * Get identifier of cache
	 *
	 * @return string Identifier used by concrete cache storage engine
	 */
	function getIdentifier ()
	{
		return $this->_identifier ;
	}

	/**
	 * Check if identifier is not null
	 *
	 * @return boolean True if identifier is not null, false otherwise
	 */
	function checkIdentifier ()
	{
		
		return !is_null($this->_identifier) ;
	}

	/**
	 * Set time limit in minutes
	 *
	 * @param float $tl Time limit in minutes
	 * @return CacheBase Current instance for chained command on this element
	 */
	function setTimeLimit ( $tl )
	{
		$this->_tl = floatval($tl) ;

		return $this ;
	}

	/**
	 * Get time limits in minutes
	 *
	 * @return int  Time limit in minutes
	 */
	function getTimeLimit ()
	{
		return $this->_tl ;
	}

	/**
	 * Get time limit in seconds
	 *
	 * @return int Time limit in seconds
	 */
	function getSTimeLimit ()
	{
		return $this->_tl * 60 ;
	}

	/**
	 * Get time limit in milliseconds
	 *
	 * @return int Time limit in milliseconds
	 */
	function getMSTimeLimit ()
	{
		return $this->_tl * 60000 ;
	}

	/**
	 * Overrides <FlushableItem>
	 *
	 */
	function set($value, $check = false)
	{
		if (debuggin() || $check === true)
		{
			if (is_object($value))
			{
				
			} else if (is_array($value) )
			{
				foreach ( $value as $val )
				{
					
				}
			}
		}

		parent::set($value) ;
	}


	/**
	 * [ABTRACT] Tests if cache concrete engine is available. THIS METHOD MUST BE IMPLEMENTED IN CONCRETE CACHE ENGINE.
	 *
	 * @return boolean True if cache concrete engine is available, false otherwise
	 */
	abstract function available () ;

	/**
	 * [ABTRACT] Restores cache from cache storage. THIS METHOD MUST BE IMPLEMENTED IN CONCRETE CACHE ENGINE.
	 * 
	 */
	abstract function restore () ;
}

?>
