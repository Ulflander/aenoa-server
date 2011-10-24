<?php


class TaskView {
	
	public $template ;
	
	public $avoidMessages = false ;
	
	public $avoidEndMessage = false ;
	
	function __construct ( $title , $isHome = false )
	{
		$this->template = new Template ( 'html/Global.thtml' , $title ) ;
		$this->template->useLayout = true ;
		$this->template->layoutName = 'layout-backend' ;
		$this->template->set ( 'is_home' , $isHome ) ;
		$this->template->set ( 'status_message' , '' ) ;
		$this->template->set ( 'menu' , array() ) ;
		
		$this->setTitle ( $title ) ;
		
		if ( $isHome == false )
		{
			$this->setMenuItem ( 'Return to home' , url() , 'home' ) ;
		}
		
		$this->template->set ( 'project_class' , 'light-block' ) ;
		
		$this->template->set ( 'is_home' , $isHome ) ;
	}
	
	function setTask ( $task )
	{
		$this->template->set ( 'task' , $task ) ;
	}
	
	function setProject ( $project , $currentTask )
	{
		if ( $project->valid == true && $currentTask != 'ManageProject' && $this->template->get('is_home') == false )
		{
			$this->setMenuItem ( 'Return to project' , url() . 'ManageProject:' . $project->name , 'run' ) ;
		}
		
		
		$this->template->set ( 'project_class' , $project->class ) ;
		
		$this->template->set ( 'project_name' , $project->name ) ;
		
		$this->template->set ( 'project' , $project ) ;
	}
	
	function setStatusBar ( $message , $class = '' )
	{
		if ( $this->template->get ( 'status_message' ) == '' )
		{
			$this->template->set ( 'status_message' , '<span class="first '.$class.'">' . $message . '</span>' ) ;
		} else {
			$this->template->set ( 'status_message' , $this->template->get ( 'status_message' ) .'<span class="'.$class.'">' . $message . '</span>' ) ;
		}
	}
	
	function removeProjectMenuItem ()
	{
		$items = $this->template->get ( 'menu' ) ;
		$newItems = array () ;
		foreach( $items as &$item )
		{
			if ( $item['title'] != 'Return to project' )
			{
				array_push( $newItems , $item ) ;
			}
		}
		
		$this->template->set ( 'menu' , $newItems ) ;
	}
	
	function setMenuItem ( $title , $URL , $class = '' , $target = null )
	{
		$item = array () ;
		$item['title'] = $title ;
		$item['URL'] = $URL ;
		$item['class'] = $class ;
		$item['target'] = $target ;
		
		$this->template->set ( 'menu' , array_merge ( $this->template->get ( 'menu' ) , array( $item ) ) ) ;
	}
	
	function setTitle ( $title = '' ) 
	{
		$this->template->title = array_shift ( explode ( ':' , $title ) ) ;
		$this->template->set ( 'title_class' , array_shift ( explode ( ':' , $title ) ) ) ;
	}
	
	function setOptions ( $options )
	{
		$this->template->set ( 'controls' , $options ) ;
		$this->template->append ( 'tasks' , 'form_controls' ) ;
	}
	
	function setStatus ( $message , $substatus = false ) 
	{
		if ( $this->avoidMessages == false )
		{
			$this->template->set ( 'substatus' , $substatus ) ;
			$this->template->set ( 'message' , $message ) ;
			$this->template->append ( 'tasks' , 'task_status' ) ;
		}
	}


	function setWarning ( $message , $substatus = false )
	{
		if ( $this->avoidMessages == false )
		{
			$this->template->set ( 'substatus' , $substatus ) ;
			$this->template->set ( 'message' , $message ) ;
			$this->template->append ( 'tasks' , 'task_warning' ) ;
		}
	}
	
	function setProgressBar ( $message , $id , $initialPercent = 0 )
	{
		if ( $this->avoidMessages == false )
		{
			$this->template->set ( 'id' , $id ) ;
			$this->template->set ( 'message' , $message ) ;
			$this->template->set ( 'percent' , $initialPercent ) ;
			$this->template->append ( 'tasks' , 'task_progress' ) ;
		}
	}
	
	function updateProgressBar ( $id , $percent , $status = '' )
	{
		if ( $this->avoidMessages == false )
		{
			
			if ( $percent < 0 )
			{
				$this->template->appendScript ( 'tasks' , 'document.getElementById("'.$id.'").style.width = "100%" ;' ) ;
				$this->template->appendScript ('tasks' , ' document.getElementById("'.$id.'").innerHTML = "" ;') ;
				$this->template->appendScript ( 'tasks' , 'document.getElementById("status-'.$id.'").innerHTML = "'. $status .'" ;' ) ;
			} else if ( $percent < 100 )
			{
				$this->template->appendScript ( 'tasks' , 'document.getElementById("'.$id.'").style.width = "'.ceil ( $percent ).'%" ;' ) ;
				$this->template->appendScript ( 'tasks' , 'document.getElementById("status-'.$id.'").innerHTML = "'. $status .'" ;' ) ;
				$this->template->appendScript ('tasks' , ' document.getElementById("'.$id.'").innerHTML = "'. ceil ( $percent ) . '%" ;') ;
			} else {
				$this->template->appendScript ('tasks' , ' document.getElementById("container-'.$id.'").style.display = "none" ;') ;
				$this->template->appendScript ( 'tasks' , 'document.getElementById("status-'.$id.'").style.display = "none" ;' ) ;
			}
		}
	}
	
	function setSuccess ( $message , $substatus = false ) 
	{
		if ( $this->avoidMessages == false )
		{
			$this->template->set ( 'substatus' , $substatus ) ;
			$this->template->set ( 'message' , $message ) ;
			$this->template->append ( 'tasks' , 'task_done' ) ;
		}
	}
	
	function setError ( $message , $substatus = false )
	{
		if ( $this->avoidMessages == false )
		{
			$this->template->set ( 'substatus' , $substatus ) ;
			$this->template->set ( 'error' , $message ) ;
			$this->template->append ( 'tasks' , 'task_failure' ) ;
		}
	}
	
	
	function redirect ( $to , $delay = 5000 )
	{
		$this->template->appendScript ( 'tasks' , 'redirect ( "Redirection:" , "'.$to.'" , '.$delay.'  );' ) ;
	}
	
	function endTask ( $success = true )
	{
		if ( $this->avoidMessages == false && $this->avoidEndMessage == false )
		{
			$this->template->set ( 'task_success' , $success ) ;
			$this->template->append ( 'tasks' , 'task_end' ) ;
		}
	}
	
	function appendContent ( $htmlContent , $container = 'tasks' )
	{
		$this->template->appendContent ( $container , $htmlContent ) ;
	}
	
	
	function render ()
	{
		
		$this->template->render () ;
		
	}
}
?>