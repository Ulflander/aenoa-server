<?php

/**
 * Concrete implementation of AbstractDBEngine for MySQL
 *
 * @see AbstractDBEngine
 */
class MySQLEngine extends AbstractDBEngine {

	private $__connection = false;
	private $__sqlOps = array('like', 'ilike', 'or', 'not', 'in', 'between', 'regexp', 'similar to');
	protected $__lastId;
	protected $_queries = array () ;

	/////////////////////////////////////////////////////
	// AbstractDBEngine implementation


	function isUsable() {
		return ( $this->__connection != false );
	}

	/**
	 * Enable TRANSACTION mode : no query is sended until endTransaction is called
	 */
	function startTransaction() {
		$this->_queries = array();
		$this->_inTransaction = true;
	}

	/**
	 * Disable TRANSACTION mode : all queries since call of startTransaction are sended, then transaction mode is disabled
	 * 
	 * @return boolean True if transaction did not return any error, false otherwise
	 */
	function endTransaction() {
		
		mysql_query('START TRANSACTION', $this->__connection);
		$res = true;
		foreach ($this->_queries as $query) {
			if (!mysql_query($query, $this->__connection)) {
				if (debuggin()) {
					trigger_error('SQL TRANSACTION MODE ERROR: ' . mysql_error($this->__connection), E_USER_WARNING);
				}

				$res = false;
			}
		}
		mysql_query('COMMIT', $this->__connection);

		$this->_inTransaction = false;

		return $res;
	}

	/**
	 * For MySQLEngine, $database must be an array containing:
	 * ['host'] => 'your.mysql.host.com'
	 * ['login'] => 'mysql_login'
	 * ['password'] => 'mysql_passwd'
	 * ['database'] => 'mysql_database'
	 *
	 * You can eventually add these keys and values:
	 *
	 * ['persistent'] => bool true/false
	 * ['table_prefix'] => 'aenoa_'
	 * ['no_drop'] => bool true/false // Default is false, if you set no_drop to true,
	 * 					// then tables that does not exist in structure are not dropped from database
	 * 					// Otherwise, tables that does not exist in structure will be dropped by default
	 * ['table_engine'] => 'MYISAM' // Default is INNODB, this will be applied to every table
	 *
	 * If you need to use a particular port, add it to the host:
	 * ['host'] => 'your.mysql.host.com:3306'
	 *
	 * @see AbstractDBEngine::setSource
	 * @param object $database
	 * @param bool $create Could be not available depending on your MySQL rights
	 * @return
	 */
	function setSource($database, $create = false) {
		if ($this->compareSource($database)) {
			return true;
		}
		$this->source = $database;
		$this->close();

		return $this->open();
	}

	/**
	 *
	 *
	 * @see AbstractDBEngine::getIdentifiers()
	 */
	function getIdentifiers() {
		return $this->database;
	}

	/**
	 * Method to compare a database source with the current engine database source.
	 *
	 * @param array $database
	 * @return True is $database array and current database are the same, false otherwise.
	 */
	function compareSource($database) {
		return ( $this->isValidSource($this->source)
			&& $this->isValidSource($database)
			&& $this->source['host'] == $database['host']
			&& $this->source['login'] == $database['login']
			&& $this->source['password'] == $database['password']
			&& $this->source['database'] == $database['database'] );
	}

	/**
	 * MySQLEngine method to check $database array.
	 * Will return true if all these array keys exist: host, login, password, database
	 * Will return false otherwise
	 *
	 * @param object $database
	 * @return True if $database array contains valid keys, false otherwise
	 */
	function isValidSource($database) {
		return (!empty($database)
			&& array_key_exists('host', $database)
			&& array_key_exists('login', $database)
			&& array_key_exists('password', $database)
			&& array_key_exists('database', $database) );
	}

