<?php






/**
 * JSONDatabase is helpfull for little databases,
 * or to make a cache of a part of big MySQL dbs.
 */
class JSONDBEngine extends AbstractDB {
	
	
	private $jfile ;
	
	private $__edited = false ;
	

	/////////////////////////////////////////////////////
	// AbstractDB implementation
	
	
	function isUsable () 
	{
		return (is_null($this->jfile) == false && $this->jfile->exists () ) ;
	}
	
	/**
	 * For MySQLEngine, the database must be an array containing theses values:
	 * 'host' => 'db_host',
	 * 'login' => 'db_login',
	 * 'password' => 'db_pass',
	 * 'database' => 'db' ,
	 * 
	 * @param array $database
	 * @return 
	 */
	function setSource ( $database , $create = false ) 
	{
		if ( $database == $this->database && $this->isUsable() )
		{
			return true ;
		}
		
		$this->close () ;
		
		if ( $this->sourceExists ( $database ) == true )
		{
			$this->database = $database ;
		} else if ( $create == true )
		{
			$this->createSource( $database ) ;
		}
		
		return $this->open () ;
	}
	
	function open ()
	{
		$this->jfile = new JSONFile ( $this->database , false ) ;
		
		$exists = $this->jfile->exists () ;
		
		$_data = &$this->jfile->read () ;
		
		if ( $_data !== false )
		{
			$validation = $this->__validateAndImport ( &$_data ) ;
		}
		
		return $exists && $validation ;
	}
	
	function test ( $host , $login , $password )
	{
		$f = new JSONFile ( $host , false ) ;
		$exists = $f->exists () ;
		$f->close () ;
		return $exists ;
	}
	
	function close ()
	{
		$res = false ;
		
		if ( !is_null ( $this->jfile ) )
		{
			if ( $this->__edited == true )
			{
				if ( $this->__write() == true )
				{
					$res = true ;
				}
			} else {
				$res = true ;
			}
			if ( $this->jfile->close () == false )
			{
				$res = false ;
			}
		}
		
		return $res ;
	}
	
	function createSource ( $database ) 
	{
		if ( $this->sourceExists ( $database ) == false )
		{
			$jfile = new JSONFile ( $database , true ) ;
		
			return $this->setSource ( $database ) ;
		}
		
		return false ;
	}
	
	function sourceExists ( $database )
	{
		$jfile = new JSONFile ( $database , false ) ;
		
		return $jfile->exists () ;
	}
	
	
	function setStructure ( &$structure = array () , $create = false ) 
	{
		$res = true ;
		$tstruct = array () ;
		
		foreach ( $structure as $table => &$struct )
		{
			$tstruct[$table] = array () ;
			
			if ( array_key_exists($table, $this->tables) == false )
			{
				$this->tables[$table] = 0 ;
			}
			
			foreach ( $struct as &$field ) 
			{
				if ( DBHelper::validateField ( $field ) )
				{
					$tstruct[$table][] = $field ;
				} else {
					$res = false ;
				}
			}
		}
		
		$this->__edited = true ;
		
		$this->structure = &$tstruct ;
		return $res ;
	}
	
	function hasStructureCapability () 
	{
		return true ;
	}
	
	function query ( $table , $query ) 
	{
		return false ;
	}
	
	function hasQueryCapability ()
	{
		return false ;
	}
	
	function tableExists ( $tableName ) 
	{
		return ( array_key_exists($tableName, $this->tables ) && count ( $this->structure[$tableName] ) > 0 ) ;
	}
	
	function find ( $table , $id , $fields = array () )
	{
		if ( $this->tableExists( $table ) )
		{
			$arr = &$this->selectFieldsInRow ( $this->data[$table][$id] , $fields );
			
			return $arr ;
		}
		
		return false ;
	}
	
