<?php

class AeSeoFriend {
	
	public $HEADERS = array(
	
		'common' => array (
			
		),
		
		'open-graph' => array (
			'og:title' => 'TITLE',
			'og:type' => 'article',
			'og:image' => 'IMAGE',
			'og:url' => 'URI',
			'og:site_name' => 'SITE_NAME',
			'og:description' => 'EXCERPT',
		) ,
		
		'open-graph-for-facebook' => array (
			'og:admins' => 'FB_UID' ,
			'og:app_id' => 'FB_APP_ID' ,
		) ,
		
	);
	
	private $_title ;
	
	function setPageTitle ( $title )
	{
		$this->_title ;
	}
	
	function getPageTitle ()
	{
		return $this->_title ;
	}
	
	
	
} 


?>