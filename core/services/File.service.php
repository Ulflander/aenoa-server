<?php

class FileService extends Service {
	
	
	public function getList ( $path = '.' )
	{
		$futil = new FSUtil ( Config::get ( App::APP_PUBLIC_REPOSITORY ) ) ;
		
		if ( $futil->dirExists ( Config::get ( App::APP_PUBLIC_REPOSITORY ) . $path ) == false )
		{
			
		}
		
		$this->protocol->addData ( 'list' , 
			$futil->getFilesList ( Config::get ( App::APP_PUBLIC_REPOSITORY ) . $path , false)
		) ;
	}
	

	public function downloadFile ( $path = '.' , $fileContent = null )
	{
	}
}


?>