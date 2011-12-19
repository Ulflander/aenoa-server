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
	 * @param mixed $structure 
	 * @param type $connect
	 * @return type
	 */
	static function connect($id, $engine, $config, $structure = false, $connect = true ) {
		if (is_null(self::$_dbs)) {
			self::$_dbs = new Collection ();
		}

		if (self::get($id) != null) {
			App::do500('Database yet declared: ' . $id);
		}
		
		$tables = self::_getStructure($id, $structure) ;
		
		if (empty($tables)) {
			App::do500('No table found for database ' . $id . '.');
		}
		
		
		
		$db = new $engine($id, $tables);

		if ($db->sourceExists($config, true)) {
			if (!$db->setSource($config)) {
				App::do500('Connection to DB ' . $id . ' failed.');
			}
		} else {
			App::do500('Source for DB ' . $id . ' does not exist.');
		}
		
		self::$_dbs->set ( $id , $db ) ;
		
		if ($db->setStructure($tables, true) == false ) {
			App::do500('Database ' . $id . ' requires to be deployed.');
		}


		return $db ;
	}

	/**
	 * Get a db engine given its id. Default id is "main".
	 *
	 * @param string $id Id of database to get
	 * @return AbstractDBEngine Database object if found, null otherwise
	 */
	static function get($key = 'main') {
		return is_null(self::$_dbs) ? null : self::$_dbs->get($key);
	}

	/**
	 * Checks if a database engine exists
	 *
	 * @param string $key Identifier of database engine
	 * @return boolean True if engine exists, false otherwise
	 */
	static function has($key = 'main') {
		return is_null(self::$_dbs) ? false : self::$_dbs->has($key);
	}

	/**
	 * Returns all database engines in an associative array
	 *
	 * @return array Array of all database engines
	 */
	static function getAll() {
		return is_null(self::$_dbs) ? array() : self::$_dbs->getAll();
	}

	/**
	 * Retrieve database structure
	 * 
	 * @param type $id
	 * @param type $structure
	 * @return array 
	 */
	private function _getStructure($id, $structure = false ) {

		$structureFile = null ;
		
		$futil = new FSUtil(ROOT);
		
		// Get the structure file
		// if structure is a string, it's the structure filename+extension
		if ( is_string ($structure) )
		{
			$structureFile = $structure ;
		// If structure is an array, consider that it's the structure
		} else if ( is_array($structure) )
		{
			$tables = $structure ;
		// If structure is FALSE, we consider that structure file is named {structure_id}.php
		} else if ( $structure === false )
		{
			$structureFile = $id . '.php' ;
		// If structure us null, we consider starting with an empty structure
		} else if ( is_null($structure) )
		{
			$structure = array () ;
		} else {
			
		}
		
		
		if ( !is_null($structureFile) )
		{
			
			global $FILE_UTIL ;
			
			if ($futil->fileExists(AE_APP_STRUCTURES . $structureFile) == false) {
				App::do500('Structure file for database ' . $id . ' not found.');
			}

			include ( AE_APP_STRUCTURES . $structureFile);

			if (!isset($tables) || empty($tables)) {
				App::do500('Structure file for DB ' . $id . ' is not valid.');
			}
		}
		
		
		if ( !isset ( $tables ) )
		{
			App::do500 ( sprintf (_('Structure for database "%s" not found'),$id) ) ;
		}
		
		
		if ( $id == 'main' )
		{
			if ( Config::get(App::USER_CORE_SYSTEM) === true)
			{
				include( AE_STRUCTURES . 'users.php' ) ;
				
				if ( !empty( $tables ) )
				{
					$tables = array_merge ($users, $tables ) ;
				} else {
					$tables = $users ;
				}
			}
		
			if ( Config::get(App::API_REQUIRE_KEY) === true )
			{
				include( AE_STRUCTURES . 'api.php' ) ;
				
				if ( !empty( $tables ) )
				{
					$tables = array_merge ( $tables, $api ) ;
				} else {
					$tables = $api ;
				}
			}
		}
		
		
		return $tables;
	}

}

?>
