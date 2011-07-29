<?php

/**
 * Aenoa Server
 * 
 *
 * @category 		core.libs.common
 * @author 			Xavier Laumonier <xav@xav-blog.com>
 * @copyright 		Copyright (c) 2008-2009, Xavier Laumonier-
 * @since			Zen v0.1 - Aenoa Server v1
 * @version			1.0
 */
 


/**
 * ZEN MAILER
 * 
 * Uses core PHP "mail" function
 * 
 * Use XMail as you want, see examples below.
 * 
 * Create a new mailer with
 * (start code)
 * $mailer = new AeMail () ;
 * (end)
 *
 * Use it by defining an array of the mail properties:
 * (start code)
 * $mail = array (
 *					'to' => 'mail@domain.ext' ,
 *						// OR array ( 'mail@domain.ext' , 'mail@domain.ext' , 'mail@domain.ext' , ... ) 
 *						// OR user_array ( 'id' => int , 'email' => 'mail@domain.ext' , ... )
 *					'content' => 'the content of your mail' ,
 *					'subject' => 'the subject of your mail' ,
 *		(optional)	'database_template' => array ( 'template_model' => 'modelAlias' , 'template_model_id' => int ) 
 *		(optional)	'language' => 'lang_code',
 *		(optional)	'template' => array ( 'vars' => array( 'foo' => 'bar' ) , 'file' => 'dir/file.thtml' )
 *		(optional)	'from' => 'mail@domain.ext',
 *		(optional)	'receipt' => 'mail@domain.ext',
 *		(optional)	'attachements' => array ( 'dir/file.ext','dir/file.ext','dir/file.ext' )
 *		(optional)	'cc' => 'mail@domain.ext' ,
 *						// OR array ( 'mail@domain.ext' , 'mail@domain.ext' , 'mail@domain.ext' , ... )
 *		(optional)	'bcc' => 'mail@domain.ext' ,
 *						// OR array ( 'mail@domain.ext' , 'mail@domain.ext' , 'mail@domain.ext' , ... )
 *		(optional)	'replyTo' => 'mail@domain.ext' ,
 *		(optional)	'return' => 'mail@domain.ext' ,
 *		(optional)	'charset' => 'encoding_charset'
 *		(optional)	'sendAs' => 'text'/'html'/'both' 
 *		(optional)	'useCache' => 'true'/'false' 
 *						// if set to false, view or db template will be rendered for each mail sended
 *		(optional)	'vars' => array ( 'aVar' => 'value of aVar' , 'otherVar' => 'other value' ) 
 *						// replace {aVar} in the content and if content is a view file, replace $aVar too
 * 		) ;
 * $mailer->sendThis ( $mail ) ;
 * (end)
 *
 *
 *
 *
 * You can create complex array with many mails :
 *
 * (start code)
 * $mails = array ( 
 *				array (
 *					'to' => 'mail@domain.ext', 
 *							// OR array ( 'mail@domain.ext' , 'mail@domain.ext' , 'mail@domain.ext' , ... ) 
 *							// OR user_array ( 'id' => int , 'email' => 'mail@domain.ext' , ... )
 *					'content' => 'the content of your mail'
 *					'subject' => 'the subject of your mail'
 *				) , 
 *				array (
 *					'to' => 'anybody@anydomain.any'
 *					'subject' => 'the subject of your other mail'
 *					'content' => 'the content of your other mail'
 *				)
 * 		) ;
 * $mailer->sendThis ( $mails ) ;
 * (end)
 *
 *
 *
 *
 * You can use it by defining XMailComponent properties :
 * 
 * (start code)
 * $mailer->to = 'mail@domain.ext' ;
 * 				// OR array ( 'mail@domain.ext' , 'mail@domain.ext' , 'mail@domain.ext' , ... ) 
 * 				// OR user_array ( 'id' => int , 'email' => 'mail@domain.ext' , ... )
 * $mailer->content = 'the content of your mail' 
 * $mailer->database_template = ...
 * $mailer->language = ...
 * $mailer->template = ...
 * $mailer->from = ...
 * $mailer->subject = ...
 * $mailer->cc = ...
 * $mailer->bcc = ...
 * $mailer->receipt = ...
 * $mailer->attachements = ...
 * $mailer->replyTo = ...
 * $mailer->charset = ...
 * $mailer->sendAs = ...
 * $mailer->vars = ...
 * $mailer->useCache = ...
 * $mailer->send () ;
 * 		// OR $mailer->send ( $to = 'mail@domain.ext' OR array ( 'mail@domain.ext' , 'mail@domain.ext' , 'mail@domain.ext' , ... ) 
 * 		// OR user_array ( 'id' => int , 'email' => 'mail@domain.ext' , ... ) )
 * (end)
 *
 *
 *
 *
 * Or use both array and properties systems :
 *
 * (start code)
 *
 * $html_content = 'an <b>HTML</b> <em>content</em>' ;
 * $plain_text_content = 'a plain text content' ;
 *
 * $mailer->subject = 'the subject of your mail' 
 * $mailer->from = 'you@yourdomain.ext' 
 * $mails = array () ;
 * foreach ( $users as $user )
 * {
 *	if ( $user['acceptHtmlMail'] == true )
 *	{
 *    $mails[] = array ( 'to' => $object['email'] , 'content' => $html_content , 'sendAs' => 'both' ) ;
 * 	} else {
 *    $mails[] = array ( 'to' => $object['email'] , 'content' => $plain_text_content , 'sendAs' => 'plain' ) ;
 * 	}
 * }
 * $mailer->sendThis ( $mails ) ;
 * (end)
 *
 */
