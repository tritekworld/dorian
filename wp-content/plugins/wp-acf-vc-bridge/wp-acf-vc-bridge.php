<?php
/*
Plugin Name: WP ACF-VC Bridge
Plugin URI: http://wpacfvcbridge.com
Description: Integrates Advanced Custom Fields and Visual Composer WordPress Plugins
Version: 1.5.13
Author: Pavlo Reva
Author URI: https://codecanyon.net/user/pavelreva/portfolio/?ref=pavelreva
Copyright: Pavlo Reva
Text Domain: wp-acf-vc-bridge
Domain Path: /lang
*/

// TODO: Field Picker => Add VC param When set to Custom context, allow to choose if it should use global post (loop) or main queried object.

if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'WpAcfVcBridge' ) ) :


define( 'WP_ACF_VC_BRIDGE_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'WP_ACF_VC_BRIDGE_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );


class WpAcfVcBridge {
  
  const VERSION = '1.5.10';

  protected static $instance;
  
  protected $is_own_acf_included;


  /**
   *  This function will setup plugin
   */
  private function __construct() {
    
    // Set text domain
    load_plugin_textdomain( 'wp-acf-vc-bridge', false, plugin_basename( dirname( __FILE__ ) ) . '/lang' ); 

    $this->is_vc_active = $this->isVisualComposerPluginActive();
    $this->is_acf_free_active = $this->isAcfFreePluginActive();
    $this->is_acf_active = $this->isAcfPluginActive();

    // Include ACF PRO shipped with this plugin
    if ( ! $this->is_acf_free_active && ! $this->is_acf_active ) {
      $this->includeAcfPro();
      $this->is_acf_active = true;
      $this->is_own_acf_included = true;
    }
    else {
      $this->is_own_acf_included = false;
    }

    $this->includeComponents();

    if ( is_admin() && ( ! $this->is_vc_active || ! $this->is_acf_active || $this->is_acf_free_active ) ) {
      add_action( 'admin_notices', array( $this, 'adminNotices' ) );
    }

    if ( is_admin() ) {
      // Since 1.4.16
      add_action( 'init', array( $this, 'integrate_vc_grid_params' ), 9999 );
    }
  }


  /**
   *  Includes all the plugin's components
   */
  public function includeComponents() {
    if ( $this->is_acf_active ) {
      $this->configure();
    }

    if ( $this->is_vc_active ) {
      include_once( WP_ACF_VC_BRIDGE_PLUGIN_PATH . 'includes/vc-snippets.php' );
      include_once( WP_ACF_VC_BRIDGE_PLUGIN_PATH . 'includes/vc-vc-snippet.php' );
      include_once( WP_ACF_VC_BRIDGE_PLUGIN_PATH . 'includes/vc-snippet-widget.php' );
    }
    
    if ( $this->is_vc_active && $this->is_acf_active ) {
      include_once( WP_ACF_VC_BRIDGE_PLUGIN_PATH . 'includes/acf-field-visual-composer.php' );
      include_once( WP_ACF_VC_BRIDGE_PLUGIN_PATH . 'includes/vc-acf-field-picker.php' );
      include_once( WP_ACF_VC_BRIDGE_PLUGIN_PATH . 'includes/acf-field-templates.php' );
      include_once( WP_ACF_VC_BRIDGE_PLUGIN_PATH . 'includes/vc-acf-template-field-picker.php' );
    }

    if ( $this->is_acf_active ) {
      add_action( 'init', array( $this, 'addCustomOptionsPages' ) );
    }

    /*
      Since 1.4.4
      Support for proper rendering of Ultimate VC Addons shortcodes in Visual Composer field
    */
    if ( $this->isUltimateVcAddonsPluginActive() ) {
      include_once( WP_ACF_VC_BRIDGE_PLUGIN_PATH . 'includes/integrations/ultimate_vc_addons.php' );
    }

  }


  /**
   *  Create or retrieve instance
   */
  public static function instance() {
    return self::$instance ? self::$instance : self::$instance = new self();
  }


