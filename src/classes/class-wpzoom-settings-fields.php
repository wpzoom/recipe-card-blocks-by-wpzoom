<?php
/**
 * Class Settings Fields
 *
 * @since   1.1.0
 * @package WPZOOM Recipe Card Block
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class for settings page.
 */
class WPZOOM_Settings_Fields {
	private $fiels_type = array( 'radiobox', 'checkbox', 'select', 'multiselect', 'input', 'textarea', 'button' );

	/**
	 * The Constructor.
	 */
	public function __construct() {
	    // add_action( 'admin_enqueue_scripts', array( $this, 'scripts' ) );
	}

	public function get_fields_type() {
		return $this->fiels_type;
	}
	 
	public function checkbox( $args ) {
		// get the value of the setting we've registered with register_setting()
		$options = get_option( 'wpzoom-recipe-card-settings' );

		$checked = isset( $options[ $args['label_for'] ] ) ? $options[ $args['label_for'] ] : $args['default'];
	?>
		<input type="checkbox" class="<?php echo esc_attr( $args['class'] ) ?>" name="<?php echo esc_attr( $args['label_for'] ); ?>" <?php checked( $checked, $args['default'], true ); ?>/>

		<?php if ( isset( $args['description'] ) ): ?>
			<p class="description">
				<?php echo $args['description']; ?>
			</p>
		<?php endif ?>
	<?php
	}
	 
	public function select( $args ) {
		// get the value of the setting we've registered with register_setting()
		$options = get_option( 'wpzoom-recipe-card-settings' );
	?>
	 <!-- <select id="<?php echo esc_attr( $args['label_for'] ); ?>"
	 data-custom="<?php echo esc_attr( $args['wpzoom_custom_data'] ); ?>"
	 name="wpzoom_options[<?php echo esc_attr( $args['label_for'] ); ?>]"
	 >
	 <option value="red" <?php echo isset( $options[ $args['label_for'] ] ) ? ( selected( $options[ $args['label_for'] ], 'red', false ) ) : ( '' ); ?>>
	 <?php esc_html_e( 'red pill', 'wpzoom-recipe-card' ); ?>
	 </option>
	 <option value="blue" <?php echo isset( $options[ $args['label_for'] ] ) ? ( selected( $options[ $args['label_for'] ], 'blue', false ) ) : ( '' ); ?>>
	 <?php esc_html_e( 'blue pill', 'wpzoom-recipe-card' ); ?>
	 </option>
	 </select>
	 <p class="description">
	 <?php esc_html_e( 'You take the blue pill and the story ends. You wake in your bed and you believe whatever you want to believe.', 'wpzoom-recipe-card' ); ?>
	 </p> -->
	 <?php
	}

	public function scripts( $hook ) {
	    if ( $hook != 'settings_page_wpzoom-recipe-card-settings' ) {
	        return;
	    }

	    wp_enqueue_style(
	    	'wpzoom-rcb-admin-style',
	    	untrailingslashit( WPZOOM_RCB_PLUGIN_URL ) . '/dist/assets/admin/css/style.css',
	    	array(),
	    	WPZOOM_RCB_VERSION
	    );

	    wp_enqueue_script(
	    	'wpzoom-rcb-admin-script',
	    	untrailingslashit( WPZOOM_RCB_PLUGIN_URL ) . '/dist/assets/admin/js/script.js',
	    	array( 'jquery' ),
	    	WPZOOM_RCB_VERSION
	    );
	}
}
