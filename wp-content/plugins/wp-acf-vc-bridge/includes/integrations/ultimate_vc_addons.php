<?php

if( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'wpavb_ultimate_vc_addons' ) ) :

class wpavb_ultimate_vc_addons {


  public static $instance;
  public $captured_vc_post_content = '';

  
  function __construct() {
    add_filter( 'ultimate_front_scripts_post_content', array( $this, 'post_content' ), 10, 2 );
  }


  /**
   *  Create or retrieve instance
   */
  public static function instance() {
    return self::$instance ? self::$instance : self::$instance = new self();
  }
  
  
  /**
   *  capture_post_content()
   *
   *  This filter is appied to the $value after it is loaded from the db and before it is returned to the template
   *
   *  @param $value (mixed) the value which was loaded from the database
   *  @param $post_id (mixed) the $post_id from which the value was loaded
   *  @param $field (array) the field array holding all the field options
   *
   *  @return  $value (mixed) the modified value
   */
  function capture_post_content( $value, $post_id, $field ) {
    $this->captured_vc_post_content .= is_array( $value ) && isset( $value[ 'content' ] ) ? $value[ 'content' ] : '';
    return $value;
  }


  /**
  *  post_content()
  *
  *  Appends all rendered ACF VC Fields to post content, so Ultimate VC Addons plugin can detect own shortcodes
  *  and enqueue corresponding assets
  *
  *  @since 1.3.2
  *
  *  @param (string) $post_content
  *  @param (object) $post
  *  @return  (string)
  */
  function post_content( $post_content, $post ) {
    // Trigger ACF to process ACF fields assigned to this post or terms
    add_filter( 'acf/format_value/type=visual_composer', array( $this, 'capture_post_content' ), 5, 3 );
    $fields = get_fields();

    // Append captured VC content to the current post content for Ultimate VC Addons evaluation
    return $post_content . ' ' . $this->captured_vc_post_content;
  }

}

wpavb_ultimate_vc_addons::instance();

endif;

?>