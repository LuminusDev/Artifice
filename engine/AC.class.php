
<?php

/**
* Classe AC (AppContainer)
* Tableaux contenant les classes de l'application Artifice grâce à Pimple
*
* @author Kévin Barreau <kevin.barreau.info@gmail.com>
* LR 15/02/2014
*
**/

require 'Pimple.php';

class AC
{
	static private $pimple = null;
	static private $pimple_require = array();

	public static function checkInstance()
	{
		if(self::$pimple === null)
			self::$pimple = new Pimple();
	}

	public static function getInstance()
	{
		self::checkInstance();
		return self::$pimple;
	}

	public static function set($key, $object, $require = null)
	{
		self::checkInstance();
		self::$pimple_require[$key] = $require;
		self::$pimple[$key] = $object;
	}

	public static function get($key)
	{
		self::checkInstance();
		if (isset(self::$pimple_require[$key]) && is_callable(self::$pimple_require[$key])) {
			call_user_func(self::$pimple_require[$key]);
		}
		return isset(self::$pimple[$key]) ? self::$pimple[$key] : null;
	}

	public static function loadModel($name)
	{
		$model = self::get($name."_models");
		if (empty($model)) {
			$res_explode = explode('/',$name);
			$classname = array_pop($res_explode)."Model";
			self::set($name."_models",
				function ($c) use ($classname) {
					return new $classname;
				},
				function () use ($name) {
					require_once MODELPATH.$name.'.php';
				}
			);
			$model = self::get($name."_models");
		}
		return $model;
	}
}
