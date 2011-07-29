<?php





/**
 * The FilePregMatch runs preg_match_all function in directories and files.
 */
class FilePregMatch {
	
	/**
	 * Used to check extension
	 * @private
	 */
	private $fileTools ;
	
	private $fileCount = 0 ;
	
	private $matchesCount = 0 ;
	
	private $fileMatchesCount = 0 ;
	
	private $results = array () ;
	
	/**
	 * Constructor
	 * @private
	 */
	public function __construct ()
	{
		$this->fileTools = new FSUtil ( '' , 10000) ;
		
		
	}
	
	function reset ()
	{
		$this->fileCount = 0 ;
		$this->matchesCount = 0 ;
		$this->fileMatchesCount = 0 ;
		$this->results = array () ;
	}
	
	/**
	 * pregMatchDir is a recursive method that will loop in any subdirectories and files to find
	 * all matches of your regex.
	 * 
	 * Use getResults method to retrieve matches of regex.
	 * 
	 * @param string $dir Base directory
	 * @param mixed $regex The regex as string, or an array of regex
	 * @param mixed $fileExts An array of extensions (without the dot), or * for every extensions.
	 */
	function pregMatchDir ( $dir , $regex , $fileExts = '*' )
	{
		$dir = setTrailingDS ( $dir ) ;
		if ($handle = opendir($dir)) 
		{
			while($name=readdir($handle))
			{
				if ($name==='.' or $name==='..')
				{
					continue;
				} else 
				if ( is_dir ($dir.$name.DS ) )
				{
					$this->pregMatchDir ( $dir.$name.DS , $regex , $fileExts ) ;
				} else if ( in_array ( $this->fileTools->getExtension ($name) , $fileExts ) || $fileExts == '*' )
				{
			
					$arr = $this->pregMatchFile ( $dir.$name , $regex ) ;
					
					if ( !empty ( $arr ) )
					{
						$this->fileMatchesCount ++ ;
						$this->results[$dir.$name] = $arr ;
					}
				}
			}
			
			closedir ( $handle ) ;
		}
	}
	
	/**
	 * pregMatchFile is a method that will find all matches of your regex in one file.
	 * 
	 * @param string $file File name and path
	 * @param mixed $regex The regex as string, or an array of regex
	 * @return array An array containing all results
	 */
	function pregMatchFile (  $file , $regex )
	{
		$this->fileCount ++ ;
		
		$result = array () ;
		
		$handle = @fopen($file, "r");
		$data = @fread($handle, @filesize($file));
		@fclose($handle); 
		
		if ( is_null ( $data ) )
		{
			return $result ;
		}
		
		if ( !is_array ( $regex ) )
		{
			$regex = array ( $regex ) ;
		}
		
		foreach ( $regex as $reg )
		{
			@preg_match_all ( $reg ,$data , $matches ) ;
			foreach ( $matches[1] as $k => $v )
			{
				if ( $v != '' )
				{
					$res = array() ;
					array_push( $res , $v ) ; 
					$i = 2 ;
					while ( array_key_exists ( $i , $matches ) )
					{
						array_push( $res , $matches[$i][$k] ) ; 
						$i ++ ;
					}
					$result[] = $res ;
					$this->matchesCount ++ ;
				}
			}
		}

		return $result ;
	}
	
	function getFileCount ()
	{
		return $this->fileCount ;
	}
	
	function getMatchesCount ()
	{
		return $this->matchesCount ;
	}
	
	function getFileMatchesCount ()
	{
		return $this->fileMatchesCount ;
	}
	
	function getResults ()
	{
		return $this->results ;
	}
}





?>