class AeMail
{
	private $version = '0.9' ;

	var $charset = 'utf-8' ;
	
	var $domain = null ;
	
	var $abuse = null ;
	
	var $pushSmtpUser = null ;
	
	var $from = null ;
	
	var $return = null ;
	
	var $cc = null ;
	
	var $bcc = null ;
	
	var $subject = null ;
	
	var $content = null ;
	
	var $replyTo = null ;
	
	var $receipt = null ;
	
	var $language = null ;
	
	var $sendAs = 'both' ;
	
	// custom user headers
	var $headers = array () ;
	
	var $attachments = array() ;
	
	var $attachPath = null ;
	
	var $vars = array() ;
	
	var $database_template = array () ;
	
	var $template = array () ;
	
	var $server = 'PHPMAILSERVER' ;
	
	var $ip = null ;
	
	var $degradedMsg = null ;
	
	// All email adresses are validated by the CakePHP Validation class.
	// The 'email' function of Validation class lets check email host.
	// You can set here if you want to check host.
	var $checkEmailHost = false ;
	
	// Use or not the special based-64 subject encode
	var $encodeSubject = true ;
	
	// Use or not the special based-64 content encode
	var $encodeContent = false ;
	
	// Render or not DB templates and view content for each mail sended
	var $useCache = true ;
	
	// as per RFC2822 Section 2.1.1
	var $lineLength = 255;
	
	var $log = array () ;
	
	var $_eol = null ;
	
	var $_errors = array () ;
	
	var $emailViewPath = '' ;
	
	private $__mailer = 'Aenoa Server Mail Component' ;
	
	private $__current = array () ;
	
	// Initialize the component
	function __construct() {
	
		$this->resetCfg () ;
		
		if ( strtoupper ( substr(PHP_OS,0,3) == 'WIN' ) )
		{
	      $this->_eol = "\r\n" ;
	    } else 
		if ( strtoupper( substr(PHP_OS,0,3) == 'MAC' ) )
		{
	      $this->_eol = "\n" ;
	    } else {
	      $this->_eol = "\n" ;
	    }
		
		
		$this->degradedMsg = u( _( '[ This message should be viewed in HTML. This is a degraded version ! ]' ) ) ;
		
	}
	
	// First send access, send based on the array given
	function sendThis ( $mailOrMailsArray )
	{
		$this->__current = array () ;
		if ( $this->isMail ( $mailOrMailsArray ) )
		{
			$this->__current = $mailOrMailsArray ;
			$this->__complete () ;
			$result = $this->__send () ;
			$this->__saveLog () ;
			return $result ;
		} else if ( is_array ( $mailOrMailsArray ) )
		{
			$no_errors = true ;
			foreach ( $mailOrMailsArray as $mail ) 
			{
				$this->__current = $mail ;
				$this->__complete () ;
				if ( !$this->__send () && $no_errors == true )
				{
					$no_errors = false ;
				}
			}
			$this->__saveLog () ;
			return $no_errors ;
		} else {
			$this->__saveLog () ;
			return false ;
		}
	}
	
	
	// Second send access, send based on component properties
	function send ( $to = null )
	{
		$this->__current = array () ;
		$mail = $this->getCurrent () ;
		
		if ( !is_null ( $to ) )
		{
			if ( !$this->isValidTo ( $to ) )
			{
				return false ;
			} else {
				$mail['to'] = $to ;
			}
		}
		
		$this->__current = $mail ;
		$result = $this->__send () ;
		$this->__saveLog () ;
		return $result ;
	}
	
