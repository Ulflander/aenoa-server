<?php

/**
 * Class: Controller
 * 
 * Controller is one of the main classes of Aenoa Server.
 * 
 *
 *
 */
class Controller extends Object {

	/**
	 * Title of page
	 * @var unknown_type
	 */
	public $title = '';

	/**
	 * Temp viewPath
	 * @var unknown_type
	 */
	public $viewPath = 'html/messages.thtml';

	/**
	 * Does controller avoid render
	 * @var boolean
	 */
	public $avoidRender = false;

	/**
	 * Global FSUtil object reference
	 * 
	 * Used to access, modify, create... files and folders
	 * 
	 * @var FSUtil
	 */
	public $futil;

	/**
	 * Does selected data in Model automatically sent to view
	 */
	public $propagation = false ;

	/**
	 * @var Template
	 */
	protected $view;

	/**
	 * @var Model
	 */
	protected $model;

	/**
	 * @var AbstractDBEngine
	 */
	public $db;

	/**
	 * Incoming data from user
	 * @var array
	 */
	protected $data = array();

	/**
	 * Output data for user
	 * @var array
	 */
	public $output = array();

	/**
	 * A list of response statuses
	 * @var array
	 */
	protected $responses = array();

	/**
	 * Name of the controller
	 * @var string
	 */
	private $name = null;

	/**
	 * Action performing
	 * @var string
	 */
	protected $action = null;
	private $_models = array();
	private $_implicit = 'Main';

	/**
	 * Model class name
	 * @var string
	 */
	private $modelClassName = null;

	/**
	 * This define a SUCCESS response status
	 */
	const RESPONSE_SUCCESS = 'success';

	/**
	 * This define an ERROR response status
	 */
	const RESPONSE_ERROR = 'error';

	/**
	 * This define a WARNING response status
	 */
	const RESPONSE_WARNING = 'warning';

	/**
	 * This define a NOTICE response status
	 */
	const RESPONSE_NOTICE = 'notice';

	/**
	 * This define a CRITIC response status
	 */
	const RESPONSE_CRITIC = 'critic';

	/**
	 * This define a INFO response status
	 */
	const RESPONSE_INFO = 'info';

	/**
	 * This define a HELP response status
	 */
	const RESPONSE_HELP = 'help';

	/**
	 * Constructor
	 */
	function __construct() {
		$this->title = Config::get(App::APP_NAME);

		$this->db = App::getDatabase('main');

		$this->data = App::$sanitizer->getAll('POST');

		global $FILE_UTIL;

		$this->futil = $FILE_UTIL;

		if (App::getSession()->has('Controller.responses')) {
			$this->responses = App::getSession()->uget('Controller.responses');
		}

		if (property_exists($this, 'models')) {
			$this->setModels($this->models);
		}
	}

	///// START NEW WAY TO USE MODELS


	/**
	 * Get a model or a database model
	 *
	 *
	 *
	 * <p>How to use:</p>
	 * <pre>
	 * class FooController extends Controller {
	 *
	 *		// Declaring models
	 *		public $models = array (
	 *			'products',			// A Model from implicit database (main, by default)
	 *			'main/categories',  // A Model from explicit database Main
	 *			'remote/table'		// A Model from an explicit remote database
	 *		) ;
	 *
	 *		function bar ()
	 *		{
	 *
	 *
	 *			// Get a random entry of products
	 *			$this->Products->findRandom () ;
	 *
	 *			// Implicit database is main, so doing this is the same as upper
	 *			$this->Main->Products->findRandom () ;
	 *
	 *			// Call an explicit database model
	 *			$this->Remote->Table->findAll () ;
	 *			
	 * 		}
	 *
	 * }
	 * </pre>
	 *
	 *
	 * @see Model
	 * @see GetableCollection
	 * @param string $name Name of Model or GetableCollection to get
	 * @return Model
	 */
	final function __get($name) {
		
		if ( $this->_models[$this->_implicit]->has($name) ) {
			return $this->_models[$this->_implicit]->$name ;
		} else if (ake($name, $this->_models)) {
			return $this->_models[$name];
		}

		throw new ErrorException('Trying to get unknown database or model <strong>' . $name
								. '</strong> from Controller <strong>' . get_class($this) . '</strong>' );

		return null ;
	}


