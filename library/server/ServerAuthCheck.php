<?php

/**
 * ServerAuthCheck checks for GET keys in case of securized services
 */
class ServerAuthCheck {

	static private $_lastKey = '';
	static private $_lastKeyValid = false;

	function __construct() {
		$result = false;

		if (App::$sanitizer->exists('GET', 'key') && App::$sanitizer->exists('GET', 'hash')) {
			self::$_lastKey = App::$sanitizer->get('GET', 'key');

			$db = App::getDatabase('main');
			$key = $db->findFirst('ae_api_keys', array('public' => App::$sanitizer->get('GET', 'key')));

			if (!empty($key) && App::$sanitizer->get('GET', 'hash') == sha1($key['private'])) {
				self::$_lastKeyValid = true;
				$result = true;
			}
		}

		if ($result == false) {
			App::do401('REST API Authentication failure');
		}
	}

	static function getUseReport() {
		if (self::$_lastKey != '') {
			return self::$_lastKey . ' / ' . ( self::$_lastKeyValid ? 'Valid key' : 'Invalid key' );
		}

		return 'No key';
	}

}

?>