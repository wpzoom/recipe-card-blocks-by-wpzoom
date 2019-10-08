<?php
/**
 * Class Settings Fields
 *
 * @since   1.1.0
 * @package WPZOOM_Recipe_Card_Blocks
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
	 * @var array $options  Store all options value of the setting we've registered with register_setting()
	 */
	public static $options;

	/**
	 * @var array $fields_type
	 */
	private $fields_type = array( 'checkbox', 'select', 'multiselect', 'input', 'textarea', 'button' );

	/**
	 * The Constructor.
	 */
	public function __construct() {
		self::$options = WPZOOM_Settings::$options;
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
		$value = self::parse_text_field( $args );
		$type = isset( $args['type'] ) ? $args['type'] : 'text';
	?>
		<fieldset class="wpzoom-rcb-field-input">
			<?php
				if ( isset( $args['badge'] ) ) { echo $args['badge']; }
				$this->create_nonce_field( $args );
			?>

			<input name="wpzoom-recipe-card-settings[<?php echo esc_attr( $args['label_for'] ); ?>]" type="<?php echo esc_attr( $type ) ?>" id="<?php echo esc_attr( $args['label_for'] ) ?>" value="<?php echo $value ?>" class="regular-text" <?php echo ( self::is_disabled( $args ) ? 'disabled' : '' ); ?>/>

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
		$checked = self::parse_checkbox_field( $args );
        $has_preview = isset( $args['preview'] ) && $args['preview'] === true;
	?>
		<fieldset class="wpzoom-rcb-field-checkbox">
			<?php
				if ( isset( $args['badge'] ) ) { echo $args['badge']; }
				$this->create_nonce_field( $args );
			?>

			<label for="<?php echo esc_attr( $args['label_for'] ) ?>">
				<input type="hidden" name="wpzoom-recipe-card-settings[<?php echo esc_attr( $args['label_for'] ); ?>]" value="0" />
				<input name="wpzoom-recipe-card-settings[<?php echo esc_attr( $args['label_for'] ); ?>]" type="checkbox" id="<?php echo esc_attr( $args['label_for'] ); ?>" value="1" <?php checked( '1', $checked ); ?> <?php echo ( self::is_disabled( $args ) ? 'disabled' : '' ); ?>/>

				<?php if ( isset( $args['description'] ) ): ?>
					<?php echo $args['description']; ?>
				<?php endif ?>
			</label>

            <?php if ( $has_preview ): ?>

                <?php
                $filename 				= WPZOOM_RCB_PLUGIN_DIR . 'dist/assets/images/previews/' . esc_attr( $args['label_for'] ) . '.png';
                $preview_position 		= isset( $args['preview_pos'] ) ? $args['preview_pos'] : 'right';
                $preview_thumbnail_url 	= untrailingslashit( WPZOOM_RCB_PLUGIN_URL ) . '/dist/assets/images/previews/' . esc_attr( $args['label_for'] ) . '.png';

                if ( file_exists( $filename ) ) {
                    printf(
                        '<span class="wpzoom-rcb-field-preview dashicons dashicons-visibility" data-preview-position="%s" data-preview-thumbnail="%s" title="%s"></span>',
                        $preview_position,
                        $preview_thumbnail_url,
                        esc_html__( 'Preview', 'wpzoom-recipe-card' )
                    );    
                }
                ?>

            <?php endif ?>
            
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
		$selected = self::parse_select_field( $args );
	?>
		<fieldset class="wpzoom-rcb-field-select">
			<?php $this->create_nonce_field( $args ); ?>
			<select id="<?php echo esc_attr( $args['label_for'] ); ?>"
				name="wpzoom-recipe-card-settings[<?php echo esc_attr( $args['label_for'] ); ?>]"
				<?php echo ( self::is_disabled( $args ) ? 'disabled' : '' ); ?>
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
		$text 			= isset( $args['text'] ) ? $args['text'] : __( 'Save Changes', 'wpzoom-recipe-card' );
		$type 			= isset( $args['type'] ) ? $args['type'] : 'submit';
		$button_type 	= isset( $args['button_type'] ) ? $args['button_type'] : 'primary large';
		$name 			= isset( $args['label_for'] ) ? $args['label_for'] : 'wpzoom_rcb_button_field_submit';
		$wrap 			= isset( $args['wrap'] ) ? $args['wrap'] : false;

		if ( ! is_array( $button_type ) ) {
			$button_type = explode( ' ', $button_type );
		}

		$button_shorthand = array( 'primary', 'small', 'large' );
		$classes          = array( 'button' );

		foreach ( $button_type as $t ) {
	        if ( 'secondary' === $t || 'button-secondary' === $t ) {
	            continue;
	        }
	        $classes[] = in_array( $t, $button_shorthand ) ? 'button-' . $t : $t;
		}
		// Remove empty items, remove duplicate items, and finally build a string.
		$class = implode( ' ', array_unique( array_filter( $classes ) ) );

		$id = $name;

		if ( isset( $args['badge'] ) ) { echo $args['badge']; }

		$this->create_nonce_field( $args );


		echo sprintf(
			'<input type="%s" name="%s" id="%s" class="%s" value="%s"%s>',
			esc_attr( $type ),
			esc_attr( $name ),
			esc_attr( $id ),
			esc_attr( $class ),
			esc_attr( $text ),
			( self::is_disabled( $args ) ? ' disabled' : '' )
		);

		?>

		<?php if ( isset( $args['description'] ) ): ?>
			<p class="description">
				<?php echo $args['description']; ?>
			</p>
		<?php endif ?>

	<?php
	}

	/**
	 * HTML for Color Picker field
	 * 
	 * @since 2.3.2
	 * @param array $args 
	 * @return void
	 */
	public function colorpicker( $args ) {
		$value = self::parse_text_field( $args );
		$default_value = WPZOOM_Settings::get_default_option_value( $args['label_for'] );
	?>
		<fieldset class="wpzoom-rcb-field-color-picker">
			<?php
				if ( isset( $args['badge'] ) ) { echo $args['badge']; }
				$this->create_nonce_field( $args );
			?>

			<input name="wpzoom-recipe-card-settings[<?php echo esc_attr( $args['label_for'] ) ?>]" type="text" id="<?php echo esc_attr( $args['label_for'] ) ?>" value="<?php echo strtoupper( $value ) ?>" data-default-color="<?php echo esc_attr( $default_value ) ?>" class="wpzoom-rcb-color-picker" />

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
	 * Parse input|textarea field to prevent value change via browser dev tool.
	 * 
	 * @since 1.2.1
	 * @param array $args  The field arguments
	 * @return boolean|string
	 */
	public static function parse_text_field( $args ) {
		$default_value = WPZOOM_Settings::get_default_option_value( $args['label_for'] );

		$value = isset( self::$options[ $args['label_for'] ] ) ? self::$options[ $args['label_for'] ] : $args['default'];

		if ( self::is_disabled( $args ) ) {
			self::$options[ $args['label_for'] ] = $default_value;
			WPZOOM_Settings::update_option( self::$options );
			return $default_value;
		}

		return sanitize_text_field( $value );
	}

	/**
	 * Parse checkbox|radio field to prevent value change via browser dev tool.
	 * 
	 * @since 1.2.1
	 * @param array $args  The field arguments
	 * @return boolean|string
	 */
	public static function parse_checkbox_field( $args ) {
		$default_value = WPZOOM_Settings::get_default_option_value( $args['label_for'] );

		if ( empty( self::$options ) ) {
			$checked = $args['default'];
		} else {
			$checked = isset( self::$options[ $args['label_for'] ] ) ? self::$options[ $args['label_for'] ] : '0';
		}

		if ( self::is_disabled( $args ) ) {
			self::$options[ $args['label_for'] ] = $default_value;
			WPZOOM_Settings::update_option( self::$options );
			return $default_value;
		}

		return $checked;
	}

	/**
	 * Parse select field to prevent value change via browser dev tool.
	 * 
	 * @since 1.2.1
	 * @param array $args  The field arguments
	 * @return boolean|string
	 */
	public static function parse_select_field( $args ) {
		$default_value = WPZOOM_Settings::get_default_option_value( $args['label_for'] );

		if ( empty( self::$options ) ) {
			$selected = $args['default'];
		} else {
			$selected = isset( self::$options[ $args['label_for'] ] ) ? self::$options[ $args['label_for'] ] : '';
		}

		if ( self::is_disabled( $args ) ) {
			self::$options[ $args['label_for'] ] = $default_value;
			WPZOOM_Settings::update_option( self::$options );
			return $default_value;
		}

		return $selected;
	}

	/**
	 * Check is disabled field
	 * 
	 * @since 1.2.1
	 * @param array $args Field arguments
	 * @return boolean
	 */
	public static function is_disabled( $args ) {
		return isset( $args['disabled'] ) && true === $args['disabled'];
	}

	/**
	 * Enqueue scripts and styles
	 * 
	 * @param string $hook
	 */
	public function scripts( $hook ) {
	    $pos = strpos( $hook, WPZOOM_RCB_SETTINGS_PAGE );

	    if ( $pos === false ) {
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
