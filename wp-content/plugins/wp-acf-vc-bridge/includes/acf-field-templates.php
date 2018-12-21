<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'acf_field_templates' ) ) :

final class acf_field_templates {


  /**
   * Plugin instance
   *
   * @since 1.3.8
   * @var object $instance
   */
  protected static $instance;
  

  /**
   * Flag if instance has been initialized
   *
   * @since 1.3.8
   * @var boolean $initialized
   */
  private $initialized = false;
  

  /**
   *  __construct
   *
   *  Initialize acf_field_templates
   *
   *  @type  function
   *  @date  28/10/17
   *  @since 1.3.8
   *
   *  @param N/A
   *  @return  N/A
   */

  private function __construct() {
    
    add_action( 'init', array( $this, 'init' ) );
  }


  /**
   *  instance
   *
   *  Create or retrieve instance. Singleton pattern
   *
   *  @type  function
   *  @date  28/10/17
   *  @since 1.3.8
   *
   *  @static
   *
   *  @param N/A
   *  @return  (object) acf_field_templates instance
   */

  public static function instance() {
    return self::$instance ? self::$instance : self::$instance = new self();
  }


  /**
   *  init
   *
   *  Init templates
   *
   *  @type  function
   *  @date  28/10/17
   *  @since 1.3.8
   *
   *  @param (array) $field
   *  @return  n/a
   */

  public function init() {

    // Can be initialized just once
    if ( $this->initialized ) return;
    $this->initialized = true;

    $this->template_context = array();

    // Prepare template field options
    $vc_snippets = class_exists( 'WpAcfVcBridgeVcSnippets' ) ? WpAcfVcBridgeVcSnippets::getPosts() : array();
    $this->template_options = array();
    foreach ( $vc_snippets as $key => $vc_snippet_post ) {
      $this->template_options[ 0 ] = __( 'No template', 'wp-acf-vc-bridge' );
      $this->template_options[ $vc_snippet_post->ID ] = $vc_snippet_post->post_title;
    }

    // Allow 3rd party to customize template options
    $this->template_options = apply_filters( 'acf_field_templates', $this->template_options );

    add_action( 'acf/render_field_settings', array( $this, 'acf_render_field_settings' ), 10, 1 );
  }


  /**
   *  render_template
   *
   *  Renders template for Flexible Content layout or Repeater row
   *
   *  @type  function
   *  @date  28/10/17
   *  @since 1.3.8
   *
   *  @param (array) $field
   *  @return  n/a
   */

  public function render_template( $template, $template_content, $values, $sub_fields, $post_id ) {
    // Set new context
    $context = array(
      'template' => $template,
      'values' => $values,
      'sub_fields' => $sub_fields,
      'post_id' => $post_id,
    );
    $this->template_context []= $context;

    // Allow 3rd parties to modify before processing shortcodes
    $template_content = apply_filters( 'acf_field_templates/template_content_before', $template_content, $context );

    // Process template
    $template_content = do_shortcode( $template_content );

    // Allow 3rd parties to modify after processing shortcodes
    $template_content = apply_filters( 'acf_field_templates/template_content_after', $template_content, $context );

    // Release context
    array_pop( $this->template_context );

    return $template_content;
  }

  
  /**
   *  get_template_context
   *
   *  Returns context for currently processing template field
   *
   *  @type  function
   *  @date  28/10/17
   *  @since 1.3.8
   *
   *  @param (array) $field
   *  @return  n/a
   */

  public function get_template_context() {
    $last_element = count( $this->template_context ) ? array_slice( $this->template_context, -1 )[ 0 ] : false;
    return $last_element;
  }


  /**
   *  acf_render_field_settings
   *
   *  Renders template settings for content field types
   *
   *  @type  function
   *  @date  28/10/17
   *  @since 1.3.8
   *
   *  @param (array) $field
   *  @return  n/a
   */

