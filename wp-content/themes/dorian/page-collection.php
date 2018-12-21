<?php
/*
Template Name: Collection
*/
?>

<?php get_header(); ?>

      <?php get_sidebar(); ?>


        <div id="main" role="main">

          <div class="contact-wrap">
            <div class="contact_top_block">
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
				<span class="contact_wrap_more"><i class="fas fa-plus"></i> Еще 3 салона...</span>
				<p class="contact_wrap_hiden">
				  МИЧУРИНСКИЙ ПР-Т 10к1
				  <a href="tel:+74956496132">+7 (495) 649-6132</a>
				</p>
				<p class="contact_wrap_hiden">
				  РЕУТОВ, МКАД 2-й км
				  <a href="tel:+74956460143">+7 (495) 646-0143</a>
				</p>
				<p class="contact_wrap_hiden">
				  НОВАЯ МОСКВА, КИЕВСКОЕ ш.
				  <a href="tel:+74956629701">+7 (495) 662-9701</a>
				</p>
				<span class="contact_wrap_href">Перейти в раздел “Где купить”</span>
			</div>
          </div> <!-- //.contact-wrap -->

          <div class="collections-wrap">
            <ul class="list-unstyled">
            	<?php
			      		$collection_cur = get_page_by_title(get_the_title()); // массив текущей страницы
			      		$collection_parent = get_ancestors($collection_cur->ID, 'page'); // ID родительских элементов текущей страницы
			      		$collection_menu = get_children(array(
			      			'post_parent' => $collection_parent[0],
									'post_type' => 'page',
									'orderby' => 'menu_order'
			      		));
			      		foreach ($collection_menu as $collection_item) : ?>
		              <li <?php if ( $collection_item->post_title == get_the_title()) { echo 'class="active"'; } ?>><a href="<?php the_permalink($collection_item->ID); ?>"><?php echo get_the_post_thumbnail($collection_item->ID); ?><span><?php echo $collection_item->post_title; ?></span></a></li>
		            <?php endforeach; ?>
            </ul>
          </div> <!-- //.collections-wrap -->

          <div class="main-content">

            <div id="header">
              <span><?php the_title();?></span>
              <ul class="list-unstyled list-inline" id="collectionMenu">
                <li class="active" data-menuanchor="photogallery"><a href="#photogallery">ФОТОГАлерея</a></li>
                <li data-menuanchor="configurator"><a href="#configurator">КОНФИГУРАТОР:<br>цвета и модели</a></li>
                <?php if (!empty(get_field("collection_video"))) { ?>
				<li data-menuanchor="video"><a href="#video">ВИДЕО о<br>КОЛЛекции</a></li>
				<?php } ?>
				<?php if (!empty(get_field("collection_boxes"))) { ?>
				<li data-menuanchor="frames"><a href="#frames">ВАРИАНТЫ<br>КОРОБОВ</a></li>
				<?php } ?>
				<?php if (!empty(get_field("collection_advantages"))) { ?>
                <li data-menuanchor="advantages"><a href="#advantages">ПРеимущества<br>коллекции</a></li>
				<?php } ?>
              </ul>
            </div> <!-- //#header -->

            <div id="fullpage">
              
              <div class="section" id="section-photogallery">
                  
                  <?php 

                    $images = get_field('collection_gallery');
                    $size = 'full'; // (thumbnail, medium, large, full or custom size)

                    if( $images ): 
                  ?>
                      <?php foreach( $images as $image ): ?>
                        <div class="slide item">
                          <?php echo wp_get_attachment_image( $image['ID'], $size ); ?>
                        </div> <!-- //.item -->
                      <?php endforeach; ?>
                    <?php endif; ?>
                  
                  <div class="carousel-caption">
                    <p><?php the_field('collection_description'); ?></p>
                  </div>

              </div> <!-- //.section -->

              <div class="section" id="section-configurator"> 
                <div class="section-wrap">
                  
                  <?php 
  	                $collection_name = strtolower(get_the_title()); // название коллекции со страницы
                    $term_cur = get_term_by('slug', $collection_name, 'configurator_cat'); // текущая коллекция по слагу
                    $terms = get_terms( array(
                      'taxonomy'    => 'configurator_cat',
                      'pad_counts'  => 1
                    ) );
                    // оставим только термины с parent=0
                    $terms = wp_list_filter( $terms, array('parent'=>$term_cur->term_id) );
                    ?>
                  <ul class="tabs-year list-unstyled list-inline">
                  <?php 
                    foreach ($terms as $child) :
                      echo '<li><a href="#' . $child->slug . '" data-toggle="tab">' . $child->name . '</a></li>';
                   ?>
                    <?php endforeach; ?>
                   <!-- 
                    <li class="active"><a href="#2018" data-toggle="tab">Коллекция 2018</a></li>
                    <li class="hidden"><a href="#2016" data-toggle="tab">Коллекция 2016</a></li>
                   -->               
                  </ul>
                  <div class="tab-content content-year">
                    <?php 
                      foreach ($terms as $child) : // содержимое вкладки по году
					  $colorsordered = array();
                    ?>
                    <div id="<?php echo $child->slug; ?>" class="tab-pane fade">
                      <ul class="tab-ral list-unstyled list-inline">
                        <?php 
                          $color_cur = get_term_by('slug', $child->slug, 'configurator_cat'); // коллекция по слагу
                          $colors = get_term_children( $color_cur->term_id, 'configurator_cat' );
                          foreach ($colors as $color) :
                            $color_single = get_term_by( 'id', $color, 'configurator_cat' );
                            $image_id = get_term_meta( $color_single->term_id, '', 1 );
							$colorsordered[$image_id['wpcf-color_order'][0]] = $color;
                         ?>
						 
                          <?php endforeach; ?>
						  
						  <?php ksort($colorsordered); ?>
						  
						  <?php foreach ($colorsordered as $colorordered) { 
								$color_single = get_term_by( 'id', $colorordered, 'configurator_cat' );
								/*$color_single = get_terms( array(
								  'taxonomy' => 'configurator_cat',
								  'child_of' => $color,
								  'meta_key' => 'color_order',
								  'orderby' => 'meta_value_num', 
								  'order' => 'ASC'
								  )
								);*/
								$image_id = get_term_meta( $color_single->term_id, '', 1 );
								$image_url = $image_id['wpcf-collection_color'][0];								
								//echo 'порядок цвета: '.$image_id['wpcf-color_order'][0];
								echo '<li><a href="#' . $color_single->slug . '" data-toggle="tab" style="background: url('. $image_url .') no-repeat center center; background-size: cover;"><span>' . $color_single->name . '</span></a></li>';
								//echo '<li><a href="#' . $color_single->slug . '" data-toggle="tab">' . types_render_termmeta('collection_color', array('term_id' => $color_single->term_id)) . '<span>' . $color_single->name . '</span></a></li>';
						  }
						  ?>
						  
						  
                      </ul>
                      <div class="tab-content model-wrap">
  	                    <?php 
  		                    foreach ($colorsordered as $colorordered) : 
  		                    	 $color_single = get_term_by( 'id', $colorordered, 'configurator_cat' );
                      	?>
  	                      <div id="<?php echo $color_single->slug; ?>" class="tab-pane fade">
  	                        
  	                        <div class="tab-content models-content hidden-xs">
  	                        	<?php 
								$models = get_posts( array(
  	                          			'post_type' => 'configurator_model',
  	                          			'numberposts' => -1,
  	                          			'orderby' => 'title',
  	                          			'order' => 'ASC',
  	                          			'tax_query' => array(
  	                          				array(
  	                          					'taxonomy' => 'configurator_cat',
  	                          					'field' => 'slug',
  	                          					'terms' => $color_single->slug
  	                          				)
  	                          			)
  	                          		) );
								foreach ($models as $model_item) : ?>
  	                          <div class="tab-pane fade" id="<?php echo $model_item->post_name; ?>">
  	                            <div class="img-wrap"> 
                                  <a href="<?php echo types_render_field('model_img', array('id' => $model_item->ID, 'url' => true)); ?>" class="mag_image">
    	                              <?php echo types_render_field('model_img', array('id' => $model_item->ID)); ?>
                                  </a>
								  <div class="mag_image_display"></div>
								  <div class="bottom">
									  <div class="lupa">
										<span>Наведите курсор для увеличения</span>
									  </div> <!-- //.lupa -->
									  <div class="copyright">
										&copy; Разработано для DORIAN компанией “Stradivari Design SARL”. Копирование запрещено и преследуется по закону.
									  </div> <!-- //.copyright -->
									</div>
  	                            </div> <!-- //.img-wrap -->
  	                            <div class="info">
  	                              <div class="title">
  	                                <span><?php echo $model_item->post_title; ?></span> 
  	                                <?php echo types_render_field('model_subname', array('id' => $model_item->ID)); ?>
  	                              </div> <!-- //.title -->
  	                              <div class="price-wrap">
  	                                <div class="price">
  	                                  <i class="fas fa-circle"></i>
  	                                  <span><?php echo types_render_field('model_oldprice', array('id' => $model_item->ID)); ?></span>
  	                                  <?php echo types_render_field('model_price', array('id' => $model_item->ID)); ?> Р
  	                                </div> <!-- //.price -->
  	                                <div class="note">
  	                                  <?php echo types_render_field('model_dopinfo', array('id' => $model_item->ID)); ?>
  	                                  <span><?php echo types_render_field('model_sale', array('id' => $model_item->ID)); ?></span>
  	                                </div> <!-- //.note -->
  	                              </div> <!-- //.price-wrap -->
  	                            </div> <!-- //.info -->
  	                          </div> <!-- //.tab-pane -->
  	                          <?php endforeach; ?>
  	                        </div> <!-- //.tab-content -->
							
							<div class="tab-models hidden-xs">
  	                          <span>Выберите модель</span>
  	                          <ul class="list-models list-unstyled">
  	                          	<?php 
  	                          		foreach ($models as $model_item) :
  		                          	 	//$model_number = str_replace(get_the_title().'', '', $model_item->post_title);
                                    $span1 = types_render_field('span_1', array('id' => $model_item->ID));
                                    $span2 = types_render_field('span_2', array('id' => $model_item->ID));
  	                          	 ?>
  		                            <li><a href="#<?php echo $model_item->post_name; ?>" data-toggle="tab"><?php echo types_render_field('model_img', array('id' => $model_item->ID)); ?><span class="span1"><?php echo $span1; ?></span><span class="span2"><?php echo $span2; ?></span></a></li>
  	                            <?php endforeach; ?>
  	                          </ul>
  	                        </div> <!-- //.tab-models -->

                            <div class="visible-xs">
                              <div class="model-slider">
                                <?php foreach ($models as $model_item) : ?>
                                  <div class="model-item">
                                    <div class="wrap">
                                      <?php echo types_render_field('model_img', array('id' => $model_item->ID)); ?>
                                      <div class="info">
                                        <div class="title">
                                          <span><?php echo $model_item->post_title; ?></span> 
                                          <?php echo types_render_field('model_subname', array('id' => $model_item->ID)); ?>
                                        </div> <!-- //.title -->
                                        <div class="price-wrap">
                                          <div class="price">
                                            <i class="fas fa-circle"></i>
                                            <span><?php echo types_render_field('model_oldprice', array('id' => $model_item->ID)); ?></span>
                                            <?php echo types_render_field('model_price', array('id' => $model_item->ID)); ?> Р
                                          </div> <!-- //.price -->
                                          <div class="note">
                                            <?php echo types_render_field('model_dopinfo', array('id' => $model_item->ID)); ?>
                                            <span><?php echo types_render_field('model_sale', array('id' => $model_item->ID)); ?></span>
                                          </div> <!-- //.note -->
                                        </div> <!-- //.price-wrap -->
                                      </div> <!-- //.info -->
                                    </div> <!-- //.wrap -->
                                  </div> <!-- //.model-item -->
                                <?php endforeach; ?>
                              </div> <!-- //.model-slider -->
                            </div> <!-- //.visible-xs -->

  	                      </div> <!-- //.tab-pane -->
                        <?php endforeach; ?>
                        
                         
                      </div> <!-- //.tab-content -->
                    </div> <!-- //.tab-pane -->
                    <?php endforeach; ?>
                  </div> <!-- //.tab-content content-year -->

                </div> <!-- //.section-wrap -->
              </div> <!-- //.section -->

			  
			<?php if (!empty(get_field("collection_video"))) { ?>
				<div id="section-video" class="section">
					<div class="section-wrap">
					<h2 class="visible-xs">ВИДЕО О КОЛЛЕКЦИИ</h2>
					<div class="video-wrap">
						<p><?php echo get_field("collection_video"); ?></p>
					</div>
					</div>
				</div>
			<?php } ?>
			  
			<?php if (!empty(get_field("collection_boxes"))) { ?>
				<?php echo get_field("collection_boxes"); ?>
			<?php } ?>
			
			<?php if (!empty(get_field("collection_advantages"))) { ?>
				<?php echo get_field("collection_advantages"); ?>
			<?php } ?>
			  
			  
              

			  
            </div> <!-- //# -->
          </div> <!-- //.main-content -->
        </div> <!-- //#main -->

    </div> <!-- //.container-fluid -->
    
  </div> <!-- //#wrapper -->

