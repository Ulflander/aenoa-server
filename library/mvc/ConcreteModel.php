<?php

/**
 * Class: ConcreteModel
 * 
 * This version of model implements functionnalities of the future of Aenoa Server models.
 * 
 * For now it's extended from <Model> class, but will finally be merged with <Model>.
 * 
 * If you wish to test this new kind of model, just extend your own models from <ConcreteModel> rather than from <Model>
 * 
 */
class ConcreteModel extends Model {
	
	static private $methods = array (
		'find',
		'findAll',
		'findFirst',
		'findAndOrder',
		'findRandom',
		'findAscendants',
		'findAndRelatives',
		'findRelatives',
		'edit',
		'editAll',
		'add',
		'addAll',
		'count',
		'lastId',
		'newId',
		'delete',
		'deleteAll'
	) ;
	
	final function __call($name, $arguments) {
		
		if ( in_array ( $name , self::$methods) )
		{
			array_unshift( $arguments , $this->table ) ;
			
			return call_user_method_array($name, $this->db, $arguments ) ;
		}
		
		throw new ErrorException('Method ' . $name . ' does not exist in class ' . get_class($this) );
	}
	
	
}
?>
