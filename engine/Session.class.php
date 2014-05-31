<?php

/**
* Classe Session
* Encapsulation du système de session PHP.
* Permet une utilisation non-bloquante des sessions PHP
* dans le cadre d'accès simultanés, comme les widgets Ajax
*
* @author Kévin Barreau <kevin.barreau.info@gmail.com>
* LR 31/05/2014
*
**/

class Session
{
	static private $lock = false;

	public static function start()
	{
		if (session_id() === "" || !self::$lock) {
			session_start();
		}
	}

	public static function stop()
	{
		if (session_id() !== "" && !self::$lock) {
			session_write_close();
		}
	}

	public static function setLock($lock)
	{
		self::$lock = $lock;
		// si la session est bloquante, on demarre la session
		if ($lock) {
			self::start();
		}
	}

	/* variable parameter number */
	public static function get()
	{
		$keys = func_get_args();
		self::start();
		$tmp = $_SESSION;
		if (!self::$lock) {
			self::stop();
		}

		if (func_num_args() < 1) {
			return $tmp;
		}

		$last_key = array_pop($keys);
		foreach ($keys as $key) {
		    if (!isset($tmp[$key]) || !is_array($tmp[$key])) {
	            return false;
	        }
	        $tmp = $tmp[$key];
		}
		return isset($tmp[$last_key]) ? $tmp[$last_key] : false;
	}

	/* variable parameter number */
	public static function set($value)
	{
		if (func_num_args() < 2) {
			return false;
		}
		$keys = array_slice(func_get_args(),1);
		self::start();

		$last_key = array_pop($keys);
	    $tmp = &$_SESSION;
	    foreach ($keys as $key) {
	        if (!isset($tmp[$key]) || !is_array($tmp[$key])) {
	            $tmp[$key] = array();
	        }
	        $tmp = &$tmp[$key];
	    }
	    $tmp[$last_key] = $value;

		if (!self::$lock) {
			self::stop();
		}
		return true;	
	}

	/* variable parameter number */
	public static function delete()
	{
		$keys = func_get_args();
		self::start();

		$last_key = array_pop($keys);
	    $tmp = &$_SESSION;
	    foreach ($keys as $key) {
	        if (!isset($tmp[$key]) || !is_array($tmp[$key])) {
	            $tmp[$key] = array();
	        }
	        $tmp = &$tmp[$key];
	    }
	    unset($tmp[$last_key]);

		if (!self::$lock) {
			self::stop();
		}
		return true;	
	}
}
