<?php

/**
 * Description of AeEHtmlParser
 *
 * Get a EHtml template and returns a HTML template
 */
class EHtmlBase
{

	const STATE_INLINE = 'inline';

	const STATE_MULTILINE = 'multiline';

	private $state = 'inline';
	
	function addToken ( $token, $callback )
	{
			
	}

	function evaluate($template = 'No template given', $parameters = array())
	{
		
		$lines = explode("\n", str_replace('    ', "\t" , $template));

		$lines = $this->clean($lines);

		$lines = $this->parseScope($lines);
		
		$lines = $this->extractMethods ( $lines ) ;
		
		$res = $this->renderScope($lines);
		
		return implode("\n",$res) . "\n";
	}

	/**
	 * Clean an array of lines: strip comments (# token) and empty lines
	 * 
	 * @param array $lines 
	 */
	function clean(array $lines) {
		$l = count($lines);
		$res = array();
		$i = -1;
		while ($i < $l -1) {
			$i++;

			if (trim($lines[$i]) == '' || strpos(trim($lines[$i]), '#') === 0) {
				continue;
			}

			$res[] = $lines[$i];
		}
		return $res;
	}
	
	function parseScope(array $lines, $scope = 0) {
		
		$l = count($lines);
		$res = array(
			'lines' => array () ,
			'methods' => array () 
		);
		$sub = array();
		$prev = null ;
		$multiline = '' ;
		$i = 0;

		while ($i < $l) {
			$line = $lines[$i] ;
			
			if ( $this->state == self::STATE_MULTILINE )
			{
				if ( trim($line) === '<' )
				{
					echo ('<script type="text/javascript">alert("' . $multiline . '");</script>');
					$res['lines'][] = $multiline ;
					$this->state = self::STATE_INLINE ;
				} else {
					$multiline .= "\n" . $lines[$i] ;
				}
				$i ++;
				continue ;
			}
			
			$s = $this->getScopeLevel($line);
			if ($s > $scope) {
				while ($i < $l && $s > $scope ) {
					$line = $lines[$i] ;
					$s = $this->getScopeLevel($line);
					if ($s > $scope) {
						$sub[] = $line;
						$i++;
					} else {
						$i -- ;
						break;
					}
				}
				
				$res['lines'][] = $this->parseScope($sub, $scope + 1);
				if ( !is_null($prev) )
				{
					$res['lines'][] = $prev ;
				}
				$sub = array();
				$prev = null ;
			} else {
				$line = trim($line) ;
				if ( (preg_match('/>$/i',$line) === 1 || preg_match('/^>/i',$line) === 1 ) && preg_match('/^\+/i',$line) == 0 )
				{
					$this->state = self::STATE_MULTILINE ;
					$multiline .= $line ;
				} else {
					$res['lines'][] = $line ;
					if ( !$this->isTokenizedLine($line) )
					{
						$prev = '//'.$line;
					} else {
						$prev = null ;
					}
				}
			}
			$i++;
		}
		return $res;
	}

	function getScopeLevel($line) {
		preg_match('/^([\t]{0,})/i', $line, $r);
		if (!empty($r)) {
			return strlen($r[1]);
		} else {
			return 0;
		}
	}

	
	function extractMethods ( array $lines , array $methods = array () )
	{
		
		$lines = $lines['lines'] ;
		$l2 = array () ;
		$last = false ;
		
		foreach ($lines as $line) {
			if (is_array($line)) {
				if ( $last )
				{
					$methods[$last] = $line ;
					$last = false ;
					continue ;
				}
				$l2[] = $this->extractMethods($line , $methods );
			} else {
				if ( preg_match('/^\s{0,}=\s{0,}[^\>]/i',$line) > 0 )
				{
					$last = preg_replace('/^(\s{0,}=\s{0,})/i','',$line) ;
				} else {
					$last = false ;
					$l2[] = $line ;
				}
			}
		}
		
		
		return array ( 'lines' => $l2 , 'methods' => $methods ) ;
	}
	
	function renderScope(array $lines, $scope = 0, array $parameters = array(), array &$res = array () , array $methods = array () ) {
		
		$methods = array_merge($lines['methods'],$methods);
		
		$lines = $lines['lines'] ;
		
		foreach ($lines as $line) {
			if (is_array($line)) {
				$this->renderScope($line, $scope + 1, $parameters, $res , $methods );
			} else {
				$res[] = $this->renderLine($line, $scope , $methods);
			}
		}
		
		return $res ;
	}

