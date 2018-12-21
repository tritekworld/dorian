<?php

if( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'acf_field_visual_composer' ) ) :

class acf_field_visual_composer extends acf_field {

  
  /**
   *  This function will setup the field type data
   *
   * @param $settings array
   */
  function __construct( $settings ) {
    
    $this->name = 'visual_composer';
    $this->label = __( 'Visual Composer', 'wp-acf-vc-bridge' );
    $this->category = 'content';
    
    $this->defaults = array(
      'view_mode' => 'full', // compact, full
      'allow_classic_mode' => 0, // 0, 1
      'edit_btn_label' => __( 'Edit content', 'wp-acf-vc-bridge' ),
      'done_btn_label' => __( 'Done', 'wp-acf-vc-bridge' ),
      'fullscreen_btn_label' => __( 'Full screen mode', 'wp-acf-vc-bridge' ),
      'description_label' => __( 'Description', 'wp-acf-vc-bridge' ),
      'return_format' => 'complete', // array, content_raw, content_processed, complete
      'front_wrapper' => array(
        'id' => '',
        'class' => '',
        'style' => '',
      ),
    );

    $this->default_values = array(
      'content'    => '',
      'css'      => '',
      'description'    => '',
    );
    
    $this->l10n = array();
    
    $this->settings = array_merge( $settings, array( 'version' => WpAcfVcBridge::VERSION ) );
    
    parent::__construct();

    add_action( 'wp_ajax_vc_get_vc_grid_data', array( $this, 'ajax_get_grid_data' ), 5 );
    add_action( 'wp_ajax_nopriv_vc_get_vc_grid_data', array( $this, 'ajax_get_grid_data' ), 5 );
  }
  
  
  /**
   *  Creates extra settings for the field. These are visible when editing a field
   *
   *  @param $field (array) the $field being edited
   */
  function render_field_settings( $field ) {
    
    acf_render_field_setting( $field, array(
      'label'     => __( 'View mode', 'wp-acf-vc-bridge' ),
      'instructions'  => __( 'Compact mode shows description and edit button, while full mode renders VC backend editor (could be slower)', 'wp-acf-vc-bridge' ),
      'type'      => 'radio',
      'name'      => 'view_mode',
      'layout'    => 'horizontal', 
      'choices'   => array(
        'full'  => __( 'Full', 'wp-acf-vc-bridge' ),
        'compact'    => __( 'Compact', 'wp-acf-vc-bridge' ), 
      )
    ));

    acf_render_field_setting( $field, array(
      'label'     => __( 'Allow Classic Mode','wp-acf-vc-bridge' ),
      'instructions'  => '',
      'type'      => 'true_false',
      'name'      => 'allow_classic_mode',
      'message'   => __( 'Shows or hides Backend editor and Classic mode switch buttons ', 'wp-acf-vc-bridge' )
    ));

    acf_render_field_setting( $field, array(
      'label'     => __( 'Edit button label', 'wp-acf-vc-bridge' ),
      'instructions'  => '',
      'type'      => 'text',
      'name'      => 'edit_btn_label',
    ));

    acf_render_field_setting( $field, array(
      'label'     => __( 'Done button label', 'wp-acf-vc-bridge' ),
      'instructions'  => '',
      'type'      => 'text',
      'name'      => 'done_btn_label',
    ));

    acf_render_field_setting( $field, array(
      'label'     => __( 'Full screen mode button label', 'wp-acf-vc-bridge' ),
      'instructions'  => '',
      'type'      => 'text',
      'name'      => 'fullscreen_btn_label',
    ));

    acf_render_field_setting( $field, array(
      'label'     => __( 'Description label', 'wp-acf-vc-bridge' ),
      'instructions'  => '',
      'type'      => 'text',
      'name'      => 'description_label',
    ));

    acf_render_field_setting( $field, array(
      'label'     => __( 'Return value', 'wp-acf-vc-bridge' ),
      'instructions'  => __( 'Specify the returned value on front end','acf' ),
      'type'      => 'radio',
      'name'      => 'return_format',
      'layout'    => 'vertical',
      'choices'   => array(
        'complete'      => __( 'Complete (process content shortcodes and custom CSS styles)', 'wp-acf-vc-bridge' ),
        'content_processed'     => __( 'Content (shortcodes processed)', 'wp-acf-vc-bridge' ),
        'content_raw'     => __( 'Raw content', 'wp-acf-vc-bridge' ),
        'array'     => __( 'Raw data array', 'wp-acf-vc-bridge' ),
      )
    ));

    
    // Front-end wrapper
    acf_render_field_wrap(array(
      'label'     => __( 'Front-end Wrapper Attributes', 'wp-acf-vc-bridge' ),
      'instructions'  => __( 'If filled, then div tag with specified attributes will wrap the contents', 'wp-acf-vc-bridge' ),
      'type'      => 'text',
      'name'      => 'id',
      'prefix'    => $field[ 'prefix' ] . '[front_wrapper]',
      'value'     => $field[ 'front_wrapper' ][ 'id' ],
      'prepend'   => 'id',
      'wrapper'   => array(
        'data-name' => 'front_wrapper'
      )
    ), 'tr');

    acf_render_field_wrap(array(
      'label'     => '',
      'instructions'  => '',
      'type'      => 'text',
      'name'      => 'class',
      'prefix'    => $field[ 'prefix' ] . '[front_wrapper]',
      'value'     => $field[ 'front_wrapper' ][ 'class' ],
      'prepend'   => 'class',
      'wrapper'   => array(
        'data-append' => 'front_wrapper'
      )
    ), 'tr');
    
    acf_render_field_wrap(array(
      'label'     => '',
      'instructions'  => '',
      'type'      => 'text',
      'name'      => 'style',
      'prefix'    => $field[ 'prefix' ] . '[front_wrapper]',
      'value'     => $field[ 'front_wrapper' ][ 'style' ],
      'prepend'   => 'style',
      'append'    => '',
      'wrapper'   => array(
        'data-append' => 'front_wrapper'
      )
    ), 'tr');

  }
    
  
  /**
   * Render field in admin
   *
   * @param $field object
   */
  function render_field( $field ) {
    // Setup value
    $field[ 'value' ] = empty( $field[ 'value' ] ) ? array() : $field[ 'value' ];
    $field[ 'value' ] = ! is_array( $field[ 'value' ] ) ? array( 'content' => $field[ 'value' ] ) : $field[ 'value' ]; // If switching from another text field
    $field[ 'value' ] = wp_parse_args( $field[ 'value' ], $this->default_values );
    $view_mode = $field[ 'view_mode' ];
    ?>
    <div class="acf-hidden">
      <?php foreach( $field[ 'value' ] as $key => $value ): ?>
        <input type="hidden" class="input-<?= $key; ?>" name="<?= esc_attr( $field[ 'name' ] ); ?>[<?= $key; ?>]" value="<?= esc_attr( $value ); ?>" />
      <?php endforeach; ?>
    </div>

    <div class="acf-field-vc view-mode-<?= $view_mode ?> allow-classic-mode-<?= $field[ 'allow_classic_mode' ] ?>">
      <p class="acf-field-vc-description"><?= $field[ 'value' ][ 'description' ] ?></p>
      <div class="acf-field-vc-wrapper">
        <div class="acf-field-vc-popup">
          <div class="acf-field-vc-popup-header">
            <a class="acf-field-vc-done button button-primary button-large"><?= $field[ 'done_btn_label' ] ?></a>
            <input class="acf-field-vc-input-description" type="text" value="<?= $field[ 'value' ][ 'description' ] ?>" placeholder="<?= $field[ 'description_label' ] ?>">
          </div>
          <div class="acf-field-vc-popup-content">
            <iframe class="acf-field-vc-iframe" data-src="<?= admin_url( 'post-new.php?post_type=' . WpAcfVcBridgeVcSnippets::POST_TYPE . '&vc_snippet_embedded=1' ) ?>"></iframe>
            <div class="acf-field-vc-loader sk-folding-cube">
              <div class="sk-cube1 sk-cube"></div>
              <div class="sk-cube2 sk-cube"></div>
              <div class="sk-cube4 sk-cube"></div>
              <div class="sk-cube3 sk-cube"></div>
            </div>            
          </div>
        </div>
      </div>
      <a class="acf-field-vc-edit button button-primary button-large"><?= 'compact' == $view_mode ? $field[ 'edit_btn_label' ] : $field[ 'fullscreen_btn_label' ] ?></a>
    </div>
    <?php
  }

  
  /**
   *  This action is called in the admin_enqueue_scripts action on the edit screen where the field is created.
   *  Adds CSS + JavaScript to assist your render_field() action.
   */
  function input_admin_enqueue_scripts() {
    $url = $this->settings[ 'url' ];
    $version = $this->settings[ 'version' ];
    $asset_name = 'acf-field-visual-composer-input';

    // JS
    wp_register_script( $asset_name, "{$url}assets/js/{$asset_name}.js", array( 'acf-input' ), $version );
    wp_enqueue_script( $asset_name );
    
    // CSS
    wp_register_style( $asset_name, "{$url}assets/css/{$asset_name}.css", array( 'acf-input' ), $version );
    wp_enqueue_style( $asset_name );
  }
  
  
  /**
   *  This filter is appied to the $value after it is loaded from the db and before it is returned to the template
   *
   *  @param $value (mixed) the value which was loaded from the database
   *  @param $post_id (mixed) the $post_id from which the value was loaded
   *  @param $field (array) the field array holding all the field options
   *
   *  @return  $value (mixed) the modified value
   */
  function format_value( $value, $post_id, $field ) {
    if ( empty( $value ) ) {
      return $value;
    }

    $value = ! is_array( $value ) ? array( 'content' => $value ) : $value; // If switching from another text field
    $value = array_merge( $this->default_values, $value );

    if ( function_exists( 'visual_composer' ) ) {
      WPBMap::addAllMappedShortcodes();
      $shortcodes_custom_css = visual_composer()->parseShortcodesCustomCss( $value[ 'content' ] );
      if ( ! empty( $shortcodes_custom_css ) ) {
        $shortcodes_custom_css = strip_tags( $shortcodes_custom_css );
        $value[ 'css' ] .= $shortcodes_custom_css;
      }
    }


    switch ( $field[ 'return_format' ] ) {
      case 'array':
        return $value;
      break;
      case 'content_raw':
        return self::wrap_value( $value[ 'content' ], $field[ 'front_wrapper' ] );
      break;
      case 'content_processed':
        return self::wrap_value( do_shortcode( $value[ 'content' ] ), $field[ 'front_wrapper' ] );
      break;
      case 'complete':
        $content = do_shortcode( $value[ 'content' ] );
        $css = $value[ 'css' ] ? sprintf( '<style type="text/css">%s</style>', $value[ 'css' ] ) : '';
        return self::wrap_value( $content . $css, $field[ 'front_wrapper' ] );
      break;
    }


    return $value;
  }


