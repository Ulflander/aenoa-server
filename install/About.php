
/*

File: Installing Aenoa Server

How to install Aenoa Server:

You have two ways to install Aenoa Server, through a shell script or downloading yourself packages.

Using shell script:

This has been tested on Debian 5+, Ubuntu 10+, Mac OS 10.5+

Just open a terminal and do the following..
(start code)
// Go into your DocumentRoot folder
cd /your/document/root

// Create a new folder for your new app
mkdir my-project

// Go into your new folder
cd my-project

// And finally run Aenoa Server install script
curl http://up.aenoa-systems.com/install.sh | sh
(end)

This will download up-to-date packages required to run an Aenoa Server application,
and will create all required files and folders for starting properly a new app.

You can see content of install script at http://up.aenoa-systems.com/install.txt

Installing manually:


Common application structure:
(start code)
	/aenoa-server: Aenoa Server library
	/app: Application folder
	 /controllers: Controllers
	 /hooks: Hooks files
	 /locale: The language files in gettext mode
	   /en_US: Default language
	 /models: Main models
	 /plugins: unused yet, required Aenoa Framework folder
	 /services: Services files
	 /structures: Structure files of databases
	 /templates: All about views
	/assets: Assets used for application
	 /js: JavaScript files
	 /css: CSS files
	 /img: Images
	/static:
	 /acf: the CSS framework used for Aenoa Server backend
	 /ajsf: the Javascript framework used for Aenoa Server backend
(end)


Using Aenoa Server for more than one app:

Just move the aenoa-server folder in a parent folder of all your apps, as following.
(start code)

/aenoa-server
  /your-first-app
    /app
	/assets
	/...
  /another-app
    /app
	/assets
	/...

(end)

Then change in app-conf.php files the include of aenoa-server bootstrap.
(start code)

// Change this
require_once ( ROOT . DIRECTORY_SEPARATOR. 'aenoa-server'.DIRECTORY_SEPARATOR.'bootstrap.php' ) ;

// to this
require_once ( dirname(ROOT) . DIRECTORY_SEPARATOR. 'aenoa-server'.DIRECTORY_SEPARATOR.'bootstrap.php' ) ;

(end)


About others files in install folder:

Files contained in install folder are models used by Aenoa Server shell installer.
You can copy them if needed into your app folders.


*/
