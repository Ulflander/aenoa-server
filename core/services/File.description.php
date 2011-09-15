<?php 
/**
 * <h2>Aenoa File service documentation</h2>
 * 
 * 
 * <p>This service let you navigate and manage files and folders in the public folder of the Aenoa Server.</p>
 * 
 * 
 * 
 * 
 * 
 * <h2>Service method: getList</h2>
 * 
 * 
 * <p>This methods let\'s explore the public file system of an Aenoa Server</p>
 * 
 * <h3>Parameters</h3>
 * 
 * 
 * <b>path</b> (Optional: yes, default value is ""."" ) The path of the folder to list 
 * 
 * 
 * 
 * <h3>Returns in case of success</h3>
 * 
 * <b>list</b> The list of files and folders  <pre>$futil->getFilesList ( Config::get ( App::APP_PUBLIC_REPOSITORY ) . $path , false)</pre>
 * 
 * 
 * <h3>Returns in case of failure</h3>
 * 
 * 
 * 
 * 
 * 
 * @see FileService
 * 
 */

class FileServiceDescription { 
	public $generated = 'September 12, 2011, 9:56 am' ;
	public $methods = array (
	'description' => 'This service let you navigate and manage files and folders in the public folder of the Aenoa Server.',
	'methods' => array (
		'getList' => array (
			'name' => 'getList',
			'arguments' => array (
				0 => array (
					'name' => 'path',
					'optional' => true,
					'default' => '"."',
					'description' => 'The path of the folder to list',
					),
				),
			'firstLevelReturns' => array (
				0 => array (
					'name' => 'list',
					'value' => '$futil->getFilesList ( Config::get ( App::APP_PUBLIC_REPOSITORY ) . $path , false)',
					'description' => 'The list of files and folders',
					),
				),
			'secondLevelReturns' => array (
				),
			'description' => 'This methods let\'s explore the public file system of an Aenoa Server',
			),
		),
	);
}
?>