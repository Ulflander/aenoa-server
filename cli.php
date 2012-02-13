<?php

function arguments($args ) {
    $ret = array(
        'exec'      => '',
        'options'   => array(),
        'flags'     => array(),
        'arguments' => array(),
    );

    $ret['exec'] = array_shift( $args );

    while (($arg = array_shift($args)) != NULL) {
        // Is it a option? (prefixed with --)
        if ( substr($arg, 0, 2) === '--' ) {
            $option = substr($arg, 2);

            // is it the syntax '--option=argument'?
            if (strpos($option,'=') !== FALSE)
                array_push( $ret['options'], explode('=', $option, 2) );
            else
                array_push( $ret['options'], $option );

            continue;
        }

        // Is it a flag or a serial of flags? (prefixed with -)
        if ( substr( $arg, 0, 1 ) === '-' ) {
            for ($i = 1; isset($arg[$i]) ; $i++)
                $ret['flags'][] = $arg[$i];

            continue;
        }

        // finally, it is not option, nor flag
        $ret['arguments'][] = $arg;
        continue;
    }
    return $ret;
}//function arguments


function err ( $error )
{
	echo "" ;
	exit(1);
}

if (PHP_SAPI === 'cli')
{

	$args = arguments($argv)  ;
	$args = $args['arguments'] ;
	require_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'bootstrap.php') ;

	switch ( $args[0] )
	{
		case 'CSS':
			$compressor = new AeCSSCompressor () ;
			if (!is_file($args[1]))
			{
				err ( 'CSS File does not exist' ) ;
				exit (1) ;
			}
			$css = $compressor->compress($args[1], true) ;
			$f = new File ( $args[2] , true ) ;
			if ( !$f->write( $css ) || !$f->close () )
			{
				err('Resulting CSS File not written') ;
			}
			break;
	}

	print_r( "Done.\n");

} else {
	die ('<h2>Forbidden</h2>') ;
}
?>