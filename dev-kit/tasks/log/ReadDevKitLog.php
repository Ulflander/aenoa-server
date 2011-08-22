<?php


class ReadDevKitLog extends Task {
    
	function process ()
	{
		$logsContent = '' ;
		$f = new File ( DK_DEV_KIT . 'tmp' . DS . '.aenoalog' , false ) ;
		
		if ( $f->exists () ) {
			
			$log = $f->read () ;
			
			$log = str_replace ( "\n" . '- Task done' , '' , $log ) ;
			
			$arr = explode ( "\n" , $log ) ;
			
			$arr = array_reverse ( $arr ) ;
			
			foreach ( $arr as &$v )
			{
				$__v = explode  ( ' ' , $v ) ;
				if ( count ( $__v ) > 1 )
				{
					$__v[1] = '<em>' . $__v[0] . ' ' . $__v[1] . '</em>' ;
					array_shift( $__v ) ;
				}
				$v = implode ( ' ' , $__v ) ;
			}
			
			array_pop ( $arr ) ;
			
			$this->view->setStatus ( '<br/><br/>' . implode ( '<br/><br/>' , $arr ), true) ;
		} else {
			$this->view->setWarning ( 'Log is empty' ) ;
		}
		
		$this->view->avoidEndMessage = true ;
		$this->view->setMenuItem ( 'Delete log' , url() . 'DeleteDevKitLog' , 'delete' ) ;
		$this->view->setMenuItem ( 'Task center' , url() . 'TaskCenter' ) ;
		
	}
	
}
?>