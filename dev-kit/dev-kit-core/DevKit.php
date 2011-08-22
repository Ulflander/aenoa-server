<?php

/**
 * The DevKit class offer methods relative to Aenoa dev-kit management :
 * - projects
 * - backups
 * - plugins
 * 
 * and so on.
 */
class DevKit {
    
	private $futil ;
	
	private $manager ;
	
	const BACKUP_DIR = '.aenoabackup' ;
	
	const TRASH_DIR = '.aenoatrash' ;
	
	const DEVKIT_DIR = 'dev-kit' ;
	
	const PREF_FILE = '.aenoaprefs.json' ;
	
	const DOC_DIR = 'documentation' ;
	
	private $projects = array () ;
	
	private $tasks = array () ;
	
	private $recentProjects = array () ;
	
	private $devKitCoreDirectories = array ( 'dev-kit' , 'goodies' , self::BACKUP_DIR , self::TRASH_DIR ) ;
	
	/**
	 * Constructor
	 * @return 
	 */
	function __construct ()
	{
		global $FILE_UTIL, $TASK_MANAGER, $broker ;
		$this->futil = new FSUtil(dirname(ROOT)) ;
		
		$this->refreshConf () ;
	}
	
	
	function refreshTasks ()
	{
		
		$paths[] = DK_TASKS ;
		
		$dirs = $this->futil->getDirsList ( DK_TASKS ) ;
		
		foreach ( $dirs as $v )
		{
			$paths[] = $v['path'] ;
		}
		
		$dirs = $this->futil->getDirsList ( DK_PLUGINS ) ;
		
		foreach ( $dirs as $v )
		{
			$paths[] = $v['path'] ;
		}
		
		$tasks = array () ;
		
		foreach ( $paths as $path )
		{
			$files = $this->futil->getFilesList ( $path , false ) ;
			
			if ( $files )
			{
				foreach ( $files as $file )
				{
					if (array_key_exists('filename', $file) )
					{
						$tasks[$file['filename']] = $path ;
					}
				}
			}
			
		}
		
		$this->tasks = $tasks ;
	}
	
	function addMostRecentProject ( $projectName )
	{
		if ( !in_array ( $projectName , $this->recentProjects ) )
		{
			global $broker ;
			$prefs = &$broker->preferences ;
			
			if ( $projectName != $prefs->get ( 'recent_project_1' )
				&& $projectName != $prefs->get ( 'recent_project_2' ) 
				&& $projectName != $prefs->get ( 'recent_project_3' ) )
			{
				$prefs->set ( 'recent_project_3' , $prefs->get ( 'recent_project_2' ) ) ;
				$prefs->set ( 'recent_project_2' , $prefs->get ( 'recent_project_1' ) ) ;
				$prefs->set ( 'recent_project_1' , $projectName ) ;
			}
		}
	}
	
	function getRecentProjects ()
	{
		$this->refreshConf () ;
		
		return $this->recentProjects ;
	}
	
	function refreshConf ()
	{
		global $broker ;
		
		$prefs = new PHPPrefsFile ( DK_DEV_KIT . 'conf.php' , false ) ;
		
		$broker->preferences = &$prefs ;
		
		if ( $prefs->exists () )
		{
			$this->recentProjects = array (
				new DevKitProject ( $prefs->get ( 'recent_project_1' ) ) ,
				new DevKitProject ( $prefs->get ( 'recent_project_2' ) ) ,
				new DevKitProject ( $prefs->get ( 'recent_project_3' ) ) ,
			);
		}
		
	}
	
	function isTask ( $taskName , $caseSensitive = true )
	{
		if ( empty ( $this->tasks ) )
		{
			$this->refreshTasks () ;
		}
		
		if ( $caseSensitive && array_key_exists ( $taskName , $this->tasks ) == true )
		{
			return true ;
		} else if ( $caseSensitive == false )
		{
			$taskName = strtolower( $taskName ) ;
			
			foreach ( $this->tasks as $t => $p )
			{
				if ( $taskName === strtolower($t) )
				{
					return true ;
				}
			}
		}
		
		return false ;
	}
	
