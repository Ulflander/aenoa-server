<?php


class BehaviorableObject {
	
	private $_behaviors = array () ;
	
	function addBehavior ( $behavior )
	{
		if ( !array_key_exists($behavior, $this->_behaviors ) )
		{
			
			$class = $behavior.'Behavior' ;
			
			
			if ( !class_exists($class) )
			{
				if ( is_file(AE_BEHAVIORS.$class.'.php') )
				{
					require_once (AE_BEHAVIORS.$class.'.php') ;
				} else if ( is_file (ROOT.'templates'.DS.'behaviors'.DS.$class.'.php'))
				{
					require_once (ROOT.'templates'.DS.'behaviors'.DS.$class.'.php') ;
				}
			}
			
			if ( class_exists($class) )
			{
				$this->_behaviors[$behavior] = new $class ( $this ) ;
			}
		}
	}
	
	function __call($name, $arguments) {
		foreach ( $this->_behaviors as $b )
		{
			if ( method_exists($b, $name) )
			{
				return call_user_func_array(array($b,$name), $arguments);
			}
		}
		
		trigger_error('Function ' . $name . ' does not exists in BehaviorableObject.' ) ;
    }
	
}

?>