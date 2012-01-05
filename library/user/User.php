<?php

/**
 * Class: User
 *
 * This is an Aenoa Server representation of an user.
 *
 * An instance of User is created at initialization of the app session.
 *
 * Users can store three types of data :
 *		- properties stored in a field of table ae_users (app_properties field)
 *			- this
 *		- properties stored in ae_users_info table, and therefore formalized in structures
 *			- this data is available through a <User::getData> method
 *			-
 *		- properties stored in core cookie
 *			- this data is editable, through a <Cookie> instance
 *
 *
 * @see App::getUser
 * @see Session
 * @see Cookie
 * @see LoginService
 * @see UserCoreController
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
		if (is_null(self::$_currentlogged) ) {
			App::do401('Require logged user');
		}

		return self::$_currentlogged;
	}

	/**
	 * Static method to create a new password given its length
	 * 
	 * @param int $length Length of password, default: 8
	 * @return string A brand new password 
	 */
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
	
	/**
	 * Creates a new User instance
	 * 
	 *
	 * @param string $identifier Identifier should be an email. If the instance is the first instance of User, (e.g. the one instanciated by Session) and if the user is logged, then this instance is stored statically, in order to use static method User::requireLogged()
	 * @param string $cookieName Name of created cookie when user logs in
	 */
	function __construct($identifier = null , $cookieName = null ) {
		if (is_null($identifier)) {
			$this->_identifier = $identifier;
		}
		
		if ( Config::has(App::APP_COOKIE_NAME) )
		{
			$this->_cookieName = Config::get(App::APP_COOKIE_NAME) ;
		} else if ( !is_null ( $cookieName ) )
		{
			$this->_cookieName = $cookieName ;
		}
		
		$this->_cookie = new Cookie($this->_cookieName);

		if (!is_null(App::$session) && App::$session->get('User.logged') === 1 && is_null(self::$_currentlogged))
		{
			

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

	/**
	 * Check if user is logged
	 *
	 * @return boolean True if user is logged, false otherwise
	 */
	function isLogged() {
		return $this->_logged === 1;
	}

	/**
	 * Check whether user level is set to 0 (god/superadmin...)
	 *
	 * @return boolean True if user level is set to 0, false otherwise
	 */
	function isGod() {
		return $this->getTrueLevel() == 0;
	}

	/**
	 * Get the main identifier of the user (email address)
	 *
	 * @return string Email address of user
	 */
	function getIdentifier() {
		return $this->_identifier;
	}

	/**
	 * Get the database id of the user data (ae_users table)
	 *
	 * @return int The database id of the user data (ae_users table)
	 */
	function getDatabaseId() {
		return $this->_id;
	}

	/**
	 * Get the lastname of the user
	 *
	 * @return string Lastname of the user
	 */
	function getLastname() {
		return $this->_lastname;
	}

	/**
	 * Get the firstname of the user
	 *
	 * @return string Firstname of the user
	 */
	function getFirstname() {
		return $this->_firstname;
	}

	/**
	 * Get fullname (concatenation of firstname and lastname, separated by a space)
	 *
	 * @return string Fullname, concatenation of firstname and lastname
	 */
	function getFullName() {
		return $this->_firstname . ' ' . $this->_lastname;
	}
	
	/**
	 * Set a fake level to the current user (testing purpose), only if true level of user is equal to 0
	 * 
	 * @param int $level New level to simulate
	 * @return User Current instance for chained command on this element
	 */
	function setFakeLevel($level) {
		if ($this->getTrueLevel() == 0) {
			$this->_level = $level;
			App::$session->set('User.level', $this->_level);
		}
		
		return $this ;
	}
	
	/**
	 * Reset fake level of the user to its original level
	 * 
	 * @see User::setFakeLevel
	 * @return User Current instance for chained command on this element
	 */
	function unsetFakeLevel() {
		if ($this->_logged) {
			$this->_level = $this->_group['level'];
		}
		
		return $this ;
	}
	
	/**
	 * Get the real level of the user, in case a fake level has been set up
	 * 
	 * @return int The real level of user
	 */
	function getTrueLevel() {
		if ($this->_logged) {
			return $this->_group['level'];
		}

		return 100;
	}
	
	/**
	 * Checks if level of user is equal to given level
	 * 
	 * @param int $level Level to test 
	 * @return boolean True if user has given level, false otherwise 
	 */
	function isLevel($level) {
		return $this->_level == $level;
	}
	
	/**
	 * Returns the level of the user. If user is not logged, level 100 is returned
	 * 
	 * @return int Level of the user
	 */
	function getLevel() {
		if ($this->_logged) {
			return $this->_level;
		}

		return 100;
	}
	
	/**
	 * Get the localized group label of the user. If user is not logged, label 'Visitor' is returned.
	 * 
	 * @return string The localized label of group of the user
	 */
	function getGroup() {
		if ($this->_logged) {
			return $this->getGroupLabelByLevel($this->_level);
		}

		return _('Visitor');
	}
	
	
	/**
	 * Get the localized real group label of the user. If user is not logged, label 'Visitor' is returned.
	 * 
	 * This method does not rely on fake level.
	 * 
	 * @return string The localized label of group of the user
	 */
	function getTrueGroup() {
		if ($this->_logged) {
			return $this->_group['label'];
		}

		return _('Visitor');
	}
	
	/**
	 * Get label of a group given a level
	 * 
	 * @param int $level Level of group to get label
	 * @return string Group level if found, NULL otherwise 
	 */
	function getGroupLabelByLevel($level) {
		foreach ($this->_groups as $group) {
			if ($group['level'] == $level) {
				return $group['label'];
			}
		}
		return null;
	}

	/**
	 * Get all groups
	 * 
	 * @return array Groups as array 
	 */
	function getGroupList() {
		return $this->_groups;
	}

	/**
	 * Log a user in the system
	 *
	 * A cookie is created, and then available using <User.>
	 *
	 *
	 * @param string $email Email of the user to log in 
	 * @param string $sha1password Password of the user to log in
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
	
	
	/**
	 * Reload infos of current user if user is logged
	 * 
	 * @return User Current instance for chained command on this element
	 */
	function reloadInfos() {
		if ($this->isLogged()) {
			$db = App::getDatabase();
			$this->_loadInfos($db->findAndRelatives('ae_users', $this->_id));
		}
		
		return $this ;
	}
	
	/**
	 * Setup loaded infos in current User instance
	 * @private
	 */
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
	 * Log out user from the system. Will remove session user data and will close session.
	 * 
	 * @see Session
	 * @return User Current instance for chained command on this element 
	 */
	function logout() {
		
		if ( $this->_logged )
		{
			if ( self::$_currentlogged === $this )
			{
				self::$_currentlogged = null;
			}

			$this->_logged = false;
		}

		App::$session->uset('User.*');

		App::$session->close(false);

		return $this ;
	}

	
	/**
	 * Recheck in database if current user identifier and given password correspond.
	 * 
	 * @param string $sha1password Password hashed using sha1 function
	 * @return boolean True if given password is the one of the current user, false otherwise 
	 */
	function recheckPassword($sha1password) {
		if ($this->_logged == true) {
			$db = App::getDatabase('main');

			$user = $db->findFirst('ae_users', array('email' => $this->_identifier, 'password' => $sha1password));

			return!empty($user);
		}
		return false;
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
	 * // or as chained command:
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


	/**
	 * Get data from ae_users_info table associated to current user
	 *
	 * If user is not logged, this method returns an empty array.
	 *
	 * @return array Associative array of data of user
	 */
	function getData() {
		return $this->_data;
	}

	/**
	 * Get the cookie associated to the Aenoa Server User
	 * 
	 * @see Cookie
	 * @return Cookie The Cookie object
	 */
	function getCookie() {
		return $this->_cookie;
	}

}

?>