  /**
   *  Configures ACF plugin
   */
  public function configureAcf() {
    // Load fields
    include_once( WP_ACF_VC_BRIDGE_PLUGIN_PATH . 'config/acf.php' );

    if ( $this->is_own_acf_included ) {
      // Disable available updates notification because this plugin is shipped within this plugin anyways
      acf_update_setting( 'show_updates', false );
    }

    // Google Maps settings
    $google_api_key = get_field( 'wp_acf_vc_bridge_google_api_key', 'option' );
    if ( $google_api_key ) {
      acf_update_setting( 'google_api_key', $google_api_key );
    }
    $enqueue_google_maps = get_field( 'wp_acf_vc_bridge_enqueue_google_maps', 'option' );
    if ( 'no' == $enqueue_google_maps ) {
      acf_update_setting( 'enqueue_google_maps', false );
    }

    // Shortcode settings
    $force_shortcodes_processing = get_field( 'wp_acf_vc_bridge_force_shortcodes_processing', 'option' );
    if ( $force_shortcodes_processing && is_array( $force_shortcodes_processing ) ) {
      if ( in_array( 'post', $force_shortcodes_processing ) ) {
        add_filter( 'the_content','do_shortcode' );
      }
      if ( in_array( 'widget', $force_shortcodes_processing ) ) {
        add_filter( 'widget_text', function( $widget_text = '', $instance = 0, $this_obj = null ) {
          return do_shortcode( $widget_text );
        } );
      }
      if ( in_array( 'category_description', $force_shortcodes_processing ) ) {
        add_filter( 'category_description', function( $description = '', $category = 0 ) {
          return do_shortcode( $description );
        } );
      }
    }

  }

  /**
   *  Configures plugin
   */
  private function configure() {
    
    // Create Plugin Options page
    acf_add_options_page( array(
      'page_title'  => 'WP ACF-VC Bridge Settings',
      'menu_title'  => 'WP ACF-VC Bridge',
      'menu_slug'   => 'wp-acf-vc-bridge-settings',
      'capability'  => 'edit_posts',
    ) );

    add_action( 'acf/init', array( $this, 'configureAcf' ), 999 );
    // add_action( 'acf/init', array( $this, 'configureAcf' ) );

  }


  /**
   *  Registers custom ACF Options pages
   */
  public function addCustomOptionsPages() {
    $options_pages = get_field( 'wpacfvcbridge_options_pages', 'option' );

    // Bail early if no options pages added or invalid
    if ( ! $options_pages || ! is_array( $options_pages ) || empty( $options_pages ) ) {
      return false;
    }

    foreach ( $options_pages as $options_page ) {

      // Page title is required
      $options_page[ 'page_title' ] = isset( $options_page[ 'page_title' ] ) ? trim( $options_page[ 'page_title' ] ) : '';
      if ( ! $options_page[ 'page_title' ] ) {
        continue;
      }

      // Menu title is optional, using page title if not specified
      $options_page[ 'menu_title' ] = isset( $options_page[ 'menu_title' ] ) ? trim( $options_page[ 'menu_title' ] ) : '';
      $options_page[ 'menu_title' ] = $options_page[ 'menu_title' ] ? $options_page[ 'menu_title' ] : $options_page[ 'page_title' ];

      // Menu slug is optional, uses menu title if not specified and sanitizes it
      $options_page[ 'menu_slug' ] = isset( $options_page[ 'menu_slug' ] ) ? trim( $options_page[ 'menu_slug' ] ) : '';
      $options_page[ 'menu_slug' ] = $options_page[ 'menu_slug' ] ? $options_page[ 'menu_slug' ] : $options_page[ 'menu_title' ];
      $options_page[ 'menu_slug' ] = sanitize_title_with_dashes( $options_page[ 'menu_slug' ] );

      // Capatibility is optional, uses edit_posts by default
      $options_page[ 'capability' ] = isset( $options_page[ 'capability' ] ) ? trim( $options_page[ 'capability' ] ) : '';
      $options_page[ 'capability' ] = $options_page[ 'capability' ] ? $options_page[ 'capability' ] : 'edit_posts';

      // Prepare and Sanitize subpages
      $subpages = array();
      $options_page[ 'subpages' ] = isset( $options_page[ 'subpages' ] ) && is_array( $options_page[ 'subpages' ] ) ? $options_page[ 'subpages' ] : array();
      foreach ( $options_page[ 'subpages' ] as $subpage ) {

        // Subpage title is required
        $subpage[ 'page_title' ] = isset( $subpage[ 'page_title' ] ) ? trim( $subpage[ 'page_title' ] ) : '';
        if ( ! $subpage[ 'page_title' ] ) {
          continue;
        }

        // Menu title is optional, using page title if not specified
        $subpage[ 'menu_title' ] = isset( $subpage[ 'menu_title' ] ) ? trim( $subpage[ 'menu_title' ] ) : '';
        $subpage[ 'menu_title' ] = $subpage[ 'menu_title' ] ? $subpage[ 'menu_title' ] : $subpage[ 'page_title' ];

        $subpages []= array(
          'page_title' => $subpage[ 'page_title' ],
          'menu_title' => $subpage[ 'menu_title' ],
          'parent_slug' => $options_page[ 'menu_slug' ],
        );
      }
      unset( $options_page[ 'subpages' ] );

      // Root page will redirect to the first sub-page if exists
      // $options_page[ 'redirect' ] = ! empty( $subpages );
      $options_page[ 'redirect' ] = true;

      // Create Options Page
      acf_add_options_page( $options_page );

      // Add Subpages
      foreach ( $subpages as $subpage ) {
        acf_add_options_sub_page( $subpage );
      }
      
    }

    // exit;

  }


