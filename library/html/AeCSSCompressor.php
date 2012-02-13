<?php

class AeCSSCompressor extends AeCSSEdition
{
	
	/**
	 * Auth chars in a css string
	 * @var string
	 */
	public static $PATTERNS = array (
		array (
			'preg_replace',
			'/\"/s' ,
			'\'',
			'Unified quotes'
		),
		array (
			'preg_replace',
			'/(\/\*(.*?)\*\/)/s' ,
			'',
			'Comments removed'
		),
		array (
			'preg_replace',
			'/\s\s+/' ,
			' ',
			'Odd spaces removed'
		),
		array (
			'preg_replace',
			'/(\s)?(:|;|,|{|})(\s)?/s' ,
			'\\2',
			'Unusefull spaces removed'
		),
		array (
			'preg_replace',
			'/#([a-f0-9])\1([a-f0-9])\2([a-f0-9])\3/i' ,
			'#\1\2\3',
			'Color shortcuts created'
		),
		array (
			'preg_replace',
			'/font-weight:bold/i' ,
			'font-weight:700',
			'Font-weight bold shortcuts created'
		),
		array (
			'preg_replace',
			'/font-weight:normal/i' ,
			'font-weight:400',
			'Font-weight normal shortcuts created'
		),
		array (
			'preg_replace',
			'/(?<![0-9#])0[a-z%]{1,2}/i' ,
			'__ZERO__',
			'Optimized zero values'
		),
		array (
			'preg_replace',
			'/(?<![0-9])0(.[0-9]{1,10}[in|cm|mm|pt|pc|px|rem|em|%|ex|gd|vw|vh|vm|deg|grad|rad|ms|s|khz|hz])/' ,
			'\1',
			'Optimized numbers'
		),
		array (
			'preg_replace_callback',
			'/[^a-z0-9-_\{\}\<\>:;\*\.\+\#,\/\/\s\'\)\(\[\]!%@=\$\^]{1,}/i' ,
			'AeCSSEdition::toIso10646',
			'Unauthorized chains encoded into ISO 10646'
		),
		array (
			'str_replace',
			array("('", "')", ';}','__ZERO__', '! important') ,
			array('(' , ')' , '}' ,'0', '!important'),
			'Final cleaning'
		),
	) ;
	
	
	/**
	 * Compress a CSS file
	 * 
	 * This method will import recursively relative external style sheets embedded using '@import' CSS rule
	 * 
	 * @param string $cssfile Path to the CSS file to compress
	 * @param bool $solveImports True if you want to solve imports, false otherwise. Default: true.
	 * @param bool $saveToBaseFile True if you want to save compressed CSS in original file
	 * @return Returns false if the CSS file has not been found, returns the compressed CSS content otherwise.
	 */
	public static function compress ( $cssfile , $solveImports = true, $saveToBaseFile = false )
	{
		self::$__report = array () ;
		
		self::$__solveImports = $solveImports ;
		
		self::$__basePath = setTrailingDS ( dirname ( $cssfile ) ) ;
		
		self::$__tcss = '' ; 
		
		$file = basename ( $cssfile ) ;
		
		$css = self::getCSSContent ( $file ) ;
		
		self::$__tcss = $css ;
		
		$css = self::compressString ( $css , false ) ;
        
		if ( $css === false )
		{
			return false ;
		}
		
		if ( $saveToBaseFile )
		{
			$f = new File ( $cssfile ) ;

			$f->write ( $css ) ;

			$f->close () ;
		}
		
		return $css ;
		
	}
	
	/**
	 * Parse a CSS string using 
	 * 
	 * @param string $cssfile Path to the CSS file to compress
	 * @return Returns false if the CSS file has not been found, returns the compressed CSS content otherwise.
	 */
	public static function compressString ( $cssString , $clearReport = true )
	{
		if ( $clearReport )
		{
			self::$__report = array () ;
		}
		
		self::$__tcss = $cssString ;
		
		foreach ( self::$PATTERNS as $pattern )
		{
			// get method
			if ($pattern[0] == 'preg_replace' || $pattern[0] == 'preg_replace_callback' ) 
			{
				
				preg_match_all ( $pattern[1] , $cssString , $res ) ;
				
				self::$__report[] = $pattern[3] . ': <strong>' . count($res[0]) . '</strong>' ;
				
			} else {
				
				self::$__report[] = $pattern[3] . ': done' ;
				
			}
			
			$cssString = @$pattern[0] ( $pattern[1] , $pattern[2], $cssString ) ;
			
		}
		
        self::$_before = strlen ( self::$__tcss ) ;
        
        self::$_after = strlen ( $cssString ) ;
        
		return $cssString ;
		
	}
	
	/**
	 * Returns the CSS content before the compression
	 */
	public static function getBefore ()
	{
		return self::$__tcss ;
	}
	
	/**
	 * Returns the CSS content length before the compression
	 */
	public static function getLenBefore ()
	{
		return self::$_before ;
	}

	/**
	 * Returns the CSS content length after the compression
	 */
	public static function getLenAfter ()
	{
		return self::$_after ;
	}
	
	/**
	 * @private
	 */
	private static $_before = 0 ;
	
	/**
	 * @private
	 */
	private static $_after = 0 ;
	
	/**
	 * @private
	 */
	private static $__tcss = '' ;
}

?>