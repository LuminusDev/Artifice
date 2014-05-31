
<?php

/**
 * Classe Widget
 * Permet de creer un widget dans la page
 *
 * @author Guillaume Marques <guillaume.marques33@gmail.com>
 * @author Kévin Barreau <kevin.barreau.info@gmail.com>
 * LR 31/05/2014
 **/

date_default_timezone_set('Europe/Paris');

require_once 'Cache.class.php';

abstract class Widget
{
	protected $_name; //Nom du widget
	protected $_var; // Variable qui contiendra le contenu html du widget

	protected $_cacheActivated; //booléen pour l'activation du cache
	protected $_cache; //Cache du widget
	protected $_cacheDuration; //Durée du cache du widget

	protected $_isAjax; //booléen pour affichage du widget en ajax

	protected $_content = array(); //Contenu du widget
	protected $data = array(); //Donnees du model pour la vue
	protected $_widget = array(); //Widgets contenu dans le widget /INCEPTION/

	/**
	 * __construct
	 *
	 * @param String::name, nom du widget
	 * @param String::var, variable qui contiendra ce widget
	**/
	function __construct($name, $cacheDuration = 0, $var = null, $isAjax = false)
	{
		$this->_name = $name;
		$this->_var = $var;
		$this->_cacheDuration = $cacheDuration;
		$this->_cacheActivated = ($cacheDuration>60);

		$this->setIsAjax($isAjax);

		if ($this->_cacheActivated) {
			$this->_cache = new Cache($name, $cacheDuration);
		}
	}

	abstract public function run();

	/**
	 * getName
	 * Retroune le nom du widget
	**/
	public function getName()
	{
		return $this->_name;
	}

	/**
	 * setIsAjax
	 * Retourne si le widget doit être affiché en ajax ou non
	 * @param String::name, nom du widget
	**/
	public function setIsAjax($isAjax = false)
	{
		$this->_isAjax = $isAjax;
		if ($this->_isAjax) {
			// widget en session pour le retrouver avec ajaxEngine
			Session::set($this->_cacheDuration, SESSAJAX, $this->_name, 'cacheDuration');
			Session::set($this->_content, SESSAJAX, $this->_name, 'content');
			Session::set($this->getWidget(), SESSAJAX, $this->_name, 'widget');
		}
	}

	/**
	 * getIsAjax
	 * Retourne si le widget doit être affiché en ajax ou non
	**/
	public function getIsAjax()
	{
		return $this->_isAjax;
	}

	/**
	 * getContent
	 * Retourne les variables contenues dans le widget
	**/
	public function getContent()
	{
		return $this->_content;
	}

	/**
	 * getCacheDuration
	 * Retourne la durée de mise en cache du widget
	**/
	public function getCacheDuration()
	{
		return $this->_cacheDuration;
	}

	/**
	 * getCacheActivated
	 * Retourne si le widget est mis en cache
	**/
	public function getCacheActivated()
	{
		return $this->_cacheActivated;
	}

	/**
	 * getVar
	 * Retourne le nom de la variable qui doit contenir le contenu du widget
	 **/
	public function getVar()
	{
		return $this->_var;
	}

	/**
	 * setVar
	 * Defini le nom de la variable qui doit contenir le contenu du widget
	 **/
	public function setVar($var)
	{
		$this->_var = $var;
	}

	/**
	 * getWidget
	 * Retourne le tableau des widgets inclus sous la forme :
	 * name => cacheDuration => 3600
	 *      => content => array getContent()
	 *      => widget => arrau getWidget()
	**/
	public function getWidget()
	{
		$array = array();
		foreach ($this->_widget as $k => $v) {
			$array[$k]['cacheDuration'] = $v->getCacheDuration();
			$array[$k]['content'] = $v->getContent();
			$array[$k]['widget'] = $v->getWidget();
		}
		return $array;
	}

	/**
	 * assign
	 * Permet de donnnées des valeurs aux variables contenues dans le widget
	 *
	 * @param Array( nomVariable=>valeur ) data
	 **/
	public function assign($data = array())
	{
		foreach ($data as $k=>$v) {
			if ($v instanceof Widget) {
				if ($this->_cacheDuration > $v->getCacheDuration() && $v->getCacheDuration() > 60) {
					$v->setIsAjax(true);
				}
				$v->setVar($k);
				$this->_widget[$k]=$v;
				if ($this->_isAjax) {
					Session::set($this->getWidget(), SESSAJAX, $this->_name, 'widget');
				}
			}
			else {
				$this->_content[$k]=$v;
				if ($this->_cacheActivated) { //MAJ nom du cache
					$this->_cache->params($k, $v);
				}
				if ($this->_isAjax) {
					Session::set($this->_content, SESSAJAX, $this->_name, 'content');
				}
			}
		}
	}

	/**
	 * getHTML
	 *
	 * @return code HTML du widget
	**/
	public function getHTML()
	{

		if ($this->_isAjax) {
			return $this->getContentForAjax();
		}
		//Sinon
		return $this->getContentForHTTP();
	}

	/**
	 * getContentForHTTP()
	 *
	 * @return code HTML du widget pour une reqûete HTTP
	**/
	public function getContentForHTTP()
	{
		//Si le fichier est déjà enregistré on l'affiche
		if ($this->_cacheActivated && !$this->_cache->isCacheExpired()) {
			return $this->_cache->getCacheContent();
		}

		//On récupère l'affichage de tous les widgets inclus
		if (count($this->_widget) > 0) {
			foreach ($this->_widget as $widget) {
				$html[$widget->getVar()]=$widget->getHTML();
			}
			extract($html); //Extraction de l'html 
		}


		extract($this->_content);		

		ob_start(); //On démarre la cache

		echo '<!-- Generation: '.date('d/m/Y H:i:s').'  -- Widget: '.$this->_name.' -->';

		// model du widget
		$this->run();
		// vue du widget
		include WIDGETPATH.$this->_name.'/'.VIEWFILE;

		$result = ob_get_contents();

		ob_end_clean();

		if ($this->_cacheActivated) {
			$this->_cache->updateCacheContent($result);
		}

		return $result;
	}

	/**
	* getContentForAjax
	*
	* @return code HTML et javascript pour un futur appel en Ajax
	**/
	public function getContentForAjax()
	{
		$content = '<div class="widgetAjax_'.preg_replace('#/#','-',$this->_name).'"></div>';
		return $content;
	}
}
