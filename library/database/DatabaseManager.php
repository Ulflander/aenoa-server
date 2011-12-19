<?php

/*
 * Class: DatabaseManager
 *
 * DatabaseManager creates and stores databases engines.
 *
 * The main database must allways be named "main".
 *
 * How to create a new database:
 *
 * (start code)
 * 
 * $dbEngine = DatabaseManager::connect ( 'main' , 'MySQL' , array (
 * 		'host' => 'localhost',
 * 		'login' => 'root',
 * 		'password' => '',
 * 		'database' => 'db_name'
 * ) ) ;
 *
 * (end)
 *
 * How to get an existing database:
 *
 * (start code)
 *
 * // Get an engine identified by "someId"
 * $dbEngine = DatabaseManager::get ( 'someId' ) ;
 *
 * // or for "main"
 * $mainDbEngine = DatabaseManager::get () ;
 * 
 * (end)
 *
 * Since:
 * 1.0.6
 *
 * See also:
 * <AbstractDBEngine>
 */

class DatabaseManager extends Object {

	/**
	 * $private
	 * @var Collection
	 */
	private static $_dbs = null;

	/**
	 * Creates a new database engine and connects it
	 *
	 *
	 *
	 * @param type $id
	 * @param type $engine
	 * @param type $config
	 * @param type $structure
	 * @param type $connect
	 * @return type
	 */
	static function connect($id, $engine, $config, $structure = null, $connect = true ) {
		if (is_null(self::$_dbs)) {
			self::$_dbs = new Collection ();
		}

		if (self::get($id) != null) {
			App::do500('Database yet declared: ' . $id);
		}

		if (is_null($structureFile) && Config::get(App::USER_CORE_SYSTEM) !== true) {
			App::do500('No structure file given for database ' . $id . '.');
		}

		if (!is_null($structureFile)) {
			if (is_array($structureFile)) {
				if (empty($structureFile)) {
					App::do500('Structure for database ' . $id . ' is empty.');
				}

				$tables = $structureFile;
			} else {

				if (self::$futil->fileExists(AE_APP_STRUCTURES . $structureFile) == false) {
					App::do500('Structure file for database ' . $id . ' not found.');
				}

				include ( AE_APP_STRUCTURES . $structureFile);

				if (!isset($tables) || empty($tables)) {
					App::do500('Structure file for DB ' . $id . ' is not valid.');
				}
			}
		}

		if ($id == 'main') {
			if (Config::get(App::USER_CORE_SYSTEM) === true) {
				include( AE_STRUCTURES . 'users.php' );

				if (!empty($tables)) {
					$tables = array_merge($users, $tables);
				} else {
					$tables = $users;
				}
			}

			if (Config::get(App::API_REQUIRE_KEY) === true) {
				include( AE_STRUCTURES . 'api.php' );

				if (!empty($tables)) {
					$tables = array_merge($tables, $api);
				} else {
					$tables = $api;
				}
			}
		}

		$db = new $engine($id, $tables);

		if ($db->sourceExists($source, true)) {
			if (!$db->setSource($source)) {
				App::do500('Connection to DB ' . $id . ' failed.');
			}
		} else {
			App::do500('Source for DB ' . $id . ' does not exist.');
		}

		if (empty($tables)) {
			App::do500('No table found for database ' . $id . '.');
		}

		self::$_dbs[$id]['engine'] = $db;
		self::$_dbs[$id]['structure'] = $tables;

		if ($db->setStructure($tables, true) == false && strpos(self::$query, 'maintenance/check-context') !== 0) {
			App::do500('Database ' . $id . ' requires to be deployed.');
		}


		return self::$_dbs->get($id);
	}

	/**
	 * Get a db engine given its id. Default id is "main".
	 *
	 * @param string $id Id of database to get
	 * @return AbstractDBEngine Database object if found, null otherwise
	 */
	static function get($key = 'main') {
		return self::$_dbs->get($key);
	}

	/**
	 * Checks if a database engine exists
	 *
	 * @param string $key Identifier of database engine
	 * @return boolean True if engine exists, false otherwise
	 */
	static function has($key = 'main') {
		return self::$_dbs->has($key);
	}

	/**
	 * Returns all database engines in an associative array
	 *
	 * @return array Array of all database engines
	 */
	static function getAll() {
		return self::$_dbs->getAll();
	}

	/**
	 * Retrieve tables data
	 *
	 * @private
	 * @param type $id
	 * @return array
	 */
	private function _getTables($id) {
		$tables = array();

		return $tables;
	}

}

?>
