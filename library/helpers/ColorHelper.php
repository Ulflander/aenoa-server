<?php

/*
	Class: ColorHelper

	Helps you manage color codes and get closed colors
	
	Example:
	(start code)

	$helper = new ColorHelper () ;

	$helper->setList ( array ( 
				'beige' => '#EBDEC7',
				'maroon' => '#794F26',
				'green' => '#56AE1F',
				'yellow' => '#F9DD48',
				'orange' => '#FF9005',
				'red' => '#EE1423',
				'rose' => '#F06EAA',
				'violet' => '#6D558E',
				'blue' => '#3A93F2',
				'gray' => '#969696',
				'black' => '#000000',
				'white' => '#ffffff'
		) );



	$closest = $helper->closest ( '#808080', true ) ;

	// or

	$closest = $helper->closest ( ColorHelper::html2rgb ( '#808080' ) , true ) ;

	echo $closest ;
	// #969696


	(end)

 
 
 */
class ColorHelper {

	
	
	/**
	 * Convert an html color to a RGB array
	 * 
	 * @param string $color HTML color
	 * @return array RGB color 
	 */
	public static function html2rgb( $color ) {

		if ($color[0] == '#') {
			$color = substr($color, 1);
		}

		if (strlen($color) == 6) {
			list($r, $g, $b) = array($color[0] . $color[1], $color[2] . $color[3], $color[4] . $color[5]);
		} elseif (strlen($color) == 3) {
			list($r, $g, $b) = array($color[0] . $color[0], $color[1] . $color[1], $color[2] . $color[2]);
		} else {
			return false;
		}

		$r = hexdec($r);
		$g = hexdec($g);
		$b = hexdec($b);
		
		return array($r, $g, $b);
	}
	
	/**
	 * Convert a RGB array to html color
	 * 
	 * @param array $rgb RGB color
	 * @return string HTML color 
	 */
	public static function rgb2html(array $rgb)
	{
		
		if ( !self::validateRgb ( $rgb ) )
		{
			return '#000000' ;
		}
		
		$r = dechex($rgb[0]);

		if (strlen($r) < 2) {
			$r = '0' . $r;
		}

		$g = dechex($rgb[1]);

		if (strlen($g) < 2) {
			$g = '0' . $g;
		}

		$b = dechex($rgb[2]);

		if (strlen($b) < 2) {
			$b = '0' . $b;
		}


		return '#' . $r . $g . $b;
	}
	
	/**
	 * Calculate distance between two colors
	 * 
	 * @param array $color1 an RGB color
	 * @param array $color2 an RGB color
	 * @return float Distance between the two colors 
	 */
	public static function distance(array $color1, array $color2 ) {

		return sqrt(pow($color1[0] - $color2[0], 2) +
				pow($color1[1] - $color2[1], 2) +
				pow($color1[2] - $color2[2], 2));
	}
	
	/**
	 * Checks if an RGB color is a valid color array
	 * 
	 * @param array $color An RGB color as array
	 * @return boolean True if RGB color is valid, false otherwise
	 */
	public static function validateRgb ( $color )
	{
		return is_array ( $color )
			&& count ( $color ) === 3
			&& self::validateRgbValue ( $color[0] )
			&& self::validateRgbValue ( $color[1] )
			&& self::validateRgbValue ( $color[2] ) ;
	}
	
	/**
	 * Checks if a RGB color value (R or G or B) is valid
	 * 
	 * @param int $value Value of tint
	 * @return boolean True if color value is valid, false otherwise 
	 */
	public static function validateRgbValue ( $value )
	{
		return is_int ( $value ) && $value >= 0 && $value <= 255 ;
	}
	
	
	/**
	 * Default color is black
	 * @var array
	 */
	private $_color = array(0, 0, 0);
	
	/**
	 * Preloaded list of colors
	 *
	 * @var array
	 */
	private $_colors = array(
		'white' => array(255, 255, 255),
		'black' => array(0, 0, 0),
		'green' => array(0, 128, 0),
		'silver' => array(192, 192, 192),
		'lime' => array(0, 255, 0),
		'gray' => array(128, 0, 128),
		'olive' => array(128, 128, 0),
		'yellow' => array(255, 255, 0),
		'maroon' => array(128, 0, 0),
		'navy' => array(0, 0, 128),
		'red' => array(255, 0, 0),
		'blue' => array(0, 0, 255),
		'purple' => array(128, 0, 128),
		'teal' => array(0, 128, 128),
		'fuchsia' => array(255, 0, 255),
		'aqua' => array(0, 255, 255)
	);
	
	
	/**
	 * Creates a new ColorHelper instance
	 * 
	 * @param [string|array] $color Color to set as main color for this helper
	 */
	public function __construct ( $color = null )
	{
		if ( !is_null($color) )
		{
			$this->set($color) ;
		}
	}
	
	/**
	 * Set main color
	 * 
	 * @param type $color
	 * @return ColorHelper Current instance for chained command on this element
	 */
	public function set ( $color )
	{
		if ( is_string($color) )
		{
			$this->_color = self::html2rgb($color) ;
		}
		
		$this->_color = $color ;
		
		return $this ;
	}

	/**
	 * Get main color
	 * 
	 * @return array Returns an RGB array of the main color
	 */
	public function get ()
	{
		return $this->_color ;
	}
	
	/**
	 * Get main color as hex
	 * 
	 * @return array Returns an hex value of the main color
	 */
	public function getHex ()
	{
		return $this->_color ;
	}
	
	/**
	 * Reset list for closest color calculation
	 * 
	 * @param array $colors An array of RGB colors
	 * @return ColorHelper Current instance for chained command on this element
	 */
	public function setList ( array $colors )
	{
		$this->_colors = array() ;
		
		foreach ( $colors as $color )
		{
			if ( is_string ( $color ) )
			{
				array_push($this->_colors, self::html2rgb($color) ) ;
				
			} else if ( self::validateRgb ( $color ) )
			{
				array_push($this->_colors, $color ) ;
			}
		}
		
		return $this ;
	}
	
	/**
	 * Get list of colors for distance calculation
	 * 
	 * @return array Array of RGB colors
	 */
	public function getList ()
	{
		return $this->_colors ;
	}
	
	/**
	 * Returns closest color from list
	 * 
	 * @param type $color
	 * @param type $returnHex 
	 */
	public function closest ( $color = null , $returnHex = false )
	{
		// First 
		if ( is_string($color) )
		{
			$color = self::html2rgb($color) ;
		}
		
		$min = 1000 ;
		$result = null ;
		
		foreach ( $this->_colors as &$c )
		{
			$d = self::distance( $color , $c ) ;
			
			if ( $d < $min )
			{
				$min = $d ;
				$result = $c ;
			}
		}
		
		if ( $returnHex === true )
		{
			return self::rgb2html( $result ) ;
		}
		
		return $result ;
	}
}

?>
