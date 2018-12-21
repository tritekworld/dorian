<?php

if ( !defined( 'ABSPATH' ) ) die();

if ( ! class_exists( 'VcAcfFieldPicker' ) ) :

class VcAcfFieldPicker {
  
  const NAME = 'vc-acf-field-picker';
  
  public static $instance;
  public $defaults;
  public $option_fields;


  /**
   *  This function will setup ACF field picker
   */
  private function __construct() {
    add_action( 'init', array( $this, 'init' ), 9999 );
    add_shortcode( self::NAME, array( $this, 'shortcode' ) );

    if ( apply_filters( 'vc_acf_field_picker_filters', true ) ) {
      include_once( WP_ACF_VC_BRIDGE_PLUGIN_PATH . 'includes/vc-acf-field-picker-filters.php' );
    }

    if ( is_admin() ) {
      add_action( 'admin_enqueue_scripts', array( $this, 'enqueueScripts' ) );
      add_action( 'wp_ajax_acf_field_picker_get_page_custom_options', array( $this, 'ajaxGetPageCustomOptions' ) );
      add_action( 'wp_ajax_acf_field_picker_page_custom_items', array( $this, 'ajaxGetPageCustomItems' ) );
      add_action( 'wp_ajax_acf_field_picker_page_custom_fields', array( $this, 'ajaxGetPageCustomFields' ) );
    }

    /*
      Grid Item Support
      Since 1.2.9
    */
    add_shortcode( self::NAME . '-gitem', array( $this, 'shortcodeGridItem' ) );
    add_filter( 'vc_grid_item_shortcodes', array( $this, 'mapGridItemShortcodes' ) );
    add_filter( 'vc_gitem_template_attribute_vc_acf_field_picker', array( $this, 'vc_gitem_template_attribute_vc_acf_field_picker' ), 10, 2 );
  }


  /**
   *  Create or retrieve instance
   */
  public static function instance() {
    return self::$instance ? self::$instance : self::$instance = new self();
  }


  /**
   *  Enqueues required JS to help in VC editor
   */
  public function enqueueScripts() {
    wp_register_script( self::NAME, WP_ACF_VC_BRIDGE_PLUGIN_URL . 'assets/js/vc-acf-field-picker.js', array( 'jquery' ), WpAcfVcBridge::VERSION );
    wp_enqueue_script( self::NAME );
    wp_localize_script(
      self::NAME,
      'vcAcfFieldPicker',
      array(
        'url' => admin_url( 'admin-ajax.php' ),
        'nonce' => wp_create_nonce( self::NAME ),
      )
    );
  }


  /**
   *  Processes ajax call for quering other pages
   */
  public function ajaxGetPageCustomItems() {
    check_ajax_referer( self::NAME, 'nonce' );

    $result = array();
    $items = array();

    $page_type = explode( ':', $_POST[ 'page_type' ] );
    $page = trim( $_POST[ 'page' ] );
    if ( 2 == count( $page_type ) ) {

      switch ( $page_type[ 0 ] ) {
        case 'post':
          if ( is_numeric( $page ) ) {
            $items = ( $items = get_post( $page ) ? (array)$items : array() );
          }
          else {
            $args = array(
              'post_type' => $page_type[ 1 ],
              'posts_per_page' => 10,
              'post_status' => array( 'publish', 'private', 'inherit', 'draft' ),
            );
            if ( $page ) {
              $args[ 's' ] = $page;
            }
            $query = new WP_Query( $args );
            $items = count( $query->posts ) ? $query->posts : array();
          }
          $items = array_map( function( $post ) {
            return array(
              'id' => $post->ID,
              'title' => $post->post_title,
            );
          }, $items );
        break;
        case 'term':
          $taxonomy = $page_type[ 1 ];
          if ( version_compare( get_bloginfo( 'version' ), '4.0', '>=' ) ) {
            $terms = get_terms( array(
              'taxonomy' => $taxonomy,
              'hide_empty' => false,
            ) );
          }
          else {
            $terms = get_terms( $taxonomy, array(
              'hide_empty' => false,
            ) );
          }
          if ( $page ) {
            $terms = array_filter( $terms, function( $term ) use ( $page ) {
              return is_numeric( $page ) ? $page == $term->term_id : ( FALSE !== strpos( $term->name .' ' . $term->slug, $page ) );
            } );
          }
          $items = array_map( function( $term ) use ( $taxonomy ) {
            return array(
              'id' => sprintf( '%s_%s', $taxonomy, $term->term_id ),
              'title' => $term->name,
            );
          }, $terms );
        break;
      }
    }

    $items = array_map( function( $item ) {
      return sprintf( '<div class="vc-acf-field-picker-page-selector-list-item" data-id="%1$s"><span>%2$s</span> <small>(%1$s)</small></div>', $item[ 'id' ], $item[ 'title' ] ? $item[ 'title' ] : __( '(No title)', 'wp-acf-vc-bridge' ) );
    }, $items );

    $result[ 'items' ] = implode( '', array_values( $items ) );

    echo json_encode( $result );
    die();
  }