	function getAllTaskKeywords ()
	{
		if ( empty ( $this->tasks ) )
		{
			$this->refreshTasks () ;
		}
		
		$results = array () ;
		foreach ( $this->tasks as $t => $p )
		{
			$results = array_merge($results , explode ( ' ' , strtolower(preg_replace('/(?<=\\w)([A-Z])/', ' \\1', $t) ) ) ) ;
		}
		
		return array_unique( $results ) ;
	}
	
	function getTrueTaskName ( $taskName )
	{
		if ( empty ( $this->tasks ) )
		{
			$this->refreshTasks () ;
		}
		
		$taskName = strtolower( $taskName ) ;
			
		foreach ( $this->tasks as $t => $p )
		{
			if ( $taskName === strtolower($t) )
			{
				return $t ;
			}
		}
		
		return false ;
	}
	
	function getTaskPath ( $taskName )
	{
		if ( empty ( $this->tasks ) )
		{
			$this->refreshTasks () ;
		}
		
		if ( array_key_exists ( $taskName , $this->tasks ) == true )
		{
			return $this->tasks[$taskName] ;
		}
		return false ;
	}
	
	
	function getAllTasks ()
	{
		if ( empty ( $this->tasks ) )
		{
			$this->refreshTasks () ;
		}
		
		return $this->tasks ;
	}
	
	
	function refreshProjects ()
	{
		
		// Retrieve List of projects
		$rootFiles = $this->futil->getFilesList () ;
		$projects = array () ;
		
		// These directories are core dirs of anenoa, we don't show them as projects
		// Hidden files will be ... hidden, too.
		
		
		foreach ( $rootFiles as $file )
		{
			if ( !(empty ( $file ) ) && $file['type'] == 'dir' 
				&& $this->futil->isHiddenFile ( $file['name'] ) == false 
				&& !in_array ( $file['name'] , $this->devKitCoreDirectories ) )
			{
				$project = new DevKitProject ( $file['name'] ) ;
				$projects[$file['name']] = $project ;
			}
		}
		
		$this->projects = $projects;
	}
	
	
	
	function checkProjectName ( $projectName )
	{
		return ( $projectName != '' && !in_array ( $projectName , $this->devKitCoreDirectories ) && $this->isTask ( $projectName ) == false ) ;
	}
	/**
	 * Retrieve all projects and transform them into DevKitProject objects.
	 * 
	 * @return array An array containing all projects of dev-kit, formatted in DevKitProject objects. 
	 */
	function getAllProjects ()
	{
		if ( empty ( $this->projects ) )
		{
			$this->refreshProjects () ;
		}
		return $this->projects ;
	}
	
	function isProject ( $project )
	{
		if ( !(empty ( $project ) ) 
				&& $this->futil->isHiddenFile ( $project ) == false 
				&& !in_array ( $project , $this->devKitCoreDirectories )
				&& $this->futil->dirExists ( $project ) )
		{
			return true ;
		}
		return false ;
	}
	
	
	/**
	 * Retrieve a list of all plugins
	 * 
	 * @return array An array containing plugins name as key, and plugins path as value
	 */
	function getAllPlugins ()
	{
		$files = $this->futil->getDirsList ( DK_PLUGINS ) ;
		$plugins = array () ;
		
		foreach ( $files as $file )
		{
			$plugins[$file['name']] = $file['path'] ;
		}
		
		return $plugins ;
	}
	
	/**
	 * Retrieve a list af plugins filtered by project and projectType
	 * 
	 * @param object $requireProject [optional] Should the plugin require a project or not
	 * @param object $projectType [optional] Type of project plugin should be applyable
	 * @return 
	 */
	function getFilteredPlugins ( $requireProject = true , $projectType = null )
	{
		$plugins = $this->getAllPlugins () ;
		
		$projectPlugins = array () ;
		
		foreach ( $plugins as $name => $path )
		{
			if ( is_file ( $path . $name .'.php' ) == false )
			{
				continue;
			}
			require_once ( $path . $name . '.php' ) ;
			if ( class_exists($name , false ) == false )
			{
				continue;
			}
			
			$pluginObj = new $name () ;
			if ( property_exists ( $pluginObj, 'requireProject' ) && $pluginObj->requireProject == $requireProject )
			{
				if ( !is_null ( $projectType ) && property_exists($pluginObj, 'requireTypes' ) ) 
				{
					if ( is_array ( $pluginObj->requireTypes ) && !in_array ( $projectType , $pluginObj->requireTypes ) )
					{
						continue;
					}
				}
				
				$projectPlugins[] = $name ;
			}
		}

		return $projectPlugins ;
	}
	
