<?php

if ( !defined( 'ABSPATH' ) ) die();

if ( ! class_exists( 'VcVcSnippet' ) ) :

class VcVcSnippet {
  
  protected static $instance;
  protected static $name = 'vc-vc-snippet';
  public $defaults;
  public $vc_snippet_options;


  /**
   *  This function will setup VC Snippet component
   */
  private function __construct() {
    add_action( 'init', array( $this, 'init' ) );
    add_shortcode( self::$name, array( $this, 'shortcode' ) );

    /*
      Grid Item Support
      Since 1.2.9
    */
    add_shortcode( self::$name . '-gitem', array( $this, 'shortcodeGridItem' ) );
    add_filter( 'vc_grid_item_shortcodes', array( $this, 'mapGridItemShortcodes' ) );
    // add_filter( 'vc_gitem_template_attribute_vc_acf_field_picker', array( $this, 'vc_gitem_template_attribute_vc_acf_field_picker' ), 10, 2 );
    

    /*
      Since 1.4.17
      Add VC Grids support in VC Snippets
    */
    add_action( 'wp_ajax_vc_get_vc_grid_data', array( $this, 'ajax_get_grid_data' ), 5 );
    add_action( 'wp_ajax_nopriv_vc_get_vc_grid_data', array( $this, 'ajax_get_grid_data' ), 5 );
  }


  /**
   *  Create or retrieve instance
   */
  public static function instance() {
    return self::$instance ? self::$instance : self::$instance = new self();
  }


  /**
   *  Initializes component
   */
  public function init() {

    // Check if Visual Composer is installed
    if ( ! defined( 'WPB_VC_VERSION' ) ) {
      return;
    }

    $this->defaults = array(
      'id' => '',
    );

    /*
      Frontend editor sipport
      Since 1.2.6
    */
    $is_vc_frontend_editor_request = function_exists( 'vc_post_param' ) ? in_array( vc_post_param( 'action' ), array( 'vc_edit_form', 'vc_load_shortcode' ) ) || ( 'true' === vc_get_param( 'vc_editable' ) ) : false;
    if ( is_admin() || $is_vc_frontend_editor_request ) {
      $this->integrateWithVC();
    }

    /*
      Adds WPML Support
      Since 1.3.2
    */
    $this->setupWpmlSupport();
  }


  /**
   *  Adds compatibility with WPML
   *
   *  @since 1.3.2
   *
   *  @return n/a
   */
  private function setupWpmlSupport() {

    $wpml_vc_snippet_ids = get_field( 'wp_acf_vc_bridge_wpml_vc_snippet_ids', 'option' );
    $wpml_vc_snippet_ids = is_array( $wpml_vc_snippet_ids ) ? $wpml_vc_snippet_ids : array();

    if ( in_array( 'adjust_ids', $wpml_vc_snippet_ids ) ) {

      $should_return_original = in_array( 'return_original', $wpml_vc_snippet_ids );
      
      add_filter( 'vc_vc_snippet_get_html_id', function( $orig_id ) use ( $should_return_original ) {
        $id = apply_filters( 'wpml_object_id', $orig_id, 'vc_snippet', true );
        $id = ! $id && $should_return_original ? $orig_id : $id;
        return $id;
      } );

    }

  }


  /**
   *  Retrieves VC Snippets posts as options for VC
   *
   *  @since 1.2.9
   *
   *  @return array
   */
  private function getVcSnippetsOptions() {
    if ( ! isset( $this->vc_snippet_options ) ) {
      $this->vc_snippet_options = array();

      foreach ( WpAcfVcBridgeVcSnippets::getPosts() as $post ) {
        
        /*
          Since 1.3.3
          Allow having VC Snippets with same post title
        */
        $title = sprintf( '%s (%s)', $post->post_title, $post->ID );

        $this->vc_snippet_options[ $title ] = $post->ID;
      }
    }

    return $this->vc_snippet_options;
  }


