<?php

class AeAutoForm {
	
	private $_id ;
	
	private $_primaryKey ;
	
	private $_primaryFormKey ;
	
	private $_primaryVal ;
	
	private $_dbID ;
	
	private $_table ;
	
	private $_struct ;
	
	private $_fields ;
	
	private $_url ;
	
	private $_result ;
	
	private $_hasRequired = false ;
	
	private $_validities ;
	
	private $_hasFile = false ;

	private $_currentFieldset = null;
	
	private $db ;
	
	
	public $renderContainer = true ;
	
	public $renderLabel = true ;
	
	public $renderField = true ;
	
	public $renderDescription = true ;
	
	public $fieldsOnly = false ;
	
	
	
	function setDatabase ( $dbID , $table , $structure = null )
	{
		$this->_dbID = $dbID ;
		$this->_table = $table ;
		
		$this->db = App::getDatabase( $this->_dbID ) ;
		
		if ( $this->db )
		{
			$this->_globalStructure = &$this->db->getStructure() ;
			if ( $this->_globalStructure && ake($this->_table,$this->_globalStructure))
			{
			
				if ( !is_null($structure) )
				{
					$this->_struct = $structure;
				} else {
					$this->_struct = $this->_globalStructure[$this->_table] ;
				}
				
				$this->_primaryKey = $this->db->getTableSchema($this->_table)->getPrimary() ;
				
				$this->_primaryFormKey = $this->_dbID . '/' . $this->_table . '/' . $this->_primaryKey ;
				
				return true ;
			}
		}
		
		return false ;
	}
	
