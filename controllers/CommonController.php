<?php

class CommonController extends Controller{
	
	
	
	
	function email ( )
	{
		if ( AenoaRights::hasRightsOnQuery('common/email' ) == false )
		{
			App::do401 ('Query failure') ;
		}
		
		if ( true !== $this->validateInputs ( array (
				'common/contact/sender_email' => DBValidator::EMAIL,
				'common/contact/content' => DBValidator::NOT_EMPTY,
				'common/contact/name' => DBValidator::NOT_EMPTY
			) ) )
		{
			$this->addResponse(_('Some errors has been found. Please fill correctly the contact form.'), self::RESPONSE_ERROR ) ;
		} else {
			
			$l = "Send Mail:\n" ;
			$l .= "From: " . $this->data['common/contact/sender_email'] . "\n" ;
			$l .= "Message: " . $this->data['common/contact/content'] . "\n" ;
			$l .= "Date: " . date('Y/m/d h:i:s',time()). "\n" ;
			
			Log::wlog( $l ) ;
			
			
			
			$to = Config::has(App::APP_EMAIL) ? Config::get(App::APP_EMAIL) : Config::get(App::APP_EMAIL) ;
			
			$content = sprintf(_('Hello, %s') , $to ) . '<br /><br />';
			$content .= sprintf(_('A new message has been sent from application %s') , Config::get(App::APP_NAME) ) . '<br /><br />';
			$content .= sprintf(_('From: %s, %s') , $this->data['common/contact/name'], $this->data['common/contact/sender_email'] ) . '<br /><br />' ;
			$content .= sprintf(_('Website: %s') , @$this->data['common/contact/website'] ) . '<br /><br />' ;
			$content .= sprintf(_('Content: %s') , @$this->data['common/contact/content'] ) . '<br /><br />' ;
			
			$mailer = new AeMail () ;
			
			if ( $mailer->sendThis (
				array ( 
					'to' => $to ,
					'subject' => _('Aenoa Server contact form'),
					'content' => $content ,
				)
			) )
			{		
				$this->data['common/contact/content'] = '';
				
				$this->addResponse(_('Thank you. Your mail has been sent.'), self::RESPONSE_SUCCESS ) ;
			} else {
				$this->addResponse(_('A failure occured during mail sending process. We apologize for the inconvenience.'), self::RESPONSE_ERROR ) ;
			}
		}
		
		$this->createView ('html/widget-render.thtml') ;
		
		$this->view->set('widgetName', 'contact-form') ;
		
		$this->view->set('mode', 'email');
		
		if ( !empty($this->data) )
		{
			$this->view->set('widgetOptions', array (
				'subject' => @$this->data['common/contact/subject'],
				'sender_email' => $this->data['common/contact/sender_email'],
				'name' => $this->data['common/contact/name'],
				'content' => $this->data['common/contact/content'],
				'website' => $this->data['common/contact/website']
			)) ;
		} else {
			$this->view->set('widgetOptions', array () ) ;
		}
	}
	
	
	function feedback ( )
	{
		$this->email () ;
		$this->view->set('mode', 'feedback');
	}
	
	function confirm ( $image = false )
	{
		if ( $image !== false && $image == 'getCaptcha' )
		{
			$captcha = new AeCaptcha () ;
			
			App::noCache () ;
			
			App::$session->set('confirmCaptcha',strtoupper($captcha->getCode ())) ;
			
			App::end(false);
			
			$captcha->render () ;
			
			return;
		}
		
		if ( !empty($this->data) && array_key_exists('confirm/result', $this->data ) && array_key_exists('confirm/captcha', $this->data ) )
		{
			if ( strtoupper($this->data['confirm/result']) != App::$session->get('confirmCode') || strtoupper($this->data['confirm/captcha']) != App::$session->get('confirmCaptcha') )
			{
				App::$session->set('confirmStep',App::$session->get('confirmStep')+1 ) ;
				
				if ( App::$session->get('confirmStep') > 2 )
				{
					App::$session->set('confirmStep',0);
					App::isBot() ;
					return;
				}
				
			} else {
				App::isNotBot() ;
				return;
			}
		} else {
			App::$session->set('confirmStep', 0 ) ;
		}
		
		
		$letter = chr(rand(65,90)) ;
		$n1 = rand ( 2, 8 ) ;
		$n2 = rand ( 2, 8 ) ;
		
		App::$session->set('confirmCode',$letter.($n1+$n2) ) ;
		
		$this->createView ('html/widget-render.thtml') ;
			
		$this->view->set('widgetName', 'confirm-action') ;
		$this->view->set('widgetOptions', array ());
	
		$this->view->set('step' , App::$session->get('confirmStep') ) ;
		$this->view->set('letter' , $letter ) ;
		$this->view->set('num_1' , $n1 );
		$this->view->set('num_2' , $n2 );
		
	}
	
	
	
	function upload ( $dbid, $table, $field , $id = null )
	{
		if ( $dbid != 'main' )
		{
			$this->db = App::getDatabase($dbid); 
		}
		
		if ( is_null($this->db) )
		{
			App::do500('Structure does not exists');
		}
	
		if ( $this->db->tableExists($table) == false )
		{
			App::do500('Table does not exists');
		}
		
		$struct = $this->db->getStructure() ;
		$fieldStruct = array () ;
		foreach ( $struct[$table] as $f )
		{
			if ( $f['name'] == $field && $f['type'] == DBSchema::TYPE_FILE )
			{
				$fieldStruct = $f ;
				break;
			}
		}
		
		if ( empty($fieldStruct) )
		{
			App::do500('Field does not exists');
		}
		
		$id = $dbid.'/'.$table.'/'.$field ;
		
		if(!empty($_FILES))
		{
			$upload = new AeUpload () ;
			
			if ( ake('requirements',$fieldStruct) )
			{
				$req = $fieldStruct['requirements'] ;
				if ( ake('mimetypes',$req) )
				{
					if ( $req['mimetypes'] == 'webimage' )
					{
						$upload->requireWebImage() ;
					} else {
						$upload->setRequiredTypes($req['mimetypes']);
					}
				}
				if ( ake('minSize',$req) )
				{
					$upload->setMinImageSize($req['minSize']) ;
				}
				if ( ake('maxSize',$req) )
				{
					$upload->setMaxImageSize($req['maxSize']) ;
				}
				if ( ake('filesize',$req) )
				{
					$upload->setMaxUploadSize($req['filesize']) ;
				}
				
				if ( ake('convert_webimage', $fieldStruct) )
				{
					$upload->conversions = $fieldStruct['convert_webimage'];
				}
			}
			
			if ( ake('auto_rename',$fieldStruct) && $fieldStruct['auto_rename'] === true )
			{
				$upload->renameTo( sha1 ( $dbid . '_' . $table . '_' . time () ) ) ;
			}
			
			
			
			if ( $upload->process ( 'upload/'.$id.'/1' ) )
			{
				$this->view->set('uploadedFile',$upload->getPath() ) ;
				
				$this->addResponse(_('File uploaded')) ;
			} else {
				$this->addResponse(sprintf(_('File not uploaded: %s'),$upload->getError ()), self::RESPONSE_ERROR) ;
			}
		}
		
		$this->view->useLayout = true ;
		
		$this->view->layoutName = 'upload' ;
		
		$this->view->set('id',$id) ;
	}
	
	
}
