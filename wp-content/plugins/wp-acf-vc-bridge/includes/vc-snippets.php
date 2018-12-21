<?php

if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'WpAcfVcBridgeVcSnippets' ) ) :

class WpAcfVcBridgeVcSnippets {

  const POST_TYPE = 'vc_snippet';
  const CATEGORY = 'vc_snippet_cat';
  
  protected static $instance;
  protected static $posts;

  /**
   *  Setup VC Snippet component
   */
  private function __construct() {

    if ( is_admin() && isset( $_GET[ 'vc_snippet_embedded' ] ) ) {
      add_action( 'admin_init', array( $this, 'adminEditPostStyles' ) );
      add_action( 'admin_enqueue_scripts', array( $this, 'adminDisableAutoSave' ) );
      // add_action( 'acf/input/admin_enqueue_scripts', array( $this, 'adminDisableAcf' ) ); // Deprecated since 1.3.8
    }

    if ( is_admin() ) {
      add_action( 'admin_enqueue_scripts', array( $this, 'enqueueScripts' ) );
      add_action( 'wp_ajax_vc_snippet_get_categories', array( $this, 'ajaxGetCategories' ) );
      add_action( 'wp_ajax_vc_snippet_get_posts', array( $this, 'ajaxGetPosts' ) );
      add_action( 'add_meta_boxes', array( $this, 'removeMetaboxes' ), 100 );
    }

    if ( is_admin() && ! isset( $_GET[ 'vc_snippet_embedded' ] ) ) {
      // Integrate with Templates Panel
      add_filter( 'vc_get_all_templates', array( $this, 'appendSnippetsToTemplates' ) );
      add_filter( 'vc_templates_render_category', array( $this, 'renderTemplatesCategory' ) );
      add_filter( 'vc_templates_render_template', array( $this, 'renderTemplateBarItem' ), 10, 2 );
      add_filter( 'vc_templates_render_backend_template', array( $this, 'renderTemplate' ), 10, 2 );
      add_filter( 'vc_templates_render_backend_template_preview', array( $this, 'renderTemplate' ), 10, 2 );
    }

    // Integrate with Templates Panel
    add_filter( 'vc_templates_render_frontend_template', array( $this, 'renderTemplateFrontEnd' ), 10, 2 );

    add_action( 'init', array( $this, 'init' ), 0 );
  }


  /**
   *  Create or retrieve instance
   */
  public static function instance() {
    return self::$instance ? self::$instance : self::$instance = new self();
  }


  /**
   *  remove_metabox
   *
   *  Removes Yoast SEO metabox from edit custom page template edit screen
   *  Yoast SEO expects that media scripts will be loaded, but they are not on edit screen,
   *  which causes errors and breaks whole frontend functionality
   *
   *  @type  function
   *  @date  31/01/18
   *  @since 1.5.2
   *
   *  @param N/A
   *
   *  @return  N/A
   */

  public function removeMetaboxes() {
    remove_meta_box( 'wpseo_meta', self::POST_TYPE, 'normal' );
  }


  /**
   *  This filter is called by Visual Composer to include third-party templates
   *
   * @param $data array
   */
  public function appendSnippetsToTemplates( $data ) {
    $posts = self::getPosts();

    $templates = array();
    foreach ( $posts as $post ) {
      $templates []= array(
        'unique_id' => $post->ID,
        'name' => $post->post_title,
        'type' => self::POST_TYPE,
        'image' => '',
        'custom_class' => '',
      );
    }

    $new_data = array(
      'category' => self::POST_TYPE,
      'category_name' => __( 'VC Snippets', 'wp-acf-vc-bridge' ),
      'category_description' => __( 'Append VC Snippets to current layout', 'wp-acf-vc-bridge' ),
      'category_weight' => 12,
      'templates' => $templates,
    );

    $data []= $new_data;
    return $data;
  }


  /**
   *  This filter is called by Visual Composer to render templates category
   *
   * @param $category array
   */
  public function renderTemplatesCategory( $category ) {
    if ( self::POST_TYPE === $category[ 'category' ] ) {
      $vc_templates_panel_editor = new Vc_Templates_Panel_Editor();
      $category['output'] = '<div class="vc_col-md-12">';
      if ( isset( $category[ 'category_name' ] ) ) {
        $category[ 'output' ] .= '<h3>' . esc_html( $category[ 'category_name' ] ) . '</h3>';
      }
      if ( isset( $category['category_description'] ) ) {
        $category[ 'output' ] .= '<p class="vc_description">' . esc_html( $category[ 'category_description' ] ) . '</p>';
      }
      $category[ 'output' ] .= '</div>';
      $category[ 'output' ] .= '
      <div class="vc_column vc_col-sm-12">
        <div class="vc_ui-template-list vc_templates-list-'.self::POST_TYPE.' vc_ui-list-bar" data-vc-action="collapseAll">';
      if ( ! empty( $category[ 'templates' ] ) ) {
        foreach ( $category[ 'templates' ] as $template ) {
          $category[ 'output' ] .= $vc_templates_panel_editor->renderTemplateListItem( $template );
        }
      }
      $category[ 'output' ] .= '
      </div>
    </div>';

    }

    return $category;
  }


