<?php

/**
 * Class: AbstractDBEngine
 *
 * AbstractDBEngine describe how all DB engines work.
 *
 * All DB engines must extend AbstractDBEngine.
 *
 *
 * Common parameters formatting:
 *
 * Conditions:
 *
 * You can use a strict equality creating a simple associative array:
 *
 * > // This will search for all entries which name is foo and password is bar
 * > $conditions = array ( 'name' => 'foo' , 'password' => 'bar' );
 *
 *
 * > // This will search for all entries which name is foo and password is different than bar
 * > $conditions = array ( 'name' => 'foo' , 'password' => '!= bar' );
 *
 *
 * > // This will search for all entries which name is foo and password is different than bar
 * > $conditions = array ( 'datetime' => '< NOW()' , 'password' => '!= bar' );
 *
 * See also:
 * <DBSchema>, <DBTableSchema>, <MySQLEngine>, <DBValidator>, <Database>
 */
class AbstractDBEngine extends DBSchema {

	/**
	 * 
	 * 
	 * 
	 * @param unknown_type $tableStructure
	 */
	static function getSearchable(&$tableStructure) {
		foreach ($tableStructure as &$field) {
			if (array_key_exists('searchable', $field) && $field['searchable'] == true) {
				return $field['name'];
			}
		}

		return false;
	}

	static function getFilterable(&$tableStructure) {
		foreach ($tableStructure as &$field) {
			if (array_key_exists('filterable', $field) && $field['filterable'] == true) {
				return $field['name'];
			}
		}

		return false;
	}

	/////////////////////////////////////////////////////
	// Properties usable in concrete classes

	/**
	 * Data stored in memory
	 *
	 * Some engines does not need this
	 *
	 * @var
	 */
	protected $data;

	/**
	 * Source infos
	 *
	 * Check out concrete engines implementation to get doc about source formatting.
	 *
	 *
	 * @var mixed
	 */
	protected $source;

	/**
	 * Structure of database
	 *
	 * @var
	 */
	protected $structure = array();

	/**
	 * Indexed structure of database
	 *
	 * @var
	 */
	protected $struct = array();

	/**
	 * List of tables
	 *
	 * @var
	 */
	protected $tables = array();

	/**
	 * Last id
	 *
	 * @var
	 */
	protected $__lastId;
	protected $id;
	protected $hasStructureDeploymentError = false;

	/**
	 * Is database currently in transaction
	 *
	 *
	 * @var boolean
	 */
	protected $_inTransaction = false;

	/**
	 * Set the database engine id
	 *
	 * @param string $id Identifier of database
	 */
	function setID($id) {
		$this->id = $id;
	}

	/////////////////////////////////////////////////////
	// Help methods

	/**
	 * Return a datetime string corresponding to given timestamp or current time() timestamp
	 *
	 *
	 * @param unknown_type $timestamp
	 */
	function getDatetime($timestamp = null) {
		if (is_null($timestamp)) {
			$timestamp = time();
		}
		return date("Y-m-d H:i:s", $timestamp);
	}

	/////////////////////////////////////////////////////
	// Methods to override

	/**
	 * This method open the database
	 * 
	 * Concrete implementation returns true if database is connected, false otherwise
	 *
	 * @return boolean True is database connected, false otherwise
	 */
	function open() {
		return false;
	}

	/**
	 * This method close the database
	 *
	 * @return
	 */
	function close() {
		return false;
	}

	/**
	 * This method only tests connectivity without opening any database.
	 *
	 * For some concrete engines, like JSONDBEngine, $host should be the path of the JSON DB file.
	 *
	 * @param $host
	 * @param $login
	 * @param $password
	 * @return unknown_type
	 */
	function test($host, $login, $password) {
		return false;
	}

	/**
	 * Check if engine is usable and database is opened and ready
	 *
	 * @return bool True if db engine is usable, false otherwise
	 */
	function isUsable() {
		return false;
	}

	/**
	 * 
	 * 
	 *
	 */
	function getIdentifiers() {
		return $this->database;
	}

