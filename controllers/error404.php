<?php

	class Error404 extends TemplateEngine
	{
		public function __construct()
		{
			// not parent, so not unset($_SESSION[SESSAJAX]);
		}

		public function index()
		{
			header("HTTP/1.0 404 Not Found", true, 404);

			$this->_template = "base.template";
			$this->_meta['description'] = "Page introuvable";
			$this->_meta['title'] = "Error 404";
			$this->_meta['page_url'] = AC::get('router')->get_url();

			$this->createWidget('samples/footer','content');

			$this->display();
		}
	}
