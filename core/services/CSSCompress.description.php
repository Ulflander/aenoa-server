<?php 
/**
 * <h2>Aenoa CSSCompress service documentation</h2>
 * 
 * 
 * <p>This service allows compression of CSS strings.</p>
 * 
 * 
 * 
 * 
 * 
 * <h2>Service method: compress</h2>
 * 
 * 
 * <p>The compress method is the main method of CSSCompress service.</p>
 * 
 * <h3>Parameters</h3>
 * 
 * 
 * <b>cssString</b> (Optional: no) The CSS String to compress 
 * 
 * 
 * 
 * <h3>Returns in case of success</h3>
 * 
 * <b>css_string</b> Report of compression  <pre>AeCSSCompressor::compressString ( $cssString )</pre>
 * <b>len_before</b> Length of CSS content before compression  <pre>AeCSSCompressor::getLenBefore ()</pre>
 * <b>len_after</b> Length of CSS content after compression  <pre>AeCSSCompressor::getLenAfter ()</pre>
 * <b>report</b> A report of all actions done for compression  <pre>AeCSSCompressor::getReport ()</pre>
 * 
 * 
 * <h3>Returns in case of failure</h3>
 * 
 * 
 * 
 * 
 * 
 * @see CSSCompressService
 * 
 */

class CSSCompressServiceDescription { 
	public $generated = 'September 12, 2011, 9:56 am' ;
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
					'name' => 'css_string',
					'value' => 'AeCSSCompressor::compressString ( $cssString )',
					'description' => 'Report of compression',
					),
				1 => array (
					'name' => 'len_before',
					'value' => 'AeCSSCompressor::getLenBefore ()',
					'description' => 'Length of CSS content before compression',
					),
				2 => array (
					'name' => 'len_after',
					'value' => 'AeCSSCompressor::getLenAfter ()',
					'description' => 'Length of CSS content after compression',
					),
				3 => array (
					'name' => 'report',
					'value' => 'AeCSSCompressor::getReport ()',
					'description' => 'A report of all actions done for compression',
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