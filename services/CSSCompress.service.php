<?php

class CSSCompressService extends Service {
	
	public function compress ( $cssString )
	{
		$this->protocol->addData ( 'css_string' , AeCSSCompressor::compressString ( $cssString ) ) ; 
		$this->protocol->addData ( 'len_before' ,  AeCSSCompressor::getLenBefore () ) ;
		$this->protocol->addData ( 'len_after' ,  AeCSSCompressor::getLenAfter () ) ;
		$this->protocol->addData ( 'report' ,  AeCSSCompressor::getReport () ) ;
	}
	
	
}


?>