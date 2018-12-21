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
      <div class="sidebar">
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
             <ul class="main-menu list-unstyled">
               <li>
                 <a href="#submenu1" data-toggle="collapse" aria-expanded="false" aria-controls="submenu1" class="collapsed">МЕЖКОМНАТНЫЕ ДВЕРИ</a>
                 <ul class="submenu collapse list-unstyled" id="submenu1">
                   <li class="active"><a href="/dorian/modern/">СОВРЕМЕННЫЕ<span class="line"></span></a></li>
                   <li><a href="#">БЕЛАЯ КЛАССИКА</a></li>
                   <li><a href="#">ДИЗАЙНЕРСКИЕ МАССИВ</a></li>
                   <li><a href="#">КЛАССИКА МАССИВ</a></li>
                   <li><a href="#">ГЛЯНЕЦ</a></li>
                   <li><a href="#">СКРЫТЫЕ ДВЕРИ</a></li>
                   <li><a href="#">ALLUMINIO</a></li>
                   <li><a href="#">УМНЫЕ РЕШЕНИЯ</a></li>
                   <li><a href="#">РУЧКИ</a></li>
                 </ul> 
               </li>
               <li><a href="#submenu2" data-toggle="collapse" aria-expanded="false" aria-controls="submenu2" class="collapsed">СДВИЖНЫЕ ПЕРЕГОРОДКИ</a></li>
               <li><a href="#submenu3" data-toggle="collapse" aria-expanded="false" aria-controls="submenu3" class="collapsed">ВХОДНЫЕ ДВЕРИ</a></li>
               <li><a href="#submenu4" data-toggle="collapse" aria-expanded="false" aria-controls="submenu4" class="collapsed">НАПОЛЬНЫЕ ПОКРЫТИЯ</a></li>
             </ul>
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
              <a href="#"><img src="<?php echo get_template_directory_uri(); ?>/assets/img/icon_fullscreen.png" alt=""></a>
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

          <?php
            // циклы вывода записей
            // если записи найдены
            if ( have_posts() ){
              while ( have_posts() ){
                the_post();

                echo '<h3><a href="'. get_permalink() .'">'. get_the_title() .'</a></h3>';

                echo get_the_excerpt();
              }
            }
            // елси записей не найдено
            else{
              echo ' <p>Записей нет...</p>';
            }
            ?>

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