<?php


class WebpageHelper {
	
	
	/**
	 * Outputs the current webpage title
	 * 
	 * @return void
	 */
	public static function displayWebpageTitle () 
	{
		echo Webpage::getCurrentTitle () ;
	}
	
}
?>