<?php


class TaskManager {
	
	/**
	 * The view of the task
	 * @var
	 */
	public $view ;
	
	/**
	 * Name of the task
	 * @var
	 */
	private $taskName ;
	
	/**
	 * Name of project
	 * @var
	 */
	private $taskProject ;
	
	/**
	 * Concrete task object
	 * @var
	 */
	private $taskObject ;
	
	/**
	 * Task options
	 * @var
	 */
	public $taskOptionsDefinition = array () ;
	
	/**
	 * Result of options
	 * @var
	 */
	private $taskOptions = array () ;
	
	/**
	 * Path to the task file
	 * @var
	 */
	private $taskPath ;
	
	/**
	 * Is the task a plugin
	 * @var
	 */
	private $isPlugin = false ;
	
	/**
	 * Is the task processable or not
	 * @var
	 */
	private $runnable = false ;
	
	/**
	 * All the paths that could contain the task file
	 * @var
	 */
	public $paths = array () ;

	/**
	 * String representing the next task
	 * @var
	 */
	private $next ;
	
	/**
	 * Is the task done or not
	 * @var
	 */
	private $isDone = false ;
	
	/**
	 * FSUtil reference
	 * @var
	 */
	private $futil ;
	
	public $devKit ;
	
	public $defaultTask = 'Home' ;
	
	public $shouldBackup = false ;
	