  /**
   *  Processes ajax call for quering fields on a selected page
   */
  public function ajaxGetPageCustomFields() {
    check_ajax_referer( self::NAME, 'nonce' );

    $result = array();
    $items = array();

    $field_objects = get_field_objects( $_POST[ 'field_id' ] );
    $field_objects = $field_objects && is_array( $field_objects ) ? $field_objects : array();
    $items = array_map( function( $item ) {
      return sprintf( '<option class="%1$s" value="%1$s" rel="post">%2$s (%1$s) [%3$s]</option>', $item[ 'name' ], $item[ 'label' ], $item[ 'type' ] );
    }, $field_objects );

    $result[ 'fields' ] = implode( '', $items );

    echo json_encode( $result );
    die();
  }


  /**
   *  Processes ajax call for quering page type options
   */
  public function ajaxGetPageCustomOptions() {
    check_ajax_referer( self::NAME, 'nonce' );

    $result = array();
    $options = array();

    $items = get_post_types( array( 'public' => true, 'show_ui' => true ), 'objects' );
    $options []= '<optgroup label="Post Types">';
    foreach ( $items as $item ) {
      $options []= sprintf( '<option class="%1$s" value="%1$s" rel="post">%2$s (%1$s)</option>', $item->name, $item->label );
    }
    $options []= '</optgroup>';

    $items = get_taxonomies( array( 'public' => true, 'show_ui' => true ), 'objects' );
    $options []= '<optgroup label="Taxonomy Terms">';
    foreach ( $items as $item ) {
      $options []= sprintf( '<option class="%1$s" value="%1$s" rel="term">%2$s (%1$s)</option>', $item->name, $item->label );
    }
    $options []= '</optgroup>';

    $result[ 'options' ] = implode( '', $options );

    echo json_encode( $result );
    die();
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
      'page_field' => '',
      'option_field' => '',
      'field_context' => 'page',
      'page_type' => '',
      'page_custom' => '',
      'page_custom_field_id' => '', // Field id for Custom Page
      'page_id' => '', // Helper field for Custom Page
      'fields_group' => '',
      'data_context' => '',
      'force_data_context' => false,
      'sub_field' => '',
      'show_label' => '',
      'hide_empty' => '',
      'extra_classes' => '',
      'wrap_field' => 'yes', // Since 1.4.3
      'wrapper_tag' => 'div', // Since 1.4.3
    );


    /*
      Frontend editor sipport
      Since 1.2.6
    */
    $is_vc_frontend_editor_request = function_exists( 'vc_post_param' ) ? in_array( vc_post_param( 'action' ), array( 'vc_edit_form', 'vc_load_shortcode' ) ) || ( 'true' === vc_get_param( 'vc_editable' ) ) : false;
    if ( $is_vc_frontend_editor_request || WpAcfVcBridge::instance()->willInitializeBackendEditor() || WpAcfVcBridge::instance()->isEditingGridItem() ) {
      $this->option_fields = array();

      if ( $option_fields = get_field_objects( 'option' ) ) {
        foreach ( $option_fields as $field ) {
          $this->option_fields[ $field[ 'label' ] ] = $field[ 'name' ];
        }
      }

      $this->integrateWithVC();
    }