	/**
	 * Load models into controller.
	 *
	 * @param type $models
	 */
	function setModels($models) {
		$_models = array();

		// First clean array of models, applying implicit database ids when required
		foreach ($models as $model) {
			if (strpos($model, '/') !== false) {
				list($id, $table) = explode('/', $model);
			} else {
				$id = urlize($this->_implicit, '_');
				$table = $model;
			}
			

			if (!ake($id, $_models)) {
				$_models[$id] = array();
			}

			$_models[$id][] = $table;
		}
		
		// Then actually load models
		foreach ($_models as $database => $models) {
			$tables = array();
			
			foreach ($models as $model) {
				$tables[camelize($model,'_')] = $this->_loadModel($database, $model);
			}
			$this->_models[camelize($database, '_')] = new GetableCollection($tables);
		}

		if ( !ake($this->_implicit, $this->_models) )
		{
			$this->_models[$this->_implicit] = new GetableCollection ( array () ) ;
		}
	}
	
	private function _loadModel($database, $model) {
		
		$table = $model ;
		$model = camelize($model).'Model';

		$path = AE_APP_MODELS . $model . '.php';

		if (is_file($path)) {
			require_once($path);
		}

		

		// Create model
		if (class_exists($model)) {
			$mObj = new $model($this, $database, $table) ;
		} else {
			$mObj = new Model($this, $database, $table) ;
		}

		$mObj->propagate( $this->propagation ) ;



		return $mObj ;
	}

	/**
	 * Set implicit database
	 *
	 * @param string $id [Optional] Implicit database identifier, dafault is "main"
	 * @return Controller Current 
	 */
	function implicit( $id = 'main' ) {
		if (is_string($id)) {
			$this->_implicit = $id;
		}

		return $this ;
	}
	
	/**
	 * Checks if a database identifier is the implicit one
	 * 
	 * @param string $id Database identifier
	 * @return boolean True if database is implicit, false otherwise
	 */
	function isImplicit($id) {
		return $id == $this->_implicit;
	}

	/**
	 * Get the implicit database identifier
	 * 
	 * @return string Current implicit database identifier 
	 */
	function getImplicit() {
		return $this->_implicit;
	}

	/**
	 * Propagate some data to view, if view exists
	 *
	 * @param string $key Name of variable in view
	 * @param mixed $value Value of data
	 * @return Controller Current instance for chained command on this element
	 */
	function propagate ( $key , $value ) 
	{
		if ( $this->hasView() )
		{
			$this->view->set ( $key , $value ) ;
		}
		return $this ;
	}

	///// END NEW WAY TO USE MODELS
	
	

	/**
	 * Reset the data of the controller
	 *
	 * @protected
	 * @return Controller Current instance for chained command
	 */
	protected function reset() {
		$this->data = array();
	}

	/**
	 * Reset the data of the controller
	 *
	 * @return Controller Current instance for chained command
	 */
	function getData() {
		return $this->data;
	}

	/**
	 *
	 */
	function getResponses() {
		return $this->responses;
	}
	
	
	// TODO: move this in Model
	protected function validateInputs($ruleArray) {
		$errors = array();

		if (!empty($this->data)) {
			foreach ($ruleArray as $field => $regexp) {
				$fieldName = ucfirst(array_pop(explode('/', $field)));
				if (!array_key_exists($field, $this->data)) {
					$errors[] = sprintf(_('Field <strong>%s</strong> has not been filled'), $fieldName);
				} else {
					preg_match_all('/' . $regexp . '/', $this->data[$field], $m);

					if (empty($m) || empty($m[0])) {
						$errors[] = sprintf(_('Field <strong>%s</strong> is not well formatted'), $fieldName);
					}
				}
			}
		} else {
			$errors[] = _('No data has been sent');
		}

		foreach ($errors as $error) {
			$this->addResponse($error, self::RESPONSE_ERROR);
		}

		return (empty($errors) ? true : $errors );
	}

