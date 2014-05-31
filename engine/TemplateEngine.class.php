<?php

date_default_timezone_set('Europe/Paris'); 


/**
 * Le moteur
 *
 * @author: Guillaume Marques <guillaume.marques33@gmail.com>
 * @author: Kévin Barreau <kevin.barreau.info@gmail.com>
 * LR 31/05/14
**/

require_once 'Widget.class.php';
require_once 'EngineException.class.php';


/* Classe */
class TemplateEngine
{
	protected $_template; //nom du template utilisé
	protected $_widget = array(); //Widgets contenus dans la page
	protected $_meta = array(); //Contenu des balises <meta> de notre

	protected $_lang; //langue

	/**
	 * __contruct
	**/
	function __construct()
	{
		// session bloquante
		Session::setLock(true);

		// indispensable pour cleaner ajax
		Session::delete(SESSAJAX);
		Session::delete(SESSAJAXTOKEN);

		// creation token CSRF
		AC::get('token')->create();
	}

	/**
	 * Méthode createWidget
	 * Crée un widget et le retourne
	 *
	 * @param String::$name
	 *			Nom du widget à créer
	 * @param String::$variable
	 *			Nom de la variable qui va contenir le contenu du widget
	 * @param Array::$assign
	 *			Tableau contenant les variables à assigner dans le widget
	 * @return Widget
	 *			Widget venant d'être créé	
	**/
	public function createWidget($name, $variable = null, $assign = array())
	{
		require_once WIDGETPATH.$name.'/model.php';
		$res_explode = explode('/', $name);
		$className = array_pop($res_explode);
		$tmpWidget = new $className($variable);

		$tmpWidget->assign($assign);

		if (!empty($variable)) {
			$this->_widget[$tmpWidget->getVar()]= $tmpWidget;
		}

		return $tmpWidget;
	}

	/**
	 * Méthode displayWidget
	 * Crée et affiche le contenu d'un widget dont le nom est passé en paramètre
	 *
	 * @param String::$name
	 *			Nom du widget à afficher
	 * @param Array::$assign
	 *			Tableau contenant les variables à assigner dans le widget	
	**/
	public function displayWidget($name, $assign = array())
	{
		echo $this->createWidget($name)->getHTML();
	}

	/**
	 * Méthode display
	 * Permet l'affichage de la page
	**/
	public function display()
	{
		//On récupère l'affichage de tous les widgets
		if (count($this->_widget) > 0) {
			foreach ($this->_widget as $widget) {
				$html[$widget->getVar()] = $widget->getHTML();
			}
			extract($html); //Extraction de l'html 
		}

		extract($this->_meta); //Extraction des données <meta>

		ob_start(); //Ouverture du tampon
		include TEMPLATEPATH.$this->_template.'.php'; //On insère le template
		$result = ob_get_contents(); //Récupérons le contenu du tampon
		ob_end_clean(); //Femerture + nettoyage tampon

		echo $result; //On affiche le résultat
	}

	/**
	 * Méthode displayAjax
	 * Permet l'affichage des widgets avec un appel en ajax
	**/
	public function displayAjax()
	{
		//On ajoute le script si besoin pour afficher les widgets en ajax
		$arrayWidgetAjax = null;
		if ($sess_ajax = Session::get(SESSAJAX)) {
			$arrayWidgetAjax = '<script type="text/javascript"> var widgetsAjax = ';
			foreach ($sess_ajax as $key => $value) {
				$nameWidgetsAjax[] = $key;
			}
			$arrayWidgetAjax .= json_encode($nameWidgetsAjax).';';
			$arrayWidgetAjax .= '
									function loopAjaxWidget(widgets) {
								        var widget_data = {
								        	token: "'.AC::get('token')->get().'",
								            widgetName: widgets[0],
								            allWidgets: widgets
								        };

								       $.ajax({
								            url: "'.AC::get('router')->url("data/ajaxengine").'",
								            type: "POST",
								            data: widget_data,
								            dataType: "json",
								            success: function(msg)
								            {
									                if(msg.validate)
									                {
														$(".widgetAjax_"+msg.item).before(msg.data).remove();
									                }
								            },
								            error: function(msg){}
								        });
										widgets.shift();
										if (widgets.length > 0) {
											loopAjaxWidget(widgets);
										}
								        return false;
									};
									$(document).ready(function(){loopAjaxWidget(widgetsAjax);});
							    </script>';
		}
		echo $arrayWidgetAjax;
	}
}
