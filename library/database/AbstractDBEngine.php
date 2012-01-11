<?php

/**
 * AbstractDBEngine describe how all DB engines work.
 *
 * All DB engines must extend AbstractDBEngine.
 *
 *
 *
 *
 * Structures:
 *
 *
 *
 * > $field = array (
 * >		'name' => 'my_field_id',
 * >		'type' => DBSchema::TYPE_INT,
 * >		'behavior' => DBSchema::BHR_INCREMENT,
 * >		'default' => 'any value'
 * > ) ;
 *
 * for enum type, a fifth property must be applied:
 *
 * > $enumField = array (
 * >		'name' => 'my_enum_field',
 * >		'type' => DBSchema::TYPE_ENUM,
 * >		'behavior' => DBSchema::BHR_DT_ON_EDIT,
 * >		'default' => 'any value'
 * >		'values' => array ( 'any value' , 'another value' )
 * > ) ;
 *
 *
 * So here what would be an entire table structure:
 *
 * (start code)
 * $usersTable = array (
 * 		array (
 * 			'name' => 'id',
 * 			'type' => DBSchema::TYPE_INT,
 * 			'behavior' => DBSchema::BHR_INCREMENT
 * 		),
 * 		array (
 * 			'name' => 'email',
 * 			'label' => 'Email address',
 * 			'type' => DBSchema::TYPE_STRING,
 * 			'validation' => array (
 * 				'rule' => DBValidator::EMAIL,
 * 				'message' => 'The email field must be a well-formatted email address'
 * 			)
 * 		),
 * 		array (
 * 			'name' => 'password',
 * 			'label' => 'Password',
 * 			'type' =>  DBSchema::TYPE_STRING,
 * 			'behavior' => DBSchema::BHR_SHA1,
 * 			'validation' => array (
 * 				'rule' => '/[A-Za-z0-9\-_]{6,10}/',
 * 				'message' => 'Your password must contain 6 to 10 chars "A" to "Z", "a" to "z", "0" to "9", "-" and "_". '
 * 			)
 * 		),
 * 		array (
 * 			'name' => 'profile_picture',
 * 			'label' => 'Profile picture',
 * 			'type' =>  DBSchema::TYPE_FILE,
 * 			'behavior' => array ( &CallBackObjectInstance , 'methodName' , array ( 'argument 2' , 'some other argument' ) ) ,
 * 			'validation' => array (
 * 				'rule' => array ( 'jpg' , 'png' ),
 * 				'message' => 'Your profile picture must be a JPG or a PNG file.'
 * 			)
 * 		)
 * ) ;
 * (end)
 *
 *
 *
 *
 *
 *
 *
 *
 *
 * Type:
 *
 *
 *
 *
 * For all other types, it depends on concrete engine, but there should be at least implementation of these ones :
 * - int
 * - float
 * - string
 * - boolean
 * - enum
 * - timestamp
 * - datetime
 * - date
 *
 * Concrete engine must reflect these types, for example: in MySQL concrete engine,
 * string will correspond to TEXT, int to INT, boolean will be an enum like this one: ( 'TRUE' , 'FALSE' ), ...
 * Creating a new type (e.g. VARCHAR for MySQL) must be possible using the concrete engine.
 *
 *
 *
 *
 *
 *
 *
 *
 *
 *
 * Behaviors:
 *
 *
 *
 *
 * The behaviors that must be usable in concrete engine are:
 * - BHR_INCREMENT : Behavior Increment (only for int typed fields)
 * - BHR_TS_ON_EDIT : Behavior Timestamp on edit (only for timestamp type fields)
 * - BHR_DT_ON_EDIT : Behaviour Datetime on edit (only for datetime type fields)
 * - BHR_SHA1 : Behavior sha1 (only for string type fields)
 * - BHR_MD5 : Behavior md5 (only for string type fields)
 * - BHR_URLIZE : Behavior Urlize (only for string type fields)
 *
 * Behavior for a field can be replaced by a callback method of yours.
 * Regarding this field behavior:
 *
 * (start code)
 * $profilePictureField = array (
 * 			'name' => 'profile_picture',
 * 			'label' => 'Profile picture',
 * 			'type' =>  DBSchema::TYPE_FILE,
 * 			'behavior' => array ( &CallBackObjectInstance , 'methodName' , array ( 'argument 2' , 'some other argument' ) ) ,
 * 			'validation' => array (
 * 				'rule' => array ( 'jpg' , 'png' ),
 * 				'message' => 'Your profile picture must be a JPG or a PNG file.'
 * 			);
 * (end)
 * we should have a class named CallBackObjectInstance, that implements the method methodName:
 * (start code)
 * class CallBackObjectInstance {
 * 		function methodName ( $fieldValue , $argument2 , $someOtherArgument )
 * 		{
 * 			// do something on value
 * 			if ( is_uploaded_file ( $fieldValue ) )
 * 			{
 * 				//...
 * 			}
 * 			// and return it
 * 			return $fieldValue ;
 * 		}
 * }
 * (end)
 *
 * Here are some others valid callback behaviors and their corresponding functions:
 *
 * (start code)
 * $behaviorFunction = array ( 'function_name' ) ;
 *
 * function function_name ( $fieldValue )
 * {
 * 		// Make something
 * 		return $fieldValue;
 * }
 *
 *
 * $behaviorFunctionWArgs = array ( 'function_name' , array ( 'an argument' ) ) ;
 *
 * function function_name ( $fieldValue , $otherArgument)
 * {
 * 		// Make something
 * 		return $fieldValue;
 * }
 * (end)
 * and so on.
 *
 *
 *
 *
 *
 *
 *
 *
 *
 * Magic field names:
 *
 *
 *
 * There is 3 magic fields name:
 * - updated
 * - modified
 * - created
 *
 * If you add in your table structure a field named like one of these,
 * when an entry is added in your table, the 'created' field will automatically be
 * filled with current Date and Time or Timestamp.
 * And so on, if an entry is updated, the 'updated' or 'modified' or both fields are automatically filled with
 * current Date and Time or Timestamp.
 *
 * These fields must be typed as DATETIME or as TIMESTAMP.
 *
 * This feature is considered as an auto behavior, because the implementation of magic fields resolution is done
 * in DBTableSchema::applyInputBehaviors method.
 *
 * Using BHR_DT_ON_EDIT or BHR_TS_ON_EDIT on magic fields 'updated' and 'modified'  is obvious:
 * the time will be updated twice.
 *
 *
 * Writing conditions:
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
 * @see DBSchema
 * @see DBTableSchema
 * @see MySQLEngine
 * @see DBValidator
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

	function get($table, $id, $fields = array()) {
		return $this->find($table, $id, $fields);
	}

	function getAll($table, $cond, $limit = 0, $fields = array()) {
		return $this->findAll($table, $cond, $limit, $fields);
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
	 * Find an entry in a table based on ID
	 *
	 * @param object $table The table where to search
	 * @param object $id The ID of an entry
	 * @param object $fields [optional] Fields to return, default will return all fields
	 * @param object $order , default will return ascendant order
	 * @return bool False on failure, entries on success
	 */
	function findNextPrevious($table, $id, $fields = array(),$order) {
		return false;
	}
	
	
	/**
	 * Find all entries in a table, depending on conditions
	 *
	 * @param object $table The table where to search
	 * @param object $cond [optional] Conditions
	 * @param object $limit [optional] Max returned entries
	 * @param object $fields [optional] Fields to return, default will return all fields
	  * @param object $distinct [optional] flag disctinct for unique values in a column
	 * @return bool False on failure, entries on success
	 */
	function findAll($table, $cond = array(), $limit = 0, $fields = array(),$distinct = false) {
		return false;
	}

	/**
	 * Find first entry in a table, depending on conditions
	 *
	 * @param object $table The table where to search
	 * @param object $cond [optional] Conditions
	 * @param object $fields [optional] Fields to return, default will return all fields
	 * @param object $order_fields Fields to order
	 * @param object $order [optional] order type, default will ascendant order
	 * @return bool False on failure, entry on success
	 */
	function findAndOrder($table, $distinct = false,$cond = array(), $limit = 0, $fields = array(), $order_fields = array(), $order = 'ASC') {
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
	 * Find first entry in a table, depending on conditions
	 *
	 * @param object $table The table where to search
	 * @param object $cond [optional] Conditions
	 * @param object $fields [optional] Fields to return, default will return all fields
	 * @param object $fields [optional] Fields to order
	 * @param object $order , default will return ascendant order
	 * @return bool False on failure, entry on success
	 */
	function findLast($table, $cond = array(), $fields = array()) {
		return false;
	}

	
	
	/**
	 * Find random results
	 *
	 *
	 * @param unknown_type $table
	 * @param unknown_type $fields
	 * @param unknown_type $conds
	 */
	function findRandom($table, $fields = array(), $conds = array()) {
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
					$result = $this->findChildren($inf['source'], $result, $subFields, $recursivity - 1);
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