  /**
  *  load_value()
  *
  *  Loads the CSS and description additionally to the content
  *
  *  @since 1.3.2
  *
  *  @param $value (mixed) the value found in the database
  *  @param $post_id (mixed) the $post_id from which the value was loaded
  *  @param $field (array) the field array holding all the field options
  *  @return  $value
  */
  function load_value( $value, $post_id, $field ) {

    // If value saved when plugin version was before 1.3.2
    if ( $value && is_array( $value ) ) {
      return $value;
    }

    $content = strval( $value );
    $value = $this->default_values;
    $field_name = $field[ 'name' ];

    // Fill all value params
    foreach ( $this->default_values as $key => $default_value ) {

      if ( $key === 'content' ) {
        $value[ $key ] = $content;
      }
      // All value params except the content are saved as meta values in wp_postmeta, wp_termmeta or wp_options tables
      else {
        $meta_key = sprintf( '%s_%s', $field_name, $key );
        $value[ $key ] = strval( acf_get_metadata( $post_id, $meta_key ) );
      }
      
    }

    return $value;
  }
  
  
  /**
  *  delete_value()
  *
  *  Remove the associated field params like CSS and description
  *
  *  @since 1.3.2
  *
  *  @param $post_id (mixed) the $post_id from which the value was deleted
  *  @param $field_name (string) the $meta_key which the value was deleted
  *  @param $field (object) the $meta_key which the value was deleted
  *  @return  n/a
  */
  
