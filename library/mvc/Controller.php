<?php






/**
 * Controller is one of the main classes of Aenoa Server
 * 
 * @author xavier
 *
 */
class Controller extends AeObject {
	
	/**
	 * Title of page
	 * @var unknown_type
	 */
	public $title = '' ;
	
	/**
	 * Temp viewPath
	 * @var unknown_type
	 */
	public $viewPath = 'html/messages.thtml' ;
	
	/**
	 * Does controller avoid render
	 * @var boolean
	 */
	public $avoidRender = false ;
	
	/**
	 * Global FSUtil object reference
	 * 
	 * Used to access, modify, create... files and folders
	 * 
	 * @var FSUtil
	 */
	public $futil ;
	
	/**
	 * @var Template
	 */
	protected $view ;
	
	/**
	 * @var Model
	 */
	protected $model ;
	
	/**
	 * @var AbstractDBEngine
	 */
	public $db ;
	
	/**
	 * Incoming data from user
	 * @var array
	 */
	protected $data = array () ;
	
	/**
	 * Output data for user
	 * @var array
	 */
	public $output = array () ;
	
	/**
	 * A list of response statuses
	 * @var array
	 */
	protected $responses = array () ;
	
	/**
	 * Name of the controller
	 * @var string
	 */
	private $name = null ;
	
	/**
	 * Action performing
	 * @var string
	 */
	protected $action = null ;
	
	/**
	 * Model class name
	 * @var string
	 */
	private $modelClassName = null ;
	
	/**
	 * This define a SUCCESS response status
	 */
	const RESPONSE_SUCCESS = 'success' ;
	
	/**
	 * This define an ERROR response status
	 */
	const RESPONSE_ERROR = 'error' ;
	
	/**
	 * This define a WARNING response status
	 */
	const RESPONSE_WARNING = 'warning' ;
	
	/**
	 * This define a NOTICE response status
	 */
	const RESPONSE_NOTICE = 'notice' ;
	
	/**
	 * This define a CRITIC response status
	 */
	const RESPONSE_CRITIC = 'critic' ;
	
	/**
	 * This define a INFO response status
	 */
	const RESPONSE_INFO = 'info' ;
	
	/**
	 * This define a HELP response status
	 */
	const RESPONSE_HELP = 'help' ;
	
	/**
	 * Constructor
	 */
	function __construct ()
	{
		$this->title = Config::get(App::APP_NAME) ;
		
		$this->db = App::getDatabase ( 'main' ) ;
		
		$this->data = App::$sanitizer->getAll('POST') ;
		
		global $FILE_UTIL ;
		
		$this->futil = $FILE_UTIL ;
		
		if ( App::getSession()->has('Controller.responses') )
		{
			$this->responses = App::getSession()->get('Controller.responses');
			App::getSession()->uset('Controller.responses');
		}
	}
	
	/**
	 * Reset the data of the controller
	 *
	 * @protected
	 * @return Controller Current instance for chained command
	 */
	protected function reset ()
	{
		$this->data = array () ;
	}
	
	/**
	 * Reset the data of the controller
	 *
	 * @return Controller Current instance for chained command
	 */
	function getData () 
	{
		return $this->data ;
	}
	
	/**
	 *
	 */
	function getResponses () 
	{
		return $this->responses ;
	}
	
	protected function validateInputs ( $ruleArray )
	{
		$errors = array () ;
		
		if ( !empty( $this->data ) )
		{
			foreach ( $ruleArray as $field => $regexp )
			{
				$fieldName = ucfirst(array_pop(explode('/',$field)));
				if ( !array_key_exists($field,$this->data) )
				{
					$errors[] = sprintf(_('Field <strong>%s</strong> has not been filled'),$fieldName);
				} else 
				{
					preg_match_all('/'.$regexp.'/',$this->data[$field],$m);
					
					if (empty($m)||empty($m[0]))
					{
						$errors[] = sprintf(_('Field <strong>%s</strong> is not well formatted'),$fieldName);
					}
				}
			}
		} else {
			$errors[] = _('No data has been sent') ;
		}
		
		foreach ( $errors as $error )
		{
			$this->addResponse( $error, self::RESPONSE_ERROR ) ;
		}
		
		return (empty($errors) ? true : $errors ) ;
	}
	