	// Return current internal mail structure
	function getCurrent ()
	{
		$mail = array () ;
		$mail['to'] = $this->to ;
		$mail['from'] = $this->from ;
		$mail['subject'] = $this->subject ;
		$mail['content'] = $this->content ;
		$mail['attachements'] = $this->attachements ;
		$mail['attachPath'] = $this->attachPath ;
		$mail['vars'] = $this->vars ;
		$mail['language'] = $this->language ;
		$mail['cc'] = $this->cc ;
		$mail['bcc'] = $this->bcc ;
		$mail['replyTo'] = $this->replyTo ;
		$mail['receipt'] = $this->receipt ;
		$mail['return'] = $this->return ;
		$mail['charset'] = $this->charset ;
		$mail['sendAs'] = $this->sendAs ;
		$mail['useCache'] = $this->useCache ;
		$mail['database_template'] = $this->database_template ;
		$mail['template'] = $this->template ;
		$mail['headers'] = $this->headers ;
		
		return $mail ;
	}
	
	// Reset current internal mail structure
	function reset ()
	{
		$this->to = null ;
		$this->from = null ;
		$this->subject = null ;
		$this->content = null ;
		$this->attachements = array () ;
		$this->attachPath = null ;
		$this->vars = array () ;
		$this->language = null ;
		$this->cc = array () ;
		$this->bcc = array () ;
		$this->replyTo = null ;
		$this->receipt = null ;
		$this->return = null ;
		$this->charset = 'utf-8' ;
		$this->sendAs = 'both' ;
		$this->useCache = true ;
		$this->database_template = array () ;
		$this->template = array () ;
		$this->headers = array () ;
		$this->__current = array () ;
		$this->log = array () ;
		$this->resetCfg () ;
	}
	// Reset current internal mail structure
	function resetCfg ()
	{
		
		if (Config::has(App::APP_ENCODING)) {
			$this->charset = Config::get(App::APP_ENCODING);
		}
		
		if (Config::has(App::APP_LANG)) {
			$this->language = Config::get(App::APP_LANG);
		}
		
		if (Config::has(App::APP_EMAIL)) {
			$this->from = Config::get(App::APP_EMAIL);
		} else 
		if (Config::has(App::MAILER_EMAIL)) {
			$this->from = Config::get(App::MAILER_EMAIL);
		}
		
		if (Config::has(App::MAILER_RETURN_TO)) {
			$this->return = Config::get(App::MAILER_RETURN_TO);
		}
		
		
		if ( Config::get(App::MAILER_SEND_IP) === true && !empty ( $_SERVER["REMOTE_ADDR"] ) ) {
			$this->ip = $_SERVER["REMOTE_ADDR"] ;
		}
		
		if ( Config::has(App::MAILER_DOMAIN) ) {
			$this->domain = Config::get(App::MAILER_DOMAIN) ;
		}
		
		if ( Config::has(App::MAILER_ABUSE) ) {
			$this->abuse = Config::get(App::MAILER_ABUSE) ;
		}
		
		if ( Config::has(App::MAILER_SMTP_USER) ) {
			$this->pushSmtpUser = Config::get(App::MAILER_SMTP_USER) ;
		}
	}
		
	
	// Log errors or successes
	private function __log ( $report , $success = null )
	{
		if ( $success === false )
		{
			$prefix = '[ERROR][MAIL] ' ;
		} else if ( $success === true ) {
			$prefix = '[SUCCESS][MAIL] ' ;
		} else {
			$prefix = '[INFO][MAIL] ' ;
		}
		if ( !empty ( $this->__current ) )
		{
			$this->log[] = $prefix . ' :: Reports : [' . implode ( '][' , $report ) . '] :: Mail : ' . @$this->__current['to'] . ', ' . @$this->__current['subject'] . ' :: ' . date ( 'Y/m/d G:i:s' ) . '@' . $this->server . '/' . $this->ip ;
		} else {
			$this->log[] = $prefix . ' :: Reports : [' . implode ( '][' , $report ) . '] :: ' . date ( 'Y/m/d G:i:s' ) . '@' . $this->server . '/' . $this->ip ;
		}
	}
	
