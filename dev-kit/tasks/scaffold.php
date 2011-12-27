<?php

class Scaffold extends Task {

	var $requireProject = false ;
	
	var $requireValidProject = false ;
	
	var $requireTypes = array(DevKitProjectType::UNKNOWN, ) ;  
	
	function init ()
	{
		
	}
	
	function getOptions ()
	{
		// We create an array of options
		$options = array () ;
		
		
		return $options ;
	}
	
	function onSetParams ( $options )
	{
		return true ;
	}
	
	function process ()
	{
		$dbs = DatabaseManager::getAll() ;
		
		foreach ( $dbs as $db )
		{
			
			$structure = $db->getStructure () ;
			
			$id = $db->getIdentifier () ;
			
			foreach ( $structure as $tableName => $fields )
			{
				$this->scaffod($id, $tableName);
			}
				
		
		}
	}

	
	
	function scaffod ( $database , $table )
	{
		
		$camelizedTable = camelize ( $table ) ;
		
		$controllerName = $camelizedTable . 'Controller' ;
		$modelName = $camelizedTable . 'Model' ;
		
		if ( $this->futil->fileExists ( AE_APP_CONTROLLERS . $controllerName . '.php' ) == false )
		{
			
			$templ = new Template ( 'php/Controller.thtml' ) ;
			
			
			$templ->setAll ( array (
				
				'controllerName' => $controllerName,
				'modelName' => $modelName
				
				) ) ;
			
			$controllerFile = "<?php\n" .$templ->render ( false ) . "\n?>" ;
			
			
			$file = new File ( AE_APP_CONTROLLERS . $controllerName . '.php' , true ) ;
			
			if ( $file->write ( $controllerFile ) && $file->close () )
			{
				$this->view->setSuccess ( 'Controller for table ' . $database . '/' . $table . ' created' ) ;
			} else {
				$this->view->setError ( 'Controller for table ' . $database . '/' . $table . ' not written in file, may be a permission problem' ) ;
	
			}
			
		} else {
			$this->view->setWarning( 'Controller for table ' . $database . '/' . $table . ' exists yet' ) ;
		}
		
		
		
		if ( $this->futil->fileExists ( AE_APP_MODELS . $controllerName . '.php' ) == false )
		{
			
			$templ = new Template ( 'php/Controller.thtml' ) ;
			
			
			$templ->setAll ( array (
				
				'controllerName' => $controllerName,
				'modelName' => $modelName
				
				) ) ;
			
			$modelFile = "<?php\n" .$templ->render ( false ) . "\n?>" ;
			
			
			$file = new File ( AE_APP_MODELS . $modelName . '.php' , true ) ;
			
			if ( $file->write ( $modelFile ) && $file->close () )
			{
				$this->view->setSuccess ( 'Controller for table ' . $database . '/' . $table . ' created' ) ;
			} else {
				$this->view->setError ( 'Controller for table ' . $database . '/' . $table . ' not written in file, may be a permission problem' ) ;
	
			}
			
		} else {
			$this->view->setWarning( 'Controller for table ' . $database . '/' . $table . ' exists yet' ) ;
		}
		
	}
	
	function beforeEnd () 
	{
		
	}

}
?>