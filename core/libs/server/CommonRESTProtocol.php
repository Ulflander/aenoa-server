<?php


class CommonRESTProtocol extends AbstractProtocol {
	
	
	
	private $_preformatted = null ;
	
	private $format = 'json' ;
	
	private $mode = 'GET' ;
	
	
	function setMode ( $mode )
	{
		$this->mode = $mode ;
	}
	
	function addPreformattedData ( $value )
	{
		$this->_preformatted = $value ;
	}
	
	
	function encode ( $data )
	{
		switch($this->format)
		{
			case 'json':
				return json_encode ( $data ) ;
		}
		
		return null ;
	}
	
	function decode ( $data )
	{
		switch($this->format)
		{
			case 'json':
				return json_decode ( $data ) ;
		}
		
		return null ;
	}
	
	function getToSendData ()
	{
		if ( !is_string($this->_preformatted) )
		{
			return $this->encode($this->_data);
		} else {
			return $this->_preformatted ;
		}
	}
	
	function validateData ( $data )
	{
		switch ( $this->mode )
		{
			
		}
		return true ;
	}
	
	
	function getQuery ()
	{
		if ( !empty($this->_service))
		{
			return $this->_service ;
		}
		return null ;
	}
	
	function getFormattedResponse ()
	{
		switch($this->format)
		{
			case 'json':
				header ('Content-Type: text/x-json');
				break;
		}
		
		return $this->getToSendData () ;
	}
	
	
	function callService ()
	{
		$params = array (
			'databaseID' => $this->_service['structure'],
			'table' => $this->_service['table'],
			'avoidRender' => true
				);
		
		switch ( $this->mode )
		{
			case 'GET':
				if ( $this->_service['element'] != '' )
				{
					$controller = Controller::launchController ( 'Database' , 'read' , $this->_service['element'] , $params);
				} else {
					$controller = Controller::launchController ( 'Database' , '__enumerate' , null , $params);
				}
				
				App::sendHeaderCode(200) ;
				break;
				
			case 'POST':
				if(empty($_POST))
				{
					App::$sanitizer->setPUTasPOST($this->_service['structure'], $this->_service['table'] , $this->format) ;
				}
				$controller = Controller::launchController ( 'Database' , 'add' , null , $params );
				
				$db = App::getDatabase($this->_service['structure']) ;
				
				$struct = $db->getStructure() ;
				
				App::sendHeaderCode(201) ;
				
				App::redirect( url() . 'rest/'.$this->_service['structure'] . '/' . $this->_service['table'] . '/' . AbstractDB::getPrimary($struct[$this->_service['table']] ,$controller->output) .'.' . $this->format );
				break;
				
			case 'PUT':
				App::$sanitizer->setPUTasPOST($this->_service['structure'], $this->_service['table'] , $this->format) ;
				$controller = Controller::launchController ( 'Database' , 'edit' , $this->_service['element'] , $params );
				App::sendHeaderCode(202) ;
				break;
				
			case 'DELETE':
				$controller = Controller::launchController ( 'Database' , 'delete' , $this->_service['element'] , $params );
				App::sendHeaderCode(200) ;
				break;
		}
		
		$this->_data = $controller->output ;
		
		// And we're done
		$this->respond () ;
		
	}
}
?>