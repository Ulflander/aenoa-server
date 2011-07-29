<?php


class Webpage extends Template {
	
	////////////////////////////////////
	// Static part
	
	
	private static $default = 'index' ;
	
	private static $current = 'index' ;
	
	public static $pages = array () ;
	
	public static function registerWebpage ( $filename , $title , $parentFilename = null , $isDefault = false ) 
	{
		$page = array () ;
		
		$page['filename'] = $filename ;
		$page['title'] = $title ;
		$page['childs'] = array () ;
		
		if ( $isDefault == true )
		{
			self::$default = $filename ; 
		}
		
		if ( $parentFilename == null )
		{
			array_push ( self::$pages , $page ) ;
			
			return true ;
		} else {
			$parent = self::__getPage ( $parentFilename, self::$pages ) ;
			if ( !is_null ( $parent ) )
			{
				array_push ( $parent['childs'] , $page ) ;
				return true ;
			}
		}
		
		return false ;
	}
	
	private static function __getPage ( $filename, &$arr )
	{
		foreach ( $arr as &$page )
		{
			if ( $page['filename'] == $filename )
			{
				return $page;
			} else if ( !empty ( $page['childs'] ) )
			{
				return self::__getPage ( $filename , $page['childs'] ) ;
			}
		}
		
		return null ;
	}
	
	public static function getWebpagePages ()
	{
		return self::$pages ;
	}
	public static function getCurrent ()
	{
		return self::$current ;
	}
	public static function getCurrentTitle ()
	{
		$page = self::__getPage ( self::$current , self::$pages ) ;
		if ( $page )
		{
			return $page['title'] ;
		} else {
			return '' ;
		}
	}
	public static function getDefault ()
	{
		return self::$default ;
	}
	
	
	////////////////////////////////////
	// Dynamic part
	
	private $_curWebpage = 'index' ;
	
	/**
	 * Constructor of Webpage
	 *
	 * The template object is usable both in Aenoa dev-kit and in Aenoa server
	 * 
	 * @param object $file The main template file to use
	 * @param object $title [optional] The title of the page (only used for HTML page)
	 * @return 
	 */
	function __construct ( $webpage = null , $title = '' , $autoGetWebpage = true )
	{
		parent::__construct ( AE_TEMPLATES. 'html' . DS . 'webpage.' . $this->getExtension () , Config::get(App::APP_NAME) ) ;
		
		if ( $title != $this->title )
		{
			$this->appendToTitle( $title ) ;
		}
		
		if ( $autoGetWebpage === true && App::$sanitizer->exists ( 'GET' , 'query' ) )
		{
			$query = App::$sanitizer->get ( 'GET' , 'query' ) ;
			
			if ( $this->setWebpage ( $query ) == false )
			{
				App::do404 () ;
			}
		} else {
			$this->setWebpage ( $webpage ) ;
		}
	}

	
	function setWebpage ( $webpage )
	{
		if ( $this->webpageExists($webpage) == true )
		{
			$this->_curWebpage = $webpage ;
			
			self::$current = $webpage ;
			
			$title = self::getCurrentTitle() ;
			
			if ( $title )
			{
				$this->title .= ' | ' . $title ;
			}
			
			return true ;
		}
		
		return false ;
	}
	
	function render ( $echo = true )
	{
		parent::render ( $echo ) ;
	}
	
	function webpageExists ( $webpage = null )
	{
		return !is_null( $webpage ) && is_file ( ROOT .'app'.DS. 'webpages' . DS . $webpage . '.html' ) ;
	}
	
	function currentWebpageValid ()
	{
		return !is_null ( $this->_curWebpage ) ;
	}
	
	function renderWebpage ()
	{
		extract ( $this->getAll() ) ;
		
		include ROOT .'app'.DS. 'webpages' . DS . $this->_curWebpage . '.html' ;
	}
	
	function getWebpagesMenu ()
	{
		return self::$pages ;
	}
}
?>