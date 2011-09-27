<?php

require(dirname(__FILE__) . DS . 'cassandra' . DS . 'Cassandra.php');

/**
 * Concrete implementation of AbstractDBEngine for Cassandra
 *
 * @see AbstractDBEngine
 */
class CassandraEngine extends AbstractDBEngine {

    private $__connection = false;
    protected $__lastId;

    /////////////////////////////////////////////////////
    // AbstractDBEngine implementation


    function isUsable() {
	return ( $this->__connection != false );
    }

    /**
     * Enable TRANSACTION mode : no query is sended until endTransaction is called
     */
    function startTransaction() {
	$this->_inTransaction = true;
    }

    /**
     * Disable TRANSACTION mode : all queries since call of startTransaction are sended, then transaction mode is disabled
     * 
     * @return boolean True if transaction did not return any error, false otherwise
     */
    function endTransaction() {
	$res = false;

	$this->_queries = array();

	$this->_inTransaction = false;

	return $res;
    }

    /**
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
     * Method to compare a database source with the current engine database source.
     *
     * @param array $database
     * @return True is $database array and current database are the same, false otherwise.
     */
    function compareSource($database) {
	return ( $this->isValidSource($this->source) );
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
	return (!empty($database) );
    }

    /**
     * Open Cassandra connection
     *
     * @see AbstractDBEngine::open()
     */
    function open() {

	try {
	    $this->__connection = Cassandra::createInstance(array($this->source));

	    return true;
	} catch (Exception $e) {
	    if (debuggin()) {
		echo $e->__toString();
	    }
	};

	return false;
    }

    /**
     * Closes Cassandra connection
     * 
     * @see AbstractDBEngine::close()
     */
    function close() {
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

    function sourceExists($database, $create = false) {
	$result = false;

	try {
	    Cassandra::createInstance(array($database));

	    return true;
	} catch (Exception $e) {
	    if (debuggin()) {
		echo $e->__toString();
	    }
	};

	return false;
    }

    function createSource($database) {
	$result = false;
	return $result;
    }

    function setStructure($structure = array(), $create = false) {
	if ($this->isUsable() == false) {
	    return false;
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

	if (( (!empty($tstruct) && $create) || ($this->hasAnyTable() == true && debuggin() ) ) && Config::get(App::DBS_AUTO_EXPAND) == true) {
	    $res2 = $this->__applyStructure($tstruct2);
	} else if ($this->hasAnyTable() == false) {
	    $res2 = false;
	}

	return $res2;
    }

    function hasStructureCapability() {
	return true;
    }

    function hasQueryCapability() {
	return false;
    }

    function find($table, $id, $fields = array()) {
	return array();
    }

    function findAll($table, $cond = array(), $limit = 0, $fields = array()) {
	$schema = $this->tableExistsOr403($table);

	$result = false;
	return $result;
    }

    function findFirst($table, $cond = array(), $fields = array(), $childsRecursivity = 0) {
	$schema = $this->tableExistsOr403($table);


	return array();
    }

    function findRandom($table, $fields = array(), $conds = array()) {
	$schema = $this->tableExistsOr403($table);

	return $result;
    }

    function edit($table, $id, $content = array()) {
	$schema = $this->tableExistsOr403($table);

	return false;
    }

    function editAll($table, $content = array(), $cond = array()) {
	$schema = $this->tableExistsOr403($table);

	return false;
    }

    function add($table, $content = array()) {
	$schema = $this->tableExistsOr403($table);


	$this->__lastId = -1;
	return false;
    }

    function addAll($table, $rows = array()) {
	$this->tableExistsOr403($table);

	return false;
    }

    function count($table, $cond = array()) {
	$schema = $this->tableExistsOr403($table);

	return 0;
    }

    function lastId() {
	return $this->__lastId;
    }

    function delete($table, $id) {
	$schema = $this->tableExistsOr403($table);

	return false;
    }

    function deleteAll($table, $cond = array()) {
	$schema = $this->tableExistsOr403($table);

	return false;
    }

    protected function hasAnyTable() {

	return!empty($this->__connection);
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

	    return false;
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
	$this->__connection->createKeyspace('CassandraExample');
	$this->__connection->useKeyspace('CassandraExample');
	return true;
    }

    private function __getCreateField(&$field, $onAlter) {
	$type = $this->aenoaTypeToCassandraType($field['type'], @$field['values'], @$field['default'], @$field['length'], @$field['validation']);
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

    public function aenoaTypeToCassandraType($type) {
	if (array_key_exists($type, $this->_types)) {

	    return $this->_types[$type];
	}

	return $type;
    }

    private $_types = array(
	'file' => Cassandra::TYPE_UTF8,
	'string' => Cassandra::TYPE_UTF8,
	'text' => Cassandra::TYPE_UTF8,
	'int' => Cassandra::TYPE_INTEGER,
	'float' => Cassandra::TYPE_LONG,
	'datetime' => Cassandra::TYPE_TIME_UUID,
	'enum' => Cassandra::TYPE_UTF8,
	'timestamp' => Cassandra::TYPE_TIME_UUID,
	'boolean' => Cassandra::TYPE_INTEGER,
	'child' => Cassandra::TYPE_UTF8,
	'parent' => Cassandra::TYPE_UTF8
    );

}

?>
