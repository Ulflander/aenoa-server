<?php

class TemplateRenderingService extends Service {

    function beforeService()
    {
	parent::beforeService();
	
	
	if ( Config::get(App::USER_CORE_SYSTEM) !== true )
	{
		App::do500(_('Attempt to use Aenoa core user login service'), _('Core user login service is not available')) ;
	} else if ( Config::get(App::API_REQUIRE_KEY) !== true )
	{
		App::do500(_('Attempt to use Aenoa core user login service'), _('Core API keys management service is not available')) ;
	}

	$this->db = App::getDatabase() ;
		
	$this->authRequired = true ;
	
    }
    
    function getElement( $element, $userId = null )
    {
	$tpl = new Template () ;
	
	$user = App::getUser() ;
	
	if ( !is_null($userId) )
	{
	    $dbuser = $this->db->findFirst ('ae_users', array ('id'=>$userId) ) ;
	    
	    if ( !$dbuser || empty ( $dbuser ) )
	    {
		$this->protocol->addError('User not valid') ;
	    }
	    
	    $user->login ( $dbuser['email'] , $dbuser['password'] ) ;
	}
	
	
	$tpl->setAll( array (
	    
	    'user_object' => $user,
	    'user_super' => $user->isLevel(0)
		
	));
	
	
	ob_start() ;
	
	$tpl->renderElement($element) ;
	
	$result = ob_get_contents();
	
	ob_end_clean() ;
	
	$this->protocol->addData('element' , $result ) ;
    }
}

?>