	function build ( $data = array () ,
					$fields = array () , 
					$actionURL = '#' , 
					$ajaxize = false ,
					$validities = array () )
	{
		
		
		
		
		if ( empty ( $this->_fields ) )
		{
			$this->_fields = array () ;
			
			foreach ( $this->_struct as $field )
			{
				if ( empty ( $fields ) || in_array ( $field['name'] , $fields ) ) 
					$this->_fields[] = $field ;
			}
		}
		
		if ( !empty ( $data ) )
		{
			$haveToFormat = false ;
			foreach ( $data as $k => $v )
			{
				if ( strpos($k, '/') === false )
				{
					$haveToFormat = true ;
					
					if ( $k == $this->_primaryKey )
					{
						$this->_primaryVal = $v ;
					}
				} else if ( $k == $this->_primaryFormKey )
				{
					$this->_primaryVal = $v ;
				}
			}
			if ( $haveToFormat )
			{
				$data = keysToFormKeys($this->_dbID, $this->_table , $data) ;
			}
		}
		
		$this->_url = $actionURL;
		
		$this->_result = array () ;
			
		foreach ( $this->_fields as $field )
		{
			if ( $field['type'] == DBSchema::TYPE_FILE )
			{
				$this->_hasFile = true ;
				break;
			}
		}
		
		if ( $this->fieldsOnly === false )
		{
			$this->startForm ( @$data ) ;
		}
		
		foreach ( $this->_fields as $field )
		{
			$this->addField ( $field , @$data , @$validities[$field['name']]) ;
		}
		
		
		if ( $this->fieldsOnly === false )
		{
			$this->endForm () ;
			echo implode("\n",$this->_result) ;
		} else {
			return implode("\n",$this->_result) ;
		}
		
	}
	
	
	public function startForm ( $data = array () )
	{
		$header = '<form ' ;
		if ( $this->_hasFile ) 
		{
			$header .= 'enctype="multipart/form-data" ' ; 
		}
		if (!empty($data) && !ake($this->_dbID .'/'. $this->_table . '/'.$this->_primaryKey,$data) )
		{
			$header .= ' data-modified="modified"';
		}
		$header .= 'id="' .$this->_dbID .'/'. $this->_table . '" action="'.$this->_url.'" method="post" data-validation-message="'._('You\'ve made some modifications on data. Don\'t forget to submit in order to save data.').'">' ;
		
		$this->_result[] = $header ;
		$this->_result[] = '<input type="hidden" id="__SESS_ID" name="__SESS_ID" value="'.App::$session->getSID(). '" />' ;
	}
	
	
	public function addField ( $field , $fieldData = null , $valid = null )
	{
		
		if ( ake('uneditable', $field ) && $field['uneditable'] == true )
		{
			return;
		}
		
		if ( ake('group',$field) && $this->fieldsOnly == false )
		{
			if ( !is_null($this->_currentFieldset) )
			{
				$this->_result[] = '</fieldset>' ;
			}
			
			$this->_currentFieldset = $field['group'] ;
			
			$this->_result[] = '<fieldset>' ;
			$this->_result[] = '<legend>' . $this->_currentFieldset . '</legend>' ;
			
		}
		
		$data = @$fieldData[$this->_dbID .'/'.$this->_table.'/'.$field['name']] ;
		
		
		$res = null ;
		if ( $field['type'] )
		{
			
			$id = $this->_dbID .'/'.$this->_table . '/' . $field['name'] ;
			
			if ( $field['name'] == 'id' && !is_null($data) )
			{
				$this->_result[] = '<input type="hidden" id="' . $id . '" name="' . $id . '" value="' . $data . '" />' ;
				return;
			} else if ( $field['name'] == 'id' )
			{
				return;
			}
			
			$label = ucfirst($field['label']) ;
			$r = '' ;
			$d = '' ;
			$class = array() ;
			
			if ( !is_null( $valid ) )
			{
				( $valid === true ?
					$class[] = 'valid' :
					$class[] = 'invalid' 
				) ;
			}
			
			if ( $field['name'] == 'label' )
			{
				$class[] = 'title' ;
			}
			
			if ( !@empty( $field['validation'] ) )
			{
				$r .= ' required="required" pattern="' . $field['validation']['rule'] . '" data-error="' .$field['validation']['message']. '"' ;
				$label .= ' *' ;
				$this->_hasRequired=true ;
			}
			
			if ( !is_null ( $data ) && $field['type'] != DBSchema::TYPE_TEXT && !( @$field['behavior'] & DBSchema::BHR_PICK_ONE || @$field['behavior'] & DBSchema::BHR_PICK_IN ) )
			{
				if ( is_array ( $data ) )
				{
					$d = ' value="' . @$data[$this->db->getTableSchema($field['source'])->getPrimary()] . '"' ;
				}else {
					$d = ' value="' . $data . '"' ;
				}
			}
			
			if ( $field['type'] == DBSchema::TYPE_TEXT && ( @$field['behavior'] & DBSchema::BHR_PICK_ONE || @$field['behavior'] & DBSchema::BHR_PICK_IN ) )
			{
				$field['type'] = DBSchema::TYPE_STRING ;
			}
			
			if ( ake('js_plugin_edit',$field) )
			{
				$d .= ' data-plugin="' . $field['js_plugin_edit'] . '"' ;
			}
			
			if ( !empty($class) )
			{
				$r .= ' class="'.implode(' ',$class).'"' ;
			}
			
			switch ( $field['type'] )
			{
				case DBSchema::TYPE_BOOL:
					$res = '<input type="checkbox" id="'.$id.'" name="'.$id.'"'.$r.' '.($data == 1 ? 'checked="checked"' :'').' />';
					break;
					
				case DBSchema::TYPE_DATETIME:
						if ($field['name'] == 'created' || $field['name'] == 'updated' || $field['name'] == 'modified' )
						{
							$res = '<input id="'.$id.'" name="'.$id.'" type="text" '.$r.$d.' readonly="readonly" class="date" />';
						} else {
							$res = '<input id="'.$id.'" name="'.$id.'" type="text" '.$r.$d.' class="date" />';
						}
					break;
					
											
				case DBSchema::TYPE_ENUM:
					$res = '<select id="'.$id.'" name="'.$id.'"'.$r.'>' . "\n";
					foreach ( $field['values'] as $idx => $key )
					{
						$k = $key ;
						if ( $key == $data )
						{
							$k .= '" selected="selected' ;
						}
						$res.= '<option value="' . $k . '">'. ( array_key_exists( 'labels' ,$field ) ? $field['labels'][$idx] : $key ).'</option>' . "\n" ;
					}
					$res .= '</select>' . "\n" ;
					break;
										
				case DBSchema::TYPE_PARENT:
				case DBSchema::TYPE_CHILD:
					if ( !(@$field['behavior'] & DBSchema::BHR_PICK_ONE || @$field['behavior'] & DBSchema::BHR_PICK_IN ) )
					{
						$res = '' ;
						$res .= '<div class="hidden" id="'.$id.'" name="'.$id.'"'.$r.'data-behavior="as-input" data-read-only="read-only" data-source-main="'.$field['source-main-field'].'">';
						if ( !empty ( $data ) )
						{
							$linked_primary = $this->db->getTableSchema($field['source'])->getPrimary();
							
							if ( @$field['behavior'] & DBSchema::BHR_PICK_ONE || ake(0,$data) == false )
							{
								$linked_id = $data[$linked_primary] ;
								$data = array($data);
							} else if ( ake(0,$data) == true && ake($linked_primary, $data[0]) )
							{
								$linked_id = $data[0][$linked_primary] ;
							}
							$res .= json_encode($data);
						} else {
							return;
						}
						$res .= '</div>' ;
						if ( !is_null($this->_primaryVal) )
						{
							if ( DBSchema::TYPE_PARENT == $field['type'] )
							{
								$res .= '<a href="' . url() . 'database/' . $this->_dbID . '/' . $this->_table . '/edit/' . $this->_primaryVal .'/'.$field['source'].'" class="icon16 edit">'._('Edit').'</a>' ;
							} else {
								$res .= '<a href="' . url() . 'database/' . $this->_dbID . '/' . $field['source'] . '/edit/' . $linked_id .'" class="icon16 edit">'._('Edit').'</a>' ;
							} 
						}
						break;
					}
				case DBSchema::TYPE_FILE:
				case DBSchema::TYPE_INT:
				case DBSchema::TYPE_FLOAT:	
				case DBSchema::TYPE_STRING:
					if ( @$field['behavior'] & DBSchema::BHR_PICK_ONE || @$field['behavior'] & DBSchema::BHR_PICK_IN )
					{
						$primaryKey = @$this->db->getTableSchema($field['source'])->getPrimary() ;
						$m = ' data-ac-multi="'.(@$field['behavior'] & DBSchema::BHR_PICK_ONE?'false':'true').'"' ;
						$res = '<input type="hidden" id="'.$id.'" name="'.$id.'"'.$r.' />';
						$d = ' value="' . @$data[@$field['source-main-field']] . '" ' ;
						$res .= '<div class="mid-left"><div id="'.$id.'/display" name="'.$id.'/display" data-behavior="as-input" '.$m.' >' ;
						$res .= '</div></div>';
						$res .= '<div class="mid-right"><label for="'.$id.'/input"></label><input type="text" id="'.$id.'/input" name="'.$id.'/input" autocomplete="off" placeholder="'._('Type a few letters...').'"';
						$res .= ' data-ac-source="'.$this->_dbID.'/'.@$field['source'].'/'.@$field['source-main-field'].'" data-ac-conditions="'.@str_replace('_PRIMARY_ID' , @$fieldData[$this->_table.'/'.$this->_primaryKey] , @$field['source-conditions'] ).'"' ;
						$res .= ' data-ac-target="'.$id.'/display" data-ac-primary-key="'.$primaryKey.'"' ;
						$res .= ' data-ac-empty-message="'._('No suggestion').'" '.$m.' /></div>' ;
					
						if ( !empty ( $data ) )
						{
							if ( @$field['behavior'] & DBSchema::BHR_PICK_ONE || array_key_exists(0,$data) == false )
							{
								$data = array($data);
							}
							$res .= '<textarea class="hidden" id="'.$id.'/__data">'.json_encode($data).'</textarea>' ;
						}
					
					} else {
						
						$r .= ' placeholder="' . ucfirst($field['label']) .'"' ;
						
						if ( @$field['length'] )
						{
							$r .= ' maxlength="'.$field['length'].'"' ;
						}
						
						if ( @$field['behavior'] & DBSchema::BHR_LAT_LNG )
						{
							$r .= ' data-behavior="latlng"' ;
							$r .= ' data-behavior-title="'._('Get the coordinates').'"' ;
							$r .= ' data-behavior-search="'._('Search').'"' ;
							
							if ( @$field['georequest-field'] )
								$r .= ' data-behavior-georequest-field="'.@$field['georequest-field'].'"' ;
							
							
							if ( @$field['georequest-country'] )
								$r .= ' data-behavior-georequest-country="'.@$field['georequest-country'].'"' ;
							
							
							if ( @$field['georequest-manual'] === true ) {
								$r .= ' data-behavior-georequest-manual="'._('Set coordinates manually').'"' ;
								$r .= ' data-behavior-georequest-manual-message="'._('Zoom and drag the map to get the right coordinates').'"' ;
							}
							
						} else if ( @$field['urlize-to'] )
						{
							$r .= ' data-behavior-urlize-to="'.$this->_dbID.'/'.$this->_table.'/' . $field['urlize-to'] . '"' ;
						}
						
						if ( $field['type'] == DBSchema::TYPE_FILE )
						{
							$type = 'hidden' ;
							$r .= ' data-upload="unique" data-base-url="' . url() . '" data-upload-url="common/upload/' .$this->_dbID.'/'. $this->_table . '/' . $field['name'] .'"' ;
							$r .= ' data-upload-message="Upload new" data-close-upload-message="Close upload"';
						} else {
							$type = 'text' ;
						}
						
						$res = '<input type="'.$type.'" id="'.$id.'" name="'.$id.'"'.$r.$d.' />';
					}
					break;
										
				case DBSchema::TYPE_TEXT:
					$r .= ' placeholder="' . ucfirst($field['label']) .'"' ;
					$res = '<textarea id="'.$id.'" name="'.$id.'"'.$r.'>'.$data.'</textarea>';
					break;
				
			}
			
			
			if ( !is_null($res) )
			{
				if ( $this->renderContainer != false )
				{
					$this->_result[] = '<div class="control '.str_replace('/','-',$id).'">';
				}
				
				if ( $this->renderLabel != false )
				{
					$this->_result[] = '<label for="'.$id.'">' . $label . '</label>';
				}
				
				if ( $this->renderField != false )
				{
					$this->_result[] = $res ;
				}
				
				if ( $this->renderDescription != false )
				{
					if ( @$field['description'] )
					{
						$this->_result[] = '<div class="description">' . $field['description'] . '</div>';
					}
					
				}
				
				if ( $this->renderContainer != false )
				{
					$this->_result[] = '</div>';
				}
			}
		}
	}

	
	public function endForm ()
	{
		if ( !is_null($this->_currentFieldset) )
		{
			$this->_result[] = '</fieldset>' ;
		}
			
		
		
		$this->_result[] = '<div id="submit_'.$this->_table.'" class="submit">';
		if ($this->_hasRequired)
		{
			$this->_result[] = '<span class="p">Fields highlighted by * sign are required.</span>' ;
		}
		$this->_result[] = '<input type="submit" value="Submit" method="post" class="right">' ;
		$this->_result[] = '</div>';
		$this->_result[] = '</form>' ;
	
		$this->_result[] = '<script type="text/javascript">if(ajsf){';
		$this->_result[] = 'ajsf.load("aejax");';
		$this->_result[] = 'ajsf.load("aepopup");';
		$this->_result[] = 'ajsf.load("aeforms");';
		if ( $this->_hasFile )
		{
			$this->_result[] = 'ajsf.load("aeforms-upload");';
		}
		$this->_result[] = 'ajsf.ready(function(){if(ajsf.forms) new ajsf.forms.Form(_("#'.$this->_dbID .'/'.
						$this->_table.'"),true); _("[type=submit]","#'.$this->_dbID .'/'.
						$this->_table.'").on("click",function(e){ajsf.prevent(e); _("#'.$this->_dbID .'/'.
							$this->_table.'").doSubmit("'._('Some required fields are not valid').'");}); });' ;
		$this->_result[] = '}</script>';
		
	}
}

?>
