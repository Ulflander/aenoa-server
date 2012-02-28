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
	 * Creates a new FileCache instance
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
	 * Get file name and path for this cache instance
	 * 
	 * @return string Path to file
	 */
	function getFilePath ()
	{
		return AE_APP_CACHE . sha1($this->getIdentifier()) . '.cache' ;
	}
	
	/**
	 * Get cache File object
	 * 
	 * @see File
	 * @return File The file object reference
	 * @throws ErrorException Throws an exception if file not opened or not created 
	 */
	function getFile ()
	{
		$f = new File ( $this->getFilePath() , true) ;
		
		if ( $f->readable() && $f->writable() )
		{
			return $f ;
		}
		
		throw new ErrorException ( 'File cache not able to retrieve file' ) ;
	}

	/**
	 * Restores data from File Cache
	 *
	 * @return FileCache Current instance for chained command on this element
	 */
	function restore ()
	{
		if ( $this->available () === false )
		{
			return $this ;
		}

		$f = $this->getFile () ;
		
		$this->set ( $f->read () ) ;

		return $this ;
	}

	/**
	 * Flush data to File cache
	 *
	 * @throws ErrorException Throws an exception if cache not written
	 */
	function flush ()
	{
		$this->checkIdentifier () ;
		
		$f = $this->getFile () ;

		if ( $f->write( $this->get () ) )
		{
			throw new ErrorException ('FileCache cannot store '. $this->_identifier . ' variables') ;
		}
	}

}

?>