	final function setIDS ( $name, $action, $modelClassName = null )
	{
		if ( is_null($this->name) )
		{
			$this->name = $name ;
			
			$this->action = $action ;
			
			$this->modelClassName = $modelClassName ;
		}
	}
	
	
	protected function getName ()
	{
		return $this->name ;
	}
	
	protected function getAction ()
	{
		return $this->action ;
	}
	
	protected function runAction ( $action )
	{
		if ( method_exists ($this, $action ) )
		{
			$this->action = $action ;
			
			$this->createView () ;
			
			$this->$action () ;
		} else {
			App::do404 ( 'Action ' . $action . ' not found in ' . $this->name ) ;
		}
		
	}
	
	public function addResponse ( $text, $type = 'success' )
	{
		if ( !array_key_exists( $type, $this->responses ) )
		{
			$this->responses[$type] = array () ;
		}
		$this->responses[$type][] = $text ;
	}
	
	public function getDB ()
	{
		return $this->db ;
	}
	
	public function setView ( View &$view )
	{
		$this->view = $view ;
	}
	
	public function getView ()
	{
		return $this->view ;
	}

	public function getModel ()
	{
		return $this->model ;
	}

	public function setModel ( Model &$model )
	{
		$this->model = $model ;
	}
	
	/**
	 * 
	 * @param unknown_type $controllerName
	 * @return Model
	 */
	public function getNewModel ( $controllerName )
	{
		$_m = camelize ( $controllerName ) . 'Model' ;
		
		$model = ROOT.'app'.DS.'models'.DS . $_m . '.php' ;
		
		if ( is_file($model) )
		{
			require_once($model);
		}
				
		// Create model
		if ( class_exists($_m) )
		{
			$model = new $_m ( $this , $this->getDB() ) ;
			
			return $model ;
		} else {
			return new Model ( $this, $this->getDB() ) ;
		}
	}
	
	public function reloadModel ( $controllerName )
	{
		$this->setModel($this->getNewModel ( $controllerName ) );
	}
	
	protected function createViewFromAction ( $actionName , $mode = 'html' )
	{
		$this->createView( $mode . DS . uncamelize($this->name) . DS . uncamelize($actionName) . '.thtml' ) ;
	}
	
	protected function bindToView ( View &$view )
	{
		$this->view = $view ;
		
		$this->view->set ('input_data', $this->data ) ;
	}
	
	protected function createView ( $viewPath = null , $mode = 'html' )
	{
		if ( is_null( $viewPath ) )
		{
			$viewPath = $mode . DS .  uncamelize($this->name)
					. DS .  uncamelize($this->action)
					. (strpos($this->action,'.') === false ? '.thtml' : '') ;
		}
		
		$this->viewPath = $viewPath ;
		
		if ( $this->view == null )
		{
			$this->view = new Template ( $this->viewPath , $this->title ) ;
			
			$this->view->set ('input_data', $this->data ) ;
		} else {
			
			$this->view->appendToTitle( $this->title ) ;
			$this->view->setFile($viewPath) ;
		}

		return $this->view ;
	}
	
	protected function renderView ()
	{
		if ( is_null( $this->view ) )
		{
			return;
		}
		
		if ( $this->view->isRendered () == false )
		{
			$this->view->set ( '__responses', $this->responses ) ;
			$this->view->render () ;
		}
		
	}
	
	
	function __destruct ()
	{
		if ( !headers_sent() && !empty($this->responses) )
		{
			App::getSession()->set('Controller.responses',$this->responses);
		}
	}
	
	
	
	//////////////////////////////////////////////////////////////
	////////// STATIC PART
	
	/**
	 * Paths to controllers
	 * @var array
	 */
	private static $_paths = array () ;
	
	/**
	 * Last instance of controller
	 * @var Controller
	 */
	private static $_ctrl = array () ;
	
	/**
	 * Adds some paths where controllers could be (use this method in your app-conf.php file)
	 * 
	 * @param string $controllersFolder
	 */
	static function addPath ( string $controllersFolder )
	{
		$_paths[] = $controllersFolder;
	}
	
