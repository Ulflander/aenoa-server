<?php

class Widget extends Options {

	protected $file = null;

	function __construct($options, $render = true, $echo = true) {

		$this->setFile(AE_TEMPLATES . 'widgets' . DS . uncamelize(get_class($this)) . 'thtml');

		$this->setAll($options);

		if ($render) {
			$this->render($echo);
		}
		
		$this->formalize ( 
			new DBField( 'before',		DBField::TYPE_STRING ) ,
			new DBField( 'after',		DBField::TYPE_STRING ) ,
			new DBField( 'class',		DBField::TYPE_STRING ) ,
			new DBField( 'subclass',	DBField::TYPE_STRING ) ,
			new DBField( 'tag',			DBField::TYPE_INT ) ,
			new DBField( 'subtag',		DBField::TYPE_INT )
		) ;
		
	}

	/**
	 * Abstract to method to be overidden in concrete Widget classe
	 */
	function check() {
		
	}

	function getFile() {
		return $this->file;
	}

	function setFile($file) {
		if ($this->exists($file)) {
			$this->file = $file;
		}
	}

	function exists($file = null) {
		if (is_null($file)) {
			$file = $this->file;
		}

		return App::$futil->fileExists($file);
	}

	function render($echo = true) {
		$result = '';

		if (is_null($this->file)) {
			App::do500(_('Template file for widget not found'), __FILE__, __LINE__);
		} else if (!$this->validate()) {
			App::do500(_('Widget options not validated'), __FILE__, __LINE__, $this->getAll() );
		}
		
		extract ($this->getAll());
		
		require ($this->file) ;
	}

}

?>
