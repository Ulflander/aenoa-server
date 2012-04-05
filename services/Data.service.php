<?php

class DataService extends Service {
	
	
	public function autoComplete ( $query , $source , $conditions = '' , $results = 10 )
	{
		$res = array () ;
		@list($dbid, $table, $field ) = explode('/', $source) ;
		
		if ( App::hasDatabase($dbid) )
		{
			$db = App::getDatabase($dbid) ;
			
			$struct = $db->getStructure();
			
			$conds = array () ;
			
			$limit = array () ;
			
			
			
			if ( $conditions != '' )
			{
				$_c = explode(';',$conditions) ;
				
				foreach ( $_c as $c )
				{
					$c = explode('=',$c) ;
					
					if ( $c[0] == 'orderby')
					{
						$order = explode('/' , $c[1]) ;
						$limit = array ( $results, 0, $order[0], $order[1]) ;
					} else if ( $c[1] != '' )
					{
						$conds[$c[0]] =  $c[1] ;
					}
				}
			}
			
			$res = $db->findAscendants($table,$db->findChildren($table,$db->findAll($table,array_merge(array($field . ' LIKE' => $query . '%'), $conds), (!empty($limit) ? $limit : $results ) ),array(),2),array(),2);
		}
		$this->protocol->addData ( 'results' , $res ) ;
	}
	
	public function getAllAndChilds ( $source , $conditions = '' , $keysAsLabel = false , $max = 300 )
	{
		$res = array () ;
		@list($dbid, $table ) = explode('/', $source) ;
		
		if ( App::hasDatabase($dbid) )
		{
			$db = App::getDatabase($dbid) ;
			
			$struct = $db->getStructure();
			
			$conds = array () ;
			$limit = array () ;
			
			if ( $conditions != '' )
			{
				$_c = explode(';',$conditions) ;
				
				foreach ( $_c as $c )
				{
					$c = explode('=',$c) ;
					
					if ( $c[0] == 'orderby')
					{
						$order = explode('/' , $c[1]) ;
						$limit = array ( $max, 0, $order[0], $order[1] ) ;
					} else if ( $c[1] != '' )
					{
						$conds[$c[0]] =  $c[1] ;
					}
				}
			}
			
			$res = $db->findAll($table,$conds, $limit);
			$res = $db->findAscendants($table,$db->findChildren($table,$res)) ;
			
			if($keysAsLabel === true )
			{
				$res = $db->keysToLabel($table,$res) ;
			}
			
			$this->protocol->addData ( 'results' , $res ) ;
			
		} else {
			$this->protocol->setSuccess ( false ) ;
		}
	}
	
	public function getOneAndChilds ( $source , $conditions = ''  , $keysAsLabel = false)
	{
		$res = array () ;
		
		@list($dbid, $table, $id ) = explode('/', $source) ;
		
		if ( App::hasDatabase($dbid) )
		{
			$db = App::getDatabase($dbid) ;
			
			$struct = $db->getStructure();
			
			$conds = array () ;
			
			if ( $conditions != '' )
			{
				$_c = explode(';',$conditions) ;
				
				foreach ( $_c as $c )
				{
					$c = explode('=',$c) ;
					$conds[$c[0]] =  $c[1] ;
				}
			}
			
			$res = $db->find($table,$id);
			$res = $db->findAscendants($table,$db->findChildren($table,$res)) ;
		
			if($keysAsLabel == true )
			{
				$res = $db->keysToLabel($table,$res) ;
			}
			
			$this->protocol->addData ( 'results' , $res ) ;
			
		} else {
			$this->protocol->setSuccess ( false ) ;
		}
	}
}


?>