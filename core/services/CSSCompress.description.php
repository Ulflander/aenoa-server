<?php 
 class CSSCompressServiceDescription { 
	public $generated = 'October 28, 2010, 11:40 am' ;
	public $methods = array (
	'description' => 'This service allows compression of CSS strings.',
	'methods' => array (
		'compress' => array (
			'name' => 'compress',
			'arguments' => array (
				0 => array (
					'name' => 'cssString',
					'optional' => false,
					'description' => 'The CSS String to compress',
					),
				),
			'firstLevelReturns' => array (
				0 => array (
					'name' => 'report',
					'value' => 'AeCSSCompressor::getReport ()',
					'description' => 'Report of compression',
					),
				),
			'secondLevelReturns' => array (
				),
			'description' => 'The compress method is the main method of CSSCompress service.',
			),
		),
	);
}
?>