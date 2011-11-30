<?php 
/**
 * <h2>Aenoa TemplateRendering service documentation</h2>
 * 
 * 
 * <p>Renders some elements or templates of application or server, and returns the result</p>
 * 
 * 
 * 
 * 
 * 
 * <h2>Service method: getElement</h2>
 * 
 * 
 * <p>Render an HTML element</p>
 * 
 * <h3>Parameters</h3>
 * 
 * 
 * <p><b>element</b> (Optional: no) The element name, as string</p>
 * <p><b>userId</b> (Optional: yes, default value is "null" ) If given, will simulate a connected user in rendered element</p>
 * <p><b>vars</b> (Optional: yes, default value is "array()" ) Some variables to add to template rendering, as an associative array.</p>
 * <p><b>language</b> (Optional: yes, default value is "null" ) Locale to use to render template. Available locales are the ones in your Aenoa Server application.</p>
 * 
 * 
 * 
 * 
 * <h3>Returns in case of success</h3>
 * 
 * <p><b>element</b> The HTML result  <pre>$result</pre></p>
 * 
 * 
 * 
 * 
 * <h3>Returns in case of failure</h3>
 * 
 * <p>Nothing is returned in case of failure</p>
 * 
 * 
 * 
 * 
 * @see TemplateRenderingService
 * 
 */

class TemplateRenderingServiceDescription { 
	public $generated = 'November 30, 2011, 1:54 pm' ;
	public $methods = array (
	'description' => 'Renders some elements or templates of application or server, and returns the result',
	'methods' => array (
		'getElement' => array (
			'name' => 'getElement',
			'arguments' => array (
				0 => array (
					'name' => 'element',
					'optional' => false,
					'description' => 'The element name, as string',
					),
				1 => array (
					'name' => 'userId',
					'optional' => true,
					'default' => 'null',
					'description' => 'If given, will simulate a connected user in rendered element',
					),
				2 => array (
					'name' => 'vars',
					'optional' => true,
					'default' => 'array()',
					'description' => 'Some variables to add to template rendering, as an associative array.',
					),
				3 => array (
					'name' => 'language',
					'optional' => true,
					'default' => 'null',
					'description' => 'Locale to use to render template. Available locales are the ones in your Aenoa Server application.',
					),
				),
			'firstLevelReturns' => array (
				0 => array (
					'name' => 'element',
					'value' => '$result',
					'description' => 'The HTML result',
					),
				),
			'secondLevelReturns' => array (
				),
			'description' => 'Render an HTML element',
			),
		),
	);
}
?>