  function delete_value( $post_id, $field_name, $field ) {
    $field_name = $field[ 'name' ];

    foreach ( $this->default_values as $key => $default_value ) {
      if ( $key === 'content' ) continue;
      
      $meta_key = sprintf( '%s_%s', $field_name, $key );
      acf_delete_metadata( $post_id, $meta_key );
    }

  }
    

  /**
  *  update_value()
  *
  *  This filter is applied to the $value before it is saved in the db
  *
  *  @since 1.2.9
  *
  *  @param $value (mixed) the value found in the database
  *  @param $post_id (mixed) the $post_id from which the value was loaded
  *  @param $field (array) the field array holding all the field options
  *  @return  $value
  */
  function update_value( $value, $post_id, $field ) {
    $value = ! is_array( $value ) ? array( 'content' => $value ) : $value;
    $value = array_merge( $this->default_values, $value );

    /*
      Since 1.2.9
    */
    $this->update_vc_grid_value( $value, $post_id, $field );


    /*
      Update relative field's settings, like CSS and description
      Since 1.3.2
    */
    $field_name = $field[ 'name' ];
    foreach ( $this->default_values as $key => $default_value ) {
      if ( $key === 'content' ) continue;
      
      $meta_key = sprintf( '%s_%s', $field_name, $key );
      $param_value = $value[ $key ];
      acf_update_metadata( $post_id, $meta_key, $param_value );
    }

    // From now on the value is the contents of Visual Composer
    $value = $value[ 'content' ];

    return $value;
  }


