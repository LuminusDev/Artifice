
<?php

	class Sample extends TemplateEngine
	{
		public function index($arg1, $arg2)
		{
			$this->_template = "base.template";
			$this->_meta['description'] = "Bienvenue sur la page d'exemple du framework Artifice !";
			$this->_meta['title'] = "Première Sample Artifice";
			$this->_meta['page_url'] = AC::get('router')->get_url();

			$this->createWidget('samples/footer','content');

			echo htmlentities($arg1)." ".htmlentities($arg2);

			$this->display();
		}

		public function fonction1($my_arg)
		{
			$this->_template = "base.template";
			$this->_meta['description'] = "Bienvenue sur la page d'exemple, fonction1 du framework Artifice !";
			$this->_meta['title'] = "Première Sample Artifice, fonction 1";
			$this->_meta['page_url'] = AC::get('router')->get_url();

			$this->createWidget('samples/footer','content');

			echo htmlentities($my_arg);

			$this->display();
		}

		public function fonction2()
		{
			$this->_template = "base.template";
			$this->_meta['description'] = "Bienvenue sur la page d'exemple, fonction2 du framework Artifice !";
			$this->_meta['title'] = "Première Sample Artifice, fonction 2";
			$this->_meta['page_url'] = AC::get('router')->get_url();

			$this->createWidget('samples/footer','content');

			$this->display();
		}
	}