	/**
	 * Open MySQL connection
	 *
	 * @see AbstractDBEngine::open()
	 */
	function open() {
		if (!is_resource($this->__connection) && !empty($this->source)) {
			if (array_key_exists('persistent', $this->source) && $this->source['persistent'] == true) {
				$this->__connection = mysql_pconnect($this->source['host'], $this->source['login'], $this->source['password']);
			} else {
				$this->__connection = mysql_connect($this->source['host'], $this->source['login'], $this->source['password']);
			}
			if (is_resource($this->__connection) && mysql_select_db($this->source['database'], $this->__connection) === true) {
				$q = "SET character_set_results = 'utf8', character_set_client = 'utf8', character_set_connection = 'utf8', character_set_database = 'utf8', character_set_server = 'utf8'";

				$this->log($q);

				mysql_query($q, $this->__connection);
			} else {
				$this->log('Connection attempt failed');
			}
		}

		return is_resource($this->__connection);
	}

	/**
	 * Closes MySQL connection
	 *
	 * @see AbstractDBEngine::close()
	 */
	function close() {
		if (is_resource($this->__connection)) {
			mysql_close($this->__connection);
		}

		return true;
	}

	/**
	 *
	 *
	 *
	 */
	function getConnection() {
		return $this->__connection;
	}

	function test($host, $login, $password) {
		return ( mysql_connect($host, $login, $password) !== false );
	}

	function sourceExists($database, $create = false) {
		if ($this->__connection == false) {
			$this->__connection = @mysql_connect($database['host'], $database['login'], $database['password']);
		}

		if (is_resource($this->__connection) && mysql_select_db($database['database'], $this->__connection) === true) {
			mysql_query("SET character_set_results = 'utf8', character_set_client = 'utf8', character_set_connection = 'utf8', character_set_database = 'utf8', character_set_server = 'utf8'", $this->__connection);

			$result = true;
		} else {
			$result = false;

			if ($create) {
				return $this->createSource($database);
			}
		}

		return $result;
	}

	function createSource($database) {
		if (!debuggin() || Config::get(App::DBS_AUTO_EXPAND) !== true) {
			return false;
		}
		$result = false;
		if ($this->sourceExists($database) == false) {
			if ($this->__connection === false) {
				$this->__connection = mysql_connect($database['host'], $database['login'], $database['password']);
			}

			if ($this->__connection !== false) {

				mysql_query("SET character_set_results = 'utf8', character_set_client = 'utf8', character_set_connection = 'utf8', character_set_database = 'utf8', character_set_server = 'utf8'", $this->__connection);


				$result = mysql_query('CREATE DATABASE `' . $database['database'] . '` DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;', $this->__connection) && mysql_select_db($database['database'], $this->__connection);
			}
		}
		return $result;
	}

	function setStructure($structure = array(), $create = false) {
		if ($this->isUsable() == false) {
			return;
		}

		$res = true;
		$res2 = true;
		$tstruct = array();
		$tstruct2 = array();

		foreach ($structure as $table => &$struct) {
			$tstruct[$table] = array();
			$tstruct2[$table] = array();

			if (array_key_exists($table, $this->tables) == false) {
				$this->tables[$table] = 0;
			}

			foreach ($struct as &$field) {
				if (DBTableSchema::validateField($field)) {
					$tstruct2[$table][$field['name']] = $field;
					$tstruct[$table][] = $field;
				} else {
					$res = false;
				}
			}
		}

		$this->structure = &$tstruct;

		$this->struct = $tstruct2;

		if ((!empty($tstruct) && $create) || ( ($this->hasAnyTable() == true && debuggin() && Config::get(App::DBS_AUTO_EXPAND) == true ) )) {
			$res2 = $this->__applyStructure($tstruct2);
		} else if ($this->hasAnyTable() == false) {
			$res2 = false;
		}

		return $res && $res2;
	}

	function hasStructureCapability() {
		return true;
	}

	function query($query) {
		$this->log($query);

		return @mysql_query($query, $this->getConnection());
	}

	function hasQueryCapability() {
		return true;
	}

	function find($table, $id, $fields = array()) {
		$schema = $this->tableExistsOr403($table);

		$res = $this->findAll($table, array($schema->getPrimary() => $id), 1, $fields);

		if (!empty($res)) {
			return $res[0];
		}

		return array();
	}

