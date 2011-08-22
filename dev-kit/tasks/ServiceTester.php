<?php


class ServiceTester extends Task {
    
	
	// Let's process search
	function process ()
	{
		
		$protocol = new AenoaServerProtocol () ;
		$protocol->addData ( 'path' , 'yop' ) ;
		$protocol->setService ( 'core::File::getList' ) ;

		$server = new RemoteService ( 'http://localhost:8888/aenoa-desk/api' , $protocol ) ;
		
		pr ( $server->connect () ) ;
		
		
	}
	



}







?>