  public function acf_render_field_settings( $field ) {
    switch ( $field[ 'type' ] ) {
      case 'flexible_content':
        $this->acf_render_flexible_content_field_settings( $field );
      break;
      case 'repeater':
        $this->acf_render_repeater_field_settings( $field );
      break;
      case 'group':
        $this->acf_render_group_field_settings( $field );
      break;
    }
  }


  /**
   *  acf_render_flexible_content_field_settings
   *
   *  Renders layout template settings for flexible content field type
   *
   *  @type  function
   *  @date  28/10/17
   *  @since 1.3.8
   *
   *  @param (array) $field
   *  @return  n/a
   */

  private function acf_render_flexible_content_field_settings( $field ) {
    $layout_templates = isset( $field[ 'layout_templates' ] ) &&is_array( $field[ 'layout_templates' ] ) ? $field[ 'layout_templates' ] : array();
    $field_params = array(
      'type'    => 'select',
      'choices' => $this->template_options,
      );
    ?>
    <tr class="acf-field acf-field-setting-vc_templates" data-name="vc_templates" data-setting="flexible_content" data-id="<?php echo $field['key']; ?>">
      <td class="acf-label">
        <label><?php _e( 'Layout Templates', 'wp-acf-vc-bridge' ); ?></label>
        <p class="description"><?php _e( 'For each layout below you can pick a VC Snippet that will serve as a template.', 'wp-acf-vc-bridge' ); ?></p>
      </td>
      <td class="acf-input">
        <ul class="acf-fc-meta acf-bl">
          <?php foreach( $field[ 'layouts' ] as $layout ) : 
            $layout_key = $layout[ 'key' ];
            $field_params[ 'prefix' ] = "{$field['prefix']}[layout_templates]";
            $field_params[ 'name' ] = $layout_key;
            $field_params[ 'value' ] = isset( $layout_templates[ $layout_key ] ) ? $layout_templates[ $layout_key ] : '';
          ?>
            <li class="acf-fc-meta-template">
              <div class="acf-input-prepend"><?= $layout[ 'label' ] ?></div>
              <div class="acf-input-wrap select"><?php acf_render_field( $field_params ); ?></div>
            </li>
          <?php endforeach; ?>
        </ul>
        <p class="description"><?php _e( 'Before newly added layout appears here, the field group must be saved.', 'wp-acf-vc-bridge' ); ?></p>
      </td>
    </tr>      
    <?php
  }  


  /**
   *  acf_render_repeater_field_settings
   *
   *  Renders template settings for repeater field type
   *
   *  @type  function
   *  @date  28/10/17
   *  @since 1.3.8
   *
   *  @param (array) $field
   *  @return  n/a
   */

  private function acf_render_repeater_field_settings( $field ) {
    acf_render_field_setting( $field, array(
      'label'     => __( 'Template', 'wp-acf-vc-bridge' ),
      'instructions'  => __( 'You can pick a template to use for displaying each row.', 'wp-acf-vc-bridge' ),
      'type'      => 'select',
      'name'      => 'row_template',
      'choices'   => $this->template_options
    ));
  }  
  

  /**
   *  acf_render_group_field_settings
   *
   *  Renders template settings for group field type
   *
   *  @type  function
   *  @date  28/10/17
   *  @since 1.5.0
   *
   *  @param (array) $field
   *  @return  n/a
   */

  private function acf_render_group_field_settings( $field ) {
    acf_render_field_setting( $field, array(
      'label'     => __( 'Template', 'wp-acf-vc-bridge' ),
      'instructions'  => __( 'You can pick a template to use for displaying this group.', 'wp-acf-vc-bridge' ),
      'type'      => 'select',
      'name'      => 'group_template',
      'choices'   => $this->template_options
    ));
  }  
  
}


/**
 *  acf_field_templates
 *
 *  The main function responsible for returning acf_field_templates object
 *
 *  @type  function
 *  @date  28/10/17
 *  @since 1.3.8
 *
 *  @param N/A
 *  @return (object) acf_field_templates instance
 */

function acf_field_templates() {
  return acf_field_templates::instance();
}


// initialize
acf_field_templates();


endif; // class_exists check

?>