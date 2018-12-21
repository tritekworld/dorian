<?php
/*
Template Name: Home Page
*/
?>
<!DOCTYPE html>
<html>
<head lang="ru">
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="icon" href="<?php echo get_template_directory_uri(); ?>/assets/img/favicon.ico">
  <title><?php the_title(); ?> | Modern</title>
  <!-- Import main styles -->
  <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.2.0/css/all.css" integrity="sha384-hWVjflwFxL6sNzntih27bfxkr27PmbbK/iSvJ+a4+0owXq79v+lsFkW54bOGbiDQ" crossorigin="anonymous">
    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
  <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
  <!--[if lt IE 9]>
<script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
<![endif]-->
  <?php wp_head(); ?>
</head>
<body>

  <div id="wrapper">

    <div class="container-fluid">
      <div class="sidebar" id="navMenu">
         <nav class="navbar">
           <div class="navbar-header">
              <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar-main">
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
              </button>
              <?php the_custom_logo(); ?>
            </div>
            <div class="collapse navbar-collapse" id="navbar-main">
             <?php 
			wp_nav_menu( array(
				'menu_class'      => 'main-menu list-unstyled', 
				'container'       => '',
				'depth'           => 2,
				'link_before'     => '<span>',
				'link_after'      => '</span>'
			) );
			?>
            </div>
          </nav>
          <footer class="hidden-xs">
			<ul class="list-unstyled list-inline footer-menu">
			  <li><a href="#">Где купить</a></li>
			  <li><a href="#">Новости</a></li>
			  <li><a href="#">О компании</a></li>
			  <li><a href="#">Архитекторам и дизайнерам</a></li>
			  <li><a href="#">Стать дилером</a></li>
			  <li><a href="#">Портфолио</a></li>
			</ul>
			<div class="bottom">
			  <a href="#zakaz" class="zakaz">Заказать замер</a>
			  <ul class="list-unstyled list-inline">
				<li><a href="#"><img src="<?php echo get_template_directory_uri(); ?>/assets/img/icon_fb.png" alt=""></a></li>
				<li><a href="#"><img src="<?php echo get_template_directory_uri(); ?>/assets/img/icon_inst.png" alt=""></a></li>
			  </ul>
			</div> <!-- //.bottom -->
		  </footer>
        </div> <!-- //.sidebar --> <!-- //.col -->
       
        <div id="main" role="main">
          <div class="contact-wrap">
            <ul class="list-unstyled">
              <li class="dropdown">
                <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                  Москва
                  <b class="caret"></b>
                </a>
                <!-- <ul class="dropdown-menu">
                  <li><a href="#">Тула</a></li>
                  <li><a href="#">Королев</a></li>
                </ul> -->
              </li>
            </ul>
            <p>
              Севастопольский пр-т 51к2 
              <a href="tel:+74956498642">+7 (495) 649-8642</a>
            </p>
            <p>
              ВОЛОКОЛАМСКОЕ ШОССЕ 13
              <a href="tel:+74956498692">+7 (495) 649-8692</a>
            </p>
            <a href="#"><i class="fas fa-plus"></i> Еще 4 салона...</a>
          </div> <!-- //.contact-wrap -->

          <div class="category-content">
            <div class="collection-item colore">
              <img src="<?php echo get_template_directory_uri(); ?>/assets/img/modern/modern_colore.jpg" alt="">
              <a href="/dorian/modern/colore/" class="overlay">
                <span>COLORE</span>
              </a> <!-- //.overlay -->
            </div> <!-- //.collection-item -->
            <div class="collection-item luca">
              <img src="<?php echo get_template_directory_uri(); ?>/assets/img/modern/modern_luca.jpg" alt="">
              <div class="overlay">
                <h1>
                  DORIAN -<br>
                  ФАБРИКА -<br>
                  УНИКАЛЬНЫХ-<br>
                  ДВЕРЕЙ
                </h1>
                <p>Лучшие натуральные материалы и сильный выраженный дизайн. Технологические инновации и гарантированное качество.<br>
                Это то, что мы стремимся вложить в каждую нашу дверь.</p>
                <dl>
                  <dt>Luca Stradivari</dt>
                  <dd>Head of “Stradivari Design”,.<br>
                  chief designer of DORIAN.</dd>
                </dl>
              </div> <!-- //.overlay -->
            </div> <!-- //.collection-item -->
            <div class="collection-item deco">
              <img src="<?php echo get_template_directory_uri(); ?>/assets/img/modern/modern_deco.jpg" alt="">
              <a href="#" class="overlay">
                <span>DECO</span>
              </a> <!-- //.overlay -->
            </div> <!-- //.collection-item -->
            <div class="collection-item level">
              <img src="<?php echo get_template_directory_uri(); ?>/assets/img/modern/modern_level.jpg" alt="">
              <a href="#" class="overlay">
                <span>LEVEL</span>
              </a> <!-- //.overlay -->
            </div> <!-- //.collection-item -->
            <div class="collection-item grace">
              <img src="<?php echo get_template_directory_uri(); ?>/assets/img/modern/modern_grace.jpg" alt="">
              <a href="#" class="overlay">
                <span>GRACE</span>
              </a> <!-- //.overlay -->
            </div> <!-- //.collection-item -->
          </div> <!-- //.category-content -->

        </div> <!-- //#main --> 
    </div> <!-- //.container-fluid -->
    
  </div> <!-- //#wrapper -->

  <footer class="visible-xs">
    <div class="bottom">
      <a href="#"><img src="<?php echo get_template_directory_uri(); ?>/assets/img/icon_fullscreen.png" alt=""></a>
      <a href="#zakaz" class="zakaz">Заказать замер</a>
      <ul class="list-unstyled list-inline">
        <li><a href="#"><img src="<?php echo get_template_directory_uri(); ?>/assets/img/icon_fb.png" alt=""></a></li>
        <li><a href="#"><img src="<?php echo get_template_directory_uri(); ?>/assets/img/icon_inst.png" alt=""></a></li>
      </ul>
    </div> <!-- //.bottom -->
  </footer>


  <?php wp_footer(); ?>

</body>
</html>