	function findAll ( $table = null , $cond = array () , $limit = 0 , $fields = array ()  )
	{
		if ( $table == null )
		{
			foreach ( $this->data as $_tableName => $_table ) 
			{
				$table = $_tableName;
				break;
			}
		}
			
		if ( $this->tableExists( $table ) )
		{
			
			$results = array () ;
		
			foreach ( $this->data[$table] as &$row )
			{
				$continue = !$this->__evalue ( $cond , $row ) ;
				
				if ( $continue || empty ( $row ) )
				{
					continue;
				}
				
				$results[] = &$this->selectFieldsInRow ( $row , $fields ) ;
				
				if ( $limit > 0 && count ( $results ) == $limit )
				{
					break ;
				}
			}
			
			return $results ;
		}
		
		return false ;
	}
	
	function findFirst ( $table , $cond = array () , $fields = array ()  )
	{
		$res = $this->findAll($table , $cond , 1 , $fields ) ;
		
		if ( empty ( $res ) )
		{
			return false ;
		}
		
		
		return array_shift($res) ;
	}
	
	function edit ( $table , $id , $content = array () )
	{
		return $this->editAll ( $table , $content , array ( 'id' => $id ) ) ;
	}
	
	function editAll ( $table , $content = array () , $cond = array () )
	{
		if ( $this->tableExists($table) && !empty ( $content ) )
		{
			foreach ( $this->data[$table] as &$row )
			{
				$continue = !$this->__evalue ( $cond , $row ) ;
				
				if ( $continue )
				{
					continue;
				}
				
				$newRow = array () ;

				DBHelper::applyBehaviors ( &$this->structure[$table] , &$content , true ) ;
				
				foreach ( $this->structure[$table] as $field )
				{
					if ( array_key_exists($field['name'], $content) )
					{
						$row[$field['name']] = &$content[$field['name']] ;
					}
				}
				
				$row = &$newRow ;
			}
			
			$this->__edited = true ;
		
			return true ;
		}
		
		return false ;
	}
	
	function add ( $table , &$content = array () ) 
	{
		if ( $this->tableExists($table) && !empty ( $content ) )
		{
			$row = array () ;

			foreach ( $this->structure[$table] as &$field )
			{
				if (array_key_exists('behavior' , $field) && $field['behavior'] == self::BHR_INCREMENT )
				{
					$this->__lastId = $row[$field['name']] = $this->newTableId ( $table ) ;
					continue;
				}
				
				if ( array_key_exists($field['name'], $content) )
				{
					$val = $content[$field['name']] ;
				} else {
					$val = '' ;	
				}
				
				$val &= DBHelper::applyBehaviors ( &$field , &$val , false ) ;
				
				if ( $val != '' )
				{
					$row[$field['name']] = &$val ;
				}
			}
			
			$this->data[$table][$row['id']] = &$row ;
			
			$this->__edited = true ;
			
			return true ;
		}
		
		return false ;
	}
	
	function addAll ( $table , &$rows = array () ) 
	{
		$res = true ;
		foreach( $rows as &$row )
		{
			if ( $this->add ( $table , $row ) == false )
			{
				$res = false ;
			}
		}
		
		return $res ;
	}
	
	function count ( $table ) 
	{
		if ( $this->tableExists( $table ) ) 
		{
			return count ( $this->data[$table] ) ;
		}
		
		return false ;
	}
	
	
	function newTableId ( $table ) 
	{
		if ( $this->tableExists($table))
		{
			$this->tables[$table] ++ ;
			return $this->tables[$table] ;
		}
		
		return false ;
	}
	
	function lastTableId ( $table )
	{
		if ( $this->tableExists($table))
		{
			return $this->tables[$table] ;
		}
		
		return false ;
	}
	
	function lastId ()
	{
		return $this->__lastId ;
	}
	
	function delete ( $table , $id )
	{
		return $this->deleteAll ( $table , array ( 'id' => $id ) ) ;
	}
	