	private function __saveLog ()
	{
		Log::wlog ( implode ( "\n" , $this->log ) );
	}
	
	function getLog ()
	{
		return implode ( $this->_eol . '<br />' , $this->log ) ;
	}
	
	
	// First step : send mail one per one to each adressee
	private function __send ()
	{
		if ( $this->isMail ( $this->__current ) )
		{
			if ( is_null ( $this->__current['to'] ) && is_null ( $this->to ) )
			{
				$this->__log ( array ( 'no destination (property "to") for the mail' ) , false ) ;
				return false ;
			}
			
			
			if ( empty ( $this->__current['to'] ) )
			{
				$this->__current['to'] = $this->to ;
			}
			
			$to = $this->__current['to'] ;
			
			
			if ( is_array ( $to ) && !$this->isUser ( $to ) )
			{
				$no_errors = true ;
				foreach ( $to as $unique_to )
				{
					$this->__current['to'] = $unique_to ;
					$this->__log ( array ( 'Try to send mail to :' . $this->__current['to'] . ' (mass adressees mode)' ) ) ;
					if ( ( ( $this->isValidTo ( $unique_to ) && !$this->__mail () ) || !$this->isValidTo ( $unique_to ) ) && $no_errors = true )
					{
						$no_errors = false ;
					}
				}
				return $no_errors ;
			} else {
				$this->__log ( array ( 'Try to send mail to :' . $this->__current['to'] ) ) ;
				return $this->__mail () ;
			}
		}		
		$this->__log ( array ( 'Given mail has not recognized subject and content. Bad email.' ) ) ;
		return false ;
	}
	
	private function __encodeSubject ()
	{
		//$subject = base64_encode($this->__current['subject']) ;
		//$subject = $this->__wrap ( $subject , 40 ) ;
		//$this->__current['encoded_subject'] = '=?' . $this->charset . '?B?' . str_replace ( $this->_eol , '?=' . "\r\n" . '=?' . $this->charset . '?B?' , $subject ) . '?=' ;
		$this->__current['encoded_subject'] = '=?' . $this->charset . '?B?' . base64_encode($this->__current['subject']) . '?=' ;
	}
	
	// In case of use of both array and component properties system, complete the email structure array with the component properties
	private function __complete ()
	{
		$toComplete = array ( 'subject' , 'content' , 'from' , 'return' , 'receipt' , 'replyTo' , 'cc' , 'bcc' , 'charset' , 'sendAs' , 'useCache' , 'database_template' , 'template' ) ;
		foreach ( $toComplete as $varname )
		{
			if ( empty ( $this->__current[$varname] ) && property_exists ( $this , $varname ) && ( !is_null ( $this->$varname ) || !empty ( $this->$varname ) ) )
			{
				$this->__current[$varname] = $this->$varname ;
			}
		}
		
		if ( empty ( $this->__current['replyTo'] ) && !empty ( $this->__current['from'] ) )
		{
			$this->__current['replyTo'] = $this->__current['from'] ;
		}
	}
	
	
	// Final function mail
	private function __mail ()
	{
		
		if ( !$this->__build () )
		{
			return false ;
		}
		
		if ( $this->encodeSubject === true )
		{
			$this->__encodeSubject () ;
		}
		
		if ( $this->encodeSubject == false )
		{
			$subject = $this->__current['subject'] ;
		} else {
			$subject = $this->__current['encoded_subject'] ;
		}
		
		
		$result = mail ( $this->__current['to'] , $subject , $this->__current['final_content'] , $this->__current['headers'] ) ;
		
		if ( !$result )
		{
			$this->__log ( array ( 'Mail not sended by server {' . $this->server . '/' . $this->ip . '}' ) , false ) ;
		} else {
			$this->__log ( array ( 'Mail sended' ) , true ) ;
		}
		
		$this->__boundary = null ;
		
		return $result ;
	}
	
