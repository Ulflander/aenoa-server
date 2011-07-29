<?php

class SocialService extends Service {
	
	function getShareService ( $pageTitle , $pageURI, $pageExcerpt = null )
	{
		$this->protocol->addData ( 'share_services' , AeSocialShare::mapShares( $pageTitle , $pageURI, $pageExcerpt ) ) ;
	}
}

?>