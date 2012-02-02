<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class Event {
	
	private $blocked = false ;
	
	
	function block ()
	{
		$this->blocked = true ;
	}
	
	function isBlocked ()
	{
		return $this->blocked ;
	}
	
	
	
}

?>