	/**
	 * A static function to load (using php require_once function) a controller file.
	 * This method does NOT instanciate the controller.
	 * This method does checks for a corresponding model and require it too, without instanciating it.
	 * Check the instanciateController method for that.
	 * 
	 * @param string $controllerName
	 * @return bool True if controller file has been found, false otherwise
	 */
	static function requireController ( $controllerName , $action )
	{
		$paths = array_merge ( array (
			ROOT.'app'.DS.'controllers'.DS,
			ROOT.'controllers'.DS,
			AE_CONTROLLERS
		) , self::$_paths ) ;

		if (strpos($action, '.') !== false)
		{
			$action = substr($action, 0, strpos($action, '.')) ;
		}
		
		$modelName = camelize ( $controllerName ) . 'Model' ;
		$controllerName = camelize ( $controllerName ) . 'Controller' ;
		$action = camelize($action) ;
		
		foreach ( $paths as $p )
		{
			if ( is_file($p . $controllerName . '.php' ) )
			{
				require_once($p . $controllerName . '.php');
				
				if ( !class_exists($controllerName) )
				{
					if ( debuggin() )
					{
						App::do404 ( $controllerName . ' is not defined as class.' ) ;
					}
					return false ;
				}
				
				if ( !method_exists($controllerName,$action) )
				{
					if ( debuggin() )
					{
						App::do404 ( $action . ' is not defined in class ' . $controllerName ) ;
					}
					return false ;
				}
				
				if ( !is_public_controller_method($controllerName, $action) || substr($controllerName,0,1) == '_' )
				{
					if ( debuggin() )
					{
						App::do404 ( $action . ' is not public in class ' . $controllerName ) ;
					}
					return false ;
				}
				
				
				return true ;
						
				break;
			}
		}
		return false ;
	}
	
	
	/**
	 * A static function to instanciate a controller and launch its corresponding action.
	 * 
	 * @param string $controllerName
	 * @return Controller
	 */
	static function launchController ( $controllerName , $action , $mainParam = null, $controllerParams = array (), $othersParams = array () )
	{
		// Format names
		$_n = camelize ( $controllerName ) . 'Controller' ;
		$_m = camelize ( $controllerName ) . 'Model' ;

		$viewAction = $action ;

		if (strpos($action, '.') !== false)
		{
			$action = substr($action, 0, strpos($action, '.')) ;
		}

		$action = lcfirst(camelize($action)) ;
		
	
		// Format parameters
		$params = array () ;
		if ( !is_null($mainParam) )
		{
			$params[] = $mainParam ;
		}
		if ( !is_null($othersParams) && !empty ($othersParams) )
		{
			$params = array_merge($params, $othersParams);
		}
		
		// Instanciate controller
		$controller = new $_n () ;
		
		// Setting controller properties 
		foreach ( $controllerParams as  $k => $v )
		{
			@$controller->{$k} = $v ;
		}
		
		// Setting controller id
		$controller->setIDS ( $controllerName , $viewAction , $_m ) ;
	
		self::$_ctrl = $controller ;
		
		$controller->reloadModel ( $controllerName ) ;
		
		
		// Let's controller manager view creation
		$controller->createView () ;

		
		
		// Call beforeAction
		$controller->beforeAction ( $action ) ;
		
		// Call the action !
		$res = call_user_func_array(array($controller,$action) , $params ) ;
		
		// After the action : call afterAction
		$controller->afterAction ( $action ) ;
		
		// End App (write session, close DB engines....) without die
		App::end(false);
		
		// If rendering required, let's render
		if ( $controller->avoidRender == false )
		{
			$controller->renderView () ;
		}
		
		// After render
		$controller->afterRender () ;
		
		// We're done
		return $controller ;
	}
	
	/**
	 * Returns the current loaded controller
	 * 
	 * @return Controller
	 */
	static function getCurrent ()
	{
		return self::$_ctrl ;
	}
	
	/**
	 * Returns true if a controller has been loaded
	 * 
	 * @return boolean
	 */
	static function hasCurrent ()
	{
		return is_object(self::$_ctrl) ;
	}
	
	/**
	 * Function: shutdown
	 * 
	 * Stop the controller
	 * 
	 * Returns:
	 * return_type
	 *
	 */
	static function shutdown ()
	{
		if ( self::hasCurrent () )
		{
			self::$_ctrl->__destruct() ;
			self::$_ctrl = null ;
		}
	}
	
	
	protected function beforeAction ( $action )
	{
		
	}
	
	protected function afterAction ( $action )
	{
		
	}
	
	protected function afterRender ()
	{
		
	}
}

?>