  /**
   *  Processes shortcode
   *
   *  @param $atts array Shortcode attributes
   *  @param $content null|string Shortcode content
   *
   *  @return string
   */
  public function shortcode( $atts, $content = null ) {
    $atts = shortcode_atts(
      $this->defaults,
      $atts,
      self::$name // Allow 3rd parties to adjust. Since 1.3.0
    );
    $id = isset( $atts[ 'id' ] ) && $atts[ 'id' ] ? $atts[ 'id' ] : 0;
    return self::getHtml( $id );
  }

  
  /**
   *  Get post content by post id with shortcodes processed 
   *
   *  @param $id boolean|int
   *
   *  @return string
   */
  public static function getHtml( $id = false ) {
    /*
      Allow 3rd parties to adjust. WPML is the perfect example
      Since 1.3.0
    */
    $id = apply_filters( 'vc_vc_snippet_get_html_id', $id );

    if ( ( ! $id = (int)$id ) || ( ! $post = get_post( $id ) ) || ( WpAcfVcBridgeVcSnippets::POST_TYPE != $post->post_type ) ) {
      return '';
    }

    $css = self::getCss( $id );
    $content = do_shortcode( $post->post_content );

    /*
      Since 1.4.17
      VC Grids support in VC Sippets
    */
    $vc_post_settings = get_post_meta( intval( $id ), '_vc_post_settings', true );
    if ( isset( $vc_post_settings[ 'vc_grid_id' ][ 'shortcodes' ] ) && is_array( $vc_post_settings[ 'vc_grid_id' ][ 'shortcodes' ] ) ) {
      $shortcode_ids = array_keys( $vc_post_settings[ 'vc_grid_id' ][ 'shortcodes' ] );

      // Append VC Snippet Id for each generated VC Grid template codes exist in current VC Snippet
      foreach ( $shortcode_ids as $shortcode_id ) {
        // VC Grid settings are saved as json enconded data
        // e.g. "shortcode_id":"1516391447515-e40f766a-8a84-8"
        $pattern = sprintf( '&quot;shortcode_id&quot;:&quot;%s&quot;', $shortcode_id );
        $pattern = sprintf( '/%s(?!(.*?)vc_snippet_id(.*?)})/i', preg_quote( $pattern ) );
        $replacement = sprintf( '&quot;shortcode_id&quot;:&quot;%s&quot;,&quot;vc_snippet_id&quot;:&quot;%s&quot;', $shortcode_id, $id );
        $content = preg_replace( $pattern, $replacement, $content );
      }
    }

    return $css . $content;
  }

  
  /**
   *  Get post content by post id with shortcodes processed 
   *
   *  @since 1.3.8
   *
   *  @param $id boolean|int
   *
   *  @return string
   */
  public static function getCss( $id = false ) {
    $id = apply_filters( 'vc_vc_snippet_get_html_id', $id );

    if ( ( ! $id = (int)$id ) || ( ! $post = get_post( $id ) ) || ( WpAcfVcBridgeVcSnippets::POST_TYPE != $post->post_type ) ) {
      return '';
    }

    $shortcodes_custom_css = get_post_meta( $id, '_wpb_shortcodes_custom_css', true );
    $shortcodes_custom_css = $shortcodes_custom_css ? $shortcodes_custom_css : '';
    $custom_css = get_post_meta( $id, '_wpb_post_custom_css', true );
    $custom_css = $custom_css ? $custom_css : '';
    $css = $shortcodes_custom_css . $custom_css;

    return $css ? sprintf( '<style type="text/css">%s</style>', $css ) : '';
  }


