<?php

// Multilingual initialization
// USes 
/**
 * Internationalization class for Aenoa Server
 *
 */
class I18n {

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

	function __construct($domain = 'default', $lang = null, $codeset = 'UTF8', $path = null) {
		if (is_null($lang)) {
			if (App::getSession() && App::getSession()->has('I18n.lang')) {
				$lang = App::getSession()->get('I18n.lang');
			} else if (App::getUser()->hasProperty('Webkupi.locale')) {
				$lang = App::getUser()->getProperty('Webkupi.locale');
			} else {
				$lang = Config::get(App::APP_LANG);
			}
		}

		$dir = '';

		$this->_currentLanguage = $lang;

		$this->_domain = $domain;

		if (is_null($path)) {
			$this->_localePath = ROOT . 'app' . DS . 'locale' . DS;
		} else {
			$this->_localePath = $path;
		}

		if (function_exists('bindtextdomain')) {
			$dir = $this->_getLocale($lang);

			if ($dir == '') {
				$dir = $this->_getLocale($lang . '.' . $codeset);
			}
		}

		if (is_null(self::$mainInstance)) {
			self::$mainInstance = &$this;

			if ($dir == '') {
				if (!debuggin()) {
					App::do500('Localization initialization failed');
				} else {
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

	function getCurrent() {
		return $this->_currentLanguage;
	}

	function switchTo($newlang) {
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

}

?>