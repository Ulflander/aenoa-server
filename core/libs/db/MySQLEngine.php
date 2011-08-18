<?php





class MySQLEngine extends AbstractDB {
	
	private $__connection = false ;
	
	private $__sqlOps = array('like', 'ilike', 'or', 'not', 'in', 'between', 'regexp', 'similar to');
	
	protected $__lastId ;
	
	/////////////////////////////////////////////////////
	// AbstractDB implementation
	
	
	function isUsable () 
	{
		return ( $this->__connection != false ) ;
	}
	
	private $_doTemp = false ;
	
	/**
	 * Enable sql TRANSACTION mode : no query is sended until endTransaction is called
	 */
	function startTransaction ()
	{
		$this->_doTemp = true ;
	}
	
	/**
	 * Disable sql TRANSACTION mode : all queries since call of startTransaction are sended
	 * @return boolean True if transaction did not return any error, false otherwise 
	 */
	function endTransaction ()
	{
		mysql_query('START TRANSACTION',$this->__connection) ;
		$res = true ;
		foreach ( $this->_queries as $query )
		{
			if (!mysql_query($query,$this->__connection) )
			{
				if ( debuggin () )
				{
					trigger_error('SQL TRANSACTION MODE ERROR: ' . mysql_error ( $this->__connection ) , E_USER_WARNING ) ;
				} 
				
				$res = false ;
			}
		}
		mysql_query('COMMIT',$this->__connection) ;
		
		$this->_queries = array () ;
		
		$this->_doTemp = false ;
	}
	
	
	
	/**
	 * For MySQLEngine, $databse must be an array containing:
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
	 * @param object $database
	 * @param bool $create Could be not available depending on your MySQL rights
	 * @return 
	 */
	function setSource ( $database , $create = false ) 
	{
		if ( $this->isSameDatabase ( $database ) )
		{
			return true ;
		}
		
		$this->database = $database ;
		
		$this->close () ;
		
		return $this->open () ;
	}
	
	
	/**
	 * MySQLEngine method to compare a database with the current engine database.
	 * 
	 * @param object $database
	 * @return True is $database array and current database are the same, false otherwise.
	 */
	function isSameDatabase ( $database )
	{
		return ( $this->isValidDatabaseArr ( $this->database )
			&& $this->isValidDatabaseArr ( $database ) 
			&& $this->database['host'] == $database['host'] 
			&& $this->database['login'] == $database['login'] 
			&& $this->database['password'] == $database['password'] 
			&& $this->database['database'] == $database['database'] ) ;
	}
	
	
	/**
	 * MySQLEngine method to check $database array.
	 * Will return true if all these array keys exist: host, login, password, database
	 * Will return false otherwise
	 * 
	 * @param object $database
	 * @return True if $database array contains valid keys, false otherwise 
	 */
	function isValidDatabaseArr ( $database )
	{
		return ( !empty ( $database ) 
			&& array_key_exists('host', $database ) 
			&& array_key_exists('login', $database ) 
			&& array_key_exists('password', $database ) 
			&& array_key_exists('database', $database ) ) ;
	}
	
	
	function open ()
	{
		if( !is_resource($this->__connection) && !empty($this->database) )
		{
			if ( array_key_exists('persistent', $this->database ) && $this->database['persistent'] == true )
			{
				$this->__connection = mysql_pconnect ( $this->database['host'] , $this->database['login'] , $this->database['password'] ) ;
			} else {
				$this->__connection = mysql_connect ( $this->database['host'] , $this->database['login'] , $this->database['password'] ) ;
			}
			
			if ( is_resource($this->__connection) && mysql_select_db ( $this->database['database'], $this->__connection) === true ) 
			{
				mysql_query("SET character_set_results = 'utf8', character_set_client = 'utf8', character_set_connection = 'utf8', character_set_database = 'utf8', character_set_server = 'utf8'", $this->__connection);
				
				$this->_log[] = "SET character_set_results = 'utf8', character_set_client = 'utf8', character_set_connection = 'utf8', character_set_database = 'utf8', character_set_server = 'utf8'" ;
			} else {
				$this->_log[] = 'Connection attempt failed' ;
			}
		}
		return is_resource($this->__connection)  ;
	}
	
	
	
	
	function getConnection ()
	{		
		return $this->__connection ;
	}
	
	function close ()
	{
		if ( is_resource($this->__connection) )
		{
			mysql_close( $this->__connection ) ;
		}
		
		return true ;
	}

	function test ( $host , $login , $password )
	{
		return ( mysql_connect ( $host , $login , $password ) !== false ) ;
	}
	
	
	
	function sourceExists ( $database , $create = false )
	{
		if( $this->__connection == false )
		{
			$this->__connection = @mysql_connect ( $database['host'] , $database['login'] , $database['password'] )  ;
		}
		
		if ( is_resource( $this->__connection ) && mysql_select_db ( $database['database'] , $this->__connection ) === true ) 
		{
			mysql_query("SET character_set_results = 'utf8', character_set_client = 'utf8', character_set_connection = 'utf8', character_set_database = 'utf8', character_set_server = 'utf8'", $this->__connection);
				
			$result = true ;
		} else {
			$result = false ;
			
			if ( $create)
			{
				return $this->createSource($database);
			}
		}
	
		return $result ;
	}

