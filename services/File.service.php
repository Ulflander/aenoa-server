<?php

class FileService extends Service {
	
	
	public function getList ( $path = '.' )
	{
		$futil = new FSUtil ( Config::get ( App::APP_PUBLIC_REPOSITORY ) ) ;
		
		if ( $futil->dirExists ( Config::get ( App::APP_PUBLIC_REPOSITORY ) . $path ) == false )
		{
			$this->protocol->setFailure(_('Public file repository does not exists'));
			return;
		}
		
		$this->protocol->addData ( 'list' , 
			$futil->getFilesList ( Config::get ( App::APP_PUBLIC_REPOSITORY ) . $path , false)
		) ;
	}
	
}


?>