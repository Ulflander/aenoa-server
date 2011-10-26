<?php

class EHtmlElement {

	var $tokenized = false;
	var $token = '';
	var $keyword = '';
	var $rawTokenContent = '';
	var $parameters = array();
	var $values = array();
	var $multiline = false ;
	var $source = '';
	var $result = '' ;

	function isTokenized() {
		return $this->tokenized === true;
	}

	function addParam($param) {
		array_push($this->parameters, $param);
	}

	function addValue($val) {
		array_push($this->values, $val);
	}

	function render($indentation, $methods, $variables, EHtmlBase $base) {
		if ($this->isTokenized()) {
			return $this->renderTokenized($indentation, $methods, $variables, $base);
		}

		return $this->renderHTML($indentation, $methods, $variables, $base);
	}

	private function renderHTML($indentation, $methods, $variables, EHtmlBase &$base) {
		
		$res = $indentation;

		$classes = array () ;

		$styles = array() ;
		
		$attributes = '' ;
		
		$customAttr = '' ;

		$value = '' ;

		$closure = '' ;

		$res .= '<' . $this->keyword ;

		foreach ( $this->parameters as $param )
		{
			$tok = substr($param, 0, 1);
			if ( $tok == '$' )
			{
				if ( ($i = intval(substr($param, 1)) ) > 0 && count($variables) >= $i )
				{
					$param = $variables[$i-1] ;
					$tok = substr($param, 0, 1);
				}
			}
			switch ( true )
			{
				case in_array($tok , array('{','(','"')):
					$val = substr($param,1,  mb_strlen($param)-2);
					break;
				default:
					$val = substr($param, 1) ;
					break;
			}

			switch ( $tok )
			{
				// RAW value
				case '"':
					$value .= $val ;
					break;
				// CSS class
				case '.':
					$classes[] = $val ;
					break;
				// CSS inline Style
				case '%':
					$styles[] = $val ;
					break;
				// HTML Id
				case '#':
					$attributes .= ' id="' . $val . '"';
					break;
				// Raw attributes
				case '(':
					$attributes .= ' ' . $val ;
					break;
				// PHP echo tag, will defined value (and later, closure)
				case '{':
					$value .= '<?php echo ' . $val . ' ?>' ;
					break;
				// src, action, href attribute depending on main tag (script, iframe, img, a tags)
				case '@':
					$url = $val ;
					if ( strpos($val,'.') === 0 )
					{
						$val = '<?php echo url() ?>' . substr($val,1);
					}
					switch($this->keyword)
					{
						case 'script':
						case 'iframe':
							$attributes .= ' src="' . $val . '"' ;
							$closure = '</' . $this->keyword . '>' ;
							break;
						case 'a':
							$attributes .= ' href="' . $val . '"' ;
							break;
						case 'form':
							$attributes .= ' action="' . $val . '"' ;
							break;
					}
					break;
				default:
					$custom = $base->getCustomTokenResult($tok, $val, true, $this) ;
					if (!is_null($custom) )
					{
						$customAttr .= ' ' . $custom ;
					}
			}
		}

		if ( !empty ($classes) )
		{
			$attributes .= ' class="'.implode(' ', $classes) . '"' ;
		}

		if ( !empty ($styles) )
		{
			$attributes .= ' style="'.implode(' ', $styles) . '"' ;
		}
		
		$tagClosure = '>' ;
		
		if( $this->keyword == 'input' )
		{
			$tagClosure = ' />' ;
			$value = '' ;
		}
		
		if ( $value != '' )
		{
			$closure = '</'.$this->keyword.'>' ;
		}
		

		$this->result = $res . $attributes . $customAttr . $tagClosure . $value . $closure ;
		
		return $this->result ;
	}

	

