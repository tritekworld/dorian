<?php

if ( !defined( 'ABSPATH' ) ) die();

/**
 *  Setup default render filters for all ACF PRO fields
 *  Called with: apply_filters( sprintf( 'vc_acf_field_picker_render_field_type_%s', $field_object[ 'type' ] ), $value, $field_object, $post_id );
 *
 */

add_action( 'init', function() {

  if ( apply_filters( 'vc_acf_field_picker_default_fields_rendering', true ) ) {
    add_filter( 'vc_acf_field_picker_render_field_type_password', 'vc_acf_field_picker_render_field_type_password_filter', 10, 3 );
    add_filter( 'vc_acf_field_picker_render_field_type_file', 'vc_acf_field_picker_render_field_type_file_filter', 10, 3 );
    add_filter( 'vc_acf_field_picker_render_field_type_gallery', 'vc_acf_field_picker_render_field_type_gallery_filter', 10, 3 );
    add_filter( 'vc_acf_field_picker_render_field_type_visual_composer', 'vc_acf_field_picker_render_field_type_visual_composer_filter', 10, 3 );
    add_filter( 'vc_acf_field_picker_render_field_type_image', 'vc_acf_field_picker_render_field_type_image_filter', 10, 3 );
    add_filter( 'vc_acf_field_picker_render_field_type_select', 'vc_acf_field_picker_render_field_type_select_filter', 10, 3 );
    add_filter( 'vc_acf_field_picker_render_field_type_radio', 'vc_acf_field_picker_render_field_type_radio_filter', 10, 3 );
    add_filter( 'vc_acf_field_picker_render_field_type_checkbox', 'vc_acf_field_picker_render_field_type_checkbox_filter', 10, 3 );
    add_filter( 'vc_acf_field_picker_render_field_type_true_false', 'vc_acf_field_picker_render_field_type_true_false_filter', 10, 3 );
    add_filter( 'vc_acf_field_picker_render_field_type_post_object', 'vc_acf_field_picker_render_field_type_post_object_filter', 10, 3 );
    add_filter( 'vc_acf_field_picker_render_field_type_page_link', 'vc_acf_field_picker_render_field_type_page_link_filter', 10, 3 );
    add_filter( 'vc_acf_field_picker_render_field_type_email', 'vc_acf_field_picker_render_field_type_email_filter', 10, 3 );
    add_filter( 'vc_acf_field_picker_render_field_type_url', 'vc_acf_field_picker_render_field_type_url_filter', 10, 3 );
    add_filter( 'vc_acf_field_picker_render_field_type_relationship', 'vc_acf_field_picker_render_field_type_relationship_filter', 10, 3 );
    add_filter( 'vc_acf_field_picker_render_field_type_taxonomy', 'vc_acf_field_picker_render_field_type_taxonomy_filter', 10, 3 );
    add_filter( 'vc_acf_field_picker_render_field_type_user', 'vc_acf_field_picker_render_field_type_user_filter', 10, 3 );
    add_filter( 'vc_acf_field_picker_render_field_type_google_map', 'vc_acf_field_picker_render_field_type_google_map_filter', 10, 3 );
    add_filter( 'vc_acf_field_picker_render_field_type_color_picker', 'vc_acf_field_picker_render_field_type_color_picker_filter', 10, 3 );
    add_filter( 'vc_acf_field_picker_render_field_type_group', 'vc_acf_field_picker_render_field_type_group_filter', 10, 3 );
    add_filter( 'vc_acf_field_picker_render_field_type_repeater', 'vc_acf_field_picker_render_field_type_repeater_filter', 10, 3 );
    add_filter( 'vc_acf_field_picker_render_field_type_flexible_content', 'vc_acf_field_picker_render_field_type_flexible_content_filter', 10, 3 );
    add_filter( 'vc_acf_field_picker_render_field_type_table', 'vc_acf_field_picker_render_field_type_table_filter', 10, 3 );
  }
  
  add_action( 'wp_enqueue_scripts', 'vc_acf_field_picker_enqueue_google_map_assets' );
  
} );



