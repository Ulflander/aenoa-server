<?php

/**
 * Template is used both for templates rendering
 * .
 * Use it in your services, controllers to easily render XML or HTML content
 */
class Template extends View {
	
	/**
	 * The root path to templates
	 * @var
	 */
	private $root ;
	
	/**
	 * Variables array which will be extracted when template will be rendered.
	 * @var
	 */
	public $vars = array () ;
	
	/**
	 * The template file used to render a view.
	 * @var
	 */
	private $file ;
	
	/**
	 * The CSS files to embed
	 * @var
	 */
	private $cssFiles = array () ;
	
	/**
	 * The JS files to embed
	 * @var
	 */
	private $jsFiles = array () ;
	
	/**
	 * Title of the page
	 * @var
	 */
	public $title ;
	
	/**
	 * Use the ACF: Aenoa CSS framework
	 */
	public $useACF = true ;
	
	/**
	 * Use the AJSF: Aenoa JS framework
	 */
	public $useAJSF = true ;
	
	/**
	 * Does the template use the layout file or render template directly
	 * @var boolean
	 */
	public $useLayout = false ;
	
	/**
	 * Variable: layoutName
	 * 
	 * Name of the layout to use
	 * 
	 * Setting another for 
	 * 
	 * @var string
	 */
	protected $_layoutName = 'layout' ;
	

	/**
	 * Is the template a simple webpage
	 */
	public $webpage = false ;
	
	/**
	 * FSUtil tool
	 * @var FSUtil
	 */
	public $futil ;
	
	
	protected $prependFile = null ;
	
	protected $appendFile = null ;
	
	
	/**
	 * A static reference to the master template
	 * 
	 * @var Template
	 */
	private static $masterTemplate ;
	
	
	private static $_paths = array () ;
	
	private $subTemplates = array () ;
	
	protected $uniqueWidgetID = '' ;
	
	private $_widgetIndex = 0 ;
	
	protected $_mode = 'html' ;
	
	/**
	 * Constructor of template
	 *
	 * The template object is usable both in Aenoa dev-kit and in Aenoa server
	 * 
	 * @param object $file The main template file to use
	 * @param object $title [optional] The title of the page (only used for HTML page)
	 * @return 
	 */
	function __construct ( $file = null , $title = '' , $linkToMaster = false )
	{
		global $FILE_UTIL;
		
		$this->futil = &$FILE_UTIL;
		
		$this->setMode('html');
		
		if ( is_null ( self::$masterTemplate ) )
		{
			self::$masterTemplate = &$this ;
		} else if ( $linkToMaster === true )
		{
			self::$masterTemplate->registerSub ( $this ) ;
		} else if ( is_a ( $linkToMaster , 'Template' ) )
		{
			$linkToMaster->registerSub ( $this ) ;
		}
		
		if ( $this->futil->dirExists ( ROOT . 'templates' . DS ) )
		{
			self::$_paths[] = setTrailingDS(ROOT . 'templates' . DS) ;
		}
	
		if ( $this->futil->dirExists ( ROOT . 'app' . DS . 'templates' . DS ) )
		{
			self::$_paths[] = setTrailingDS(ROOT . 'app' . DS . 'templates' . DS) ;
		}
		
		self::$_paths[] = setTrailingDS(AE_TEMPLATES) ;
		
		$this->setFile ( $file ) ;
		
		$this->title = $title ; 
		
		$this->set ( 'input_data' , array () ) ;
		
		$this->set ( 'title_class' , '' ) ;
		
		$this->set ( 'content_for_layout' , '' ) ;
		
		$this->set ( 'is_home' , true ) ;
		
	}
	
	public function __set($attribute, $value)
	{
		
		if ( $attribute == 'layoutName' )
		{
			$this->useLayout = true ;
			$this->_layoutName = $value;
		}
		
	}
	
	public function __get($attribute)
	{
	
		if ( $attribute == 'layoutName' )
		{
			return $this->_layoutName ;
		}
		
	}
	
	
	/**
	 * Get current rendering mode (HTML, email, xml...)
	 * @return string The current rendering mode
	 */
	function getMode ()
	{
		return $this->_mode ;
	}
	
