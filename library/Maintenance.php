<?php

class Maintenance {
	
	const DUMP_CMD = 'Maintenance.MySQLDumpCommand' ;
	
	
	static function check ()
	{
		global $FILE_UTIL ;
		
		if ( $FILE_UTIL->fileExists('.maintenance') && ( !array_key_exists('query',$_GET) || $_GET['query'] != 'maintenance/check-context' ) )
		{
			App::doRespond(503,null,false);
			
			if ( $FILE_UTIL->fileExists(AE_APP_TEMPLATES.'maintenance.html') )
			{
				include(AE_APP_TEMPLATES.'maintenance.html');
			} else {
				include(AE_TEMPLATES.'maintenance.html');
			}
			
			App::end () ;
		}
	}
	
	static function start ()
	{
		new File (ROOT.'.maintenance',true);
	}
	
	static function stop ()
	{
		$f = new File (ROOT.'.maintenance',false);
		
		if ( $f->exists() )
		{
			$f->delete() ;
		}
	}
	
	
}

?>