  /**
   *  Includes ACF PRO
   */
  private function includeAcfPro() {

    // Customize ACF path
    add_filter( 'acf/settings/path', function ( $path ) {
      return WP_ACF_VC_BRIDGE_PLUGIN_PATH . 'vendor/advanced-custom-fields-pro/';
    } );

    // Customize ACF dir
    add_filter( 'acf/settings/dir', function ( $dir ) {
      return WP_ACF_VC_BRIDGE_PLUGIN_URL . 'vendor/advanced-custom-fields-pro/';
    } );

    // Include ACF
    include_once( WP_ACF_VC_BRIDGE_PLUGIN_PATH . 'vendor/advanced-custom-fields-pro/acf.php' );

    // Update variable
    $this->is_acf_active = class_exists( 'acf' );
  }


  /**
   *  Detect if plugin is active by regular expression
   */
  public function isPluginActive( $re ) {
    $active_plugins = (array)get_option( 'active_plugins', array() );
    $matches = preg_grep( $re, $active_plugins );
    return count( $matches ) > 0;
  }


  /**
   *  Detect if plugin is active by regular expression
   */
  public function isVisualComposerPluginActive() {
    return class_exists( 'Vc_Manager' ) || $this->isPluginActive( '#^js_composer(.*?)/js_composer\.php$#i' );
  }


  /**
   *  Detect if plugin is active by regular expression
   */
  public function isAcfPluginActive() {
    return class_exists( 'acf_pro' ) || $this->isPluginActive( '#^advanced\-custom\-fields\-pro(.*?)/acf\.php$#i' );
  }


  /**
   *  Detect if plugin is active by regular expression
   */
  public function isAcfFreePluginActive() {
    return ( class_exists( 'acf' ) && ! class_exists( 'acf_pro' ) ) || $this->isPluginActive( '#^advanced\-custom\-fields/acf\.php$#i' );
  }


  /**
   *  Detect if plugin is active by regular expression
   */
  public function isUltimateVcAddonsPluginActive() {
    return class_exists( 'Ultimate_VC_Addons' ) || $this->isPluginActive( '#^Ultimate_VC_Addons/Ultimate_VC_Addons\.php$#i' );
  }


  /**
   *  Retrieve post type on edit/add new pages in admin before current screen has been initialized
   */
  public function getAdminCreateEditPostType() {
    if ( ! is_admin() ) {
      return false;
    }

    global $pagenow;
    
    // Editing post 
    // E.g. /wp-admin/post.php?post=285&action=edit
    if ( ( 'post.php' == $pagenow ) && isset( $_GET[ 'post' ] ) && isset( $_GET[ 'action' ] ) && ( 'edit' === $_GET[ 'action' ] ) ) {
      if ( $post_type = get_post_type( intval( $_GET[ 'post' ] ) ) ) {
        return $post_type;
      }
    }

    // Adding new post
    // E.g. /wp-admin/post-new.php
    // or: /wp-admin/post-new.php?post_type=page
    if ( 'post-new.php' == $pagenow ) {
      if ( ! isset( $_GET[ 'post_type' ] ) ) {
        return 'post';
      }
      elseif ( isset( $_GET[ 'post_type' ] ) ) {
        return post_type_exists( $_GET[ 'post_type' ] ) ? $_GET[ 'post_type' ] : false;
      }
    }

    return false;  
  }

