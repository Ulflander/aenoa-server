<?php

class EHtmlElement extends AeObject {

	var $tokenized = false;
	var $token = '';
	var $keyword = '';
	var $rawTokenContent = '';
	var $parameters = array();
	var $values = array();
	var $multiline = false;
	var $source = '';
	var $result = '';

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

	function renderInnerPHP($string) {
		preg_match_all('/{([^}]*)}/i', $string, $matches);
		if (count($matches[0]) > 0) {
			foreach ($matches[0] as $k => $v) {
				$string = str_replace($matches[0][$k], '<?php echo ' . $matches[1][$k] . ' ?>', $string);
			}
		} else {

			preg_match_all('/\[([^]]*)\]/i', $string, $matches);


			if (count($matches[0]) > 0) {


				foreach ($matches[0] as $k => $v) {
					$string = str_replace($matches[0][$k], '<?php echo _(\'' . $matches[1][$k] . '\') ?>', $string);
				}
			}
		}

		return $string;
	}

	function solveMethodParams($string, $variables = array()) {
		if (empty($variables)) {
			return $string;
		}
		preg_match_all('/\$([0-9]{1,})/i', $string, $matches);
		if (count($matches[0]) > 0) {
			foreach ($matches[0] as $k => $v) {
				$index = intval($matches[1][$k]) - 1;
				if (count($variables) > $index) {
					$string = str_replace($matches[0][$k], trim($variables[$index], ' "'), $string);
				}
			}
		}
		return $string;
	}

	private function renderHTML($indentation, $methods, $variables, EHtmlBase &$base) {

		$res = $indentation;

		$classes = array();

		$styles = array();

		$attributes = '';

		$customAttr = '';

		$value = '';

		$closure = '';

		$res .= '<' . $this->keyword;

		foreach ($this->parameters as $param) {
			$tok = substr($param, 0, 1);
			if ($tok == '$') {
				if (($i = intval(substr($param, 1)) ) > 0 && count($variables) >= $i) {
					$param = $variables[$i - 1];
					$tok = substr($param, 0, 1);
				}
			} else {
				$param = $this->solveMethodParams($param, $variables);
			}




			switch (true) {
				case in_array($tok, array('{', '(', '"', '[')):
					$val = substr($param, 1, mb_strlen($param) - 2);
					break;
				default:
					$val = substr($param, 1);
					break;
			}

			switch ($tok) {
				// RAW value
				case '"':
					$value .= $val;
					break;
				// CSS class
				case '.':
					$classes[] = $this->renderInnerPHP($val);
					break;
				// CSS inline Style
				case '%':
					$styles[] = $val;
					break;
				// HTML Id
				case '#':
					$attributes .= ' id="' . $this->renderInnerPHP($val) . '"';
					break;
				// Raw attributes
				case '(':
					$attributes .= ' ' . $this->renderInnerPHP($val);
					break;
				// PHP echo tag, will defined value (and later, closure)
				case '{':
					$value .= '<?php echo ' . $val . ' ?>';
					break;
				// PHP echo tag, will defined value (and later, closure)
				case '[':
					$value .= '<?php echo _(\'' . $val . '\') ?>';
					break;
				// src, action, href attribute depending on main tag (script, iframe, img, a tags)
				case '@':
					if (strpos($val, '.') === 0) {
						$val = '<?php echo url() ?>' . $this->renderInnerPHP(substr($val, 1));
					} else {
						$val = $this->renderInnerPHP($val);
					}
					switch ($this->keyword) {
						case 'script':
						case 'iframe':
						case 'img':
							$attributes .= ' src="' . $val . '"';
							$closure = '</' . $this->keyword . '>';
							break;
						case 'a':
							$attributes .= ' href="' . $val . '"';
							break;
						case 'form':
							$attributes .= ' action="' . $val . '"';
							break;
					}
					break;
				default:
					$custom = $base->getCustomTokenResult($tok, $val, true, $this);
					if (!is_null($custom)) {
						$customAttr .= ' ' . $custom;
					}
			}
		}

		if (!empty($classes)) {
			$attributes .= ' class="' . implode(' ', $classes) . '"';
		}

		if (!empty($styles)) {
			$attributes .= ' style="' . implode(' ', $styles) . '"';
		}

		$tagClosure = '>';

		if ($this->keyword == 'input' || $this->keyword == 'img') {
			$tagClosure = ' />';
			$value = '';
		}


		$this->result = $res . $attributes . $customAttr . $tagClosure . $value . $closure;

		return $this->result;
	}

	private function renderTokenized($indentation, $methods, $variables, EHtmlBase &$base) {
		$res = $indentation;

		switch ($this->token) {

			// Method call
			case ';':
				if (!ake($this->keyword, $methods)) {
					break;
				}
				$res = implode("\n", $base->renderScope($methods[$this->keyword], strlen($indentation), $this->parameters));
				break;
			// PHP statement
			case '?':
				$res .= '<?php ' . $this->rawTokenContent
					. ($this->multiline ? "\n" . $indentation : '') . ' ?>' . "\n";
				break;
			// PHP echo statement
			case '!':
				$res .= '<?php echo ' . $this->rawTokenContent . ' ?>' . "\n";
				break;
			// renderElement
			case '&':
				$res .= '<?php $this->renderElement(\'' . $this->rawTokenContent . '\'); ?>' . "\n";
				break;
			// Close HTML tag
			case '/':
				if ($this->keyword == 'input' || $this->keyword == 'img') {
					break;
				}
				$res .= '</' . $this->keyword . '>';
				break;
			// HTML comment statement
			case '-':
				$res .= '<!-- ' . $this->rawTokenContent
					. ($this->multiline ? "\n" . $indentation : '') . '-->';
				break;
			// Raw content
			case '.':
				$res .= $this->rawTokenContent;
				break;
			// Javascript
			case '^':
				$res .= '<script type="text/javascript">' . $this->rawTokenContent
					. ($this->multiline ? "\n" . $indentation : '') . '</script>';
				break;
			// Parameter of function
			case '$':
				if (($i = intval($this->keyword) ) > 0 && count($variables) >= $i) {
					$param = $variables[$i - 1];
					$tok = substr($param, 0, 1);
					switch (true) {
						case in_array($tok, array('{', '(', '"')):
							$val = substr($param, 1, mb_strlen($param) - 2);
							break;
						default:
							$val = substr($param, 1);
							break;
					}

					switch ($tok) {
						// PHP echo tag, will defined value (and later, closure)
						case '{':
							$val = '<?php echo ' . $val . ' ?>';
							break;
					}

					$res .= $val;
				}
				break;
			default:
				$custom = $base->getCustomTokenResult($this->token, $this->rawTokenContent, false, $this);

				if (is_null($custom) && $this->token != '') {

					// echo '-- unknown token: ' . $this->token . ' in line ' . $this->source . '--';
				} else {
					$res .= $custom;
				}
		}
		return $res;
	}

}

?>