	private function renderTokenized($indentation, $methods, $variables, EHtmlBase &$base ) {
		$res = $indentation;

		switch ($this->token) {

			// Method call
			case ';':
				if ( !ake($this->keyword , $methods) )
				{
					break;
				}
				$res = implode("\n",$base->renderScope($methods[$this->keyword], strlen($indentation), $this->parameters));
				break;
			// PHP statement
			case '?':
				$res .= '<?php ' . $this->rawTokenContent 
							. ($this->multiline ? "\n".$indentation : '') . ' ?>'."\n";
				break;
			// PHP echo statement
			case '!':
				$res .= '<?php echo ' . $this->rawTokenContent . ' ?>'."\n";
				break;
			// renderElement
			case '&':
				$res .= '<?php $this->renderElement(\'' . $this->rawTokenContent . '\'); ?>'."\n";
				break;
			// Close HTML tag
			case '/':
				$res .= '</' . $this->keyword . '>';
				break;
			// HTML comment statement
			case '-':
				$res .= '<!-- ' . $this->rawTokenContent
							. ($this->multiline ? "\n".$indentation : '') .'-->' ;
				break;
			// Raw content
			case '.':
				$res .= $this->rawTokenContent;
				break;
			// Javascript
			case '^':
				$res .= '<script type="text/javascript">'.$this->rawTokenContent
							. ($this->multiline ? "\n".$indentation : '') .'</script>' ;
				break;
			// Parameter of function
			case '$':
				if ( ($i = intval($this->keyword) ) > 0 && count($variables) >= $i )
				{
					$param = $variables[$i-1] ;
					$tok = substr($param, 0, 1);
					switch ( true )
					{
						case in_array($tok , array('{','(','"')):
							$val = substr($param,1,  mb_strlen($param)-2);
							break;
						default:
							$val = substr($param, 1) ;
							break;
					}

					switch ( $tok )
					{
						// PHP echo tag, will defined value (and later, closure)
						case '{':
							$val = '<?php echo ' . $val . ' ?>' ;
							break;
					}
					
					$res .= $val ;
				}
				break;
			default:
				$custom = $base->getCustomTokenResult($this->token, $this->rawTokenContent , false , $this) ;
				if (is_null($custom) )
				{
					echo '-- unknown token: ' . $this->token . ' in line ' . $this->source . '--';
				} else {
					$res .= $custom ;
				}
		}
		return $res;
	}

}

class EHtmlBase {
	const STATE_INLINE = 'inline';

	const STATE_MULTILINE = 'multiline';

	private $state;

	private $methods = array () ;

	private $customTokens = array () ;

	function __construct() {
		$this->state = self::STATE_INLINE;
	}

	function addToken($token, $callback) {
		$this->customTokens[$token] = $callback ;
	}

	function getCustomTokenResult ( $token , $value, $inline = false , $element = null )
	{
		if ( ake ( $token , $this->customTokens ) )
		{
			return $this->{$this->customTokens[$token]} ( $token, $value , $inline, $element ) ;
		}
		return null ;
	}