	function findAll($table, $cond = array(), $limit = 0, $fields = array()) {
		$schema = $this->tableExistsOr403($table);
		$q = 'SELECT ' . $this->__selectFields($fields, $table) . ' FROM `' . $this->source['database'] . '`.`' . $table . '` ';
		$q .= $this->__getCond($cond, $table);
		$q .= $this->__getLimit($table, $limit);
		$q .= ';';
		$this->log($q);
		$res = mysql_query($q, $this->getConnection());
		if ($res === false) {
			return $res;
		}
		$result = $this->__fetchArr($res, $schema->getInitial(), $fields, array(), false);
		@mysql_free_result($res);
		return $result;
	}

	function findAndOrder($table, $cond = array(), $limit = 0, $fields = array(), $order_fields = array(), $order = 'ASC') {
		$schema = $this->tableExistsOr403($table);
		$q = 'SELECT ' . $this->__selectFields($fields, $table) . ' FROM `' . $this->source['database'] . '`.`' . $table . '` ';
		$q .= $this->__getCond($cond, $table);
		$q .= $this->__getLimit($table, $limit);
		$q .= ' ORDER BY ' . $this->__selectFields($order_fields, $table) . ' ';
		$q .= $order;
		$q .= ';';
		$this->log($q);
		$res = mysql_query($q, $this->getConnection());
		if ($res === false) {
			return $res;
		}
		$result = $this->__fetchArr($res, $schema->getInitial(), $fields, array(), false);

		@mysql_free_result($res);
		return $result;
	}

	function findFirst($table, $cond = array(), $fields = array(), $childsRecursivity = 0) {
		$res = $this->findAll($table, $cond, 1, $fields, $childsRecursivity);
		if (!empty($res)) {
			return $res[0];
		}
		return array();
	}

	function findRandom($table, $fields = array(), $conds = array()) {
		$schema = $this->tableExistsOr403($table);

		$q = 'SELECT ' . $this->__selectFields($fields, $table) .
			' FROM `' . $this->source['database'] . '`.`' . $table . '` ' .
			$this->__getCond($conds, $table) .
			' ORDER BY RAND() LIMIT 1 ';



		$this->log($q);

		$res = mysql_query($q, $this->getConnection());
		$result = $this->__fetchArr($res, $schema->getInitial(), $fields);

		if (!empty($result)) {
			return $result[0];
		}
		return $result;
	}

	private function __selectFields($fields, $table) {
		return ( empty($fields) ?
				'`' . implode('`,`', $this->__getStructureFields($table)) . '`' :
				'`' . implode('`,`', $this->getTableSchema($table)->filterFields($fields)) . '`'
			);
	}

	private function __getCond($cond, $table = '') {
		if (empty($cond)) {
			return ' WHERE 1';
		} else {
			$c = '';


			foreach ($cond as $fieldname => $val) {
				$operator = '=';

				if (strlen($c) > 0) {
					$c.=' AND ';
				}

				$fieldname = trim($fieldname);

				$escapeVal = true;
				
				if (is_string($val) && strlen($val) > 0 && $val[0] == '(')
				{
					$escapeVal = false;
				}

				if (@$this->struct[$table][$fieldname]['behavior'] & DBSchema::BHR_PICK_IN) {
					$val = '\'(^' . $val . '\,)|(\,' . $val . '$)|(\,' . $val . '\,)|(^' . $val . '$)\'';
					$escapeVal = false;
					$operator = 'REGEXP';
				}

				// $operatorMatch = '/^(\\x20(' . join(')|(', $this->__sqlOps) .')|\\x20<[>=]?(?![^>]+>)|\\x20[>=!]{1,3}(?!<))/is';

				if (is_string($val)) {
					if ($val === 'IS NULL') {
						$c.= '`' . $fieldname . '` IS NULL';
						continue;
					} else
					if ($val === 'IS NOT NULL') {
						$c.= '`' . $fieldname . '` IS NOT NULL';
						continue;
					}
				}

				if (substr_count($fieldname, ' ') == 1) {
					list($fieldname, $operator) = explode(' ', $fieldname);
				}
				if (!is_array($val)) {
					$c .= '`' . $fieldname . '` ' . trim($operator);

					if ($escapeVal) {
						$c .= ' \'' . $val . '\'';
					} else {
						$c .= ' ' . $val;
					}
				} else if (!empty($val)) {
					$c .= '`' . $fieldname . '` IN (\'' . implode('\',\'', $val) . '\')';
				}
			}
			$c = ' WHERE ' . $c;
		}
		return $c . ' ';
	}

