<?php

/**
 * EHtmlBase parses an EHtml string and returns an HTML/PHP string
 * 
 */
class EHtmlBase extends AeObject {
	
	/**
	 * 
	 */
	const STATE_INLINE = 'inline';

	const STATE_MULTILINE = 'multiline';

	private $state;
	private $methods = array();
	private $customTokens = array();
	protected $dependencies = '';

	function __construct() {
		$this->state = self::STATE_INLINE;
		
		if ( !defined ('DS') )
		{
			define ( 'DS' , DIRECTORY_SEPARATOR ) ;
		}
		
	}

	function addToken($token, $callback) {
		$this->customTokens[$token] = $callback;
	}

	function getCustomTokenResult($token, $value, $inline = false, $element = null) {
		if (ake($token, $this->customTokens)) {
			return $this->{$this->customTokens[$token]}($token, $value, $inline, $element);
		}
		return null;
	}

	function evaluate($template = 'No template given', $parameters = array(), $dependencies = '') {
		if (substr(trim($template), 0, 1) === '%%') {
			return '';
		}
		
		$this->dependencies = rtrim($dependencies, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

		$this->state = self::STATE_INLINE;

		$this->methods = array();

		$lines = explode("\n", str_replace('    ', "\t", $template));

		$lines = $this->clean($lines);

		$lines = $this->includeDependencies($lines);

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

		$res2 = array();
		$prev = '';
		foreach ($res as $line) {
			if (preg_match('/^\s{0,}\+\s{1,}/', $line) > 0) {
				$prev .= ' ' . trim(preg_replace('/^(\s{0,}\+\s{1,})/', '', $line));
				continue;
			}

			$res2[] = $prev;
			$prev = $line;
		}

		$res2[] = $prev;

		return $res2;
	}

	function includeDependencies($lines) {
		if ($this->dependencies == '') {
			return $lines;
		}

		$lines2 = array();

		foreach ($lines as &$line) {
			preg_match_all('/^(\s{0,})%\s{0,}([a-z0-9\-\_\.]{1,})/i', $line, $res);
			if (@$res[2][0]) {
				$ind = $res[1][0];
				$content = @file_get_contents($this->dependencies . $res[2][0] . '.ehtml');
				$linesContent = explode("\n", $content);
				if (count($linesContent) > 0 && substr($linesContent[0], 0, 2) == '%%') {
					array_shift($linesContent);
					foreach ($linesContent as $line2) {
						if (trim($line2) == '' || strpos(trim($line2), '#') === 0) {
							continue;
						}
						$lines2[] = $ind . $line2;
					}
				}
			} else {
				$lines2[] = $line;
			}
		}

		return $lines2;
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
				if (preg_match('/<$/i', $line) === 1 || preg_match('/^</i', $line) === 1) {
					$this->state = self::STATE_MULTILINE;
					$multiline .= preg_replace('/(<)$/i', '', $line);
				} else {

					if (!is_null($prev)) {
						$res['lines'][] = $prev;
						$prev = null;
					}

					$res['lines'][] = $line;

					array_shift(explode(' ', $line));
					if (!$this->isTokenizedLine($line) && trim($line) != '' && !in_array(array_shift(explode(' ', trim($line))), array('img', 'input'))) {
						$prev = '/ ' . $line;
					} else {
						$prev = null;
					}
				}
			}
			$i++;
		}

		if (!is_null($prev)) {
			$res['lines'][] = $prev;
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

		return $l2;
	}

	function renderScope(array $lines, $scope = 0, array $parameters = array(), array &$res = array()) {

		foreach ($lines as $line) {
			if (is_array($line)) {
				$this->renderScope($line, $scope + 1, $parameters, $res);
			} else {
				$res[] = $this->renderLine($line, $scope, $this->methods, $parameters);
			}
		}

		return $res;
	}

	function renderLine($line, $scope = 0, array $methods = array(), $parameters = array()) {

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
		$nested = 0;
		$prev = $char = '';
		$escapes = array(
			'"' => '"',
			'\'' => '\'',
			'(' => ')',
			'[' => ']',
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
			if ($step > 0 && $escaped) {
				$current .= $char;

				if (( $escapedChar == '(' || $escapedChar == '{' ) && $char == $escapedChar) {
					$nested++;
				}

				// End of escape
				if ($escapes[$escapedChar] == $char && $prev != '\\') {
					if ($nested == 0) {
						$element->addParam($current);
						$current = '';
						$nested = 0;
						$escaped = false;
					} else {
						$nested--;
					}
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
				$current = '';
			} else if (ake($char, $escapes) && ( $prev == ' ' || $prev == "\t" )) {
				$current .= $char;
				$escaped = true;
				$escapedChar = $char;
			} else {
				$current .= $char;
			}

			continue;
		}
		if (strpos($element->source, "\n") !== false) {
			$element->multiline = true;
		}
		return $element;
	}

	function isTokenizedLine($line) {
		preg_match('/^([^a-z0-9\s\n]{1,2}|sprintf)/i', $line, $res);
		return!empty($res);
	}

}

?>