<?php

class AeCSSEdition {
	
	
	const TYPE_FONT_COLOR = 'color';
	
	const TYPE_BACKGROUND_COLOR ='background-color';
	
	const TYPE_BORDER_COLOR ='border-color';
	
	const TYPE_BORDER ='border';
	
	public $charset = 'UTF-8' ;
	
	
	protected static $__solveImports = true ;
	
	protected static $__basePath = '' ;
	
	protected static $__report = array () ;
	
	
	/**
	 * Get a textual report of operation
	 */
	public static function getReport ()
	{
		return self::$__report ;
	}
	
	
	function parse ( $css )
	{
		$css = AeCSSCompressor::compressString($css) ;
		
		preg_match_all('/CHARSET \'([^\']{1,})\'/im',$css,$matches) ;
		
		if ( !empty($matches[1]) )
		{
			$this->charset = $matches[1] ;
		}
		
		$css = preg_replace('/(@[^;]{1,};)/im','',$css) ;
		
		preg_match_all('/([^{]{1,})\{([^}]{1,})\}/im',$css,$matches) ;
		
		$rules = array () ;
		
		foreach ( $matches[1] as $idx => $classname )
		{
			$rules[$classname] = array () ;
			
			$r = explode(';',$matches[2][$idx] ) ;
			
			foreach ( $r as $_r )
			{
				$_r2 = explode(':' , $_r) ;
				$rules[$classname][$_r2[0]] = $_r2[1] ;
			}
		}
		
		return $rules ;
	}
	
	public static function toIso10646 ($matches)
	{
	    $len = mb_strlen($matches[0]);
		$c = '' ;
	    for ($i = 0; $i < $len; $i++) {
	        $char = mb_substr($matches[0], $i, 1,'UTF-8');
	    	if ( $char != '' )
		        $c .= '\\' . bin2hex($char) ;
        }
        
		return $c;
	}
	

	/**
	 * This method retrieve the CSS content and solve imports
	 * @private
	 */
	public static function getCSSContent ( $cssfile )
	{
		self::$__report[] = 'File included: ' . $cssfile ;
		
		$file = new File ( realpath ( self::$__basePath . $cssfile ) , false ) ;
		pr(self::$__basePath . $cssfile);
		if ( $file->exists () == false )
		{
			return false ;
		}
		
		$css = $file->read () ;
		
		if ( self::$__solveImports == true )
		{
			$css = preg_replace_callback( '/@import\s{0,10}"(.*?)"\s{0,10};/' , 'AeCSSCompressor::import' , $css) ;
		}
		
		$file->close () ;
		
		return $css ;
	}
	
	/**
	 * This method is a callback for preg_replace_callback in AeCSSCompressor::getCSSContent.
	 * It will call recursively getCSSContent to import external stylesheets.
	 * @private
	 */
	public static function import ( $match )
	{
		return self::getCSSContent ( $match[1] ) ;
	}
}


?>