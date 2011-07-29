<?php


class UpdatePostProcess {
	
	
	function __construct ( Controller &$updateController , $fromVersion, $toVersion )
	{
		
		
		$updateController->_sendMessage (sprintf(_('Aenoa Server is initing post process from version %s to version %s'),$fromVersion,$toVersion),'notice') ;
		
		$futil = new FSUtil(ROOT) ;
		$futil2 = new FSUtil(AE_SERVER) ;
		
		$error = false ;
			
		/**
		 * Compose with first version of Aenoa Server : all apps required folders where in project root (templates, structures...).
		 * Now everything should be in an "app" folder 
		 */
		if ( version_comp($fromVersion,'1-0-3') < 1 )
		{
			$updateController->_sendMessage (sprintf(_('Post process for version %s to %s '),$fromVersion,'1-0-3'),'notice') ;
			
			if ( $futil2->dirExists('core'.DS.'tmp') )
			{
				$futil2->removeDir('core'.DS.'tmp') ;
			}
			
			if ( !$futil->dirExists('app') )
			{
				$futil->createDir(ROOT,'app');
			}
			
			$dirsToMove = array ('controllers','locale','services','structures','templates','webpages');
		
			foreach($dirsToMove as $dir)
			{
				if ( $futil->dirExists($dir) )
				{
					if ( !$futil->moveDir($dir,ROOT.'app'.DS.$dir) )
					{
						$error = true ;
					}
				}
			}
			if ($error)
			{
				$updateController->_sendMessage (_('Post process failed'),'error') ;
			} else {
				$updateController->_sendMessage (_('Post process success'),'success') ;
			}
		}
		
		$error = false ;
		
		$updateController->_sendMessage (sprintf(_('Aenoa Server is deploying required elements'),$fromVersion,$toVersion),'notice') ;
		
		
	}
	
}
?>