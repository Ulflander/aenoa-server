<?php

class FileController extends Controller{

	
	
	protected $_file ;
	
	protected function edit ( $file = null, $url = null )
	{
		
		$this->createView('html'.DS.'file'.DS.'edit.thtml') ;
		
		if ( is_null($file) && is_null($url) )
		{
			App::do500('FileController: File or base url not given');
		}
		
		$f = new File(ROOT.$file);
		
		
		if ( $f->exists() )
		{
		
			if ( !empty($this->data ) && ake('file/content', $this->data) )
			{
 				if ( $f->write($this->data['file/content']) )
 				{
					$this->addResponse(sprintf( _('File %s saved'), $file) ) ;
 				} else {
 					$this->addResponse(sprintf( _('File NOT %s saved'), $file), self::RESPONSE_WARNING ) ;
 				}
				$this->view->set('content', $this->data['file/content'] ) ;
			} else {
				$this->view->set('content', $f->read() ) ;
			} 
		
		
			$this->view->set('filename', $file ) ; 
			
			$this->view->set('url', setTrailingSlash($url));
			$f->close () ;
		} else {
			App::do500('FileController: File does not exists / ' . $file);
		}
		
	}
	
	
}
	