	function createSource ( $database ) 
	{
		if ( !debuggin () || Config::get(App::DBS_AUTO_EXPAND) !== true )
		{
			return false ;
		}
		$result = false ;
		if ( $this->sourceExists( $database ) == false )
		{
			if( $this->__connection === false )
			{
				$this->__connection = mysql_connect ( $database['host'] , $database['login'] , $database['password'] ) ;
			}
			
			if ( $this->__connection !== false )
			{
				
				mysql_query("SET character_set_results = 'utf8', character_set_client = 'utf8', character_set_connection = 'utf8', character_set_database = 'utf8', character_set_server = 'utf8'", $this->__connection);
				
				
				$result = mysql_query( 'CREATE DATABASE `'.$database['database'].'` DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;' , $this->__connection ) && mysql_select_db ( $database['database'] , $this->__connection );
				
			}
			
		
		}
		return $result ;
	}
	
	function setStructure ( $structure = array (), $create = false ) 
	{
		if ( $this->isUsable( ) == false )
		{
			return;
		}
		
		$res = true ;
		$res2 = true ;
		$tstruct = array () ;
		$tstruct2 = array () ;
		
		foreach ( $structure as $table => &$struct )
		{
			$tstruct[$table] = array () ;
			$tstruct2[$table] = array () ;
			
			if ( array_key_exists($table, $this->tables) == false )
			{
				$this->tables[$table] = 0 ;
			}
			
			foreach ( $struct as &$field ) 
			{
				
				if ( DBHelper::validateField ( $field ) )
				{
					$tstruct2[$table][$field['name']] = $field;
					$tstruct[$table][] = $field ;
				} else {
					$res = false ;
				}
			}
		}
		
		$this->__edited = true ;
		
		$this->structure = &$tstruct ;
		
		$this->struct = $tstruct2 ;
		
		if ( ( (!empty ( $tstruct ) && $create) || ($this->hasAnyTable () == true && debuggin () ) ) && Config::get(App::DBS_AUTO_EXPAND) == true )
		{
			$res2 = $this->__applyStructure () ;
		} else if ( $this->hasAnyTable () == false ) {
			$res2 = false ;
		}
		
		return $res && $res2 ;
	}
	
	function hasStructureCapability () 
	{
		return true ;
	}
	
	function query ( $query ) 
	{
		$this->_log[] = 'Query(query): ' . $query ;
		return @mysql_query($query, $this->getConnection()) ;
	}
	
	function hasQueryCapability ()
	{
		return true ;
	}
	
	function tableExists ( $tableName ) 
	{
		return array_key_exists( $tableName, $this->struct ) ;
	}
	
	function tableExistsOr403 ( $tableName ) 
	{
		if( !$this->tableExists( $tableName ) )
		{
			App::do403( sprintf( _('Table %s does not exists' ), $tableName ) ) ;
		}
	}
	
	function find ( $table , $id , $fields = array () )
	{
		$this->tableExistsOr403($table);
		
		$res = $this->findAll ( $table , array ( $primaryKey = AbstractDB::getPrimary($this->struct[$table]) => $id ) , 1 , $fields );
		if ( !empty($res) ) 
		{
			return $res[0] ;
		}
		return array() ;
	}
	
	function findAll ( $table , $cond = array () , $limit = 0 , $fields = array () )
	{
		$this->tableExistsOr403($table);
		
		$q = 'SELECT ' . $this->__selectFields($fields,$table) . ' FROM `' . $this->database['database'] . '`.`' . $table . '` ' ;
		$q .= $this->__getCond ( $cond , $table) ;
		$q .= $this->__getLimit ( $table , $limit ) ;
		$q .= ';';
		$this->_log[] = 'Query(findAll): ' . $q ;
		$res = mysql_query($q, $this->getConnection()) ;
		if ( $res === false )
		{	return $res ;
		}
		$result =  $this->__fetchArr($res,$this->struct[$table],$fields,array(),false);
		@mysql_free_result ( $res ) ;
		return $result ;
	}
	
	private $__childCache = array () ;
	
