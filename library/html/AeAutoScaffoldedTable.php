<?php



class AeAutoScaffoldedTable extends AeAutoTable {
	
	
	function getVal ( $val, $field )
	{
		return '<?php echo $d[\'' . $field['name'] . '\'] ?>';
	}


	function getURL ()
	{
		return '<?php echo $baseURL ?>';
	}
	
	
	function startTable ( $page=1, $length= 1 , $count=1 , $order , $dir )
	{
		parent::startTable($page, $length , $count, $order, $dir);
		
		$this->_result[] = '<?php foreach ( $data as $d ): ?>' ;
	}
	
	function build ( $data , $page=1, $length = 1 , $count=1 , $order = null , $dir = null )
	{
		if ( empty( $data ) )
		{
			trigger_error ( 'You must select at least one row of data to scaffold tables' ) ;
		}
		
		parent::build( array ( $data[0] ) , $page, $length , $count, $order, $dir ) ;
	}

	function getNextPageLink ($page, $length , $count , $order , $dir)
	{
	
		if ( $page < $length )
		{
			$this->_result[] = '<?php echo ($page < $length ? \' | <a class="bold" href="\' . $baseURL . \'index/\'. ($page + 1) . (!is_null($order) ? \'/\' . $order . \'/\' . $dir : \'\' ) .\'">\' . _(\'Next page\') . \'</a>\' : \'\' ) ?>' ;
		}
	}
	
	function getPreviousPageLink ($page, $length , $count , $order , $dir)
	{
		$this->_result[] = '<?php echo ($page >1 ? \' | <a class="bold" href="\' . $baseURL . \'index/\'. ($page - 1) . (!is_null($order) ? \'/\' . $order . \'/\' . $dir : \'\' ) .\'">\' . _(\'Previous page\') . \'</a>\' : \'\' ) ?>' ;
	}
		
	function getPageInfo ($page, $length , $count , $order , $dir)
	{
		$this->_result[] = '<?php echo sprintf(_ngettext(\'There is one element\',\'There are %d elements\',$count),$count) ?>' ;
		$this->_result[] = '<?php echo ($length > 1 ? \' | \' . sprintf(_(\'Page %d/%d\'),$page, $length) : \'\' ) ?>' ;
	}

	function getClass ($field, $order, $dir)
	{
		return '' ;
	}

	function endTable ( $page=1, $length= 1 , $count=1 , $order , $dir, $cols )
	{
		$this->_result[] = '<?php endforeach; ?>' ;
		
		parent::endTable($page, $length , $count, $order, $dir, $cols);
	}
}


?>