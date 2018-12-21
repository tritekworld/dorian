<?php
/**
 * The template for displaying pages
 **/
?>

<?php get_header(); ?>

      <?php get_sidebar(); ?>
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

          <?php if ( have_posts() ) :  while ( have_posts() ) : the_post(); ?>
              <?php the_content(); ?>
            <?php endwhile; ?>
          <?php endif; ?>

        </div> <!-- //.mcategory-content -->
      </div> <!-- //#main -->

    </div> <!-- //.container-fluid -->
    
  </div> <!-- //#wrapper -->

<?php get_footer(); ?>