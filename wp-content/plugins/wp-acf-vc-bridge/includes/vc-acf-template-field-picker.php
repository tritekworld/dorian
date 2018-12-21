<?php

if ( !defined( 'ABSPATH' ) ) die();

if ( ! class_exists( 'VcAcfTemplateFieldPicker' ) ) :

class VcAcfTemplateFieldPicker {
  
  const NAME = 'vc-acf-template-field-picker';
  
  protected static $instance;
  public $defaults;
  public $option_fields;


  /**
   *  This function will setup ACF field picker
   */
  private function __construct() {
    add_action( 'init', array( $this, 'init' ) );
    add_shortcode( self::NAME, array( $this, 'shortcode' ) );
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

    $this->ignore_field_types = array( 'message', 'tab' );
    $this->template_field_types = array( 'flexible_content', 'repeater' );

    $this->defaults = array(
      'template_field' => '',
      'show_label' => '',
      'hide_empty' => '',
      'extra_classes' => '',
    );


    $is_vc_frontend_editor_request = function_exists( 'vc_post_param' ) ? in_array( vc_post_param( 'action' ), array( 'vc_edit_form', 'vc_load_shortcode' ) ) || ( 'true' === vc_get_param( 'vc_editable' ) ) : false;
    if ( $is_vc_frontend_editor_request || WpAcfVcBridge::instance()->willInitializeBackendEditor() ) {
      $this->option_fields = array();

      if ( $option_fields = get_field_objects( 'option' ) ) {
        foreach ( $option_fields as $field ) {
          $this->option_fields[ $field[ 'label' ] ] = $field[ 'name' ];
        }
      }

      $this->integrateWithVC();
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

    // Resolve field name
    $field_path = ! empty( $sanitized_atts[ 'template_field' ] ) ? explode( ':', $sanitized_atts[ 'template_field' ] ) : array();
    $field_name = count( $field_path ) ? array_slice( $field_path, -1 )[ 0 ] : false;

    // No reason to proceed if field name was set incorrectly
    if ( ! $field_name ) {
      return '';
    }

    // Get field from context
    $template_context = acf_field_templates()->get_template_context();
    $post_id = $template_context[ 'post_id' ];
    $available_values = $template_context[ 'values' ];
    $available_fields = $template_context[ 'sub_fields' ];
    $field_value = isset( $available_values[ $field_name ] ) ? $available_values[ $field_name ] : null;
    $field_object = isset( $available_fields[ $field_name ] ) ? $available_fields[ $field_name ] : null;

    // Do not proceed if such field doesn't exist
    if ( is_null( $field_value ) || is_null( $field_object ) ) {
      return '';
    }


    $field_object[ 'value' ] = $field_value;
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

    // Wrap field
    $result = sprintf( '<div class="vc-acf-field-picker-field vc-acf-field-picker-field-type-%s %s">%s</div>', $field_object[ 'type' ], $sanitized_atts[ 'extra_classes' ], $result );

    return $result;
  }


  /**
   *  Retrieves sub-fields from a give set of fields
   *
   *  @since 1.3.8
   *
   *  @param $fields array
   *  @param $prefix array
   *  @param $exclude array
   *  @param $include array
   *
   *  @return array
   */

  private function getSubFields( $fields, $prefix = array(), $exclude = array(), $include = array(), $is_base = false ) {
    $result = array();

    foreach ( $fields as $key => $field ) {

      // Skip excluded and allow just included fields
      if ( count( $exclude ) && in_array( $field[ 'type' ], $exclude ) ) {
        continue;
      }
      elseif ( count( $include ) && ! in_array( $field[ 'type' ], $include ) ) {
        continue;
      }

      // Process field type
      switch ( $field[ 'type' ] ) {
        case 'clone':
          $clone_fields = $this->getSubFields( $field[ 'sub_fields' ], $prefix, $exclude );
          $result = array_merge( $result, $clone_fields );
        break;
        
        case 'flexible_content':

          $new_prefix = array_merge( $prefix, array( $field[ 'name' ] ) );
          $result []= array(
            'value' => implode( ':', $new_prefix ),
            'title' => $field[ 'label' ],
            'enabled' => $is_base ? false : true,
          );

          $layouts = isset( $field[ 'layouts' ] ) && is_array( $field[ 'layouts' ] ) ? $field[ 'layouts' ] : array();
          foreach ( $layouts as $layout ) {

            $layout_prefix = $is_base ? array_merge( $new_prefix, array( $layout[ 'name' ] ) ) : array_merge( $new_prefix, array( $layout[ 'name' ] ) );
            $layout_fields = $this->getSubFields( $layout[ 'sub_fields' ], $layout_prefix, $exclude );
            if ( count( $layout_fields ) ) {

              $result []= array(
                'value' => implode( ':', $layout_prefix ),
                'title' => $layout[ 'label' ],
                'enabled' => false,
              );

              $result = array_merge( $result, $layout_fields );
            }
          }
        break;
        
        case 'repeater':
          $new_prefix = $is_base ? array_merge( $prefix, array( $field[ 'name' ] ) ) : array_merge( $prefix, array( $field[ 'name' ] ) );
          $repeater_fields = isset( $field[ 'sub_fields' ] ) && is_array( $field[ 'sub_fields' ] ) && count( $field[ 'sub_fields' ] ) ? $this->getSubFields( $field[ 'sub_fields' ], $new_prefix, $exclude ) : array();
          if ( count(  $repeater_fields ) ) {
            $result []= array(
              'value' => implode( ':', $new_prefix ),
              'title' => $field[ 'label' ],
              'enabled' => $is_base ? false : true,
            );

            $result = array_merge( $result, $repeater_fields );
          }
        break;
        
        default:
          $result []= array(
            'value' => implode( ':', array_merge( $prefix, array( $field[ 'name' ] ) ) ),
            'title' => $field[ 'label' ],
            'enabled' => true,
          );
        break;
      }
      
    }

    return $result;
  }


  /**
   *  Efficiently retrieves all ACF Field Groups and their Fields to be used in VC
   *
   *  @since 1.3.8
   *
   *  @return array
   */
  private function getAcfTemlpateFields() {
    $template_fields = array();
    $template_fields []= array(
      'value' => '',
      'title' => __( '-- Select field --', 'wp-acf-vc-bridge' ),
      'enabled' => true,
    );

    $field_groups = function_exists( 'acf_get_field_groups' ) ? acf_get_field_groups() : apply_filters( 'acf/get_field_groups', array() );

    // Loop groups if exist
    if ( $field_groups ) {
      foreach ( $field_groups as $field_group ) {

        $id = isset( $field_group['id'] ) ? 'id' : ( isset( $field_group[ 'ID' ] ) ? 'ID' : 'id' );
        $field_group_id = $field_group[ $id ];

        // Local fields can't be retrieved
        if ( ! $field_group_id ) {
          continue;
        }

        // Get group fields
        $group_fields = function_exists( 'acf_get_fields' ) ? acf_get_fields( $field_group_id ) : apply_filters( 'acf/field_group/get_fields', array(), $field_group_id );
       
        if ( $group_fields ) {

          $group_fields = $this->getSubFields( $group_fields, array(), $this->ignore_field_types, $this->template_field_types, true );
          if ( count( $group_fields ) ) {
            $template_fields = array_merge( $template_fields, $group_fields );
          }

        }
     
      }
    } 

    return $template_fields;
  }


  /**
   *  Register shortcode param in Visual Composer
   *
   *  @since 1.3.8
   *
   *  @param $name string
   *  @param $callback callable
   *  @param $js_url string
   *
   *  @return n/a
   */

  public function addShortcodeParam( $name, $callback, $js_url = null ) {

    if ( function_exists( 'vc_add_shortcode_param' ) ) {
      vc_add_shortcode_param( $name, $callback, $js_url );
      return true;
    }
    elseif ( function_exists( 'add_shortcode_param' ) ) {
      add_shortcode_param( $name, $callback, $js_url );
      return true;
    }
    else {
      return false;
    }

  }


  /**
   *  Render param in Visual Composer
   *
   *  @since 1.3.8
   *
   *  @param $settings array
   *  @param $value mixed
   *
   *  @return string
   */

  public function renderParamAcfTemplateField( $settings, $value ) {
    ob_start();
    ?>
      <select name="<?= $settings[ 'param_name' ] ?>" class="wpb_vc_param_value wpb-input wpb-select <?= $settings[ 'param_name' ] ?> acf_template_field">
        <?php foreach ( $settings[ 'value' ] as $key => $item ) : ?>
          <option value="<?= $item[ 'value' ] ?>" <?= ! $item[ 'enabled' ] ? 'disabled="disabled"' : '' ?> <?= selected( $item[ 'value' ], $value, false ) ?>><?= str_repeat( '&nbsp;', substr_count( $item[ 'value' ], ':' ) ) . $item[ 'title' ] ?></option>
        <?php endforeach; ?>
      </select>
    <?php
    return ob_get_clean();
  }


  /**
   *  Integrate shortcode with Visual Composer
   *
   *  @since 1.3.8
   *
   *  @param $settings array
   *  @param $value mixed
   *
   *  @return string
   */

  public function integrateWithVC() {
    $params = array();

    if ( ! $this->addShortcodeParam( 'acf_template_field', array( $this, 'renderParamAcfTemplateField' ) ) ) {
      return false;
    }


    // Custom
    $params []= array(
      'type' => 'acf_template_field',
      'holder' => 'div',
      'heading' => __( 'Template Field', 'wp-acf-vc-bridge' ),
      'param_name' => 'template_field',
      'value' => $this->getAcfTemlpateFields(),
      'std' => '',
    );

    $params []= array(
      'type' => 'checkbox',
      'heading' => __( 'Show label', 'wp-acf-vc-bridge' ),
      'param_name' => 'show_label',
      'value' => array(
        __( 'Yes', 'wp-acf-vc-bridge' ) => 'yes' 
      ),
      'description' => __( "If checked, the field label (configured in field settings) will be shown before the field value", 'wp-acf-vc-bridge' ),
    );

    $params []= array(
      'type' => 'checkbox',
      'heading' => __( 'Hide empty', 'wp-acf-vc-bridge' ),
      'param_name' => 'hide_empty',
      'value' => array(
        __( 'Yes', 'wp-acf-vc-bridge' ) => 'yes' 
      ),
      'description' => __( "If checked, then field will not be displayed if its value is empty", 'wp-acf-vc-bridge' ),
    );

    $params []= array(
      'type' => 'textfield',
      'heading' => __( 'Extra classes', 'wp-acf-vc-bridge' ),
      'param_name' => 'extra_classes',
      'value' => '',
      'description' => __( 'Additional class names for the field wrapper', 'wp-acf-vc-bridge' ),
    );


    vc_map( array(
      'name' => __( 'ACF Template Field', 'wp-acf-vc-bridge' ),
      'description' => __( 'Embed Advanced Custom Fields field when used in template', 'wp-acf-vc-bridge' ),
      'base' => self::NAME,
      'class' => 'vc-acf-field-picker',
      'icon' => 'vc_icon-acf',
      'category' => 'Content',
      'params' => $params,
      'post_type' => WpAcfVcBridgeVcSnippets::POST_TYPE,
    ) );

  }



}

VcAcfTemplateFieldPicker::instance();

endif;
?>