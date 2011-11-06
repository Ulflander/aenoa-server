<?php

class Webpage extends Template {

	////////////////////////////////////
	// Static part


	private static $default = 'index';
	private static $current = 'index';
	public static $pages = array();

	public static function registerWebpage($filename, $title, $parentFilename = null, $isDefault = false) {
		$page = array();

		$filename = self::solveFilename($filename) ;

		$page['filename'] = $filename;
		$page['title'] = $title;
		$page['childs'] = array();

		if ($isDefault == true) {
			self::$default = $filename;
		}

		if ($parentFilename == null) {
			array_push(self::$pages, $page);

			return true;
		} else {
			$parent = self::__getPage($parentFilename, self::$pages);
			if (!is_null($parent)) {
				array_push($parent['childs'], $page);
				return true;
			}
		}

		return false;
	}

	private static function __getPage($filename, &$arr) {

		$filename = self::solveFilename($filename) ;

		foreach ($arr as &$page) {
			if ($page['filename'] == $filename) {
				return $page;
			} else if (!empty($page['childs'])) {
				return self::__getPage($filename, $page['childs']);
			}
		}

		return null;
	}

	public static function getWebpagePages() {
		return self::$pages;
	}

	public static function getCurrent() {
		return self::$current;
	}

	public static function getCurrentTitle() {
		$page = self::__getPage(self::$current, self::$pages);
		if ($page) {
			return $page['title'];
		} else {
			return '';
		}
	}


	public static function solveFilename ( $filename )
	{
		if ( strpos( $filename , '.html') === false && strpos( $filename , '.ehtml' ) === false )
		{
			return $filename . '.html' ;
		}

		return $filename ;
	}

	public static function getDefault() {
		return self::$default;
	}

	////////////////////////////////////
	// Dynamic part

	private $_curWebpage = 'index';

	/**
	 * Constructor of Webpage
	 *
	 * The template object is usable both in Aenoa dev-kit and in Aenoa server
	 *
	 * @param object $file The main template file to use
	 * @param object $title [optional] The title of the page (only used for HTML page)
	 * @return
	 */
	function __construct($webpage = null, $title = '', $autoGetWebpage = true) {

		if ( !is_null( $webpage ) )
		{
			$webpage = self::solveFilename($webpage) ;
		}

		parent::__construct(AE_TEMPLATES . 'html' . DS . 'webpage.' . $this->getExtension(), Config::get(App::APP_NAME));

		if ($title != $this->title) {
			$this->appendToTitle($title);
		}


		if (App::getSession()->has('Controller.responses')) {
			$this->set('__responses', App::getSession()->get('Controller.responses'));
			App::getSession()->uset('Controller.responses');
		}

		if ($autoGetWebpage === true) {
			$query = App::getQuery();

			if ($this->setWebpage($query) == false) {
				App::do404('Webpage not found');
			}
		} else {
			$this->setWebpage($webpage);
		}
	}

	function setWebpage($webpage) {

		$webpage = self::solveFilename($webpage) ;

		if ($this->webpageExists($webpage) == true) {
			$this->_curWebpage = $webpage;

			self::$current = $webpage;

			$title = self::getCurrentTitle();

			if ($title) {
				$this->title .= ' | ' . $title;
			}

			return true;
		}

		return false;
	}

	function render($echo = true) {
		parent::render($echo);
	}

	function webpageExists($webpage = null) {
		return!is_null($webpage) && is_file(ROOT . 'app' . DS . 'webpages' . DS . self::solveFilename($webpage) );
	}

	function currentWebpageValid() {
		return!is_null($this->_curWebpage);
	}

	function renderWebpage()
	{

		extract($this->getAll());

		if ( preg_match('/\.ehtml$/', $this->_curWebpage ) )
		{
			$ehtml = new AeEHtml () ;

			$ehtml->fromFileToFile(ROOT . 'app' . DS . 'webpages' . DS . $this->_curWebpage , AE_TMP . str_replace('/', '__' , $this->_curWebpage ) ) ;

			include AE_TMP . str_replace('/', '__' , $this->_curWebpage ) ;

			return;
		}

		include ROOT . 'app' . DS . 'webpages' . DS . $this->_curWebpage ;
		
	}

	function getWebpagesMenu() {
		return self::$pages;
	}
}

?>