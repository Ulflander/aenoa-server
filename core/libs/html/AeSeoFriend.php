<?php

// http://developers.facebook.com/docs/opengraph/
// http://searchenginewatch.com/article/2067564/How-To-Use-HTML-Meta-Tags

class AeSeoFriend {
	
	public $HEADERS_PROPERTY = array(
	
		'open-graph' => array (
			'og:title' => 'title',
			'og:type' => 'article',
			'og:image' => 'image',
			'og:url' => 'uri',
			'og:site_name' => 'appname',
			'og:description' => 'excerpt',
		) ,
		
		'open-graph-for-facebook' => array (
			'og:admins' => 'facebookUid' ,
			'og:app_id' => 'facebookAppUid' ,
		) ,
		
	);
	
	public $HEADERS_NAME = array(
	    
		'common' => array (
			'description' => 'excerpt',
		),
		
	);
	
	private $_title ;
	
	function getResult ()
	{
	    $str = '' ;
	    foreach ( $this->HEADERS_PROPERTY as &$arr )
	    {
		foreach ( $arr as $meta => $value )
		{
		    $str .= "\n" . '<meta property="'.$value.'" />' ;
		}
	    }
	    
	    /*
	    
	    foreach ( $this->HEADERS_NAME as &$arr )
	    {
	     ... 
	    }
	     */
	    
	    return $str ;
	}
	
	function setPageTitle ( $title )
	{
		$this->_title ;
	}
	
	function getPageTitle ()
	{
		return $this->_title ;
	}
	
	
	function setExcerpt ( $excerpt )
	{
	    
	}
	
	function getExcerpt ()
	{
	    
	}
	
	
} 


?>