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
			<?php
				$disabled = isset( $args['disabled'] ) && true === $args['disabled'];

				if ( isset( $args['badge'] ) ) { echo $args['badge']; }

				$this->create_nonce_field( $args );
			?>

			<input name="wpzoom-recipe-card-settings[<?php echo esc_attr( $args['label_for'] ); ?>]" type="<?php echo esc_attr( $type ) ?>" id="<?php echo esc_attr( $args['label_for'] ) ?>" value="<?php echo $value ?>" class="regular-text" <?php echo ( $disabled ? 'disabled' : '' ); ?>/>

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
			<?php
				$disabled = isset( $args['disabled'] ) && true === $args['disabled'];
				
				if ( isset( $args['badge'] ) ) { echo $args['badge']; }

				$this->create_nonce_field( $args );
			?>

			<label for="<?php echo esc_attr( $args['label_for'] ) ?>">
				<input name="wpzoom-recipe-card-settings[<?php echo esc_attr( $args['label_for'] ); ?>]" type="checkbox" id="<?php echo esc_attr( $args['label_for'] ); ?>" value="<?php echo esc_attr( $args['default'] ) ?>" <?php checked( '1', $checked ); ?> <?php echo ( $disabled ? 'disabled' : '' ); ?>/>

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
		$disabled = isset( $args['disabled'] ) && true === $args['disabled'];

		if ( empty( $options ) ) {
			$selected = $args['default'];
		} else {
			$selected = isset( $options[ $args['label_for'] ] ) ? $options[ $args['label_for'] ] : '';
		}
	?>
		<fieldset class="wpzoom-rcb-field-select">
			<?php $this->create_nonce_field( $args ); ?>
			<select id="<?php echo esc_attr( $args['label_for'] ); ?>"
				name="wpzoom-recipe-card-settings[<?php echo esc_attr( $args['label_for'] ); ?>]"
				<?php echo ( $disabled ? 'disabled' : '' ); ?>
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
	 * HTML for Button field type
	 * 
	 * @param array $args 
	 * @return void
	 */
	public function button( $args ) {
		$text = isset( $args['text'] ) ? $args['text'] : __( 'Save Changes', 'wpzoom-recipe-card' );
		$type = isset( $args['type'] ) ? $args['type'] : 'primary';
		$name = isset( $args['label_for'] ) ? $args['label_for'] : 'wpzoom_rcb_button_field_submit';
		$wrap = isset( $args['wrap'] ) ? $args['wrap'] : false;
		$other_attributes = isset( $args['other_attributes'] ) ? $args['other_attributes'] : null;

		if ( isset( $args['badge'] ) ) { echo $args['badge']; }

		$this->create_nonce_field( $args );
		submit_button( $text, $type, $name, $wrap, $other_attributes );
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

	public function create_nonce_field( $args ) {
		if ( ! isset( $args['nonce'] ) ) {
			return;
		}

		$action = isset( $args['nonce']['action'] ) ? $args['nonce']['action'] : -1;
		$name = isset( $args['nonce']['name'] ) ? $args['nonce']['name'] : '_wpnonce';
		$referer = isset( $args['nonce']['referer'] ) ? $args['nonce']['referer'] : true;
		$echo = isset( $args['nonce']['echo'] ) ? $args['nonce']['echo'] : true;

		wp_nonce_field( $action, $name, $referer, $echo );
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
