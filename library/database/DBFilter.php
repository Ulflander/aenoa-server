<?php

/**
 * Class: DBFilter
 *
 * Helps you define data filters and get corresponding db conditions
 */
class DBFilter extends Object {
	
	private $_identifier ;
	
	private $_filters = array () ;
	
	function __construct( $identifier = null ) {
		
		$this->_identifier = $identifier . '_db_filter' ;
		
		$session = App::getSession() ;
		
		if ( $session->has ( $this->_identifier ) )
		{
			$filters = $session->get($this->_identifier) ;
			
			foreach ( $filters as $type => &$arr )
			{
				if ( !empty ( $arr ) )
				{
					$this->addAll ( $type , $arr ) ;
				}
			}
		}
	}
	
	function addAll ( $type , $identifiers )
	{
		foreach ( $identifiers as $id )
		{
			$this->add ( $type , $id ) ;
		}
	}
	
	
	function add ( $type , $identifier )
	{
		if ( !ake ( $type , $this->_filters ) )
		{
			$this->_filters[$type] = array () ;
		}
		
		if ( !in_array($identifier, $this->_filters[$type] ) )
		{
			$this->_filters[$type][] = $identifier ;
		}
		
		$this->flush () ;
		
		return $this ;
	}
	
	function remove ( $type , $identifier )
	{
		if ( !ake ( $type , $this->_filters ) )
		{
			return $this ;
		}
		
		if (in_array($identifier, $this->_filters[$type]) )
		{
			unset ( $this->_filters[$type][array_search($identifier, $this->_filters[$type] )] ) ;
			
			if ( empty ( $this->_filters[$type] ) )
			{
				unset ( $this->_filters[$type] ) ;
			}
			
			$this->flush () ;
		}
		
		return $this ;
	}
	
	function reset ()
	{
		$this->_filters = array () ;
		
		$this->flush() ;
		
		return $this ;
	}
	
	
	function getAll ()
	{
		return $this->_filters ;
	}
	
	
	function flush ()
	{
		App::getSession()->set($this->_identifier, $this->getAll() ) ;
	}
	
}

?>