function vc_acf_field_picker_render_field_type_password_filter( $value, $field_object, $post_id ) {
  return str_repeat( "*", strlen( $value ) );
}


function vc_acf_field_picker_render_field_type_file_filter( $value, $field_object, $post_id ) {
  if ( ! $value ) {
    return $value;
  }

  if ( is_array( $value ) ) {
    $value = $field_object[ 'value' ];
    $url = $value[ 'url' ];
    $title = $value[ 'title' ];
  }
  elseif ( is_numeric( $value ) ) {
    if ( $post = get_post( $value ) ) {
      $url = $post->guid;
      $title = $post->post_title;
    }
  }
  else {
    $url = $value;
    $title = $value;
  }
  return sprintf( '<a href="%s" target="_blank">%s</a>', $url, $title );
}


function vc_acf_field_picker_render_field_type_gallery_filter( $value, $field_object, $post_id ) {
  if ( ! $value ) {
    return $value;
  }

  if ( is_array( $value ) && ! empty( $value ) ) {
    $value = array_map( function( $item ) {
      return $item[ 'ID' ];
    }, $value );
    $value = sprintf( '[gallery ids="%s"]', implode( ',', $value ) );
    $value = do_shortcode( $value );
  }
  return $value;
}


function vc_acf_field_picker_render_field_type_visual_composer_filter( $value, $field_object, $post_id ) {
  if ( ! $value ) {
    return $value;
  }

  if ( is_array( $value ) && ! empty( $value ) ) {
    $content = do_shortcode( $value[ 'content' ] );
    $css = $value[ 'css' ] ? sprintf( '<style type="text/css">%s</style>', $value[ 'css' ] ) : '';
    return acf_field_visual_composer::wrap_value( $content . $css, $field_object[ 'front_wrapper' ] );
  }
  else {
    return $value;
  }
}


function vc_acf_field_picker_render_field_type_image_filter( $value, $field_object, $post_id ) {
  if ( ! $value ) {
    return $value;
  }

  $size = apply_filters( 'vc_acf_field_picker_render_field_type_image_size', 'medium', $post_id, $field_object );

  if ( is_array( $value ) ) {
    $value = $field_object[ 'value' ];
    return wp_get_attachment_image( $value[ 'ID' ], $size );
  }
  elseif ( is_numeric( $value ) ) {
    return wp_get_attachment_image( $value, $size );
  }
  else {
    return sprintf( '<img src="%s">', $value );
  }
}


function vc_acf_field_picker_render_field_type_select_filter( $value, $field_object, $post_id ) {
  if ( ! $value ) {
    return $value;
  }

  if ( is_array( $value ) && ! isset( $value[ 'label' ] ) ) {
    $items = array();
    foreach ( $value as $key => $item ) {
      $items []= vc_acf_field_picker_get_select_field_value( $item );
    }

    return sprintf( '<ul><li>%s</li></ul>', implode( '</li><li>', $items ) );
  }
  else {
    return vc_acf_field_picker_get_select_field_value( $value );
  }
}


function vc_acf_field_picker_get_select_field_value( $value ) {
  if ( is_array( $value ) && isset( $value[ 'label' ] ) ) {
    return sprintf( '%s: %s', $value[ 'label' ], esc_html( $value[ 'value' ] ) );
  }
  elseif ( is_array( $value ) ) {

  }
  else {
    return esc_html( $value );
  }
}


function vc_acf_field_picker_render_field_type_radio_filter( $value, $field_object, $post_id ) {
  return vc_acf_field_picker_render_field_type_select_filter( $value, $field_object, $post_id );
}



