<?php


class Search extends Task {
    
	private $query ;
	
	function getOptions ()
	{
		if ( !$this->project || $this->project->name == '' )
		{
			global $broker ;
			$query = $broker->sanitizer->get ( 'POST' , 'query' ) ;
			
			$opt = new Field () ;
			$opt->label = 'Search query' ;
			$opt->required = true ;
			$opt->name = 'query' ;
			$opt->type = 'input' ;
			
			$opt->value = $query ;
			
			return array ( $opt ) ;
		} else {
			$this->query = $this->project->name ;
		}
	}
	
	
	function onSetParams ( $options = array () )
	{
		if ( $this->hasParam ( 'query' ) && $this->params['query'] != '' )
		{
			$this->query = $this->params['query'] ;
			
		}
		return true ;
	}
	
	// Let's process search
	function process ()
	{
		$this->view->avoidEndMessage = true ;
		
		$this->project = new DevKitProject ( $this->query ) ;
		
		$this->view->setProject ( $this->project , $this->taskName ) ;
		
		if ( substr_count($this->query, '/' ) > 0 )
		{
			$arr = explode ( '/' , $this->query );
			$this->query = array_shift ( $arr ) ;
			foreach ( $arr as $k => $v )
			{
				
			}
			$next = implode ( '/' , $arr ) ;
		}
		
		// Check if query is direct command task on a project
		if ( substr_count($this->query, ':' ) > 0 )
		{
			$arr = explode ( ':' , str_replace ( ' ' , '' , $this->query ) ) ;
			$task = $arr[0] ;
			$project = $arr[1] ;
			$projObj = new DevKitProject ( $arr[1] ) ;
			if ( $projObj->valid == false )
			{
				$query = $task ;
			}
		} else {
			
			$task =str_replace ( ' ' , '' , $this->query ) ;
		}
		
		// If query is really a task, then directly redirects to task
		if ( $task != '' && $this->devKit->isTask ( $task , false ) )
		{
			$url = $this->devKit->getTrueTaskName ( $task ) ;
			
			if ( isset ( $project ) )
			{
				$url.= ':'.$project ;
			}
			
			if ( isset ( $next ) ) 
			{
				$url .= '/' . $next ;
			}
			
			$this->manager->redirect ( url() . $url , 0 ) ;
		} else 
		// If query is really a project, then directly redirects to Manageproject task
		if ( $task != '' && $this->devKit->isProject ( $task ) )
		{
			$this->manager->redirect ( url() . 'ManageProject:' . $task , 0 ) ;
		}
		
		$this->query = strtolower ( trim ( $this->query ) );
		
		$results = array () ;
		$results['approx'] = null ;
		$results['tasks'] = array () ;
		$results['projects'] = array () ;

		$tasks = $this->devKit->getAllTasks () ;
		$projects = $this->devKit->getAllProjects () ;
		
		$tquery = $this->query ;
		
		if ( strlen ( $tquery ) > 2 )
		{
			if ( substr_count($tquery, ' ') > 0 )
			{
				$tquery = str_replace ( ' ' , '' , $tquery ) ;
			}
			
			$best = 100 ;
			$approx = null ;
			
			$keywords = $this->devKit->getAllTaskKeywords () ;
			
			$__result = SearchApproxTerm::search ( $tquery , $keywords ) ;
			
			if ( $best > $__result['result'] )
			{
				$best = $__result['result'] ;	
				$approx = $__result['term'] ;
				
				if ( $__result['result'] < 1.7 )
				{
					$results['approx'] = 'keyword ' . $this->getResultLink($__result['term'] , 'Search:'. $__result['term']) ;
				}
			}
			
			foreach ( $tasks as $__task => $__taskPath )
			{
				$__result = SearchApproxTerm::compare ( $__task , $tquery ) ;
				
				
				if ( $best > $__result )
				{
					$best = $__result ;	
					$approx = $__task ;
					if ( $__result < 1.7 )
					{
						$results['approx'] = 'task ' . $this->getResultLink($__task , $__task) . ' (you will be redirected to task)' ;
					}
				}
			}
			
			foreach ( $projects as $__project )
			{
				$__result = SearchApproxTerm::compare ( $__project->name , $tquery ) ;
				
				
				if ( $best > $__result )
				{
					$best = $__result ;	
					$approx = $__project->name ;
					if ( $__result < 1.7 )
					{
						$results['approx'] = 'project '. $this->getResultLink($__project->name , 'ManageProject:'.$__project->name) . ' (you will be redirected to project)' ;
					}
				}
			}
			
			$types = array ( DevKitProjectType::AENOA , DevKitProjectType::WORDPRESS ) ;
			foreach ( $types as $__type )
			{
				$__result = SearchApproxTerm::compare ( $__type , $tquery ) ;
				
				
				if ( $best > $__result )
				{
					$best = $__result ;	
					$approx = $__type ;
					
					if ( $__result < 1.7 )
					{
						$results['approx'] = 'type '. $this->getResultLink($__type , 'Search:' .$__type) ;
					}
				}
			}
			
			$this->view->setStatusBar ( 'Best approaching term: <em>' . $approx . '</em> with a result of <em>' . $best . '</em>') ;
			
			if ( $best == 0 )
			{
				$this->query = strtolower ( $approx ) ;
				$results['approx'] = null ;
				
			}
		}
		
		
		$__queries = explode ( ' ' , $this->query ) ;
		$__queries = array_unique( $__queries ) ;
		
		foreach ( $tasks as $__task => $__taskPath )
		{
			foreach ( $__queries as $__query )
			{
				if ( !$__query )
				{
					continue;
				} 
				if ( substr_count(strtolower($__task), $__query ) > 0 || $__query == '*')
				{
					$results['tasks'][] = $this->getResultLink($__task, $__task , $__query) ;
					break;
				}
			}
		}
		
		foreach ( $projects as $__project )
		{
			foreach ( $__queries as $__query )
			{
				if ( !$__query )
				{
					continue;
				} 
				if ( substr_count($__project->name, $__query ) > 0 || strtolower($__project->type) == $__query || $__query == '*' )
				{
					$str = $this->getResultLink($__project->name , 'ManageProject:'.$__project->name , $__query) ;
					if ( $__project->type != DevKitProjectType::UNKNOWN )
					{
						$str .= ' / ' . $__project->type ;
					}
					$results['projects'][] = $str ;
					break;
				}
			}
		}
		
		$this->view->template->set ( 'results' , $results ) ;
		$this->view->template->set ( 'query' , implode ( ' ' , $__queries ) ) ;
		$this->view->template->append ( 'tasks' , 'search_results' ) ;
		
		
		$this->view->setStatusBar ( count ( $results['tasks']) + count ( $results['projects']).' result(s) for query') ;
		
		$this->view->setStatusBar ( '<a href="http://www.google.com/search?q='.$this->query.'" target="_blank">Search for '.$this->query.' on Google</a>' , 'google') ;
		
	}
	
	private function getResultLink ( $title , $URL , $query = null )
	{
		if ( !is_null ( $query ) )
		{
			$title = str_replace(array($query,ucfirst($query)), array('<u>' .$query. '</u>','<u>' .ucfirst($query). '</u>'), $title);
		}
		return '<a href="' . url() . $URL . '">'.$title.'</a>' ;
	}
	
}













?>