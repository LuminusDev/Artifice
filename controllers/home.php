
<?php

	class Home extends TemplateEngine
	{
		public function index()
		{
			$this->_template = "base.template";
			$this->_meta['description'] = "Bienvenue sur le framework Artifice !";
			$this->_meta['title'] = "Première page Artifice";
			$this->_meta['page_url'] = AC::get('router')->get_url();

			$this->createWidget('samples/footer','content');

			$this->display();
		}
	}
