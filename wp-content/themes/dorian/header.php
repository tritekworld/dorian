<!DOCTYPE html>
<html>
<head lang="ru">
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="user-scalable=0, width=device-width, initial-scale=1, maximum-scale=1">
  <link rel="icon" href="<?php echo get_template_directory_uri(); ?>/assets/img/favicon.ico">
  <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
  <title><?php bloginfo( 'name' ); ?> | <?php the_title(); ?></title>
  <!-- Import main styles -->
  <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.2.0/css/all.css" integrity="sha384-hWVjflwFxL6sNzntih27bfxkr27PmbbK/iSvJ+a4+0owXq79v+lsFkW54bOGbiDQ" crossorigin="anonymous">
    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
  <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
  <!--[if lt IE 9]>
<script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
<![endif]-->

  <?php wp_head(); ?>
<script type='text/javascript' src='<?php echo get_template_directory_uri(); ?>/assets/js/jquery.magnifier.js'></script>  
  
<?php if(stristr($_SERVER['HTTP_USER_AGENT'], "Mobile")){ // if mobile browser ?>
<link rel='stylesheet' href='<?php echo get_template_directory_uri(); ?>/assets/css/media_mobile.css' type='text/css' />
<?php } else { // desktop browser ?>
<link rel='stylesheet' href='<?php echo get_template_directory_uri(); ?>/assets/css/media_pc.css' type='text/css' />
<?php } ?>
  
  
</head>
<body>

  <div id="wrapper">

    <div class="container-fluid">