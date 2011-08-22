<?php

class TaskInitializer {
	
	static public function initialize ( &$taskManager , $taskName , &$taskView , &$task , &$project = null )
	{
		global $broker;
		
		$task->coreInit ( &$broker ) ;
		// Task manager
		$task->setManager ( &$taskManager ) ;
		// Task manager
		$task->setDevKit ( &$taskManager->devKit ) ;
		// Task view
		$task->setView ( &$taskView ) ;
		
		$task->project = $project ;
		
		$task->taskName = $taskName ;
		
		if ( method_exists ( $task , 'isPlugin' ) && $task->isPlugin () == true )
		{
			$task->initPlugin () ;
		}
	}
	
	
}

?>