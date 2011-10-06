#!/bin/sh
echo 'Creating configuration'
touch index.php
echo '<?php' > index.php
echo "require_once('app-conf.php') ;" >> index.php
echo 'App::start() ;' >> index.php
echo '?>' >> index.php

touch app-conf.php
echo '<?php' > app-conf.php
echo '' >> app-conf.php
echo "define ( 'DEBUG' , true );" >> app-conf.php
echo "define ( 'ROOT', dirname(__FILE__) . DIRECTORY_SEPARATOR ) ;" >> app-conf.php
echo "require_once ( ROOT . DIRECTORY_SEPARATOR. 'aenoa-server'.DIRECTORY_SEPARATOR.'bootstrap.php' ) ;" >> app-conf.php
read -p 'First give the new application name: ' APP_NAME
echo "Config::set(App::APP_NAME,'"$APP_NAME"');" >> app-conf.php
echo 'Please give the lead developer email for this application: '
read APP_EMAIL
echo "Config::set(App::APP_EMAIL,'"$APP_EMAIL"');" >> app-conf.php
echo "Config::set(App::APP_LANG,'en_US');" >> app-conf.php
echo "Config::set(App::USER_REGISTER_AUTH,true);" >> app-conf.php
echo "Config::set(App::USER_CORE_SYSTEM,true);" >> app-conf.php
echo "Config::set(App::DBS_AUTO_EXPAND, true);" >> app-conf.php
echo '' >> app-conf.php
echo '?>' >> app-conf.php
ae_trimfile app-conf.php
