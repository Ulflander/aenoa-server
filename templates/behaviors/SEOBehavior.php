<?php

class SEOBehavior extends Behavior {
	 
	public $type = 'View' ;
	
	/**
	 * Get a SEO URL  
	 *
	 * @param type $element
	 * @return string 
	 */
	function getSeoURL ( $base , $element )
	{
		return url() .setTrailingSlash($base) .$element['slug'].'-'.$element['id'] . '.html' ;
	}
	
	function getSeoURLByCollection ( $base , GetableCollection $element )
	{
		return url().setTrailingSlash($base) . $element->Slug . '-' . $element->Id . '.html' ;
	}
}

?>