  /**
  *  convert_grid_atts_to_assoc()
  *
  *  Converts atts array from param="value" to associative array
  *
  *  @since 1.3.4
  *
  *  @param $atts (array)
  *  @return  $array
  */
  public function convert_grid_atts_to_assoc( $atts ) {
    $result = array();
    foreach ( $atts as $key => $value ) {
      if ( preg_match( '/(.*?)=\"(.*?)\"/', $value, $matches ) ) {
        $result[ $matches[ 1 ] ] = $matches[ 2 ];
      }
    }

    return $result;
  }


  /**
  *  update_vc_grid_value()
  *
  *  Updates grid settings if applies
  *
  *  @since 1.2.9
  *
  *  @param $value (mixed) the value found in the database
  *  @param $post_id (mixed) the $post_id from which the value was loaded
  *  @param $field (array) the field array holding all the field options
  *  @return  $value
  */
  public function update_vc_grid_value( $value, $post_id, $field ) {

    if ( ! class_exists( 'Vc_Hooks_Vc_Grid' ) ) {
      return;
    }

    // Parse VC content and extract VC Grid settings
    $post = new stdClass();
    $post->post_content = stripcslashes( $value[ 'content' ] );
    $vc_grid_hooks = new Vc_Hooks_Vc_Grid();
    $vc_grid_settings = $vc_grid_hooks->gridSavePostSettingsId( array(), $post_id, $post );


    /*
      @Since 1.3.4
      Convert shortcode atts returned by gridSavePostSettingsId in param="value" indexed array format
      to associative array
    */
    if ( isset( $vc_grid_settings[ 'vc_grid_id' ] ) && isset( $vc_grid_settings[ 'vc_grid_id' ][ 'shortcodes' ] ) ) {
      foreach ( $vc_grid_settings[ 'vc_grid_id' ][ 'shortcodes' ] as $grid_id => $shortcode ) {
        $atts = $shortcode[ 'atts' ];
        if ( ! isset( $atts[ 'grid_id' ] ) ) {
          $shortcode[ 'atts' ] = $this->convert_grid_atts_to_assoc( $atts );
          $vc_grid_settings[ 'vc_grid_id' ][ 'shortcodes' ][ $grid_id ] = $shortcode;
        }
      }
    }


    // Get list of existing Grid IDs saved for this post_id and field_key
    $vc_grid_ids_option_key = sprintf( 'vc_grid_id_%s_%s', $post_id, $field[ 'key' ] );
    $existing_grid_ids = get_option( $vc_grid_ids_option_key, array() );

    // Remove those Grid settings from options table
    foreach ( $existing_grid_ids as $grid_id ) {
      $vc_grid_settings_option_key = sprintf( 'vc_grid_settings_%s', $grid_id );
      delete_option( $vc_grid_settings_option_key );
    }


    // Save new list of the newly used VC Grid IDs
    $vc_grid_shortcodes = isset( $vc_grid_settings[ 'vc_grid_id' ][ 'shortcodes' ] ) ? $vc_grid_settings[ 'vc_grid_id' ][ 'shortcodes' ] : array();
    $new_grid_ids = ! empty( $vc_grid_shortcodes ) ? array_keys( $vc_grid_shortcodes ) : array();

    // Clear and Stop if there are no VC Grids used in VC field
    if ( empty( $new_grid_ids ) ) {
      delete_option( $vc_grid_ids_option_key );
      return;
    }
    // Save new IDs in options table
    else {
      update_option( $vc_grid_ids_option_key, $new_grid_ids );
    }



    // Save each grid's settings as separate records in options table
    foreach ( $vc_grid_shortcodes as $grid_id => $shortcode ) {
      $vc_grid_settings_option_key = sprintf( 'vc_grid_settings_%s', $grid_id );
      update_option( $vc_grid_settings_option_key, array(
        'vc_grid_id' => array(
          'shortcodes' => array(
            $grid_id => $shortcode
          )
        )
      ) );
    }

  }


