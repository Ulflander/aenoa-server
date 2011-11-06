<?php

require(dirname(__FILE__).DS.'sag'.DS.'src'.DS.'Sag.php') ;

/**
 * Concrete implementation of AbstractDBEngine for CouchDB
 *
 * @see AbstractDBEngine
 */
class CouchDBEngine extends AbstractDBEngine {

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
	
	return $res ;
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
	
	return false ;
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
	
	return $res2;
    }

    function hasStructureCapability() {
	return false;
    }

    function hasQueryCapability() {
	return false;
    }

    function find($table, $id, $fields = array()) {
	return array();
    }


    function findAll($table, $cond = array(), $limit = 0, $fields = array()) {
	$schema = $this->tableExistsOr403($table);

	$result = false ;
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
	
	return !empty($this->__connection);
    }

    private function __getTableName($tableName) {
	if (array_key_exists('table_prefix', $this->source)) {
	    return $this->source['table_prefix'] . $tableName;
	}

	return $tableName;
    }

}

?>