	function findAscendants ( $table , $dbselection , $subFields = array () , $recursivity = 1 , $ordered = true )
	{
		$this->tableExistsOr403($table);
		
		if ( array_key_exists($table , $this->struct ) == false )
		{
			trigger_error('Table ' . $table . ' does not exists in structure ' . $this->database['host'] ) ;
			return $dbselection ;
		}
		
		if(count($this->struct[$table]) < 2 )
		{
			return $dbselection;
		}
		
		$unique = false ;
		foreach ( $dbselection as $k => &$res )
		{
			if ( is_string($k) )
			{
				$dbselection = array($dbselection) ;
				$unique = true ;
				break;
			}
		}
		
		$cond = array () ;
		
		$childTables = array () ;
		$result = array () ;
		$q = '' ;
	
		foreach( $this->struct[$table] as $fieldName => &$field )
		{
			if ( @$field['behavior'] & AbstractDB::BHR_PICK_IN || @$field['behavior'] & AbstractDB::BHR_PICK_ONE || $field['type'] == AbstractDB::TYPE_PARENT || $field['type'] == AbstractDB::TYPE_CHILD )
			{
				if ( array_key_exists($field['source'],$subFields) && empty($subFields[$field['source']]) )
				{
					continue;
				}
				$n = $fieldName ;
				$n2 = AbstractDB::getPrimary($this->struct[$field['source']]) ;
				
				$childTable = array () ;
				$childTable['fieldName'] = $n2;
				$childTable['isSameTable'] = ($field['source'] == $table);
				$childTable['resName'] = $n;
				$childTable['source'] = $field['source'];
				$childTable['ids'] = array () ;
				$childTable['multi'] = @$field['behavior'] & AbstractDB::BHR_PICK_IN ;
				foreach ( $dbselection as $k => &$res )
				{
					if ( !is_array($res) ) 
					{
						$res = array( $res ) ;
					}
					if ( array_key_exists( $n , $res) && $res[$n] !== 0 && !is_array($res[$n]))
					{
						$childTable['ids'][] = $res[$n];
					}
				}
				
				$childTable['ids'] = array_unique($childTable['ids']) ;
				
				$childTables[] = $childTable ;
			}
		}
		
		foreach( $childTables as &$inf )
		{
			
			if ( !empty ($inf['ids']) )
			{
				$primaryKey = $inf['fieldName'] ;
				
				$__ids = $inf['ids'] ;
				$__res2 = array () ;
				
				if ( ake($inf['source'] , $this->__childCache ) )
				{
					$cache = $this->__childCache[$inf['source']] ;
					foreach ( $__ids as $k => $__id )
					{
						if ( !is_array($__id) && ake($__id, $cache) )
						{
							$__res2[] = $cache[$__id] ;
							unset($__ids[$k]) ;
						}
					}
				} else {
					$this->__childCache[$inf['source']] = array () ;
				}
				
				if ( !empty ( $__ids ) )
				{
					// Subfields
					$__f = array () ;
					if ( array_key_exists($inf['source'],$subFields) && is_array ( $subFields[$inf['source']] ) )
					{
						$__f = $subFields[$inf['source']] ;
						
						if ( !in_array($primaryKey, $__f) )
						{
							array_unshift($__f, $primaryKey );
						}
					}
					
					$result = $this->findAll($inf['source'], array($primaryKey => $__ids), 0, $__f);
					
					foreach ( $result as &$res )
					{
						$this->__childCache[$inf['source']][$res[$primaryKey]] = $res;
					}
				} else {
					$result = array () ;
				}
				
				
				$result = array_merge( $result, $__res2 );
				
				if ( $inf['multi'] == true && array_key_exists(0, $result) == false )
				{
					$result = array ($result) ;
				}		
				if ( $recursivity > 1 )
				{
					$result = $this->findAscendants($inf['source'] ,$result, $subFields, $recursivity - 1 );
				}				
				foreach ( $dbselection as &$res )
				{
					if ( array_key_exists( $inf['resName'] , $res ) && $res[$inf['resName']] != '' )
					{
						foreach ( $result as &$res2 )
						{
							if ( !is_array($res2) || !ake($inf['fieldName'],$res2) )
							{
								continue;
							}
							
							if ( $res2[$inf['fieldName']] == $res[$inf['resName']] )
							{
								if( $inf['multi'] == false )
									$res[$inf['resName']] = $res2 ;
								else
									$res[$inf['resName']] = array($res2);
								break;
							} else if (is_string($res[$inf['resName']]) && strpos($res[$inf['resName']],',') !== false)
							{
								$ids = explode(',',$res[$inf['resName']]);
								if ( in_array($res2[$inf['fieldName']], $ids ) )
								{
									$res['___' . $inf['resName']][] = $res2 ;
									$res['___table'] = $inf['source'] ;
								}
							}
								
						}
					}
				}
			}
		}
	
		foreach( $dbselection as &$res )
		{
			foreach ( $res as $name => &$val )
			{
				if ( strpos($name,'___') === 0)
				{
					$table = $res['___table'] ;
					$n = substr($name,3);
					if ( $ordered )
					{
						if (is_string($res[$n]) && strpos($res[$n],',') !== false )
						{
							$ids = explode(',',$res[$n]);
							$arr = array () ;
							$primary = AbstractDB::getPrimary($this->getTableStructure($table)) ;
							foreach($ids as $_id)
							{
								foreach($res[$name] as $k=>$r)
								{
									if ( $r[$primary] == $_id )
									{
										$arr[] = $r ;
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
					unset ($res[$name] );
				}
			}
		}
		
				
		return ( $unique ? $dbselection[0] : $dbselection ) ;
		
	}
	
	function findRelatives ( $table , $dbselection , $subFields = array (), $recursivity = 1 , $ordered = true )
	{
		return $this->findAscendants($table , $this->findChildren($table , $dbselection , $subFields, $recursivity, $ordered) , $subFields, $recursivity, $ordered);
	}
	
	function findChildren ( $table , $dbselection , $subFields = array (), $recursivity = 1 , $ordered = true )
	{
		$this->tableExistsOr403($table);
		
		if ( array_key_exists($table , $this->struct ) == false )
		{
			trigger_error('Table ' . $table . ' does not exists in structure ' . $this->database['host'] ) ;
			return $dbselection ;
		}
		
		if(count($this->struct[$table]) < 2 )
		{
			return $dbselection;
		}
		
		$unique = false ;
		foreach ( $dbselection as $k => &$res )
		{
			if ( is_string($k) )
			{
				$dbselection = array($dbselection) ;
				$unique = true ;
				break;
			}
		}
		
		$cond = array () ;
		
		$childTables = array () ;
		$result = array () ;
		$q = '' ;
	
		foreach( $this->struct[$table] as $fieldName => &$field )
		{
			if ( @$field['behavior'] & AbstractDB::BHR_PICK_IN || @$field['behavior'] & AbstractDB::BHR_PICK_ONE || $field['type'] == AbstractDB::TYPE_PARENT || $field['type'] == AbstractDB::TYPE_CHILD )
			{
				if ( array_key_exists($field['source'],$subFields) && empty($subFields[$field['source']]) )
				{
					continue;
				}
				
				if ( $field['source'] == $table )
				{
					$n = AbstractDB::getPrimary($this->struct[$table]) ;
					$n2 = $field['name'] ;
				} else {
					$n = $field['name'] ;
					$n2 = AbstractDB::getPrimary($this->struct[$field['source']]) ;
				}
				
				$childTable = array () ;
				$childTable['fieldName'] = $n2;
				$childTable['isSameTable'] = ($field['source'] == $table);
				$childTable['resName'] = $n;
				$childTable['source'] = $field['source'];
				$childTable['ids'] = array () ;
				$childTable['multi'] = @$field['behavior'] & AbstractDB::BHR_PICK_IN ;
				
				foreach ( $dbselection as $k => &$res )
				{
					if ( is_array($res) )
					{
						if ( array_key_exists( $n , $res ) && $res[$n] != '' && !is_array($res[$n]) )
						{
							if ( strpos($res[$n],',') !== false )
							{
								$childTable['ids'] = array_merge($childTable['ids'], explode(',',$res[$n]) );
							} else {
								$childTable['ids'][] = $res[$n];
							}
						}
					}
				}
				$childTable['ids'] = array_unique($childTable['ids']) ;
				
				$childTables[] = $childTable ;
			}
		}
		foreach( $childTables as &$inf )
		{
			
			if ( !empty ($inf['ids']) )
			{
				$primaryKey = $inf['fieldName'] ;
				
				$__ids = $inf['ids'] ;
				$__res2 = array () ;
				
				if ( ake($inf['source'] , $this->__childCache ) )
				{
					$cache = $this->__childCache[$inf['source']] ;
					foreach ( $__ids as $k => $__id )
					{
						if ( ake($__id, $cache) )
						{
							$__res2[] = $cache[$__id] ;
							unset($__ids[$k]) ;
						}
					}
				} else {
					$this->__childCache[$inf['source']] = array () ;
				}
				
				
				if ( !empty ( $__ids ) )
				{
					// Subfields
					$__f = array () ;
					if ( array_key_exists($inf['source'],$subFields) && is_array ( $subFields[$inf['source']] ) )
					{
						$__f = $subFields[$inf['source']] ;
					}
					
					$result = $this->findAll($inf['source'], array($primaryKey => $__ids), 0, $__f);
				
					if ( array_key_exists($inf['source'],$subFields) && is_array ( $subFields[$inf['source']] ) )
					{
					
						$__f = $subFields[$inf['source']] ;
					}
					foreach ( $result as &$res )
					{
						if ( ake($inf['resName'], $res ) )
						{
							$this->__childCache[$inf['source']][$res[$inf['resName']]] = $res;
						}
					}
				} else {
					$result = array () ;
				}
				
			
				
				$result = array_merge( $result, $__res2 );
				
				
				if ( $inf['multi'] == true && array_key_exists(0, $result) == false )
				{
					$result = array ($result) ;
				}		
				if ( $recursivity > 1 )
				{
					$result = $this->findChildren($inf['source'] ,$result, $subFields, $recursivity - 1 );
				}				
				foreach ( $dbselection as &$res )
				{
					if ( $inf['isSameTable'] )
					{
						$res[$table] = array () ;
					}
					if ( array_key_exists( $inf['resName'] , $res ) && $res[$inf['resName']] != '' )
					{
						foreach ( $result as &$res2 )
						{
							if ( !is_array($res2) || !ake($inf['fieldName'],$res2) )
							{
								continue;
							}
							
							if ($inf['isSameTable'] == false )
							{
								if ( $res2[$inf['fieldName']] == $res[$inf['resName']] )
								{
									if( $inf['multi'] == false )
										$res[$inf['resName']] = $res2 ;
									else
										$res[$inf['resName']] = array($res2);
									break;
								} else if ( strpos($res[$inf['resName']],',') !== false)
								{
									$ids = explode(',',$res[$inf['resName']]);
									if ( in_array($res2[$inf['fieldName']], $ids ) )
									{
							
										$res['___' . $inf['resName']][] = $res2 ;
										$res['___table'] = $inf['source'] ;
									}
								}
							} else {
								
								if ( $res2[$inf['fieldName']] == $res[$inf['resName']] )
								{
									$res[$table][] = $res2;
								} else if ( strpos($res[$inf['resName']],',') !== false)
								{
									$ids = explode(',',$res[$inf['resName']]);
									if ( in_array($res2[$inf['fieldName']], $ids ) )
									{
										$res[$table][] = $res2 ;
									}
								}
							}
						}
					}
				}
			}
		}
		
		foreach( $dbselection as &$res )
		{
			if ( !is_array($res) )
			{
				continue;
			}
			foreach ( $res as $name => &$val )
			{
				if ( strpos($name,'___') === 0)
				{
					$table = $res['___table'] ;
					$n = substr($name,3);
					if ( $ordered )
					{
						if (is_string($res[$n]) && strpos($res[$n],',') !== false )
						{
							$ids = explode(',',$res[$n]);
							$arr = array () ;
							$primary = AbstractDB::getPrimary($this->getTableStructure($table)) ;
							foreach($ids as $_id)
							{
								foreach($res[$name] as $k=>$r)
								{
									if ( $r[$primary] == $_id )
									{
										$arr[] = $r ;
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
					unset ($res[$name] );
				}
			}
		}
		
		return ( $unique ? $dbselection[0] : $dbselection ) ;
	}
	
	
	function keysToLabel ( $table , $dbselection )
	{
		
		$this->tableExistsOr403($table);
		
		$unique = false ;
		foreach ( $dbselection as $k => &$res )
		{
			if ( is_string($k) )
			{
				$dbselection = array($dbselection) ;
				$unique = true ;
				break;
			}
		}
		
		foreach ( $dbselection as $k => &$res )
		{
			$nres = array () ;
			
			foreach ( $res as $key => &$field )
			{
				// This is a child
				if ( is_array($field) )
				{
					if(ake($key,$this->struct[$table]) )
					{
						$nres[$this->struct[$table][$key]['label']] = $this->keysToLabel($this->struct[$table][$key]['source'],$field) ;
					}
				// This is not a child
				} else {
						if(ake($key,$this->struct[$table]) )
						{
							if( ake('label',$this->struct[$table][$key]) )
								$nres[_($this->struct[$table][$key]['label'])] = $field ;
							else
								$nres[_(ucfirst($key))] = $field ;
						}
				}
			}
			
			$res = $nres ;
		}
		
		return ( $unique ? $dbselection[0] : $dbselection ) ;
	}
	
	function findFirst ( $table , $cond = array () , $fields = array () , $childsRecursivity = 0 )
	{
		$this->tableExistsOr403($table);
		
		$res = $this->findAll ( $table , $cond , 1 , $fields , $childsRecursivity ) ;
		if ( !empty($res) ) 
		{
			return $res[0] ;
		}
		return array() ;
	}
	
	function findRandom ( $table, $fields = array () , $conds = array () )
	{
		$this->tableExistsOr403($table);
		
		$q = '' ;
		
		$q = 'SELECT ' . $this->__selectFields($fields,$table) . ' FROM `' . $this->database['database'] . '`.`' . $table . '` '.$this->__getCond($conds, $table).' ORDER BY RAND() LIMIT 1 ' ;
		$this->_log[] = 'Query(add): ' . $q ;
		$res =  mysql_query( $q , $this->getConnection()) ;
		$result =  $this->__fetchArr($res,$this->struct[$table],$fields);
		@mysql_free_result ( $res ) ;
		if ( !empty($result) ) 
		{
			return $result[0] ;
		}
		return $result ;
		
	}
	
	private function __selectFields ( $fields, $table )
	{
		return ( empty ( $fields ) ?
			'`' . implode('`,`',$this->__getStructureFields($table)) . '`' :
			'`' . implode('`,`',$fields) . '`'
			);
	}
	
	private function __getCond ( $cond, $table = '' )
	{
		if ( empty ( $cond ) )
		{
			return ' WHERE 1' ;
		} else {
			$c = '' ;
			
			
			foreach ( $cond as $fieldname => $val )
			{
				$operator = '=' ;
				
				if ( strlen($c)>0)
				{
					$c.=' AND ' ;
				}
				
				$fieldname = trim($fieldname) ;
				
				$escapeVal = true ;
				if ( ake($fieldname, $this->struct[$table]) && @$this->struct[$table][$fieldname]['behavior'] & AbstractDB::BHR_PICK_IN )
				{
					$val = '\'(^' . $val . '\,)|(\,' . $val . '$)|(\,' . $val . '\,)|(^' . $val . '$)\'';
					$escapeVal = false ;
					$operator = 'REGEXP' ;
				}
				
				// $operatorMatch = '/^(\\x20(' . join(')|(', $this->__sqlOps) .')|\\x20<[>=]?(?![^>]+>)|\\x20[>=!]{1,3}(?!<))/is';
				
				if ( is_string($val) )
				{
					if ( $val === 'IS NULL' )
					{
						$c.= '`' . $fieldname . '` IS NULL' ;
						continue; 
					} else 
					if ( $val === 'IS NOT NULL' )
					{
						$c.= '`' . $fieldname . '` IS NOT NULL' ;
						continue; 
					}
				}
				
				if ( substr_count($fieldname,' ') == 1 )
				{
					list($fieldname , $operator) = explode( ' ' , $fieldname ) ;
				}
				if ( !is_array( $val ) )
				{
					$c .= '`' . $fieldname . '` '. trim($operator) ;
					
					if ( $escapeVal )
					{
						$c .= ' \'' . $val . '\'' ;
					} else {
						$c .= ' ' . $val ;
					}
				} else if ( !empty($val) )
				{
					$c .= '`' . $fieldname . '` IN (\'' .implode('\',\'', $val) . '\')' ;
				}
			}
			$c = ' WHERE ' . $c ;
		}
		return $c . ' ' ;
	}
	
	private function __getLimit ( $table , $limit )
	{
		if ( $limit == 0 )
		{
			return '' ;
		}
		
		if ( is_int ( $limit ) )
		{
			return ' LIMIT 0,' . $limit ;
		}else if ( is_array ( $limit ) )
		{
			$c = count($limit) ;
			if ( $c == 2 && is_int($limit[0]) )
			{
				return ' LIMIT ' . $limit[1] . ', ' . $limit[0] ;
			} else if ( ($c == 2 || $c == 1) && is_string($limit[0]) )
			{
				return ' ORDER BY `' . $this->database['database'] . '`.`' . $table . '`.`' .$limit[0] .'` ' . ($c == 1 || strtoupper($limit[1]) == 'ASC' ? 'ASC' : 'DESC' ) ;
			} else if ( $c == 4 )
			{
				return ' ORDER BY `' . $this->database['database'] . '`.`' . $table . '`.`' .$limit[2] .'` ' . (strtoupper($limit[3]) == 'ASC' ? 'ASC' : 'DESC' ) . ' LIMIT ' . $limit[1] . ', ' . $limit[0] ;
			} 
		}
	}
	
	private function __getStructureFields ( $table )
	{
		$a = array () ;
		foreach( $this->struct[$table] as $name => $field )
		{
			$a[] = $name;
		}
		return $a ;
	}
	
	function edit ( $table , $id , $content = array () )
	{
		$this->tableExistsOr403($table);
		
		$q = '' ;
		
		if ( !empty ( $content ) )
		{
			$entries = array () ;
			
			foreach ( $this->struct[$table] as $name => &$field )
			{
				if ( $name == 'created' )
				{
					continue;
				}
				
				if ( array_key_exists($name, $content) )
				{
					$val = $content[$name] ;
				} else if ( $name != 'updated' && $name != 'modified' ) 
				{
					continue;
				}
				
				$val &= DBHelper::applyBehaviors ( $field , $val , true, $this->getConnection() ) ;
				
				if ( $val != '' )
				{
					$row[$name] = &$val ;
				}
				
				$entries[] = '`' . $name  . '` = \'' .$val. '\'' ;
				
			}
			
			$q = 'UPDATE `' . $this->database['database'] . '`.`' . $table . '` SET ' ;
			$q .= implode ( ', ' , $entries ) ;
			$q .= ' WHERE `' . $this->database['database'] . '`.`' . $table . '`.`'.AbstractDB::getPrimary($this->struct[$table]).'` = ' . (is_numeric($id) ? $id : '\'' .$id . '\'')  . ' LIMIT 1';
			$this->_log[] = 'Query(edit): ' . $q ;
			if ( !$this->_doTemp )
			{
				$res =  mysql_query($q, $this->getConnection()) ;
			} else {
				$this->_queries[] = $q ;
				$res = true ;
			}
			return $res ;
		}
		
		return false ;
	}
	
	function editAll ( $table , $content = array () , $cond = array () )
	{
		$this->tableExistsOr403($table);
		
		$q = '' ;
		
		if ( !empty ( $content ) )
		{
			$entries = array () ;
			
			foreach ( $this->struct[$table] as $name => &$field )
			{
				if ( $name == 'created' )
				{
					continue;
				}
				
				if ( array_key_exists($name, $content) )
				{
					$val = $content[$name] ;
				} else {
					continue;
				}
				
				$val &= DBHelper::applyBehaviors ( $field , $val , true , $this->getConnection() ) ;
				
				if ( $val != '' )
				{
					$row[$name] = &$val ;
				}
				
				if( substr($val,0,1) == '(' )
				{
					$entries[] = '`' . $name . '` = ' . $val ;
				} else {
					$entries[] = '`' . $name . '` = \'' . $val. '\'' ;
				}
				
			}
			
			$q = 'UPDATE `' . $this->database['database'] . '`.`' . $table . '` SET ' ;
			$q .= implode ( ', ' , $entries ) ;
			$q .= $this->__getCond ( $cond , $table) ;
			$this->_log[] = 'Query(edit): ' . $q ;
			if ( !$this->_doTemp )
			{
				$res =  mysql_query($q, $this->getConnection()) ;
			} else {
				$this->_queries[] = $q ;
				$res = true ;
			}
			return $res ;
		}
		
		return false ;
	}
	
	function add ( $table , $content = array () ) 
	{
		$this->tableExistsOr403($table);
		
		$q = '' ;
		
		if ( !empty ( $content ) )
		{
			$q = $this->__getAddQuery($table , $content) ;
			$this->_log[] = 'Query(add): ' . $q ;
			if ( !$this->_doTemp )
			{
				$res =  mysql_query($q, $this->getConnection()) ;
				$this->__lastId = mysql_insert_id ($this->getConnection() ) ;
			} else {
				$this->_queries[] = $q ;
				$res = true ;
			}
			
			return $res ;
		}
		$this->__lastId = -1 ;
		return false ;
	}
	
	function addAll ( $table , $rows = array () ) 
	{
		$this->tableExistsOr403($table);
		
		$q = '' ;
		foreach ( $rows as &$row )
		{
			$q .= $this->__getAddQuery($table , $row , ($q!='')) ;
		}
		
		$this->_log[] = 'Query(addAll): ' . $q ;
	
		if ( !$this->_doTemp )
		{
			$res =  mysql_query($q, $this->getConnection()) ;
			$this->__lastId = ( $res ? mysql_insert_id ($this->getConnection() ) : -1 ); 
			if ( !$res)
			{
				pr( $this->getLog() ) ;
			}
		} else {
			$this->_queries[] = $q ;
				$res = true ;
		}
		
			
			
		return $res ;
	}
	
	function __getAddQuery ( $table , $content , $onlyValues = false )
	{
		$q = '' ;
		
		$keys = array () ;
		$values = array () ;

		foreach ( $this->struct[$table] as $name => &$field )
		{
			if ( array_key_exists($name, $content) )
			{
				$val = $content[$name] ;
			} else {
				$val = '' ;	
			}
			
			$val &= DBHelper::applyBehaviors ( $field , $val , false , $this->getConnection()) ;
			
			$row[$name] = &$val ;
			
			$keys[] = $name ;
			
			$values[] = $val ;
		}
		
		$vals = '\'' .  implode ( '\',\'' , $values ) . '\'' ;
		
		if ( $onlyValues == false )
		{
			$q = 'INSERT INTO `' . $this->database['database'] . '`.`' . $table . '` (`' ;
			$q .= implode ( '`,`' , $keys ) ;
			$q .= '`) VALUES' ;
		} else {
			$q = ',' ;
		}
		
		$q .= ' (' . str_replace ( array ( "\n" ) , array ( '' ) , $vals ) ;
		$q .= ') ' . "\n";
		
		return $q ;
	}
	
	function count ( $table , $cond = array () ) 
	{
		$this->tableExistsOr403($table);
		
		$q = 'SELECT COUNT(*) FROM `' . $this->database['database'] . '`.`' . $table . '` ' . ( !empty ( $cond ) ? $this->__getCond($cond, $table) : '' ) . ' ;' ;
		$this->_log[] = 'Query(count): ' . $q ;
		$res =  mysql_fetch_array ( mysql_query($q, $this->getConnection()) ) ;
		return $res[0] ;
	}
	
	
	
	function lastId ()
	{
		return $this->__lastId ;
	}
	
	function delete ( $table , $id )
	{
		$this->tableExistsOr403($table);
		
		$q = 'DELETE FROM `' . $this->database['database'] . '`.`' . $table . '` WHERE `' . $table . '`.`' . AbstractDB::getPrimary($this->struct[$table]) . '` = ' .(is_numeric($id) ? $id : '\'' .$id . '\'')  . ' ;';
		
		$this->_log[] = 'Query(delete): ' . $q ;
		$res =  mysql_query( $q, $this->getConnection() ) ;
		return $res ;
	}
	
	function deleteAll ( $table , $cond = array () ) 
	{
		$this->tableExistsOr403($table);
		
		$q = 'DELETE FROM `' . $this->database['database'] . '`.`' . $table . '` ' . $this->__getCond($cond, $table). ' ;';
		
		$this->_log[] = 'Query(delete): ' . $q ;
		$res =  mysql_query( $q, $this->__connection ) ;
		return $res ;
	}
	
	function hasAnyTable ()
	{
		$tables = $this->__fetchArr(mysql_query ( 'SHOW TABLES FROM `' . $this->database['database'] . '`' , $this->__connection)) ;
		return !empty($tables);
	}
	
	// OK, let's apply Aenoa DB Structure to MySQL Database
	private function __applyStructure ()
	{
		// There is no table: we create tables
		if ( $this->hasAnyTable () == false )
		{
			$res = true ;
			
			// For each table in structure
			foreach ( $this->struct as $tableName => &$fields )
			{
				// We create the table
				if ( !$this->__createTable($tableName, $fields) )
				{
					$res = false ;
				}
			}
			
			return $res ;
		// That's OK ! There are some tables in database
		} else {
			
			$res = true ;
			
			$q =  'SHOW TABLES FROM `' . $this->database['database'] . '`' ;
			
			$this->_log[] = 'Query(__applyStructure): ' . $q ;
			
			$r = mysql_query ( $q , $this->getConnection() ) ;
			if ( !$r )
			{
				return false ;
			}
			
			$tables = $this->__fetchArr($r) ;
			
			// Let's check in DB tables if there is any table that should be droppable
			foreach ( $tables as $k => $tableName ) 
			{
				// If the table does not exist in the structure we DROP it
				if ( array_key_exists( $tableName , $this->struct ) == false 
					&& (array_key_exists ( 'no_drop' , $this->database ) == false 
					|| $this->database['no_drop'] === true) )
				{
					$q = 'DROP TABLE `' . $this->database['database'] . '`.`' . $tableName . '`' ;
					$this->_log[] = 'Query(__applyStructure): ' . $q ;
					$res = mysql_query ($q, $this->getConnection()) ;
					unset ( $tables[$k] ) ;
				}
			}
			
			// Any 
			foreach ( $this->struct as $tableName => &$structfields ) 
			{
				if ( in_array($tableName, $tables ) == false && $this->__createTable($tableName, $structfields ) == false )
				{
					$res = false ;
					continue;
				}
				
				$q =  'DESCRIBE `' . $this->database['database'] . '`.`' . $tableName . '`'  ;
				$this->_log[] = 'Query(__applyStructure): ' . $q ;
				$fields = mysql_query ($q, $this->getConnection()) ;
				$fields = $this->__fetchArr($fields) ;
				
				foreach ( $structfields as &$structFieldDesc )
				{
					$found = false ;
					foreach( $fields as &$dbFieldDesc )
					{
						if ( $dbFieldDesc[0] == @$structFieldDesc['name'] )
						{
							$found=true;
							break;
						}
					}
					if(!$found)
					{
						$q = 'ALTER TABLE `' . $this->database['database'] . '`.`' . $tableName . '` ADD ' . $this->__getCreateField($structFieldDesc,true);
						$this->_log[] = 'Query(__applyStructure): ' . $q ;
						if ( !$this->query($q, $this->getConnection()) )
						{
							$res = false ;
						}
					}
				}
				
				foreach( $fields as &$dbFieldDesc )
				{
					$found = false ;
					foreach ( $structfields as &$structFieldDesc )
					{
						if ( $dbFieldDesc[0] == $structFieldDesc['name'] )
						{
							$found=true;
							break;
						}
					}
					if(!$found)
					{
						$q = 'ALTER TABLE `' . $this->database['database'] . '`.`' . $tableName . '` DROP `' . $dbFieldDesc[0] . '`';
						$this->_log[] = 'Query(__applyStructure): ' . $q ;
						if ( !$this->query($q, $this->getConnection()) )
						{
							$res = false ;
						}
					}
				}
				
			}
			return $res ;
		}
	}
	
	
	private function __fetchArr ( $ressource , $tableStruct=null, $selectFields=array(), $simpleArray = true )
	{
		if ( $ressource === false )
		{
			return array () ;
		}
		
		$res = array () ;
		while ( true )
		{
			if ( ($v=@mysql_fetch_row ( $ressource )) !== false )
			{
				if ( count ( $v ) == 1 )
				{
					$res[] = $v[0];
				} else {
					$res[] = $v ;
				}
			} else {
				break;
			}
		}
		if ( !is_null($tableStruct) && @$res[0] && is_array ($res[0]) )
		{
			$pres = array();
			foreach ( $res as $k=> &$line_array )
			{
				$pres[$k] = array();
				if ( !empty($selectFields) )
				{
					foreach ( $selectFields as $fieldname )
					{
						$fieldDesc = $tableStruct[$fieldname] ;
						
						$pres[$k][$fieldname] = DBHelper::applyOutputBehaviors ( $fieldDesc , array_shift($line_array) ) ;
					}
				} else {
				
					foreach ( $tableStruct as $name => &$field )
					{
						$pres[$k][$name] = DBHelper::applyOutputBehaviors ( $field , array_shift($line_array)); 
							
					}
				}
			}	
		} else {
			$pres = &$res ;
		}
		@mysql_free_result( $ressource ) ;
		
		return $pres ;
	}
	
	private function __createTable ( $tableName , &$fields )
	{
		$q = 'CREATE TABLE `' . $this->database['database'] . '`.`' . $this->__getTableName ( $tableName ) . '` (' ;
				
		foreach ( $fields as &$field )
		{
			$f = $this->__getCreateField($field, false) ;
			if ( $f !== false )
			{
				$q .= $f . ' , ';
			}
		}
		
		$q = substr($q, 0, strlen( $q ) - 3 ) . ') ENGINE = ' . $this->__getEngine() . $this->__getCharacterSet() . ' ; ' ;
		
		$this->_log[] = 'Query(__createTable): ' . $q ;
		
		return mysql_query ( $q, $this->getConnection() ) ;
	}
	
	private function __getCreateField(&$field, $onAlter)
	{
		$type =  $this->aenoaTypeToMySQLType ( $field['type'] , @$field['values'] , @$field['default'] , @$field['length'], @$field['validation'] ) ;
		if ( is_null($type) )
		{
			return false;
		}
		
		$q = '`' . $field['name'] . '` ' . $type ;
		
		if ( @$field['behavior'] & AbstractDB::BHR_INCREMENT )
		{
			$q .= ' NOT NULL AUTO_INCREMENT' ;
		}
		
		if ( $field['name'] == 'id' )
		{
			if ( $onAlter )
			{
				$q .= ', ADD PRIMARY KEY ( `id` )' ;
			} else {
				$q .= ', PRIMARY KEY ( `id` )' ;
			}
		} else if ( @$field['behavior'] & AbstractDB::BHR_PRIMARY )
		{
			if ( $onAlter )
			{
				$q.= ', ADD PRIMARY KEY ( `'.$field['name'].'` )' ;
			} else {
				$q .= ', PRIMARY KEY ( `'.$field['name'].'` )' ;
			}
		} else if ( @$field['behavior'] & AbstractDB::BHR_PRIMARY )
		{
			if ( $onAlter )
			{
				$q.= ', ADD UNIQUE ( `'.$field['name'].'` )' ;
			} else {
				$q.= ', UNIQUE ( `'.$field['name'].'` )' ;
			}
		}
		return $q ;
	}
	
	private function __getEngine ()
	{
		if ( array_key_exists('table_engine', $this->database )
			&& in_array ( strtoupper($this->database['table_engine']) , array ( 'INNODB' , 'MYISAM', 'MEMORY' , 'MRG_MYISAM' ) ) )
		{
			return strtoupper($this->database['table_engine']) ;
		}
		
		return 'INNODB' ;
	}
	
	private function __getCharacterSet ()
	{
		return ' CHARACTER SET utf8 COLLATE utf8_unicode_ci' ;
	}
	
	private function __getTableName ( $tableName )
	{
		if ( array_key_exists('table_prefix', $this->database ) )
		{
			return $this->database['table_prefix'] . $tableName ;
		}
		
		return $tableName ;
	}
	
	public function aenoaTypeToMySQLType ( $type , $values = null , $default = null, $length = null, $validation = null )
	{
		if ( array_key_exists($type, $this->_types) )
		{
			if ( $type == 'enum' )
			{
				$_type = 'ENUM(\'' . implode ( '\' , \'' , $values ) . '\') NOT NULL DEFAULT \'' . $default . '\'' ;
				
			} else {
				$_type = '' ;
				
				if ( !is_null($validation) && $validation && in_array($type,array('string','text','int','c','float','datetime')) )
				{
					$_type = ' NOT NULL' ;
				}
				
				$_type = $this->_types[$type] . $_type;
				
				if ( is_int($length) && $length > 0 )
				{
					switch ( true )
					{
						case $type == 'string' && $length <= 255 :
							$_type = str_replace ( '(255)' , '('.$length.')', $_type ) ;
							break;
						case $type == 'int' && $length <= 11 :
							$_type = str_replace ( '(11)' , '('.$length.')', $_type ) ;
							break;
					}
				} 
			}
			return $_type ;
		}
		
		return $type ;
	}
	
	private $_types = array(
			'file' => 'VARCHAR(255)',
			'string' => 'VARCHAR(255)',
			'text' => 'LONGTEXT',
			'int' => 'INT(11)',
			'c' => 'INT(11)',
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