<?php

/*

	Class: APCCache

	APC cache concrete engine

	Check out http://www.php.net/manual/en/ref.apc.php

	See Also:
	<CacheBase>

 */
class APCCache extends CacheBase {

	/**
	 * Creates a new APCCache instance
	 * 
	 * @param string $identifier [Optional] Identifier is used by concrete cache for storage identification.
	 * @param boolean $localized [Optional] Does cache depends on localization 
	 * @param float $tl [Optional] Cache time limit in minute
	 */
	function __construct ( $identifier = null , $localized = false , $tl = 10 )
	{
		parent::__construct($identifier, $localized, $tl) ;
	}
	
	/**
	 * Check if APC cache is available.
	 *
	 * APCCache requires to be available two things : the function 'apc_fetch' exists, and CacheBase has a valid identifier.
	 *
	 * @return boolean True if APC caching is available, false otherwise
	 */
	function available ()
	{
		return function_exists('apc_fetch') === true && $this->checkIdentifier () === true ;
	}

	/**
	 * Restores data from APC Cache
	 *
	 * @return APCCache Current instance for chained command on this element
	 */
	function restore ()
	{
		if ( $this->available () === false )
		{
			return $this ;
		}

		$val = apc_fetch ( $this->_identifier );

		if ( $val === false )
		{
			return $this ;
		}
		
		$this->set( $val ) ;

		return $this ;
	}

	/**
	 * Flush data to APC cache
	 *
	 * @throws ErrorException Throws an exception if cache not stored
	 */
	function flush ()
	{
		if ( $this->hasChanged () )
		{
			return;
		}
		
		$this->checkIdentifier () ;

		if ( $this->hasNot () )
		{
			apc_delete( $this->_identifier ) ;

			return;
		}
		
		
		if ( apc_store($this->_identifier, $this->get() , $this->getSTimeLimit() ) === false )
		{
			throw new ErrorException ('APCCache cannot store '. $this->_identifier . ' variables') ;
		}
	}

}

?>
