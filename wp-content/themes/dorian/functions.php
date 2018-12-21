<?php 

	function my_deregister_scripts(){
	  wp_deregister_script( 'wp-embed' );
	}
	add_action( 'wp_footer', 'my_deregister_scripts' );
	add_action( 'wp_enqueue_scripts', 'dorian_styles' );
	add_action( 'wp_enqueue_scripts', 'jquery_lib' );
	add_action( 'wp_enqueue_scripts', 'dorian_scripts' );

	function jquery_lib(){
	  wp_enqueue_script( 'jquery' );
	}

	function dorian_styles() {
	  wp_enqueue_style( 'main', get_stylesheet_uri() );
	  wp_enqueue_style( 'font-awesome', get_template_directory_uri() . '/assets/libs/font-awesome/css/font-awesome.css' );
	  wp_enqueue_style( 'bootstrap', get_template_directory_uri() . '/assets/css/bootstrap.min.css' );
	  //wp_enqueue_style( 'ekko-lightbox', get_template_directory_uri() . '/assets/css/ekko-lightbox.css' );
	  wp_enqueue_style( 'slick', get_template_directory_uri() . '/assets/css/slick.css' );
	  //wp_enqueue_style( 'slick-theme', get_template_directory_uri() . '/assets/css/slick-theme.css' );
	  //wp_enqueue_style( 'formstyler', get_template_directory_uri() . '/assets/css/jquery.formstyler.css' );
	  //wp_enqueue_style( 'formstyler-theme', get_template_directory_uri() . '/assets/css/jquery.formstyler.theme.css' );
	  //wp_enqueue_style( 'jquery-ui', get_template_directory_uri() . '/assets/css/jquery-ui.min.css' );
	  wp_enqueue_style( 'fullpage', get_template_directory_uri() . '/assets/css/fullpage.min.css' );
	  wp_enqueue_style( 'zoom', get_template_directory_uri() . '/assets/css/cloud-zoom.css' );
	  wp_enqueue_style( 'style', get_template_directory_uri() . '/assets/css/style.css' );
	}

	function dorian_scripts() {
	  //wp_enqueue_script( 'functions', get_template_directory_uri() . '/assets/js/jquery-migrate-1.2.1.min.js', array( 'jquery' ), null );
	  wp_enqueue_script( 'bootstrap', get_template_directory_uri() . '/assets/js/bootstrap.min.js', array( 'jquery' ), null );
	  //wp_enqueue_script( 'ekko-lightbox', get_template_directory_uri() . '/assets/js/ekko-lightbox.js', array( 'jquery' ), null );
	  wp_enqueue_script( 'slick', get_template_directory_uri() . '/assets/js/slick.js', array( 'jquery' ), null );
	  //wp_enqueue_script( 'formstyler', get_template_directory_uri() . '/assets/js/jquery.formstyler.min.js', array( 'jquery' ), null );
	  //wp_enqueue_script( 'jquery-ui', get_template_directory_uri() . '/assets/js/jquery-ui.js', array( 'jquery' ), null );
	  //wp_enqueue_script( 'jquery-ui-touch-punch', get_template_directory_uri() . '/assets/js/jquery.ui.touch-punch.min.js', array( 'jquery-ui' ), null );
	  //wp_enqueue_script( 'jquery-number', get_template_directory_uri() . '/assets/js/jquery.number.min.js', array( 'jquery' ), null );
	  wp_enqueue_script( 'fullpage', get_template_directory_uri() . '/assets/js/fullpage.min.js', array( 'jquery' ), null );
	  wp_enqueue_script( 'zoom', get_template_directory_uri() . '/assets/js/cloud-zoom.js', array( 'jquery' ), null );
	  wp_enqueue_script( 'custom', get_template_directory_uri() . '/assets/js/custom.js', array( 'jquery' ), null );
	  wp_enqueue_script( 'functions', get_template_directory_uri() . '/assets/js/functions.js', array( 'jquery' ), null );
	}

	add_theme_support( 'custom-logo' );
	add_theme_support( 'menus' );
	add_theme_support( 'post-thumbnails', array( 'post' ) );
	
	register_nav_menus(array(
		'main-menu'    => 'Левое меню'
	));

 ?>