	// Launch task process
	function __construct ()
	{
		new Log () ;
		
		
		// File util, a very util tool
		$this->futil = new FSUtil(dirname(ROOT));
		
		$this->devKit = new DevKit () ;
		
		
		// Detect all paths where a task could be
		$this->retrievePaths () ;
		
		// Retrieve URL parameters of task
		$this->getTaskParams () ;
		
		global $broker ;
		
		if ( $broker->preferences->isEmpty () != false )
		{
			$this->taskName = 'SDKPrefs' ;
			// Create the view
			$this->view = new TaskView ( $this->taskName , true ) ;
		} else {
			
			// Create the view
			$this->view = new TaskView ( $this->taskName , $this->taskName == $this->defaultTask ) ;
		}
		
		// We start logging task process
		$log = date('y/m/d H:i') . ' Starting task: ' . $this->taskName ;
		if ( $this->next )
		{
			$log .= '- Waiting next: ' . str_replace('/','-', $this->next ) ;
		}
		Log::wlog ( $log ) ;
		
		
		// If we have a project name from parameters
		if ( $this->taskProject )
		{
			// We can't use backup and trash directories as projects
			if ( in_array( $this->taskProject , array ( DevKit::BACKUP_DIR , DevKit::TRASH_DIR ) ) )
			{
				// we cancel
				$this->cancel ( 'Tasks can not be processed on backup and trash dev-kit directories.' ) ;
			}
			
			// We create a project object
			$project = new DevKitProject ( $this->taskProject ) ;
			// And we pass it to the view
			$this->view->setProject ( $project , $this->taskName ) ;
			
			if ( $project->valid )
			{
			
		
				// If locked project, protect it
				if ( $project->isLocked () == true && $this->taskName != 'UnlockProject')
				{
					$this->cancel ( 'The required project is locked. Only UnlockProject task is authorized for locked projects.'  ) ;
				}
				
				if ( !in_array ( $this->taskName , array ( 'ManageProject', 'LockProject' , 'TrashProject' ) ) )
				{
					$this->devKit->addMostRecentProject ( $project->name ) ;
				}
			}
		}
		
		// If the task does not exists
		if ( $this->retrieveTask() == false )
		{
			// we cancel
			$this->cancel ( 'Task has not been found.' , 'Search:' . $this->taskName ) ;
		}
		
		// If the task is a plugin
		if ( $this->isPlugin )
		{
			// we modify main title class to reflect plugins origin
			$this->view->template->set ( 'title_class' , 'plugin ' . $this->view->template->get ('title_class') ) ;
		}
		
		
		
		// If task class does not exists in file
		if ( class_exists($this->taskName , false ) != true ) 
		{
			// We cancel
			$this->cancel ( 'Task file has been found, but task class is not instanciable.' ) ;
		}
		
		// If task class does not extends Task main class
		if ( is_subclass_of($this->taskName , 'Task' ) == false )
		{
			// We cancel
			$this->cancel ( 'Task file has been found, task class has been instanciated, but class does not extend Task class.' ) ;
		// Else, we check if __construct magic method has been implemented in concrete task class
		// constructor is never authorized: process must be exactly the same for each task.
		} else if ( method_exists($this->taskName, '__construct' ) )
		{
		 	$this->cancel ( 'Task file has been found, task class exists, but uses of magic method __construct is not authorized in Task sub classes. Use init () method instead.' ) ;
		// No constructor: we can init the task
		} else {
			// We instanciate the task object
			$this->taskObject = new $this->taskName () ;
			
			$this->taskObject->coreInit ( $broker ) ;
			
		}
	
		// TODO: Refactor lines below: odd way to pass arguments to task...
		
		// We setup the task object with main infos : task name
		$this->taskObject->taskName = $this->taskName ;
		// Task path
		$this->taskObject->taskPath = $this->taskPath ;
		// Task manager
		$this->taskObject->setManager ( &$this ) ;
		// Task manager
		$this->taskObject->setDevKit ( &$this->devKit ) ;
		// Task view
		$this->taskObject->setView ( &$this->view ) ;
	
		if ( $this->isPlugin == true && method_exists($this->taskName, 'initPlugin' ) ) 
		{
			if ( $this->taskObject->initPlugin () == false )
			{
				if ( $this->taskObject->isActivated () == false )
				{
					$this->cancel ( 'Plugin '.$this->taskName.' is not activated.' ) ;
				} else {
					$this->cancel ( 'Plugin '.$this->taskName.' is not usable.' ) ;
				}
			}
		}
	
		
		// We check for :
		// - valid project required
		// - project required (valid or not)
		if ( property_exists($this->taskObject, 'requireValidProject' ) && $this->taskObject->requireValidProject == true )
		{
			if ( !$this->taskProject || $project->valid == false )
			{
				$this->view->setWarning ( 'This task requires a valid project to be processed.' ) ;
				$this->done ( false ) ;
			}
		} else if ( property_exists($this->taskObject, 'requireProject' ) && $this->taskObject->requireProject == true )
		{
			if ( !$this->taskProject )
			{
				$this->view->setWarning ( 'This task requires a project to be processed.' ) ;
				$this->done ( false ) ;
			}
		}
		
		// If considered project is dev-kit
		// protect it
		if ( $this->taskProject && $this->taskProject == DevKit::DEVKIT_DIR )
		{
			$this->view->setWarning ( 'It is not advised to process tasks on dev-kit. Make it at your own risks !' ) ;
			
			$unauthActions = array ( 'RenameProject' , 'DeleteProject' , 'TrashProject' , 'DeployAenoaServer' ) ;
			
			// Cancel unauth actions on dev-kit
			if ( in_array ( $this->taskName , $unauthActions ) )
			{
				$this->cancel ( 'It is not authorized to use the following tasks on dev-kit: ' . implode ( ', ' , $unauthActions ) ) ;
			}
			
		}
		
		// Check if task require a precise project type
		if ( property_exists($this->taskObject, 'requireTypes') && $this->taskProject )
		{
			$requireTypes = $this->taskObject->requireTypes ;
			if ( !is_array ( $requireTypes ) ) 
			{
				trigger_error('Task property requireTypes for object ' . $this->taskName . ' must be typed as array.' , E_USER_WARNING ) ;
			} else {
				if ( !in_array ( $project->type , $requireTypes ) )
				{
					$this->view->setWarning ( 'This task requires a project that type fit one of this/these project type/s: <ul><li>' . implode ( '</li><li>' , $requireTypes ) .'</li></ul>' ) ;
					$this->done ( false ) ;
				}
			}
		}
		
		// Give to task the project
		if ( $this->taskProject )
		{
			$this->taskObject->project = $project ;
			
			Log::wlog ( '- On object: ' . $project->name ) ;
		
		}
		
		// Prepare next task
		if ( $this->hasNext () )
		{
			$this->view->template->set ( 'nextTask' , implode ( ' &raquo; ' , explode ( '/' , $this->next ) ) ) ;
		}
		
		// Give task to view
		$this->view->setTask ( $this->taskObject ) ;
		
		// Init task
		$this->taskObject->init () ;
		
		// Retrieve globals received options params
		if ( $this->retrieveOptions ( ) )
		{
			// If retrieveOptions returns true, then params has been sended, let's make use validation in Task:
			// Task->setParams -> {$ConcreteTask}->onSetParams
			$userValidation = $this->taskObject->setParams ( $this->taskOptions , &$this->taskOptionsDefinition ) ;
			
			if ( $this->runnable == true )
			{
				$this->runnable = $userValidation ;
			}
		}
		// If options are required, and are not valid or existing
		if ( $this->runnable == false && $this->isDone == false )
		{
			if ( $this->runnable == false )
			{
				$this->view->setStatusBar ( 'This task requires some data before beeing processed' ) ;
			}
			
			$this->view->setOptions ( $this->taskOptionsDefinition ) ;
			
			if ( $this->shouldBackup == true )
			{
				$this->view->setWarning ( 'You should do a backup before using this task !' , true ) ;
			}
			
			$this->view->render () ;
			$this->view->hideIndicator () ;
			
			Log::wlog ( '- Waiting for options' . "\n" ) ;
			
		// Everything is ok : we process the task
		} else if ( $this->isDone == false )
		{
			$this->taskObject->process () ;
			$this->done ( true ) ;
		}
			
	}
	
	public function cancel ( $message = '' , $redirectTo = null , $authNextTasks = true )
	{
		if ( headers_sent () == false )
		{
			header('Status: 404 Not Found', false, 404);
		}
		
		$this->view->setError ( $message ) ;
		
		if ( $this->hasNext () == false && !is_null ( $redirectTo ) ) 
		{
			$this->view->setStatus ( 'Going to search task in 2 seconds...' ) ;
			$this->view->redirect ( url() . $redirectTo , 2000 ) ;
		}
		
		
		$this->view->endTask () ;
		$this->view->render () ;
		
		$this->isDone = true ;
		
		if($authNextTasks)
		{
			$this->next () ;
		}
		
		Log::wlog ( '- Task cancelled' . "\n" ) ;
		
		die () ;
	}
	
	
	public function wait ()
	{
		$this->view->endTask () ;
		$this->view->render () ;
	}
	
