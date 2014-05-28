<?php

/**
 * Classe Token
 * Permet de gérer un token de sécurité dans Artifice
 *
 * @author Kévin Barreau <kevin.barreau.info@gmail.com>
 * LR 28/05/2014
**/

class Token
{
	private $string_token = null;

	public function __construct()
	{
		// Nothing
	}

	public function create()
	{
		$this->string_token = $this->randomName(10);
		$_SESSION[SESSAJAXTOKEN] = $this->tokenize($this->string_token);
	}

	public function get()
	{
		return $this->string_token;
	}

	public function tokenize($string)
	{
		return hash('sha256', SESSAJAXTOKENSALT.$string);
	}

	public function check($string)
	{
		return $this->tokenize($string) === $_SESSION[SESSAJAXTOKEN];
	}

	public function randomName($taille)
	{
		$string = "";
		$chaine = "abcdefghijklmnpqrstuvwxy0123456789";
		$len = strlen($chaine);
		srand((double)microtime()*1000000);

		for ($i = 0; $i < $taille; $i++) {
			$string .= $chaine[rand()%$len];
		}

		return $string;
	}
}