	function evaluate($template = 'No template given', $parameters = array()) {

		$this->state = self::STATE_INLINE;

		$this->methods = array ( ) ;

		$lines = explode("\n", str_replace('    ', "\t", $template));

		$lines = $this->clean($lines);

		$lines = $this->parseScope($lines);

		$lines = $this->extractMethods($lines);

		$lines = $this->filterLines($lines);

		$res = $this->renderScope($lines);

		return implode("\n", $res) . "\n";
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
		while ($i < $l - 1) {
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
			'lines' => array(),
			'methods' => array()
		);
		$sub = array();
		$prev = null;
		$multiline = '';
		$i = 0;

		while ($i < $l) {
			$line = $lines[$i];

			if ($this->state == self::STATE_MULTILINE) {
				if (trim($line) === '>') {
					$res['lines'][] = $multiline;
					$this->state = self::STATE_INLINE;
					$multiline = '';
				} else {
					$multiline .= "\n" . $lines[$i];
				}
				$i++;
				continue;
			}

			$s = $this->getScopeLevel($line);
			if ($s > $scope) {


				while ($i < $l && $s > $scope) {
					$line = $lines[$i];
					$s = $this->getScopeLevel($line);
					if ($s > $scope) {
						$sub[] = $line;
						$i++;
					} else {
						$i--;
						break;
					}
				}
				
				
				$res['lines'][] = $this->parseScope($sub, $scope + 1);
				if (!is_null($prev)) {
					$res['lines'][] = $prev;
				}
				$sub = array();
				$prev = null;
			} else {
				$line = trim($line);
				if (preg_match('/<$/i', $line) === 1 || preg_match('/^</i', $line) === 1 ) {
					$this->state = self::STATE_MULTILINE;
					$multiline .= preg_replace('/(<)$/i','',$line);
				} else {
					$res['lines'][] = $line;
					if (!$this->isTokenizedLine($line)) {
						$prev = '/ ' . $line;
					} else {
						$prev = null;
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

	function extractMethods(array $lines, array $methods = array()) {

		$lines = $lines['lines'];
		$l2 = array();
		$last = false;

		foreach ($lines as $line) {
			if (is_array($line)) {
				if ($last) {
					$methods[$last] = $line;
					$last = false;
					continue;
				}
				$l2[] = $this->extractMethods($line, $methods);
			} else {
				if (preg_match('/^\s{0,}:\s/i', $line) > 0) {
					$last = preg_replace('/^(\s{0,}:\s{0,})/i', '', $line);
				} else {
					$last = false;
					$l2[] = $line;
				}
			}
		}


		$this->methods = array_merge($methods, $this->methods);

		return $l2 ;
	}

	function renderScope(array $lines, $scope = 0, array $parameters = array(), array &$res = array() ) {
		
		foreach ($lines as $line) {
			if (is_array($line)) {
				$this->renderScope($line, $scope + 1, $parameters, $res);
			} else {
				$res[] = $this->renderLine($line, $scope, $this->methods , $parameters);
			}
		}

		return $res;
	}

	function filterLines ( $lines )
	{

		$lines = $this->solveLineAdds($lines);

		if ( ake('__add',$lines) )
		{
			unset ( $lines['__add'] ) ;
		}

		return $lines ;
	}

	function solveLineAdds ( $lines )
	{
		$lines2 = array () ;
		$last = null ;
		$add = '' ;
		foreach ( $lines as $line )
		{

			if (is_array($line) )
			{
				$res = $this->solveLineAdds($line) ;
				
				if ( ake ('__add', $res ) )
				{
					$last .= $res['__add'] ;
					unset($res['__add']);
					$line = $res ;
				}
				
				$line = $res ;
			} else if ( preg_match('/^\s{0,}\+\s{1,}/',$line) > 0 )
			{
				
				if ( !ake('__add',$lines2) )
				{
					$lines2['__add'] = '' ;
				}
				$lines2['__add'] .= ' ' . preg_replace('/^\s{0,}(\+)\s{1,}/','',$line);
				$line = null ;
			}

			if( !is_null($last) )
			{
				$lines2[] = $last ;
			}
			
			$last = $line ;
		}

		if( !is_null($last) )
		{
			$lines2[] = $last ;
		}

		return  $lines2 ;
	}

	function renderLine($line, $scope = 0, array $methods = array(), $parameters = array () ) {

		$ind = '';

		while ($scope-- > 0) {
			$ind .= "\t";
		}

		return $this->parseLine($line)->render($ind, $methods, $parameters, $this);
	}

	/**
	 *
	 * @param string $line
	 * @return EHtmlElement 
	 */
	function parseLine($line) {
		$line = trim($line) . ' ';

		$len = mb_strlen($line);

		$element = new EHtmlElement ();

		$element->source = $line;


		// 0=> token
		// 1=> id
		// 2=> tokenized || params
		$step = 0;

		$escaped = false;
		$escapedChar = '';
		$prev = $char = '';
		$escapes = array(
			'"' => '"',
			'\'' => '\'',
			'(' => ')',
			'{' => '}',
			'%' => ';'
		);
		$current = '';

		$step = preg_match('/^[^a-z0-9]{1,2}/i', $line) == 1 ? 0 : 1;

		for ($i = 0; $i < $len; $i++) {
			$continue = false;
			$prev = $char;
			$char = $line[$i];



			// Escape mode, we check for escape end or we continue
			if ( $step > 0 && $escaped )
			{
				$current .= $char ;
				// End of escape
				if ( $escapes[$escapedChar] == $char && $prev != '\\' )
				{

					$element->addParam($current);
					$current = '' ;
					$escaped = false ;
				}
				
			} else if ($char == ' ' || $char == "\t") {
				switch ($step) {
					case 0:
						$element->tokenized = true;
						$element->token = $current;
						preg_match_all('/^[^a-z0-9]{1,2}\s{1,}([a-z0-9]{1,})/i', $line, $m);
						if (count($m[1]) > 0) {
							$element->keyword = $m[1][0];
						}

						$element->rawTokenContent = preg_replace('/^[^a-z0-9]{1,2}/i', '', trim($line));
						$step++;
						break;
					case 1:
						$element->keyword = $current;
						break;
					default:
						$current = trim($current);
						if (preg_match('/^[^a-z0-9]{1,2}/i', $current) == 1) {
							$element->addParam($current);
						} else {
							$element->addValue($current);
						}
				}
				$step++;
				$current = '' ;
				
			} else if ( ake ($char,$escapes) )
			{
				$current .= $char ;
				$escaped = true ;
				$escapedChar = $char ;
			} else {
				$current .= $char ;
			}

			continue;
		}
		if ( strpos($element->source,"\n") !== false )
		{
			$element->multiline = true ;
		}
		return $element;
	}

	function isTokenizedLine($line) {
		preg_match('/^([^a-z0-9\s\n]{1,2}|sprintf)/i', $line, $res);
		return!empty($res);
	}

}

?>