<?php


class JSGetterSetter extends Task {
	
	
	function getOptions ()
	{
		$opt = new Field () ;
		$opt->name = 'variables' ;
		$opt->type = 'textfield' ;
		$opt->label = 'Add variables names (lcfirst & camelized)' ;
		$opt->required = true ;
		
		
		return array ( &$opt ) ;
	}
	
	
	function process ()
	{
		
		$vars = explode("\n" , $this->params['variables'] ) ;
		$res = array() ;
		
		foreach ( $vars as $var )
		{
			
			$var = trim($var);
			if (strpos($var,',') )
			{
				$c = explode(',',$var);
				$var = trim($c[0]) ;
				$comment = trim($c[1]) ;
			} else {
				$comment = '...' ;
			}
			
			
			$priv = '_' . $var ;
			$getter = 'get' . ucfirst($var) ;
			$setter = 'set' . ucfirst($var) ;
			
			$res[] = '' ;
			$res[] = '/*' ;
			$res[] = "\tFunction: " . $setter ;
			$res[] = "\t" ;
			$res[] = "\tSet ".lcfirst($comment) ;
			$res[] = "\t" ;
			$res[] = "\tParameters:" ;
			$res[] = "\t\t" . $var . ' - ' . ucfirst($comment);;
			$res[] = "\t" ;
			$res[] = "\tReturns:" ;
			$res[] = "\tCurrent instance for chained commands on this element";
			$res[] = '*/' ;
			$res[] = $setter .': function ( '.$var.' )' ;
			$res[] = '{' ;
			$res[] = "\tthis._$var = $var ;" ;
			$res[] = "\treturn this ;" ;
			$res[] = '},' ;
			
			$res[] = '' ;
			$res[] = '/*' ;
			$res[] = "\tFunction: " . $getter ;
			$res[] = "\t" ;
			$res[] = "\tGet ".lcfirst($comment) ;
			$res[] = "\t" ;
			$res[] = "\tReturns:" ;
			$res[] = "\t" . ucfirst($comment);
			$res[] = '*/' ;
			$res[] = $getter .': function ()' ;
			$res[] = '{' ;
			$res[] = "\treturn this._$var ;" ;
			$res[] = '},' ;
			
			$res[] = '' ;
		}
		
		die('<pre>'.implode("\n",$res).'</pre>');
		
		$this->view->setStatus ( 'JS code:<br/><pre class="code">'.implode("\n",$res).'</pre>' ) ;
		
		return false ;
	}
}
?>