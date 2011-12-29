<?php

class Scaffold extends Task {
	
	
	// Not scaffold these tables name for main structure
	private $unscaffoldable = array (
		'ae_users',
		'ae_groups',
		'ae_users_info',
		'ae_api_keys',
		'ae_confirmations'
	) ;
	
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
		
		foreach ( $dbs as $id => $db )
		{
			
			$structure = $db->getStructure () ;
			
			foreach ( $structure as $tableName => $fields )
			{
				if (in_array($tableName, $this->unscaffoldable ) )
				{
					continue;
				}
				
				$this->scaffod($id, $tableName);
			}
				
		
		}
	}

	
	
	function scaffod ( $database , $table )
	{
		
		$camelizedTable = camelize ( $table , '_' ) ;
		
		$controllerName = $camelizedTable . 'Controller' ;
		$modelName = $camelizedTable . 'Model' ;
		$viewRoot = AE_APP_TEMPLATES . 'html' . DS ;
		$viewFolderName = str_replace('_','-',$table) ;
		$viewFilename = 'index.thtml' ;
		
		$this->view->setStatus ('Will try to create files ' . $controllerName . '.php, ' . $modelName . '.php, ' . $viewFolderName . '/'. $viewFilename) ;
		
		
		// First create controller if not exists
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
		
		
		// Then model
		// Code is quite the same, but is se
		if ( $this->futil->fileExists ( AE_APP_MODELS . $modelName . '.php' ) == false )
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
			$this->view->setWarning( 'Model for table ' . $database . '/' . $table . ' exists yet' ) ;
		}
		
		
		if ( !$this->futil->dirExists($viewRoot . $viewFolderName ) && !$this->futil->createDir($viewRoot , $viewFolderName) )
		{
			$this->view->setError('Folder for views of table ' . $table . ' not created.') ;
			return ;
		}
		
		if ( !$this->futil->fileExists($viewRoot . $viewFolderName . DS . $viewFilename ) )
		{
			$f = new File ( $viewRoot . $viewFolderName . DS . $viewFilename , true ) ;
			
			if ( !$f->exists() )
			{
				$this->view->setError('Template index.thtml of table ' . $table . ' not created.') ;
				return ;
			}
		}
		
		
		
		
	}
	
	function beforeEnd () 
	{
		
	}

}
?>