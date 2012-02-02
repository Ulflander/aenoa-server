<?php

/**
 * Class: ColorHelper
 *
 * Helps you manage color codes and get approximatives color
 * 
 */
class ColorHelper {

	/**
	 * Default color is black
	 * @var array
	 */
	private $_color = array(0, 0, 0);
	
	
	private static $colors = array(
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
	
	public function __construct ( $color = null )
	{
		if ( !is_null($color) )
		{
			
		}
	}
	
	public function set ( $color )
	{
		if ( is_string($color) )
		{
			$this->_color = $this->html2rgb($color) ;
		}
		
		$this->_color = $color ;
		
		return $this ;
	}

	public function get ()
	{
		return $this->_color ;
	}
	
	public function html2rgb( $color ) {

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

	public function rgb2html(array $rgb) {

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

	public function distance(array $color1, array $color2 = array()) {
		if (empty($color2)) {
			$color2 = $this->_color;
		}

		return sqrt(pow($color1[0] - $color2[0], 2) +
				pow($color1[1] - $color2[1], 2) +
				pow($color1[2] - $color2[2], 2));
	}
	
	
}

?>
