<?php

/*
 * Class: IndexedArray
 *
 * Class used by models to store indexed arrays.
 * 
 * How to use:
 * (start code)
 *  // creates a new instance of IndexedArray
 * $arr = new IndexedArray () ;
 * 
 * // Push some elements
 * $arr->push('someValue');
 * $arr->push('anotherValue');
 * $arr->push('Hello World');
 * $arr->push(23);
 * 
 * // Get somme elements
 * echo $arr->get(0) ; // someValue
 * echo $arr->get(2) ; // Hello World
 * 
 * // Loop on elements
 * while ( $val = $arr->next () )
 * {
 *		echo $val . ' - ' ;
 * }
 * 
 * // will echo 
 * // someValue - anotherValue - Hello World - 23 - 
 * 
 * (end)
 * 
 * See also:
 * <Collection>
 *
 *
 */
class IndexedArray extends Object {

	private $_selection = array () ;

	private $_idx = 0 ;

	private $_length = 0 ;

	/**
	 * Creates a new instance of IndexedArray
	 *
	 * @param array $array Base array
	 */
	function __construct ( array $array = array () )
	{
		for ( $i = 0, $l = count ( $array ) ; $i < $l ; $i ++ )
		{
			$this->push ( $array[$i] ) ;
		}
	}

	/**
	 * Alias of IndexedArray::push
	 *
	 * @see IndexedArray::push
	 * @param mixed $value Value to push in array
	 * @return IndexedArray Current instance for chained command on this element
	 */
	function set ( $value )
	{
		$this->push( $value ) ;

		return $this ;
	}

	/**
	 * Removes array values at given indexes
	 *
	 * @param int $index Index of value to remove
	 * @param int $index2 [Optional] Another index
	 * @param int ...
	 * @return IndexedArray Current instance for chained command on this element
	 */
	function uset ( $index1 /* , $index2, ... */ )
	{
		$indexes = func_get_args() ;

		foreach ( $indexes as $index )
		{
			if ( $this->has($index))
			{
				array_splice($this->_selection, $index, 1) ;

				$this->_length -- ;
			}
		}

		return $this ;
	}

	/**
	 * Push all values from an existing indexed array
	 *
	 * @param array $array Indexed array
	 * @return IndexedArray Current instance for chained command on this element
	 */
	function setAll ( array $array )
	{
		for ( $i = 0, $l = count ( $array ) ; $i < $l ; $i ++ )
		{
			$this->push ( $array[$i] ) ;
		}

		return $this ;
	}

	/**
	 * Empty array, reset iterator and length
	 *
	 * @return IndexedArray Current instance for chained command on this element
	 */
	function usetAll ()
	{
		$this->_selection = array () ;
		
		$this->_length = 0 ;

		$this->_idx = 0 ;

		return $this ;
	}


	/**
	 * Unshift a value at the beginning of array
	 *
	 * @param mixed $value Value to unshift into array
	 * @return IndexedArray Current instance for chained command on this element
	 */
	function unshift ( $value )
	{
		array_unshift($this->_selection, $value) ;

		$this->_length ++ ;

		return $this ;
	}

	/**
	 * Push a value at the end of array
	 *
	 * @param mixed $value Value to push at the end of array
	 * @return IndexedArray Current instance for chained command on this element
	 */
	function push ( $value )
	{
		array_push($this->_selection, $value) ;

		$this->_length ++ ;

		return $this ;
	}

	
	/**
	 * Get a value at given index
	 *
	 * @param int $index Index of value to get
	 * @return mixed Value if found, null otherwise
	 */
	function get ( $index )
	{
		if ( $this->has ( $index ) )
		{
			return $this->_selection[$index] ;
		}

		return null ;
	}

	/**
	 * Delete a value given its index and returns it
	 *
	 * @param int $index Index of value to get and remove
	 * @return mixed Value if found, null otherwise
	 */
	function uget ( $index )
	{
		if ( $this->has($index) )
		{
			$val = $this->get( $index ) ;

			$this->uset( $index ) ;

			return $val ;
		}

		return null ;
	}

	/**
	 * Returns all indexed array
	 *
	 * @return array All data of instance
	 */
	function getAll ()
	{
		$arr = $this->_selection ;

		return $arr ;
	}

	/**
	 * Pop the last element of array
	 *
	 * @return mixed Value if found, null otherwise
	 */
	function pop ()
	{
		if ( $this->_length > 0 )
		{
			$val = array_pop( $this->_selection ) ;

			$this->_length -- ;

			return $val ;
		}

		return null ;
	}

	/**
	 * Shift the first element of array
	 *
	 * @return mixed Value if found, null otherwise
	 */
	function shift ()
	{
		if ( $this->_length )
		{
			$val = array_shift( $this->_selection ) ;

			$this->_length -- ;

			return $val ;
		}

		return null ;
	}

	/**
	 * Returns length of indexed array
	 *
	 * @return int Length of indexed array
	 */
	function count ()
	{
		return $this->_length ;
	}

	/**
	 * Alias of IndexedArray::count
	 *
	 * @see IndexedArray::count
	 * @return int Length of indexed array
	 */
	function length ()
	{
		return $this->count() ;
	}

	/**
	 * Reset the iterator to 0 or to given index
	 *
	 * @param int $index [Optional] Index of iterator, default is 0
	 * @return IndexedArray Current instance for chained command on this element
	 */
	function iterator ( $index = 0 )
	{
		$this->_idx = $index ;

		return $this ;
	}

	/**
	 * Checks if an index exists
	 *
	 * @param int $index [Optional] Index to test
	 * @return boolean True if index exists, false otherwise
	 */
	function has ( $index = 0 )
	{
		return $index >= 0 && $index < $this->_length ;
	}

	/**
	 * Checks if array is empty
	 *
	 * @return boolean True if array os empty, false otherwise
	 */
	function isEmpty ()
	{
		return $this->has(0) === false ;
	}

	/**
	 * Get value for current iterator index
	 *
	 * @return mixed Value if index exists, null otherwise
	 */
	function item ()
	{
		if ( $this->has ( $this->_idx) )
		{
			return $this->_selection[$this->_idx] ;
		}

		return null ;
	}

	/**
	 * Get next value
	 *
	 * @return mixed Value if index exists, null if loop done
	 */
	function next ()
	{
		if ( $this->_idx > $this->_length - 1 )
		{
			$this->_idx = 0 ;

			return null ;
		}

		$res = &$this->item () ;

		$this->_idx ++ ;

		return $res ;
	}

}

?>