  /**
  *  ajax_get_grid_data()
  *
  *  Selectively adds hooks to VC Grid data requesting when it has been added to Visual Composer ACF Field
  *
  *  @since 1.2.9
  *
  *  @return  n/a
  */
  public function ajax_get_grid_data() {
    if ( ! function_exists( 'vc_request_param' ) ) {
      return;
    }

    $vc_request_param_data = vc_request_param( 'data' );
    $requested_vc_grid_id = $vc_request_param_data[ 'shortcode_id' ];

    $vc_grid_settings_option_key = sprintf( 'vc_grid_settings_%s', $requested_vc_grid_id );
    $this->vc_grid_settings = get_option( $vc_grid_settings_option_key, false );

    // Stop further processing because the grid requested is not the one created in ACF field
    if ( ! $this->vc_grid_settings ) {
      return;
    }

    // From now on we just need to watch the get post meta calls until VC attempts to access VC Grid Settings
    // get_post_meta is fired from WPBakeryShortCode_VC_Basic_Grid::findPostShortcodeById()
    add_filter( 'get_post_metadata', array( $this, 'filter_vc_grid_post_meta' ), 10, 4 );


    /*
      Since 1.4.16
    */
    if ( isset( $this->vc_grid_settings[ 'vc_grid_id' ][ 'shortcodes' ][ $requested_vc_grid_id ][ 'atts' ] ) ) {
      $atts = $this->vc_grid_settings[ 'vc_grid_id' ][ 'shortcodes' ][ $requested_vc_grid_id ][ 'atts' ];
      if ( isset( $atts[ 'not_exclude_cur_post' ] ) && ( 'dont_exclude' === $atts[ 'not_exclude_cur_post' ] ) ) {
        $this->current_post = $vc_request_param_data[ 'page_id' ];
        add_action( 'pre_get_posts', array( $this, 'remove_excluded_current_post_from_query' ) );
      }
    }
  }


