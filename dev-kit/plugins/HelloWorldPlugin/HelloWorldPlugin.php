<?php


/**
 * HelloWorldPlugin 
 * An Aenoa SDK Task
 * 
 * Author: Xav 
 * Copyright: Xav 2010 
 * Support URL: No support 
 * Author URL: http://www.aenoa-systems.com 
 * 
 * (Don't modify these informations here but in AenoaPluginEdit task) 
 */

class HelloWorldPlugin extends DevKitPlugin {

	
	function process ()
	{
		$this->view->setStatus ('Hello World') ;
	}
	

}
?>