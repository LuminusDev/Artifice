<?php
	
/**
 * Classe AjaxEngine
 * Permet de creer des widgets en asynchrone avec ajax
 *
 * Cette classe fait partie integrante du framework Artifice
 *
 * @author Kévin Barreau <kevin.barreau.info@gmail.com>
 * LR 28/05/2014
 **/
	class Ajaxengine extends TemplateEngine
	{
		public function __construct()
		{
			// not parent, so not Session::delete(SESSAJAX);
		}

		public function index()
		{
			$widget_exist = function ($name) {
				return Session::get(SESSAJAX) && array_key_exists($name, Session::get(SESSAJAX)) && file_exists(WIDGETPATH.$name);
			};

			$create_multi_widget = function ($name, $arrayWidget) use (&$create_multi_widget) {
				$tmpw = $this->createWidget($name, $name);
				foreach ($arrayWidget['content'] as $key => $value) {
					$tmpw->assign(array($key => $value));
				}
				foreach ($arrayWidget['widget'] as $k => $v) {
					$newWidget = $create_multi_widget($k,$v);
					$tmpw->assign(array($k => $newWidget));
				}
				return $tmpw;
			};

			$return = array();

			$name = isset($_POST['widgetName']) ? htmlentities($_POST['widgetName']) : null;
			$token = isset($_POST['token']) ? htmlentities($_POST['token']) : null;
			if (!empty($name) && $widget_exist($name) && AC::get('token')->check($token)) {

				$content_widget = Session::get(SESSAJAX, $name, 'content');
				$widget_widget = Session::get(SESSAJAX, $name, 'widget');
				$tmpw = $this->createWidget($name);

				// On récupère les paramètres
				foreach ($content_widget as $key => $value) {
					$tmpw->assign(array($key => $value));
				}

				foreach ($widget_widget as $k => $v) {
					$createWidget = $create_multi_widget($k,$v);
					$tmpw->assign(array($k => $createWidget));
				}

				$result = $tmpw->getContentForHTTP();

				$return['item'] = preg_replace('#/#','-',$name);
				$return['validate'] = true;
				$return['data'] = utf8_encode($result);
			} else {
				$return['validate'] = false;
			}

			if (isset($_POST['allWidgets'])) {
				$arrayAllWidgets = $_POST['allWidgets'];
				array_shift($arrayAllWidgets);
				if (!empty($arrayAllWidgets)) {
					$return['widgets'] = $arrayAllWidgets;
				} else {
					$return['widgets'] = false;
				}
			} else {
				$return['widgets'] = false;
			}

			Session::delete(SESSAJAX, $name);
			$sess_ajax = Session::get(SESSAJAX);
			if (empty($sess_ajax)) {
				Session::delete(SESSAJAX);
			}
			echo json_encode($return);
		}
	}
