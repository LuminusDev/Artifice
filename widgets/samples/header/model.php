
<?php

class Header extends Widget
{
	public function __construct($variable = null)
	{
		// display in ajax
		parent::__construct('samples/header', 0, $variable, true);
	}

	public function run()
	{
		// nothing
	}
}
