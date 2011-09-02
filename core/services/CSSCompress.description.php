<?php 
/**
 * <h2>Aenoa CSSCompress service documentation</h2>
 * 
 * <p>This service allows compression of CSS strings.</p>
 * 
 * 
 * 
 * 
 * @see AenoaServerProtocol
 * @see Service
 * @see ServiceDescription
 * @see RemoteService
 * @see Gateway
 * 
 * 
 * <h2>Service method: compress</h2>
 * 
 * 
 * <p>The compress method is the main method of CSSCompress service.</p>
 * 
 * 
 * Parameters:
 * 
 * <h4>cssString</h4><p>Optional: *no*</p><p>The CSS String to compress</p>
 * 
 * 
 * 
 * 
 * 
 * 
 */

class CSSCompressServiceDescription { 
	public $generated = 'September 2, 2011, 7:11 am' ;
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