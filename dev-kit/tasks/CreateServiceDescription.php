<?php

class CreateServiceDescription extends Task {

	var $requireProject = true;
	var $requireValidProject = true;
	var $requireTypes = array(DevKitProjectType::AENOA,);
	private $_methods = array();
	private $_description = array();
	private $_isCore = false;
	private $_formatSearch = array('>', '<', "\n");
	private $_formatReplace = array('&gt;', '&lt;', '<br />');

	function getOptions() {
		// We create an array of options
		$options = array();
		if ($this->hasParam('service') || $this->hasParam('secondStep')) {
			$service = ( $this->hasParam('service') ? $this->params['service'] : $this->params['secondStep'] );


			if (strpos($service, 'core/') === false && is_file(ROOT . 'app' . DS . 'services' . DS . $service . '.service.php')) {
				$this->_methods = ServiceIntrospector::introspect(ROOT . 'app' . DS . 'services' . DS . $service . '.service.php', $service);

				$serviceName = $service;

				if (is_file(ROOT . 'app' . DS . 'services' . DS . $service . '.description.php')) {
					require_once ( ROOT . 'app' . DS . 'services' . DS . $service . '.description.php' );
					$descClassName = $service . 'ServiceDescription';
					$descClass = new $descClassName ();
					$this->_description = $descClass->methods;
				}
			} else if (strpos($service, 'core/') === 0) {
				$serviceName = str_replace('core/', '', $service);

				$this->_isCore = true;

				$this->_methods = ServiceIntrospector::introspect(AE_CORE_SERVICES . $serviceName . '.service.php', $serviceName);

				if (is_file(AE_CORE_SERVICES . $serviceName . '.description.php')) {
					require_once ( AE_CORE_SERVICES . $serviceName . '.description.php' );
					$descClassName = $serviceName . 'ServiceDescription';
					$descClass = new $descClassName ();
					$this->_description = $descClass->methods;
				}
			}

			if (!empty($this->_methods[1])) {
				foreach ($this->_methods[1] as $error) {
					if (is_a($error, 'ServiceIntrospectorError')) {
						$this->view->setError('Service introspection error: ' . $error->error . ' / on method: ' . $error->method);
					}
				}
				$this->manager->cancel('Service introspection failed, see errors below.');
			} else if (empty($this->_methods[0])) {
				$this->manager->cancel('This service has not been found');
			}


			$opt = new Field ();
			$opt->fieldset = 'Creation of service description';
			$opt->label = 'Service name';
			$opt->type = 'label';
			$opt->value = $service;

			$options[] = $opt;

			$opt = new Field ();
			$opt->type = 'textfield';
			$opt->label = 'Description of service ' . $service;
			$opt->required = true;
			$opt->name = 'description';
			$opt->value = $this->getDescription();
			$opt->valid = ($opt->value != '' ? true : false);

			$options[] = $opt;

			$opt = new Field ();
			$opt->type = 'hidden';
			$opt->name = 'secondStep';
			$opt->required = true;
			$opt->value = $service;

			$options[] = $opt;

			foreach ($this->_methods[0] as $methodName => $description) {
				$opt = new Field ();
				$opt->fieldset = $methodName . ' method';
				$opt->label = 'Method name';
				$opt->type = 'label';
				$opt->value = $methodName;

				$options[] = $opt;

				$opt = new Field ();
				$opt->type = 'textfield';
				$opt->label = 'Description for method ' . $methodName;
				$opt->required = true;
				$opt->name = $methodName . ':description';
				$opt->value = $this->getDescription($methodName);
				$opt->valid = ($opt->value != '' ? true : false);

				$options[] = $opt;

				$i = 1;
				foreach ($description['arguments'] as $argument) {
					$opt = new Field ();
					$opt->label = 'Method argument ' . $i;
					$opt->type = 'label';
					$opt->value = $argument;

					$options[] = $opt;


					$opt = new Field ();
					$opt->type = 'textfield';
					$opt->label = 'Description for argument ' . $i;
					$opt->value = '';
					$opt->required = true;
					$opt->name = '' . $methodName . ':arguments:' . ($i - 1) . ':description';
					$opt->value = $this->getDescription($methodName, 'arguments', $i - 1);
					$opt->valid = ($opt->value != '' ? true : false);

					$options[] = $opt;

					$i++;
				}

				foreach (array('firstLevelReturns', 'secondLevelReturns') as $key) {
					$i = 1;
					foreach ($description[$key] as $return) {
						$opt = new Field ();
						$opt->label = $key . ' ' . $i;
						$opt->type = 'label';
						$opt->value = $return;

						$options[] = $opt;


						$opt = new Field ();
						$opt->label = 'Description for ' . $key . ' ' . $i;
						$opt->value = '';
						$opt->type = 'textfield';
						$opt->required = true;
						$opt->name = $methodName . ':' . $key . ':' . ($i - 1) . ':description';
						$opt->value = $this->getDescription($methodName, $key, $i - 1);
						$opt->valid = ($opt->value != '' ? true : false);

						$options[] = $opt;

						$i++;
					}
				}
			}

			return $options;
		}

		$services = array();

		if ($this->futil->dirExists(AE_CORE_SERVICES)) {
			$list = $this->futil->getFilesList(AE_CORE_SERVICES, true);
		}

		foreach ($list as $file) {
			if ($file['type'] != 'dir' && ($pos = strpos($file['name'], '.service.php')) !== false) {
				$serviceName = 'core/' . substr($file['name'], 0, $pos);
				$services[$serviceName] = $serviceName;
			}
		}

		if ($this->futil->dirExists(ROOT . 'app')) {
			$list = $this->futil->getFilesList(ROOT . 'app' . DS . 'services', true);
		}


		foreach ($list as $file) {
			if ($file['type'] != 'dir' && ($pos = strpos($file['name'], '.service.php')) !== false) {
				$serviceName = substr($file['name'], 0, $pos);
				$services[$serviceName] = $serviceName;
			}
		}

		$opt = new Field ();
		$opt->label = 'Service to describe';
		$opt->type = 'select';
		$opt->values = $services;
		$opt->required = true;
		$opt->name = 'service';

		$options[] = $opt;

		return $options;
	}

