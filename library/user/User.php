<?php

/**
 * This is an Aenoa Server representation of a user.
 *
 * An instance of User is created at initialization of the app session.
 *
 *
 *
 * @see Session
 */
final class User {

	private $_data = array();
	private $_id = null;
	// This MUST be an email address.
	private $_identifier = null;
	private $_group = null;
	private $_firstname = null;
	private $_lastname = null;
	private $_properties = array();
	private $_logged = 0;
	private $_level = 100;
	private $_groups = array();
	private static $_currentlogged = null;
	private $_cookieName = 'AEUSER';
	private $_cookie;

	/**
	 * If user is not logged, send a 401 HTTP response and a HTML page that display the Authentication problem
	 *
	 * @return the current logged user
	 */
	public static function requireLogged() {
		if (is_null(self::$_currentlogged)) {
			App::do401('Require logged user');
		}

		return self::$_currentlogged;
	}

	/**
	 *
	 *
	 *
	 * @param string $identifier Identifier should be an email. If the instance if the first one (e.g. the one instanciated by Session)
	 */
	function __construct($identifier = null) {
		if (is_null($identifier)) {
			$this->_identifier = $identifier;
		}
		
		if ( Config::has(App::APP_COOKIE_NAME) )
		{
			$this->_cookieName = Config::get(App::APP_COOKIE_NAME) ;
		}

		if (!is_null(App::$session) && App::$session->get('User.logged') === 1 && is_null(self::$_currentlogged)) {
			
			$this->_cookie = new Cookie($this->_cookieName);

			$this->_identifier = App::$session->get('User.identifier');
			$this->_firstname = App::$session->get('User.firstname');
			$this->_lastname = App::$session->get('User.lastname');
			$this->_data = App::$session->get('User.data');
			$this->_group = App::$session->get('User.group');
			$this->_groups = App::$session->get('User.groups');
			$this->_level = App::$session->get('User.level');
			$this->_id = App::$session->get('User.id');
			$this->_properties = App::$session->get('User.properties');

			$this->_logged = 1;

			self::$_currentlogged = $this;
		}
	}

	function isLogged() {
		return $this->_logged === 1;
	}

	function isGod() {
		return $this->getTrueLevel() == 0;
	}

	function getIdentifier() {
		return $this->_identifier;
	}

	function getDatabaseId() {
		return $this->_id;
	}

	function getLastname() {
		return $this->_lastname;
	}

	function getFirstname() {
		return $this->_firstname;
	}

	function getFullName() {
		return $this->_firstname . ' ' . $this->_lastname;
	}

	function getData() {
		return $this->_data;
	}

	/**
	 * Get the cookie associated to th Aenoa Server User
	 * 
	 * @see Cookie
	 * @return Cookie The Cookie object
	 */
	function getCookie() {
		return $this->_cookie;
	}

	function unsetFakeLevel() {
		if ($this->_logged) {
			$this->_level = $this->_group['level'];
		}
	}

	function setFakeLevel($level) {
		if ($this->getTrueLevel() == 0) {
			$this->_level = $level;
			App::$session->set('User.level', $this->_level);
		}
	}

	function getTrueLevel() {
		if ($this->_logged) {
			return $this->_group['level'];
		}

		return 100;
	}

	function isLevel($level) {
		return $this->_level == $level;
	}

	function getLevel() {
		if ($this->_logged) {
			return $this->_level;
		}

		return 100;
	}

	function getGroup() {
		if ($this->_logged) {
			return $this->getGroupLabelByLevel($this->_level);
		}

		return _('Visitor');
	}

	function getTrueGroup() {
		if ($this->_logged) {
			return $this->_group['label'];
		}

		return _('Visitor');
	}

	function recheckPassword($sha1password) {
		if ($this->_logged == true) {
			$db = App::getDatabase('main');

			$user = $db->findFirst('ae_users', array('email' => $this->_identifier, 'password' => $sha1password));

			return!empty($user);
		}
		return false;
	}