	/**
	 * Set current rendering mode (HTML, email, xml...)
	 * @param mode string 'html', 'email' , 'xml' or any mode that you want to create (csv, SQL, ...)
	 * 
	 */
	function setMode ( $mode )
	{
		if ( $mode == 'html' || $mode == 'email')
		{
			$this->_mode = $mode;
		}
		
		switch ($this->_mode)
		{
			case 'html':
			case 'email':
				$this->addBehavior('HTML') ;
		}
		
	}
	
	
	/**
	 * Get templates file extension depending on current rendering mode
	 * 
	 * @return string The file extension : will returns 'thtml' if mode is 'email' or 'html', or will return the mode as extension for any other new mode ('xml' mode will returns 'xml' extension...)
	 */
	function getExtension ()
	{
		switch ($this->_mode)
		{
			case 'html':
			case 'email':
				return 'thtml' ;
		}
		return $this->_mode ;
	}
	
	function setTitle ( $title )
	{
		$this->title = $title ;
	}
	
	function appendToTitle ( $subtitle = '' , $sep = ' | ' )
	{
		if ( $subtitle == '' || $subtitle == $this->title )
		{
			return ;
		}
		$arr = explode ( $sep, $this->title ) ;
		array_unshift($arr, $subtitle ) ;
		array_clean($arr);
		$this->title = implode( $sep , $arr ) ; 
	}
	
	static function addTemplatesPath ( $path )
	{
		if ( is_dir ( $path ) )
		{
			self::$_paths[] = setTrailingDS($path) ;
		}
	}
	
	static function hasCustom ( $file )
	{
		$file = 'html' . DS . $file . '.thtml' ;
		
		foreach ( self::$_paths as $path )
		{
			if ( $path != AE_TEMPLATES && file_exists ( $path . $file ) )
			{
				return true ;
			}
		}
		
		return false ;
	}
	
	
	function setFile ( $file )
	{
		$this->file = $file ;
	}
	
	function getFile ( $file )
	{
		if ( file_exists ( $file ) )
		{
			return $file ;
		}
		
		foreach ( self::$_paths as $path )
		{
			if ( File::sexists ( $path . $file ) )
			{
				return $path . $file ;
			}
		}
		
		return null ;
	}
	
	function registerSub ( Template &$template )
	{
		if ( ! in_array ( $template, $this->subTemplates ) )
		{
			$this->subTemplates[] = $template ;
			foreach ( $this->vars as $k => $v )
			{
				$template->set ( $k , $v ) ;
			}
		}
	}
	
	/**
	 * Delete a variable in template variables array.
	 * 
	 * @param object $name Name of the variable to delete
	 * @return 
	 */
	function reset ( $name )
	{
		if ( array_key_exists($name, $this->vars) )
		{
			unset ( $this->vars[$name] ) ;
		
			foreach ( $this->subTemplates as &$template )
			{
				$template->reset ( $name ) ;
			}
		}
	}
	
	/**
	 * Set a variable in template variables array.
	 * 
	 * @param object $name Name of the variable
	 * @param object $val Value of the variable
	 * @return 
	 */
	function set ( $name , $val )
	{
		$this->vars[$name] = $val ;
		
		foreach ( $this->subTemplates as &$template )
		{
			$template->set ( $name , $val ) ;
		}
	}
	
	
	/**
	 * Set all variables from an array in template variables array.
	 * 
	 * @param object $array Array of variables
	 * @return 
	 */
	function setAll ( $array )
	{
		foreach ( $array as $k => $v )
		{
			$this->set ( $k , $v ) ;
		}
	}
	
	
	/**
	 * Get a variable in template variables array.
	 * 
	 * @param object $name Name of the variable
	 * @param object $val Value of the variable
	 * @return 
	 */
	function get ( $name )
	{
		if ( $this->has ( $name ) )
		{
			return $this->vars[$name] ;
		}
		
		return '' ;
	}

	
	/**
	 * Get all variables from template variables array.
	 * 
	 * @return array
	 */
	function getAll ()
	{
		return $this->vars ;
	}
	
