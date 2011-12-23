<?php

/*
 * Class: HTTPStatus
 *
 *
 * Sends an HTTP status code.
 *
 * Must be called before any header sent.
 *
 * How to use:
 * (start code)
 * // Just status before doing a redirection
 * new HTTPStatus ( 301 ) ;
 *
 * // Send a header message
 * new HTTPStatus ( 40A , 'Unknown user' ) ;
 * (end)
 *
 *
 * 
 */
class HTTPStatus {


	protected static $headers = array (
		'100' => '100 Continue',
		'101' => '101 Switching Protocols',
		'102' => '102 Processing',
		'122' => '122 Request-URI too long',
		'200' => '200 OK',
		'201' => '201 Created',
		'202' => '202 Accepted',
		'204' => '204 No Content',
		'205' => '205 Reset Content',
		'206' => '206 Partial Content',
		'207' => '207 Multi-Status',
		'226' => '226 IM Used',
		'300' => '300 Multiple Choices',
		'301' => '301 Moved Permanently',
		'302' => '302 Found',
		'303' => '303 See Other',
		'304' => '304 Not Modified',
		'305' => '305 Use Proxy',
		'306' => '306 Switch Proxy',
		'307' => '307 Temporary Redirect',
		'400' => '400 Bad Request',
		'401' => '401 Unauthorized',
		'402' => '402 Payment Required',
		'403' => '403 Forbidden',
		'404' => '404 Not Found',
		'405' => '405 Method Not Allowed',
		'406' => '406 Not Acceptable',
		'407' => '407 Proxy Authentication Required',
		'408' => '408 Request Timeout',
		'409' => '409 Conflict',
		'410' => '410 Gone',
		'411' => '411 Length Required',
		'412' => '412 Precondition Failed',
		'413' => '413 Request Entity Too Large',
		'414' => '414 Request-URI Too Long',
		'415' => '415 Unsupported Media Type',
		'416' => '416 Requested Range Not Satisfiable',
		'417' => '417 Expectation Failed',
		'422' => '422 Unprocessable Entity',
		'423' => '423 Locked',
		'424' => '424 Failed Dependency',
		'425' => '425 Unordered Collection',
		'426' => '426 Upgrade Required',
		'500' => '500 Internal Server Error',
		'501' => '501 Not Implemented',
		'502' => '502 Bad Gateway',
		'503' => '503 Service Unavailable',
		'504' => '504 Gateway Time-out',
		'505' => '505 HTTP Version not supported',
		'507' => '507 Insufficient Storage',
		'509' => '509 Bandwidth Limit Exceeded'

	);


	public function __construct( $code, $message = null  ) {

		if ( headers_sent() == false )
		{
			header( 'Status: ' . self::$headers[strval($code)], false, $code ) ;

			if ( !is_null ( $message ) )
			{
				header ( 'X-AeServer-Message: '.$message ) ;
			}
		}
		
	}

}

?>
