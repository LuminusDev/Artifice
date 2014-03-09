<?php 
	
	/* Configuration TemplateEngine */
	define( "SITE_NAME", 'Artifice');
	define( "BASE_URL", 'http://dev.luminus.loc/'); //must finish by a '/'
	define( "BASE_FOLDER", '/'); //folder where artifice is placed on the server, if not on the root. Example : "/myartifice"

	define( "WEBROOT", $_SERVER['DOCUMENT_ROOT']);
	define( "VIEWPATH", __DIR__.'/../views/'); //must finish by a '/'
	define( "CONTROLLERPATH", __DIR__.'/../controllers/'); //must finish by a '/'
	define( "MODELPATH", __DIR__.'/../models/'); //must finish by a '/'
	define( "TEMPLATEPATH", VIEWPATH.'templates/'); //must finish by a '/'

	/* Configuration Cache */
	define( "CACHEPATH", __DIR__.'/../cache/'); //must finish by a '/'
	define( "CACHEEXT", '.ca' );

	/* Configuration EngineException */
	define( 'DIVSTYLE' , 'background: #ffbcc1; width: 800px; margin: 100px auto 100px auto; border-radius: 5px; padding: 30px; border: 1px solid #c6000d;');
	define( 'MAILPOSTMASTER', 'postmaster@yt-stats.com');

	/* Configuration Widget */
	define("WIDGETPATH", __DIR__.'/../widgets/'); //must finish by a '/'
	define("MODELFILE", 'model.php');
	define("VIEWFILE", 'view.php');

	/* Configuration AjaxEngine */
	define( "SESSAJAX",'widgetsAjax');
	define( "SESSAJAXTOKEN", 'widgetsAjaxToken');

	/* Configuration Base de données */
	define( "DBHOST", 'localhost');
	define( "DBNAME", 'your_db_name');
	define( "DBUSER", 'your_db_user');
	define( "DBPWD", 'your_db_password');