	/**
	 * Check if a variable exists in template variables array.
	 * 
	 * @param object $name Name of the variable
	 * @return 
	 */
	function has ( $name )
	{
		return array_key_exists($name, $this->vars) ;
	}
	
	
	/**
	 * Render a template element.
	 * 
	 * @param object $file The element file to render.
	 * @return 
	 */
	function renderElement ( $file )
	{
		$title = $this->title ;
		
		extract ( $this->vars ) ;
		
		$file = $this->getFile ( $this->_mode . DS . 'elements' . DS . $file . '.' . $this->getExtension () ) ;
		
		if ( !is_null ( $file ) )
		{
			include ( $file ) ;
		}
	}

	
	/**
	 * Render a template element.
	 * 
	 * @param object $file The element file to render.
	 * @return 
	 */
	function renderWidget ( $file , $options = array () )
	{
		$title = $this->title ;
		extract ( $this->vars ) ;
		$this->uniqueWidgetID = 'aewidg_' . urlize($file, '_') . '_' . $this->_widgetIndex ; 
		$file = $this->getFile ( $this->_mode . DS . 'widgets' . DS . $file . '.' . $this->getExtension () ) ;
		$widget = $this->getWidgetOptions ( $options ) ;
		if ( !is_null ( $file ) )
		{
			
			$this->_widgetIndex ++ ;
			echo $widget['before'] ;
			include ( $file ) ;
			echo $widget['after'] ;
		}
	}
	
	function getWidgetOptions ( $options = array () )
	{
		if ( array_key_exists('tag',$options) == false ) $options['tag'] = 'ul' ;
		if ( array_key_exists('subtag',$options) == false ) $options['subtag'] = 'li' ;
		if ( array_key_exists('class',$options) == false ) $options['class'] = 'inline no-style-type' ;
		if ( array_key_exists('subclass',$options) == false ) $options['subclass'] = '' ;
		if ( array_key_exists('before',$options) == false ) $options['before'] = '' ;
		if ( array_key_exists('after',$options) == false ) $options['after'] = '' ;
		
		return $options ;
	}
	
	/**
	 * Render the template.
	 * 
	 * @return 
	 */
	function render ( $echo = true ) 
	{
		if ( $this->rendered == true )
		{
			return ;
		}
		
		if ( $echo && headers_sent() == false )
		{
			header('Content-type: text/html; charset=utf-8');
		}
		
		if ( !is_null(App::getSession()) && ($user = App::$session->getUser() ) && $user->isLogged () )
		{
			$this->set ( 'user_object' , $user ) ;
			$this->set ( 'user_super' , $user->isLevel(0) ) ;
		} else {
			$this->set ( 'user_object' , null ) ;
			$this->set ( 'user_super' , false ) ;
		}
			
		$title = $this->title ;
		
		extract ( $this->vars ) ;
		
		$content_for_layout = '' ;
		
		$file = $this->getFile ( $this->file ) ;
		
		if ( $echo == false || $this->useLayout )
		{
			ob_start () ;
		}
		
		if ( !is_null($this->prependFile) )
		{
			include($this->prependFile);
		}
		
		
		if ( !is_null($file) )
		{
			include ( $file ) ;
		} else if ( debuggin() )
		{
			pr('No template file: ' . $this->file ) ;
		}
		
		if ( !is_null($this->appendFile) )
		{
			include($this->appendFile);
		}
		
		
		
		$this->rendered = true ;
		
		
		if ( $echo == false || $this->useLayout )
		{
			$content_for_layout .= ob_get_contents () ;
			
			ob_end_clean() ;
			
			if ( $this->useLayout && $echo == true)
			{
				$file = $this->getFile ( $this->_mode . DS . 'layouts' . DS . $this->layoutName . '.' . $this->getExtension () ) ;
				
				if ( is_null ( $file ) )
				{
				    $file = $this->getFile ( $this->_mode . DS . $this->layoutName . '.' . $this->getExtension () ) ;
				}
				
				if ( !is_null ( $file ) )
				{
					include ( $file ) ;
				} else {
					echo 'No layout file: ' . $this->file ;
				}
			}
			
			return $content_for_layout ;
		}
		
		flush() ;
		
	}
	
	
	/**
	 * Adds a CSS file to embed in the main template page
	 * 
	 * 
	 * @param object $cssFilename The name of the CSS file
	 * @return 
	 */
	function addCSS ( $cssFilename )
	{
		$this->cssFiles[] = $cssFilename ;
	}
	
	
	/**
	 * Adds a Javascript file to embed in the main template page
	 * 
	 * 
	 * @param object $jsFilename The name of the JS file
	 * @return 
	 */
	function addJS ( $jsFilename )
	{
		$this->jsFiles[] = $jsFilename ;
	}
	
	function prependTemplate ( $content )
	{
		$path =  $this->getFile($content ) ;
		if ($path)
		{
			$this->prependFile = $path ;
			return true ;
		}
		return false ;
	}
	
	function appendTemplate ( $content )
	{
		$path =  $this->getFile($content ) ;
		if ($path)
		{
			$this->appendFile = $path ;
			return true ;
		}
		return false ;
	}
	
	
}
?>