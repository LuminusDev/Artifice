
<?php

	$router = AC::get('router');

	/* base et error404 sont les chemins par défaut pour l'accueil et pour la page d'erreur 404 */
	$router->connectDefault('base','home');
	$router->connectDefault('error404','error404');

	/* connect($path, $url, $type)
			$path
				Path to the function of controller in the controller repertory 
				(if function is 'index', do NOT write it)
			$url
				Url to connect to the path
			$type
				{'DIRECT', 'SECONDARY', 'MAIN'}
	*/
	$router->connect('home', 'newhome', 'SECONDARY');

	/* Sample of uses */
	$router->connect('sample/fonction1', 'first/{$1}', 'MAIN');
	$router->connect('sample/fonction2', 'second', 'DIRECT');
