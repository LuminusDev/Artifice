<?php
  	session_start();

	require __DIR__.'/../inc/base.config.php';
  	require __DIR__.'/../engine/AC.class.php';

  	require __DIR__.'/../inc/container.config.php';

	require __DIR__.'/../engine/TemplateEngine.class.php';
	require __DIR__.'/../engine/ArtificeModel.class.php';

	AC::get('router')->run();
	
?>
