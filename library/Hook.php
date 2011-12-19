<?php

/**
 * Class: Hook
 *
 * Hooks are made to customize some core actions of Aenoa Server.
 *
 * For now, some hooks are available for UserCore features (login, logout, registering...).
 * More and more hooks will be available as system will slowly grow.
 * Hooks are documented in Aenoa Server documentation in <Hooks> part.
 *
 * How to catch a hook:
 *
 * For this example, we will send an email to the system administrator when a new user registers.
 * 
 * - Create a new file named "UserCoreRegistered.php" in folder "hooks" of your app
 * - Put in it the following code
 *
 * (start code)
 * <?php
 * 
 * 		// Send a mail to main administrator when a new user is registered
 *		// $args[0] is the user database id
 *		// $args[1] is the user email
 *
 *		// Create a new mail
 * 		$mailer = new AeMail () ;
 *
 *		// And send a mail
 * 		$mailer->sendThis (
 * 			array (
 *				// Send the mail to APP_EMAIL
 * 				'to' => Config::get( App::APP_EMAIL )  ,
 *				// Set the subject
 * 				'subject' => sprintf(_('[%s] New user registration'), Config::get(App::APP_NAME)),
 *				// And the content
 * 				'content' => sprintf(_('User %s is now registered in system.'), $args[1]),
 * 			)
 * 		) ;
 *
 *
 * ?>
 * (end)
 *
 * - and that's it, the file will be executed at each time "UserCoreRegistered" hook is triggered
 * 
 * How to create your own hook:
 *
 * You may want to create your own hooks in your application.
 * To do so, just create a new instance of <Hook> at the precise location of the hook.
 *
 * (start code)
 *
 * class Foo {
 *
 *		function bar ( $param )
 *		{
 *			//	Hook: FooBarHook
 *			//
 *			//	This hook is dispatched when bar method of Foo class is called
 *			//
 *			//	Parameters:
 *			//		$args[0] - [mixed] A parameter
 *
 *			new Hook ('FooBarHook' , $param ) ;
 *		}
 * }
 *
 * $foo = new Foo () ;
 * $foo->bar ( 'Hello world') ;
 * (end)
 * Don't forget to comment hook with name and parameters !
 *
 * 
 * And, in the {APP}/hooks/FooBarHook.php
 *
 * (start code)
 * <?php
 *
 * echo $args[0];
 * // Hello world
 *
 * ?>
 * (end)
 *
 *
 *
 */
class Hook {
	
	private static $_paths = null ;

	/**
	 * Create and execute a new hook
	 *
	 * @param type $hookName Name of hook
	 * @param mixed
	 */
	public function __construct ( $hookName )
	{
		$args = func_get_args() ;
		
		array_shift($args);
		
		$hooks = self::getHooks ($hookName) ;
		
		foreach ( $hooks as $hook )
		{
			include($hook);
		}
	}

	/**
	 * [STATIC] Returns hooks files by hook name
	 *
	 * @param string $hookName Name of hook
	 * @return array Array of hooks paths
	 */
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
	 * [STATIC] Retrieve the possible paths for hooks
	 *
	 * 
	 * 
	 * <ul>
	 * <li>Main app hooks: {APP_FOLDER}/hooks</li>
	 * <li>Plugins hooks: {APP_FOLDER}/{PLUGIN}/hooks/</li>
	 * </ul>
	 *
	 * @return array Array of all paths where hooks may be
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
	
	/**
	 * [STATIC] Regenerate cache file of hooks paths
	 * 
	 * @return boolean True if file has been saved, false otherwise
	 */
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

			if ( $files !== false )
			{
				foreach(  $files as $file )
				{
					if ( !ake($file['filename'],$allPaths))
					{
						$allPaths[$file['filename']] = array () ;
					}
					$allPaths[$file['filename']][] = $file['path'] ;
				}
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
			$f->close () ;
			return true ;
		}

		self::$_paths = $allPaths ;

		return false ;
	}

	/**
	 * Returns hooks paths, by reading the cache file or by regenerating the cache
	 *
	 * @return array Array of all hooks paths
	 */
	public static function getCache ()
	{
		if ( !debuggin () && is_file(AE_APP_CACHE . 'hooks_folders.php') )
		{
			include(AE_APP_CACHE . 'hooks_folders.php');
		} else {
			self::regeneratePathsCache () ;
		}

		return self::$_paths ;
	}
}

?>