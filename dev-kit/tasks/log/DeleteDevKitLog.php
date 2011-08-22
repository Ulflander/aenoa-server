<?php


class DeleteDevKitLog extends Task {
    
	function getOptions ()
	{
		$opt = new Field () ;
		$opt->setupConfirm ( 'Confirm empty dev-kit log') ;
		
		return array ( $opt ) ;
	}
	
	function process ()
	{
		$logsContent = '' ;
		$f = new File ( DK_DEV_KIT . 'tmp' . DS . '.aenoalog' , false ) ;
		
		if ( $f->exists () && $f->write ( '' ) ) {
			
			$this->view->setSuccess ( 'Log is now empty' ) ;
		} else {
			$this->view->setError ( 'Log does not exists' ) ;
		}
		
		$this->view->setMenuItem ( 'See log' , url() . 'ReadDevKitLog' ) ;
	}
	
}
?>