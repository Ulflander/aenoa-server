<?php

class HTMLBehavior extends Behavior {

	/**
	 * Create id, name, value part of a form field
	 * @param unknown_type $id
	 * @param unknown_type $data
	 * return string 
	 */
	public static function fieldValueAndID($id, &$data = array()) {
		$str = 'id="' . $id . '" name = "' . $id . '" ';

		if (array_key_exists($id, $data)) {
			$str .= 'value="' . $data[$id] . '" ';
		}

		return $str;
	}

	public function field($type, $id = '', $class = '', $placeholder = '', $requiredText = '', $validation = '', $value = '', $attributes = array()) {
		$str = ($id != '' ? ' id="' . $id . '" name="' . $id . '"' : '' ) . $this->extractAttributes($attributes);

		$data = &$this->_parent->get('input_data');

		if (array_key_exists($id, $data)) {
			$value = $data[$id];
		}

		if ($requiredText != '') {
			$str .= ' placeholder="' . $placeholder . '" required="required"';
		}

		if ($class != '') {
			$str .= ' class="' . $class . '"';
		}

		if ($validation != '') {
			$str .= ' pattern="' . $validation . '"';
		}

		if ($type == 'textarea') {
			return '<textarea ' . $str . '>' . $value . '</textarea>';
		}

		return '<input type="' . $type . '" ' . $str . ' value="' . $value . '" />';
	}

	/**
	 * Creates a Javascript redirection to an URL
	 * 
	 * @param object $url The URL to go to
	 * @param object $delay [optional] The delay before redirection
	 * @return 
	 */
	function redirect($url, $delay = 4000) {
		$str = '<script type="text/javascript">';
		if ($delay > 0) {
			$str .= 'self.setTimeout("self.location.href = \'' . $url . '\';",' . $delay . ') ;';
		} else {
			$str .= 'self.location.href = \'' . $url . '\';';
		}
		$str .= '</script>';

		if ($this->_parent->isRendered() == true) {
			echo $str;
			flush();
		} else {
			$this->_parent->vars['content_for_layout'] .= $str;
		}
	}

	/**
	 * Append content to an HTML container by rendering an element.
	 * 
	 * Use it only with HTML : if view is rendered, then content is appended using javascript.
	 * 
	 * @param object $container The ID of the container in HTML page
	 * @param object $element The element file to render
	 * @return 
	 */
	function append($container, $element) {
		if ($this->_parent->isRendered() == true) {
			$str = '<script type="text/javascript">';
			$str .= 'window.document.getElementById("' . $container . '").innerHTML = \'';
			ob_start();
			$this->_parent->renderElement($element);
			$s = ob_get_contents();
			ob_end_clean();
			$scripts = $this->cleanAndReturnScripts($s);
			$str .= $this->cleanContent($this->cleanScripts($s));
			$str .= '\' + window.document.getElementById("' . $container . '").innerHTML; ' . $scripts . '</script>';

			echo $str;
			flush();
		} else {
			ob_start();
			$this->_parent->renderElement($element);
			$str = $this->cleanContent(ob_get_contents());
			ob_end_clean();

			$this->_parent->vars['content_for_layout'] = $str . $this->_parent->vars['content_for_layout'];
		}
	}

	/**
	 * Append HTML content to an HTML container.
	 * 
	 * Use it only with HTML : if view is rendered, then content is appended using Javascript.
	 * 
	 * @param object $container The ID of the container in HTML page
	 * @param object $content The HTML content to append
	 * @return 
	 */
	function appendContent($container, $content) {
		$str = '<script type="text/javascript">';
		$scripts = $this->cleanAndReturnScripts($content);
		$str .= 'window.document.getElementById("' . $container . '").innerHTML = \'';
		$str .= $this->cleanContent($this->cleanScripts($content));
		$str .= '\' + window.document.getElementById("' . $container . '").innerHTML ;';
		$str .= $scripts . '</script>';

		if ($this->_parent->isRendered() == true) {
			echo $str;
			flush();
		} else {
			$this->_parent->vars['content_for_layout'] = $str . $this->_parent->vars['content_for_layout'];
		}
	}

	/**
	 * Append HTML content to an HTML container.
	 * 
	 * Use it only with HTML : if view is rendered, then content is appended using Javascript.
	 * 
	 * @param object $container The ID of the container in HTML page
	 * @param object $content The HTML content to append
	 * @return 
	 */
	function appendScript($container, $content) {
		$str = '<script type="text/javascript">' . $content . '</script>';

		if ($this->_parent->isRendered() == true) {
			echo $str;
			flush();
		} else {
			$this->_parent->vars['content_for_layout'] = $str . $this->_parent->vars['content_for_layout'];
		}
	}