  /**
   *  Creates params for VC component and registers it
   */
  public function integrateWithVC() {
    $params = array();

    $params []= array(
      'type' => 'dropdown',
      'holder' => 'div',
      'class' => 'vc-vc-snippet-field-preview',
      'heading' => __( 'VC Snippet', 'wp-acf-vc-bridge' ),
      'param_name' => 'id',
      'value' => $this->getVcSnippetsOptions(),
      'std' => '',
    );


    vc_map( array(
      'name' => __( 'VC Snippet', 'wp-acf-vc-bridge' ),
      'description' => __( 'Embed Visual Composer content snippet', 'wp-acf-vc-bridge' ),
      'base' => self::$name,
      'class' => 'vc-vc-snippet',
      'category' => 'Content',
      'admin_enqueue_js' => WP_ACF_VC_BRIDGE_PLUGIN_URL . '/assets/js/vc-vc-snippet.js?v' . WpAcfVcBridge::VERSION,
      'icon' => 'icon-wpb-atm',
      'js_view' => 'VcVcSnippetView',
      'custom_markup' => '<h4 class="wpb_element_title"> <i class="vc_general vc_element-icon icon-wpb-atm"></i> VC Snippet</h4>{{{ content }}}',
      'params' => $params
    ) );

  }


  /**
  *  ajax_get_grid_data()
  *
  *  Selectively adds hooks to VC Grid data requesting when it has been added to VC Snippet
  *
  *  @since 1.4.17
  *
  *  @return  n/a
  */
  public function ajax_get_grid_data() {
    if ( ! function_exists( 'vc_request_param' ) ) {
      return;
    }

    $vc_request_param_data = vc_request_param( 'data' );
    $vc_snippet_id = isset( $vc_request_param_data[ 'vc_snippet_id' ] ) ? $vc_request_param_data[ 'vc_snippet_id' ] : false;
    if ( $vc_snippet_id ) {
      
      if ( isset( $_REQUEST ) && is_array( $_REQUEST ) ) {
        $_REQUEST[ 'page_id' ] = $vc_snippet_id;
      }
      if ( isset( $_REQUEST[ 'data' ] ) && is_array( $_REQUEST[ 'data' ] ) ) {
        $_REQUEST[ 'data' ][ 'page_id' ] = $vc_snippet_id;
      }
      
      if ( isset( $_GET ) && is_array( $_GET ) ) {
        $_GET[ 'page_id' ] = $vc_snippet_id;
      }
      if ( isset( $_GET[ 'data' ] ) && is_array( $_GET[ 'data' ] ) ) {
        $_GET[ 'data' ][ 'page_id' ] = $vc_snippet_id;
      }

    }

  }


  /**
   *  Registers shortcode in VC Grid Item
   *
   *  @since 1.2.9
   *
   *  @param $value string
   *  @param $data array
   *
   *  @return string
   */
  public function mapGridItemShortcodes( $shortcodes ) {
    $params = array();

    $params []= array(
      'type' => 'dropdown',
      'holder' => 'div',
      'class' => 'vc-vc-snippet-field-preview',
      'heading' => __( 'VC Snippet', 'wp-acf-vc-bridge' ),
      'param_name' => 'id',
      'value' => $this->getVcSnippetsOptions(),
      'std' => '',
    );

    $shortcode = array(
      self::$name . '-gitem' => array(
        'name' => __( 'VC Snippet', 'wp-acf-vc-bridge' ),
        'description' => __( 'Embed Visual Composer content snippet', 'wp-acf-vc-bridge' ),
        'base' => self::$name . '-gitem',
        'class' => 'vc-vc-snippet',
        'category' => 'Content',
        'admin_enqueue_js' => WP_ACF_VC_BRIDGE_PLUGIN_URL . '/assets/js/vc-vc-snippet.js?v' . WpAcfVcBridge::VERSION,
        'icon' => 'icon-wpb-atm',
        'js_view' => 'VcVcSnippetView',
        'custom_markup' => '<h4 class="wpb_element_title"> <i class="vc_general vc_element-icon icon-wpb-atm"></i> VC Snippet</h4>{{{ content }}}',
        'params' => $params,
        'post_type' => Vc_Grid_Item_Editor::postType(),
      )
    );

    return $shortcodes + $shortcode;    
  }


  /**
   *  Renders shortcode for VC Grid Item
   *
   *  @since 1.2.9
   *
   *  @param $atts array
   *  @param $content string
   *
   *  @return string
   */
  public function shortcodeGridItem( $atts, $content = null ) {
    return $this->shortcode( $atts, $content );
  }


}

VcVcSnippet::instance();

endif;
?>