function vc_acf_field_picker_render_field_type_checkbox_filter( $value, $field_object, $post_id ) {
  if ( ! $value ) {
    return $value;
  }

  if ( is_array( $value ) ) {
    $items = array();
    foreach ( $value as $item ) {
      $items []= is_array( $item ) ? sprintf( '%s: %s', $item[ 'label' ], esc_html( $item[ 'value' ] ) ) : $item;
    }
    return sprintf( '<ul><li>%s</li></ul>', implode( '</li><li>', $items ) );
  }
  else {
    return esc_html( $value );
  }
}


function vc_acf_field_picker_render_field_type_true_false_filter( $value, $field_object, $post_id ) {
  return $value ? __( 'yes', 'wp-acf-vc-bridge' ) : __( 'no', 'wp-acf-vc-bridge' );
}


function vc_acf_field_picker_render_field_type_post_object_filter( $value, $field_object, $post_id ) {
  if ( ! $value ) {
    return $value;
  }

  if ( is_array( $value ) ) {
    $items = array_map(function( $item ) {
      return vc_acf_field_picker_render_field_type_filter_get_post_link( $item );
    }, $value );

    return sprintf( '<ul><li>%s</li></ul>', implode( '</li><li>', $items ) );
  }
  else {
    return vc_acf_field_picker_render_field_type_filter_get_post_link( $value );
  }
}


function vc_acf_field_picker_render_field_type_relationship_filter( $value, $field_object, $post_id ) {
  return vc_acf_field_picker_render_field_type_post_object_filter( $value, $field_object, $post_id );
}


function vc_acf_field_picker_render_field_type_filter_get_post_link( $value ) {
  if ( is_object( $value ) ) {
    return sprintf( '<a href="%s">%s</a>', get_permalink( $value->ID ), $value->post_title );
  }
  else {
    if ( $post = get_post( (int)$value ) ) {
      return sprintf( '<a href="%s">%s</a>', get_permalink( $post->ID ), $post->post_title );
    }
    else {
      return $value;
    }
  }
}


function vc_acf_field_picker_render_field_type_page_link_filter( $value, $field_object, $post_id ) {
  if ( ! $value ) {
    return $value;
  }

  if ( is_array( $value ) ) {
    $items = array_map(function( $item ) {
      return sprintf( '<a href="%s">%s</a>', $item, $item );
    }, $value );

    return sprintf( '<ul><li>%s</li></ul>', implode( '</li><li>', $items ) );
  }
  else {
    return sprintf( '<a href="%s" target="_blank">%s</a>', $value, $value );
  }
}


function vc_acf_field_picker_render_field_type_email_filter( $value, $field_object, $post_id ) {
  return $value ? sprintf( '<a href="mailto:%s">%s</a>', $value, $value ) : $value;
}


function vc_acf_field_picker_render_field_type_url_filter( $value, $field_object, $post_id ) {
  return $value ? sprintf( '<a href="%s">%s</a>', $value, $value ) : $value;
}


function vc_acf_field_picker_render_field_type_taxonomy_filter( $value, $field_object, $post_id ) {
  if ( ! $value ) {
    return $value;
  }

  $taxonomy = $field_object[ 'taxonomy' ];

  if ( is_array( $value ) ) {
    $items = array_map(function( $item ) use ( $taxonomy ) {
      return vc_acf_field_picker_render_field_type_filter_get_term_link( $item, $taxonomy );
    }, $value );

    return sprintf( '<ul><li>%s</li></ul>', implode( '</li><li>', $items ) );
  }
  else {
    return vc_acf_field_picker_render_field_type_filter_get_term_link( $value, $taxonomy );
  }
}


function vc_acf_field_picker_render_field_type_filter_get_term_link( $value, $taxonomy = null ) {
  if ( is_object( $value ) ) {
    return sprintf( '<a href="%s">%s</a>', get_term_link( $value->term_id, $value->taxonomy ), $value->name );
  }
  else {
    if ( $term = get_term_by( 'id', (int)$value, $taxonomy ) ) {
      return sprintf( '<a href="%s">%s</a>', get_term_link( $term->term_id, $term->taxonomy ), $term->name );
    }
    else {
      return $value;
    }
  }
}