	private function __getLimit($table, $limit) {
		if ($limit == 0) {
			return '';
		}

		if (is_int($limit)) {
			return ' LIMIT 0,' . $limit;
		} else if (is_array($limit)) {
			
			$c = count($limit);
			
			if ($c == 2 && is_int($limit[0])) {
				return ' LIMIT ' . $limit[1] . ', ' . $limit[0];
			} else if (($c == 2 || $c == 1) && is_string($limit[0])) {
				return ' ORDER BY `' . $this->source['database'] . '`.`' . $table . '`.`' . $limit[0] . '` ' . ($c == 1 || strtoupper($limit[1]) == 'ASC' ? 'ASC' : 'DESC' );
			} else if ($c == 4) {
				return ' ORDER BY `' . $this->source['database'] . '`.`' . $table . '`.`' . $limit[2] . '` ' . (strtoupper($limit[3]) == 'ASC' ? 'ASC' : 'DESC' ) . ' LIMIT ' . $limit[1] . ', ' . $limit[0];
			}
		}
	}

	private function __getStructureFields($table) {
		$a = array();

		$structure = $this->getTableSchema($table)->getInitial();

		foreach ($structure as $name => $field) {
			$a[] = $name;
		}
		return $a;
	}

	function edit($table, $id, $content = array()) {
		$schema = $this->tableExistsOr403($table);

		$q = '';

		if (!empty($content)) {
			$entries = array();

			$initial = $schema->getInitial();

			foreach ($initial as $name => &$field) {
				if ($name == 'created') {
					continue;
				}

				if (array_key_exists($name, $content)) {
					$val = $content[$name];
				} else if ($name != 'updated' && $name != 'modified') {
					continue;
				}
				$val &= DBTableSchema::applyInputBehaviors($field, $val, true, $this->getConnection());
				if ($val != '') {
					$row[$name] = &$val;
				}

				$entries[] = '`' . $name . '` = \'' . $val . '\'';
			}

			$q = 'UPDATE `' . $this->source['database'] . '`.`' . $table . '` SET ';
			$q .= implode(', ', $entries);
			$q .= ' WHERE `' . $this->source['database'] . '`.`' . $table . '`.`' . $schema->getPrimary() . '` = ' . (is_numeric($id) ? $id : '\'' . $id . '\'') . ' LIMIT 1';

			$this->log($q);

			if (!$this->_inTransaction) {
				$res = mysql_query($q, $this->getConnection());
			} else {
				$this->_queries[] = $q;
				$res = true;
			}
			return $res;
		}

		return false;
	}

	function editAll($table, $content = array(), $cond = array()) {
		$schema = $this->tableExistsOr403($table);

		$q = '';

		if (!empty($content)) {
			$entries = array();

			$initial = $schema->getInitial();

			foreach ($initial as $name => &$field) {
				if ($name == 'created') {
					continue;
				}
				
				if (array_key_exists($name, $content)) {
					$val = $content[$name];
				} else {
					continue;
				}
				$val &= DBTableSchema::applyInputBehaviors($field, $val, true, $this->getConnection());
				
				if ($val != '') {
					$row[$name] = &$val;
				}

				if (substr($val, 0, 1) == '(') {
					$entries[] = '`' . $name . '` = ' . $val;
				} else {
					$entries[] = '`' . $name . '` = \'' . $val . '\'';
				}
			}
			if ( empty($entries) )
			{
				return false;
			}
			
			$q = 'UPDATE `' . $this->source['database'] . '`.`' . $table . '` SET ';
			$q .= implode(', ', $entries);
			$q .= $this->__getCond($cond, $table);

			$this->log($q);

			if (!$this->_inTransaction) {
				$res = mysql_query($q, $this->getConnection());
			} else {
				$this->_queries[] = $q;
				$res = true;
			}
			return $res;
		}

		return false;
	}

