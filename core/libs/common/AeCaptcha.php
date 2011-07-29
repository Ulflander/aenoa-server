<?php


class AeCaptcha {


	/**
	 * 
	 * Generates a new Captcha
	 * 
	 * The captcha image will be kept in memory until the save or render methods are called
	 * 
	 * @param int $w
	 * @param int $h
	 * @param int $length
	 */
	function __construct ( $w = 200, $h = 60 , $length = 6 )
	{
		if ( !function_exists('imagecreate') )
		{
			trigger_error('Aenoa Server requires GD library for captcha feature.', E_USER_ERROR ) ;
		}
		
		$this->code = AeCaptcha::getNewCode ( $length ) ;
		
		$this->url = $this->generate( $this->code, $w, $h ) ;
	}
	
	private $code ;
	
	private $url ;
	
	private $image ;
	
	function getCode ()
	{
		return $this->code ;
	}
	
	function getURL ()
	{
		return url() . $this->url ;
	}

	static function getNewCode($length, $chars = '23456789BCDFGHJKMNPQRSTVWXYZ') {
		$code = '';
		$l = strlen($chars) ;
		while (strlen($code) < $length) {
			$code .= substr($chars, mt_rand(0, $l-1), 1);
		}
		return $code;
	}

	function generate ($code, $w, $h ) {
		
		$font = AE_SERVER . 'tools' . DS . 'fonts' . DS . 'neurotox.ttf' ;
		$font2 = AE_SERVER . 'tools' . DS . 'fonts' . DS . 'highnoon.ttf' ;
		
		/* font size will be 75% of the image height */
		$font_size = $h * 0.55 ;
		
		$image = imagecreate($w, $h);
		
		/* set the colours */
		$background_color = imagecolorallocate($image, 255, 255, 255);
		
		$noise_color = imagecolorallocate($image, 120, 140, 180);
		$text_color = imagecolorallocate($image, 20, 20, 40);
		
		
		for( $i=0; $i<($w*$h)/10; $i++ ) {
		//	$noise_color = imagecolorallocate($image, mt_rand(0,3), mt_rand(0,3), mt_rand(0,3));
			imagefilledellipse($image, mt_rand(0,$w), mt_rand(0,$h), 2, 2, $noise_color);
			imagefilledellipse($image, mt_rand(0,$w), mt_rand(0,$h), 1, 1, $text_color);
		}
		
		for( $i=0; $i<5; $i++ ) {
		//	$noise_color = imagecolorallocate($image, mt_rand(100,140), mt_rand(120,140), mt_rand(120,180));
			imageline($image, mt_rand(0,$w), mt_rand(0,$h), mt_rand(0,$w), mt_rand(0,$h), $text_color);
		}
		
		$textbox = imagettfbbox($font_size, 0, $font, $code);
		
		$x = ($w - $textbox[4])/2;
		$y = ($h - $textbox[5])/2;
		
		
		imagettftext($image, $font_size, 0, 10, $y-2, $text_color, $font2 , $code);
		
		/* output captcha image to browser */
		
		$filename = md5($code);
		
		$url = 'g_assets'.DS.'captcha_'.$filename.'.jpg' ;
		
		$this->image = $image ;
		
		
		return $url;
	}
	
	/**
	 * Directly render the Captcha image
	 * Once the image is rendered OR saved, it is destroyed
	 */
	function render ()
	{
		header('Content-type: image/jpeg');
		imagejpeg($this->image);
		imagedestroy($this->image);
	}
	/**
	 * Save the Captcha image in given path or auto aenoa path
	 * Once the image is rendered OR saved, it is destroyed
	 * 
	 * @param string $path Path to the new captcha file
	 */
	function save ( $path = null )
	{
		if ( is_null($path) )
		{
			$path = ROOT.$this->url ;
		}
		imagejpeg($this->image,$path);
		imagedestroy($this->image);
	}
}


?>