	final function setIDS($name, $action, $modelClassName = null) {
		if (is_null($this->name)) {
			$this->name = $name;

			$this->action = $action;

			$this->modelClassName = $modelClassName;
		}
	}

	protected function getName() {
		return $this->name;
	}

	protected function getAction() {
		return $this->action;
	}

	protected function runAction($action) {
		if (method_exists($this, $action)) {
			$this->action = $action;

			$this->createView();

			$this->$action();
		} else {
			App::do404('Action ' . $action . ' not found in ' . $this->name);
		}
	}

	public function addResponse($text, $type = 'success') {
		if (!array_key_exists($type, $this->responses)) {
			$this->responses[$type] = array();
		}
		$this->responses[$type][] = $text;
	}

	public function getDB() {
		return $this->db;
	}

	public function setView(View &$view) {
		$this->view = $view;
	}

	public function getView() {
		return $this->view;
	}

	public function hasView() {
		return is_object($this->view);
	}
	
	
	/**
	 * [DEPRECATED]
	 * 
	 * @see Controller::__get
	 * @return Model 
	 */
	public function getModel() {
		return $this->model;
	}
	
	/**
	 * [DEPRECATED]
	 * 
	 * @see Controller::setModels
	 * @param Model $model 
	 */
	public function setModel(Model &$model) {
		$this->model = $model;
	}

	/**
	 * [DEPRECATED]
	 * 
	 * @see Controller::_loadModel
	 * @param string $controllerName
	 * @return Model
	 */
	public function getNewModel($controllerName) {
		$_m = camelize($controllerName) . 'Model';

		$model = ROOT . 'app' . DS . 'models' . DS . $_m . '.php';

		if (is_file($model)) {
			require_once($model);
		}

		$database = $this->getDB() ? $this->getDB()->getDatabaseId() : null ;

		// Create model
		if (class_exists($_m)) {
			$model = new $_m($this, $database );

			return $model;
		} else {
			return new Model($this, $database );
		}
	}
	
	/**
	 * [DEPRECATED]
	 * 
	 * @see Controller::setModels
	 * @param type $controllerName 
	 */
	public function reloadModel($controllerName) {
		$this->setModel($this->getNewModel($controllerName));
	}

	protected function createViewFromAction($actionName, $mode = 'html') {
		$this->createView($mode . DS . uncamelize($this->name) . DS . uncamelize($actionName) . '.thtml');
	}

	protected function bindToView(View &$view) {
		$this->view = $view;

		$this->view->set('input_data', $this->data);
	}

	protected function createView($viewPath = null, $mode = 'html') {
		if (is_null($viewPath)) {
			$viewPath = $mode . DS . uncamelize($this->name)
				. DS . uncamelize($this->action)
				. (strpos($this->action, '.') === false ? '.thtml' : '');
		}

		$this->viewPath = $viewPath;

		if ($this->view == null) {
			$this->view = new Template($this->viewPath, $this->title);

			$this->view->set('input_data', $this->data);
		} else {

			$this->view->appendToTitle($this->title);
			$this->view->setFile($viewPath);
		}

		return $this->view;
	}

	protected function renderView() {
		if (is_null($this->view)) {
			return;
		}

		if ($this->view->isRendered() == false) {
			$this->view->set('__responses', $this->responses);
			$this->view->render();
		}
	}

	function __destruct() {
		if (!headers_sent() && !empty($this->responses)) {
			App::getSession()->set('Controller.responses', $this->responses);
		}
	}

	//////////////////////////////////////////////////////////////
	////////// STATIC PART

