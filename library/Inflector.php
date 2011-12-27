<?php

/**
 * Inflector is used to
 */
class Inflector extends Object {



	function camelize ( $word , $separator = '_' )
	{
		return str_replace(' ', '', ucwords(str_replace($separator, ' ', $lowerCaseWord)));
	}

	function underscore ( $word )
	{
		return strtolower(preg_replace('/(?<=\\w)([A-Z])/', '-\\1', $camelCasedWord));
	}

	function humanize ( $word )
	{
		return ucwords(str_replace($separator, ' ', $lowerCaseWord));
	}

	function singularize ( $word )
	{

	}

	function pluralize ( $word )
	{
		
	}

	function deaccent ( $word )
	{
		
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
	function urlize ( $str , $separator = '-' )
	{
		if ( !is_null ( $str ) ) {
			return str_replace(' ', $separator ,trim(preg_replace('/[^a-z0-9\\'.$separator.'\s]+/', '' , strtolower( strtr(
				utf8_decode($str),
				utf8_decode('ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜÝÞßàáâãäåæçèéêëìíîïðñòóôõöøùúûýýþÿŔŕ'),
							'aaaaaaaceeeeiiiidnoooooouuuuybsaaaaaaaceeeeiiiidnoooooouuuyybyRr'
			)))));
		}
		return '' ;
	}

	function transliterate ( $str , $separator = '-' )
	{
		
	}

}

?>
