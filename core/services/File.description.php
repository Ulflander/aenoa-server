<?php 
 class FileServiceDescription { 
	public $generated = 'October 19, 2010, 12:29 pm' ;
	public $methods = array (
	'description' => 'This service let you navigate and manage files and folders in the public folder of the Aenoa Server.',
	'methods' => array (
		'getList' => array (
			'name' => 'getList',
			'arguments' => array (
				0 => array (
					'name' => 'path',
					'optional' => false,
					'default' => '"."',
					'description' => 'The path of the folder to list',
					),
				),
			'firstLevelReturns' => array (
				0 => array (
					'name' => 'list',
					'value' => '$futil->getFilesList ( PUBLIC_ROOT . $path )',
					'description' => 'The list of files and folders',
					),
				),
			'secondLevelReturns' => array (
				),
			'description' => 'This methods let\\\'s explore the public file system of an Aenoa Server',
			),
		'downloadFile' => array (
			'name' => 'downloadFile',
			'arguments' => array (
				0 => array (
					'name' => 'path',
					'optional' => true,
					'default' => '"."',
					'description' => 'Path where download the file',
					),
				1 => array (
					'name' => 'fileContent',
					'optional' => true,
					'default' => 'null',
					'description' => 'Content of the file to download',
					),
				),
			'firstLevelReturns' => array (
				),
			'secondLevelReturns' => array (
				),
			'description' => 'ODd ',
			),
		),
	);
}
?>