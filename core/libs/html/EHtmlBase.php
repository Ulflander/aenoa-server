<?php

class EHtmlElement {

	var $tokenized = false;
	var $token = '';
	var $keyword = '';
	var $rawTokenContent = '';
	var $parameters = array();
	var $values = array();
	var $source = '';

	function isTokenized() {
		return $this->tokenized === true;
	}

	function addParam($param) {
		array_push($this->parameters, $param);
	}

	function addValue($val) {
		array_push($this->values, $val);
	}

	function render($indentation, $methods, $variables) {
		if ($this->isTokenized()) {
			return $this->renderTokenized($indentation, $methods, $variables);
		}

		return $this->renderHTML($indentation, $methods, $variables);
	}

	private function renderHTML($indentation, $methods, $variables) {
		$res = $indentation;

		$res .= '<' . $this->keyword;


		return $res . '>';
	}

	private function renderTokenized($indentation, $methods, $variables) {
		$res = $indentation;

		switch ($this->token) {

			// Method call
			case ';':
				$res .= 'Method ! ';
				break;
			// PHP statement
			case '?':
				$res .= '<?php ' . $this->rawTokenContent . ' ?>';
				break;
			// PHO echo statement
			case '!':
				$res .= '<?php echo ' . $this->rawTokenContent . ' ?>';
				break;
			// Close HTML tag
			case '/':
				$res .= '</' . $this->keyword . '>';
				break;
			case '-':
				$res .= '<!-- ' . $this->rawTokenContent . ' -->';
				break;
			case '.':
				$res .= $this->rawTokenContent;
				break;
			/* default:return;//vdfjs
			  case '/':
			  $res .= $this->closeLine($token['content']);
			  break;
			  case '_':
			  $res .= '<!-- ' . $token['content'] . ' -->';
			  break;
			  case '"':
			  $res .= $token['content'];
			  break;
			  case '=':
			  $res .= $this->getRawHTMLInline($token['content']);
			  break;
			  case '!':
			  preg_match_all('/^[\s]{0,}=>\s{0,1}([a-zA-Z0-9\-\_]{1,})/im', $line, $r);
			  if (count($r[1]) > 0) {
			  if (array_key_exists($r[1][0], $methods)) {
			  $res2 = array();
			  $res = implode("\n", $this->renderScope($methods[$r[1][0]], $scope + 1, array(), $res2, $methods));
			  }
			  }
			  break;
			  case '>':
			  $res .= '<?php ' . preg_replace('/^(>)/im', '', $line) . "\n" . $ind . '?>';
			  break; */
			default:
				echo '-- unknown token: ' . $this->token . ' in line ' . $this->source . '--';
		}
		return $res . "\n";
	}

}

class EHtmlBase {
	const STATE_INLINE = 'inline';

	const STATE_MULTILINE = 'multiline';

	private $state;

	function __construct() {
		$this->state = self::STATE_INLINE;
	}

	function addToken($token, $callback) {
		
	}

	function evaluate($template = 'No template given', $parameters = array()) {
		$this->state = self::STATE_INLINE;

		$lines = explode("\n", str_replace('    ', "\t", $template));

		$lines = $this->clean($lines);

		$lines = $this->parseScope($lines);

		$lines = $this->extractMethods($lines);

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
				if (trim($line) === '<') {
					echo ('<script type="text/javascript">alert("' . $multiline . '");</script>');
					$res['lines'][] = $multiline;
					$this->state = self::STATE_INLINE;
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
				if ((preg_match('/>$/i', $line) === 1 || preg_match('/^>/i', $line) === 1 ) && preg_match('/^\+/i', $line) == 0) {
					$this->state = self::STATE_MULTILINE;
					$multiline .= $line;
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
					$last = preg_replace('/^(\s{0,}=\s{0,})/i', '', $line);
				} else {
					$last = false;
					$l2[] = $line;
				}
			}
		}


