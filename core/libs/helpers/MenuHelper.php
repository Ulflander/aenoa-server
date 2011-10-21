<?php


class MenuHelper {
	
	/**
	 * Displays a menu by retrieving Webpage pages.
	 * 
	 * @param object $class [optional] Class names for the UL element
	 * @param object $showCurrent [optional] Should the current page be shown, default: true
	 * @return void
	 */
	public static function displayWebpageMenu ( $class = '' , $showCurrent = true ) 
	{
		$menu = Webpage::getWebpagePages () ;
		$current = Webpage::getCurrent () ;
		$default = Webpage::getDefault () ;
		
		$str = '<ul id="webpages-menu" class="'. $class .'">' . "\n" ;
		foreach ( $menu as $menuItem )
		{
			if ( $menuItem['filename'] != $current )
			{
				$str .= '<li><a href="' . url() ;
				if ( $default != $menuItem['filename'] )
				{
					$str .= $menuItem['filename'] ;
				}
				$str .= '">' . $menuItem['title'] . '</a></li>' . "\n" ;
			} else if ( $showCurrent == true )
			{
				$str .= '<li class="current">' . $menuItem['title'] . '</li>' . "\n" ;
			}
		}
		$str .= '</ul>'  . "\n" ;
		
		echo $str ;
	}
	
	/**
	 * Generates and displays a menu of the whole backend
	 * 
	 * @param object $class [optional] Class names for the UL element
	 * @param object $showCurrent [optional] Should the current page be shown, default: true
	 * @return void
	 */
	public static function displayBackendMenu ( $struct = 'main' , $class = '' , $showCurrent = true ) 
	{
		
		$baseURL = url() ;
		
		if ( debuggin() )
		{
			$str = '<ul class="no-list-style aemenu '. $class .'">' . "\n" ;
			$str .= '<li class=""><a href="'.url().'maintenance/status" class="icon16 home">' . ucfirst(sprintf(_('Manager home'),$struct)) . '</a></li>' . "\n" ;
			$str .= '</ul>' ;

			echo $str ;
		}
		
		$str = '<ul id="dev-menu" class="no-list-style aemenu '. $class .'">' . "\n" ;
		$str .= '<li class="caption">' . _('Development') . '</li>' . "\n" ;
		$str .= '<li><a href="'.url().'dev/PHPI18n" title="'. _('Extract I18n string') .'" class="icon16 i18n">' . _('Extract I18n string') . '</a></li>' . "\n" ;
		$str .= '<li><a href="'.url().'dev/GenerateStructureDocFile" title="'. _('Update structures doc') .'" class="icon16 manual">' . _('Update structures doc') . '</a></li>' . "\n" ;
		$str .= '<li><a href="'.url().'dev/GenerateDocumentation" title="'. _('Create documentation') .'" class="icon16 manual">' . _('Create documentation') . '</a></li>' . "\n" ;
		$str .= '</ul>'  . "\n" ;
		
		
		echo $str ;
		
		
		$structures = App::getAllDBs();
		$str = '' ;
		
		foreach ($structures as $id => &$db )
		{
		    self::displayStructuresMenu($id) ;
		}
		
		echo $str ;
		
		$menu = array () ;
		
		$futil = new FSUtil(ROOT);
		$files = $futil->getTree(AE_APP_WEBPAGES);
		
		$str = '<ul id="webpages-menu" class="no-list-style aemenu '. $class .'">' . "\n" ;
		$str .= '<li class="caption">' . _('Webpages') . '</li>' . "\n" ;
		foreach ( $files as $file )
		{
		    if ( strpos($file, '.html') === false)
		    {
			continue;
		    }
		    $file = str_replace(AE_APP_WEBPAGES,'',$file) ;
		    $str .= '<li><a href="'.url().'webpages/edit/'.$file.'" title="'.sprintf(_('Edit %s webpage'), $file ).'" class="icon16 file">' . $file . '</a></li>' . "\n" ;
		}
		$str .= '<li><a href="'.url().'webpages/create" title="'. _('Add a new webpage') .'" class="icon16 add">' . _('Add a new webpage') . '</a></li>' . "\n" ;
		$str .= '</ul>'  . "\n" ;
		
		echo $str ;
		
	}
	/**
	 * Generates and displays a menu using database structures.
	 * 
	 * @param object $class [optional] Class names for the UL element
	 * @param object $showCurrent [optional] Should the current page be shown, default: true
	 * @return void
	 */
	public static function displayStructuresMenu ( $struct = 'main' , $class = '' , $showCurrent = true ) 
	{
		$db = App::getDatabase($struct) ;
		$baseURL = url() . 'database/' . $struct . '/' ;
		
		$str = '<ul class="no-list-style aemenu '. $class .'">' . "\n" ;
		$str .= '<li class="caption">' . ucfirst(sprintf(_('%s database'),$struct)) . '</li>' . "\n" ; ;
		
		if ( $db->isUsable() )
		{
			$structure = $db->getStructure () ;
			
			foreach ( $structure as $table => $fields )
			{
				$class = 'icon16' ;
				switch($table)
				{
					case 'ae_users': $class = 'icon16 user' ; break;
					case 'ae_groups': $class = 'icon16 group' ; break;
					case 'ae_api_keys': $class = 'icon16 lock' ; break;
					default: $class ='icon16 db';
				}
				
				$tableName = _(ucfirst(humanize($table,'_'))) ;
				
				if ( strpos($tableName,'Ae ') !== false )
				{
					$tableName = str_replace('Ae ', '' , $tableName ) ;
				}
				
				$str .= '<li><a href="' . $baseURL . $table .'/index' ;
				$str .= '" class="'.$class.'">' . $tableName . '</a></li>' . "\n" ;
			}
		}
		$str .= '</ul>'  . "\n" ;
		
		echo $str ;
	}
	
}
?>