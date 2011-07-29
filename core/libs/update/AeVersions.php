<?php

class AeVersions {
	
	
	const UPDATE_ACF_PACKAGE = 'acf' ;
	
	const UPDATE_AJSF_PACKAGE = 'ajsf' ;
	
	const UPDATE_AESERVER_PACKAGE = 'aenoaserver' ;
	
	const UPDATE_DEPENDENCIES_PACKAGE = 'static' ;
	
	private $packages = array () ;
	
	private $_hasUpdate = false ;
	
	function __construct () {
		
		// TODO: change this to an autodetection system of package paths : for now, packages are all in parent of ROOT
		$trueRoot = setTrailingDS( dirname(ROOT) );
		
		$this->packages = array (
			self::UPDATE_ACF_PACKAGE => new AeVersionedPackage(self::UPDATE_ACF_PACKAGE,$trueRoot .'acf' ),
			self::UPDATE_AESERVER_PACKAGE => new AeVersionedPackage(self::UPDATE_AESERVER_PACKAGE,$trueRoot .'aenoa-server' ),
			self::UPDATE_AJSF_PACKAGE => new AeVersionedPackage(self::UPDATE_AJSF_PACKAGE,$trueRoot .'ajsf' ),
			self::UPDATE_AJSF_PACKAGE => new AeVersionedPackage(self::UPDATE_AJSF_PACKAGE,$trueRoot .'static' ),
		);
		
		$appPackage = $this->getAppPackageName () ;
		
		$this->packages[$appPackage] = new AeVersionedPackage($appPackage,ROOT);
	}
	
	function getAppPackageName ()
	{
		return urlize(Config::get(App::APP_NAME)) ;
	}
	
	function hasUpdates( $filelist )
	{
		$updates = array () ;
		foreach ($this->packages as &$pack )
		{
			if ( $pack->hasUpdate($filelist) )
			{
				$updates[] = $pack ;
			}
		}
		
		return $updates ;
	}
	
	function getVersionObject ( $name )
	{
		foreach ($this->packages as &$pack )
		{
			if ( $name === $pack->getPackageName () )
			{
				return $pack ;
			}
		}
		
		return null ;
	}
	
}

?>