function vc_acf_field_picker_render_field_type_user_filter( $value, $field_object, $post_id ) {
  if ( ! $value ) {
    return $value;
  }

  if ( is_array( $value ) && ! isset( $value[ 'ID' ] ) ) {
    $items = array_map(function( $item ) {
      return vc_acf_field_picker_render_field_type_filter_get_user_link( $item );
    }, $value );

    return sprintf( '<ul><li>%s</li></ul>', implode( '</li><li>', $items ) );
  }
  else {
    return vc_acf_field_picker_render_field_type_filter_get_user_link( (object)$value );
  }
}


function vc_acf_field_picker_render_field_type_filter_get_user_link( $value ) {
  if ( ! is_object( $value ) && ( ! $value = get_user_by( 'id', (int)$value ) ) ) {
    return '';
  }

  $url = get_author_posts_url( $value->ID );
  $name = $value->user_firstname . ' ' . $value->user_lastname;
  $name = $name ? $name : $value->display_name;
  $name = $name ? $name : $value->user_nicename;
  $name = $name ? $name : $value->nickname;
  return $url && $name ? sprintf( '<a href="%s">%s</a>', $url, $name ) : '';
}


function vc_acf_field_picker_render_field_type_google_map_filter( $value, $field_object, $post_id ) {

  $value = sprintf(
    '<div class="vc-acf-field-picker-google-map acf-map"><div class="marker" data-lat="%s" data-lng="%s"></div></div>',
    $value[ 'lat' ],
    $value[ 'lng' ]
  );

  return $value;
}


function vc_acf_field_picker_enqueue_google_map_assets() {

  $google_api_key = function_exists( 'acf_get_setting' ) ? acf_get_setting( 'google_api_key' ) : '';
  $google_api_key = apply_filters( 'vc_acf_field_picker_google_api_key', $google_api_key );
  $google_maps_api_script_url = 'https://maps.googleapis.com/maps/api/js?key=' . $google_api_key;

  // Google Maps API
  $should_load_google_maps_api_script = apply_filters( 'vc_acf_field_picker_enqueue_google_maps', get_field( 'wp_acf_vc_bridge_enqueue_google_maps', 'option' ) );
  if ( $should_load_google_maps_api_script === 'yes' ) {
    // <script src="https://maps.googleapis.com/maps/api/js?key=YOUR_API_KEY"></script>
    // if ( acf_get_setting( 'enqueue_google_maps' ) ) {
      wp_enqueue_script( 'vc-acf-field-picker-google-map-api', $google_maps_api_script_url, array(), null, true );
    // }
  }

  // ACF Google Map field
  if ( apply_filters( 'vc_acf_field_picker_enqueue_acf_google_maps', get_field( 'wp_acf_vc_bridge_enqueue_acf_google_maps', 'option' ) === 'yes' ? true : false ) ) {
    wp_register_script( 'vc-acf-field-picker-google-map', WP_ACF_VC_BRIDGE_PLUGIN_URL . 'assets/js/vc-acf-field-picker-google-map.js', array( 'jquery' ), WpAcfVcBridge::VERSION, true );
    wp_enqueue_script( 'vc-acf-field-picker-google-map' );

    wp_register_style( 'vc-acf-field-picker-google-map', WP_ACF_VC_BRIDGE_PLUGIN_URL . 'assets/css/vc-acf-field-picker-google-map.css', array(), WpAcfVcBridge::VERSION );
    wp_enqueue_style( 'vc-acf-field-picker-google-map' );
    wp_localize_script(
      'vc-acf-field-picker-google-map',
      'vcAcfFieldPickerGoogleMap',
      array(
        'shouldLoadGoogleMapsApiScript' => $should_load_google_maps_api_script,
        'googleApiScriptUrl' => $google_maps_api_script_url,
      )
    );
  }
}


