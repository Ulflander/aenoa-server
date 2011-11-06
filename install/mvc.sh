#!/bin/sh
echo 'Creation of new MVC package'
read -p 'Please give the name of the corresponding table/concept: ' name
cd ../app/controllers/
f=$name'Controller.php'
touch $f
echo '<?php' >> $f
echo 'class '$name'Controller extends Controller {' >> $f
echo '\tfunction index () {' >> $f
echo '\t}' >> $f
echo '}' >> $f
echo '?>\c' >> $f
cd ../models
f=$name'Model.php'
touch $f
echo '<?php' >> $f
echo 'class '$name'Model extends Model {' >> $f
echo '\tfunction index () {' >> $f
echo '\t}' >> $f
echo '}' >> $f
echo '?>\c' >> $f
cd ../templates/html
f=$name'.thtml'
mkdir $name
cd $name
touch $f