	function add($table, $content = array()) {
		$schema = $this->tableExistsOr403($table);

		$q = '';

		if (!empty($content)) {
			$q = $this->__getAddQuery($table, $content);

			$this->log($q);

			if (!$this->_inTransaction) {
				$res = mysql_query($q, $this->getConnection());
				$this->__lastId = mysql_insert_id($this->getConnection());
			} else {
				$this->_queries[] = $q;
				$res = true;
			}

			return $res;
		}
		$this->__lastId = -1;
		return false;
	}

	function addAll($table, $rows = array()) {
		$this->tableExistsOr403($table);

		$q = '';

		foreach ($rows as &$row) {
			$q .= $this->__getAddQuery($table, $row, ($q != ''));
		}

		$this->log($q);

		if (!$this->_inTransaction) {
			$res = mysql_query($q, $this->getConnection());

			$this->__lastId = ( $res ? mysql_insert_id($this->getConnection()) : -1 );
		} else {
			$this->_queries[] = $q;

			$res = true;
		}



		return $res;
	}

	function __getAddQuery($table, $content, $onlyValues = false) {
		$q = '';

		$keys = array();

		$values = array();

		$initial = $this->getTableSchema($table)->getInitial();

		foreach ($initial as $name => &$field) {
			if (array_key_exists($name, $content)) {
				$val = $content[$name];
			} else {
				$val = '';
			}
			$val &= DBTableSchema::applyInputBehaviors($field, $val, false, $this->getConnection());
			$row[$name] = &$val;

			$keys[] = $name;

			$values[] = $val;
		}

		$vals = '\'' . implode('\',\'', $values) . '\'';

		if ($onlyValues == false) {
			$q = 'INSERT INTO `' . $this->source['database'] . '`.`' . $table . '` (`';
			$q .= implode('`,`', $keys);
			$q .= '`) VALUES';
		} else {
			$q = ',';
		}

		$q .= ' (' . str_replace(array("\n"), array(''), $vals) . ') ' . "\n";

		return $q;
	}

	function count($table, $cond = array()) {
		$schema = $this->tableExistsOr403($table);

		$q = 'SELECT COUNT(*) FROM `' . $this->source['database'] . '`.`' . $table . '` ' . (!empty($cond) ? $this->__getCond($cond, $table) : '' ) . ' ;';
		$this->log($q);

		$res = mysql_fetch_array(mysql_query($q, $this->getConnection()));

		return $res[0];
	}

	function lastId() {
		return $this->__lastId;
	}

	function delete($table, $id) {
		$schema = $this->tableExistsOr403($table);

		$q = 'DELETE FROM `' .
			$this->source['database'] . '`.`' .
			$table .
			'` WHERE `' . $table . '`.`' . $schema->getPrimary() .
			'` = ' . (is_numeric($id) ? $id : '\'' . $id . '\'') . ' ;';

		$this->log($q);
		$res = mysql_query($q, $this->getConnection());

		return $res;
	}

	function deleteAll($table, $cond = array()) {
		$schema = $this->tableExistsOr403($table);

		$q = 'DELETE FROM `' . $this->source['database'] . '`.`' . $table . '` ' . $this->__getCond($cond, $table) . ' ;';

		$this->log($q);
		$res = mysql_query($q, $this->__connection);

		return $res;
	}

	protected function hasAnyTable() {
		$tables = $this->__fetchArr(mysql_query('SHOW TABLES FROM `' . $this->source['database'] . '`', $this->__connection));

		return!empty($tables);
	}