function vc_acf_field_picker_render_field_type_color_picker_filter( $value, $field_object, $post_id ) {
  $value = $value ? $value : 'transparent';
  return sprintf( '<div class="vc-acf-field-picker-color-picker" title="%s" style="background:%s;"></div>', $value, $value );
}


function vc_acf_field_picker_render_field_type_group_filter( $value, $field_object, $post_id ) {
  // Value must be an array. Empty or formatted repeater should not be processed by this filter
  if ( ! is_array( $value ) ) {
    return $value;
  }

  // Check if valid  
  if ( ! isset( $field_object[ 'sub_fields' ] ) ) {
    return $value;
  }

  $sub_fields = array();
  foreach ( $field_object[ 'sub_fields' ] as $sub_field_object ) {
    $sub_fields[ $sub_field_object[ 'name' ] ] = $sub_field_object;
  }

  /*
    Since 1.3.8
    Prepare templates for rows
  */
  $group_template = isset( $field_object[ 'group_template' ] ) && $field_object[ 'group_template' ] ? $field_object[ 'group_template' ] : false;
  $group_template_post = $group_template ? get_post( $group_template ) : false;
  $group_template_post = is_a( $group_template_post, 'WP_Post' ) ? $group_template_post : false;

  // Process group template if specified
  if ( $group_template_post ) {

    $value = sprintf(
      '<div class="repeater-row-template repeater-row-template-%s">%s</div>',
      $group_template,
      acf_field_templates()->render_template(
        $group_template,
        $group_template_post->post_content,
        $value,
        $sub_fields,
        $post_id
      )
    );

    $value = sprintf( '<div class="vc-acf-field-picker-group">%s</div>', $value );
    
    $template_css = $group_template_post ? VcVcSnippet::getCss( $group_template_post->ID ) : '';
    $value .= $template_css ? $template_css : '';

  }  

  return $value;
}


function vc_acf_field_picker_render_field_type_repeater_filter( $value, $field_object, $post_id ) {
  // Value must be an array. Empty or formatted repeater should not be processed by this filter
  if ( ! is_array( $value ) ) {
    return $value;
  }

  // Check if valid  
  if ( ! isset( $field_object[ 'sub_fields' ] ) ) {
    return $value;
  }

  $sub_fields = array();
  foreach ( $field_object[ 'sub_fields' ] as $sub_field_object ) {
    $sub_fields[ $sub_field_object[ 'name' ] ] = $sub_field_object;
  }

  /*
    Since 1.3.8
    Prepare templates for rows
  */
  $row_template = isset( $field_object[ 'row_template' ] ) && $field_object[ 'row_template' ] ? $field_object[ 'row_template' ] : false;
  $row_template_post = $row_template ? get_post( $row_template ) : false;
  $row_template_post = is_a( $row_template_post, 'WP_Post' ) ? $row_template_post : false;
  $template_css = $row_template_post ? VcVcSnippet::getCss( $row_template_post->ID ) : '';

  $rows = array();
  foreach ( $value as $key => $item ) {
    if ( ! is_array( $item ) ) {
      continue;
    }

    $row_items = array();

    // Process row template if specified
    if ( $row_template_post ) {
      $row_items []= sprintf(
        '<div class="repeater-row-template repeater-row-template-%s">%s</div>',
        $row_template,
        acf_field_templates()->render_template(
          $row_template,
          $row_template_post->post_content,
          $item,
          $sub_fields,
          $post_id
        ) 
      );
    }
    // Otherwise, process Row items in a standard way
    else {
  
      foreach ( $item as $field_name => $sub_field_value ) {
        $sub_field_object = $sub_fields[ $field_name ];
        $sub_field_object[ 'value' ] = $sub_field_value;
        $sub_field_value = apply_filters( sprintf( 'vc_acf_field_picker_render_field_type_%s', $sub_field_object[ 'type' ] ), $sub_field_value, $sub_field_object, $post_id );
        $sub_field_value = apply_filters( sprintf( 'vc_acf_field_picker_render_field_name_%s', $sub_field_object[ 'name' ] ), $sub_field_value, $sub_field_object, $post_id );
        $sub_field_value = apply_filters( sprintf( 'vc_acf_field_picker_render_field_key_%s', $sub_field_object[ 'key' ] ), $sub_field_value, $sub_field_object, $post_id );

        /*
          Field prefix/suffix
          Since 1.5.0
        */
        $prepend = is_array( $sub_field_object ) && isset( $sub_field_object[ 'prepend' ] ) ? $sub_field_object[ 'prepend' ] : '';
        $prepend = $prepend ? sprintf( '<span class="vc-acf-field-picker-field-prepend">%s</span>', $prepend ) : '';
        $sub_field_value = $prepend . $sub_field_value;
        $append = is_array( $sub_field_object ) && isset( $sub_field_object[ 'append' ] ) ? $sub_field_object[ 'append' ] : '';
        $append = $append ? sprintf( '<span class="vc-acf-field-picker-field-append">%s</span>', $append ) : '';
        $sub_field_value = $sub_field_value . $append;

        $row_items []= sprintf( '<div class="vc-acf-field-picker-repeater-column">%s</div>', $sub_field_value );
      }

    }

    $rows []= sprintf( '<div class="vc-acf-field-picker-repeater-row">%s</div>', implode( '', $row_items ) );
  
  }

  $value = sprintf( '<div class="vc-acf-field-picker-repeater">%s</div>', implode( '', $rows ) );

  /*
    Since 1.3.8
    Append template CSS
  */
  $value .= $template_css ? $template_css : '';

  return $value;
}


