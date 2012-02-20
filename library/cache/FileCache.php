<?php

/*

	Class: FileCache

	File cache concrete engine

	Check out http://www.php.net/manual/en/ref.apc.php

	See Also:
	<CacheBase>

 */
class FileCache extends CacheBase {

	/**
	 * Check if File cache is available.
	 *
	 * FileCache requires to be available two things : CacheBase has a valid identifier and AE_APP_CACHE folder is writable.
	 *
	 * @return boolean True if File caching is available, false otherwise
	 */
	function available ()
	{
		return $this->checkIdentifier () === true && is_writable (AE_APP_CACHE) === true ;
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
		
		$this->setAll( $val ) ;

		return $this ;
	}

	/**
	 * Flush data to APC cache
	 *
	 * @throws ErrorException Throws an exception if cache not stored
	 */
	function flush ()
	{
		$this->checkIdentifier () ;

		if ( apc_store($this->_identifier, $this->getAll() , $this->getSTimeLimit() ) === false )
		{
			throw new ErrorException ('APCCache cannot store '. $this->_identifier . ' variables') ;
		}
	}

}

?>