	// OK, let's apply Aenoa DB Structure to MySQL Database
	private function __applyStructure($structure) {
		// There is no table: we create tables
		if ($this->hasAnyTable() == false) {
			$res = true;

			// For each table in structure
			foreach ($structure as $tableName => &$fields) {
				// We create the table
				if (!$this->__createTable($tableName, $fields)) {
					$res = false;
				}
			}

			return $res;
			// That's OK ! There are some tables in database
		} else {

			$res = true;
			$q = 'SHOW TABLES FROM `' . $this->source['database'] . '`';

			$this->log($q);
			$r = mysql_query($q, $this->getConnection());
			if (!$r) {
				return false;
			}

			$tables = $this->__fetchArr($r);

			// Let's check in DB tables if there is any table that should be droppable
			foreach ($tables as $k => $tableName) {
				// If the table does not exist in the structure we DROP it
				if (array_key_exists($tableName, $structure) == false
					&& (array_key_exists('no_drop', $this->source) == false
					|| $this->source['no_drop'] === true)) {
					$q = 'DROP TABLE `' . $this->source['database'] . '`.`' . $tableName . '`';
					$this->log($q);
					$res = mysql_query($q, $this->getConnection());
					unset($tables[$k]);
				}
			}

			// Any
			foreach ($structure as $tableName => &$structfields) {
				if (in_array($tableName, $tables) == false && $this->__createTable($tableName, $structfields) == false) {
					$res = false;
					continue;
				}
				$q = 'DESCRIBE `' . $this->source['database'] . '`.`' . $tableName . '`';
				$this->log($q);
				$fields = mysql_query($q, $this->getConnection());
				$fields = $this->__fetchArr($fields);

				foreach ($structfields as &$structFieldDesc) {
					$found = false;
					foreach ($fields as &$dbFieldDesc) {
						if ($dbFieldDesc[0] == @$structFieldDesc['name']) {
							$found = true;
							break;
						}
					}
					if (!$found) {
						$q = 'ALTER TABLE `' . $this->source['database'] . '`.`' . $tableName . '` ADD ' . $this->__getCreateField($structFieldDesc, true);
						$this->log($q);
						if (!$this->query($q, $this->getConnection())) {
							$res = false;
						}
					}
				}

				foreach ($fields as &$dbFieldDesc) {
					$found = false;
					foreach ($structfields as &$structFieldDesc) {
						if ($dbFieldDesc[0] == $structFieldDesc['name']) {
							$found = true;
							break;
						}
					}
					if (!$found) {
						$q = 'ALTER TABLE `' . $this->source['database'] . '`.`' . $tableName . '` DROP `' . $dbFieldDesc[0] . '`';
						$this->log($q);
						if (!$this->query($q, $this->getConnection())) {
							$res = false;
						}
					}
				}
			}
			return $res;
		}
	}

	private function __fetchArr($ressource, $tableStruct=null, $selectFields=array(), $simpleArray = true) {
		if ($ressource === false) {
			return array();
		}

		$res = array();

		while (true) {
			if (($v = @mysql_fetch_row($ressource)) !== false) {
				if (count($v) == 1) {
					$res[] = $v[0];
				} else {
					$res[] = $v;
				}
			} else {
				break;
			}
		}
		if (!is_null($tableStruct) && @$res[0] && is_array($res[0])) {
			$pres = array();
			foreach ($res as $k => &$line_array) {
				$pres[$k] = array();
				if (!empty($selectFields)) {
					foreach ($selectFields as $fieldname) {
						$fieldDesc = $tableStruct[$fieldname];
						$pres[$k][$fieldname] = DBTableSchema::applyOutputBehaviors($fieldDesc, array_shift($line_array));
					}
				} else {

					foreach ($tableStruct as $name => &$field) {
						$pres[$k][$name] = DBTableSchema::applyOutputBehaviors($field, array_shift($line_array));
					}
				}
			}
		} else {
			$pres = &$res;
		}

		@mysql_free_result($ressource);

		return $pres;
	}