	/**
	 * Set database source
	 *
	 * <p>If a database was opened yet, then this one will be closed before creating new database.
	 * New database will be set as current database.</p>
	 *
	 * <p>If the database is the same as the current database, then setSource will always return true.</p>
	 *
	 * <p>The create option may be unavailable in some engines.</p>
	 *
	 * @see AbstractDBEngine::createSource
	 * @param string $database
	 * @param object $create [optional] Create DB if database does not exists
	 * @return bool True if database exists and is usable, false otherwise
	 */
	function setSource($database, $create = false) {
		return false;
	}

	/**
	 * Create database source
	 *
	 * <p>If a database was opened yet, then this one will be closed before creating new database.
	 * New database will be set as current database.</p>
	 *
	 * <p>This method can be unavailable depending on concrete database engine.
	 * Refer to <AbstractDBEngine::hasCreationCapability> to know if an engine can create new databases.</p>
	 *
	 * @param string $database Type of param $database depends on concrete implementation. Check out concrete engine.
	 * @return bool True if database has been created, false otherwise
	 */
	function createSource($database) {
		return false;
	}

	/**
	 * Returns true if concrete engine can create a new database
	 *
	 * @return bool
	 */
	function hasCreationCapability() {
		return false;
	}

	/**
	 * Check if a database source exists
	 *
	 * @param object $database Type of param $database depends on concrete implementation. Check out concrete engine.
	 * @return bool True is source exists, false otherwise
	 */
	function sourceExists($database) {
		return false;
	}

	/**
	 * Compare a source with another to know if it's the same
	 *
	 *
	 * @param mixed $database Type of param $database depends on concrete implementation. Check out concrete engine.
	 * @return boolean True if source is the same, false otherwise
	 */
	function compareSource($database) {
		return false;
	}

	/**
	 * Check if a source seems to be valid
	 *
	 *
	 * @param mixed $database Type of param $database depends on concrete implementation. Check out concrete engine.
	 * @return boolean True is source seems to be valid, false otherwise
	 */
	function isValidSource($database) {
		return false;
	}

	/**
	 * Returns structure of all tables
	 *
	 * @return array The structure (fields list) of a table, false if table is not found or if concrete engine
	 * has no structure capability.
	 *
	 */
	function getStructure() {
		return $this->struct;
	}

	/**
	 * Returns structure of a table
	 *
	 * @param string $table Table name
	 * @return array The structure (fields list) of a table, false if table is not found or if concrete engine
	 * has no structure capability.
	 *
	 */
	function getTableStructure($table) {
		if ($this->tableExists($table)) {
			return $this->struct[$table];
		}

		return false;
	}

	/**
	 * Returns true if concrete engine has transaction capability
	 *
	 * @return boolean True if engine has transaction capability, false otherwise.
	 */
	function hasTransactionCapability() {
		return false;
	}

	/**
	 * Enable transaction mode
	 */
	function startTransaction() {
		return false;
	}

	/**
	 * Disable transaction mode
	 *
	 * @return boolean True if transaction did not return any error, false otherwise
	 */
	function endTransaction() {
		return false;
	}

	/**
	 * Set structure of database
	 *
	 * If table does not exists, then it should be created
	 *
	 * The method can be unavailable depending on concrete database engine.
	 * Use hasStructureCapability method to define if query is usable or not in
	 * concrete classe.
	 *
	 * @return True if structure has been set, fals otherwise
	 */
	function setStructure(&$structure = array(), $create = false) {
		return false;
	}

	/**
	 * Returns true if concrete engine can create db structure
	 *
	 * @return bool
	 */
	function hasStructureCapability() {
		return false;
	}

	/**
	 * Perform a user query on a table
	 *
	 * The method can be unavailable depending on concrete database engine.
	 * Use hasQueryCapability method to define if query is usable or not in
	 * concrete classe.
	 *
	 * @return array Results if found, false on error
	 */
	function query($query) {
		return false;
	}

	/**
	 * Returns true if concrete engine can perform direct queries
	 *
	 * @return bool
	 */
	function hasQueryCapability() {
		return false;
	}

	function exists($table) {
		
	}

