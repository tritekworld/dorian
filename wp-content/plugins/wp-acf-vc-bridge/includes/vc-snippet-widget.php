<?php

if ( !defined( 'ABSPATH' ) ) die();

if ( ! class_exists( 'WpAcfVcBridgeVcSnippetWidget' ) ) :
  
  class WpAcfVcBridgeVcSnippetWidget extends WP_Widget {

    public static $widget_id = 'wp_acf_vc_bridge_vc_snippet_widget';
    public static $widget_slug = 'wp-acf-vc-bridge-vc-snippet-widget';

    protected $defaults;

    /**
    * Register widget with WordPress.
    */
    function __construct() {
      $this->defaults = array(
        'title' => '',
        'show_title' => 'on',
        'id' => '',
      );

      parent::__construct(
        self::$widget_id, // Base ID
        __( 'VC Snippet', 'wp-acf-vc-bridge' ), // Widget Title
        array( 'description' => __( 'Embed VC Snippet', 'wp-acf-vc-bridge' ) ) // Args
      );
    }

    /**
     * Front-end display of widget.
     *
     * @param array $args     Widget arguments.
     * @param array $instance Saved values from database.
     */
    public function widget( $args, $instance ) {
      $instance = array_filter( $instance );
      $instance = wp_parse_args( $instance, $this->defaults );

      $result = $args[ 'before_widget' ];

      if ( 'on' == $instance[ 'show_title' ] ) {
        $title = __( $instance[ 'title' ], 'wp-acf-vc-bridge' );
        $result .= sprintf( '%s%s%s', $args[ 'before_title' ], $title, $args[ 'after_title' ] );
      }

      $result .= VcVcSnippet::getHtml( $instance[ 'id' ] );
      $result .= $args[ 'after_widget' ];

      echo $result;
    }

    /**
     * Back-end widget form.
     *
     * @see WP_Widget::form()
     *
     * @param array $instance Previously saved values from database.
     */
    public function form( $instance ) {
      $posts = WpAcfVcBridgeVcSnippets::getPosts();
      $title = isset( $instance[ 'title' ] ) ? esc_attr( $instance[ 'title' ] ) : '';
      $id = isset( $instance[ 'id' ] ) ? $instance[ 'id' ] : '';
      $show_title = isset( $instance[ 'show_title' ] ) ? $instance[ 'show_title' ] : '';

      $categories = WpAcfVcBridgeVcSnippets::getCategories();
      ?>
      <p>
        <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'wp-acf-vc-bridge' ); ?></label> 
        <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" placeholder="<?= $this->defaults[ 'title' ] ?>" />
      </p>
      <p>
        <input class="checkbox" type="checkbox" <?php checked( $show_title, 'on' ); ?> id="<?php echo $this->get_field_id( 'show_title' ); ?>" name="<?php echo $this->get_field_name( 'show_title' ); ?>" /> 
        <label for="<?php echo $this->get_field_id( 'show_title' ); ?>"><?php _e( 'Show title', 'wp-acf-vc-bridge' ); ?></label>
      </p>
      <p>
        <label for="<?php echo $this->get_field_id( 'filter' ); ?>"><?php _e( 'Filter by category:', 'wp-acf-vc-bridge' ); ?></label>
        <select id="<?php echo $this->get_field_id( 'filter' ); ?>">
          <option value="0"><?php printf( '&mdash; %s &mdash;', __( 'Select', 'wp-acf-vc-bridge' ) ); ?></option>
          <?php foreach ( $categories as $category ) : ?>
            <option value="<?php echo esc_attr( $category->slug ); ?>">
              <?php echo esc_html( $category->name ); ?>
            </option>
          <?php endforeach; ?>
        </select>
      </p>
      <p>
        <label for="<?php echo $this->get_field_id( 'id' ); ?>"><?php _e( 'VC Snippet:', 'wp-acf-vc-bridge' ); ?></label>
        <select id="<?php echo $this->get_field_id( 'id' ); ?>" name="<?php echo $this->get_field_name( 'id' ); ?>">
          <option value="0"><?php printf( '&mdash; %s &mdash;', __( 'Select', 'wp-acf-vc-bridge' ) ); ?></option>
          <?php foreach ( $posts as $post ) : ?>
            <option value="<?php echo esc_attr( $post->ID ); ?>" <?php selected( $id, $post->ID ); ?>>
              <?php echo esc_html( $post->post_title ); ?>
            </option>
          <?php endforeach; ?>
        </select>
      </p>
      <script type="text/javascript">
        (function( $ ) {
          $( function onDomReady() {
            var categoriesFieldId, postsFieldId, jqPostsSelect;
            
            filterFieldId = '<?php echo $this->get_field_id( 'filter' ); ?>';
            postsFieldId = '<?php echo $this->get_field_id( 'id' ); ?>';
            jqPostsSelect = $( '#' + postsFieldId );

            $( '#widgets-right' ).on( 'change', '#' + filterFieldId, function onCategoryFilterChange() {
              var jqFilterSelect, currentValue;

              jqFilterSelect = $( this );
              currentValue = jqPostsSelect.val();

              VcSnippets.getPosts( jqFilterSelect.val(), function onGetFilteredPosts( posts ) {
                if ( ! posts ) return;
                jqPostsSelect.html( VcSnippets.getPostsOptionsHtml( posts ) ).val( currentValue );
              });
            } );
          } );
        })( jQuery );
      </script>
      <?php      
    }

    /**
     * Sanitize widget form values as they are saved.
     *
     * @see WP_Widget::update()
     *
     * @param array $new_instance Values just sent to be saved.
     * @param array $old_instance Previously saved values from database.
     *
     * @return array Updated safe values to be saved.
     */
    public function update( $new_instance, $old_instance ) {
      $instance = array();

      $posts = WpAcfVcBridgeVcSnippets::getPosts();
      $id = $new_instance[ 'id' ];
      $found = false;
      foreach ( $posts as $post ) {
        if ( $post->ID == $id ) {
          $found = true;
          break;
        }
      }

      $instance[ 'title' ] = $new_instance[ 'title' ];
      $instance[ 'id' ] = $found ? (int)$id : 0;
      $instance[ 'show_title' ] = 'on' == $new_instance[ 'show_title' ] ? 'on' : 'off';

      $instance = array_filter( $instance );
      $instance = wp_parse_args( $instance, $this->defaults );

      return $instance;
    }

  }

endif;

add_action( 'widgets_init', function() {
  register_widget( 'WpAcfVcBridgeVcSnippetWidget' );
} );

?>