	private function __build ()
	{
		if ( $this->__current['sendAs'] != 'text' && $this->__current['sendAs'] != 'both' && $this->__current['sendAs'] != 'html' )
		{
			$this->__log ( array ( 'Send type instruction not good, it should be array $this->__current ( "sendAs" => "text"/"html"/"both" ) not found' ) , false ) ;
			return false ;
		}
		
		$this->__boundary = $this->__boundary () ;
		
		$headers =  $this->__createHeaders () ;
		
		$content = array () ;
		
		if ( !empty ( $this->__current['attachments'] ) )
		{
			$headers [] = 'Content-Type: multipart/mixed;' . $this->_eol . "\t" . 'boundary="' . $this->__boundary . '"' ;
			$headers [] = 'This part of the E-mail should never be seen. If' ;
			$headers [] = 'you are reading this, consider upgrading your e-mail' ;
			$headers [] = 'client to a MIME-compatible client.' ;
		} elseif ( $this->__current['sendAs'] === 'text' )
		{
			$headers [] = 'Content-Type: text/plain; charset=' . $this->charset ;
		} elseif ( $this->__current['sendAs'] === 'html')
		{
			$headers [] = 'Content-Type: text/html; charset=' . $this->charset ;
		} elseif ( $this->__current['sendAs'] === 'both')
		{
			$headers [] = 'Content-Type: multipart/alternative;' . $this->_eol . "\t" . 'boundary="' . $this->__boundary . '"';
		}
		
		if ( $this->encodeContent == true )
		{
			$headers [] = 'Content-Transfer-Encoding: base64';
		} else {
			$headers [] = 'Content-Transfer-Encoding: 7bit';
		}
		
		//$headers [] = 'Content-Disposition: inline;' ;
		
		if ( $this->__current['sendAs'] == 'both' && !empty ( $this->__current['attachments'] ) )
		{
			$boundary = $this->__boundary () ;
			$headers[] = '' ;
			$headers[] = '--' . $this->__boundary ;
			$headers[] = 'Content-Type: multipart/alternative; boundary=' . $boundary . '';
		} else {
			$boundary = $this->__boundary ;
		}
		
		$this->__current['headers'] = implode ( $this->_eol , $headers ) ;
		
		$this->__processContent () ;
		
		if ( $this->__current['sendAs'] == 'text' || $this->__current['sendAs'] == 'both' )
		{
			if ( !empty ( $this->__current['clean'] ) )
			{
				if ( $this->__current['sendAs'] == 'both' || !empty ( $this->__current['attachments'] ) )
				{
					$content[] = '--' . $boundary ;
					if ( $this->encodeContent == true )
					{
						$content[] = 'Content-Type: text/plain;' ;
						$content[] = 'Content-Transfer-Encoding: base64' ;
					} else {
						$content[] = 'Content-Type: text/plain; charset=' . $this->charset ;
						$content[] = 'Content-Transfer-Encoding: 7bit' ;
					}
				}
				$content[] = '' ;
				$content[] = $this->processVars ( $this->__current['clean'] ) ;
				$content[] = '' ;
			} else {
				$this->__log ( array ( 'Try to send as plain "text", but no plain data found for content' ) , false ) ;
			}
		}
		
		if ( $this->__current['sendAs'] == 'html' || $this->__current['sendAs'] == 'both' )
		{
			if ( !empty ( $this->__current['html'] ) )
			{
				if ( $this->__current['sendAs'] == 'both' || !empty ( $this->__current['attachments'] ) )
				{
					$content[] = '--' . $boundary ;
					if ( $this->encodeContent == true )
					{
						$content[] = 'Content-Type: text/html;' ;
						$content[] = 'Content-Transfer-Encoding: base64' ;
					} else {
						$content[] = 'Content-Type: text/html; charset=' . $this->charset;
						$content[] = 'Content-Transfer-Encoding: 7bit';
					}
				}
				$content[] = '' ;
				$content[] = $this->processVars ( $this->__current['html'] ) ;
				$content[] = '' ;
			} else {
				$this->__log ( array ( 'Try to send as "html", but no html data found for content' ) , false ) ;
			}
		}
		
		if ( $this->__current['sendAs'] == 'both' )
		{
			$content[] = '--' . $boundary . '--' ;
			$content[] = '' ;
		}

		$this->__current['final_content'] = implode ( $this->_eol , $content ) ;
		
		if ( !empty ( $this->__current['attachments'] ) )
		{
			$files = $this->__attachFiles () ; 
			$this->__current['final_content'] .= $files ;
		}
		
		return true ;
	}
	