	/**
	 * New API for
	 *
	 * @param type $table
	 * @param type $id
	 * @param type $recursivity
	 * @param type $fields
	 * @param type $subfields
	 * @return type
	 */
	function get($table, $id, $recursivity=1, $fields=array(), $subfields=array())
	{
		return $this->findAndRelatives($table, $id, $fields, $subfields, $recursivity );
	}
	
	function getFirst($table, $cond = array(), $recursivity = 1 , $fields=array(), $subfields=array())
	{
		return $this->findRelatives( $table , $this->findFirst($table, $cond, $fields), $subfields, $recursivity ) ;
	}
	
	function getAlone($table, $id, $fields = array()) {
		return $this->find($table, $id, $fields);
	}

	function getAll($table, $cond, $limit = 0, $fields = array()) {
		return $this->findAll($table, $cond, $limit, $fields);
	}
	
	function getAllAndChilds ( $table , $cond = array () , $limit = 0 , $recursivity = 1 , $fields = array () , $subfields = array () , $ordered = true )
	{
		return $this->findChildren($table, $this->findAll($table, $cond, $limit, $fields), $subfields, $recursivity , $ordered ) ;
	}
	
	function getAllAndRelatives ($table, $cond = array () , $limit = 0 , $recursivity = 1 , $fields = array () , $subfields = array () , $ordered = true )
	{
		return $this->findRelatives($table, $this->findAll($table, $cond, $limit, $fields), $subfields, $recursivity, $ordered);
	}
	
	function getChilds($table, $selection, $recursivity=1, $subfields=array(), $ordered = true )
	{
		return $this->findRelatives($table, $selection, $subfields, $recursivity, $ordered);
	}
	
	function getRand ( $table, $fields = array () , $conds = array () , $num = 1 )
	{
		return $this->findRandom($table, $fields, $conds, $num ) ;
	}
	

	function set($table, $data, $id) {
		
	}
	
	
	

	/**
	 * Find an entry in a table based on ID
	 *
	 * @param object $table The table where to search
	 * @param object $id The ID of an entry
	 * @param object $fields [optional] Fields to return, default will return all fields
	 * @return bool False on failure, entries on success
	 */
	function find($table, $id, $fields = array()) {
		return false;
	}

	/**
	 * Find all entries in a table, depending on conditions
	 *
	 * @param object $table The table where to search
	 * @param object $cond [optional] Conditions
	 * @param object $limit [optional] Max returned entries
	 * @param object $fields [optional] Fields to return, default will return all fields
	 * @return bool False on failure, entries on success
	 */
	function findAll($table, $cond = array(), $limit = 0, $fields = array()) {
		return false;
	}

	/**
	 * Find first entry in a table, depending on conditions
	 *
	 * @param object $table The table where to search
	 * @param object $cond [optional] Conditions
	 * @param object $fields [optional] Fields to return, default will return all fields
	 * @return bool False on failure, entry on success
	 */
	function findAndOrder($table, $cond = array(), $limit = 0, $fields = array(), $order_fields = array(), $order = 'ASC') {
		return false;
	}

	/**
	 * Find first entry in a table, depending on conditions
	 *
	 * @param object $table The table where to search
	 * @param object $cond [optional] Conditions
	 * @param object $fields [optional] Fields to return, default will return all fields
	 * @param object $fields [optional] Fields to order
	 * @param object $order , default will return ascendant order
	 * @return bool False on failure, entry on success
	 */
	function findFirst($table, $cond = array(), $fields = array()) {
		return false;
	}

	/**
	 * Find random results
	 *
	 * @param type $table
	 * @param type $fields
	 * @param type $conds
	 * @param type $num
	 * @return type 
	 */
	function findRandom($table, $fields = array(), $conds = array(), $num ) {
		return false;
	}

	/**
	 * Edit an entry
	 *
	 * @param object $table The table where to search
	 * @param object $id The ID of entry to edit
	 * @param object $content [optional] Entry content
	 * @return bool False on failure, true on success
	 */
	function edit($table, $id, $content = array()) {
		return false;
	}

