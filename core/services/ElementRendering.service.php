<?php

class ElementRenderingService extends Service {

    public function beforeService()
    {
	parent::beforeService();
	
	//$this->authRequired = true ;
    }
    
    public function element($element)
    {
	$this->addData('element' , 'yo') ;
	
	
    }

}

?>