  /**
   *  This filter is called by Visual Composer to render template item in the list
   *
   * @param $template_name string
   * @param $template_data array
   */
  public function renderTemplateBarItem( $template_name, $template_data ) {
    if ( self::POST_TYPE === $template_data['type'] ) {
      $vc_templates_panel_editor = new Vc_Templates_Panel_Editor();
      return $vc_templates_panel_editor->renderTemplateWindowDefaultTemplates( $template_name, $template_data );
    }
   
    return $template_name;
  }


  /**
   *  This filter is called by Visual Composer to render template for preview or adding to a page
   *
   * @param $template_id int
   * @param $template_data array
   */
  public function renderTemplate( $template_id, $template_type ) {
    if ( self::POST_TYPE === $template_type ) {
      if ( $post = get_post( (int)$template_id ) ) {
        return $post->post_content;
      }
      else {
        return '';
      }
    }
    else {
      return $template_id;
    }
  }


  /**
   *  This filter is called by Visual Composer to render template for preview or adding to a page in frontend editor
   *
   * @param $template_id int
   * @param $template_data array
   */
  public function renderTemplateFrontEnd( $template_id, $template_type ) {
    if ( self::POST_TYPE === $template_type ) {
      $post = get_post( (int)$template_id );
      $post_content = $post ? $post->post_content : '';
      vc_frontend_editor()->setTemplateContent( $post_content );
      vc_frontend_editor()->enqueueRequired();
      vc_include_template( 'editors/frontend_template.tpl.php', array(
        'editor' => vc_frontend_editor(),
      ) );
      die();
    }
    else {
      return $template_id;
    }
  }


  /**
   *  Enqueues required styles for proper VC Snippets rendering when added with ACF
   */
  public function adminEditPostStyles() {
    if (
      ( isset( $_GET[ 'post_type' ] ) && ( self::POST_TYPE == $_GET[ 'post_type' ] ) ) ||
      ( self::POST_TYPE == get_post_type() )
    ) {

      add_action( 'admin_print_scripts', array( $this, 'printAdminEmbeddedVcSnippetsStyles' ) );
      wp_enqueue_script( self::POST_TYPE . '_embedded', WP_ACF_VC_BRIDGE_PLUGIN_URL . '/assets/js/vc-snippet-admin-embedded.js', array( 'jquery' ), WpAcfVcBridge::VERSION );
    
    }
  }


  /**
   *  Prints CSS Styles for VC Snippets when opened in embedded mode
   */
  public function printAdminEmbeddedVcSnippetsStyles() {
    ob_start();
    include( WP_ACF_VC_BRIDGE_PLUGIN_PATH . 'assets/css/vc-snippet-admin-embedded.css' );
    $styles = ob_get_clean();
    printf( '<style type="text/css">%s</style>', $styles );
  }


  /**
   *  Disables auto-drafts
   */
  public function adminDisableAutoSave() {
    if ( self::POST_TYPE == get_post_type() ) {
      wp_dequeue_script( 'autosave' );
    }
  }


  /**
   *  Dequeue and deregister ACF when VC Snippet loaded in embedded mode
   *  Required to prevent infinite VC Snippet nesting
   *  Deprecated since 1.3.8
   */
  // public function adminDisableAcf() {
  //   wp_dequeue_script( 'acf-input' );
  //   wp_dequeue_style( 'acf-input' );

  //   wp_deregister_script( 'acf-input' );
  //   wp_deregister_style( 'acf-input' );
  // }


  /**
   *  Enqueues required JS
   */
  public function enqueueScripts() {
    wp_register_script( self::POST_TYPE, WP_ACF_VC_BRIDGE_PLUGIN_URL . '/assets/js/vc-snippets.js', array( 'jquery' ), WpAcfVcBridge::VERSION );
    wp_enqueue_script( self::POST_TYPE );
    wp_localize_script(
      self::POST_TYPE,
      self::POST_TYPE,
      array(
        'url' => admin_url( 'admin-ajax.php' ),
        'nonce' => wp_create_nonce( self::POST_TYPE ),
      )
    );
  }


