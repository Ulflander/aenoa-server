<?php

/**
 * Inflector is used for string transqformation (formating, singularization, pluralization, transliteration...)
 */
class Inflector extends Object {
	
	/**
	 * Singularization rules
	 * 
	 * @var array 
	 */
	static public $singular = array(
		'/(quiz)zes$/i' => "$1",
		'/(matr)ices$/i' => "$1ix",
		'/(vert|ind)ices$/i' => "$1ex",
		'/^(ox)en$/i' => "$1",
		'/(alias)es$/i' => "$1",
		'/(octop|vir)i$/i' => "$1us",
		'/(cris|ax|test)es$/i' => "$1is",
		'/(shoe)s$/i' => "$1",
		'/(o)es$/i' => "$1",
		'/(bus)es$/i' => "$1",
		'/([m|l])ice$/i' => "$1ouse",
		'/(x|ch|ss|sh)es$/i' => "$1",
		'/(m)ovies$/i' => "$1ovie",
		'/(s)eries$/i' => "$1eries",
		'/([^aeiouy]|qu)ies$/i' => "$1y",
		'/([lr])ves$/i' => "$1f",
		'/(tive)s$/i' => "$1",
		'/(hive)s$/i' => "$1",
		'/(li|wi|kni)ves$/i' => "$1fe",
		'/(shea|loa|lea|thie)ves$/i' => "$1f",
		'/(^analy)ses$/i' => "$1sis",
		'/((a)naly|(b)a|(d)iagno|(p)arenthe|(p)rogno|(s)ynop|(t)he)ses$/i' => "$1$2sis",
		'/([ti])a$/i' => "$1um",
		'/(n)ews$/i' => "$1ews",
		'/(h|bl)ouses$/i' => "$1ouse",
		'/(corpse)s$/i' => "$1",
		'/(us)es$/i' => "$1",
		'/s$/i' => ""
	);
	
	/**
	 * Pluralization rules
	 * 
	 * @var array 
	 */
	static public $plural = array(
		'/(quiz)$/i' => "$1zes",
		'/^(ox)$/i' => "$1en",
		'/([m|l])ouse$/i' => "$1ice",
		'/(matr|vert|ind)ix|ex$/i' => "$1ices",
		'/(x|ch|ss|sh)$/i' => "$1es",
		'/([^aeiouy]|qu)y$/i' => "$1ies",
		'/(hive)$/i' => "$1s",
		'/(?:([^f])fe|([lr])f)$/i' => "$1$2ves",
		'/(shea|lea|loa|thie)f$/i' => "$1ves",
		'/sis$/i' => "ses",
		'/([ti])um$/i' => "$1a",
		'/(tomat|potat|ech|her|vet)o$/i' => "$1oes",
		'/(bu)s$/i' => "$1ses",
		'/(alias)$/i' => "$1es",
		'/(octop)us$/i' => "$1i",
		'/(ax|test)is$/i' => "$1es",
		'/(us)$/i' => "$1es",
		'/s$/i' => "s",
		'/$/' => "s"
	);
	
	/**
	 * Irregular words
	 * 
	 * @var array 
	 */
	static public $irregular = array(
		'move' => 'moves',
		'foot' => 'feet',
		'goose' => 'geese',
		'sex' => 'sexes',
		'child' => 'children',
		'man' => 'men',
		'tooth' => 'teeth',
		'person' => 'people'
	);
	
	/**
	 * Uncountable words
	 * 
	 * @var array
	 */
	static public $uncountable = array(
        'sheep',
        'fish',
        'deer',
        'series',
        'species',
        'money',
        'rice',
        'information',
        'equipment'
    );
	
	
	static public $tlSimple = array (
		'ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜÝÞßàáâãäåæçèéêëìíîïðñòóôõöøùúûýýþÿŔŕ',
		'aaaaaaaceeeeiiiidnoooooouuuuybsaaaaaaaceeeeiiiidnoooooouuuyybyRr'
	) ;
	
	static public $tlComplex = array (
		
	) ;
	
	
	static public function addTLRule ( $from , $to )
	{
		self::$tlSimple[0] .= $from ;
		self::$tlSimple[1] .= $to ;
	}
	
	static function addTLComplexRule ( $from , $to )
	{
		self::$tlComplex[$from] = $to ;
	}

	/**
	 * Camelize a string. Mainly used for MVC features.
	 * 
	 * @param string $str Word to camelize
	 * @param string $separator [Optional] Separator to use
	 * @return type 
	 */
	static function camelize ( $str , $separator = '_' )
	{
		return str_replace(' ', '', ucwords(str_replace($separator, ' ', $str)));
	}
	