	/**
	 * Edit all entries based on some conditions
	 *
	 * @param object $table The table where to search
	 * @param object $content [optional] Entry content
	 * @param array $cond An array of conditions
	 * @return bool False on failure, true on success
	 */
	function editAll($table, $content = array(), $cond = array()) {
		return false;
	}

	/**
	 * Add an entry
	 *
	 * @param object $table The table where to add entry
	 * @param object $content [optional]
	 * @return bool False on failure, true on success
	 */
	function add($table, $content = array()) {
		return false;
	}

	/**
	 * Add many entries
	 *
	 * @param object $table The table where to add entries
	 * @param object $rows [optional] The rows to add
	 * @return bool False on failure, true on success
	 */
	function addAll($table, $rows = array()) {
		return false;
	}

	/**
	 * Return count of entries depending on conditions
	 *
	 * @param object $table The table where to add entry
	 * @param array $cond [optional]
	 * @return false on failure, entries count on success
	 */
	function count($table, $cond = array()) {
		return false;
	}

	/**
	 * Return last ID
	 * @return  last ID
	 */
	function lastId($table) {
		return false;
	}

	/**
	 * Return a new ID
	 *
	 * In some concrete engines, ID will be set automatically by the Database when inserting a new entry,
	 * so this method is only for virtual DB (CVS, JSON...).
	 *
	 *
	 * @return mixed New ID if possible, false otherwise
	 */
	function newId($table) {
		return false;
	}

	/**
	 * Delete an entry based on its ID
	 *
	 * @param object $table The table where to search
	 * @param object $id The ID of entry to delete
	 * @return bool False on failure, true on success
	 */
	function delete($table, $id) {
		return false;
	}

	/**
	 * Delete entries based on some conditions
	 *
	 * @param object $table The table where to search
	 * @param array $cond [optional]
	 * @return bool False on failure, true on success
	 */
	function deleteAll($table, $cond = array()) {
		return false;
	}

	function createDocument($id) {
		
	}

	function getDocument($id) {
		
	}

	function setDocument($id, $data) {
		
	}

	private $__childCache = array();