	/**
	 * Log a user in the system
	 *
	 *
	 * @param string $email
	 * @param string $sha1password
	 * @return boolean True if successfully logged, false otherwise
	 */
	function login($email, $sha1password) {
		if ($this->_logged == false && !is_null(App::$session)) {
			$db = App::getDatabase('main');

			$user = $db->findFirst('ae_users', array('email' => $email, 'password' => $sha1password));

			$id_hash = sha1($email . $sha1password);

			if (!empty($user)) {
				$db->edit('ae_users', $user['id'], array('last_connection' => $db->getDatetime()));


				$user = $db->findChildren('ae_users', $user);
				$this->_logged = 1;
				$this->_identifier = $email;
				App::$session->regenerate();

				$this->_cookie = new Cookie($this->_cookieName);
				$this->_cookie->set('id', $user['id'] . '-'. $id_hash);
				
				$this->_id = $user['id'];
				$this->reloadInfos($user);

				App::$session->set('User.id', $this->_id);
				App::$session->set('User.logged', 1);
				App::$session->set('User.identifier', $email);
				App::$session->set('User.groups', $db->findAll('ae_groups'));
				App::$session->set('User.group', $this->_group);
				App::$session->set('User.level', $this->_level);


				App::$session->regenerate();

				self::$_currentlogged = $this;

				return true;
			}
		}


		return false;
	}

	function reloadInfos() {
		if ($this->isLogged()) {
			$db = App::getDatabase();
			$this->_loadInfos($db->findAndRelatives('ae_users', $this->_id));
		}
	}

	private function _loadInfos($user) {

		$this->_firstname = $user['firstname'];
		$this->_lastname = $user['lastname'];
		$this->_group = $user['group'];
		$this->_properties = $user['app_properties'];
		$this->_level = $user['group']['level'];
		$this->_data = is_array($user['user_info']) ? $user['user_info'] : array();

		App::$session->set('User.properties', $this->_properties);
		App::$session->set('User.firstname', $user['firstname']);
		App::$session->set('User.lastname', $user['lastname']);
		App::$session->set('User.data', $user['user_info']);
	}

	/**
	 * Set or unset the value of a property.
	 *
	 * You have to manually call User::flushProperties for saving newly set properties.
	 *
	 * Example:
	 *
	 * (start code)
	 * // Get the user
	 * $user = App::getUser () ;
	 *
	 * // Set the property
	 * $user->setProperty ( 'Foo', 'hello world' ) ;
	 *
	 *
	 * // Get the property
	 * echo $user->getProperty ( 'Foo' ) ; // Echo 'Hello world'
	 *
	 * // Save properties
	 * $user->flushProperties () ;
	 *
	 * // In chained command:
	 * $user->setProperty('Bar', 1337)->flushProperties () ;
	 * (end)
	 *
	 * @param string $prop Key of property
	 * @param mixed $val Value of property, if null the corresponding key will be destroyed. Default to null.
	 * @return User Current instance for chained command
	 */
	function setProperty($prop, $val = null) {
		if (is_null($val)) {
			if ($this->hasProperty($prop)) {
				unset($this->_properties[$prop]);
			}
		} else {
			$this->_properties[$prop] = $val;
		}

		return $this;
	}

	/**
	 * Save user properties into database
	 *
	 * @return User Current instance for chained command
	 */
	function flushProperties() {
		if ($this->isLogged()) {
			App::getDatabase('main')->edit('ae_users', $this->_id, array('app_properties' => $this->_properties));
		}

		return $this;
	}

	/**
	 * Tests if a property exists
	 *
	 * @param string $prop Key of property
	 * @return boolean True if property exists, false otherwise
	 */
	function hasProperty($prop) {
		return ake($prop, $this->_properties);
	}

	/**
	 * Returns the value of a property
	 *
	 * @param string $prop Key of property
	 * @return mixed Value if property exists, false otherwise
	 */
	function getProperty($prop) {
		if ($this->hasProperty($prop)) {
			return $this->_properties[$prop];
		}

		return false;
	}

	/**
	 * Get all application dedicated properties
	 *
	 * @return array An array of application dedicated properties
	 */
	function getProperties() {
		return $this->_properties;
	}

	function getGroupLabelByLevel($level) {
		foreach ($this->_groups as $group) {
			if ($group['level'] == $level) {
				return $group['label'];
			}
		}
		return null;
	}

	function getGroupList() {
		return $this->_groups;
	}

	function logout() {
		self::$_currentlogged = null;

		$this->_logged = false;

		App::$session->uset('User.*');

		App::$session->close(false);

		return false;
	}

	static function getClearNewPassword($length = 8) {
		$pass = '';
		$chr = '';

		$i = 0;
		while ($i < $length) {

			switch (rand(1, 3)) {
				case 1: $chr = chr(rand(50, 57));
					break;	 /* 2-9 */
				case 2: $chr = chr(rand(65, 90));
					break;	 /* A-Z */
				case 3: $chr = chr(rand(97, 122));
					break;	 /* a-z */
			}

			/* To not mix up 0 (zero) and O (maj o) or 1 (one) and I (maj i) */
			if ($chr == chr(79) || $chr == chr(73)) {
				$i--;
			} else {
				$pass .= $chr;
			}

			$i++;
		}

		return $pass;
	}

}

?>