  /**
   *  Setup custom post type and taxonomy
   */
  public function init() {
    $labels = array(
      'name'                  => __( 'VC Snippets', 'wp-acf-vc-bridge' ),
      'singular_name'         => __( 'VC Snippet', 'wp-acf-vc-bridge' ),
      'menu_name'             => __( 'VC Snippets', 'wp-acf-vc-bridge' ),
      'name_admin_bar'        => __( 'VC Snippet', 'wp-acf-vc-bridge' ),
      'archives'              => __( 'Archives', 'wp-acf-vc-bridge' ),
      'parent_item_colon'     => __( 'Parent item:', 'wp-acf-vc-bridge' ),
      'all_items'             => __( 'All items', 'wp-acf-vc-bridge' ),
      'add_new_item'          => __( 'Add new item', 'wp-acf-vc-bridge' ),
      'add_new'               => __( 'Add item', 'wp-acf-vc-bridge' ),
      'new_item'              => __( 'New item', 'wp-acf-vc-bridge' ),
      'edit_item'             => __( 'Edit item', 'wp-acf-vc-bridge' ),
      'update_item'           => __( 'Update item', 'wp-acf-vc-bridge' ),
      'view_item'             => __( 'View item', 'wp-acf-vc-bridge' ),
      'search_items'          => __( 'Search items', 'wp-acf-vc-bridge' ),
      'not_found'             => __( 'Not found', 'wp-acf-vc-bridge' ),
      'not_found_in_trash'    => __( 'Not found in trash', 'wp-acf-vc-bridge' ),
      'featured_image'        => __( 'Featured image', 'wp-acf-vc-bridge' ),
      'set_featured_image'    => __( 'Add Featured image', 'wp-acf-vc-bridge' ),
      'remove_featured_image' => __( 'Remove Featured image', 'wp-acf-vc-bridge' ),
      'use_featured_image'    => __( 'Use as Featured image', 'wp-acf-vc-bridge' ),
      'insert_into_item'      => __( 'Insert', 'wp-acf-vc-bridge' ),
      'uploaded_to_this_item' => __( 'Uploaded to the item', 'wp-acf-vc-bridge' ),
      'items_list'            => __( 'Items list', 'wp-acf-vc-bridge' ),
      'items_list_navigation' => __( 'List navigation', 'wp-acf-vc-bridge' ),
      'filter_items_list'     => __( 'Filter items list', 'wp-acf-vc-bridge' ),
    );
    $args = array(
      'label'                 => __( 'VC Snippet', 'wp-acf-vc-bridge' ),
      'description'           => __( 'VC Snippets', 'wp-acf-vc-bridge' ),
      'labels'                => $labels,
      'supports'              => array( 'title', 'editor' ),
      'taxonomies'            => array( self::CATEGORY ),
      'hierarchical'          => false,
      'public'                => true, // false
      'show_ui'               => true, // false
      'show_in_menu'          => true, // false
      'menu_icon'             => 'dashicons-welcome-widgets-menus',
      'menu_position'         => 90,
      'show_in_admin_bar'     => false, // false
      'show_in_nav_menus'     => false, // false
      'can_export'            => true,
      'has_archive'           => false,   
      'exclude_from_search'   => true,
      'publicly_queryable'    => false,
      'rewrite'               => false,
      'capability_type'       => 'post',
    );
    register_post_type( self::POST_TYPE, $args );


    // Category
    $labels = array(
      'name'                       => __( 'Category', 'wp-acf-vc-bridge' ),
      'singular_name'              => __( 'Category', 'wp-acf-vc-bridge' ),
      'menu_name'                  => __( 'Categories', 'wp-acf-vc-bridge' ),
      'all_items'                  => __( 'All categories', 'wp-acf-vc-bridge' ),
      'parent_item'                => __( 'Parent category', 'wp-acf-vc-bridge' ),
      'parent_item_colon'          => __( 'Parent category:', 'wp-acf-vc-bridge' ),
      'new_item_name'              => __( 'New category', 'wp-acf-vc-bridge' ),
      'add_new_item'               => __( 'Add new category', 'wp-acf-vc-bridge' ),
      'edit_item'                  => __( 'Edit category', 'wp-acf-vc-bridge' ),
      'update_item'                => __( 'Update category', 'wp-acf-vc-bridge' ),
      'view_item'                  => __( 'View category', 'wp-acf-vc-bridge' ),
      'separate_items_with_commas' => __( 'Separate categories with commas', 'wp-acf-vc-bridge' ),
      'add_or_remove_items'        => __( 'Add or remove categories', 'wp-acf-vc-bridge' ),
      'choose_from_most_used'      => __( 'Choose from most used', 'wp-acf-vc-bridge' ),
      'popular_items'              => __( 'Popular', 'wp-acf-vc-bridge' ),
      'search_items'               => __( 'Search categories', 'wp-acf-vc-bridge' ),
      'not_found'                  => __( 'Not found', 'wp-acf-vc-bridge' ),
      'no_terms'                   => __( 'No categories', 'wp-acf-vc-bridge' ),
      'items_list'                 => __( 'Categories list', 'wp-acf-vc-bridge' ),
      'items_list_navigation'      => __( 'Categories liste navigation', 'wp-acf-vc-bridge' ),
    );
    $args = array(
      'labels'                     => $labels,
      'hierarchical'               => true,
      'public'                     => true,
      'show_ui'                    => true,
      'show_admin_column'          => true,
      'show_in_nav_menus'          => true,
      'show_tagcloud'              => false,
      'rewrite'                    => false,
    );
    register_taxonomy( self::CATEGORY, array( self::POST_TYPE ), $args );

    // Allow Visual Composer for VC Snippet post type
    add_filter( 'vc_check_post_type_validation', function( $flag, $type ) {
      if ( self::POST_TYPE === $type ) {
        $flag = true;
      }
      return $flag;
    }, 10, 2 );

  }


