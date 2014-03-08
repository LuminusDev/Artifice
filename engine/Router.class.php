<?php if (!defined('BASE_URL')) exit; ?>
<?php

/**
 * Classe Router
 * Recupere l'url et appelle le controleur correspondant
 * 	Gestion des erreurs 404
 * 	Gestion du reverse routing
 * 	Gestion des routes personnalisées
 *
 * @author Kévin Barreau <kevin.barreau.info@gmail.com>
 * LR 15/02/2014
**/
	class Router
	{

		private $proper_url;
		private $controller;
		private $function;
		private $params;
		private $current_repertory;

		private $pattern_parameters = '[-a-zA-Z0-9\.]+';
		private $pattern_module_controller_function = '/^[a-z0-9]+$/';
		private $pattern_wildcards_anonymous = '/{\$([\d]+)}/';

		private $routes_direct  = array();
		private $routes_second  = array();
		private $routes_default = array();

		private $routes_mode = array('RT_DIRECT' => 'DIRECT',
									 'RT_SECONDARY' => 'SECONDARY',
									 'RT_MAIN' => 'MAIN'
								);

		const RT_DIRECT    = 'DIRECT';    // route prioritaire, et la route basique est autorisee en 301
		const RT_SECONDARY = 'SECONDARY'; // route secondaire en 301
		const RT_MAIN      = 'MAIN';      // route prioritaire, et la route basique est interdite
		
		function __construct()
		{
			if (!defined('CONTROLLERPATH')) exit('Config file incomplete.');
			$this->current_repertory = CONTROLLERPATH;
			$this->proper_url = $this->remove_slash(urldecode(parse_url(substr($_SERVER['REQUEST_URI'], strlen(BASE_FOLDER)), PHP_URL_PATH)));
			$this->controller = null;
			$this->function = null;
			$this->params = array();
		}

		public function get_url()
		{
			return $this->proper_url;
		}

		public function run()
		{
			// recherche du chemin a prendre
			if (empty($this->proper_url)) {
				require CONTROLLERPATH.$this->routes_default['base'].'.php';
				$class = ucfirst($this->routes_default['base']);
				$this->controller = new $class();
				$this->function = 'index';
			} elseif ($this->isBasicRoute()) {
				$this->checkPriorityBasicRoute();
			} elseif (!$this->isPersoRoute()) {
				$this->error404();
			}

			call_user_func_array(array($this->controller,$this->function), $this->params);
		}

		private function isBasicRoute()
		{
			return $this->isValidPath($this->proper_url);
		}

		public function isValidPath($path)
		{
			$this->current_repertory = CONTROLLERPATH;
			$this->controller = null;
			$this->function = null;
			$this->params = array();
			$path = $this->remove_slash($path);

			$explode_url = explode('/', $path);

			foreach ($explode_url as $name) {
				// module
				if (empty($this->controller) && preg_match($this->pattern_module_controller_function, $name) && file_exists($this->current_repertory.$name)) {
					$this->current_repertory .= $name.'/';
				}
				// controller
				elseif (empty($this->controller) && preg_match($this->pattern_module_controller_function, $name) && file_exists($this->current_repertory.$name.'.php')) {
					require_once $this->current_repertory.$name.'.php';
					$class = ucfirst($name);
					$this->controller = new $class();
					$this->current_repertory .= $name.'/';
				}
				// fonction et parametres
				elseif (!empty($this->controller) && !empty($name)) {
					$method = array();
					if (empty($this->function) && preg_match($this->pattern_module_controller_function, $name) && $name !== 'index') {
						$method = array($this->controller,$name);
						if (is_callable($method)) {
							$this->function = $name;
							$this->current_repertory .= $name.'/';
						}
					}
					if ((empty($method) || empty($this->function)) && preg_match('#^'.$this->pattern_parameters.'$#', $name)) {
						$this->params[] = $name;
					}
				} else {
					return false;
				}
			}
			// index lorsqu'il n'y a pas de fonction explicite
			if (!empty($this->controller) && empty($this->function)) {
				$this->function = 'index';
			}
			// verification existence controleur et fonction
			if (empty($this->controller) || empty($this->function)) {
				return false;
			}
			// verification nombre d'argument
			$method = new ReflectionMethod(get_class($this->controller), $this->function);
			$nb_parameters = $method->getNumberOfParameters();
			if ($nb_parameters !== count($this->params)) {
				return false;
			}
			
			return true;
		}

		private function isPersoRoute()
		{
			// parcours des tableaux direct et second sur l'url
			$params = array();
			$array_key = array();
			foreach ($this->routes_direct as $path => $array_path) {
				$url = preg_replace_callback(
							$this->pattern_wildcards_anonymous,
							function ($matches) use (&$array_key) {
								$array_key[$matches[1].'p'] = null;
								return '(?P<'.$matches[1].'p>'.$this->pattern_parameters.')';
							},
							$this->remove_slash($array_path['url'])
						);
				if (preg_match('#^'.$url.'$#', $this->proper_url, $params)) {
					// ajout parametres
					$params = array_intersect_key($params, $array_key);
					ksort($params);
					$pathWithParams = $this->remove_slash($path).'/'.implode('/', $params);
					//var_dump($url);var_dump($pathWithParams);var_dump($this->proper_url);exit;
					return $this->isValidPath($pathWithParams);
				}
			}
			foreach ($this->routes_second as $url => $path) {
				$url = preg_replace_callback(
							$this->pattern_wildcards_anonymous,
							function ($matches) use (&$array_key) {
								$array_key[$matches[1].'p'] = null;
								return '(?P<'.$matches[1].'p>'.$this->pattern_parameters.')';
							},
							$this->remove_slash($url)
						);
				if (preg_match('#^'.$url.'$#', $this->proper_url, $params)) {
					// ajout parametres
					$params = array_intersect_key($params, $array_key);
					ksort($params);
					$pathWithParams = $this->remove_slash($path).'/'.implode('/', $params);
					if ($this->isValidPath($pathWithParams)) {
						$this->checkPriorityPersoRoute($path);
					} else {
						return false;
					}
				}
			}
			return false;
		}

		private function checkPriorityBasicRoute()
		{
			// verification validite du chemin par rapport aux routes personnalisees
			$this->current_repertory = substr($this->current_repertory, strlen(CONTROLLERPATH), -1); // suppression du dossier des controleurs et du dernier slash
			foreach ($this->routes_direct as $path => $array_path) {
				if ($this->remove_slash($path) === $this->current_repertory) {
					if (isset($array_path['main'])) {
						$this->error404();
					} else {
						$url = preg_replace_callback(
									$this->pattern_wildcards_anonymous, 
									function ($matches) {
										return isset($this->params[$matches[1]-1]) ? $this->params[$matches[1]-1] : $matches[0];
									},
									$this->remove_slash($array_path['url'])
								);
						$this->redirect301($url);
					}
				}
			}
		}

		private function checkPriorityPersoRoute($pathToCheck)
		{
			// redirection sur main s'il existe avec ce path
			$pathToCheck = $this->remove_slash($pathToCheck);
			foreach ($this->routes_direct as $path => $array_path) {
				if ($this->remove_slash($path) === $pathToCheck) {
					$url = preg_replace_callback(
								$this->pattern_wildcards_anonymous, 
								function ($matches) {
									return isset($this->params[$matches[1]-1]) ? $this->params[$matches[1]-1] : $matches[0];
								},
								$this->remove_slash($array_path['url'])
							);
					$this->redirect301($url);
				}
			}
			// redirection sur url basique sinon
			$url = substr($this->current_repertory, strlen(CONTROLLERPATH), -1); // suppression du dossier des controleurs et du dernier slash
			$url .= '/'.implode('/',$this->params);
			$this->redirect301($url);
		}

		public function error404()
		{
			require CONTROLLERPATH.$this->routes_default['error404'].'.php';
			$class = ucfirst($this->routes_default['error404']);
			$controller = new $class();
			$function = 'index';
			header("HTTP/1.0 404 Not Found", true, 404);
			call_user_func_array(array($controller,$function), array());
			exit();
		}

		public function redirect301($url)
		{
			header('HTTP/1.1 301 Moved Permanently', false, 301);
			header('Location: '.BASE_URL.'/'.$url.$_SERVER['QUERY_STRING'].'');
			exit();
		}

		public function connect($path, $url, $type = 'DIRECT')
		{
			if ($type === $this->routes_mode['RT_SECONDARY']) {
				$this->routes_second[$url] = $path;
			} else { /* direct ou main */
				$this->routes_direct[$path]['url'] = $url;
				if ($type === $this->routes_mode['RT_MAIN']) {
					$this->routes_direct[$path]['main'] = true;
				}
			}
		}

		public function connectDefault($name, $path)
		{
			$this->routes_default[$name] = $path;
		}

		/* Change this by "...$params" in PHP 5.6 instead of "array_slice(func_get_args(), 1)" */
		public function url($pathToCheck)
		{
			$exist = true;
			$params = array_slice(func_get_args(), 1);
			$pathToCheck = $this->remove_slash($pathToCheck);
			foreach ($this->routes_direct as $path => $array_path) {
				if ($this->remove_slash($path) === $pathToCheck) {
					$url = preg_replace_callback(
								$this->pattern_wildcards_anonymous, 
								function ($matches) use ($params, &$exist) {
									if (isset($params[$matches[1]-1])) {
										return $params[$matches[1]-1];
									}
									$exist = false;
									return $matches[0];
								},
								$this->remove_slash($array_path['url'])
							);
					return ($exist) ? BASE_URL.'/'.$url : BASE_URL;
				}
			}
			// Default url else

			$url = $this->remove_slash($pathToCheck.'/'.implode('/',$params));
			return BASE_URL.'/'.$url;
		}

		public function remove_slash($path)
		{
			if (!empty($path)) {
				$path = trim($path);
				$begin = 0;
				if ($path[0] === '/') {
					$begin = 1;
				}
				if ($path[strlen($path)-1] === '/') {
					$path = substr($path,$begin,-1);
				} else {
					$path = substr($path,$begin);
				}
			}
			return $path;
		}
	}
