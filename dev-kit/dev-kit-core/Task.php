<?php


/**
 * The Task class is the main class of the 
 * 
 */
class Task {
	
	/**
	 * The TaskManager instance : used to send task status
	 * @var TaskManager
	 */
	protected $manager ;
	
	/**
	 * Parameters from user
	 */
	protected $params = array () ;
	
	/**
	 * The TaskView instance
	 * @var TaskView
	 */
	public $view ;
	
	/**
	 * Name of the task
	 * @var
	 */
	protected $name ;

	/**
	 * Description of what does the task for the user
	 * @var
	 */
	protected $description ;
	
	/**
	 * If the task is about one project, the id of the project
	 * @var DevKitProject object
	 */
	public $project ;
	
	/**
	 * Name of the text (set by TaskManager)
	 * @var
	 */
	public $taskName ;
	
	/**
	 * Path to the task directory
	 * @var
	 */
	public $taskPath ;
	
	/**
	 * FSUtil object reference
	 * @var FSUtil
	 */
	public $futil ; 
	
	/**
	 * DevKit tool access
	 * @var DevKit
	 */
	protected $devKit ;
	
	/**
	 * Broker
	 * @var
	 */
	protected $broker ;
	
	
	final function coreInit ( $broker )
	{
		$this->futil = new FSUtil(dirname(ROOT)) ;
		
		$this->broker = $broker;
	}
	
	
	final function setDevKit ( $devKit )
	{
		$this->devKit = $devKit ;
	}
	
	final function setParams ( $params = array () , &$options = array () )
	{
		$this->params = $params ;
		
		if ( array_key_exists('confirm', $this->params ) && $this->params['confirm'] == 'cancelled' )
		{
			$this->manager->cancel ( 'Task cancelled' ) ;
		}
		
		return $this->onSetParams ( $options ) ;
	}
	
	final function hasParam ( $param )
	{
		return array_key_exists ( $param , $this->params ) ;
	}
	
	final function setManager ( $manager )
	{
		$this->manager = $manager ;
	}
	
	final function setView ( $view )
	{
		$this->view = $view ;
	}
	
	final function requireMemory ( $mem )
	{
		if ( App::requireMemory ( $mem ) == false )
		{
			$this->manager->cancel ( 'This is not possible to set a higher memory limit for this task. This task will not run.' ) ;
		}
	}
	
	final function refreshProject ( $name = null )
	{
		
		if ( is_null ( $name ) && $this->project )
		{
			$name = $this->project->name ;
		}
		
		$this->project = new DevKitProject ( $name ) ;
		$this->project->refresh () ;
		
		$this->view->removeProjectMenuItem () ;
		$this->view->setProject ( $this->project , $this->taskName ) ;
	}
	
	
	function init ()
	{
		
	}
	
	
	
	/**
	 * Override this
	 * @return 
	 */
	function getOptions () {
		
	}
	
	
	/**
	 * Override this method in your concrete tasks classes, and return a boolean
	 * to alert TaskManager of validity of user given parameters.
	 * 
	 * Return true if params are valids : task will be processed.
	 * Return false if params are invalid : manager will display task options form
	 * Don't override if you don't have params to check.
	 * 
	 * @return Boolean
	 */
	function onSetParams ( $options = array () )
	{
		return true;
	}
	
	function process () {
		
	}
	
	function beforeEnd () 
	{
		
	}
	
	function getOption ( $options , $optionName )
	{
		foreach ( $options as &$option )
		{
			if ( $option->name === $optionName )
			{
				return $option ;
			}
		}
	}
}
?>