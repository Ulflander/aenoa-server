<?php

abstract class Routine {
	
	abstract function apply ( AnalysisController &$controller, &$XMLSource , &$target , &$mediaSource ) ;
	
}

?>