	private function __createTable($tableName, &$fields) {
		$q = 'CREATE TABLE `' . $this->source['database'] . '`.`' . $this->__getTableName($tableName) . '` (';
		foreach ($fields as &$field) {
			$f = $this->__getCreateField($field, false);
			if ($f !== false) {
				$q .= $f . ' , ';
			}
		}

		$q = substr($q, 0, strlen($q) - 3) . ') ENGINE = ' . $this->__getEngine() . $this->__getCharacterSet() . ' ; ';

		$this->log($q);

		return mysql_query($q, $this->getConnection());
	}

	private function __getCreateField(&$field, $onAlter) {
		$type = $this->aenoaTypeToMySQLType($field['type'], @$field['values'], @$field['default'], @$field['length'], @$field['validation']);
		if (is_null($type)) {
			return false;
		}

		$q = '`' . $field['name'] . '` ' . $type;
		if (@$field['behavior'] & DBSchema::BHR_INCREMENT) {
			$q .= ' NOT NULL AUTO_INCREMENT';
		}

		if ($field['name'] == 'id') {
			if ($onAlter) {
				$q .= ', ADD PRIMARY KEY ( `id` )';
			} else {
				$q .= ', PRIMARY KEY ( `id` )';
			}
		} else if (@$field['behavior'] & DBSchema::BHR_PRIMARY) {
			if ($onAlter) {
				$q.= ', ADD PRIMARY KEY ( `' . $field['name'] . '` )';
			} else {
				$q .= ', PRIMARY KEY ( `' . $field['name'] . '` )';
			}
		} else if (@$field['behavior'] & DBSchema::BHR_PRIMARY) {
			if ($onAlter) {
				$q.= ', ADD UNIQUE ( `' . $field['name'] . '` )';
			} else {
				$q.= ', UNIQUE ( `' . $field['name'] . '` )';
			}
		}
		return $q;
	}

	private function __getEngine() {
		if (array_key_exists('table_engine', $this->source)
			&& in_array(strtoupper($this->source['table_engine']), array('INNODB', 'MYISAM', 'MEMORY', 'MRG_MYISAM'))) {
			return strtoupper($this->source['table_engine']);
		}

		return 'INNODB';
	}

	private function __getCharacterSet() {
		return ' CHARACTER SET utf8 COLLATE utf8_unicode_ci';
	}

	private function __getTableName($tableName) {
		if (array_key_exists('table_prefix', $this->source)) {
			return $this->source['table_prefix'] . $tableName;
		}

		return $tableName;
	}

	public function aenoaTypeToMySQLType($type, $values = null, $default = null, $length = null, $validation = null) {
		if (array_key_exists($type, $this->_types)) {
			if ($type == 'enum') {
				$_type = 'ENUM(\'' . implode('\' , \'', $values) . '\') NOT NULL DEFAULT \'' . $default . '\'';
			} else {
				$_type = '';

				if (!is_null($validation) && $validation && in_array($type, array('string', 'text', 'int', 'c', 'float', 'datetime'))) {
					$_type = ' NOT NULL';
				}

				$_type = $this->_types[$type] . $_type;

				if (is_int($length) && $length > 0) {
					switch (true) {
						case $type == 'string' && $length <= 255 :
							$_type = str_replace('(255)', '(' . $length . ')', $_type);
							break;
						case $type == 'int' && $length <= 11 :
							$_type = str_replace('(11)', '(' . $length . ')', $_type);
							break;
					}
				}
			}
			return $_type;
		}

		return $type;
	}

	private $_types = array(
		'file' => 'VARCHAR(255)',
		'string' => 'VARCHAR(255)',
		'text' => 'LONGTEXT',
		'int' => 'INT(11)',
		'float' => 'FLOAT',
		'datetime' => 'DATETIME',
		'enum' => 'ENUM',
		'timestamp' => 'TIMESTAMP DEFAULT 0',
		'boolean' => 'ENUM( \'true\', \'false\' ) NOT NULL DEFAULT \'false\'',
		'child' => 'INT(11)',
		'parent' => 'INT(11)'
	);

}

?>
