<?php

class EhtmlToThtml extends Task {



	function beforeEnd() {
		
	}

	function getOptions ()
	{

		$opt = new Field () ;
		$opt->label = 'Convert core Aenoa Server files too' ;
		$opt->name = 'core' ;
		$opt->type = 'radio' ;
		$opt->values = array ( 'yes' => 'Yes' , 'no' => 'No' ) ;
		$opt->required = true ;
		$opt->value='no' ;

		$opt2 = new Field () ;
		$opt2->setupConfirm ( 'Confirm' ) ;

		$opt3 = new Field () ;
		$opt3->type = 'label';
		$opt3->value = 'This task will OVERWRITE ALL existing .thtml files by the newer version of .ehtml files. Always consider backup first.';

		return array ( $opt3 , $opt2, $opt ) ;
	}

	function process ()
	{
		$files = array () ;

		if ( $this->params['core'] == 'yes' )
		{
			$files = $this->futil->getTree(AE_TEMPLATES);
		}


		$files = array_merge($files,$this->futil->getTree(AE_APP_TEMPLATES));
		$toTransform = array () ;
		foreach ( $files as $file )
		{
			$l = strlen($file) ;
			if ( strpos($file,'.ehtml') === $l - 6)
			{
				array_push($toTransform, $file); 
			}
		}


		$ehtml = new AeEHtml() ;
		$count = 0 ;
		foreach( $toTransform as $file )
		{
			$l = strlen($file);
			$res = $ehtml->fromFileToFile($file, substr($file, 0, $l - 6 ) . '.thtml' ) ;

			if( !$res )
			{
				$this->view->setError('File ' . $file . ' not converted.' ) ;
			} else {
				$count ++ ;
			}
		}

		if ( $count == count($toTransform) )
		{
			$this->view->setSuccess('All files have been converted.');
		} else {
			$this->view->setError(count($toTransform)-$count . ' files have NOT been converted. Consider write files rights.');
		}
	}
}

?>
