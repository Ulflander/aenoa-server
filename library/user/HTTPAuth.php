<?php

class HTTPAuth {
	
	static function check ()
	{
		global $FILE_UTIL ;
		$fail = false ;
		
		if ( $FILE_UTIL->fileExists('.protection') )
		{
			if ( (isset($_SERVER['PHP_AUTH_USER']) && !empty($_SERVER['PHP_AUTH_USER'])) && (isset($_SERVER['PHP_AUTH_PW']) && !empty($_SERVER['PHP_AUTH_PW'])))
			{
				if ( strip_tags($_SERVER['PHP_AUTH_USER']) == 'admin' && strip_tags($_SERVER['PHP_AUTH_PW']) == Config::get(App::SESS_STRING) )
				{
					return;
				} else {
					$fail = true ;
				}
			} else
			{
				$fail = true ;
			}
		}
		if ( $fail )
		{
			header('WWW-Authenticate: Basic realm="Not authenticated');
			App::end ();
		}
	}
	
	static function isProtected ()
	{
		global $FILE_UTIL ;
		
		return $FILE_UTIL->fileExists('.protection') ; 
		
	}
	
	static function start ()
	{
		new File (ROOT.'.protection',true);
	}
	
	static function stop ()
	{
		$f = new File (ROOT.'.protection',false);
		if ( $f->exists() )
		{
			$f->delete() ;
		}
	}
	
	
}

?>