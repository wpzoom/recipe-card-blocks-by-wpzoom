<?php
/**
 * Class Settings Page
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
class WPZOOM_Settings {
	/**
	 * Store all default settings options.
	 *
	 * @static
	 */
	public static $defaults = array();

	/**
	 * Store all settings options.
	 */
	public $settings = array();

	/**
	 * Class WPZOOM_Settings_Fields instance.
	 */
	public $_fields;

	/**
	 * The Constructor.
	 */
	public function __construct() {
	    add_action( 'admin_menu', array( $this, 'admin_menu' ) );
	    add_action( 'admin_init', array( $this, 'settings_init' ) );
	    add_action( 'admin_init', array( $this, 'set_defaults' ) );
	    add_action( 'admin_enqueue_scripts', array( $this, 'scripts' ) );

	    $this->_fields = new WPZOOM_Settings_Fields();
	}

	/**
	 * Add subitem to Settings admin menu.
	 */
	public function admin_menu() {
		add_options_page(
			__( 'WPZOOM Recipe Card Settings', 'wpzoom-recipe-card' ),
			__( 'WPZOOM Recipe Card', 'wpzoom-recipe-card' ),
			'manage_options',
			'wpzoom-recipe-card-settings',
			array( $this, 'settings_page' )
		);
	}

	/**
	 * Set default values for setting options.
	 */
	public function set_defaults() {
		foreach ( $this->settings as $key => $setting ) {
			if ( isset( $setting['sections'] ) && is_array( $setting['sections'] ) ) {
				foreach ( $setting['sections'] as $section ) {
					if ( isset( $section['fields'] ) && is_array( $section['fields'] ) ) {
						foreach ( $section['fields'] as $field ) {
							if ( isset( $field['args']['default'] ) ) {
								self::$defaults[ $field['id'] ] = $field['args']['default'];
							}
						}
					}
				}
			}
		}

		return self::$defaults;
	}

	/**
	 * Get default values of setting options.
	 *
	 * @static
	 */
	public static function get_defaults() {
		return self::$defaults;
	}

	/**
	 * Get default value by option name
	 * 
	 * @param string $option_name 
	 * @static
	 * @return boolean
	 */
	public static function get_default_option_value( $option_name ) {
		return isset( self::$defaults[ $option_name ] ) ? self::$defaults[ $option_name ] : false;
	}

	/**
	 * Initilize all settings
	 */
	public function settings_init() {
		$this->settings = array(
			'general' => array(
				'tab_id' 		=> 'tab-general',
				'tab_title' 	=> __( 'General', 'wpzoom-recipe-card' ),
				'is_active'		=> true,
				'option_group' 	=> 'wpzoom-recipe-card-settings-general',
				'option_name' 	=> 'wpzoom-recipe-card-settings',
				'sections' 		=> array(
					array(
						'id' 		=> 'wpzoom_section_general',
						'title' 	=> __( 'Defaults', 'wpzoom-recipe-card' ),
						'page' 		=> 'wpzoom-recipe-card-settings-general',
						'callback' 	=> array( $this, 'section_defaults_cb' ),
						'fields' 	=> array(
							array(
								'id' 		=> 'wpzoom_rcb_settings_display_course',
								'title' 	=> __( 'Display Course', 'wpzoom-recipe-card' ),
								'type'		=> 'checkbox',
								'args' 		=> array(
									'label_for' 	=> 'wpzoom_rcb_settings_display_course',
									'class' 		=> 'wpzoom-rcb-field',
									'description' 	=> esc_html__( 'Show course by default', 'wpzoom-recipe-card' ),
									'default'		=> true
								)
							),
							array(
								'id' 		=> 'wpzoom_rcb_settings_display_cuisine',
								'title' 	=> __( 'Display Cuisine', 'wpzoom-recipe-card' ),
								'type'		=> 'checkbox',
								'args' 		=> array(
									'label_for' 	=> 'wpzoom_rcb_settings_display_cuisine',
									'class' 		=> 'wpzoom-rcb-field',
									'description' 	=> esc_html__( 'Show cuisine by default', 'wpzoom-recipe-card' ),
									'default'		=> true
								)
							),
							array(
								'id' 		=> 'wpzoom_rcb_settings_display_difficulty',
								'title' 	=> __( 'Display Difficulty', 'wpzoom-recipe-card' ),
								'type'		=> 'checkbox',
								'args' 		=> array(
									'label_for' 	=> 'wpzoom_rcb_settings_display_difficulty',
									'class' 		=> 'wpzoom-rcb-field',
									'description' 	=> esc_html__( 'Show difficulty by default', 'wpzoom-recipe-card' ),
									'default'		=> true
								)
							),
							array(
								'id' 		=> 'wpzoom_rcb_settings_display_author',
								'title' 	=> __( 'Display Author', 'wpzoom-recipe-card' ),
								'type'		=> 'checkbox',
								'args' 		=> array(
									'label_for' 	=> 'wpzoom_rcb_settings_display_author',
									'class' 		=> 'wpzoom-rcb-field',
									'description' 	=> esc_html__( 'Show author by default', 'wpzoom-recipe-card' ),
									'default'		=> true
								)
							),
							array(
								'id' 		=> 'wpzoom_rcb_settings_author_custom_name',
								'title' 	=> __( 'Default Author Name', 'wpzoom-recipe-card' ),
								'type'		=> 'input',
								'args' 		=> array(
									'label_for' 	=> 'wpzoom_rcb_settings_author_custom_name',
									'class' 		=> 'wpzoom-rcb-field',
									'description' 	=> esc_html__( 'You can add custom author name for all new Recipe Cards. By default is post author name.', 'wpzoom-recipe-card' ),
									'default'		=> '',
									'type'			=> 'text'
								)
							),
							array(
								'id' 		=> 'wpzoom_rcb_settings_ingredients_title',
								'title' 	=> __( 'Default Ingredients Title', 'wpzoom-recipe-card' ),
								'type'		=> 'input',
								'args' 		=> array(
									'label_for' 	=> 'wpzoom_rcb_settings_ingredients_title',
									'class' 		=> 'wpzoom-rcb-field',
									'description' 	=> esc_html__( 'Add your custom Ingredients title for all new Recipe Cards.', 'wpzoom-recipe-card' ),
									'default'		=> __( 'Ingredients', 'wpzoom-recipe-card' ),
									'type'			=> 'text'
								)
							),
							array(
								'id' 		=> 'wpzoom_rcb_settings_steps_title',
								'title' 	=> __( 'Default Steps Title', 'wpzoom-recipe-card' ),
								'type'		=> 'input',
								'args' 		=> array(
									'label_for' 	=> 'wpzoom_rcb_settings_steps_title',
									'class' 		=> 'wpzoom-rcb-field',
									'description' 	=> esc_html__( 'Add your custom Steps title for all new Recipe Cards.', 'wpzoom-recipe-card' ),
									'default'		=> __( 'Directions', 'wpzoom-recipe-card' ),
									'type'			=> 'text'
								)
							),
							array(
								'id' 		=> 'wpzoom_rcb_settings_notes_title',
								'title' 	=> __( 'Default Notes Title', 'wpzoom-recipe-card' ),
								'type'		=> 'input',
								'args' 		=> array(
									'label_for' 	=> 'wpzoom_rcb_settings_notes_title',
									'class' 		=> 'wpzoom-rcb-field',
									'description' 	=> esc_html__( 'Add your custom Notes title for all new Recipe Cards.', 'wpzoom-recipe-card' ),
									'default'		=> __( 'Notes', 'wpzoom-recipe-card' ),
									'type'			=> 'text'
								)
							),
							array(
								'id' 		=> 'wpzoom_rcb_settings_heading_content_align',
								'title' 	=> __( 'Heading content align', 'wpzoom-recipe-card' ),
								'type'		=> 'select',
								'args' 		=> array(
									'label_for' 	=> 'wpzoom_rcb_settings_heading_content_align',
									'class' 		=> 'wpzoom-rcb-field',
									'default'		=> 'left',
									'options' 		=> array(
										'left' 			=> __( 'Left', 'wpzoom-recipe-card' ),
										'center' 		=> __( 'Center', 'wpzoom-recipe-card' ),
										'right' 		=> __( 'Right', 'wpzoom-recipe-card' ),
									)
								)
							),
							array(
								'id' 		=> 'wpzoom_rcb_settings_footer_copyright',
								'title' 	=> __( 'Footer Copyright', 'wpzoom-recipe-card' ),
								'type'		=> 'checkbox',
								'args' 		=> array(
									'label_for' 	=> 'wpzoom_rcb_settings_footer_copyright',
									'class' 		=> 'wpzoom-rcb-field',
									'description' 	=> esc_html__( 'Hide footer copyright text.', 'wpzoom-recipe-card' ),
									'default'		=> false,
									'is_premium' 	=> true,
								)
							),
						)
					),
					array(
						'id' 		=> 'wpzoom_section_rating_features',
						'title' 	=> __( 'Rating Feature', 'wpzoom-recipe-card' ),
						'page' 		=> 'wpzoom-recipe-card-settings-general',
						'callback' 	=> array( $this, 'section_rating_feature_cb' ),
						'fields' 	=> array(
							array(
								'id' 		=> 'wpzoom_rcb_settings_user_ratings',
								'title' 	=> __( 'User Rating', 'wpzoom-recipe-card' ),
								'type'		=> 'checkbox',
								'args' 		=> array(
									'label_for' 	=> 'wpzoom_rcb_settings_user_ratings',
									'class' 		=> 'wpzoom-rcb-field',
									'description' 	=> esc_html__( 'Allow visitors to vote your recipes.', 'wpzoom-recipe-card' ),
									'default'		=> false,
									'is_premium' 	=> true,
								)
							),
						)
					),
				)
			),
			'appearance' => array(
				'tab_id' 		=> 'tab-appearance',
				'tab_title' 	=> __( 'Appearance', 'wpzoom-recipe-card' ),
				'option_group' 	=> 'wpzoom-recipe-card-settings-appearance',
				'option_name' 	=> 'wpzoom-recipe-card-settings',
				'sections' 		=> array(
					array(
						'id' 		=> 'wpzoom_section_recipe_template',
						'title' 	=> __( 'Recipe Template', 'wpzoom-recipe-card' ),
						'page' 		=> 'wpzoom-recipe-card-settings-appearance',
						'callback' 	=> array( $this, 'section_recipe_template_cb' ),
						'fields' 	=> array(
							array(
								'id' 		=> 'wpzoom_rcb_settings_template',
								'title' 	=> __( 'Default Template', 'wpzoom-recipe-card' ),
								'type'		=> 'select',
								'args' 		=> array(
									'label_for' 	=> 'wpzoom_rcb_settings_template',
									'class' 		=> 'wpzoom-rcb-field',
									'description' 	=> esc_html__( 'Default template to use for all Recipe Cards.', 'wpzoom-recipe-card' ),
									'default'		=> 'default',
									'options' 		=> array(
										'default' 	=> __( 'Default', 'wpzoom-recipe-card' ),
										'newdesign' => __( 'New Design', 'wpzoom-recipe-card' ),
									)
								)
							),
						)
					),
					array(
						'id' 		=> 'wpzoom_section_print',
						'title' 	=> __( 'Print', 'wpzoom-recipe-card' ),
						'page' 		=> 'wpzoom-recipe-card-settings-appearance',
						'callback' 	=> '__return_false',
						'fields' 	=> array(
							array(
								'id' 		=> 'wpzoom_rcb_settings_display_print',
								'title' 	=> __( 'Display Print Button', 'wpzoom-recipe-card' ),
								'type'		=> 'checkbox',
								'args' 		=> array(
									'label_for' 	=> 'wpzoom_rcb_settings_display_print',
									'class' 		=> 'wpzoom-rcb-field',
									'description' 	=> esc_html__( 'Show Print button in recipe card', 'wpzoom-recipe-card' ),
									'default'		=> true
								)
							),
							array(
								'id' 		=> 'wpzoom_rcb_settings_print_show_image',
								'title' 	=> __( 'Show Recipe Image', 'wpzoom-recipe-card' ),
								'type'		=> 'checkbox',
								'args' 		=> array(
									'label_for' 	=> 'wpzoom_rcb_settings_print_show_image',
									'class' 		=> 'wpzoom-rcb-field',
									'description' 	=> esc_html__( 'Show Recipe Image on print sheet.', 'wpzoom-recipe-card' ),
									'default'		=> true
								)
							),
							array(
								'id' 		=> 'wpzoom_rcb_settings_print_show_steps_image',
								'title' 	=> __( 'Show Steps Image', 'wpzoom-recipe-card' ),
								'type'		=> 'checkbox',
								'args' 		=> array(
									'label_for' 	=> 'wpzoom_rcb_settings_print_show_steps_image',
									'class' 		=> 'wpzoom-rcb-field',
									'description' 	=> esc_html__( 'Show Steps Image on print sheet.', 'wpzoom-recipe-card' ),
									'default'		=> true
								)
							),
						)
					),
					array(
						'id' 		=> 'wpzoom_rcb_settings_pinterest',
						'title' 	=> __( 'Pinterest', 'wpzoom-recipe-card' ),
						'page' 		=> 'wpzoom-recipe-card-settings-appearance',
						'callback' 	=> '__return_false',
						'fields' 	=> array(
							array(
								'id' 		=> 'wpzoom_rcb_settings_display_pin',
								'title' 	=> __( 'Display Pin Button', 'wpzoom-recipe-card' ),
								'type'		=> 'checkbox',
								'args' 		=> array(
									'label_for' 	=> 'wpzoom_rcb_settings_display_pin',
									'class' 		=> 'wpzoom-rcb-field',
									'description' 	=> esc_html__( 'Show Pinterest button in recipe card', 'wpzoom-recipe-card' ),
									'default'		=> false
								)
							),
							array(
								'id' 		=> 'wpzoom_rcb_settings_pin_image',
								'title' 	=> __( 'Pin Image', 'wpzoom-recipe-card' ),
								'type'		=> 'select',
								'args' 		=> array(
									'label_for' 	=> 'wpzoom_rcb_settings_pin_image',
									'class' 		=> 'wpzoom-rcb-field',
									'description' 	=> esc_html__( 'Image to use for Pin Recipe.', 'wpzoom-recipe-card' ),
									'default'		=> 'recipe_image',
									'options' 		=> array(
										'recipe_image' => __( 'Recipe Image', 'wpzoom-recipe-card' ),
										'custom_image' => __( 'Custom Image per Recipe (Premium Only)', 'wpzoom-recipe-card' ),
									)
								)
							),
							array(
								'id' 		=> 'wpzoom_rcb_settings_pin_description',
								'title' 	=> __( 'Pin Description', 'wpzoom-recipe-card' ),
								'type'		=> 'select',
								'args' 		=> array(
									'label_for' 	=> 'wpzoom_rcb_settings_pin_description',
									'class' 		=> 'wpzoom-rcb-field',
									'description' 	=> esc_html__( 'Text description to use for Pin Recipe.', 'wpzoom-recipe-card' ),
									'default'		=> 'recipe_name',
									'options' 		=> array(
										'recipe_name' => __( 'Recipe Name', 'wpzoom-recipe-card' ),
										'recipe_summary' => __( 'Recipe Summary', 'wpzoom-recipe-card' ),
										'custom_text' => __( 'Custom Text (Premium Only)', 'wpzoom-recipe-card' ),
									)
								)
							),
						)
					),
				)
			),
			'metadata' => array(
				'tab_id' 		=> 'tab-metadata',
				'tab_title' 	=> __( 'Metadata', 'wpzoom-recipe-card' ),
				'option_group' 	=> 'wpzoom-recipe-card-settings-metadata',
				'option_name' 	=> 'wpzoom-recipe-card-settings',
				'sections' 		=> array(
					array(
						'id' 		=> 'wpzoom_section_taxonomies',
						'title' 	=> __( 'Taxonomies', 'wpzoom-recipe-card' ),
						'page' 		=> 'wpzoom-recipe-card-settings-metadata',
						'callback' 	=> '__return_false',
						'fields' 	=> array(
							array(
								'id' 		=> 'wpzoom_rcb_settings_course_taxonomy',
								'title' 	=> __( 'Course', 'wpzoom-recipe-card' ),
								'type'		=> 'checkbox',
								'args' 		=> array(
									'label_for' 	=> 'wpzoom_rcb_settings_course_taxonomy',
									'class' 		=> 'wpzoom-rcb-field',
									'description' 	=> esc_html__( 'Make Course as taxonomy.', 'wpzoom-recipe-card' ),
									'default'		=> false,
									'is_premium'	=> true
								)
							),
							array(
								'id' 		=> 'wpzoom_rcb_settings_cuisine_taxonomy',
								'title' 	=> __( 'Cuisine', 'wpzoom-recipe-card' ),
								'type'		=> 'checkbox',
								'args' 		=> array(
									'label_for' 	=> 'wpzoom_rcb_settings_cuisine_taxonomy',
									'class' 		=> 'wpzoom-rcb-field',
									'description' 	=> esc_html__( 'Make Cuisine as taxonomy.', 'wpzoom-recipe-card' ),
									'default'		=> false,
									'is_premium'	=> true
								)
							),
							array(
								'id' 		=> 'wpzoom_rcb_settings_difficulty_taxonomy',
								'title' 	=> __( 'Difficulty', 'wpzoom-recipe-card' ),
								'type'		=> 'checkbox',
								'args' 		=> array(
									'label_for' 	=> 'wpzoom_rcb_settings_difficulty_taxonomy',
									'class' 		=> 'wpzoom-rcb-field',
									'description' 	=> esc_html__( 'Make Difficulty as taxonomy.', 'wpzoom-recipe-card' ),
									'default'		=> false,
									'is_premium'	=> true
								)
							),
						)
					)
				)
			)
		);

		$this->register_settings();
	}

	/**
	 * Register all Setting options
	 * 
	 * @return boolean
	 */
	public function register_settings() {
		// filter hook
		$this->settings = apply_filters( 'wpzoom_rcb_before_register_settings', $this->settings );

		if ( empty( $this->settings ) ) {
			return;
		}

		foreach ( $this->settings as $key => $setting ) {
			register_setting( $setting['option_group'], $setting['option_name'] );

			if ( isset( $setting['sections'] ) && is_array( $setting['sections'] ) ) {
				foreach ( $setting['sections'] as $section ) {
					if ( ! isset( $section['id'] ) ) {
						return;
					}
					add_settings_section( $section['id'], $section['title'], $section['callback'], $section['page'] );

					if ( isset( $section['fields'] ) && is_array( $section['fields'] ) ) {
						foreach ( $section['fields'] as $field ) {
							if ( ! isset( $field['id'] ) ) {
								return;
							}

							if ( method_exists( $this->_fields, $field['type'] ) ) {
								$field['callback'] = array( $this->_fields, $field['type'] );
							} else {
								$field['callback'] = '__return_false';
							}

							add_settings_field( $field['id'], $field['title'], $field['callback'], $section['page'], $section['id'], $field['args'] );
						}
					}
				}
			}
		}

		return true;
	}

	/**
	 * HTML output for Setting page
	 */
	public function settings_page() {
		// check user capabilities
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		
		// add error/update messages
		
		// check if the user have submitted the settings
		// wordpress will add the "settings-updated" $_GET parameter to the url
		if ( isset( $_GET['settings-updated'] ) ) {
			// add settings saved message with the class of "updated"
			add_settings_error( 'wpzoom_rcb_messages', 'wpzoom_rcb_message', __( 'Settings Saved', 'wpzoom-recipe-card' ), 'updated' );
		}
	?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

			<div class="notice notice-info">
				<p><?php echo sprintf( __( 'At the moment the %1$sPremium%2$s version of %1$sWPZOOM Recipe Card Plugin%2$s is not available. We are trying to develop it as soon as possible!' ), '<strong>', '</strong>' ); ?></p>
			</div>

			<form action="options.php" method="post">
				<ul class="wp-tab-bar">
					<?php foreach ( $this->settings as $setting ): ?>
						<?php if ( isset( $setting['is_active'] ) ): ?>
							<li class="wp-tab-active"><a href="#<?php echo $setting['tab_id'] ?>"><?php echo $setting['tab_title'] ?></a></li>
						<?php else: ?>
							<li><a href="#<?php echo $setting['tab_id'] ?>"><?php echo $setting['tab_title'] ?></a></li>
						<?php endif ?>
					<?php endforeach ?>
					<li id="wpzoom_rcb_settings_save"><?php submit_button( 'Save Settings', 'primary', 'wpzoom_rcb_settings_save', false ); ?></li>
				</ul>
				<?php foreach ( $this->settings as $setting ): ?>
					<?php if ( isset( $setting['is_active'] ) ): ?>
						<div class="wp-tab-panel" id="<?php echo $setting['tab_id'] ?>">
							<?php 
								settings_fields( $setting['option_group'] );
								do_settings_sections( $setting['option_group'] );
							?>
						</div>
					<?php else: ?>
						<div class="wp-tab-panel" id="<?php echo $setting['tab_id'] ?>" style="display: none;">
							<?php 
								settings_fields( $setting['option_group'] );
								do_settings_sections( $setting['option_group'] );
							?>
						</div>
					<?php endif ?>
				<?php endforeach ?>
			</form>
		</div>
	<?php
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

	// section callbacks can accept an $args parameter, which is an array.
	// $args have the following keys defined: title, id, callback.
	// the values are defined at the add_settings_section() function.
	public function section_defaults_cb( $args ) {
	?>
	 	<p id="<?php echo esc_attr( $args['id'] ); ?>"><?php esc_html_e( 'Default configuration for new Recipe Cards.', 'wpzoom-recipe-card' ) ?></p>
	<?php
	}

	public function section_rating_feature_cb( $args ) {
	?>
	 	<p id="<?php echo esc_attr( $args['id'] ); ?>"><?php esc_html_e( 'Recipe Rating shown in the Recipe Card and Recipe Metadata.', 'wpzoom-recipe-card' ) ?></p>
	<?php
	}

	public function section_recipe_template_cb( $args ) {
	?>
	 	<p id="<?php echo esc_attr( $args['id'] ); ?>"><?php esc_html_e( 'You will get access to more Recipe Templates with Premium version.', 'wpzoom-recipe-card' ) ?></p>
	<?php
	}
}

new WPZOOM_Settings();
