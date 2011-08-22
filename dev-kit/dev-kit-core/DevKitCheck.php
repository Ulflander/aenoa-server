<?php


class DevKitCheck {

	private $warnings = array () ;
	
	private $errors = array () ;
	
	private $infos = array () ;
	
	function __construct ()
	{
		$this->checkPHPVersion () ;
		
		$this->checkDevkitPath() ;
	}
	
	function getInfos ()
	{
		return $this->infos ;
	}
	
	function hasWarning ()
	{
		return ( count($this->warnings) > 0 ) ;
	}
	
	function getWarnings ()
	{
		return $this->warnings ;
	}
	
	function hasError ()
	{
		return ( count($this->errors) > 0 ) ;
	}
	
	function getErrors ()
	{
		return $this->errors ;
	}
	
	function checkPHPVersion ()
	{
		if ( version_compare("5", phpversion(), "<=") )
		{
			$this->infos[] = 'Aenoa Dev-kit uses PHP ' . phpversion() . '.' ;
		} else {
			$this->errors[] = 'Aenoa Dev-kit requires PHP version 5.' ;
		}
	}
	
	
	function checkDevkitPath ()
	{
		global $FILE_UTIL ;
		$path = $FILE_UTIL->getPath () ;
		
		if ( $FILE_UTIL->hasError () )
		{
			$errors = $FILE_UTIL->getErrors () ;
			foreach ( $errors as $error )
			{
				$this->errors[] = 'File system error : ' . $FILE_UTIL->getErrorMessage ( $error ) ;
			}
		} else {
			$this->infos[] = 'Root path ' . $path . ' is valid.' ;
		}
	}
	
	
}
?>