	/**
	 * Find all parents of one or more entries, given the entries
	 *
	 *
	 * @param unknown_type $table
	 * @param unknown_type $dbselection
	 * @param unknown_type $subFields
	 * @param unknown_type $recursivity
	 * @param unknown_type $ordered
	 */
	function findAscendants($table, $dbselection, $subFields = array(), $recursivity = 1, $ordered = true) {
		$schema = $this->tableExistsOr403($table);

		if ($schema->getLength() < 2) {
			return $dbselection;
		}
		if (empty($dbselection)) {

			return $dbselection;
		}

		$unique = false;
		foreach ($dbselection as $k => &$res) {
			if (is_string($k)) {
				$dbselection = array($dbselection);
				$unique = true;
				break;
			}
		}

		$cond = array();

		$childTables = array();
		$result = array();
		$q = '';

		$initial = $schema->getInitial();

		foreach ($initial as $fieldName => &$field) {
			if (@$field['behavior'] & DBSchema::BHR_PICK_IN || @$field['behavior'] & DBSchema::BHR_PICK_ONE || $field['type'] == DBSchema::TYPE_PARENT || $field['type'] == DBSchema::TYPE_CHILD) {
				if (array_key_exists($field['source'], $subFields) && empty($subFields[$field['source']])) {
					continue;
				}
				$n = $fieldName;
				$n2 = $this->getTableSchema($field['source'])->getPrimary();

				$childTable = array();
				$childTable['fieldName'] = $n2;
				$childTable['isSameTable'] = ($field['source'] == $table);
				$childTable['resName'] = $n;
				$childTable['source'] = $field['source'];
				$childTable['ids'] = array();
				$childTable['multi'] = @$field['behavior'] & DBSchema::BHR_PICK_IN;
				foreach ($dbselection as $k => &$res) {
					if (!is_array($res)) {
						$res = array($res);
					}
					if (array_key_exists($n, $res) && $res[$n] !== 0 && $res[$n] !== '0' && !is_array($res[$n])) {
						if ($childTable['multi']) {
							$childTable['ids'] = array_merge($childTable['ids'], explode(',', $res[$n]));
						} else {
							$childTable['ids'][] = $res[$n];
						}
					}
				}

				$childTable['ids'] = array_unique($childTable['ids']);

				$childTables[] = $childTable;
			}
		}

		foreach ($childTables as &$inf) {
			if (!empty($inf['ids'])) {
				$primaryKey = $inf['fieldName'];

				$__ids = $inf['ids'];
				$__res2 = array();

				if (ake($inf['source'], $this->__childCache)) {
					$cache = $this->__childCache[$inf['source']];
					foreach ($__ids as $k => $__id) {
						if (!is_array($__id) && ake($__id, $cache)) {
							$__res2[] = $cache[$__id];
							unset($__ids[$k]);
						}
					}
				} else {
					$this->__childCache[$inf['source']] = array();
				}

				if (!empty($__ids)) {
					// Subfields
					$__f = array();
					if (array_key_exists($inf['source'], $subFields) && is_array($subFields[$inf['source']])) {
						$__f = $subFields[$inf['source']];

						if (!in_array($primaryKey, $__f)) {
							array_unshift($__f, $primaryKey);
						}
					}

					$result = $this->findAll($inf['source'], array($primaryKey => $__ids), 0, $__f);

					foreach ($result as &$res) {
						$this->__childCache[$inf['source']][$res[$primaryKey]] = $res;
					}
				} else {
					$result = array();
				}


				$result = array_merge($result, $__res2);

				if ($inf['multi'] == true && array_key_exists(0, $result) == false) {
					$result = array($result);
				}
				if ($recursivity > 1) {
					$result = $this->findAscendants($inf['source'], $result, $subFields, $recursivity - 1);
				}
				foreach ($dbselection as &$res) {
					if (array_key_exists($inf['resName'], $res) && $res[$inf['resName']] != '') {
						foreach ($result as &$res2) {
							if (!is_array($res2) || !ake($inf['fieldName'], $res2)) {
								continue;
							}

							if ($res2[$inf['fieldName']] == $res[$inf['resName']]) {
								if ($inf['multi'] == false)
									$res[$inf['resName']] = $res2;
								else
									$res[$inf['resName']] = array($res2);
								break;
							} else if (is_string($res[$inf['resName']]) && strpos($res[$inf['resName']], ',') !== false) {
								$ids = explode(',', $res[$inf['resName']]);
								if (in_array($res2[$inf['fieldName']], $ids)) {
									$res['___' . $inf['resName']][] = $res2;
									$res['___table'] = $inf['source'];
								}
							}
						}
					}
				}
			}
		}

		foreach ($dbselection as &$res) {
			foreach ($res as $name => &$val) {
				if (strpos($name, '___') === 0) {
					$table = $res['___table'];
					$n = substr($name, 3);
					if ($ordered) {
						if (is_string($res[$n]) && strpos($res[$n], ',') !== false) {
							$ids = explode(',', $res[$n]);
							$arr = array();
							$primary = $this->getTableSchema($table)->getPrimary();
							foreach ($ids as $_id) {
								foreach ($res[$name] as $k => $r) {
									if ($r[$primary] == $_id) {
										$arr[] = $r;
										unset($res[$k]);
										break;
									}
								}
							}
							$res[$n] = $arr;
						} else {
							$res[$n] = $res[$name];
						}
					} else {
						$res[$n] = $res[$name];
					}
					unset($res['___table']);
					unset($res[$name]);
				}
			}
		}


		return ( $unique ? $dbselection[0] : $dbselection );
	}

	/**
	 * Find an entry in a table based on ID, and retrieve all parents and children until recursivity level is reached
	 *
	 *
	 * @param string $table
	 * @param string $id
	 * @param array $fields
	 * @param array $subfields
	 * @param int $recursivity
	 * @return mixed False on failure, an array containing result if success
	 */
	function findAndRelatives($table, $id, $fields = array(), $subfields = array(), $recursivity = 1) {
		$schema = $this->tableExistsOr403($table);


		$res = $this->findRelatives($table, $this->findAll($table, array($schema->getPrimary() => $id), 1, $fields), $subfields, $recursivity);
		if (!empty($res)) {
			return $res[0];
		}
		return array();
	}

