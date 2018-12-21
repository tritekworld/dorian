<div class="sidebar" id="navMenu">
  <nav class="navbar">
     <div class="navbar-header">
        <button type="button" class="navbar-toggle map-toggle collapsed" data-toggle="collapse" data-target="#contacts-map">
          <i class="fa fa-map-marker"></i>
        </button>
        <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar-main">
          <img src="<?php echo get_template_directory_uri(); ?>/assets/img/icon_mob_menu.png" alt="">
        </button>
        <?php the_custom_logo(); ?>
      </div>
      <div class="mob-header visible-xs">
        <div class="collapse navbar-collapse" id="navbar-main">
          <ul class="main-menu list-unstyled">
            <li><a href="#">МЕЖКОМНАТНЫЕ ДВЕРИ</a></li>
            <li><a href="#">СДВИЖНЫЕ ПЕРЕГОРОДКИ</a></li>
            <li><a href="#">ВХОДНЫЕ ДВЕРИ</a></li>
            <li><a href="#">НАПОЛЬНЫЕ ПОКРЫТИЯ</a></li>
          </ul>
        </div>
        <ul class="mob-submenu list-inline list-unstyled">
          <li><a href="/dorian/modern/colore/">СОВРЕМЕННЫЕ<span class="line"></span></a></li>
          <li><a href="/dorian/belaya-klassika/belvedere/">БЕЛАЯ КЛАССИКА</a></li>
          <li><a href="/dorian/dizajnerskie/stradivari/">ДИЗАЙНЕРСКИЕ МАССИВ</a></li>
          <li><a href="/dorian/klassika-massiv/barolo/">КЛАССИКА МАССИВ</a></li>
          <li><a href="/dorian/glyanets/ivory/">ГЛЯНЕЦ</a></li>
          <li><a href="#">СКРЫТЫЕ ДВЕРИ</a></li>
          <li><a href="#">ALLUMINIO</a></li>
          <li><a href="#">УМНЫЕ РЕШЕНИЯ</a></li>
          <li><a href="#">РУЧКИ</a></li>
        </ul>
        <ul class="mob-collections-menu list-unstyled list-inline">
          <?php
            $collection_cur = get_page_by_title(get_the_title()); // массив текущей страницы
            $collection_parent = get_ancestors($collection_cur->ID, 'page'); // ID родительских элементов текущей страницы
            $collection_menu = get_children(array(
              'post_parent' => $collection_parent[0],
              'post_type' => 'page',
              'orderby' => 'menu_order'
            ));
            foreach ($collection_menu as $collection_item) : ?>
              <li <?php if ( $collection_item->post_title == get_the_title()) { echo 'class="active"'; } ?>><a href="<?php the_permalink($collection_item->ID); ?>"><?php echo $collection_item->post_title; ?></a></li>
            <?php endforeach; ?>
          <!-- <li class="active"><a href="#">COLORE</a></li>
          <li><a href="#">LEVEL</a></li>
          <li><a href="#">DECO</a></li>
          <li><a href="#">GRACE</a></li> -->
        </ul>
      </div> <!-- //.visible-xs -->
      <div class="collapse navbar-collapse">
       <!--<ul class="main-menu list-unstyled">
         <li class="active">
           <a href="#submenu1" data-toggle="collapse" aria-expanded="false" aria-controls="submenu1" class="">МЕЖКОМНАТНЫЕ ДВЕРИ</a>
           <ul class="submenu collapse in list-unstyled" id="submenu1">
             <li><a href="/dorian/modern/colore/">СОВРЕМЕННЫЕ<span class="line"></span></a></li>
             <li><a href="/dorian/belaya-klassika/belvedere/">БЕЛАЯ КЛАССИКА</a></li>
             <li><a href="/dorian/dizajnerskie/stradivari/">ДИЗАЙНЕРСКИЕ МАССИВ</a></li>
             <li><a href="/dorian/klassika-massiv/barolo/">КЛАССИКА МАССИВ</a></li>
             <li><a href="/dorian/glyanets/ivory/">ГЛЯНЕЦ</a></li>
             <li><a href="#">СКРЫТЫЕ ДВЕРИ</a></li>
             <li><a href="#">ALLUMINIO</a></li>
             <li><a href="#">УМНЫЕ РЕШЕНИЯ</a></li>
             <li><a href="#">РУЧКИ</a></li>
           </ul> 
         </li>
         <li><a href="#submenu2" data-toggle="collapse" aria-expanded="false" aria-controls="submenu2" class="collapsed">СДВИЖНЫЕ ПЕРЕГОРОДКИ</a></li>
         <li><a href="#submenu3" data-toggle="collapse" aria-expanded="false" aria-controls="submenu3" class="collapsed">ВХОДНЫЕ ДВЕРИ</a></li>
         <li><a href="#submenu4" data-toggle="collapse" aria-expanded="false" aria-controls="submenu4" class="collapsed">НАПОЛЬНЫЕ ПОКРЫТИЯ</a></li>
       </ul>-->
	   
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
</div> <!-- //.sidebar -->