function vc_acf_field_picker_render_field_type_flexible_content_filter( $value, $field_object, $post_id ) {
  // Value must be an array. Empty or formatted flexible content should not be processed by this filter
  if ( ! is_array( $value ) ) {
    return $value;
  }

  // Check if valid  
  if ( ! isset( $field_object[ 'layouts' ] ) ) {
    return $value;
  }
  
  $layouts = array();
  $layout_name_key_map = array();
  foreach ( $field_object[ 'layouts' ] as $layout_object ) {
    $layout_name = $layout_object[ 'name' ];
    $layouts[ $layout_name ] = $layout_object;

    if ( ! is_array( $layout_object[ 'sub_fields' ] ) ) {
      continue;
    }

    $layout_sub_fields = array();
    foreach ( $layout_object[ 'sub_fields' ] as $layout_sub_field_object ) {
      $layout_sub_fields[ $layout_sub_field_object[ 'name' ] ] = $layout_sub_field_object;
    }
    
    $layouts[ $layout_name ][ 'layout_sub_fields' ] = $layout_sub_fields;

    $layout_name_key_map[ $layout_name ] = $layout_object[ 'key' ];
  }

  /*
    Since 1.3.8
    Prepare templates for layouts
  */
  $layout_templates = isset( $field_object[ 'layout_templates' ] ) && is_array( $field_object[ 'layout_templates' ] ) ? $field_object[ 'layout_templates' ] : array();
  $layout_templates_css = array();

  $rows = array();
  foreach ( $value as $key => $item ) {
    if ( ! is_array( $item ) ) {
      continue;
    }

    $layout_items = array();
    $layout_name = $item[ 'acf_fc_layout' ];
    $layout_object = $layouts[ $layout_name ];
    unset( $item[ 'acf_fc_layout' ] );

    /*
      Since 1.3.8
      Process layout templates
    */
    $layout_key = isset( $layout_name_key_map[ $layout_name ] ) ? $layout_name_key_map[ $layout_name ] : false;
    $layout_template = $layout_key && isset( $layout_templates[ $layout_key ] ) ? $layout_templates[ $layout_key ] : false;
    $layout_template_post = $layout_template ? get_post( $layout_template ) : false;
    $layout_template_post = is_a( $layout_template_post, 'WP_Post' ) ? $layout_template_post : false;
    if ( ! isset( $layout_templates_css[ $layout_key ] ) ) {
      $layout_templates_css[ $layout_key ] = $layout_template_post ? VcVcSnippet::getCss( $layout_template_post->ID ) : '';
    }

    // Process layout template if specified
    if ( $layout_template_post ) {
      $layout_items []= sprintf(
        '<div class="fc-layout-template-%s">%s</div>', 
        $layout_template, 
        acf_field_templates()->render_template(
          $layout_template,
          $layout_template_post->post_content,
          $item,
          $layout_object[ 'layout_sub_fields' ],
          $post_id
        ) 
      );
    }
    // Otherwise, process FC items in a standard way
    else {

      foreach ( $item as $field_name => $sub_field_value ) {

        $sub_field_object = $layout_object[ 'layout_sub_fields' ][ $field_name ];
        $sub_field_object[ 'value' ] = $sub_field_value;

        $sub_field_value = apply_filters( sprintf( 'vc_acf_field_picker_render_field_type_%s', $sub_field_object[ 'type' ] ), $sub_field_value, $sub_field_object, $post_id );
        $sub_field_value = apply_filters( sprintf( 'vc_acf_field_picker_render_field_name_%s', $sub_field_object[ 'name' ] ), $sub_field_value, $sub_field_object, $post_id );
        $sub_field_value = apply_filters( sprintf( 'vc_acf_field_picker_render_field_key_%s', $sub_field_object[ 'key' ] ), $sub_field_value, $sub_field_object, $post_id );

        /*
          Field prefix/suffix
          Since 1.5.0
        */
        $prepend = is_array( $sub_field_object ) && isset( $sub_field_object[ 'prepend' ] ) ? $sub_field_object[ 'prepend' ] : '';
        $prepend = $prepend ? sprintf( '<span class="vc-acf-field-picker-field-prepend">%s</span>', $prepend ) : '';
        $sub_field_value = $prepend . $sub_field_value;
        $append = is_array( $sub_field_object ) && isset( $sub_field_object[ 'append' ] ) ? $sub_field_object[ 'append' ] : '';
        $append = $append ? sprintf( '<span class="vc-acf-field-picker-field-append">%s</span>', $append ) : '';
        $sub_field_value = $sub_field_value . $append;
      
        $layout_items []= sprintf( '<div class="vc-acf-field-picker-fc-item">%s</div>', $sub_field_value );
      
      }

    }

    $rows []= sprintf( '<div class="vc-acf-field-picker-fc-layout fc-layout-name-%s fc-layout-key-%s">%s</div>', $layout_name, $layout_key, implode( '', $layout_items ) );
  
  }
  $value = sprintf( '<div class="vc-acf-field-picker-fc">%s</div>', implode( '', $rows ) );

  /*
    Since 1.3.8
    Add layout CSS
  */
  if ( count( $layout_templates_css ) ) {
    $value .= implode( '', $layout_templates_css );
  }

  return $value;
}


function vc_acf_field_picker_render_field_type_table_filter( $value, $field_object, $post_id ) {

  if ( $value && is_array( $value ) ) {
    $header = '';
    if ( $value[ 'header' ] ) {
      $header = sprintf( '<thead><tr>%s</tr></thead>', implode( '', array_map( function( $item ) {
        return sprintf( '<th>%s</th>', $item[ 'c' ] );
      }, $value[ 'header' ] ) ) );
    }

    $body = sprintf( '<tbody>%s</tbody>', implode( '', array_map( function( $tr ) {
      $row = '<tr>';
      foreach ( $tr as $td ) {
        $row .= sprintf( '<td>%s</td>', $td[ 'c' ] );
      }
      $row .= '</tr>';

      return $row;
    }, $value[ 'body' ] ) ) );

    $value = sprintf( '<table class="vc-acf-field-picker-table">%s %s</table>', $header, $body );

  }

  return $value;
}


