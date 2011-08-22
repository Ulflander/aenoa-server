<?php

$dkRoot = dirname(__FILE__) . DS ;

define ( 'DK_DEV_KIT' , $dkRoot ) ;
define ( 'DK_TASKS' , $dkRoot . 'tasks' . DS ) ;
define ( 'DK_PLUGINS' , $dkRoot . 'plugins' . DS ) ;
define ( 'DK_TEMPLATES' , $dkRoot . 'templates' . DS ) ;

Config::set ( App::APP_PUBLIC_REPOSITORY , DK_DEV_KIT . 'public' ) ;

addAutoLoadPath ( $dkRoot . DS . 'dev-kit-core/' ) ;

Template::addTemplatesPath(DK_TEMPLATES);

TaskManager::addRedirection ( 'Manage' , 'ManageProject' ) ;

new TaskManager () ;

?>