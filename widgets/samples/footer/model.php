
<?php

class Footer extends Widget
{
	public function __construct($variable = null)
	{
		// cache 500 seconds
		parent::__construct('samples/footer', 500, $variable, false);
	}

	public function run()
	{
		$this->data['dbtest'] = $this->loadModel('sample')->getToto();
	}
}
