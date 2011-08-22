<?php


class SDKPrefs extends Task {
    
	private $query ;
	
	function getOptions ()
	{
		$this->view->setTitle ( 'Deploy Aenoa SDK | Welcome' ) ;
		
		global $broker ;
		$prefs = $broker->preferences ;
		
		$options = array () ;
		
		$opt = new Field () ;
		$opt->fieldset = 'SDK parameters' ;
		$opt->label = 'Backup path' ;
		$opt->name = 'backupPath' ;
		$opt->type = 'input' ;
		$opt->required = true ;
		$opt->description = 'This path will be used by all backup classes except ManualBackup.' ;
		
		if ( $prefs->has ( 'backupPath' ) )
		{
			$opt->value = $prefs->get ( 'backupPath' ) ;
		} else {
			$opt->description += "<br />Currently defined on default value ! You should set a backup folder outside of the SDK, for more security." ;
		}
		$options[] = $opt ;
		
		$opt = new Field () ;
		$opt->label = 'UNIX only: Backup path auto mount' ;
		$opt->name = 'backupMount' ;
		$opt->type = 'input' ;
		$opt->description = 'If your backup path is on a volume to mount, set here share details.<br />Format: user@server/sharename<br />Mounted volume name will be the same as sharename.' ;
		if ( $prefs->has ( 'backupMount' ) )
		{
			$opt->value = $prefs->get ( 'backupMount' ) ;
		}
		$options[] = $opt ;
		
		$opt = new Field () ;
		$opt->label = 'Trash path' ;
		$opt->name = 'trashPath' ;
		$opt->type = 'input' ;
		$opt->required = true ;
		$opt->description = 'This path will be used by all trash classes.' ;
		if ( $prefs->has ( 'trashPath' ) )
		{
			$opt->value = $prefs->get ( 'trashPath' ) ;
		}
		$options[] = $opt ;
		
		$opt = new Field () ;
		$opt->label = 'Activate auto MySQL configuration for projects and model deployment' ;
		$opt->name = 'autoMysqlConfig' ;
		$opt->type = 'checkbox' ;
		$opt->description = 'Defining these default parameters and activating the option will authorize SDK to auto configure your new projects and deployed models. That is a gain of time for you.' ;
		$opt->fieldset = 'Default MySQL parameters' ;
		$options[] = $opt ;
		if ( $prefs->has ( 'autoMysqlConfig' ) && $prefs->get ( 'autoMysqlConfig' ) == true )
		{
			$opt->attributes['checked'] = 'checked' ;
		}
		
		$opt = new Field () ;
		$opt->label = 'Default MySQL Database Host' ;
		$opt->name = 'mysqlHost' ;
		$opt->type = 'input' ;
		if ( $prefs->has ( 'mysqlHost' ) )
		{
			$opt->value = $prefs->get ( 'mysqlHost' ) ;
		}
		$options[] = $opt ;
		
		$opt = new Field () ;
		$opt->label = 'Default MySQL Database login' ;
		$opt->name = 'mysqlLogin' ;
		$opt->type = 'input' ;
		if ( $prefs->has ( 'mysqlLogin' ) )
		{
			$opt->value = $prefs->get ( 'mysqlLogin' ) ;
		}
		$options[] = $opt ;
		
		$opt = new Field () ;
		$opt->label = 'Default MySQL Database password' ;
		$opt->name = 'mysqlPwd' ;
		$opt->type = 'password' ;
		if ( $prefs->has ( 'mysqlPwd' ) )
		{
			$opt->value = $prefs->get ( 'mysqlPwd' ) ;
		}
		$options[] = $opt ;
		
		return $options ;
	}
	
	
	function onSetParams ( $options = array () )
	{
		$result = true ;
		
		if ( @$this->params['autoMysqlConfig'] == 'on' )
		{
			$db = new Database () ;
		
			$db->setEngine ( new MySQLEngine () ) ;
			
			if ( !array_key_exists ( 'mysqlHost' , $this->params ) || $db->test ( @$this->params['mysqlHost'] , @$this->params['mysqlLogin'] , @$this->params['mysqlPwd'] ) == false )
			{
				foreach ( $options as &$option )
				{
					if ( in_array ($option->name , array ('mysqlHost','mysqlLogin','mysqlPwd' ) ) )
					{
						$option->required = true ;
						$option->valid = false ;
						$option->description = 'If you activate auto configuration, database connection informations must be valid.' ;
					}
				}
				
				$result = false ;
			} else {
				foreach ( $options as &$option )
				{
					switch ( $option->name ) {
						case 'mysqlHost':
							$option->description = 'This host seems to be valid.' ;
							$option->required = true ;
							$option->valid = true ;
							break;
						case 'mysqlLogin':
						case 'mysqlPwd':
							$option->required = true ;
							$option->valid = null ;
							$option->description = 'Remember that login and password could be different for each database, depending on MySQL authorizations.' ;
					
					}
				}
			}
		}
		
		if ( !is_dir ( @$this->params['backupPath'] ) )
		{
			$option = $this->getOption ( &$options , 'backupPath' ) ;
			$option->description = 'This is not a valid path.' ;
			$option->valid = false ;
			$result = false ;
		}
	
		if ( !is_dir ( @$this->params['trashPath'] ) )
		{
			$option = $this->getOption ( &$options , 'trashPath' ) ;
			$option->description = 'This is not a valid path.' ;
			$option->valid = false ;
			$result = false ;
		}
		
		return $result ;
	}
	
	// Let's process search
	function process ()
	{
		global $broker ;
		$prefs = $broker->preferences ;
		
		$prefs->create () ;
		
		$prefs->set ( 'backupPath' , $this->params['backupPath'] ) ;
		$prefs->set ( 'backupMount' , $this->params['backupMount'] ) ;
		$prefs->set ( 'trashPath' , $this->params['trashPath'] ) ;
		$prefs->set ( 'autoMysqlConfig' , (@$this->params['autoMysqlConfig'] == 'on' ? true : false ) ) ;
		$prefs->set ( 'mysqlHost' , $this->params['mysqlHost'] ) ;
		$prefs->set ( 'mysqlLogin' , $this->params['mysqlLogin'] ) ;
		if ( $prefs->set ( 'mysqlPwd' , $this->params['mysqlPwd'] , true ) == true )
		{
			$this->view->setSuccess ( 'Preferences saved.' ) ;
		} else {
			$this->view->setError ( 'Preferences not saved. Check out file authorizations.' ) ;
		}
	}
	
}













?>