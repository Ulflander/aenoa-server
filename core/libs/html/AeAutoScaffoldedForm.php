<?php



class AeAutoScaffoldedForm extends AeAutoForm {
	

	function getVal ( $val, $field )
	{
		$key = $this->_dbID . '/' . $this->_table . '/' . $field['name'] ;
		return '<?php echo (ake(\'' . $key . '\',$data) ? $data[\'' . $key . '\'] : \'\' ) ?>';
	}
	
}


?>