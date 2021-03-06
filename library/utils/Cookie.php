<?php

/**
 * Class: Cookie
 *
 * Util classe to manage cookies
 * 
 * Cookie is an extension of <Collection> classe, so data of cookie is available through the <Collection> API
 * 
 * See also:
 * <Collection>, <User>
 */
class Cookie extends FlushableCollection {
	// 60 days : (60 * 60 * 24 * 60)

	/**
	 * Number of seconds before expiration of cookie
	 * 
	 * Default: 60 days
	 * 
	 * @var int 
	 */
	public $expire = 5184000;

	private $_name = 'AE';
	private $_path = '/';
	private $_domain = null;

	/**
	 * Creates a new Cookie instance
	 * 
	 * @param string $name
	 * @param string $path 
	 */
	function __construct($name = 'AE', $path = '/') {
		
		$this->setName($name);

		$this->setPath($path);

		if (Config::has(App::APP_COOKIE_DOMAIN)) {
			$this->setDomain(Config::get(App::APP_COOKIE_DOMAIN));
		} else if (strpos(url(), 'http://localhost') === 0) {
			$this->setDomain('localhost');
		}

		$this->refresh();
	}

	/**
	 * Set name of the cookie
	 * 
	 * @param string $name Name of the cookie
	 * @return Cookie Current instance for chained command on this element
	 */
	function setName($name = 'AE') {

		if (is_string($name) && $name != '') {
			$this->_name = $name;
		} else {
			App::do500('Cookie name not valid: ' . $name, __FILE__, __LINE__ - 3);
		}

		return $this;
	}

	/**
	 * Get the name of the cookie
	 * 
	 * @return string Current name of cookie
	 */
	function getName() {
		return $this->_name;
	}

	/**
	 * Set the path of the cookie
	 * 
	 * @param string $path New path of the cookie
	 * @return Cookie Current instance for chained command on this element
	 */
	function setPath($path) {
		$this->_path = $path;

		return $this;
	}

	/**
	 * Get the path of the cookie
	 * 
	 * @return string Current path of the cookie 
	 */
	function getPath() {
		return $this->_path;
	}

	/**
	 * Set the domain of the cookie
	 * 
	 * @param string $domain
	 * @return Cookie Current instance for chained command on this element
	 */
	function setDomain($domain) {
		if ($domain == 'localhost') {
			$this->_domain = false;
		}

		$this->_domain = $domain;

		return $this;
	}

	/**
	 * Get the cookie domain
	 * 
	 * @return string The cookie domain
	 */
	function getDomain() {
		return $this->_domain;
	}

	/**
	 * Refresh the cookie data from user cookies
	 * 
	 * @return Cookie Current instance for chained command on this element
	 */
	function refresh() {
		if (ake($this->_name, $_COOKIE)) {
			$vars = $_COOKIE[$this->_name];
			$vars = explode(',', $vars);

			foreach ($vars as $var) {
				$var = explode('.', $var);
				$this->set($var[0], $var[1]);
			}
		}

		return $this;
	}
	
	/**
	 * Flush cookie data
	 * 
	 * Trigger a App::do500 error in case of failure
	 * 
	 * @see App::do500
	 * @return Cookie Current instance for chained command on this element
	 */
	function flush() {
		$value = $this->getAll();
		$cookie = array();
		foreach ($value as $k => $v) {
			$cookie[] = $k . '.' . $v;
		}

		$cookie = implode(',', $cookie);

		if (headers_sent()) {
			$this->_triggerFlushError(__LINE__ - 2);
		}

		if (is_null($this->_domain)) {
			if (!setcookie($this->_name, $cookie, time() + $this->expire, $this->_path)) {
				$this->_triggerFlushError(__LINE__ - 2);
			}
		} else if (!setcookie($this->_name, $cookie, time() + $this->expire, $this->_path, $this->_domain)) {
			$this->_triggerFlushError(__LINE__ - 2);
		}

		return $this;
	}

	private function _triggerFlushError($line) {
		App::do500('Cookie not written', __FILE__, $line);
	}

}

?>