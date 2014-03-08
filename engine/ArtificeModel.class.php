
<?php

/**
* Classe ArtificeModel (database)
* Apporte une connexion à le base de données, les modèles en héritent
*
* @author Kévin Barreau <kevin.barreau.info@gmail.com>
* LR 07/03/2014
*
**/

class ArtificeModel
{
	protected $db = NULL;

	public function __construct()
	{
		$this->db = AC::get('db');
	}
}
