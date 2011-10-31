<?php


class ExploreTasks extends Task {
	
	
	function init ()
	{
		
		$this->view->setMenuItem ( 'TaskCenter' , url().'TaskCenter' , 'tasks' ) ;
		
	}
	
	function process ()
	{
		$tasks = array () ;
		
		foreach ( $this->manager->paths as $path )
		{
	
			$files = $this->futil->getFilesList ( $path , false ) ;
			
			if ( $files )
			{
				foreach ( $files as $file )
				{
					if ( !empty ( $file ) && $file['type'] != 'dir' && substr($file['name'], 0 , 1) != '.' )
					{
						$tasks[] = str_replace ( DK_DEV_KIT , '' ,$path ) . ' <a href="' . url() . 'dev/'. $file['filename'] . '">' . $file['filename'] . '</a>' ;
					}
				}
			}
			
		}
		
		$this->view->setStatusBar ( count ( $tasks ) . ' tasks' ) ;
		$this->view->setStatus ( $tasks ) ;
	}
}
?>