<?php


class DevKitProjectType {
    const AENOA = "Aenoa" ;
    
    const AENOA_PLUGIN = "Aenoa SDK Plugin" ;
	
	const WORDPRESS = "Wordpress" ;
	
	const UNKNOWN = "unknown" ;
	
	static function getAll ()
	{
		return array (
			self::AENOA => 'AENOA' ,
			self::AENOA_PLUGIN => 'AENOA PLUGIN' ,
			self::WORDPRESS => 'WORDPRESS' ,
			self::UNKNOWN => 'UNKNOWN' ,
			
		) ;
	}
}
?>