		return array('lines' => $l2, 'methods' => $methods);
	}

	function renderScope(array $lines, $scope = 0, array $parameters = array(), array &$res = array(), array $methods = array()) {

		$methods = array_merge($lines['methods'], $methods);

		//	pr($methods);

		$lines = $lines['lines'];

		foreach ($lines as $line) {
			if (is_array($line)) {
				$this->renderScope($line, $scope + 1, $parameters, $res, $methods);
			} else {
				$res[] = $this->renderLine($line, $scope, $methods);
			}
		}

		return $res;
	}

	function renderLine($line, $scope = 0, array $methods = array()) {

		$ind = '';

		while ($scope-- > 0) {
			$ind .= "\t";
		}

		return $this->parseLine($line)->render($ind, $methods, array());
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

					if ( $escapedChar == '"')
					{
						$element->addValue($current);
					} else {
						$element->addParam($current);
					}
					$current = '' ;
					$escaped = false ;
				}
				
			} else if ($char == ' ' || $char == "\t") {
				switch ($step) {
					case 0:
						$element->tokenized = true;
						$element->token = $current;
						preg_match_all('/^[^a-z0-0]{1,2}\s{1,}([a-z0-9]{1,})/i', $line, $m);
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
/*
			if ($char == '{' || $char == '(') {
				$current .= $char;

				if (!$escaped) {
					$escapedChar = $char ;
					$escaped = true;
				} else {
					$current = substr($current, 0, mb_strlen($current) - 1);
					$current .= $char;
				}
				
			} else if ($char == '}' || $char == ')') {
				$current .= $char;
				if ($escaped && $prev != '\\' && ( ($char == '}' && $escapedChar == '{') || ($char == ')' && $escapedChar == '(') ) ) {
					$escaped = false;
					$current = trim($current);
					if (preg_match('/^[^a-z0-9]{1,2}/i', $current) == 1) {
						$element->addParam($current);
					} else {
						$element->addValue($current);
					}
					$escapedChar = '';
					$step++;
					$current = '';
				} else {
					$current = substr($current, 0, mb_strlen($current) - 1);
					$current .= $char;
				}
			} else if ($char == ' ' || $char == "\t") {
				switch ($step) {
					case 0:
						$element->tokenized = true;
						$element->token = $current;
						preg_match_all('/^[^a-z0-0]{1,2}\s{1,}([a-z0-9]{1,})/i', $line, $m);
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
						if ($escaped) {
							$current .= $char;
							$continue = true;
							break;
						}
						$current = trim($current);
						if (preg_match('/^[^a-z0-9]{1,2}/i', $current) == 1) {
							$element->addParam($current);
						} else {
							$element->addValue($current);
						}
				}
				if (!$continue) {
					$step++;
					$current = '';
					$escapedChar = '';
				}
				$continue = false;
				continue;
			} else if ($char == '"') {
				if (!$escaped) {
					$escaped = true;
				} else if ($escaped && $escapedChar == '"' && $prev != '\\') {
					$escaped = false;
				} else {
					$current = substr($current, 0, mb_strlen($current) - 1);
					$current .= $char;
				}
				continue;
			} else {
				$current .= $char;
			}
		}
		*/
		pr($element);

		return $element;
	}

	function isTokenizedLine($line) {
		preg_match('/^([^a-z0-9\s\n]{1,2}|sprintf)/i', $line, $res);
		return!empty($res);
	}

	/*
	  function closeLine($line) {
	  $token = $this->getLineTag($line);
	  if (!empty($token)) {
	  return '</' . $token['tag'] . '>';
	  }
	  }

	  function renderHTMLLine($tag, $line) {
	  $escaped = array();
	  $classes = array();
	  $id = '';
	  $content = '';
	  $tokenized = array();

	  $l = mb_strlen($line);

	  $line = ' ' . $line . ' ';

	  preg_match_all('/\s\.([a-zA-Z0-9\-\_]{1,})\s/i', $line, $c);
	  $classes = $c[1];

	  preg_match_all('/\s\(([^)]{1,})\)\s/i', $line, $c);
	  $escaped = $c[1];

	  preg_match_all('/\s#([a-zA-Z0-9\-\_\\\]{1,})\s/i', $line, $c);
	  $id = @$c[1][0];

	  preg_match_all('/\s_\(([^)]{1,})\)\s/i', $line, $c);
	  if (!empty($c) && count($c[0]) > 0) {
	  $content = $this->getEchoPHPInline($c[0][0]);
	  }


	  return
	  '<' . $tag
	  . ( $id && $id != '' ? ' id="' . $id . '"' : '')
	  . (count($classes) > 0 ? ' class="' . implode(' ', $classes) . '"' : '' )
	  . (count($escaped) > 0 ? ' ' . implode(' ', $escaped) : '' ) . '>'
	  . ( $content != '' ? $content . '</' . $tag . '>' : '' );
	  }


	  function getLineToken($line) {
	  preg_match('/^([^a-zA-Z0-9\s\n]{1,2}|sprintf)(.*)$/im', $line, $res);

	  if (count($res) > 1) {
	  return array('token' => $res[1], 'content' => trim($res[2]));
	  } else {
	  return array('token' => 'no-token');
	  }
	  }

	  function getLineTag($line) {
	  preg_match('/^\s{0,}([a-zA-Z0-9]{1,})\s{0,}(.{0,})/i', trim($line), $res);
	  if (count($res) > 1) {
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

	  function renderHTMLJS($line) {

	  return $line;
	  }
	 */
}

?>