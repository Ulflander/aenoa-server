<?php 
/**
 * <h2>Aenoa Data service documentation</h2>
 * 
 * 
 * <p>This service authorize read-only access to data. It provides methods for field autocompletion and more further...</p>
 * 
 * 
 * 
 * 
 * 
 * <h2>Service method: autoComplete</h2>
 * 
 * 
 * <p>Returns a list of pairs id / value that match the query</p>
 * 
 * <h3>Parameters</h3>
 * 
 * 
 * <b>query</b> (Optional: no) The search query 
 * <b>source</b> (Optional: no) The databaseID / table / field in which to search 
 * <b>conditions</b> (Optional: yes, default value is """" ) Conditions to fit 
 * <b>results</b> (Optional: yes, default value is "10" ) Number of results to return 
 * 
 * 
 * 
 * <h3>Returns in case of success</h3>
 * 
 * This service does not return any data or failure message.
 * 
 * 
 * <h3>Returns in case of failure</h3>
 * 
 * <h2>Service method: getAllAndChilds</h2>
 * 
 * 
 * <p>Returns all data af a table (if table count &lt; 300) and the childs based on PICK_ONE and PICK_IN behaviors</p>
 * 
 * <h3>Parameters</h3>
 * 
 * 
 * <b>source</b> (Optional: no) The databaseID / table in which to search 
 * <b>conditions</b> (Optional: yes, default value is """" ) Conditions to fit 
 * <b>keysAsLabel</b> (Optional: yes, default value is "false" ) Returns labels in results despite of db field name 
 * <b>max</b> (Optional: yes, default value is "300" ) Max number of results to return 
 * 
 * 
 * 
 * <h3>Returns in case of success</h3>
 * 
 * This service does not return any data or failure message.
 * 
 * 
 * <h3>Returns in case of failure</h3>
 * 
 * <h2>Service method: getOneAndChilds</h2>
 * 
 * 
 * <p>Re</p>
 * 
 * <h3>Parameters</h3>
 * 
 * 
 * <b>source</b> (Optional: no) The databaseID / table / elementPrimaryKey to retrieve 
 * <b>conditions</b> (Optional: yes, default value is """" ) Conditions to fit 
 * <b>keysAsLabel</b> (Optional: yes, default value is "false" ) Returns labels in results despite of db field name 
 * 
 * 
 * 
 * <h3>Returns in case of success</h3>
 * 
 * This service does not return any data or failure message.
 * 
 * 
 * <h3>Returns in case of failure</h3>
 * 
 * 
 * 
 * 
 * 
 * @see DataService
 * 
 */

class DataServiceDescription { 
	public $generated = 'September 12, 2011, 9:56 am' ;
	public $methods = array (
	'description' => 'This service authorize read-only access to data. It provides methods for field autocompletion and more further...',
	'methods' => array (
		'autoComplete' => array (
			'name' => 'autoComplete',
			'arguments' => array (
				0 => array (
					'name' => 'query',
					'optional' => false,
					'description' => 'The search query',
					),
				1 => array (
					'name' => 'source',
					'optional' => false,
					'description' => 'The databaseID / table / field in which to search',
					),
				2 => array (
					'name' => 'conditions',
					'optional' => true,
					'default' => '""',
					'description' => 'Conditions to fit',
					),
				3 => array (
					'name' => 'results',
					'optional' => true,
					'default' => 10,
					'description' => 'Number of results to return',
					),
				),
			'firstLevelReturns' => array (
				),
			'secondLevelReturns' => array (
				),
			'description' => 'Returns a list of pairs id / value that match the query',
			),
		'getAllAndChilds' => array (
			'name' => 'getAllAndChilds',
			'arguments' => array (
				0 => array (
					'name' => 'source',
					'optional' => false,
					'description' => 'The databaseID / table in which to search',
					),
				1 => array (
					'name' => 'conditions',
					'optional' => true,
					'default' => '""',
					'description' => 'Conditions to fit',
					),
				2 => array (
					'name' => 'keysAsLabel',
					'optional' => true,
					'default' => 'false',
					'description' => 'Returns labels in results despite of db field name',
					),
				3 => array (
					'name' => 'max',
					'optional' => true,
					'default' => 300,
					'description' => 'Max number of results to return',
					),
				),
			'firstLevelReturns' => array (
				),
			'secondLevelReturns' => array (
				),
			'description' => 'Returns all data af a table (if table count &lt; 300) and the childs based on PICK_ONE and PICK_IN behaviors',
			),
		'getOneAndChilds' => array (
			'name' => 'getOneAndChilds',
			'arguments' => array (
				0 => array (
					'name' => 'source',
					'optional' => false,
					'description' => 'The databaseID / table / elementPrimaryKey to retrieve',
					),
				1 => array (
					'name' => 'conditions',
					'optional' => true,
					'default' => '""',
					'description' => 'Conditions to fit',
					),
				2 => array (
					'name' => 'keysAsLabel',
					'optional' => true,
					'default' => 'false',
					'description' => 'Returns labels in results despite of db field name',
					),
				),
			'firstLevelReturns' => array (
				),
			'secondLevelReturns' => array (
				),
			'description' => 'Re',
			),
		),
	);
}
?>