	// Attach files - pretty the same of Cake PHP Mail component one
	private function __attachFiles ()
	{
		$content = '' ;
		
		if ( is_array ( $this->__current['attachments'] ) && !empty ( $this->__current['attachments'] ) )
		{
			$files = array();
			foreach ($this->__current['attachments'] as $attachment)
			{
				if ( !is_null ( $this->attachPath ) )
				{
					if ( file_exists($this->attachPath.$attachment) )
					{
						$files[] = $this->attachPath . $attachment ;
					} else {
						$this->__log ( array ( 'Attachment file not found :: ' . $this->attachPath . $attachment ) , false ) ;
					}
				} else {
					if ( file_exists( WWW_ROOT . $attachment) )
					{
						$files[] = WWW_ROOT . $attachment;
					} else {
						$this->__log ( array ( 'Attachment file not found :: ' . WWW_ROOT . $attachment ) , false ) ;
					}
				}
			}
			
			foreach ($files as $file) 
			{
				$mime = $this->getMimeType ( $file ) ;
				if ( !is_null ( $mime ) )
				{
					$handle = fopen($file, 'rb');
					$data = fread($handle, filesize($file));
					$data = chunk_split( base64_encode($data) , $this->lineLength , $this->_eol ) ;
					fclose($handle);

					$content .= '' . $this->_eol;
					$content .= '--' . $this->__boundary . $this->_eol;
					$content .= 'Content-Type: ' . $mime . $this->_eol;
					$content .= 'Content-Transfer-Encoding: base64' . $this->_eol;
					$content .= 'Content-Disposition: attachment; filename="' . basename($file) . '"' . $this->_eol;
					$content .= '' . $this->_eol;
					$content .= $data . $this->_eol;
				}
			}
			
			$content .= '--' . $this->__boundary . '--' . $this->_eol;
		}
		
		return $content ;
	}
	// Returns all headers of the mail
	private function __createHeaders ()
	{
		$headers = array () ;
		//$headers [] = $this->__createUserHeader ( $this->__current['to'] , 'To' ) ;	
		$headers [] = $this->__createUserHeader ( @$this->__current['from'] , 'From' ) ;
		$headers [] = $this->__createUserHeader ( @$this->__current['replyTo'] , 'Reply-To' ) ;
		//$headers [] = $this->__createUserHeader ( @$this->__current['receipt'] , 'Disposition-Notification-To' ) ;
		$headers [] = $this->__createMultiUserHeader ( @$this->__current['cc'] , 'cc' ) ;
		$headers [] = $this->__createMultiUserHeader ( @$this->__current['bcc'] , 'Bcc' ) ;
		
	    $headers [] = 'Return-Path: <'.$this->__current['return'].'>' ;
		$headers [] = 'Mime-Version: 1.0' ;
	    $headers [] = 'Message-Id: <'.mktime().'.'.md5(rand(1000,9999)).'@aenoa-systems.com>' ;
	    $headers [] = 'Date: ' . date("r") ;
		
		if ( !is_null( $this->ip ) )
		{
		//	$headers [] = 'Sender-IP: '.$this->ip ;
		}
		
	    // $headers [] = 'X-Mailer: '. $this->__mailer . ' ' . $this->version ;
		
		if ( !is_null ( $this->domain ) )
		{
		//	$headers [] = 'X-Sender: '.$this->domain ;
		}
		if ( !is_null ( $this->pushSmtpUser ) )
		{
		//	$headers [] = 'X-auth-smtp-user: '.$this->pushSmtpUser ;
		}
		if ( !is_null ( $this->abuse ) )
		{
		//	$headers [] = 'X-abuse-contact: '.$this->abuse ; 
		}
		
		if ( !empty( $this->__current['headers'] ) && is_array ( $this->__current['headers'] ) ) {
			foreach ( $this->__current['headers'] as $key => $val ) {
				$headers [] = 'X-' . $key . ': ' . $val ;
			}
		}
		
		array_clean ( $headers ) ;
		
		return $headers ;
	}


