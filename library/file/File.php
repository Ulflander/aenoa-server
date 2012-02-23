<?php

class File {

	static function sexists($path) {
		return is_file($path);
	}

	static function sread($path) {
		$f = new File($path, false);
		if ($f->exists()) {
			$str = $f->read();
			$f->close();
			return $str;
		}
		return '';
	}

	static $webImageMimes = array(
		'image/jpg',
		'image/jpeg',
		'image/gif',
		'image/png'
	);

	static function isImage($filename) {
		return in_array(self::getMimeFromExt(array_pop(explode('.', $filename))), self::$webImageMimes);
	}
	
	static function getMimeFromExt($ext) {
		switch ($ext) {
			case 'jpg':
			case 'jpeg':
			case 'jpe':
				return 'image/jpeg';
			case 'gif':
				return 'image/gif';
			case 'png':
				return 'image/png';
			case 'bmp':
				return 'image/bmp';
			case 'flv':
				return 'video/x-flv';
			case 'js' :
				return 'application/x-javascript';
			case 'json' :
				return 'application/json';
			case 'tiff' :
				return 'image/tiff';
			case 'css' :
				return 'text/css';
			case 'xml' :
				return 'application/xml';
			case 'doc' :
			case 'docx' :
				return 'application/msword';
			case 'xls' :
			case 'xlt' :
			case 'xlm' :
			case 'xld' :
			case 'xla' :
			case 'xlc' :
			case 'xlw' :
			case 'xll' :
				return 'application/vnd.ms-excel';
			case 'ppt' :
			case 'pps' :
				return 'application/vnd.ms-powerpoint';
			case 'rtf' :
				return 'application/rtf';
			case 'pdf' :
				return 'application/pdf';
			case 'html' :
			case 'thtml' :
			case 'htm' :
			case 'php' :
				return 'text/html';
			case 'txt' :
				return 'text/plain';
			case 'mpeg' :
			case 'mpg' :
			case 'mpe' :
				return 'video/mpeg';
			case 'mp3' :
				return 'audio/mpeg3';
			case 'wav' :
				return 'audio/wav';
			case 'aiff' :
			case 'aif' :
				return 'audio/aiff';
			case 'avi' :
				return 'video/msvideo';
			case 'wmv' :
				return 'video/x-ms-wmv';
			case 'mov' :
				return 'video/quicktime';
			case 'zip' :
				return 'application/zip';
			case 'tar' :
				return 'application/x-tar';
			case 'swf' :
				return 'application/x-shockwave-flash';
			case 'odt':
				return 'application/vnd.oasis.opendocument.text';
			case 'ott':
				return 'application/vnd.oasis.opendocument.text-template';
			case 'oth':
				return 'application/vnd.oasis.opendocument.text-web';
			case 'odm':
				return 'application/vnd.oasis.opendocument.text-master';
			case 'odg':
				return 'application/vnd.oasis.opendocument.graphics';
			case 'otg':
				return 'application/vnd.oasis.opendocument.graphics-template';
			case 'odp':
				return 'application/vnd.oasis.opendocument.presentation';
			case 'otp':
				return 'application/vnd.oasis.opendocument.presentation-template';
			case 'ods':
				return 'application/vnd.oasis.opendocument.spreadsheet';
			case 'ots':
				return 'application/vnd.oasis.opendocument.spreadsheet-template';
			case 'odc':
				return 'application/vnd.oasis.opendocument.chart';
			case 'odf':
				return 'application/vnd.oasis.opendocument.formula';
			case 'odb':
				return 'application/vnd.oasis.opendocument.database';
			case 'odi':
				return 'application/vnd.oasis.opendocument.image';
			case 'oxt':
				return 'application/vnd.openofficeorg.extension';
			case 'docx':
				return 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';
			case 'docm':
				return 'application/vnd.ms-word.document.macroEnabled.12';
			case 'dotx':
				return 'application/vnd.openxmlformats-officedocument.wordprocessingml.template';
			case 'dotm':
				return 'application/vnd.ms-word.template.macroEnabled.12';
			case 'xlsx':
				return 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
			case 'xlsm':
				return 'application/vnd.ms-excel.sheet.macroEnabled.12';
			case 'xltx':
				return 'application/vnd.openxmlformats-officedocument.spreadsheetml.template';
			case 'xltm':
				return 'application/vnd.ms-excel.template.macroEnabled.12';
			case 'xlsb':
				return 'application/vnd.ms-excel.sheet.binary.macroEnabled.12';
			case 'xlam':
				return 'application/vnd.ms-excel.addin.macroEnabled.12';
			case 'pptx':
				return 'application/vnd.openxmlformats-officedocument.presentationml.presentation';
			case 'pptm':
				return 'application/vnd.ms-powerpoint.presentation.macroEnabled.12';
			case 'ppsx':
				return 'application/vnd.openxmlformats-officedocument.presentationml.slideshow';
			case 'ppsm':
				return 'application/vnd.ms-powerpoint.slideshow.macroEnabled.12';
			case 'potx':
				return 'application/vnd.openxmlformats-officedocument.presentationml.template';
			case 'potm':
				return 'application/vnd.ms-powerpoint.template.macroEnabled.12';
			case 'ppam':
				return 'application/vnd.ms-powerpoint.addin.macroEnabled.12';
			case 'sldx':
				return 'application/vnd.openxmlformats-officedocument.presentationml.slide';
			case 'sldm':
				return 'application/vnd.ms-powerpoint.slide.macroEnabled.12';
			case 'thmx':
				return 'application/vnd.ms-officetheme';
			case 'onetoc':
			case 'onetoc2':
			case 'onetmp':
			case 'onepkg':
				return 'application/onenote';
		}
	}