	public function done ( $success = true )
	{		
		if ( $this->taskObject )
		{
			$this->taskObject->beforeEnd () ;
		}
		
		$this->view->endTask ( $success ) ;
		$this->view->render () ;
		
		$this->isDone = true ;
		
		$this->next () ;
		
		Log::wlog ( '- Task done' . "\n" ) ;
		
		die () ;
	}
	
	public function redirect ( $url , $delay = 0 )
	{
		Log::wlog ( '- Task done, redirecting' . "\n" ) ;
		
		$this->view->redirect ($url , $delay ) ;
	}
	
	public function next ()
	{
		if ( $this->hasNext () )
		{
		
			$url = url() . $this->next ;
			if ( headers_sent() == false )
			{
				header ( 'Location: ' . $url ) ;
			} else {
				$this->view->setStatus ( 'Going to next task in 5 seconds...' ) ;
				$this->view->redirect ( $url , 5000);
			}
		}
	}
	
	function hasNext ()
	{
		if ( $this->next != '' )
		{
			return true ;
		}
		return false ;
	}
	
	function getTaskParams ()
	{
		$params = App::$sanitizer->get ( 'GET' , 'query' ) ;
		$tasks = explode ( '/' , $params ) ;
		$token = array_shift($tasks) ;
		$task = array_shift($tasks) ;
		if ( count ( $tasks ) > 0 )
		{
		
			$this->next = implode('/', $tasks ) ;
		}
		$arr = explode ( ':' , $task ) ;
		if ( count ( $arr ) > 0 && $arr[0] != '' )
		{
			$this->taskName = self::getRedirection ( $arr[0] ) ;
			$this->taskProject = basename( ROOT ) ;
			
			return ;
		}
		
		$this->taskName = $this->defaultTask ;
	}
	
	
	function retrieveTask ( $task = null )
	{
		if ( is_null ( $task ) )
		{
			$task = $this->taskName ;
		}
		foreach ( $this->paths as $path )
		{
			if ( is_file ( $path . $task . '.php' ) )
			{
				require_once ( $path . $task . '.php' ) ;
				
				if ( substr_count ( $path , DK_PLUGINS ) ) 
				{
					$this->isPlugin = true ;
				}
				
				$this->taskPath = $path ;
				
				return true ;
			}
		}
		return false ;
	}
	
	
	private function retrievePaths ()
	{
		$this->paths[] = DK_TASKS ;
		
		$dirs = $this->futil->getDirsList ( DK_TASKS ) ;
		
		foreach ( $dirs as $v )
		{
			$this->paths[] = $v['path'] ;
		}
		
		$dirs = $this->futil->getDirsList ( DK_PLUGINS ) ;
		
		foreach ( $dirs as $v )
		{
			$this->paths[] = $v['path'] ;
		}
	}
	
	/**
	 * 
	 * @return bool True if params has data, false otherwise
	 */
	function retrieveOptions ()
	{
		// Retrieve task options
		$this->taskOptionsDefinition = $this->taskObject->getOptions () ;
		
		global $broker ;
		$params = App::$sanitizer->POST ;
		
		$this->runnable = true ;
		
		if ( !is_array ( $this->taskOptionsDefinition ) || empty ( $this->taskOptionsDefinition ) )
		{
			return false;
		}
		
		if ( empty ( $params ) )
		{
			$this->runnable = false ;
			return false;
		}
		
		
		foreach ( $this->taskOptionsDefinition as &$option )
		{
			$param = @$params[$option->name] ;
			unset ( $params[$option->name] ) ;
			
			if ( $option->urlize == true )
			{
				$param = urlize ( $param ) ;
			}
			
		 	if ( $param != '' || $option->valid === true )
			{
				Log::wlog ( '- Has param ' . $option->name . ' / ' . $param ) ;
				
				$option->valid = true ;
				if ( $param != '' )
				{
					$option->setValue ( $param ) ;
					$this->taskOptions[$option->name] = $param ;
				} else {
					$this->taskOptions[$option->name] = $option->value ;
				}
			} else if ( $option->required == true )
			{
				$this->runnable = false ;
				$option->valid = false ;
			}
		}
		
		foreach ( $params as $k => $v )
		{
			$this->taskOptions[$k] = $v ;
		}
		
		return true ;
	}
	
	
	
	/////////////////////////////
	// Static
	
	private static $__redirections ;
	
	static function addRedirection ( $task , $trueTask ) 
	{
		self::$__redirections[$task] = $trueTask ;
	}
	
	static function getRedirection ( $task )
	{
		if ( array_key_exists( $task , self::$__redirections ) && !is_null ( self::$__redirections[$task] ) )
		{
			return self::$__redirections[$task] ;
		}
		return $task ;
	}
}
?>