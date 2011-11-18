<?php

/**
 * DataFilterWidget is a widget used to filter data tables in models, controllers, and views
 * 
 * 
 */
class DataFilterWidget extends Widget {

	function __construct ($options, $render, $echo)
	{

		$this->formalize ( 
			new DBField( 'structure',	DBField::TYPE_STRING ,	_('Structure') ,	DBValidator::NOT_EMPTY ) ,
			new DBField( 'table',		DBField::TYPE_STRING ,	_('Table') ,		DBValidator::NOT_EMPTY ) ,
			new DBField( 'recursivity', DBField::TYPE_INT ,		_('Recursivity') ,	DBValidator::NOT_EMPTY )
		) ;

		parent::__construct($options, $render, $echo) ;
		
		$this->setFile( uncamelize(get_class($this)) . '.thtml' ) ;
	}
	
	
	function render ( $echo )
	{
		parent::render($echo) ;
	}
}

?>
