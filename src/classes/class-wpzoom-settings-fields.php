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
	/**
	 * @var array $fields_type
	 */
	private $fields_type = array( 'checkbox', 'select', 'multiselect', 'input', 'textarea', 'button' );

	/**
	 * The Constructor.
	 */
	public function __construct() {
	    // add_action( 'admin_enqueue_scripts', array( $this, 'scripts' ) );
	}

	/**
	 * Get all available fields type
	 * 
	 * @return array
	 */
	public function get_fields_type() {
		return $this->fields_type;
	}

	/**
	 * HTML for Input field type
	 * 
	 * @param array $args 
	 * @return void
	 */
	public function input( $args ) {
		// get the value of the setting we've registered with register_setting()
		$options = get_option( 'wpzoom-recipe-card-settings' );

		$value = isset( $options[ $args['label_for'] ] ) ? $options[ $args['label_for'] ] : $args['default'];
		$type = isset( $args['type'] ) ? $args['type'] : 'text';
	?>
		<fieldset class="wpzoom-rcb-field-input">
			<?php $is_premium = isset( $args['is_premium'] ) && $args['is_premium']; ?>
				
			<?php if ( $is_premium ): ?>
				<span class="wpzoom-rcb-field-is_premium"><?php esc_html_e( 'Premium', 'wpzoom-recipe-card' ); ?></span>
			<?php endif ?>

			<input name="wpzoom-recipe-card-settings[<?php echo esc_attr( $args['label_for'] ); ?>]" type="<?php echo esc_attr( $type ) ?>" id="<?php echo esc_attr( $args['label_for'] ) ?>" value="<?php echo $value ?>" class="regular-text"/>

			<?php if ( isset( $args['description'] ) ): ?>
				<p class="description">
					<?php echo $args['description']; ?>
				</p>
			<?php endif ?>
		</fieldset>
	<?php
	}
	 
	/**
	 * HTML for Checkbox field type
	 * 
	 * @param array $args 
	 * @return void
	 */
	public function checkbox( $args ) {
		// get the value of the setting we've registered with register_setting()
		$options = get_option( 'wpzoom-recipe-card-settings' );

		if ( empty( $options ) ) {
			$checked = $args['default'];
		} else {
			$checked = isset( $options[ $args['label_for'] ] ) ? $options[ $args['label_for'] ] : 'false';
		}
	?>
		<fieldset class="wpzoom-rcb-field-checkbox">
			<?php $is_premium = isset( $args['is_premium'] ) && $args['is_premium']; ?>
				
			<?php if ( $is_premium ): ?>
				<span class="wpzoom-rcb-field-is_premium"><?php esc_html_e( 'Premium', 'wpzoom-recipe-card' ); ?></span>
			<?php endif ?>

			<label for="<?php echo esc_attr( $args['label_for'] ) ?>">
				<input name="wpzoom-recipe-card-settings[<?php echo esc_attr( $args['label_for'] ); ?>]" type="checkbox" id="<?php echo esc_attr( $args['label_for'] ); ?>" value="<?php echo esc_attr( $args['default'] ) ?>" <?php checked( '1', $checked ); ?> <?php echo ( $is_premium ? 'disabled' : '' ); ?>/>

				<?php if ( isset( $args['description'] ) ): ?>
					<?php echo $args['description']; ?>
				<?php endif ?>
			</label>
		</fieldset>
	<?php
	}
	 
	/**
	 * HTML for Select field type
	 * 
	 * @param array $args 
	 * @return void
	 */
	public function select( $args ) {
		// get the value of the setting we've registered with register_setting()
		$options = get_option( 'wpzoom-recipe-card-settings' );

		if ( empty( $options ) ) {
			$selected = $args['default'];
		} else {
			$selected = isset( $options[ $args['label_for'] ] ) ? $options[ $args['label_for'] ] : '';
		}
	?>
		<fieldset class="wpzoom-rcb-field-select">
			<select id="<?php echo esc_attr( $args['label_for'] ); ?>"
				name="wpzoom-recipe-card-settings[<?php echo esc_attr( $args['label_for'] ); ?>]"
		 	>
		 		<?php foreach ( $args['options'] as $value => $text ): ?>
		 			<option value="<?php echo esc_attr( $value ) ?>" <?php selected( $value, $selected ); ?>>
		 				<?php echo $text; ?>
		 			</option>
		 		<?php endforeach ?>
		 	</select>

		 	<?php if ( isset( $args['description'] ) ): ?>
		 		<p class="description">
		 			<?php echo $args['description']; ?>
		 		</p>
		 	<?php endif ?>
		</fieldset>
	<?php
	}
	 
	/**
	 * HTML for Subsection field type
	 * 
	 * @param array $args 
	 * @return void
	 */
	public function subsection( $args ) {
		echo '';
	}

	/**
	 * Enqueue scripts and styles
	 * 
	 * @param string $hook
	 */
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