  /**
   *  Is VC Backeditor enabled
   */
  public function willInitializeBackendEditor() {
    if ( ! $post_type = $this->getAdminCreateEditPostType() ) {
      return false;
    }

    return function_exists( 'vc_user_access' ) && vc_user_access()->part( 'backend_editor' )->can()->get() && vc_check_post_type( $post_type );  
  }

  /**
   *  Checks if editing grid item in admin
   */
  public function isEditingGridItem() {
    if ( ! $post_type = $this->getAdminCreateEditPostType() ) {
      return false;
    }

    return in_array( $post_type, array( 'vc_grid_item' ) );  
  }


  /**
   *  Displays notices in admin
   */
  public function adminNotices() {
    if ( ! $this->is_vc_active ) {
      self::displayAdminNotice( 
        sprintf( '<a href="https://codecanyon.net/item/visual-composer-page-builder-for-wordpress/242431?ref=pavelreva" target="_blank">%s</a> %s', __( 'Visual Composer (WPBakery Page Builder)', 'wp-acf-vc-bridge' ), __( 'is required for the WP ACF-VC Bridge!', 'wp-acf-vc-bridge' ) ), 
        'error', 
        true
      );
    }

    if ( ! $this->is_acf_active && ! $this->is_acf_free_active ) {
      self::displayAdminNotice(
        sprintf( '<a href="https://wordpress.org/plugins/advanced-custom-fields/" target="_blank">%s</a> or <a href="https://www.advancedcustomfields.com/pro/" target="_blank">%s</a> is essential for utilizing the most out of the WP ACF-VC Bridge!', __( 'ACF Free', 'wp-acf-vc-bridge' ), __( 'ACF PRO', 'wp-acf-vc-bridge' ) ),
        'warning',
        true
      );
    }

    if ( $this->is_acf_free_active ) {
      self::displayAdminNotice( __( 'Please disable your ACF free plugin version as it is no longer needed', 'wp-acf-vc-bridge' ), 'warning', true );
    }
  }


  /**
   *  Formats and outputs notice HTML
   */
  public static function displayAdminNotice( $message, $type = 'success', $is_dismissible = true ) {
    printf(
      '<div class="notice notice-%s %s"><p>%s</p></div>',
      $type,
      $is_dismissible ? 'is-dismissible' : '',
      $message
    );
  }


  /**
  *  integrate_vc_grid_params()
  *
  *  Appends custom settings to VC grids
  *
  *  @since 1.4.16
  *
  *  @param N/A
  *
  *  @return N/A
  */
  public function integrate_vc_grid_params() {

    // Prepare param attributes
    $attributes = array(
      array(
        'type' => 'checkbox',
        'param_name' => 'not_exclude_cur_post',
        'heading' => __( 'Do not exclude current post', 'wp-acf-vc-bridge' ),
        'description' => __( 'By default, current post or last post on archive pages is excluded. Check this checkbox to prevent this bahavior.', 'wp-acf-vc-bridge' ),
        'value' => array(
          __( 'Do not exclude', 'wp-acf-vc-bridge' ) => 'dont_exclude',
        ),
      ),
    );

    $vc_grids = apply_filters( 'acf_field_visual_composer/vc_grids', array( 'vc_basic_grid', 'vc_masonry_grid' ) );

    // Make sure the required function exists
    if ( method_exists( 'WPBMap', 'getAllShortCodes' ) ) {

      // Append user content restriction param to all registered shortcodes in VC
      $all_shortcodes = WPBMap::getAllShortCodes();
      foreach ( $vc_grids as $vc_grid_tag ) {

        // Make sure the shortcode exists and has params
        if ( ! isset( $all_shortcodes[ $vc_grid_tag ][ 'params' ] ) ) {
          continue;
        }

        // Append query override options
        vc_add_params( $vc_grid_tag, $attributes );
      }

    }

  }


  /**
   *  Configure on plugin activation
   */
  public static function onPluginActivation() {
    if ( class_exists( 'WpAcfVcBridgeVcSnippets' ) ) {
      WpAcfVcBridgeVcSnippets::onPluginActivation();
    }
  }


}


WpAcfVcBridge::instance();

// Plugin setup hooks
register_activation_hook ( __FILE__, array( 'WpAcfVcBridge', 'onPluginActivation' ) );

endif;
?>