    /*
      Adds WPML Support
      Since 1.4.1
    */
    $this->setupWpmlSupport();    
  }


  /**
   *  Adds compatibility with WPML
   *
   *  @since 1.4.1
   *
   *  @return n/a
   */
  private function setupWpmlSupport() {

    $vc_acf_field_picker_wpml_post_ids = get_field( 'vc_acf_field_picker_wpml_post_ids', 'option' );
    $vc_acf_field_picker_wpml_post_ids = is_array( $vc_acf_field_picker_wpml_post_ids ) ? $vc_acf_field_picker_wpml_post_ids : array();

    if ( in_array( 'adjust_ids', $vc_acf_field_picker_wpml_post_ids ) ) {


      $should_return_original = in_array( 'return_original', $vc_acf_field_picker_wpml_post_ids );
      
      add_filter( 'vc_acf_field_picker_post_id', function( $post_id, $atts ) use ( $should_return_original ) {

        // Adjust only for "Another page" option
        $field_context = isset( $atts[ 'field_context' ] ) ? $atts[ 'field_context' ] : false;
        if ( ! $field_context || ! in_array( $field_context, array( 'page', 'page_custom' ) ) ) {
          return $post_id;
        }

        // If post type
        if ( is_numeric( $post_id ) ) {
          $post_type = get_post_type( $post_id );
          if ( ! $post_type ) {
            return $post_id;
          }

          $translation_id = apply_filters( 'wpml_object_id', $post_id, $post_type, true );
          $translation_id = ! $translation_id && $should_return_original ? $post_id : $translation_id;
        }
        // If taxonomy term
        else {
          $parts = explode( '_', $post_id );
          if ( count( $parts ) < 1 ) {
            return $post_id;
          }

          $term_id = array_pop( $parts );
          $taxonomy = implode( '_', $parts );
          if ( ! $term_id || ! $taxonomy ) {
            return $post_id;
          }
        
          $translation_id = apply_filters( 'wpml_object_id', $term_id, $taxonomy, true );
          $translation_id = ! $translation_id && $should_return_original ? $post_id : $translation_id;
          $translation_id = sprintf( '%s_%s', $taxonomy, $translation_id );
        }

        
        return $translation_id;

      }, 10, 2 );

    }

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
    $sanitized_atts = shortcode_atts( $this->defaults, $atts );

    $field_context = isset( $sanitized_atts[ 'field_context' ] ) && $sanitized_atts[ 'field_context' ] ? $sanitized_atts[ 'field_context' ] : '';
    $post_id = isset( $sanitized_atts[ 'data_context' ] ) && $sanitized_atts[ 'data_context' ] ? $sanitized_atts[ 'data_context' ] : '';
    if ( ! $field_context ) {
      return '';
    }

    switch ( $field_context ) {
      case 'page':
        $field_name = isset( $sanitized_atts[ 'page_field' ] ) && $sanitized_atts[ 'page_field' ] ? $sanitized_atts[ 'page_field' ] : '';
      break;
      case 'page_custom':
        $field_name = isset( $sanitized_atts[ 'page_custom_field_id' ] ) && $sanitized_atts[ 'page_custom_field_id' ] ? $sanitized_atts[ 'page_custom_field_id' ] : '';
      break;
      case 'option':
        $field_name = isset( $sanitized_atts[ 'option_field' ] ) && $sanitized_atts[ 'option_field' ] ? $sanitized_atts[ 'option_field' ] : '';
      break;
      case 'custom':
        $fields_group_id = $sanitized_atts[ 'fields_group' ];
        $field_name = $atts[ 'group_field_' . $fields_group_id ];
        $post_id = isset( $sanitized_atts[ 'force_data_context' ] ) && $sanitized_atts[ 'force_data_context' ] ? $sanitized_atts[ 'force_data_context' ] : false;
      break;
    }

    /*
      Since 1.4.1
      Allow to adjust post_id
    */
    $post_id = apply_filters( 'vc_acf_field_picker_post_id', $post_id, $sanitized_atts );

    if ( ! $post_id ) {
      $queried_object = get_queried_object();

      /*
        Fix for VC Grids custom query
        Issue description: VC Grid uses query_posts function. 
        When passed category_name, cat or similar filtering params, 
        it sets first category/term in filter as globally queried object,
        which breaks the logic here, because plugin tries looking for fields in term,
        instead of a current post in a VC Grid loop
        Since 1.3.1
      */
      if ( wp_doing_ajax() && isset( $_POST[ 'vc_action' ] ) && ( 'vc_get_vc_grid_data' === $_POST[ 'vc_action' ] ) ) {
        $queried_object = null;
      }

      // ACF can't get fields properly from term pages if page_id is not set
      if ( isset( $queried_object->term_id ) ) {
        $post_id = $queried_object->taxonomy . '_' . $queried_object->term_id;
      }
      // If it's author
      elseif ( isset( $queried_object->user_registered ) && $queried_object->user_registered ) {
        $post_id = "user_{$queried_object->ID}"; 
      }
      // If it's post or page
      elseif ( isset( $queried_object->ID ) && $queried_object->ID ) {
        $post_id = $queried_object->ID; 
      }
      // Leave it to ACF to decide
      else {
        // $post_id = false; 
        $post_id = $post_id ? $post_id : false; // Edited
      }
    }

    // Checked required
    if ( ! $field_name ) {
      return '';
    }

    $field_object = get_field_object( $field_name, $post_id );
    if ( ! $field_object || ! is_array( $field_object ) ) {
      return '';
    }

    /*
      Sub-field
      Since 1.2.1
    */
    $sub_field = intval( $sanitized_atts[ 'sub_field' ] );
    if ( $sub_field ) {
      $field_object = $this->getSubFieldObject( $sub_field - 1, $field_object, $post_id );

      if ( ! $field_object ) {
        return '';
      }
    }


    $result = $field_object[ 'value' ];
    $result = apply_filters( sprintf( 'vc_acf_field_picker_render_field_type_%s', $field_object[ 'type' ] ), $result, $field_object, $post_id );
    $result = apply_filters( sprintf( 'vc_acf_field_picker_render_field_name_%s', $field_object[ 'name' ] ), $result, $field_object, $post_id );
    $result = apply_filters( sprintf( 'vc_acf_field_picker_render_field_key_%s', $field_object[ 'key' ] ), $result, $field_object, $post_id );

    // Can output just strings and numbers
    $result = ( is_string( $result ) || is_numeric( $result ) ) ? $result : '';

    /*
      Hide empty
      Since 1.2.4
    */
    if ( ( 'yes' === $sanitized_atts[ 'hide_empty' ] ) && empty( $result ) ) {
      return '';
    }

    /*
      Field prefix/suffix
      Since 1.5.0
    */
    $prepend = is_array( $field_object ) && isset( $field_object[ 'prepend' ] ) ? $field_object[ 'prepend' ] : '';
    $prepend = $prepend ? sprintf( '<span class="vc-acf-field-picker-field-prepend">%s</span>', $prepend ) : '';
    $result = $prepend . $result;
    $append = is_array( $field_object ) && isset( $field_object[ 'append' ] ) ? $field_object[ 'append' ] : '';
    $append = $append ? sprintf( '<span class="vc-acf-field-picker-field-append">%s</span>', $append ) : '';
    $result = $result . $append;

    /*
      Field label
      Since 1.2.4
    */
    if ( 'yes' === $sanitized_atts[ 'show_label' ] ) {
      $label = is_array( $field_object ) && isset( $field_object[ 'label' ] ) ? $field_object[ 'label' ] : '';
      $label = $label ? sprintf( '<span class="vc-acf-field-picker-field-label">%s</span>', $label ) : '';
      $result = $label . $result;
    }

    /*
      Optionally wrap fields
      Since 1.4.3
    */
    if ( 'yes' === $sanitized_atts[ 'wrap_field' ] ) {

      /*
        Wrapper classes and extra classes
        Since 1.2.4
      */
      $result = sprintf(
        '<%1$s class="vc-acf-field-picker-field vc-acf-field-picker-field-type-%2$s %3$s">%4$s</%1$s>',
        $sanitized_atts[ 'wrapper_tag' ],
        $field_object[ 'type' ],
        $sanitized_atts[ 'extra_classes' ],
        $result
      );

    }

    // Since 1.2.4
    if ( ! wp_style_is( 'vc-acf-field-picker-frontend' ) ) {
      wp_enqueue_style( 'vc-acf-field-picker-frontend', WP_ACF_VC_BRIDGE_PLUGIN_URL . '/assets/css/vc-acf-field-picker-frontend.css', array(), WpAcfVcBridge::VERSION );
    }

    return $result;
  }


  /**
   *  Retrieves sub-field object
   *
   *  @since 1.2.1
   *
   *  @param $sub_field int
   *  @param $field_object object ACF field object
   *  @param $post_id int|string Post or Term string according to ACF requirements
   *
   *  @return object
   */

  private function getSubFieldObject( $sub_field, $field_object, $post_id ) {
    // Allow 3rd parties to hook
    $sub_field_object = apply_filters( 'vc_acf_field_picker_get_sub_field_object', null, $sub_field, $field_object, $post_id );

    // Bail early if custom sub field object has been provided
    if ( null !== $sub_field_object ) {
      return $sub_field_object;
    }


    /*
      Since 1.4.0
      Add Group field type support
    */
    if ( 'group' == $field_object[ 'type' ] ) {

      // Check if sub-field exists
      if ( ! isset( $field_object[ 'sub_fields' ][ $sub_field ] ) ) {
        return null;
      }
      $sub_field_object = $field_object[ 'sub_fields' ][ $sub_field ];

      // Check if sub-field value exists
      $sub_field_name = isset( $sub_field_object[ 'name' ] ) ? $sub_field_object[ 'name' ] : '';
      if ( ! isset( $field_object[ 'value' ][ $sub_field_name ] ) ) {
        return null;  
      }

      $sub_field_object[ 'value' ] = $field_object[ 'value' ][ $sub_field_name ];

    }
    // Repeater and Flexible Content
    else {

      // Check if sub-field exists
      if ( ! isset( $field_object[ 'value' ][ $sub_field ] ) ) {
        return null;
      }

      // Just use that single sub-field and eliminate all other
      $sub_field_object = $field_object;
      $sub_field_object[ 'value' ] = array( $sub_field_object[ 'value' ][ $sub_field ] );
    
    }

    return $sub_field_object;
  }


  /**
   *  Efficiently retrieves all ACF Field Groups and their Fields to be used in VC
   *
   *  @since 1.2.9
   *
   *  @return string
   */
  private function getAcfFieldsOptions() {
    if ( ! isset( $this->acfFieldsOptions ) ) {

      // Init
      $field_groups = function_exists( 'acf_get_field_groups' ) ? acf_get_field_groups() : apply_filters( 'acf/get_field_groups', array() );
      $group_options = array();
      $custom_fields = array();
      $ignore_field_types = array( 'message', 'tab' );
      if ( $field_groups ) {
        foreach ( $field_groups as $field_group ) {
          $id = isset( $field_group['id'] ) ? 'id' : ( isset( $field_group[ 'ID' ] ) ? 'ID' : 'id' );
          $field_group_id = $field_group[ $id ];

          // Local fields can't be retrieved
          if ( ! $field_group_id ) {
            continue;
          }

          $group_options[ $field_group[ 'title' ] ] = $field_group_id;
          
          $group_fields = function_exists( 'acf_get_fields' ) ? acf_get_fields( $field_group_id ) : apply_filters( 'acf/field_group/get_fields', array(), $field_group_id );
          $group_field_options = array();
          $group_field_options[ '-- Select field --' ] = '';
          if ( $group_fields ) {
            foreach ( $group_fields as $field ) {
              if ( in_array( $field[ 'type' ], $ignore_field_types ) ) {
                continue;
              }
              elseif ( 'clone' == $field[ 'type' ] ) {
                foreach ( $field[ 'sub_fields' ] as $sub_field ) {
                  $group_field_options[ $sub_field[ 'label' ] ] = $sub_field[ 'name' ];
                }
              }
              else {
                $group_field_options[ $field[ 'label' ] ] = $field[ 'name' ];
              }
            }
          }
          $field_param_name = 'group_field_' . $field_group_id;
          $custom_fields[] = array(
            'type' => 'dropdown',
            'heading' => __( 'Field name', 'wp-acf-vc-bridge' ),
            'class' => 'vc-acf-field-picker-field-preview',
            'param_name' => $field_param_name,
            'value' => $group_field_options,
            'dependency' => array(
              'element' => 'fields_group',
              'value' => array( (string)$field_group_id ),
            ),
          );
        }
      } 

      $this->acfFieldsOptions = array(
        'group_options' => $group_options,
        'custom_fields' => $custom_fields,
      );

    }

    return $this->acfFieldsOptions;
  }


  /**
   *  Creates params for VC component and registers it
   */
  public function integrateWithVC() {
    $params = array();

    // Init
    $field_groups = function_exists( 'acf_get_field_groups' ) ? acf_get_field_groups() : apply_filters( 'acf/get_field_groups', array() );
    $group_options = array();
    $custom_fields = array();
    $ignore_field_types = array( 'message', 'tab' );
    if ( $field_groups ) {
      foreach ( $field_groups as $field_group ) {
        $id = isset( $field_group['id'] ) ? 'id' : ( isset( $field_group[ 'ID' ] ) ? 'ID' : 'id' );
        $field_group_id = $field_group[ $id ];

        // Local fields can't be retrieved
        if ( ! $field_group_id ) {
          continue;
        }

        $group_options[ $field_group[ 'title' ] ] = $field_group_id;
        
        $group_fields = function_exists( 'acf_get_fields' ) ? acf_get_fields( $field_group_id ) : apply_filters( 'acf/field_group/get_fields', array(), $field_group_id );
        $group_field_options = array();
        $group_field_options[ '-- Select field --' ] = '';
        if ( $group_fields ) {
          foreach ( $group_fields as $field ) {
            if ( in_array( $field[ 'type' ], $ignore_field_types ) ) {
              continue;
            }
            elseif ( 'clone' == $field[ 'type' ] ) {
              foreach ( $field[ 'sub_fields' ] as $sub_field ) {
                $group_field_options[ $sub_field[ 'label' ] ] = $sub_field[ 'name' ];
              }
            }
            else {
              $group_field_options[ $field[ 'label' ] ] = $field[ 'name' ];
            }
          }
        }
        $field_param_name = 'group_field_' . $field_group_id;
        $custom_fields[] = array(
          'type' => 'dropdown',
          'heading' => __( 'Field name', 'wp-acf-vc-bridge' ),
          'class' => 'vc-acf-field-picker-field-preview',
          'param_name' => $field_param_name,
          'value' => $group_field_options,
          'dependency' => array(
            'element' => 'fields_group',
            'value' => array( (string)$field_group_id ),
          ),
        );
        $this->defaults[ $field_param_name ] = '';
      }
    } 

    $params []= array(
      'type' => 'dropdown',
      'holder' => 'div',
      'class' => 'vc-acf-field-picker-field-preview',
      'heading' => __( 'Field context', 'wp-acf-vc-bridge' ),
      'param_name' => 'field_context',
      'value' => array(
        'This Page' => 'page',
        'Another Page' => 'page_custom',
        'Options' => 'option',
        'Custom' => 'custom',
      ),
      'std' => 'page',
    );
    $params []= array(
      'type' => 'dropdown',
      'holder' => 'div',
      'class' => 'vc-acf-field-picker-field-preview',
      'heading' => __( 'This Page Field', 'wp-acf-vc-bridge' ),
      'param_name' => 'page_field',
      'value' => array(),
      'std' => '',
      'dependency' => array(
        'element' => 'field_context',
        'value' => array( 'page' ),
      )
    );
    $params []= array(
      'type' => 'dropdown',
      'holder' => 'div',
      'class' => 'vc-acf-field-picker-field-preview',
      'heading' => __( 'Option Field', 'wp-acf-vc-bridge' ),
      'param_name' => 'option_field',
      'value' => $this->option_fields,
      'std' => '',
      'dependency' => array(
        'element' => 'field_context',
        'value' => array( 'option' ),
      )
    );
    $params []= array(
      'type' => 'dropdown',
      'holder' => 'div',
      'class' => 'vc-acf-field-picker-field-hidden',
      'heading' => __( 'Page Type', 'wp-acf-vc-bridge' ),
      'param_name' => 'page_type',
      'value' => array(),
      'std' => '',
      'dependency' => array(
        'element' => 'field_context',
        'value' => array( 'page_custom' ),
      )
    );

    // Custom Page
    $params []= array(
      'type' => 'textfield',
      'holder' => 'div',
      'class' => 'vc-acf-field-picker-field-preview',
      'heading' => __( 'Page', 'wp-acf-vc-bridge' ),
      'param_name' => 'page_custom',
      'value' => '',
      'dependency' => array(
        'element' => 'field_context',
        'value' => array( 'page_custom' ),
      )
    );
    $params []= array(
      'type' => 'dropdown',
      'holder' => 'div',
      'class' => 'vc-acf-field-picker-field-preview',
      'heading' => __( 'Field', 'wp-acf-vc-bridge' ),
      'param_name' => 'page_custom_field_id',
      'value' => array(),
      'std' => '',
      'dependency' => array(
        'element' => 'field_context',
        'value' => array( 'page_custom' ),
      )
    );
    $params []= array(
      'type' => 'textfield',
      'holder' => 'div',
      'class' => 'vc-acf-field-picker-field-hidden',
      'param_name' => 'page_id',
      'value' => '',
    );

    // Custom
    $acfFieldsOptions = $this->getAcfFieldsOptions();

    $params []= array(
      'type' => 'dropdown',
      'holder' => 'div',
      'class' => 'vc-acf-field-picker-field-hidden',
      'heading' => __( 'Fields Group', 'wp-acf-vc-bridge' ),
      'param_name' => 'fields_group',
      'value' => $acfFieldsOptions[ 'group_options' ],
      'std' => '',
      'dependency' => array(
        'element' => 'field_context',
        'value' => array( 'custom' ),
      )
    );
    $params = array_merge( $params, $acfFieldsOptions[ 'custom_fields' ] );

    // Context
    $params []= array(
      'type' => 'textfield',
      'holder' => 'div',
      'class' => 'vc-acf-field-picker-field-hidden',
      'param_name' => 'data_context',
      'value' => '',
    );


    /*
      Sub-field
      Since 1.2.1
    */
    $params []= array(
      'type' => 'textfield',
      'holder' => 'div',
      'class' => 'vc-acf-field-picker-field-hidden',
      'heading' => __( 'Sub-Field', 'wp-acf-vc-bridge' ),
      'param_name' => 'sub_field',
      'value' => '',
      'description' => __( 'Enter a number to extract a particular sub-item from Repeater or Flexible Content field', 'wp-acf-vc-bridge' ),
    );

    $params = array_merge( $params, $this->getExtraParams() );

    vc_map( array(
      'name' => __( 'ACF Field Picker', 'wp-acf-vc-bridge' ),
      'description' => __( 'Embed Advanced Custom Fields field from this or options page', 'wp-acf-vc-bridge' ),
      'base' => self::NAME,
      'admin_enqueue_css' => WP_ACF_VC_BRIDGE_PLUGIN_URL . '/assets/css/vc-acf-field-picker.css',
      'front_enqueue_css' => WP_ACF_VC_BRIDGE_PLUGIN_URL . '/assets/css/vc-acf-field-picker.css',
      'class' => 'vc-acf-field-picker',
      'icon' => 'vc_icon-acf',
      'category' => 'Content',
      'js_view' => 'VcAcfFieldPickerView',
      'custom_markup' => '<h4 class="wpb_element_title"> <i class="vc_general vc_element-icon vc_icon-acf"></i> ACF Field Picker</h4>{{{ content }}}',
      'params' => $params
    ) );

  }


  /**
   *  Returns extra params for ACF Picker field
   *
   *  @since 1.4.9
   *
   *  @param N/A
   *
   *  @return array
   */
  public function getExtraParams() {
    $params = array();



    /*
      Field label
      Since 1.2.4
    */
    $params []= array(
      'type' => 'checkbox',
      'heading' => __( 'Show label', 'wp-acf-vc-bridge' ),
      'param_name' => 'show_label',
      'value' => array(
        __( 'Yes', 'wp-acf-vc-bridge' ) => 'yes' 
      ),
      'description' => __( "If checked, the field label (configured in field settings) will be shown before the field value", 'wp-acf-vc-bridge' ),
    );

    /*
      Hide empty
      Since 1.2.4
    */
    $params []= array(
      'type' => 'checkbox',
      'heading' => __( 'Hide empty', 'wp-acf-vc-bridge' ),
      'param_name' => 'hide_empty',
      'value' => array(
        __( 'Yes', 'wp-acf-vc-bridge' ) => 'yes' 
      ),
      'description' => __( "If checked, then field will not be displayed if its value is empty", 'wp-acf-vc-bridge' ),
    );


    /*
      Wrap Field
      Since 1.4.3
    */
    $params []= array(
      'type' => 'checkbox',
      'heading' => __( 'Wrap field', 'wp-acf-vc-bridge' ),
      'param_name' => 'wrap_field',
      'value' => array(
        __( 'Yes', 'wp-acf-vc-bridge' ) => 'yes' 
      ),
      'std' => $this->defaults[ 'wrap_field' ],
      'description' => __( "If checked, then field will be wrapped with HTML tag with classes", 'wp-acf-vc-bridge' ),
    );


    /*
      Wrapper Tag
      Since 1.4.3
    */
    $params []= array(
      'type' => 'dropdown',
      'heading' => __( 'Wrapper tag', 'wp-acf-vc-bridge' ),
      'param_name' => 'wrapper_tag',
      'value' => array(
        'DIV' => 'div',
        'SPAN' => 'span',
        'H1' => 'h1',
        'H2' => 'h2',
        'H3' => 'h3',
        'H4' => 'h4',
        'H5' => 'h5',
        'H6' => 'h6',
        'ARTICLE' => 'article',
        'SECTION' => 'section',
        'FIGURE' => 'figure',
        'HEADER' => 'header',
        'FOOTER' => 'footer',
      ),
      'std' => $this->defaults[ 'wrapper_tag' ],
      'dependency' => array(
        'element' => 'wrap_field',
        'value' => array( 'yes' ),
      )
    );

    /*
      Extra Classes
      Since 1.2.5
    */
    $params []= array(
      'type' => 'textfield',
      'heading' => __( 'Extra classes', 'wp-acf-vc-bridge' ),
      'param_name' => 'extra_classes',
      'value' => '',
      'description' => __( 'Additional class names for the field wrapper', 'wp-acf-vc-bridge' ),
      'dependency' => array(
        'element' => 'wrap_field',
        'value' => array( 'yes' ),
      )
    );

    return $params;
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
    $atts[ 'field_context' ] = 'custom';
    return '{{ vc_acf_field_picker:' . http_build_query( (array) $atts ) . ' }}';
  }


  /**
   *  Renders vc_acf_field_picker attribute used for VC Grid Item
   *
   *  @since 1.2.9
   *
   *  @param $value string
   *  @param $data array
   *
   *  @return string
   */
  public function vc_gitem_template_attribute_vc_acf_field_picker( $value, $data ) {
    parse_str( isset( $data[ 'data' ] ) ? $data[ 'data' ] : '', $atts );
    $atts[ 'force_data_context' ] = isset( $data[ 'post' ] ) ? $data[ 'post' ]->ID : '';
    return $this->shortcode( $atts );
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

    $acfFieldsOptions = $this->getAcfFieldsOptions();

    // Custom
    $params []= array(
      'type' => 'dropdown',
      'holder' => 'div',
      'heading' => __( 'Fields Group', 'wp-acf-vc-bridge' ),
      'param_name' => 'fields_group',
      'value' => $acfFieldsOptions[ 'group_options' ],
      'std' => '',
    );
    $params = array_merge( $params, $acfFieldsOptions[ 'custom_fields' ] );

    $params = array_merge( $params, $this->getExtraParams() );

    $shortcode = array(
      self::NAME . '-gitem' => array(
        'name' => __( 'ACF Field Picker', 'wp-acf-vc-bridge' ),
        'description' => __( 'Embed Advanced Custom Fields field from this or options page', 'wp-acf-vc-bridge' ),
        'base' => self::NAME . '-gitem',
        'class' => 'vc-acf-field-picker',
        'icon' => 'vc_icon-acf',
        'category' => 'Content',
        'post_type' => Vc_Grid_Item_Editor::postType(),
        'params' => $params,
        'js_view' => 'VcAcfFieldPickerView',
        'custom_markup' => '<h4 class="wpb_element_title"> <i class="vc_general vc_element-icon vc_icon-acf"></i> ACF Field Picker</h4>{{{ content }}}',
      )
    );

    return $shortcodes + $shortcode;
  }



}

VcAcfFieldPicker::instance();

endif;
?>