	function onSetParams($options) {
		if ($this->hasParam('service') || $this->hasParam('secondStep')) {
			$this->manager->retrieveOptions();
		}
		return true;
	}

	function process() {
		$service = $this->params['secondStep'];

		$service = str_replace('core/', '', $service);

		$params = array();

		foreach ($this->params as $k => $p) {
			$p = str_replace($this->_formatSearch, $this->_formatReplace, $p);

			if (strpos($k, ':')) {
				$keys = explode(':', $k);
				$firstKey = array_shift($keys);

				if (!ake($firstKey, $params)) {
					$params[$firstKey] = array();
				}
				if (count($keys) > 0) {
					$secondKey = array_shift($keys);

					if (!ake($secondKey, $params[$firstKey])) {
						$params[$firstKey][$secondKey] = array();
					}

					if (count($keys) > 0) {
						$thirdKey = array_shift($keys);

						if (count($keys) > 0) {
							$params[$firstKey][$secondKey][$thirdKey] = array();

							$fourthKey = array_shift($keys);
							$params[$firstKey][$secondKey][$thirdKey][$fourthKey] = $p;
						} else {

							$params[$firstKey][$secondKey][$thirdKey] = $p;
						}
					} else {
						$params[$firstKey][$secondKey] = $p;
					}
				}
			} else {
				$params[$k] = $p;
			}
		}

		$this->params = $params;


		foreach ($this->_methods[0] as $methodName => &$method) {
			if (is_array($this->params[$methodName])) {
				$method = array_merge_keep_structure($method, $this->params[$methodName]);
			}
		}

		$methods = array(
			'description' => $this->params['description'],
			'methods' => $this->_methods[0]
		);

		$methods = printArray($methods, "\t");

		$doc = array(
			'<h2>' . sprintf(_('%s %s service documentation'), $this->_isCore ? 'Aenoa' : Config::get(App::APP_NAME), camelize($service)) . '</h2>',
			'',
			'',
			'<p>' . $this->params['description'] . '</p>',
			'',
			''
		);

		$doc[] = '';
		$doc[] = '';
		$doc[] = '';


		foreach ($this->_methods[0] as $m => $dsc) {
			$doc[] = '<h2>Service method: ' . $m . '</h2>';
			$doc[] = '';
			$doc[] = '';
			$doc[] = '<p>' . $dsc['description'] . '</p>';
			$doc[] = '';

			if (!empty($dsc['arguments'])) {
				$doc[] = '<h3>Parameters</h3>';
				$doc[] = '';
				$doc[] = '';
				foreach ($dsc['arguments'] as $arg) {
					$doc[] = "<p><b>" . $arg['name'] . '</b> (Optional: ' . ($arg['optional'] == true ? 'yes, default value is "' . $arg['default'] . '" ' : 'no' ) . ')'
						. ' ' . $this->formatStrForDoc($arg['description']) . '</p>';
				}
				$doc[] = '';
				$doc[] = '';
			}

			$doc[] = '';
			$doc[] = '';
			$doc[] = '<h3>Returns in case of success</h3>';
			$doc[] = '';

			if (empty($dsc['firstLevelReturns']) && empty($dsc['secondLevelReturns'])) {
				$doc[] = 'This service does not return any data or failure message.';
			} else {

				if (!empty($dsc['firstLevelReturns'])) {
					foreach ($dsc['firstLevelReturns'] as $ret) {
						$doc[] = "<p><b>" . $ret['name'] . '</b> ' . $this->formatStrForDoc($ret ['description']) . '  <pre>' . $ret['value'] . '</pre></p>';
						$doc[] = '';
						$doc[] = '';
					}
				} else {
					$doc[] = '<p>Nothing is returned in case of success</p>';
				}

				$doc[] = '';
				$doc[] = '';
				$doc[] = '<h3>Returns in case of failure</h3>';
				$doc[] = '';

				if (!empty($dsc['secondLevelReturns'])) {
					foreach ($dsc['secondLevelReturns'] as $ret) {
						$doc[] = "<p><b>" . $ret['name'] . '</b> ' . $this->formatStrForDoc($ret ['description']) . '</p>';
						$doc[] = '';
						$doc[] = '';
					}
				} else {
					$doc[] = '<p>Nothing is returned in case of failure</p>';
				}
			}
		}

		$doc[] = '';
		$doc[] = '';
		$doc[] = '';
		$doc[] = '';
		$doc[] = '@see ' . camelize($service) . 'Service';



		if ($this->_isCore) {
			$f = new File(AE_CORE_SERVICES . $service . '.description.php', true);
		} else {
			$f = new File(ROOT . 'app' . DS . 'services' . DS . $service . '.description.php', true);
		}

		$c = "<?php \n/**\n * " . implode("\n * ", $doc) . "\n * \n */\n\nclass " . camelize($service) . "ServiceDescription { \n\tpublic \$generated = '" . date("F j, Y, g:i a") . "' ;\n\tpublic \$methods = " . $methods . ";\n}\n?>";
		if ($f->write($c) && $f->close()) {
			$this->view->setSuccess('Description file for service ' . $service . ' saved.');
		} else {
			$this->view->setError('Description file for service ' . $service . ' not saved.');
		}

		$this->view->setMenuItem('New service description', url() . 'CreateServiceDescription');
	}

	private function formatStrForDoc($str) {
		$str = str_replace('<br />', "\n * ", $str);
		return $str;
	}

	private function getDescription($methodName = null, $type = null, $key = null) {

		$str = '';

		if (!empty($this->_description)
			&& array_key_exists($methodName, $this->_description['methods'])
			&& array_key_exists($type, $this->_description['methods'][$methodName])
			&& array_key_exists($key, $this->_description['methods'][$methodName][$type])
			&& array_key_exists('description', $this->_description['methods'][$methodName][$type][$key])) {
			$str = $this->_description['methods'][$methodName][$type][$key]['description'];
		} else if (is_null($type)
			&& !empty($this->_description['methods'])
			&& array_key_exists($methodName, $this->_description['methods'])
			&& array_key_exists('description', $this->_description['methods'][$methodName])) {
			$str = $this->_description['methods'][$methodName]['description'];
		} else if (is_null($methodName)
			&& !empty($this->_description)
			&& array_key_exists('description', $this->_description)) {
			$str = $this->_description['description'];
		}

		return str_replace($this->_formatReplace, $this->_formatSearch, $str);
	}

}

?>