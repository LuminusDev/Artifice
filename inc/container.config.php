
<?php

/**************** Artifice Configuration *********************/

	AC::set('router',
		function ($c) {
			return new Router;
		},
		function () {
			require_once __DIR__.'/../engine/Router.class.php';
			require_once __DIR__.'/../inc/routes.config.php';
		}
	);

	AC::set('db',
		function ($c) {
			return new PDO('mysql:host='.DBHOST.';dbname='.DBNAME, DBUSER, DBPWD);
		},
		function () {
			// Nothing
		}
	);

/**************** Perosnnal Configuration *********************/