	/**
	 * Transforms a camelized string into underscore string. Mainly used for MVC features.
	 * 
	 * @param string $str 
	 * @return string Undescored string 
	 */
	static function underscore ( $str )
	{
		return strtolower(preg_replace('/(?<=\\w)([A-Z])/', '-\\1', $str ));
	}
	
	/**
	 * Make a human readable string from a camelized or underscored string
	 * 
	 * @param string $str String to make readable
	 * @return string Readable string
	 */
	static function humanize ( $str , $separator = '_' )
	{
		return ucwords(str_replace($separator, ' ', self::underscore($str)));
	}
	
	
	/**
	 * Singularize a string. If given count is more than 1, string is returned as is.
	 * 
	 * @param string $str String to singularize
	 * @param int $count [Optional] Default is 1, if more then string returned as is
	 * @return string Singularized string if necessary
	 */
	static function singularize ( $str , $count = 1 )
	{
		
        if ( $count > 1 || in_array( strtolower( $str ), InflectorRules::$uncountable ) )
		{
            return $str;
		}

        foreach ( InflectorRules::$irregular as $result => $pattern )
        {
            $pattern = '/' . $pattern . '$/i';

            if ( preg_match( $pattern, $str ) )
                return preg_replace( $pattern, $result, $str);
        }

		
        foreach ( InflectorRules::$singular as $pattern => $result )
        {
            if ( preg_match( $pattern, $str ) )
			{
                return preg_replace( $pattern, $result, $str );
			}
        }

        return $str;
	}
	
	
	/**
	 * Pluralize a string. If given count is less than 2, string is returned as is.
	 * 
	 * @param string $str String to pluralize
	 * @param int $count [Optional] Default is 2, if less then string returned as is
	 * @return string Pluralized string if necessary
	 */
	static function pluralize ( $str , $count = 2 )
	{
        if ( $count < 2 || in_array( strtolower( $str ), InflectorRules::$uncountable ) )
		{
            return $str;
		}
		
        foreach ( InflectorRules::$irregular as $pattern => $result )
        {
            $pattern = '/' . $pattern . '$/i';

            if ( preg_match( $pattern, $str ) )
			{
                return preg_replace( $pattern, $result, $str);
			}
        }
		
        foreach ( InflectorRules::$plural as $pattern => $result )
        {
            if ( preg_match( $pattern, $str ) )
			{
                return preg_replace( $pattern, $result, $str );
			}
        }

        return $str;
	}

	/**
	 * Apply transliteration on a string.
	 * 
	 * Transliteration 
	 *
	 * @param type $str
	 * @param type $separator 
	 */
	static function transliterate ( $str , $separator = '-' )
	{
		$str = mb_strtolower($str,'UTF8');
		if ( !is_null ( $str ) ) {
			foreach( $tl_rules as $rule => $replace )
			{
				$str = mb_ereg_replace('/'.$rule.'/',$replace, $str);
			}
			
			$from = preg_split('/(?<!^)(?!$)/u',$tl__from);
			$to = preg_split('/(?<!^)(?!$)/u',$tl__to);
			
			for ( $i = 0, $l = count($from) ; $i < $l ; $i ++ )
			{
				$str = str_replace($from[$i],$to[$i], $str);
			}
			
			return str_replace(' ', $separator ,trim(preg_replace('/[^a-z0-9\\'.$separator.'\s]+/', '' , $str )));
		}
		
		return '' ;
	}
	
	
	/**
	 * Get a string without european accents
	 * 
	 * @param string $str String to deaccent
	 * @return string Deaccented string
	 */
	static function deaccent ( $str )
	{
		return utf8_decode(strtr($str,
				'ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜÝÞßàáâãäåæçèéêëìíîïðñòóôõöøùúûýýþÿŔŕ',
				'aaaaaaaceeeeiiiidnoooooouuuuybsaaaaaaaceeeeiiiidnoooooouuuyybyRr'
			));
	}

	/**
	 * Get a clean string without any special char
	 *
	 * - all European chars with accent are replaced with their equivalent whitout accent
	 * - strings authorized are letters, numbers and $separator
	 * - spaces are replaced by $separator char
	 *
	 *
	 * @param string $str String to urlize
	 * @return string Urlized string
	 */
	static function urlize ( $str , $separator = '-' )
	{
		if ( !is_null ( $str ) ) {
			return str_replace(' ', $separator ,trim(preg_replace('/[^a-z0-9\\'.$separator.'\s]+/', '' , strtolower( self::deaccent( $str ) ))));
		}
		return '' ;
	}
}

?>