	protected $path;
	protected $f;
	private $mode = 'rw+';

	

	function tail( $lines = 100, $skipEmptyLines = true, $toString = true, $stringSeparator = "\n") {


		if ($skipEmptyLines) {
			$_flags = FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES;
		} else {
			$_flags = FILE_IGNORE_NEW_LINES;
		}

		$contents = @file($this->path, $_flags);

		if ($contents === false) {

			return (sprintf(
					"Impossible de lire le contenu du fichier '%s'.", $this->path
				));
		} else {
			$tail = array();

			for ($i = 0; $i < intval($lines); $i++) {
				$tail[] = array_pop($contents);
			}

			unset($contents);

			return ($toString) ? implode($stringSeparator, $tail) : $tail;
		}
	}
	function setHeader($header) {
		
	}
	
	
	/**
	 * Creates a new File instance
	 *
	 * @param string $filepath Path to file to open or create
	 * @param boolean $create [Optional] Do create file if not exist, default is set to false
	 * @param int $chmod [Optional] File mode to apply to newly created file
	 */
	function __construct($filepath, $create = false, $chmod = 0777) {
		$this->path = $filepath;

		if ($this->exists() == false && $create == true) {
			$this->create($chmod);
		}

		if ($this->exists()) {
			$this->open();
		}
	}

	/**
	 * Rename file
	 * 
	 * @param string $newname New name of file
	 * @return boolean True if file has been renamed, false otherwise
	 */
	function rename($newname) {
		if (is_object($this->f)) {
			$this->close();
		}
		if (rename($this->path, $newname)) {
			$this->path = $newname;
			return $this->open();
		}
		return false;
	}

	/**
	 * Check if file exists
	 * 
	 * @return boolean True if file exists, false otherwise
	 */
	function exists() {
		return is_file($this->path);
	}

	/**
	 * Open file
	 * 
	 * @param mixed $opt [Optional] Mode of opening, default is rw+
	 * @return boolean True if file has been renamed, false otherwise
	 */
	function open($opt = 'rw+') {
		if ((is_object($this->f) && $this->mode == $opt ) || $this->f = @fopen($this->path, $opt)) {
			$this->mode = $opt;
			return true;
		}
		return false;
	}

	/**
	 * Function get path of file (including name of file)
	 * 
	 * @return string Path to file 
	 */
	function getPath() {
		return $this->path;
	}

	/**
	 * Copy file to given directory
	 * 
	 * @param string $newpath Path to the folder that will contain copied file
	 * @return bololean True if file correctly copied, false otherwise
	 */
	function copy($newpath) {

		$f2 = new File($newpath, true);
		return $f2->exists() && $f2->write($this->read()) && $f2->close();
	}

	/**
	 * Read content of file and return it as a string
	 * 
	 * @return string Content of file
	 */
	function read() {
		if ($this->f && filesize($this->path) > 0) {
			return fread($this->f, filesize($this->path));
		}
		return '';
	}
	
	/**
	 * Check if file has no content
	 * 
	 * @return boolean True if file is empty, false otherwise
	 */
	function isEmpty() {
		if ($this->f) {
			return filesize($this->path) == 0;
		}
		return true;
	}

	/**
	 * Delete the file
	 * 
	 * @global FSUtil $FILE_UTIL Used as tool to remove file
	 * @return boolean True if file has been removed, false otherwise
	 */
	function delete() {
		if ($this->f) {
			@fclose($this->f);
		}

		if (is_file($this->path)) {
			global $FILE_UTIL;
			return $FILE_UTIL->removeFile($this->path);
		}
		return false;
	}
	
	/**
	 *
	 * @param type $chmod
	 * @return boolean 
	 */
	function create($chmod = 0777) {
		if (!is_file($this->path) && is_dir(dirname($this->path)) && ($_f = @fopen($this->path, 'x+') )) {
			chmod($this->path, $chmod);
			fclose($_f);
			return true;
		}

		return false;
	}

	/**
	 * Append content after current content
	 * 
	 * @param string $content
	 * @return 
	 */
	function append($content) {
		if ($this->open('a')) {
			return fwrite($this->f, $content);
		}
		return false;
	}

	/**
	 * Prepend content before current content
	 * 
	 * @param string $content
	 * @return 
	 */
	function prepend($content) {
		$this->write($content . $this->read());
	}

	/**
	 * Replace all content
	 * 
	 * @param string $content
	 * @return bool True if file content written, false otherwise
	 */
	function write($content) {
		$this->open();
		if ($this->f) {
			ftruncate($this->f, 0);
			if (fwrite($this->f, $content) !== false) {
				return true;
			}
		}

		return false;
	}
	
	/**
	 * Check if file is writable
	 * 
	 * @return boolean True if file is writable, false otherwise
	 */
	function writable() {
		return is_file($this->path) && is_writable($this->path);
	}
	
	/**
	 * Check if file is readable
	 * 
	 * @return boolean True if file is readable, false otherwise
	 */
	function readable() {
		return is_file($this->path) && is_readable($this->path);
	}

	/**
	 * Return PHP ressource for this file, if file has been opened
	 * 
	 * @return mixed PHP file resource if file has been opened, null otherwise 
	 */
	function getHandler() {
		if ($this->f) {
			return $this->f;
		}
		return null;
	}
	
	/**
	 * Close the file if file has been opened
	 * 
	 * @return boolean True if the file was opened and has been closed, false otherwise
	 */
	function close() {
		if ($this->f) {
			return @fclose($this->f);
		}
		return true;
	}

	/**
	 * Destruct instance, closes file if file has been opened 
	 */
	function __destruct() {
		$this->close();
	}

}

?>