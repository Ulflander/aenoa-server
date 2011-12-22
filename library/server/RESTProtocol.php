<?php

/**
 * Here is detailed the protocol used by the REST service
 * 
 * <h3>Getting started</h3>
 * 
 * <p>Here is an example of URL to access to a table in main database structure (assuming your application is located at http://example.com)</p>
 * 
 * <pre>http://example.com/rest/structure_id/table_id.format</pre>
 * 
 * <p>Here is an example of URL to access to a precise element in a table in main database structure</p>
 * 
 * <pre>http://example.com/rest/structure_id/table_id/identifier.format</pre>
 * 
 * <h3>Let's see how Aenoa Server manage this query</h3>
 * <ul><li>REST Protocol will check for a database structure with id 'structure_id'</li>
 * <li>If found, it will check a table named 'tabled_id' in that structure</li>
 * <li>If found, it will delegate to DatabaseController the runtime of the query</li>
 * <li>Once DatabaseController has done the action, it returns data (for GET queries only)</li>
 * <li>Data is formatted into required format, and sent </li></ul>
 * 
 * <h3>Examples</h3>
 * 
 * <p>These examples assume that you have a database named 'products' registered in a structure named 'main'.</p>
 * 
 * <p>To retrieve the product with id '1', formatted in JSON, we would do</p>
 * 
 * <pre>http://example.com/rest/main/products/1.json</pre>
 * 
 * <p>To retrieve a selection of products, we would do</p>
 * 
 * <pre>http://example.com/rest/main/products.json</pre>
 * 
 * <p>To retrieve a precise selection of products, such as the last twenty created products, we would do</p>
 * 
 * <pre>http://example.com/rest/main/products.json&length=20&page=1&orderby=created&dir=desc</pre>
 * 
 * <p>Check out below all available options.</p>
 * 
 * <p>To retrieve the number of products in database, we would do</p>
 * 
 * <pre>http://example.com/rest/main/products/_count_.json</pre>
 * 
 * <p>To retrieve the number of pages of products in database for a given page length, we would do</p>
 * 
 * <pre>http://example.com/rest/main/products/_count_.json&length=20</pre>
 * 
 * <p>To retrieve all products of database, we would do</p>
 * 
 * <pre>http://example.com/rest/main/products/_enumerate_.json</pre>
 * 
 * <p>Check out below <b>Pagination options for data selection (GET request)</b> options for table elements selection</p> 
 * 
 * <h3>Available formats</h3>
 * 
 * <p>For now, the only format is json. But implementation of new formats is easy: contact us if you need another format.</p>
 * 
 * <h3>Available request types</h3>
 * 
 * <ul><li>GET: to retrieve one or more element, or to retrieve table count</li>
 * <li>POST: to add an element</li>
 * <li>PUT: to edit an element</li>
 * <li>DELETE: to delete an element</li></ul>
 * 
 * <h3>Result codes</h3>
 * <p>Aenoa REST service uses HTTP response codes:</p>
 * 
 * <h3>Common result codes</h3>
 * <ul><li>200: query is successful</li>
 * <li>401: Authentication failure</li>
 * <li>404: Required structure / table / element not found</li>
 * <li>500: System has triggered an error </li></ul>
 * 
 * <h3>POST result codes</h3>
 * <ul><li>201: Element has been created - Successful query will redirect to the newly created resource using Location header</li></ul>
 * 
 * <h3>Available options for all modes</h3>
 * 
 * <p><b>beautify</b></p>
 * <pre>beautify=true</pre>
 * <p>will return a formatted (indentation and eol) response</p>
 * 
 * <p><b>echo</b></p>
 * <pre>echo=true</pre>
 * <p>will output the response as plain text</p>
 * 
 * <h3>Global options for data selection (GET requests, table and element selection)</h3>
 * 
 * <p><b>recursivity</b></p>
 * <pre>recursivity=[0-10]</pre>
 * <p>set the level of recursivity of the query.</p>
 * <p>Recursivity defines how much linked data from current selected table to others tables should be retrieved in selection.</p>
 * <p>Recursivity has an effect only for fields typed as <b>parent</b> or <b>child</b> OR for fields that have one of these behaviors: <b>BHR_PICK_ONE</b> or <b>BHR_PICK_IN</b>.</p>
 * <p>Refer to application structure documentation to know whether a table contains such fields.</p> 
 * <p>Be aware that there is no control of recursivity. High levels of recursivity will return exhaustive data but will slow down the system. Use <b>fields</b> and <b>subfields</b> option to control returned data.</p>
 * <p>Maximum recursivity value is 10, but in most cases high levels of recursivity (more than 3) are not recommended.</p>
 * 
 * <p><b>fields</b></p>
 * <pre>fields=field1,field2</pre>
 * <p>set the needed fields, returned data will only contain these fields.</p>
 * 
 * <p><b>subfields</b></p>
 * <pre>subfields=table1:field1,field2;table2:field1,field2</pre>
 * <p>set the needed fields for each linked table (at any level of recursivity)</p>
 * 
 * <h3>Pagination options for data selection (GET requests, table selection only)</h3>
 * 
 * <p><b>length</b></p>
 * <pre>length=[0-x]</pre>
 * <p>set the requested number of result. Use <b>_count_</b> and this parameter to know how much pages are available for the given length</p>
 * 
 * <p><b>page</b></p>
 * <pre>page=1</pre>
 * <p>set the requested page of results.</p>
 * 
 * <p><b>orderby</b></p>
 * <pre>orderby=field</pre>
 * <p>orders results using given field.</p>
 * 
 * <p><b>jsonp</b></p>
 * <pre>jsonp=callback_name</pre>
 * <p>encapsulate result with callback function name given (GET requests only)</p>
 * 
 * <p><b>dir</b></p>
 * <pre>dir=[asc|desc]</pre>
 * <p>set the direction of order (asc for ascending, desc for descending). Use this parameter with <b>orderby</b> parameter.</p>
 * 
 * @see RESTGateway
 *
 */
