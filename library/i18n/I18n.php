<?php

/**
 * Internationalization class for Aenoa Server
 *
 *
 * It inits PHP_gettext module and offers methods to quickly change language.
 *
 * It loads transliteration file if this file exists
 *
 */
class I18n extends ConfDriven {


	private $_localePath = '';
	
	private $_domain;

	private $_currentLanguage = '';

	/**
	 * 
	 * @var I18n
	 */
	private static $mainInstance = null;

	private static $locales = null;

	/**
	 * Create a new I18n instance
	 *
	 * @param type $locale
	 * @param type $domain
	 * @param type $codeset
	 * @param type $path
	 */
	function __construct($locale = null, $domain = 'default', $codeset = 'UTF8', $path = null) {

		parent::__construct(AE_APP . 'transliterations');

		if (is_null($locale)) {
			if (App::getSession() && App::getSession()->has('I18n.lang')) {
				$locale = App::getSession()->get('I18n.lang');
			} else if (App::getUser()->hasProperty('Webkupi.locale')) {
				$locale = App::getUser()->getProperty('Webkupi.locale');
			} else {
				$locale = Config::get(App::APP_LANG);
			}
		}

		$dir = $this->switchTo($locale, $domain, $codeset, $path);

		if (is_null(self::$mainInstance)) {

			self::$mainInstance = &$this;

			if ($dir == '') {

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

				if (!debuggin()) {

					//	App::do500('Localization initialization failed. This message is only shown in production mode.', __FILE__ );
				} else {

					//	trigger_error('Localization initialization failed');
				}


				return;
			}
		}


		bind_textdomain_codeset($this->_domain, 'UTF8');

		textdomain($this->_domain);
	}

	private function _getLocale($locale) {

		if (!putenv('LC_MESSAGES=' . $locale) && debuggin()) {
			trigger_error('Localization initialization failed: env var not set');
		}

		bindtextdomain($this->_domain, $this->_localePath);

		return setlocale(LC_MESSAGES, $locale);
	}

	function getCurrent() {
		return $this->_currentLanguage;
	}

	function switchTo($locale, $domain = 'default', $codeset = 'UTF8', $path = null) {
		$dir = '';

		$this->_currentLanguage = $locale;

		$this->_domain = $domain;

		if (is_null($path)) {
			$this->_localePath = ROOT . 'app' . DS . 'locale' . DS;
		} else {
			$this->_localePath = $path;
		}

		if (function_exists('bindtextdomain')) {
			$dir = $this->_getLocale($locale);

			if ($dir == '') {
				$dir = $this->_getLocale($locale . '.' . $codeset);
			}
		}

		return $dir;
	}

	function switchSessionTo($locale) {

		if (in_array($locale, $this->getLangList())) {
			App::getSession()->set('I18n.lang', $locale);

			$this->_currentLanguage = $locale;

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
	 * @see ConfDriven::parseConf
	 * @private
	 */
	protected function parseConf($values = array()) {
		foreach ($values as $val) {

			if (strpos($val, '>') === false) {
				continue;
			}

			$v = explode('>', $val);

			$from = trim($v[0]);
			$to = trim($v[1]);

			if (mb_strlen($from, 'UTF-8') == 1) {
				tl_set($from, $to);
			} else {
				tl_add($from, $to);
			}
		}
	}

}

?>