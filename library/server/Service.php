<?php

abstract class Service {
    
    
    /**
     *
     * @var AenoaServerProtocol
     */
    public $protocol;
    
    protected $authRequired = false;

    public final function applyQuery(ServiceQuery $query, array $arguments = null) {
		if (!is_null($arguments)) {
			$this->beforeService();

			if ($this->authRequired === true) {
				new ServerAuthCheck ();
			}
			

			call_user_func_array(array($this, $query->serviceMethod), $arguments);

			$this->afterService();

			return true;
		}

		return false;
    }

    public function beforeService() {
	
    }

    public function afterService() {
	
    }

}

?>