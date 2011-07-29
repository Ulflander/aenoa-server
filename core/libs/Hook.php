<?php

class Hook {
	
	private static $_paths = null ;
	
	
	public function apply ()
	{
		return ;
	}
	
	
	public function __construct ()
	{
		$args = func_get_args() ;
		
		$hookName = array_shift($args);
		
		$hooks = self::getHooks ($hookName) ;
		
		foreach ( $hooks as $hook )
		{
			include($hook);
		}
	}
	
	public static function getHooks ( $hookName )
	{
		$paths = self::getPaths () ;
		$hooks = array () ;
		
		if ( ake($hookName, $paths) )
		{
			$hooks = $paths[$hookName];
		}
		
		return $hooks ;
	}
	
	
	/**
	 * Retrieve the possible paths for hooks
	 * - Main app hooks: app/hooks
	 * - Plugins hooks: app/pluginname/hooks/
	 */
	public static function getPaths ()
	{
		if ( !is_null(self::$_paths) )
		{
			return self::$_paths ;
		}
		
		self::$_paths = self::getCache () ;
		
		return self::$_paths ;
		
	}
	
	
	public static function regeneratePathsCache ()
	{
		$paths = array () ;
		
		$paths[] = AE_APP_HOOKS ;
		
		$allPaths = array () ;
		
		global $FILE_UTIL;
		
		$dirs = $FILE_UTIL->getDirsList(AE_APP_PLUGINS) ;
		
		if ( $dirs )
		{
		
			foreach ($dirs as $dir)
			{
				if ( $FILE_UTIL->dirExists($dir['path'].'hooks'))
				{
					$paths[] = $dir['path'].'hooks' ;
				}
			}
		}
		
		foreach ( $paths as $path )
		{
			$files = $FILE_UTIL->getFilesList($path) ;
			
			foreach(  $files as $file )
			{
				if ( !ake($file['filename'],$allPaths))
				{
					$allPaths[$file['filename']] = array () ;
				}
				$allPaths[$file['filename']][] = $file['path'] ;
			}
		}
		
		$str = '<?php self::$_paths = array (' ."\n";
		foreach( $allPaths as $hookName => $paths )
		{
			$str .= "\t" . '\'' . $hookName . '\' => array ( '."\n" ;
			foreach ( $paths as $p )
			{
				$str .= "\t\t" . '\'' . $p .'\','."\n"  ;
			}
			$str .= "\t" . '), '."\n" ;
		}
		$str.= '); ?>';
		
		$f = new File ( AE_APP_CACHE . 'hooks_folders.php' , true ) ;
		if ( $f->write($str) )
		{
			self::$_paths = $allPaths ;
			$f->close () ;
			return true ;
		}
		return false ;
	}

	public static function getCache ()
	{
		if ( !debuggin () || is_file(AE_APP_CACHE . 'hooks_folders.php') )
		{
			include(AE_APP_CACHE . 'hooks_folders.php');
		} else {
			self::regeneratePathsCache () ;
		}

		return self::$_paths ;
	}
}

?>