	/**
	 * Returns the plugin object: a DevKitPlugin subclass instance, if plugin has been activated.
	 * Returns false if the plugin has not been found, or if it's not activated.
	 * 
	 * @param string $plugName
	 * @return Plugin object if plugin is found and activated, FALSE otherwise.
	 */
	function getPlugin ( $plugName )
	{
		if ( file_exists ( DK_PLUGINS . $plugName . DS . $plugName . '.php' ) )
		{
			require_once  ( DK_PLUGINS . $plugName . DS . $plugName . '.php' ) ;
		}
		
		if ( class_exists ( $plugName ) )
		{
			$plugin = new $plugName () ;
			
			TaskInitializer::initialize ( &$this->manager , $plugName , $this->view , &$plugin ) ;
			
			if ( $plugin->isActivated () )
			{
				return $plugin ;
			}
		}
		
		return false ;
	}
	
	/**
	 * Retrieve a list of all projects that have a backup
	 * @return 
	 */
	function getAllBackupedProjects ()
	{
		$files = $this->futil->getFilesList ( self::BACKUP_DIR ) ;
		$projects = array () ;
		$_tp = array () ;
		
		if ( !empty( $files ) )
		{
			foreach ( $files as $file )
			{
				if ( !(empty ( $file ) ) && $file['type'] == 'dir' )
				{
					$project = $this->getProjectNameFromBackup( $file['name'] ) ;
					
					if ( in_array($project, $_tp) == false )
					{
						$_tp[] = $project ;
						$projects[] = $project ;
					}
				}
			}
		}
		
		return $projects ;
	}
	
	/**
	 * Retrieve a list of backups filtered by project
	 * @param object $project
	 * @param object $date [optional]
	 * @return 
	 */
	function getFilteredBackups ( $project , $date = null )
	{
		$files = $this->futil->getFilesList ( self::BACKUP_DIR ) ;
		$backups = array () ;
		
		if ( !empty( $files ) )
		{
			foreach ( $files as $file )
			{
				if ( !(empty ( $file ) ) && $file['type'] == 'dir' )
				{
					$bProj = $this->getProjectNameFromBackup( $file['name'] ) ;
					
					if ( $bProj == $project )
					{
						$backups[] = $file['name'] ;
					}
				}
			}
		}
		
		return $backups ;
	}
	
	function getProjectNameFromBackup ( $backupName )
	{
		$arr = explode ( '_' , $backupName ) ;
		$add = '' ;
		
		while ( !empty ( $arr ) && $arr[0] == '' )
		{
			$add .= '_' ;
			array_shift( $arr ) ;
		}
		
		if ( empty ( $arr ) )
		{
			return false;
		} else {
			$arr[0] = $add . $arr[0] ;
		}
		
		return $arr[0] ;
	}
	
	
	function checkForTrash ()
	{
		if ( $this->futil->dirExists ( self::TRASH_DIR ) == false )
		{
			$this->futil->createDir ( '' , self::TRASH_DIR ) ;
		}
	}
	
	function getTrashNewName ( $projectName )
	{
		
		while ( $this->futil->dirExists ( self::TRASH_DIR . DS . $projectName ) == true )
		{
			$arr = explode ( '-' , $projectName ) ;
			$idx = count($arr)-1 ;
			
			if ( is_numeric ( $arr[$idx] ) )
			{
				(int)$arr[$idx] ++ ;
				$projectName = implode ( '-', $arr ) ;
			} else {
				$projectName .= '-1' ;
			}
		}
		return $projectName ;
	}
}
?>