	/**
	 * Paths to controllers
	 * @var array
	 */
	private static $_paths = array();

	/**
	 * Last instance of controller
	 * @var Controller
	 */
	private static $_ctrl = array();

	/**
	 * Adds some paths where controllers could be (use this method in your app-conf.php file)
	 * 
	 * @param string $controllersFolder
	 */
	static function addPath(string $controllersFolder) {
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
	static function requireController($controllerName, $action) {
		$paths = array_merge(array(
			ROOT . 'app' . DS . 'controllers' . DS,
			ROOT . 'controllers' . DS,
			AE_CONTROLLERS
			), self::$_paths);

		if (strpos($action, '.') !== false) {
			$action = substr($action, 0, strpos($action, '.'));
		}

		$modelName = camelize($controllerName) . 'Model';
		$controllerName = camelize($controllerName) . 'Controller';
		$action = camelize($action);

		foreach ($paths as $p) {
			if (is_file($p . $controllerName . '.php')) {
				require_once($p . $controllerName . '.php');

				if (!class_exists($controllerName)) {
					if (debuggin()) {
						App::do404($controllerName . ' is not defined as class.');
					}
					return false;
				}

				if (!method_exists($controllerName, $action)) {
					if (debuggin()) {
						App::do404($action . ' is not defined in class ' . $controllerName);
					}
					return false;
				}

				if (!is_public_controller_method($controllerName, $action) || substr($controllerName, 0, 1) == '_') {
					if (debuggin()) {
						App::do404($action . ' is not public in class ' . $controllerName);
					}
					return false;
				}


				return true;

				break;
			}
		}
		return false;
	}

	/**
	 * A static function to instanciate a controller and launch its corresponding action.
	 * 
	 * @param string $controllerName
	 * @return Controller
	 */
	static function launchController($controllerName, $action, $mainParam = null, $controllerParams = array(), $othersParams = array()) {
		// Format names
		$_n = camelize($controllerName) . 'Controller';
		$_m = camelize($controllerName) . 'Model';

		$viewAction = $action;

		if (strpos($action, '.') !== false) {
			$action = substr($action, 0, strpos($action, '.'));
		}

		$action = lcfirst(camelize($action));

		// Format parameters
		$params = array();
		if (!is_null($mainParam)) {
			$params[] = $mainParam;
		}
		if (!is_null($othersParams) && !empty($othersParams)) {
			$params = array_merge($params, $othersParams);
		}

		// Instanciate controller
		$controller = new $_n ();

		// Setting controller properties
		foreach ($controllerParams as $k => $v) {
			@$controller->{$k} = $v;
		}

		// Setting controller id
		$controller->setIDS($controllerName, $viewAction, $_m);

		self::$_ctrl = $controller;

		$controller->reloadModel($controllerName);


		// Let's controller manager view creation
		$controller->createView();



		// Call beforeAction
		$controller->beforeAction($action);

		// Call the action !
		$res = call_user_func_array(array($controller, $action), $params);

		// After the action : call afterAction
		$controller->afterAction($action);

		// End App (write session, close DB engines....) without die
		App::end(false);

		// If rendering required, let's render
		if ($controller->avoidRender == false) {
			$controller->renderView();
		}

		// After render
		$controller->afterRender();

		// We're done
		return $controller;
	}

	/**
	 * Returns the current loaded controller
	 * 
	 * @return Controller
	 */
	static function getCurrent() {
		return self::$_ctrl;
	}

	/**
	 * Returns true if a controller has been loaded
	 * 
	 * @return boolean
	 */
	static function hasCurrent() {
		return is_object(self::$_ctrl);
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
	static function shutdown() {
		if (self::hasCurrent()) {
			self::$_ctrl->__destruct();
			self::$_ctrl = null;
		}
	}

	protected function beforeAction($action) {
		
	}

	protected function afterAction($action) {
		
	}

	protected function afterRender() {
		
	}

}

?>