  /**
   *  Retrieves all VC Snippet posts. Optionally filter by category slugs
   *
   *  @param $categories boolean|array
   */
  public static function getPosts( $categories = false ) {
    $query_args = array(
      'post_type' => self::POST_TYPE,
      'post_status' => 'publish',
      'posts_per_page' => -1,
    );

    if ( $categories ) {
      $query_args[ 'tax_query' ] = array(
        array(
          'taxonomy' =>  self::CATEGORY,
          'field' => 'slug',
          'terms' => (array)$categories
        )
      );
    }

    $query = new WP_Query( $query_args );
    self::$posts = $query->posts;    

    return self::$posts;
  }


  /**
   *  Retrieves all VC Snippet categories
   *
   *  @param $categories boolean|array
   */
  public static function getCategories() {
    $terms = get_terms( array(
      'taxonomy' => self::CATEGORY,
    ) );
    $terms = is_array( $terms ) ? $terms : array();
    return $terms;
  }


  /**
   *  Pricess ajax call to retrieve all VC Snippet categories
   */
  public function ajaxGetCategories() {
    check_ajax_referer( self::POST_TYPE, 'nonce' );

    $result = self::getCategories();    

    echo json_encode( $result );
    die();
  }


  /**
   *  Pricess ajax call to retrieve all VC Snippet posts, optionally filtered by category
   */
  public function ajaxGetPosts() {
    check_ajax_referer( self::POST_TYPE, 'nonce' );
    $categories = false;

    if ( isset( $_POST[ 'categories' ] ) && $_POST[ 'categories' ] ) {
      $categories = (array)$_POST[ 'categories' ];
    }

    $result = self::getPosts( $categories );
    echo json_encode( $result );
    die();
  }


  /**
   *  This function safely returns WP_Roles object instance
   */
  protected static function getWpRoles() {
    global $wp_roles;

    if ( ! isset( $wp_roles ) ) {
      $wp_roles = new WP_Roles();
    }

    return $wp_roles;
  }


  /**
   *  Configure on plugin activation
   */
  public static function onPluginActivation() {
    //  Enable Visual Composer for the this post type
    if ( ! $content_types = get_option( 'wpb_js_content_types' ) ) {
      $content_types = vc_default_editor_post_types();
    }
    if ( ! in_array( self::POST_TYPE, $content_types ) ) {
      update_option( 'wpb_js_content_types', array_merge( $content_types, array( self::POST_TYPE ) ) );
    }
    if ( ! $content_types = get_option( 'wpb_js_theme_content_types' ) ) {
      $content_types = vc_default_editor_post_types();
    }
    if ( ! in_array( self::POST_TYPE, $content_types ) ) {
      update_option( 'wpb_js_theme_content_types', array_merge( $content_types, array( self::POST_TYPE ) ) );
    }

    // Update role capabilities
    $default_enabled = array( 'administrator', 'editor' );
    $wp_roles = self::getWpRoles();
    $cap = 'vc_access_rules_post_types/' . self::POST_TYPE;

    foreach ( $default_enabled as $role ) {
      if ( ! $wp_role = get_role( $role ) ) {
        continue;
      }

      if ( ! $wp_role->has_cap( $cap ) ) {
        $wp_roles->add_cap( $role, 'vc_access_rules_post_types/' . self::POST_TYPE, true );
      }

    }
  }

}

WpAcfVcBridgeVcSnippets::instance();

endif;
?>