	// Returns content of the mail
	private function __processContent ()
	{
		if ( $this->__current['useCache'] == true && !empty ( $this->__current['html'] ) && !empty ( $this->__current['clean'] ) )
		{
			return;
		}
		
		/*
		 * OLD ZEN CODE :: todo : recreate functions templates
		if ( !empty ( $this->__current['database_template'] ) )
		{
			$this->__current['html'] = $this->__formatEOLs ( $this->__renderDBTemplate () ) ;
			$this->__current['clean'] = $this->__cleanHtml ( $this->__current['html'] ) ;
		} else*/ if ( !empty ( $this->__current['template'] ) )
		{
			$this->__current['html'] = $this->__formatEOLs ( $this->__renderTemplate () ) ;
		} else {
			$this->__current['html'] = $this->__formatEOLs ( $this->__current['content'] ) ;
		}
		
		$this->__current['clean'] = $this->__cleanHtml ( $this->__current['html'] ) ;
		
		if ( $this->encodeContent == true )
		{
			$this->__current['clean'] = $this->__wrap ( base64_encode( $this->__current['clean'] ) ) ;
		} else {
			$this->__current['clean'] = $this->__wrap ( $this->__current['clean'] ) ;
		}
		if ( $this->encodeContent == true )
		{
			$this->__current['html'] = $this->__wrap ( base64_encode( $this->__current['html'] ) ) ;
		} else {
			$this->__current['html'] = $this->__wrap ( $this->__current['html'] ) ;
		}
	}
	
	

	function getMimeType($attachment){
		$nameArray=explode('.',basename($attachment));
		switch(strtolower($nameArray[count($nameArray)-1]))
		{
			case 'jpg':
				$mimeType='image/jpeg';
			break;
			case 'jpeg':
				$mimeType='image/jpeg';
			break;
			case 'gif':
				$mimeType='image/gif';
			break;
			case 'txt':
				$mimeType='text/plain';
			break;
			case 'pdf':
				$mimeType='application/pdf';
			break;
			case 'csv';
				$mimeType='text/csv';
			break;
			case 'html':
				$mimeType='text/html';
			break;
			case 'htm':
				$mimeType='text/html';
			break;
			case 'xml':
				$mimeType='text/xml';
			break;
			default:
				$mimeType=null;
			break;
		}
		return $mimeType;
	} 
	
	
	function __renderTemplate ()
	{
		$template = new Template () ;
		$template->setMode('email') ;
		$template->setFile( $this->__current['template']['file'] );
		$template->setAll(array_key_exists( 'vars', $this->__current['template']) ? $this->__current['template']['vars'] : array ()) ;
		
		return $template->render(false) ;
	}
	
	
	/**
	 * Wrap the message using EmailComponent::$lineLength - from cakePHP
	 *
	 * @param string $message Message to wrap
	 * @return array Wrapped message
	 * @access private
	 */
	function __wrap ( $text , $len = null )
	{
		if ( is_null ( $len ) || $len > $this->lineLength )
		{
			$len = $this->lineLength ;
		}
		
		$text = str_replace(array('\r\n','\r'), '\n', $text);
		$lines = explode('\n', $text);
		$formatted = array();

		foreach ($lines as $line)
		{
			if(substr($line, 0, 1) == '.')
			{
				$line = '.' . $line;
			}
			$formatted = array_merge($formatted, explode('\n', wordwrap($line, $len , '\n', true)));
		}
		return implode ( $this->_eol , $formatted );
	}
	
	// Clean HTML tags
	private function __cleanHtml ( $htmlText )
	{
		$htmlText = preg_replace ( '`<a href="mailto:(.*?)".*?>[[:space:]]*(.*?)[[:space:]]*</a>`si', $this->_eol . '\2 : \1' . $this->_eol , $htmlText);
		$htmlText = preg_replace ( '`<a href="(.*?)".*?>[[:space:]]*(.*?)[[:space:]]*</a>`si', $this->_eol . '\2 : \1' . $this->_eol , $htmlText);
		$htmlText = preg_replace ( '`<br{0,2}>`si' , $this->_eol , $htmlText ) ;
		$htmlText = preg_replace ( '`<[table|p|td|div](?:[[:space:]]).*?>`si' , $this->_eol , $htmlText ) ;
		$htmlText = html_entity_decode ( $htmlText , ENT_QUOTES , $this->charset ) ;
		return implode ( $this->_eol , array_map ( array ( $this , '__cleanLine' ) , explode ( $this->_eol , strip_tags( $htmlText ) ) ) ) ;
	}
	
