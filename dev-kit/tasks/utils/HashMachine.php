<?php


class HashMachine extends Task {
	
	
	function getOptions ()
	{
		$opt = new Field () ;
		$opt->name = 'password' ;
		$opt->type = 'input' ;
		$opt->label = 'Clear string' ;
		$opt->required = true ;
		
		$opt2 = new Field () ;
		$opt2->name = 'method' ;
		$opt2->type = 'select' ;
		$opt2->required = true ;
		$opt2->label = 'Hash method' ;
		$opt2->values = array ( 'sha1' => 'sha1' , 'md5' => 'md5' ) ;
		
		
		return array ( &$opt , &$opt2 ) ;
	}
	
	
	function process ()
	{
		$this->view->setStatus ( $this->params['password'] . ' hashed to ' . $this->params['method'] , true ) ;
		
		switch ( $this->params['method'] ) 
		{
			case 'sha1':
				$this->view->setStatus ( sha1 ( $this->params['password'] ) , true ) ;
				break;
			case 'md5':
				$this->view->setStatus ( md5 ( $this->params['password'] ) , true ) ;
				break;
			default:
				$this->view->setError ( 'Hash method unknown' , true ) ;
				break;
		}
		
		return false ;
	}
}
?>