	/**
	 * Extract the attributes to make html attrs from a key=>val array
	 * 
	 * @param object $attributes
	 * @return 
	 */
	public function extractAttributes($attributes) {
		if (is_array($attributes) == false) {
			return '';
		}

		$str = '';
		foreach ($attributes as $k => $v) {
			$str .= ' ' . $k . '="' . $v . '"';
		}
		return $str;
	}

	/**
	 * Clean \n \r and \r\n in a string 
	 * 
	 * @param string $content
	 * @return Clean string
	 */
	public function cleanContent($content) {
		return str_replace(array("\n", "\r", "\r\n", "\t", "'"), array('', '', '', '', '\\\''), $content);
	}

	public function cleanScripts($content) {
		return preg_replace('/<script[^>]*>[^<]*<\/script>/', '', $content);
	}

	public function cleanAndReturnScripts($content) {
		preg_match_all('/<script[^>]*>([^<]*)<\/script>/'
			, $content, $scripts, PREG_PATTERN_ORDER);


		$script = '';
		if (count($scripts) > 1) {
			foreach ($scripts[1] as $value) {
				$script .= $value . ";\n";
			}
		}
		return $script;
	}

	public function getField($db, $table, $fieldname, $url = null, $data = array(), $container = true , $label = true , $field = true , $desc = true) {
		$_db = App::getDatabase($db);

		if (!$_db) {
			trigger_error('Database ' . $db . ' does not exists', E_USER_ERROR);
		}

		$struct = $_db->getStructure();

		if (!ake($table, $struct)) {
			trigger_error('Table ' . $table . '  does not exists', E_USER_ERROR);
		}

		$finalStructure = null;

		foreach ($struct[$table] as $_field) {
			if ($_field['name'] == $fieldname) {
				$finalStructure = array($_field);
				break;
			}
		}


		if (is_null($finalStructure)) {
			trigger_error('Structure for field ' . $fieldname . '  does not exists', E_USER_ERROR);
		}

		if (is_null($url)) {
			$url = url();
		}

		$form = new AeAutoForm();

		$form->fieldsOnly = true;

		if ($form->setDatabase($db, $table, $finalStructure)) {
			$form->renderContainer = $container ;
			$form->renderLabel = $label ;
			$form->renderField = $field ;
			$form->renderDescription = $desc ;
			return $form->build($data, array(), $url, true, array());
		}

		return 'Error';
	}

	public function getPickInField($db, $table, $fieldname, $pickin = false, $url = null, $data = array(), $container = true , $label = true , $field = true , $desc = true ) {
		$_db = App::getDatabase($db);

		if (!$_db) {
			trigger_error('Database ' . $db . ' does not exists', E_USER_ERROR);
		}

		$struct = $_db->getStructure();
		$field = null;

		if (!ake($table, $struct)) {
			trigger_error('Table ' . $table . '  does not exists', E_USER_ERROR);
		}

		$primary = $_db->getTableSchema($table)->getPrimary();
		foreach ($struct[$table] as $f) {
			if ($f['name'] == $fieldname) {
				$field = $f;
			}
		}

		if (is_null($field)) {
			trigger_error('Structure for field ' . $fieldname . '  does not exists', E_USER_ERROR);
		}

		$structure = array(array(
				'name' => 'from_' . $fieldname,
				'label' => $field['label'],
				'type' => DBSchema::TYPE_TEXT,
				'behavior' => $pickin ? DBSchema::BHR_PICK_IN : DBSchema::BHR_PICK_ONE,
				'source' => $table,
				'source-main-field' => $fieldname,
				'validation' => array(
					'rule' => DBValidator::NOT_EMPTY,
					'message' => _('Please fill this field'),
				)
			)
		);



		if (is_null($url)) {
			$url = url();
		}

		$form = new AeAutoForm();

		
		$form->fieldsOnly = true;

		if ($form->setDatabase($db, $table, $structure)) {
			
			$form->renderContainer = $container ;
			$form->renderLabel = $label ;
			$form->renderField = $field ;
			$form->renderDescription = $desc ;
			return $form->build($data, array(), $url, true, array());
		}

		return 'Error';
	}

}

?>
