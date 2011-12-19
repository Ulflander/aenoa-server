<?php

class HTMLBase extends Object {

	/**
	 * Renders an array into an HTML list
	 *
	 * Supports nested arrays
	 *
	 * @param array $arr The input array
	 * @param boolean $i18n Does try to localize array keys using "_" function
	 * @return string HTML code of list
	 */
	static function array2HTMLList($arr, $i18n = true ) {

		$res[] = '<ul>';
		foreach ($arr as $k => $v) {
			if (is_array($v)) {
				$res[] = '<li><span class="col-3">' . _($k) . '</span></li>';
				$res[] = arrayToList($v);
			} else {
				$res[] = '<li><span class="col-3">' . _($k) . '</span><strong>' . $v . '</strong></li>';
			}
		}
		$res[] = '</ul>';
		return implode("\n", $res);
	}

}

?>