	function deleteAll ( $table , $cond = array () ) 
	{
		if ( $this->tableExists($table))
		{
			$result = false ;
			
			foreach ( $this->data[$table] as $k => $row )
			{
				if ( !$this->__evalue ( $cond , $row ) )
				{
					continue;
				}
				
				$result = true ;
				
				unset ( $this->data[$table][$k] ) ;
			}
			
			$this->__edited = true ;
		
			return $result ;
		}
		
		return false ;
	}
	
	
	
	
	/////////////////////////////////////////////////////
	// JSONDBEngine special methods
	
	/**
	 * Evaluate conditions based on data in row
	 * @access private
	 */
	private function __evalue ( &$cond , &$row ) 
	{
		foreach ( $cond as $k => &$v )
		{
			$_v = trim ( $v ) ;
			if ( ( is_array ( $row ) || is_object( $row ) ) && array_key_exists( $k, $row ) )
			{
				if ( substr_count($_v, '!= ' ) )
				{
					$_v = substr ($_v, 3) ;
					if ( $_v == 'NOW()' ) { $_v = DBHelper::getDatetime () ; } ;
					if ( $row[$k] == $_v )
					{
						return false;
					}
				} else if ( substr_count($_v, '< ' ) )
				{
					$_v = substr ($_v, 2) ;
					if ( $_v == 'NOW()' ) { $_v = DBHelper::getDatetime () ; } ;
					if ( intval ( $row[$k] ) >= intval ( $_v ) )
					{
						return false;
					}
				
				} else if ( substr_count($_v, '> ' ) )
				{
					$_v = substr ($_v, 2) ;
					if ( $_v == 'NOW()' ) { $_v = DBHelper::getDatetime () ; } ;
					if ( intval ( $row[$k] ) <= intval ( $_v ) )
					{
						return false;
					}
				} else if ( substr_count($_v, '<= ' ) )
				{
					$_v = substr ($_v, 3) ;
					if ( $_v == 'NOW()' ) { $_v = DBHelper::getDatetime () ; } ;
					if ( intval ( $row[$k] ) > intval ( $_v ) )
					{
						return false;
					}
				} else if ( substr_count($_v, '>= ' ) )
				{
					$_v = substr ($_v, 3) ;
					if ( $_v == 'NOW()' ) { $_v = DBHelper::getDatetime () ; } ;
					if ( intval ( $row[$k] ) < intval ( $_v ) )
					{
						return false;
					}
				} else if ( $row[$k] !== $v )
				{				
					return false;
				}
			}
		}
		
		return true ;
	}
	
	
	private function __write ()
	{
		$arr = array (
			'__struct' => &$this->structure ,
			'__tables' => array ()
		) ;
		foreach ( $this->tables as $tableName => &$tableLastId )
		{
			if ( array_key_exists($tableName, $this->data) )
			{
				$arr['__tables'][$tableName] = array () ;
				$arr['__tables'][$tableName]['fields'] = array () ;
				$arr['__tables'][$tableName]['lastId'] = $tableLastId ;
				
				foreach ( $this->data[$tableName] as &$row )
				{
					$arr['__tables'][$tableName]['fields'][$row['id']] = &$row ;
				}
			}
		}
		
		return $this->jfile->write ( &$arr ) ;
	}
	
	
	private function __validateAndImport ( &$_data ) 
	{
		if ( !is_array ( $_data ) )
		{
			$_data = array () ;
		}
		
		if ( array_key_exists('__struct', $_data) == false || array_key_exists('__tables', $_data) == false )
		{
			$_data = array ( '__struct' => array () , '__tables' => array () ) ;
		}
		
		$this->structure = &$_data['__struct'] ;
		$this->data = array () ;
		$this->tables = array () ;
		$__data = $_data['__tables'] ;
		
		foreach( $this->structure as $tableName => $content )
		{
			if ( !array_key_exists ( $tableName , $__data ) )
			{
				$this->data[$tableName] = array () ;
				
				$this->tables[$tableName] = 0 ;
			} else {
				$this->data[$tableName] = &$__data[$tableName]['fields'] ;
				
				$this->tables[$tableName] = &$__data[$tableName]['lastId'] ;
			}
		}
		
		return true ;
	}
}

?>