

<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
   <head>
      <meta http-equiv="content-type" content="text/html; charset=utf-8"/>

      <title><?php echo $title." | ".SITE_NAME; ?></title>
      
      <meta name="description" content="<?php echo $description; ?>" />

      <link rel="canonical" href="<?php echo BASE_URL.'/'.$page_url; ?>" />

      <meta property="og:title" content="<?php echo $title; ?>" />
      <meta property="og:url" content="<?php echo BASE_URL.'/'.$page_url; ?>" />
      <meta property="og:description" content="<?php echo $description; ?>" />
      <meta property="og:site_name" content="<?php echo SITE_NAME;?>" />

      <!-- Javascript header -->
      <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/2.0.0/jquery.min.js"></script>
      <script type="text/javascript" src="<?php echo BASE_URL."/static/js/templateEngine.js"; ?>"></script>
  </head>

  <body>
    <header><?php $this->displayWidget('samples/header'); ?></header>
    <div class="container">
        <?php echo $content; ?>
    </div>

    <!-- Javascript footer -->
    <?php $this->displayAjax(); ?>
  </body>
</html>