	function renderLine($line, $scope = 0, array $methods = array () ) {
		
		$s = $scope ;
		$ind = '' ;
		
		while ($s -- > 0) {
			$ind .= "\t";
		}
		
		$res = $ind;
		if ( $this->isTokenizedLine($line) )
		{
			$token = $this->getLineToken($line);
			switch ($token['token']) {
				case '=':
					$res .= 'Method ! ' ;
					break;
				case 'sprintf':
				case '_(':
					$res .= $this->getEchoPHPInline($token['token'] . $token['content']);
					break;
				case '->':
					$res .= $this->getEchoPHPInline($token['content']);
					break;
				case '-':
					$res .= $this->getRawPHPInline($token['content']);
					break;
				case '//':
					$res .= $this->closeLine( $token['content'] ) ;
					break;
				case '!-':
					$res .= '<!-- ' .$token['content'] . ' -->' ;
					break;
				case '"':
					$res .= $token['content'] ;
					break;
				case '+':
					$res .= $this->getRawHTMLInline($token['content']);
					break;
				case '=>':
					preg_match_all('/^[\s]{0,}=>\s{0,1}([a-zA-Z0-9\-\_]{1,})/im',$line,$r);
					if ( count($r[1]) > 0 )
					{
						if ( array_key_exists( $r[1][0] , $methods ) )
						{
							$res2 = array() ;
							$res = implode("\n" , $this->renderScope( $methods[$r[1][0]] , $scope + 1 , array () , $res2 , $methods ) );
						}
					}
					break;
				case '>':
					$res .= '<?php ' . preg_replace('/^(>)/im', '', $line) . "\n" . $ind . '?>' ;
					break;
				default:
					$res .= '-- unknown token: ' .$token['token'] . ' in line ' . $line . '--' ;
			}
		} else {
			$tag = $this->getLineTag($line);
			if ($tag['tag'] != '') {
				
				$lines = explode("\n",$line) ;
				$firstLine = trim(array_shift($lines)) ;
				
				if ( preg_match('/>$/i', $firstLine ) === 1 )
				{
					if ( $tag['tag'] === 'script')
					{
						$res .= '<script type="text/javascript">' . "\n" ;
						$res .= "\t" . implode("\n\t",$lines) ;
						$res .= "\n" . $ind . '</script>' ;
					}
				} else {
					$res .= $this->renderHTMLLine($tag['tag'], $tag['content']) ;
				} 
			}
		}

		return $res ;
	}
	
	function closeLine ( $line )
	{
		$token = $this->getLineTag($line);
		if ( !empty($token) )
		{
			return '</' . $token['tag'] . '>' ;
		}
	}

	function renderHTMLLine($tag, $line) {
		$escaped = array();
		$classes = array();
		$id = '';
		$content = '';
		$tokenized = array();

		$l = mb_strlen($line);
		
		$line = ' ' . $line . ' ' ;

		preg_match_all('/\s\.([a-zA-Z0-9\-\_]{1,})\s/i', $line, $c);
		$classes = $c[1];

		preg_match_all('/\s\(([^)]{1,})\)\s/i', $line, $c);
		$escaped = $c[1];

		preg_match_all('/\s#([a-zA-Z0-9\-\_\\\]{1,})\s/i', $line, $c);
		$id = @$c[1][0];
		
		preg_match_all('/\s_\(([^)]{1,})\)\s/i', $line, $c);
		if ( !empty($c) && count($c[0]) > 0 )
		{
			$content = $this->getEchoPHPInline( $c[0][0] );
		}
		
		
		return
			'<' . $tag
			. ( $id && $id != '' ? ' id="' . $id . '"' : '')
			. (count($classes) > 0 ? ' class="' . implode(' ', $classes) . '"' : '' )
			. (count($escaped) > 0 ? ' ' . implode(' ', $escaped) : '' ) . '>' 
			. ( $content != '' ? $content . '</' . $tag . '>' : '' ) ;
	}
	
	function parseLine ( $line )
	{
		$line = trim($line) ;
		
		$len = mb_strlen($line) ;
		
		$result = array (
			'token' => '',
			'identifier' => '',
			'tokenized' => '',
			'parameters' => array()
		) ;
		
		// 0=> token
		// 1=> id
		// 2=> tokenized || params
		$step = 0 ;
		
		$token = 0 ;
		$id = '' ;
		$tokenized = array () ;
		$params = array () ;
		$escaped = false ;
		$prev = $char = '' ;

		$current = '' ;



		$step = preg_match('/^[^a-zA-Z0-9]{1,2}/i',$line) == 0 ? 0 : 1 ;
		
		for ( $i = 0 ; $i < $len ; $i ++ )
		{
			$prev = $char ;
			$char = $line[$i] ;
			
			if ( $line[$i] == ' ' )
			{
				switch ( $step )
				{
					case 0:
						pr('token : ' . $current ) ;
						$token = $current ;
						break;
					case 1:
						pr('id : ' . $current ) ;
						$id = $current ;
						break;
					default:
						if ( $escaped )
						{
							continue;
						}

				}
				$step ++ ;
				$current = '' ;
				continue;
			}
			
			$current .= $char ;
		}
		
		return $result ;
	}
	
	function isTokenizedLine ( $line )
	{
		preg_match('/^([^a-zA-Z0-9\s\n]{1,2}|sprintf)/i', $line, $res);
		return !empty($res) ;
	}

	function getLineToken($line) {
		preg_match('/^([^a-zA-Z0-9\s\n]{1,2}|sprintf)(.*)$/im', $line, $res);
		
		if (count($res) > 1) {
			return array( 'token' => $res[1], 'content' => trim($res[2]));
		} else {
			return array( 'token' => 'no-token' );
		}
	}

	function getLineTag($line) {
		preg_match('/^\s{0,}([a-zA-Z0-9]{1,})\s{0,}(.{0,})/i', trim($line), $res);
		if ( count($res) > 1 )
		{
			return array('tag' => $res[1], 'content' => trim($res[2]));
		}
	}

	function getRawPHPInline($line) {
		return '<?php ' . trim($line) . ' ?>';
	}

	function getEchoPHPInline($line) {
		return '<?php echo ' . trim($line) . ' ?>';
	}

	
	function getRawHTMLMultiline($lines) {
		return implode("\n", $lines);
	}

	function getRawHTMLInline($line) {
		return $line;
	}
	
	function renderHTMLJS ($line) {
		
		return $line ;
	}

}
?>