  /**
  *  remove_excluded_current_post_from_query()
  *
  *  Prevents the default VC Post Grids behavior with removing current post from the list
  *
  *  @since 1.4.16
  *
  *  @param $the_query (WP_Query)
  *
  *  @return N/A
  */
  public function remove_excluded_current_post_from_query( $the_query ) {
    $exclude = $the_query->get( 'exclude' );
    $exclude = explode( ',', $exclude );
    if ( FALSE !== ( $pos = array_search( $this->current_post, $exclude ) ) ) {
      unset( $exclude[ $pos ] );
      $exclude = count( $exclude ) ? array_values( $exclude ) : array();
      $the_query->set( 'exclude', implode( ',', $exclude ) );
    }

    $post__not_in = $the_query->get( 'post__not_in' );
    if ( $post__not_in ) {
      $post__not_in = is_array( $post__not_in ) ? $post__not_in : [ $post__not_in ];
      if ( FALSE !== ( $pos = array_search( $this->current_post, $post__not_in ) ) ) {
        unset( $post__not_in[ $pos ] );
        $post__not_in = count( $post__not_in ) ? array_values( $post__not_in ) : '';
        $the_query->set( 'post__not_in', $post__not_in );
      }
    }
  }


  /**
  *  filter_vc_grid_post_meta()
  *
  *  Retrieve proper VC Grid settings
  *
  *  @since 1.2.9
  *
  *  @param $meta_data (array)
  *  @param $object_id (int)
  *  @param $meta_key (string)
  *  @param $single (boolean)
  *  @return  $value
  */
  public function filter_vc_grid_post_meta( $meta_data, $object_id, $meta_key, $single ) {
    if ( '_vc_post_settings' !== $meta_key ) {
      return $meta_data;
    }

    // Stop listening. Deprecated since 1.4.3
    // remove_filter( 'get_post_metadata', array( $this, 'filter_vc_grid_post_meta', 10 ) );

    return $single ? $this->vc_grid_settings : array( $this->vc_grid_settings );
  }
  

  /**
   *  Apply custom wrapper on front-end
   *
   *  @param $value (mixed) the value which was loaded from the database
   *  @param $wrapper (array) wrapper settings
   *
   *  @return  $value (mixed) the modified value
   */
  public static function wrap_value( $value, $wrapper ) {
    $wrapper = array_filter( $wrapper );

    if ( ! empty( $wrapper ) ) {
      array_walk( $wrapper, function( &$item, $key ) {
        $item = sprintf( '%s="%s"', $key, $item );
      });
      $wrapper = implode( ' ', $wrapper );
      $value = sprintf( '<div %s>%s</div>', $wrapper, $value );
    }

    return $value;
  }
  

  /**
   *  This filter is used to perform validation on the value prior to saving.
   *  All values are validated regardless of the field's required setting. This allows you to validate and return
   *  messages to the user if the value is not correct
   *
   *  @param $valid (boolean) validation status based on the value and the field's required setting
   *  @param $value (mixed) the $_POST value
   *  @param $field (array) the field array holding all the field options
   *  @param $input (string) the corresponding input name for $_POST value
   *  @return  $valid
   */
  function validate_value( $valid, $value, $field, $input ) {

    // No need to check if it's not required
    if ( ! $field[ 'required' ] ) {
      return $valid;
    }
      
    // Just anything set for content is enough
    if ( empty( $value ) || empty( $value[ 'content' ] ) ) {
      return false;
    }
    
    return $valid;
  }
  
}

endif;

/*
  Since 1.3.8
  Prevent infinite vc snippet embedding
*/
if ( ! isset( $_GET[ 'vc_snippet_embedded' ] ) ) {
  
  // ACF 5: Include field
  add_action( 'acf/include_field_types', function() {
    $field_settings = array(
      'url' => WP_ACF_VC_BRIDGE_PLUGIN_URL,
      'path' => WP_ACF_VC_BRIDGE_PLUGIN_PATH,
    );

    new acf_field_visual_composer( $field_settings );
  } );

}

?>