class RESTProtocol extends AbstractProtocol {

	private $_preformatted = null;
	private $format = 'json';
	private $mode = 'GET';
	private $jsonp = null;

	/**
	 * HTTP request type (GET, POST, PUT or DELETE)
	 * 
	 * 
	 * @param string $mode
	 */
	function setMode($mode) {
		$this->mode = $mode;
	}

	function addPreformattedData($value) {
		$this->_preformatted = $value;
	}

	function encode($data) {

		$result = null;

		switch ($this->format) {
			case 'json':
				if (@$this->_service['beautify'] === 'true') {
					$result = beautify_json(json_encode_js($data));
				} else {
					$result = json_encode($data);
				}
				if (!is_null($this->jsonp)) {
					$result = $this->jsonp . '(' . $result . ');';
				}
				break;
		}

		return $result;
	}

	function decode($data) {
		switch ($this->format) {
			case 'json':
				return json_decode($data);
		}

		return null;
	}

	function getToSendData() {
		if (!is_string($this->_preformatted)) {
			return $this->encode($this->_data);
		} else {
			return $this->_preformatted;
		}
	}

	function validateData($data) {
		switch ($this->mode) {
			
		}
		return true;
	}

	function getQuery() {
		if (!empty($this->_service)) {
			return $this->_service;
		}
		return null;
	}

	function getFormattedResponse() {
		switch ($this->format) {
			case 'json':
				if (@$this->_service['echo'] === 'true') {
					header('Content-Type: text/plain; charset=utf-8');
					break;
				}
				header('Content-Type: text/x-json');
				break;
		}

		return $this->getToSendData();
	}

	function callService() {
		
		// Common parameters for DatabaseController
		$params = array(
			'databaseID' => $this->_service['structure'],
			'table' => $this->_service['table'],
			'avoidRender' => true
		);
		
		switch ($this->mode) {
			case 'GET':

				if (ake('jsonp', $this->_service)) {
					$this->jsonp = $this->_service['jsonp'];
				}

				$params['tableLength'] = ( ake('length', $this->_service) ? intval($this->_service['length']) : 10 );
				$params['recursivity'] = ( ake('recursivity', $this->_service) ? intval($this->_service['recursivity']) : 1 );


				if (ake('subfields', $this->_service)) {
					$fields = explode(';', $this->_service['subfields']);
					$final_fields = array();
					foreach ($fields as &$f) {
						$f = explode(':', $f);
						if (count($f) > 1) {
							$final_fields[@$f[0]] = explode(',', @$f[1]);
						}
					}
					$params['subFields'] = $final_fields;
				}

				if (ake('fields', $this->_service)) {
					$params['fields'] = explode(',', $this->_service['fields']);
				}

				$actionParams = array(
					( is_int(intval(@$this->_service['page'])) && intval(@$this->_service['page']) > 0 ? intval($this->_service['page']) : 1 ),
					( is_string(@$this->_service['orderby']) ? $this->_service['orderby'] : null ),
					( is_string(@$this->_service['dir']) ? $this->_service['dir'] : null ),
				);



				if ($this->_service['element'] === '_count_') {
					if (ake('length', $this->_service)) {
						array_unshift($actionParams, intval($this->_service['length']));
					}
					$action = '__count';
				} else
				if ($this->_service['element'] === '_enumerate_') {
					$action = '__enumerate';
				} else if ($this->_service['element'] != '') {
					$action = 'read';
					array_unshift($actionParams, $this->_service['element']);
				} else {
					$action = 'readAll';
				}

				$controller = Controller::launchController('Database', $action, array_shift($actionParams), $params, $actionParams, false);

				new HTTPStatus(200);

				break;

			case 'POST':
				if (empty($_POST)) {
					App::$sanitizer->setPUTasPOST($this->_service['structure'], $this->_service['table'], $this->format);
				}
				$controller = Controller::launchController('Database', 'add', null, $params);

				$db = App::getDatabase($this->_service['structure']);

				$struct = $db->getStructure();

				new HTTPStatus(201);

				App::redirect(url() . 'rest/' . $this->_service['structure'] . '/' . $this->_service['table'] . '/' . $db->getTableSchema($this->_service['table'])->getPrimary($controller->output) . '.' . $this->format);

				break;

			case 'PUT':
				App::$sanitizer->setPUTasPOST($this->_service['structure'], $this->_service['table'], $this->format);

				$controller = Controller::launchController('Database', 'edit', $this->_service['element'], $params);

				new HTTPStatus (202);
				break;

			case 'DELETE':
				$controller = Controller::launchController('Database', 'delete', $this->_service['element'], $params);
				new HTTPStatus (200);
				break;
		}

		$this->_data = $controller->output;

		// And we're done
		$this->respond();
	}

}

?>