	/**
	 * Find parents and children relative data given an array of data
	 *
	 *
	 * @param unknown_type $table
	 * @param unknown_type $dbselection
	 * @param unknown_type $subFields
	 * @param unknown_type $recursivity
	 * @param unknown_type $ordered
	 */
	function findRelatives($table, $dbselection, $subFields = array(), $recursivity = 1, $ordered = true) {
		return $this->findAscendants($table, $this->findChildren($table, $dbselection, $subFields, $recursivity, $ordered), $subFields, $recursivity, $ordered);
	}

	/**
	 * Find all children of one or more entries, given the entries
	 *
	 *
	 * @param unknown_type $table
	 * @param unknown_type $dbselection
	 * @param unknown_type $subFields
	 * @param unknown_type $recursivity
	 * @param unknown_type $ordered
	 * @return boolean
	 */
	function findChildren($table, $dbselection, $subFields = array(), $recursivity = 1, $ordered = true) {
		$schema = $this->tableExistsOr403($table);
		
		if ($schema->getLength() < 2) {
			return $dbselection;
		}
		if (empty($dbselection)) {

			return $dbselection;
		}
		$unique = false;

		foreach ($dbselection as $k => &$res) {
			if (is_string($k)) {
				$dbselection = array($dbselection);
				$unique = true;
				break;
			}
		}

		$cond = array();

		$childTables = array();
		$result = array();
		$q = '';

		$initial = $schema->getInitial();

		foreach ($initial as $fieldName => &$field) {
			if (@$field['behavior'] & DBSchema::BHR_PICK_IN || @$field['behavior'] & DBSchema::BHR_PICK_ONE || $field['type'] == DBSchema::TYPE_PARENT || $field['type'] == DBSchema::TYPE_CHILD) {
				if (array_key_exists($field['source'], $subFields) && empty($subFields[$field['source']])) {
					continue;
				}

				if ($field['source'] == $table) {
					$n = $schema->getPrimary();
					$n2 = $field['name'];
				} else {
					$n = $field['name'];
					$n2 = $this->getTableSchema($field['source'])->getPrimary();
				}

				$childTable = array();
				$childTable['fieldName'] = $n2;
				$childTable['isSameTable'] = ($field['source'] == $table);
				$childTable['resName'] = $n;
				$childTable['source'] = $field['source'];
				$childTable['ids'] = array();
				$childTable['multi'] = @$field['behavior'] & DBSchema::BHR_PICK_IN;
				foreach ($dbselection as $k => &$res) {
					if (is_array($res)) {
						if (array_key_exists($n, $res) && $res[$n] !== 0 && $res[$n] !== '0' && !is_array($res[$n])) {
							if ($childTable['multi']) {
								$childTable['ids'] = array_merge($childTable['ids'], explode(',', $res[$n]));
							} else {
								$childTable['ids'][] = $res[$n];
							}
						}
					}
				}
				$childTable['ids'] = array_unique($childTable['ids']);

				$childTables[] = $childTable;
			}
		}
		foreach ($childTables as &$inf) {
			if (!empty($inf['ids'])) {
				$primaryKey = $inf['fieldName'];

				$__ids = $inf['ids'];
				$__res2 = array();

				if (ake($inf['source'], $this->__childCache)) {
					$cache = $this->__childCache[$inf['source']];
					foreach ($__ids as $k => $__id) {
						if (ake($__id, $cache)) {
							$__res2[] = $cache[$__id];
							unset($__ids[$k]);
						}
					}
				} else {
					$this->__childCache[$inf['source']] = array();
				}

				if (!empty($__ids)) {
					// Subfields
					$__f = array();
					if (array_key_exists($inf['source'], $subFields) && is_array($subFields[$inf['source']])) {
						$__f = $subFields[$inf['source']];
					}

					$result = $this->findAll($inf['source'], array($primaryKey => $__ids), 0, $__f);

					if (array_key_exists($inf['source'], $subFields) && is_array($subFields[$inf['source']])) {

						$__f = $subFields[$inf['source']];
					}
					foreach ($result as &$res) {
						if (ake($inf['resName'], $res)) {
							$this->__childCache[$inf['source']][$res[$inf['resName']]] = $res;
						}
					}
				} else {
					$result = array();
				}



				$result = array_merge($result, $__res2);

				if ($inf['multi'] == true && array_key_exists(0, $result) == false) {
					$result = array($result);
				}
				if ($recursivity > 1) {
					$result = $this->findChildren($inf['source'], $result, $subFields, $recursivity - 1, $ordered );
				}
				foreach ($dbselection as &$res) {
					if ($inf['isSameTable']) {
						$res[$table] = array();
					}
					if (array_key_exists($inf['resName'], $res) && $res[$inf['resName']] != '') {
						foreach ($result as &$res2) {
							if (!is_array($res2) || !ake($inf['fieldName'], $res2)) {
								continue;
							}

							if ($inf['isSameTable'] == false) {
								if ($res2[$inf['fieldName']] == $res[$inf['resName']]) {
									if ($inf['multi'] == false)
										$res[$inf['resName']] = $res2;
									else
										$res[$inf['resName']] = array($res2);
									break;
								} else if (strpos($res[$inf['resName']], ',') !== false) {
									$ids = explode(',', $res[$inf['resName']]);
									if (in_array($res2[$inf['fieldName']], $ids)) {

										$res['___' . $inf['resName']][] = $res2;
										$res['___table'] = $inf['source'];
									}
								}
							} else {

								if ($res2[$inf['fieldName']] == $res[$inf['resName']]) {
									$res[$table][] = $res2;
								} else if (strpos($res[$inf['resName']], ',') !== false) {
									$ids = explode(',', $res[$inf['resName']]);
									if (in_array($res2[$inf['fieldName']], $ids)) {
										$res[$table][] = $res2;
									}
								}
							}
						}
					}
				}
			}
		}

		foreach ($dbselection as &$res) {
			if (!is_array($res)) {
				continue;
			}
			foreach ($res as $name => &$val) {
				if (strpos($name, '___') === 0) {
					$table = $res['___table'];
					$n = substr($name, 3);
					if ($ordered) {
						if (is_string($res[$n]) && strpos($res[$n], ',') !== false) {
							$ids = explode(',', $res[$n]);
							$arr = array();
							$primary = $this->getTableSchema($table)->getPrimary();
							foreach ($ids as $_id) {
								foreach ($res[$name] as $k => $r) {
									if (ake($primary, $r) && $r[$primary] == $_id) {
										$arr[] = $r;
										unset($res[$k]);
										break;
									}
								}
							}
							$res[$n] = $arr;
						} else {
							$res[$n] = $res[$name];
						}
					} else {
						$res[$n] = $res[$name];
					}
					unset($res['___table']);
					unset($res[$name]);
				}
			}
		}

		return ( $unique ? $dbselection[0] : $dbselection );
	}

	function keysToLabel($table, $dbselection) {

		$schema = $this->tableExistsOr403($table);

		if (!is_array($dbselection)) {
			return $dbselection;
		}

		$unique = false;
		foreach ($dbselection as $k => &$res) {
			if (is_string($k)) {
				$dbselection = array($dbselection);
				$unique = true;
				break;
			}
		}

		foreach ($dbselection as $k => &$res) {
			$nres = array();

			foreach ($res as $key => &$field) {
				// This is a child
				if (is_array($field)) {
					if ($schema->fieldExists($key)) {
						$nres[$this->struct[$table][$key]['label']] = $this->keysToLabel($this->struct[$table][$key]['source'], $field);
					}
					// This is not a child
				} else {
					if ($schema->fieldExists($key)) {
						if (ake('label', $this->struct[$table][$key])) {
							$nres[_($this->struct[$table][$key]['label'])] = $field;
						} else {
							$nres[_(ucfirst($key))] = $field;
						}
					}
				}
			}

			$res = $nres;
		}

		return ( $unique ? $dbselection[0] : $dbselection );
	}

}

?>
