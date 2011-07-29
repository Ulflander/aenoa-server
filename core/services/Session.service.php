<?php


class SessionService extends Service {
	
	public function connect ()
	{
		if ( App::$session->started () == true )
		{
			$this->protocol->setSuccess ( true ) ;
		} else {
			$this->protocol->setSuccess ( false ) ;
		}
	}
	
}
?>