<script>
(function ($) {
//добавляем в текущем пункте меню белую полоску слева
$('.current-page-ancestor').parent(".sub-menu").parent(".menu-item").find("a:first").addClass("active_main_menu");
	
//инициализация скролла по блокам	
$('#fullpage').fullpage({
	anchors: ['photogallery', 'configurator'<?php if (!empty(get_field("collection_video"))) { ?>, 'video'<?php } ?><?php if (!empty(get_field("collection_boxes"))) { ?>, 'frames'<?php } ?><?php if (!empty(get_field("collection_advantages"))) { ?>, 'advantages'<?php } ?>],
	menu: '#collectionMenu',
	slidesNavigation: true,
	recordHistory: false,
	fitToSection: false,
	onLeave: function(origin, destination, direction) {
		if(origin.index == 0 && direction =='down'){
		$('#header').css('background-color', '#122c4b');
		}
		if(origin.index != 0 && destination.index == 0 && direction =='up'){
		$('#header').css('background-color', 'rgba(0, 0, 0, 0.4)');
		}
  }
});
	
//зум при наведении на главное фото
$('.mag_image img').magnifier({
  magnify: 3, 
  region: {
    h: 150, 
    w: 150
  }, 
  display: $('.mag_image_display')
});
	
var collectionscount = $(".collections-wrap > .list-unstyled > li").length;
$( window ).resize(function() {
	if (collectionscount * 135 + 176 > $( window ).height()) {
		$(".collections-wrap > .list-unstyled > li").css({"margin-bottom":"5%", "height": 100/collectionscount - 1 + "%" });
	} else {
		$(".collections-wrap > .list-unstyled > li").css({"margin-bottom":"14px", "height":"120px"});
	}
});	
}(jQuery));
</script>
  
<?php get_footer(); ?>