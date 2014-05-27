<?php
	
/**
 * Classe AjaxEngine
 * Permet de creer des widgets en asynchrone avec ajax
 *
 * Cette classe fait partie integrante du framework Artifice
 *
 * @author Kévin Barreau <kevin.barreau.info@gmail.com>
 * LR 15/02/2014
 **/
	class Ajaxengine extends TemplateEngine
	{
		public function __construct()
		{
			// not parent, so not unset($_SESSION[SESSAJAX]);
		}

		public function index()
		{
			$widget_exist = function ($name) {
				return isset($_SESSION[SESSAJAX]) && array_key_exists($name, $_SESSION[SESSAJAX]) && file_exists(WIDGETPATH.$name);
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
			if (!empty($name) && $widget_exist($name) && checkToken($token,$_SESSION[SESSAJAXTOKEN])) {
				$content_widget = $_SESSION[SESSAJAX][$name]['content'];
				$widget_widget = $_SESSION[SESSAJAX][$name]['widget'];
				$tmpw = $this->createWidget($name);

				// On récupère les paramètres
				foreach ($content_widget as $key => $value) {
					$tmpW->assign(array($key => $value));
				}

				foreach ($widget_widget as $k => $v) {
					$createWidget = $create_multi_widget($k,$v);
					$tmpW->assign(array($k => $createWidget));
				}

				$result = $tmpw->getContentForHTTP();
				$result = templateEngine_translate($result);
				$result = templateEngine_minification($result);

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

			unset($_SESSION[SESSAJAX][$name]);
			if (empty($_SESSION[SESSAJAX])) {
				unset($_SESSION[SESSAJAXTOKEN]);
				unset($_SESSION[SESSAJAX]);
			}
			echo json_encode($return);
		}
	}
