<?php

/**
 * Internationalization class for Aenoa Server
 *
 */
class I18n extends ConfDriven {

	private $_dir = '';
	private $_localePath = '';
	private $_domain;
	private $_currentLanguage = '';
	private $_langs = '';

	/**
	 * 
	 * @var I18n
	 */
	private static $mainInstance = null;
	
	private static $locales = null;

	function __construct($locale = null, $domain = 'default', $codeset = 'UTF8', $path = null) {
		
		$this->file = AE_APP . 'transliterations' ;
		
		parent::__construct() ;
		
		if (is_null($locale)) {
			if (App::getSession() && App::getSession()->has('I18n.lang')) {
				$locale = App::getSession()->get('I18n.lang');
			} else if (App::getUser()->hasProperty('Webkupi.locale')) {
				$locale = App::getUser()->getProperty('Webkupi.locale');
			} else {
				$locale = Config::get(App::APP_LANG);
			}
		}
		
		$dir = $this->switchTo( $locale, $domain, $codeset , $path  ) ;
		
		if (is_null(self::$mainInstance))
		{
		
			self::$mainInstance = &$this;

			if ($dir == '') {
				if ( !debuggin() ) {
					
					App::do500('Localization initialization failed. This message is only shown in production mode.', __FILE__ );
					
				} else {
					
					trigger_error ('Localization initialization failed') ;
					
					if (!function_exists('_')) {

						function _($str) {
							return $str;
						}

						function ngettext($str1, $str2, $c) {
							if ($c == 1) {
								return $str1;
							}
							return $str2;
						}

					}
					return;
				}
			}
		}


		bind_textdomain_codeset($this->_domain, 'UTF8');

		textdomain($this->_domain);
	}

	private function _getLocale($lang) {
		putenv('LC_MESSAGES=' . $lang);

		bindtextdomain($this->_domain, $this->_localePath);

		return setlocale(LC_MESSAGES, $lang);
	}

	private function __getLocale($lang) {
		putenv('LC_MESSAGES=' . $lang);

		_bindtextdomain($this->_domain, $this->_localePath);

		return _setlocale(LC_MESSAGES, $lang);
	}

	function getCurrent() {
		return $this->_currentLanguage;
	}
	
	
	function switchTo ( $locale , $domain = 'default', $codeset = 'UTF8', $path = null )
	{
		$dir = '';

		$this->_currentLanguage = $locale;

		$this->_domain = $domain;

		if (is_null($path)) {
			$this->_localePath = ROOT . 'app' . DS . 'locale' . DS;
		} else {
			$this->_localePath = $path;
		}

		if (function_exists('bindtextdomain')){
			$dir = $this->_getLocale($locale);

			if ($dir == '') {
				$dir = $this->_getLocale($locale . '.' . $codeset);
			}
		} else if ( function_exists('_bindtextdomain') ) {
			$dir = $this->__getLocale($locale);

			if ($dir == '') {
				$dir = $this->__getLocale($locale . '.' . $codeset);
			}
		}
		
		return $dir ;
		
	}
	

	function switchSessionTo( $locale ) {
		if (in_array($newlang, $this->getLangList())) {
			App::getSession()->set('I18n.lang', $newlang);

			$this->_currentLanguage = $newlang;

			return true;
		}

		return false;
	}

	function getLangList() {
		if (!is_null(self::$locales)) {
			return self::$locales;
		}

		global $FILE_UTIL;
		$dirs = $FILE_UTIL->getDirsList('app' . DS . 'locale', false);
		$locales = array();
		foreach ($dirs as $dir) {
			$locales[] = $dir['name'];
		}

		self::$locales = $locales;

		return $locales;
	}
	
	static function getCurrentLanguage() {
		return self::$mainInstance->getCurrent();
	}
	
	static function defined() {
		return!is_null(self::$mainInstance);
	}

	/**
	 * Override of ConfDriven::parseConf
	 * 
	 * @private
	 */
	protected function parseConf ( $values = array () )
	{
		foreach ($values as $val ) {
			
			if (strpos($val, '>') === false)
			{
				continue;
			}

			$v = explode('>', $val);
			
			$from = trim($v[0]);
			$to = trim($v[1]);
			
			if (mb_strlen($from , 'UTF-8') == 1)
			{
				tl_set ( $from , $to ) ;
			} else {
				tl_add ( $from , $to ) ;
			}
		}
	}
	
}

?>