	private function __cleanLine ( $value )
	{
		return trim ( $value ) ;
	}
	
	// Format EOLs
	private function __formatEOLs ( $text ) 
	{
		$replacements = array ( 
			"\n" => $this->_eol ,
			"\r\n" => $this->_eol ,
			"\r" => $this->_eol
		) ;
		return str_replace ( array_keys ( $replacements ) , array_values ( $replacements ) , $text ) ;
	}
	
	
	// Create a header like 'Header: username <email>'
	private function __createUserHeader( $user , $header )
	{
		if ( !is_null ( $user ) && !empty ( $user ) )
		{
			$header = $header .': ' ;
			if ( $this->isUser ( $user ) )
			{
				$header .= '<' . $user['email'] . '>' ;
			} else {
				$header .= $this->__formatAddress ( $user ) ;
			}
			return $header ;
		}
		return '' ;
	}
	
	
	// Same as previous, but works with many adressees (usefull for cc and Bcc)
	private function __createMultiUserHeader( $users , $header )
	{
		if ( !is_null ( $users ) && is_array ( $users ) )
		{
			return $header . ': ' . implode(', ', array_map(array($this, '__formatAddress'), $users ) ) . $this->_eol ;
		} else if ( !is_null ( $users ) )
		{
			return $this->__createUserHeader ( $users , $header ) ;
		}
		return '';
	}
	
	// Format an email address like 'username <email>'
	private function __formatAddress ( $address )
	{
		if (strpos($address, '<') === false ) {
			return '<'.$address.'>' ;
		}
		return $address ;
	}
	
	// Mime encode of contents
	function __encode($subject) {
		return mb_encode_mimeheader ( $subject , $this->charset , 'B' , $this->_eol );
	}
	
	
	// Generate a boundary
	function __boundary () {
		return 'aenoa_mimepart_' . md5(uniqid(time()));
	}
	
	
	// Check if the basic mail structure exists
	function isMail ( $mail = null )
	{
		if ( is_null ( $mail ) )
		{
			$mail = $this->__current ;
		}
		if ( !empty ( $mail['subject'] ) || !empty ( $mail['content'] ) || !empty ( $mail['database_template'] ) || !empty ( $mail['template'] ) )
		{
			return true ;
		}
		$this->__log ( array ( 'Invalid email structure' ) , false ) ;
		return false ;
	}

	// Check if the 'to' part is valid
	function isValidTo ( $to )
	{
		// user data
		if ( $this->isUser ( $to ) )
		{
			return true ;
		} else if ( is_array ( $to ) )
		{
			$error = false ;
			foreach ( $to as $_to )
			{
				if ( !$this->isValidEmailAddress ( $_to ) && $error == false )
				{
					$error = true ;
				}
			}
			return $error ;
		} else if ( $this->isValidEmailAddress ( $to ) )
		{
			return true ;
		}
		return false ;
	}

	// Check if the array could be a database user array
	function isUser ( $array )
	{
		if ( is_array ( $array ) && !empty ( $array['email'] ) )
		{
			return true ;
		}
		return false ;
	}
	

	// Validation of an email address
	function isValidEmailAddress ( $email = null )
	{
		$m = array () ;
		if ( is_string($email) )
		{
			preg_match('/'.DBValidator::EMAIL.'/',$email,$m);
		}
		return !empty($m) && !empty($m[0]) ;
	}
	
	
	// Fill {var} vars in the content with $mail['vars'] vars
	function processVars ( $subject )
	{
		if ( !empty ( $this->__current['vars'] ) )
		{
			$search = explode( ',' , '{' . implode ( '},{' , array_keys( $this->__current['vars'] ) ) .'}' ) ;
			return str_replace( $search , array_values( $this->__current['vars'] ) , $subject);
		}
		
		return $subject ;
	}
	
	
	
	
	
	
	
}

?>