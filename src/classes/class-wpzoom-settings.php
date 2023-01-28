<?php
/**
 * Class Settings Page
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
class WPZOOM_Settings {
	/**
	 * Option name
	 */
	public static $option = 'wpzoom-recipe-card-settings';

	/**
	 * Store all default settings options.
	 *
	 * @static
	 */
	public static $defaults = array();

	/**
	 * Store all settings options.
	 *
	 * @static
	 */
	public static $settings = array();

	/**
	 * Active Tab.
	 */
	public static $active_tab;

	/**
	 * Class WPZOOM_Settings_Fields instance.
	 */
	public $_fields;

	/**
	 * Store Settings options.
	 */
	public static $options = array();

	/**
	 * License key
	 */
	public static $license_key = false;

	/**
	 * License status
	 */
	public static $license_status = false;

	/**
	 * The Constructor.
	 */
	public function __construct() {
		global $pagenow;

		self::$options = get_option( self::$option );

		// Check what page we are on.
		$page = isset( $_GET['page'] ) ? sanitize_text_field( $_GET['page'] ) : '';

		if ( is_admin() ) {
			add_action( 'admin_init', array( $this, 'settings_init' ) );
			add_action( 'admin_init', array( $this, 'set_defaults' ) );

			// Include admin scripts & styles
			add_action( 'admin_enqueue_scripts', array( $this, 'scripts' ) );

			// Do ajax request
			add_action( 'wp_ajax_wpzoom_reset_settings', array( $this, 'reset_settings' ) );
			add_action( 'wp_ajax_wpzoom_welcome_banner_close', array( $this, 'welcome_banner_close' ) );

			// Only load if we are actually on the settings page.
			if ( WPZOOM_RCB_SETTINGS_PAGE === $page ) {
				add_action( 'wpzoom_rcb_admin_page', array( $this, 'settings_page' ) );

				// Action for welcome banner
				add_action( 'wpzoom_rcb_welcome_banner', array( $this, 'welcome' ) );
			}

			$this->_fields = new WPZOOM_Settings_Fields();
		}
	}

	/**
	 * Set default values for setting options.
	 */
	public function set_defaults() {
		// Set active tab
		self::$active_tab = isset( $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : 'tab-general';

		self::$defaults = self::get_defaults();

		if ( empty( self::$defaults ) ) {
			return false;
		}

		// If 'wpzoom-recipe-card-settings' is empty update option with defaults values
		if ( empty( self::$options ) ) {
			self::update_option( self::$defaults );
		}

		// If new setting is added, update 'wpzoom-recipe-card-settings' option
		if ( ! empty( self::$options ) ) {
			$new_settings = array_diff_key( self::$defaults, self::$options );
			if ( ! empty( $new_settings ) ) {
				self::update_option( array_merge( self::$options, $new_settings ) );
			}
		}

		return apply_filters( 'wpzoom_rcb_set_settings_defaults', self::$defaults );
	}

	/**
	 * Update option value
	 *
	 * @param string|array $value
	 * @param string       $option
	 */
	public static function update_option( $value, $option = '', $autoload = null ) {
		if ( empty( $option ) ) {
			$option = self::$option;
		}

		if ( self::$options !== false ) {
			// The option already exists, so we just update it.
			update_option( $option, $value, $autoload );
		} else {
			// The option hasn't been added yet. We'll add it with $autoload set to 'no'.
			$deprecated = null;
			$autoload   = 'no';
			add_option( $option, $value, $deprecated, $autoload );
		}
	}

	/**
	 * Get default values of setting options.
	 *
	 * @static
	 */
	public static function get_defaults() {
		$defaults = array();

		foreach ( self::$settings as $key => $setting ) {
			if ( isset( $setting['sections'] ) && is_array( $setting['sections'] ) ) {
				foreach ( $setting['sections'] as $section ) {
					if ( isset( $section['fields'] ) && is_array( $section['fields'] ) ) {
						foreach ( $section['fields'] as $field ) {
							if ( isset( $field['args']['default'] ) ) {
								$defaults[ $field['id'] ] = (string) $field['args']['default'];
							}
						}
					}
				}
			}
		}

		return $defaults;
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
	 * Get license key
	 *
	 * @since 1.2.0
	 * @return string The License key
	 */
	public static function get_license_key() {
		return self::$license_key;
	}

	/**
	 * Get license status
	 *
	 * @since 1.2.0
	 * @return string The License status
	 */
	public static function get_license_status() {
		return self::$license_status;
	}

	/**
	 * Get setting options
	 *
	 * @since 1.2.0
	 * @return array
	 */
	public static function get_settings() {
		return apply_filters( 'wpzoom_rcb_get_settings', self::$options );
	}

	/**
	 * Get setting option value
	 *
	 * @since 1.2.0
	 * @param string $option  Option name
	 * @return string|boolean
	 */
	public static function get( $option ) {
		return isset( self::$options[ $option ] ) ? self::$options[ $option ] : false;
	}

	/**
	 * Welcome banner
	 * Show banner after user activate plugin
	 *
	 * @since 1.2.0
	 * @return void
	 */
	public function welcome() {
		$welcome_transient = get_transient( 'wpzoom_rcb_welcome_banner' );

		if ( false === $welcome_transient ) {
			return;
		}

		$upgrade_url = WPZOOM_Plugin_Activator::get_upgrade_url();

		ob_start();
		?>
		<div id="wpzoom-recipe-card-welcome-banner" class="wpzoom-rcb-welcome">
			<div class="inner-wrap">
				<i class="wpzoom-rcb-welcome-icon dashicons dashicons-yes"></i>
				<h3 class="wpzoom-rcb-welcome-title"><?php _e( 'Thank you for installing Recipe Card Blocks!', 'recipe-card-blocks-by-wpzoom' ); ?></h3>
				<p class="wpzoom-rcb-welcome-description"><?php _e( 'If you need help getting started with Recipe Card Blocks, please click on the links below.', 'recipe-card-blocks-by-wpzoom' ); ?></p>
				<div class="wpzoom-rcb-welcome-buttons">
					<a href="https://recipecard.io/documentation/" target="_blank" class="wpzoom-doc-link"><?php _e( 'Documentation', 'recipe-card-blocks-by-wpzoom' ); ?></a>
					<a href="https://wordpress.org/support/plugin/recipe-card-blocks-by-wpzoom/" target="_blank" class="wpzoom-support-link"><?php _e( 'Support Forum', 'recipe-card-blocks-by-wpzoom' ); ?></a>
					<a href="https://recipecard.io/support/tickets/" target="_blank" class="wpzoom-support-link"><strong><?php _e( 'Premium Support', 'recipe-card-blocks-by-wpzoom' ); ?></strong></a>
					<a href="<?php echo esc_url( $upgrade_url ); ?>" target="_blank" class="wpzoom-pro-link" style="color:#FFA921;"><strong><?php _e( 'Get the PRO version &rarr;', 'recipe-card-blocks-by-wpzoom' ); ?></strong></a>
				</div>
			</div>
			<a href="#wpzoom-recipe-card-welcome-banner" class="wpzoom-rcb-welcome-close"><i class="dashicons dashicons-no-alt"></i><?php _e( 'Close', 'recipe-card-blocks-by-wpzoom' ); ?></a>
		</div>
		<?php

		$output = ob_get_contents();
		ob_end_clean();

		echo wp_kses_post( $output );
	}

	/**
	 * Initilize all settings
	 */
	public function settings_init() {
		$premium_extended_badge = '<span class="wpzoom-rcb-badge wpzoom-rcb-field-is_premium">' . __( 'Professional License Required', 'recipe-card-blocks-by-wpzoom' ) . '</span>';
		$premium_badge = '<span class="wpzoom-rcb-badge wpzoom-rcb-field-is_premium">' . __( 'PRO Feature', 'recipe-card-blocks-by-wpzoom' ) . '</span>';
		$soon_badge    = '<span class="wpzoom-rcb-badge wpzoom-rcb-field-is_coming_soon">' . __( 'Coming Soon', 'recipe-card-blocks-by-wpzoom' ) . '</span>';

		self::$settings = array(
			'general'     => array(
				'tab_id'       => 'tab-general',
				'tab_title'    => __( 'General', 'recipe-card-blocks-by-wpzoom' ),
				'option_group' => 'wpzoom-recipe-card-settings-general',
				'option_name'  => self::$option,
				'sections'     => array(
					array(
						'id'       => 'wpzoom_section_general',
						'title'    => __( 'Defaults', 'recipe-card-blocks-by-wpzoom' ),
						'page'     => 'wpzoom-recipe-card-settings-general',
						'callback' => array( $this, 'section_defaults_cb' ),
						'fields'   => array(
							array(
								'id'    => 'wpzoom_rcb_settings_display_course',
								'title' => __( 'Display Course', 'recipe-card-blocks-by-wpzoom' ),
								'type'  => 'checkbox',
								'args'  => array(
									'label_for'   => 'wpzoom_rcb_settings_display_course',
									'class'       => 'wpzoom-rcb-field',
									'description' => esc_html__( 'Show course by default', 'recipe-card-blocks-by-wpzoom' ),
									'default'     => true,
									'preview'     => true,
									'preview_pos' => 'bottom',
								),
							),
							array(
								'id'    => 'wpzoom_rcb_settings_display_cuisine',
								'title' => __( 'Display Cuisine', 'recipe-card-blocks-by-wpzoom' ),
								'type'  => 'checkbox',
								'args'  => array(
									'label_for'   => 'wpzoom_rcb_settings_display_cuisine',
									'class'       => 'wpzoom-rcb-field',
									'description' => esc_html__( 'Show cuisine by default', 'recipe-card-blocks-by-wpzoom' ),
									'default'     => true,
									'preview'     => true,
									'preview_pos' => 'bottom',
								),
							),
							array(
								'id'    => 'wpzoom_rcb_settings_display_difficulty',
								'title' => __( 'Display Difficulty', 'recipe-card-blocks-by-wpzoom' ),
								'type'  => 'checkbox',
								'args'  => array(
									'label_for'   => 'wpzoom_rcb_settings_display_difficulty',
									'class'       => 'wpzoom-rcb-field',
									'description' => esc_html__( 'Show difficulty by default', 'recipe-card-blocks-by-wpzoom' ),
									'default'     => true,
									'preview'     => true,
									'preview_pos' => 'bottom',
								),
							),
							array(
								'id'    => 'wpzoom_rcb_settings_display_author',
								'title' => __( 'Display Author', 'recipe-card-blocks-by-wpzoom' ),
								'type'  => 'checkbox',
								'args'  => array(
									'label_for'   => 'wpzoom_rcb_settings_display_author',
									'class'       => 'wpzoom-rcb-field',
									'description' => esc_html__( 'Show author by default', 'recipe-card-blocks-by-wpzoom' ),
									'default'     => true,
									'preview'     => true,
									'preview_pos' => 'bottom',
								),
							),
							array(
								'id'    => 'wpzoom_rcb_settings_author_custom_name',
								'title' => __( 'Default Author Name', 'recipe-card-blocks-by-wpzoom' ),
								'type'  => 'input',
								'args'  => array(
									'label_for'   => 'wpzoom_rcb_settings_author_custom_name',
									'class'       => 'wpzoom-rcb-field',
									'description' => esc_html__( 'You can add a custom author name for all new Recipe Cards. By default, the post author name is shown.', 'recipe-card-blocks-by-wpzoom' ),
									'default'     => '',
									'type'        => 'text',
								),
							),
							array(
								'id'    => 'wpzoom_rcb_settings_details_title',
								'title' => __( 'Default Details Title', 'recipe-card-blocks-by-wpzoom' ),
								'type'  => 'input',
								'args'  => array(
									'label_for'   => 'wpzoom_rcb_settings_details_title',
									'class'       => 'wpzoom-rcb-field',
									'description' => esc_html__( 'Add your custom Details title for new or existing Recipe Cards.', 'recipe-card-blocks-by-wpzoom' ),
									'default'     => __( 'Details', 'recipe-card-blocks-by-wpzoom' ),
									'type'        => 'text',
								),
							),
							array(
								'id'    => 'wpzoom_rcb_settings_ingredients_title',
								'title' => __( 'Default Ingredients Title', 'recipe-card-blocks-by-wpzoom' ),
								'type'  => 'input',
								'args'  => array(
									'label_for'   => 'wpzoom_rcb_settings_ingredients_title',
									'class'       => 'wpzoom-rcb-field',
									'description' => esc_html__( 'Add your custom Ingredients title for new or existing Recipe Cards.', 'recipe-card-blocks-by-wpzoom' ),
									'default'     => __( 'Ingredients', 'recipe-card-blocks-by-wpzoom' ),
									'type'        => 'text',
								),
							),
							array(
								'id'    => 'wpzoom_rcb_settings_steps_title',
								'title' => __( 'Default Directions Title', 'recipe-card-blocks-by-wpzoom' ),
								'type'  => 'input',
								'args'  => array(
									'label_for'   => 'wpzoom_rcb_settings_steps_title',
									'class'       => 'wpzoom-rcb-field',
									'description' => esc_html__( 'Add your custom Directions title for new or existing Recipe Cards.', 'recipe-card-blocks-by-wpzoom' ),
									'default'     => __( 'Directions', 'recipe-card-blocks-by-wpzoom' ),
									'type'        => 'text',
								),
							),

                            array(
                                'id'    => 'wpzoom_rcb_settings_cta_delimiter_eq',
                                'title' => '',
                                'type'  => 'subsection',
                                'args'  => array(
                                    'label_for' => 'wpzoom_rcb_settings_cta_delimiter_eq',
                                    'class'     => 'wpzoom-rcb-field wpzoom-rcb-field-delimiter',
                                ),
                            ),

							array(
								'id'    => 'wpzoom_rcb_settings_video_title',
								'title' => __( 'Recipe Video Title', 'recipe-card-blocks-by-wpzoom' ),
								'type'  => 'input',
								'args'  => array(
									'label_for'   => 'wpzoom_rcb_settings_video_title',
									'class'       => 'wpzoom-rcb-field',
									'description' => esc_html__( 'Add your custom Recipe video title for new or existing Recipe Cards.', 'recipe-card-blocks-by-wpzoom' ),
									'default'     => __( 'Recipe Video', 'recipe-card-blocks-by-wpzoom' ),
									'type'        => 'text',
								),
							),
							array(
								'id'    => 'wpzoom_rcb_settings_notes_title',
								'title' => __( 'Default Notes Title', 'recipe-card-blocks-by-wpzoom' ),
								'type'  => 'input',
								'args'  => array(
									'label_for'   => 'wpzoom_rcb_settings_notes_title',
									'class'       => 'wpzoom-rcb-field',
									'description' => esc_html__( 'Add your custom Notes title for new or existing Recipe Cards.', 'recipe-card-blocks-by-wpzoom' ),
									'default'     => __( 'Notes', 'recipe-card-blocks-by-wpzoom' ),
									'type'        => 'text',
								),
							),
							array(
								'id'    => 'wpzoom_rcb_settings_heading_content_align',
								'title' => __( 'Recipe Title Alignment', 'recipe-card-blocks-by-wpzoom' ),
								'type'  => 'select',
								'args'  => array(
									'label_for' => 'wpzoom_rcb_settings_heading_content_align',
									'class'     => 'wpzoom-rcb-field',
									'default'   => 'left',
									'options'   => array(
										'left'   => __( 'Left', 'recipe-card-blocks-by-wpzoom' ),
										'center' => __( 'Center', 'recipe-card-blocks-by-wpzoom' ),
										'right'  => __( 'Right', 'recipe-card-blocks-by-wpzoom' ),
									),
								),
							),
							array(
								'id'    => 'wpzoom_rcb_settings_ingredients_strikethrough',
								'title' => __( 'Ingredients Strikethrough', 'recipe-card-blocks-by-wpzoom' ),
								'type'  => 'checkbox',
								'args'  => array(
									'label_for'   => 'wpzoom_rcb_settings_ingredients_strikethrough',
									'class'       => 'wpzoom-rcb-field',
									'description' => esc_html__( 'Enable strikethrough for ingredients on hover or when selected.', 'recipe-card-blocks-by-wpzoom' ),
									'default'     => true,
									'disabled'    => false,
									'preview'     => true,
									'preview_pos' => 'bottom',
								),
							),
							array(
								'id'    => 'wpzoom_rcb_settings_footer_copyright',
								'title' => __( 'Footer Credit', 'recipe-card-blocks-by-wpzoom' ),
								'type'  => 'checkbox',
								'args'  => array(
									'label_for'   => 'wpzoom_rcb_settings_footer_copyright',
									'class'       => 'wpzoom-rcb-field',
									'description' => esc_html__( 'Disable Footer Credit. Uncheck this option if you want to show your support for this plugin.', 'recipe-card-blocks-by-wpzoom' ),
									'default'     => true,
									'preview'     => true,
									'preview_pos' => 'bottom',
								),
							),
						),
					),
					array(
						'id'       => 'wpzoom_section_recipe_details',
						'title'    => __( 'Recipe Details', 'recipe-card-blocks-by-wpzoom' ),
						'page'     => 'wpzoom-recipe-card-settings-general',
						'callback' => '__return_false',
						'fields'   => array(
							array(
								'id'    => 'wpzoom_rcb_settings_display_servings',
								'title' => __( 'Display Servings', 'recipe-card-blocks-by-wpzoom' ),
								'type'  => 'checkbox',
								'args'  => array(
									'label_for'   => 'wpzoom_rcb_settings_display_servings',
									'class'       => 'wpzoom-rcb-field',
									'description' => esc_html__( 'Show servings by default', 'recipe-card-blocks-by-wpzoom' ),
									'default'     => true,
									'preview'     => true,
									'preview_pos' => 'bottom',
								),
							),
							array(
								'id'    => 'wpzoom_rcb_settings_display_preptime',
								'title' => __( 'Display Preparation Time', 'recipe-card-blocks-by-wpzoom' ),
								'type'  => 'checkbox',
								'args'  => array(
									'label_for'   => 'wpzoom_rcb_settings_display_preptime',
									'class'       => 'wpzoom-rcb-field',
									'description' => esc_html__( 'Show preparation time by default', 'recipe-card-blocks-by-wpzoom' ),
									'default'     => true,
									'preview'     => true,
									'preview_pos' => 'top',
								),
							),
							array(
								'id'    => 'wpzoom_rcb_settings_display_cookingtime',
								'title' => __( 'Display Cooking Time', 'recipe-card-blocks-by-wpzoom' ),
								'type'  => 'checkbox',
								'args'  => array(
									'label_for'   => 'wpzoom_rcb_settings_display_cookingtime',
									'class'       => 'wpzoom-rcb-field',
									'description' => esc_html__( 'Show cooking time by default', 'recipe-card-blocks-by-wpzoom' ),
									'default'     => true,
									'preview'     => true,
									'preview_pos' => 'top',
								),
							),
							array(
								'id'    => 'wpzoom_rcb_settings_display_totaltime',
								'title' => __( 'Display Total Time', 'recipe-card-blocks-by-wpzoom' ),
								'type'  => 'checkbox',
								'args'  => array(
									'label_for'   => 'wpzoom_rcb_settings_display_totaltime',
									'class'       => 'wpzoom-rcb-field',
									'description' => esc_html__( 'Show total time by default', 'recipe-card-blocks-by-wpzoom' ),
									'default'     => false,
									'preview'     => false,
									'preview_pos' => 'top',
								),
							),
							array(
								'id'    => 'wpzoom_rcb_settings_display_calories',
								'title' => __( 'Display Calories', 'recipe-card-blocks-by-wpzoom' ),
								'type'  => 'checkbox',
								'args'  => array(
									'label_for'   => 'wpzoom_rcb_settings_display_calories',
									'class'       => 'wpzoom-rcb-field',
									'description' => esc_html__( 'Show calories by default', 'recipe-card-blocks-by-wpzoom' ),
									'default'     => true,
									'preview'     => true,
									'preview_pos' => 'top',
								),
							),
						),
					),

                    array(
                        'id'       => 'wpzoom_section_equipment_features',
                        'title'    => __( 'Equipment', 'recipe-card-blocks-by-wpzoom' ),
                        'page'     => 'wpzoom-recipe-card-settings-general',
                        'callback' => array( $this, 'section_equipment_feature_cb' ),
                        'fields'   => array(

                            array(
                                'id'    => 'wpzoom_rcb_settings_display_equipment',
                                'title' => __( 'Display Equipment', 'recipe-card-blocks-by-wpzoom' ),
                                'type'  => 'checkbox',
                                'args'  => array(
                                    'label_for'   => 'wpzoom_rcb_settings_display_equipment',
                                    'class'       => 'wpzoom-rcb-field',
                                    'description' => esc_html__( 'Show equipment by default', 'recipe-card-blocks-by-wpzoom' ),
                                    'default'     => true,
                                    'preview'     => true,
                                    'disabled'    => true,
                                    'badge'       => $premium_badge,
                                    'preview_pos' => 'bottom',
                                ),
                            ),

                            array(
                                'id'    => 'wpzoom_rcb_settings_equipment_title',
                                'title' => __( 'Default Equipment Title', 'recipe-card-blocks-by-wpzoom' ),
                                'type'  => 'input',
                                'args'  => array(
                                    'label_for'   => 'wpzoom_rcb_settings_equipment_title',
                                    'class'       => 'wpzoom-rcb-field',
                                    'description' => esc_html__( 'Add your custom Equipment title for all new Recipe Cards.', 'recipe-card-blocks-by-wpzoom' ),
                                    'default'     => __( 'Equipment', 'recipe-card-blocks-by-wpzoom' ),
                                    'type'        => 'text',
                                    'disabled'    => true,
                                    'badge'       => $premium_badge,
                                ),
                            ),
                            array(
                                'id'    => 'wpzoom_rcb_settings_equipment_location',
                                'title' => __( 'Default Equipment Location', 'recipe-card-blocks-by-wpzoom' ),
                                'type'  => 'select',
                                'args'  => array(
                                    'label_for' => 'wpzoom_rcb_settings_equipment_location',
                                    'class'     => 'wpzoom-rcb-field',
                                    'default'   => 'after_directions',
                                    'disabled'    => true,
                                    'badge'       => $premium_badge,
                                    'options'   => array(
                                        'before_details'      => __( 'Before Details', 'recipe-card-blocks-by-wpzoom' ),
                                        'after_details'       => __( 'After Details', 'recipe-card-blocks-by-wpzoom' ),
                                        'after_top_labels'    => __( 'After Top Labels', 'recipe-card-blocks-by-wpzoom' ),
                                        'after_summary'       => __( 'After Summary', 'recipe-card-blocks-by-wpzoom' ),
                                        'after_ingredients'   => __( 'After Ingredients', 'recipe-card-blocks-by-wpzoom' ),
                                        'after_directions'    => __( 'After Directions', 'recipe-card-blocks-by-wpzoom' ),
                                        'after_video'         => __( 'After Video', 'recipe-card-blocks-by-wpzoom' ),
                                        'after_notes'         => __( 'After Notes', 'recipe-card-blocks-by-wpzoom' ),
                                        'after_bottom_labels' => __( 'After Bottom Labels', 'recipe-card-blocks-by-wpzoom' ),
                                    ),
                                ),
                            ),
                        ),
                    ),

					array(
                        'id'       => 'wpzoom_section_unit_system',
                        'title'    => __( 'Unit System', 'recipe-card-blocks-by-wpzoom' ),
                        'page'     => 'wpzoom-recipe-card-settings-general',
                        'callback' => '',
                        'fields'   => array(

							array(
								'id'    => 'wpzoom_rcb_settings_display_unit_system',
								'title' => __( 'Display Unit System', 'recipe-card-blocks-by-wpzoom' ),
								'type'  => 'checkbox',
								'args'  => array(
									'label_for'   => 'wpzoom_rcb_settings_display_unit_system',
									'class'       => 'wpzoom-rcb-field',
									'description' => esc_html__( 'Show Unit System by default', 'recipe-card-blocks-by-wpzoom' ),
									'default'     => true,
									'disabled'    => true,
									'preview'     => true,
									'badge'       => $premium_extended_badge,
									'preview_pos' => 'top',
								),
							),
							array(
								'id'    => 'wpzoom_rcb_settings_default_unit_system',
								'title' => __( 'Default Unit System', 'recipe-card-blocks-by-wpzoom' ),
								'type'  => 'select',
								'args'  => array(
									'label_for'   => 'wpzoom_rcb_settings_default_unit_system',
									'class'       => 'wpzoom-rcb-field',
									'default'     => 'select',
									'disabled'    => true,
									'preview'     => true,
									'badge'       => $premium_extended_badge,
									'options'   => array(
										'select'   => __( 'Select unit system', 'recipe-card-blocks-by-wpzoom' ),
										'us'   => __( 'US customary', 'recipe-card-blocks-by-wpzoom' ),
										'metric' => __( 'Metric', 'recipe-card-blocks-by-wpzoom' ),
									),
								),
							),
                        ),
                    ),


					array(
						'id'       => 'wpzoom_section_recipe_miscellaneous',
						'title'    => __( 'Editing', 'recipe-card-blocks-by-wpzoom' ),
						'page'     => 'wpzoom-recipe-card-settings-general',
						'callback' => '__return_false',
						'fields'   => array(
							array(
								'id'    => 'wpzoom_rcb_settings_sections_expanded',
								'title' => __( 'Expand all Block sections?', 'recipe-card-blocks-by-wpzoom' ),
								'type'  => 'checkbox',
								'args'  => array(
									'label_for'   => 'wpzoom_rcb_settings_sections_expanded',
									'class'       => 'wpzoom-rcb-field',
									'description' => esc_html__( 'Enable if you want all sections from the block settings to be expanded', 'recipe-card-blocks-by-wpzoom' ),
									'default'     => false,
									'preview'     => false,
								),
							),
						),
					),
				),
			),
			'appearance'  => array(
				'tab_id'       => 'tab-appearance',
				'tab_title'    => __( 'Appearance', 'recipe-card-blocks-by-wpzoom' ),
				'option_group' => 'wpzoom-recipe-card-settings-appearance',
				'option_name'  => self::$option,
				'sections'     => array(
					array(
						'id'       => 'wpzoom_section_recipe_template',
						'title'    => __( 'Recipe Template', 'recipe-card-blocks-by-wpzoom' ),
						'page'     => 'wpzoom-recipe-card-settings-appearance',
						'callback' => array( $this, 'section_recipe_template_cb' ),
						'fields'   => array(
							array(
								'id'    => 'wpzoom_rcb_settings_template',
								'title' => __( 'Default Template', 'recipe-card-blocks-by-wpzoom' ),
								'type'  => 'select',
								'args'  => array(
									'label_for'   => 'wpzoom_rcb_settings_template',
									'class'       => 'wpzoom-rcb-field',
									'description' => esc_html__( 'Default template to use for all Recipe Cards.', 'recipe-card-blocks-by-wpzoom' ),
									'default'     => 'default',
									'options'     => array(
										'default'   => __( 'Default', 'recipe-card-blocks-by-wpzoom' ),
										'newdesign' => __( 'New Design', 'recipe-card-blocks-by-wpzoom' ),
										'simple'    => __( 'Simple Design', 'recipe-card-blocks-by-wpzoom' ),
									),
								),
							),

                            array(
                                'id'    => 'wpzoom_rcb_settings_primary_color',
                                'title' => __( 'Default Primary Color', 'recipe-card-blocks-by-wpzoom' ),
                                'type'  => 'colorpicker',
                                'args'  => array(
                                    'label_for' => 'wpzoom_rcb_settings_primary_color',
                                    'class'     => 'wpzoom-rcb-field',
                                    'default'   => '#F2A123',
                                    'disabled'    => true,
                                    'badge'       => $premium_badge,
                                ),
                            ),
						),
					),


                    array(
                        'id'       => 'wpzoom_section_recipe_call_to_action',
                        'title'    => __( 'Footer Call To Action', 'recipe-card-blocks-by-wpzoom' ),
                        'page'     => 'wpzoom-recipe-card-settings-appearance',
                        'callback' => array( $this, 'section_recipe_call_to_action_cb' ),
                        'fields'   => array(
                            array(
                                'id'    => 'wpzoom_rcb_settings_cta_target',
                                'title' => __( 'Enable Footer Call-to-actions', 'recipe-card-blocks-by-wpzoom' ),
                                'type'  => 'checkbox',
                                'args'  => array(
                                    'label_for' => 'wpzoom_rcb_settings_cta_target',
                                    'class'     => 'wpzoom-rcb-field',
                                    'default'   => true,
                                    'disabled'    => true,
                                    'badge'       => $premium_badge,

                                ),
                            ),
                        ),
                    ),

					array(
						'id'       => 'wpzoom_section_recipe_nutrition',
						'title'    => __( 'Nutrition', 'recipe-card-blocks-by-wpzoom' ),
						'page'     => 'wpzoom-recipe-card-settings-appearance',
						'callback' => '__return_false',
						'fields'   => array(
							array(
								'id'    => 'wpzoom_rcb_settings_nutrition_layout',
								'title' => __( 'Layout Orientation', 'recipe-card-blocks-by-wpzoom' ),
								'type'  => 'select',
								'args'  => array(
									'label_for'   => 'wpzoom_rcb_settings_nutrition_layout',
									'class'       => 'wpzoom-rcb-field',
									'description' => esc_html__( 'Default layout to use for all Nutrition block.', 'recipe-card-blocks-by-wpzoom' ),
									'default'     => 'vertical',
									'options'     => array(
										'vertical'   => __( 'Vertical', 'recipe-card-blocks-by-wpzoom' ),
										'horizontal' => __( 'Horizontal', 'recipe-card-blocks-by-wpzoom' ),
									),
								),
							),
						),
					),

				),
			),


            'miscellaneous' => array(
                'tab_id'       => 'tab-miscellaneous',
                'tab_title'    => __( 'Miscellaneous', 'recipe-card-blocks-by-wpzoom' ),
                'option_group' => 'wpzoom-recipe-card-settings-miscellaneous',
                'option_name'  => self::$option,
                'sections'     => array(
                    array(
                        'id'       => 'wpzoom_section_snippets',
                        'title'    => __( 'Recipe Buttons', 'recipe-card-blocks-by-wpzoom' ),
                        'page'     => 'wpzoom-recipe-card-settings-miscellaneous',
                        'callback' => array( $this, 'section_recipe_snippets' ),
                        'fields'   => array(
                            array(
                                'id'    => 'wpzoom_rcb_settings_display_snippets',
                                'title' => __( 'Automatically add Buttons', 'recipe-card-blocks-by-wpzoom' ),
                                'type'  => 'checkbox',
                                'args'  => array(
                                    'label_for'   => 'wpzoom_rcb_settings_display_snippets',
                                    'class'       => 'wpzoom-rcb-field',
                                    'description' => __( 'Automatically display buttons above the post content.', 'recipe-card-blocks-by-wpzoom' ),
                                    'default'     => false,
                                    'preview'     => true,
                                    'preview_pos' => 'bottom',
                                ),
                            ),
                            array(
                                'id'    => 'wpzoom_rcb_settings_jump_to_recipe_text',
                                'title' => __( 'Jump to Recipe Text', 'recipe-card-blocks-by-wpzoom' ),
                                'type'  => 'input',
                                'args'  => array(
                                    'label_for'   => 'wpzoom_rcb_settings_jump_to_recipe_text',
                                    'class'       => 'wpzoom-rcb-field',
                                    'description' => esc_html__( 'Add custom text for Jump to Recipe button.', 'recipe-card-blocks-by-wpzoom' ),
                                    'default'     => __( 'Jump to Recipe', 'recipe-card-blocks-by-wpzoom' ),
                                    'type'        => 'text',
                                ),
                            ),
                            array(
                                'id'    => 'wpzoom_rcb_settings_print_recipe_text',
                                'title' => __( 'Print Recipe Text', 'recipe-card-blocks-by-wpzoom' ),
                                'type'  => 'input',
                                'args'  => array(
                                    'label_for'   => 'wpzoom_rcb_settings_print_recipe_text',
                                    'class'       => 'wpzoom-rcb-field',
                                    'description' => esc_html__( 'Add custom text for Print Recipe button.', 'recipe-card-blocks-by-wpzoom' ),
                                    'default'     => __( 'Print Recipe', 'recipe-card-blocks-by-wpzoom' ),
                                    'type'        => 'text',
                                ),
                            ),
                        ),
                    ),
                    array(
                        'id'       => 'wpzoom_section_print',
                        'title'    => __( 'Print', 'recipe-card-blocks-by-wpzoom' ),
                        'page'     => 'wpzoom-recipe-card-settings-miscellaneous',
                        'callback' => '__return_false',
                        'fields'   => array(
                            array(
                                'id'    => 'wpzoom_rcb_settings_display_print',
                                'title' => __( 'Display Print Button', 'recipe-card-blocks-by-wpzoom' ),
                                'type'  => 'checkbox',
                                'args'  => array(
                                    'label_for'   => 'wpzoom_rcb_settings_display_print',
                                    'class'       => 'wpzoom-rcb-field',
                                    'description' => esc_html__( 'Show Print button in recipe card', 'recipe-card-blocks-by-wpzoom' ),
                                    'default'     => true,
                                    'preview'     => true,
                                    'preview_pos' => 'bottom',
                                ),
                            ),
                            array(
                                'id'    => 'wpzoom_rcb_settings_print_only_published_posts',
                                'title' => __( 'Print only Published Posts', 'recipe-card-blocks-by-wpzoom' ),
                                'type'  => 'checkbox',
                                'args'  => array(
                                    'label_for'   => 'wpzoom_rcb_settings_print_only_published_posts',
                                    'class'       => 'wpzoom-rcb-field',
                                    'description' => esc_html__( 'Redirect visitors to the homepage when trying to print a recipe that has not been published yet.', 'recipe-card-blocks-by-wpzoom' ),
                                    'default'     => false,
                                ),
                            ),
                            array(
                                'id'    => 'wpzoom_rcb_settings_print_show_image',
                                'title' => __( 'Recipe Image', 'recipe-card-blocks-by-wpzoom' ),
                                'type'  => 'checkbox',
                                'args'  => array(
                                    'label_for'   => 'wpzoom_rcb_settings_print_show_image',
                                    'class'       => 'wpzoom-rcb-field',
                                    'description' => esc_html__( 'Show Recipe Image on print sheet.', 'recipe-card-blocks-by-wpzoom' ),
                                    'default'     => true,
                                ),
                            ),
                            array(
                                'id'    => 'wpzoom_rcb_settings_print_show_details',
                                'title' => __( 'Recipe Details', 'recipe-card-blocks-by-wpzoom' ),
                                'type'  => 'checkbox',
                                'args'  => array(
                                    'label_for'   => 'wpzoom_rcb_settings_print_show_details',
                                    'class'       => 'wpzoom-rcb-field',
                                    'description' => esc_html__( 'Show Recipe Details (servings, preparation time, cooking time, calories) on print sheet.', 'recipe-card-blocks-by-wpzoom' ),
                                    'default'     => true,
                                ),
                            ),
                            array(
                                'id'    => 'wpzoom_rcb_settings_print_show_summary_text',
                                'title' => __( 'Summary Text', 'recipe-card-blocks-by-wpzoom' ),
                                'type'  => 'checkbox',
                                'args'  => array(
                                    'label_for'   => 'wpzoom_rcb_settings_print_show_summary_text',
                                    'class'       => 'wpzoom-rcb-field',
                                    'description' => esc_html__( 'Show Recipe Summary text on print sheet.', 'recipe-card-blocks-by-wpzoom' ),
                                    'default'     => false,
                                ),
                            ),
                            array(
                                'id'    => 'wpzoom_rcb_settings_print_show_steps_image',
                                'title' => __( 'Steps Image', 'recipe-card-blocks-by-wpzoom' ),
                                'type'  => 'checkbox',
                                'args'  => array(
                                    'label_for'   => 'wpzoom_rcb_settings_print_show_steps_image',
                                    'class'       => 'wpzoom-rcb-field',
                                    'description' => esc_html__( 'Show Steps Image on print sheet.', 'recipe-card-blocks-by-wpzoom' ),
                                    'default'     => true,
                                ),
                            ),
                        ),
                    ),
                    array(
                        'id'       => 'wpzoom_rcb_settings_pinterest',
                        'title'    => __( 'Pinterest', 'recipe-card-blocks-by-wpzoom' ),
                        'page'     => 'wpzoom-recipe-card-settings-miscellaneous',
                        'callback' => '__return_false',
                        'fields'   => array(
                            array(
                                'id'    => 'wpzoom_rcb_settings_display_pin',
                                'title' => __( 'Display Pin Button', 'recipe-card-blocks-by-wpzoom' ),
                                'type'  => 'checkbox',
                                'args'  => array(
                                    'label_for'   => 'wpzoom_rcb_settings_display_pin',
                                    'class'       => 'wpzoom-rcb-field',
                                    'description' => esc_html__( 'Show Pinterest button in recipe card', 'recipe-card-blocks-by-wpzoom' ),
                                    'default'     => false,
                                    'preview'     => true,
                                    'preview_pos' => 'bottom',
                                ),
                            ),
                            array(
                                'id'    => 'wpzoom_rcb_settings_pin_image',
                                'title' => __( 'Pin Image', 'recipe-card-blocks-by-wpzoom' ),
                                'type'  => 'select',
                                'args'  => array(
                                    'label_for'   => 'wpzoom_rcb_settings_pin_image',
                                    'class'       => 'wpzoom-rcb-field',
                                    'description' => esc_html__( 'Image to use for Pin Recipe.', 'recipe-card-blocks-by-wpzoom' ),
                                    'default'     => 'recipe_image',
                                    'options'     => array(
                                        'recipe_image' => __( 'Recipe Image', 'recipe-card-blocks-by-wpzoom' ),
                                        'custom_image' => __( 'Custom Image per Recipe (Premium Only)', 'recipe-card-blocks-by-wpzoom' ),
                                    ),
                                ),
                            ),
                            array(
                                'id'    => 'wpzoom_rcb_settings_pin_description',
                                'title' => __( 'Pin Description', 'recipe-card-blocks-by-wpzoom' ),
                                'type'  => 'select',
                                'args'  => array(
                                    'label_for'   => 'wpzoom_rcb_settings_pin_description',
                                    'class'       => 'wpzoom-rcb-field',
                                    'description' => esc_html__( 'Text description to use for Pin Recipe.', 'recipe-card-blocks-by-wpzoom' ),
                                    'default'     => 'recipe_name',
                                    'options'     => array(
                                        'recipe_name'    => __( 'Recipe Name', 'recipe-card-blocks-by-wpzoom' ),
                                        'recipe_summary' => __( 'Recipe Summary', 'recipe-card-blocks-by-wpzoom' ),
                                        'custom_text'    => __( 'Custom Text (Premium Only)', 'recipe-card-blocks-by-wpzoom' ),
                                    ),
                                ),
                            ),
                        ),
                    ),


                    array(
                        'id'       => 'wpzoom_section_lightbox',
                        'title'    => __( 'Lightbox', 'recipe-card-blocks-by-wpzoom' ),
                        'page'     => 'wpzoom-recipe-card-settings-miscellaneous',
                        'callback' => array( $this, 'section_lightbox_cb' ),
                        'fields'   => array(
                            array(
                                'id'    => 'wpzoom_rcb_settings_recipe_image_lightbox',
                                'title' => __( 'Recipe Image Lightbox', 'recipe-card-blocks-by-wpzoom' ),
                                'type'  => 'checkbox',
                                'args'  => array(
                                    'label_for'   => 'wpzoom_rcb_settings_recipe_image_lightbox',
                                    'class'       => 'wpzoom-rcb-field',
                                    'description' => esc_html__( 'Open the recipe image in a lightbox when clicking it.', 'recipe-card-blocks-by-wpzoom' ),
                                    'default'     => false,
                                    'disabled'    => true,
                                    'badge'       => $premium_badge,
                                ),
                            ),
                            array(
                                'id'    => 'wpzoom_rcb_settings_instruction_images_lightbox',
                                'title' => __( 'Directions Images Lightbox', 'recipe-card-blocks-by-wpzoom' ),
                                'type'  => 'checkbox',
                                'args'  => array(
                                    'label_for'   => 'wpzoom_rcb_settings_instruction_images_lightbox',
                                    'class'       => 'wpzoom-rcb-field',
                                    'description' => esc_html__( 'Open the directions images in a lightbox when clicking them.', 'recipe-card-blocks-by-wpzoom' ),
                                    'default'     => false,
                                    'disabled'    => true,
                                    'badge'       => $premium_badge,
                                ),
                            ),

                        ),
                    ),
                    array(
                        'id'       => 'wpzoom_section_prevent_sleep',
                        'title'    => __( 'Cook Mode', 'recipe-card-blocks-by-wpzoom' ),
                        'page'     => 'wpzoom-recipe-card-settings-miscellaneous',
                        'callback' => array( $this, 'section_prevent_sleep_cb' ),
                        'fields'   => array(
                            array(
                                'id'    => 'wpzoom_rcb_settings_recipe_enable_prevent_sleep_toggle',
                                'title' => __( 'Cook Mode Toggle', 'recipe-card-blocks-by-wpzoom' ),
                                'type'  => 'checkbox',
                                'args'  => array(
                                    'label_for'   => 'wpzoom_rcb_settings_recipe_enable_prevent_sleep_toggle',
                                    'class'       => 'wpzoom-rcb-field',
                                    'description' => esc_html__( 'Enable Cook Mode', 'recipe-card-blocks-by-wpzoom' ),
                                    'default'     => false,
                                    'disabled'    => true,
                                    'badge'       => $premium_badge,
                                ),
                            ),
                            array(
                                'id'    => 'wpzoom_rcb_settings_recipe_prevent_sleep_label',
                                'title' => __( 'Toggle Label', 'recipe-card-blocks-by-wpzoom' ),
                                'type'  => 'input',
                                'args'  => array(
                                    'label_for'   => 'wpzoom_rcb_settings_recipe_prevent_sleep_label',
                                    'class'       => 'wpzoom-rcb-field',
                                    'default'     => __( 'Cook Mode', 'recipe-card-blocks-by-wpzoom' ),
                                    'type'        => 'text',
                                    'disabled'    => true,
                                    'badge'       => $premium_badge,
                                ),
                            ),
                            array(
                                'id'    => 'wpzoom_rcb_settings_recipe_prevent_sleep_description',
                                'title' => __( 'Toggle Description', 'recipe-card-blocks-by-wpzoom' ),
                                'type'  => 'input',
                                'args'  => array(
                                    'label_for'   => 'wpzoom_rcb_settings_recipe_prevent_sleep_description',
                                    'class'       => 'wpzoom-rcb-field',
                                    'default'     => __( 'Keep the screen of your device on', 'recipe-card-blocks-by-wpzoom' ),
                                    'type'        => 'text',
                                    'disabled'    => true,
                                    'badge'       => $premium_badge,
                                ),
                            ),
                        ),
                    ),
                ),
            ),

            'ratings'       => array(
                'tab_id'       => 'tab-ratings',
                'tab_title'    => __( 'Ratings', 'recipe-card-blocks-by-wpzoom' ),
                'option_group' => 'wpzoom-recipe-card-settings-ratings',
                'option_name'  => self::$option,
                'sections'     => array(
                    array(
                        'id'       => 'wpzoom_section_rating_features',
                        'title'    => __( 'Rating Feature', 'recipe-card-blocks-by-wpzoom' ),
                        'page'     => 'wpzoom-recipe-card-settings-ratings',
                        'callback' => array( $this, 'section_rating_feature_cb' ),
                        'fields'   => array(
                            array(
                                'id'    => 'wpzoom_rcb_settings_user_ratings',
                                'title' => __( 'User Rating', 'recipe-card-blocks-by-wpzoom' ),
                                'type'  => 'checkbox',
                                'args'  => array(
                                    'label_for'   => 'wpzoom_rcb_settings_user_ratings',
                                    'class'       => 'wpzoom-rcb-field',
                                    'description' => esc_html__( 'Allow visitors to vote your recipes.', 'recipe-card-blocks-by-wpzoom' ),
                                    'default'     => true,
                                    'disabled'    => true,
                                    'badge'       => $premium_badge,
                                    'preview'     => true,
                                    'preview_pos' => 'top',
                                ),
                            ),

                            array(
                                'id'    => 'wpzoom_rcb_settings_comment_ratings',
                                'title' => __( 'Comment Ratings', 'recipe-card-blocks-by-wpzoom' ),
                                'type'  => 'checkbox',
                                'args'  => array(
                                    'label_for'   => 'wpzoom_rcb_settings_comment_ratings',
                                    'class'       => 'wpzoom-rcb-field',
                                    'description' => esc_html__( 'Allow visitors to vote recipes when adding a comment', 'recipe-card-blocks-by-wpzoom' ),
                                    'default'     => true,
                                    'preview'     => true,
                                    'preview_pos' => 'top',
                                    'disabled'    => true,
                                    'badge'       => $premium_badge,
                                ),
                            ),

                            array(
                                'id'    => 'wpzoom_rcb_settings_who_can_rate',
                                'title' => __( 'Who can rate?', 'recipe-card-blocks-by-wpzoom' ),
                                'type'  => 'select',
                                'args'  => array(
                                    'label_for'   => 'wpzoom_rcb_settings_who_can_rate',
                                    'class'       => 'wpzoom-rcb-field',
                                    'description' => esc_html__( 'Select who can rate your recipes.', 'recipe-card-blocks-by-wpzoom' ),
                                    'default'     => 'everyone',
                                    'options'     => array(
                                        'loggedin' => __( 'Only logged in users can rate recipes', 'recipe-card-blocks-by-wpzoom' ),
                                        'everyone' => __( 'Everyone can rate recipes', 'recipe-card-blocks-by-wpzoom' ),
                                    ),
                                    'disabled'    => true,
                                    'badge'       => $premium_badge,
                                ),
                            ),
                            array(
                                'id'    => 'wpzoom_rcb_settings_rating_stars_color',
                                'title' => __( 'Rating Stars Color', 'recipe-card-blocks-by-wpzoom' ),
                                'type'  => 'colorpicker',
                                'args'  => array(
                                    'label_for'   => 'wpzoom_rcb_settings_rating_stars_color',
                                    'class'       => 'wpzoom-rcb-field',
                                    'description' => esc_html__( 'Change rating stars color of Recipe Card.', 'recipe-card-blocks-by-wpzoom' ),
                                    'default'     => '#F2A123',
                                    'badge'       => $premium_badge,
                                ),
                            ),
                        ),
                    ),
                ),
            ),

			'performance' => array(
				'tab_id'       => 'tab-performance',
				'tab_title'    => __( 'Performance', 'recipe-card-blocks-by-wpzoom' ),
				'option_group' => 'wpzoom-recipe-card-settings-performance',
				'option_name'  => self::$option,
				'sections'     => array(

                    array(
                        'id'       => 'wpzoom_rcb_settings_google_fonts',
                        'title'    => __( 'Google Fonts', 'recipe-card-blocks-by-wpzoom' ),
                        'page'     => 'wpzoom-recipe-card-settings-performance',
                        'callback' => '__return_false',
                        'fields'   => array(

							array(
								'id'    => 'wpzoom_rcb_settings_load_pinterest_script',
								'title' => __( 'Load Pinterest script', 'recipe-card-blocks-by-wpzoom' ),
								'type'  => 'checkbox',
								'args'  => array(
									'label_for'   => 'wpzoom_rcb_settings_load_pinterest_script',
									'class'       => 'wpzoom-rcb-field',
									'description' => __( 'Uncheck this option if you don\'t want to load the Pinterest script on your website used for the Pin It button', 'recipe-card-blocks-by-wpzoom' ),
									'default'     => true,
								),
							),
                        ),
                    ),

					array(
						'id'       => 'wpzoom_section_load_assets',
						'title'    => __( 'Assets', 'recipe-card-blocks-by-wpzoom' ),
						'page'     => 'wpzoom-recipe-card-settings-performance',
						'callback' => '__return_false',
						'fields'   => array(
							array(
								'id'    => 'wpzoom_rcb_settings_load_assets_on_all_pages',
								'title' => __( 'Load Assets on all pages?', 'recipe-card-blocks-by-wpzoom' ),
								'type'  => 'checkbox',
								'args'  => array(
									'label_for'   => 'wpzoom_rcb_settings_load_assets_on_all_pages',
									'class'       => 'wpzoom-rcb-field',
									'description' => sprintf( '%s </br><strong>%s</strong>', esc_html__( 'Enabling this option will load JavaScript and CSS files on archive pages that include posts with recipe cards.', 'recipe-card-blocks-by-wpzoom' ), esc_html__( 'NOTE: Disable this option to load assets only on the single post pages.', 'recipe-card-blocks-by-wpzoom' ) ),
									'default'     => true,
								),
							),
						),
					),
				),
			),
			//Added Tools - date: January 2022
			'tools' => array(
				'tab_id'        => 'tab-tools',
				'tab_title'    => esc_html__( 'Tools', 'recipe-card-blocks-by-wpzoom' ),
				'option_group' => 'wpzoom-recipe-card-settings-tools',
				'option_name' 	=> self::$option,
				'sections' 		=> array(
					array(
						'id' 		=> 'wpzoom_section_tools',
						'title' 	=> esc_html__( 'Tools', 'recipe-card-blocks-by-wpzoom' ),
						'page' 		=> 'wpzoom-recipe-card-settings-tools',
						'callback' 	=> '__return_false',
						'fields' 	=> array(
							array(
								'id' 		=> 'wpzoom_rcb_settings_search_recipes',
								'title' 	=> esc_html__( 'Search Recipes', 'recipe-card-blocks-by-wpzoom' ),
								'type'		=> 'scan_button',
								'args' 		=> array(
									'label_for' 	=> 'wpzoom_rcb_settings_search_recipes',
									'type'			=> 'button',
									'button_type'	=> 'secondary',
									'text' 			=> esc_html__( 'Search Recipes', 'recipe-card-blocks-by-wpzoom' ),
									'description' 	=> esc_html__( 'Go through all posts and pages on your website to find all recipe card blocks and create custom Recipe posts', 'recipe-card-blocks-by-wpzoom' ),
								)
							),
							array(
								'id' 		=> 'wpzoom_rcb_settings_create_recipe_post',
								'title' 	=> esc_html__( 'Create Recipe Post?', 'recipe-card-blocks-by-wpzoom' ),
								'type'		=> 'checkbox',
								'args' 		=> array(
									'label_for' 	=> 'wpzoom_rcb_settings_create_recipe_post',
									'class' 		=> 'wpzoom-rcb-field',
									'description' 	=> esc_html__( 'If a Recipe Card Block is added to a post, it will create a custom Recipe post type (CPT) in the All Recipes section', 'recipe-card-blocks-by-wpzoom' ),
									'default'		=> true
								)
							),
							array(
								'id' 		=> 'wpzoom_rcb_settings_update_recipe_post',
								'title' 	=> esc_html__( 'Update Recipe Post?', 'recipe-card-blocks-by-wpzoom' ),
								'type'		=> 'checkbox',
								'args' 		=> array(
									'label_for' 	=> 'wpzoom_rcb_settings_update_recipe_post',
									'class' 		=> 'wpzoom-rcb-field',
									'description' 	=> esc_html__( 'If a Recipe is updated on a page or post, it will also update the Recipe post.', 'recipe-card-blocks-by-wpzoom' ),
									'default'		=> true
								)
							),
							array(
								'id' 		=> 'wpzoom_rcb_settings_synchronize_recipe_post',
								'title' 	=> esc_html__( 'Synchronize Recipe Post?', 'recipe-card-blocks-by-wpzoom' ),
								'type'		=> 'checkbox',
								'args' 		=> array(
									'label_for' 	=> 'wpzoom_rcb_settings_synchronize_recipe_post',
									'class' 		=> 'wpzoom-rcb-field',
									'description' 	=> esc_html__( 'Synchronize the content of the Recipe post and parent post. ', 'recipe-card-blocks-by-wpzoom' ),
									'default'		=> true
								)
							),
						)
					)
				)	
			),
			'metadata'    => array(
				'tab_id'       => 'tab-metadata',
				'tab_title'    => __( 'Metadata', 'recipe-card-blocks-by-wpzoom' ),
				'option_group' => 'wpzoom-recipe-card-settings-metadata',
				'option_name'  => self::$option,
				'sections'     => array(
					array(
						'id'       => 'wpzoom_section_taxonomies',
						'title'    => __( 'Taxonomies', 'recipe-card-blocks-by-wpzoom' ),
						'page'     => 'wpzoom-recipe-card-settings-metadata',
						'callback' => '__return_false',
						'fields'   => array(
							array(
								'id'    => 'wpzoom_rcb_settings_course_taxonomy',
								'title' => __( 'Course', 'recipe-card-blocks-by-wpzoom' ),
								'type'  => 'checkbox',
								'args'  => array(
									'label_for'   => 'wpzoom_rcb_settings_course_taxonomy',
									'class'       => 'wpzoom-rcb-field',
									'description' => esc_html__( 'Make Course as taxonomy.', 'recipe-card-blocks-by-wpzoom' ),
									'default'     => false,
									'disabled'    => true,
									'badge'       => $premium_badge . $soon_badge,
								),
							),
							array(
								'id'    => 'wpzoom_rcb_settings_cuisine_taxonomy',
								'title' => __( 'Cuisine', 'recipe-card-blocks-by-wpzoom' ),
								'type'  => 'checkbox',
								'args'  => array(
									'label_for'   => 'wpzoom_rcb_settings_cuisine_taxonomy',
									'class'       => 'wpzoom-rcb-field',
									'description' => esc_html__( 'Make Cuisine as taxonomy.', 'recipe-card-blocks-by-wpzoom' ),
									'default'     => false,
									'disabled'    => true,
									'badge'       => $premium_badge . $soon_badge,
								),
							),
							array(
								'id'    => 'wpzoom_rcb_settings_difficulty_taxonomy',
								'title' => __( 'Difficulty', 'recipe-card-blocks-by-wpzoom' ),
								'type'  => 'checkbox',
								'args'  => array(
									'label_for'   => 'wpzoom_rcb_settings_difficulty_taxonomy',
									'class'       => 'wpzoom-rcb-field',
									'description' => esc_html__( 'Make Difficulty as taxonomy.', 'recipe-card-blocks-by-wpzoom' ),
									'default'     => false,
									'disabled'    => true,
									'badge'       => $premium_badge . $soon_badge,
								),
							),
						),
					),
				),
			),
		);

		$this->register_settings();
	}

	/**
	 * Register all Setting options
	 *
	 * @since 1.1.0
	 * @return boolean
	 */
	public function register_settings() {
		// filter hook
		self::$settings = apply_filters( 'wpzoom_rcb_before_register_settings', self::$settings );

		if ( empty( self::$settings ) ) {
			return;
		}

		foreach ( self::$settings as $key => $setting ) {
			$this->register_setting( $setting );
		}

		return true;
	}

	/**
	 * Register Setting
	 *
	 * @since 2.3.0
	 * @param array $setting
	 * @return void
	 */
	public function register_setting( $setting ) {
		$setting['sanitize_callback'] = isset( $setting['sanitize_callback'] ) ? $setting['sanitize_callback'] : array();
		register_setting( $setting['option_group'], $setting['option_name'], $setting['sanitize_callback'] );

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

	/**
	 * HTML output for Setting page
	 */
	public function settings_page() {
		// check user capabilities
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$reset_settings   = isset( $_GET['wpzoom_reset_settings'] ) ? sanitize_text_field( $_GET['wpzoom_reset_settings'] ) : false;
		$settings_updated = isset( $_GET['settings-updated'] ) ? sanitize_text_field( $_GET['settings-updated'] ) : false;
		?>
		<div class="wrap">
			<?php do_action( 'wpzoom_rcb_welcome_banner' ); ?>

			<h1 style="margin-bottom: 15px"><?php echo esc_html( get_admin_page_title() ); ?></h1>

			<?php settings_errors(); ?>

			<?php if ( $reset_settings && ! $settings_updated ) : ?>
				<div class="updated settings-error notice is-dismissible">
					<p><strong><?php _e('Settings were successfully reset', 'recipe-card-blocks-by-wpzoom'); ?></strong></p>
				</div>
			<?php endif; ?>

            <div class="cols-wrap">

    			<form id="wpzoom-recipe-card-settings" action="options.php" method="post">
    				<ul class="wp-tab-bar">
    					<?php foreach ( self::$settings as $setting ) : ?>
    						<?php if ( self::$active_tab === $setting['tab_id'] ) : ?>
    							<li class="wp-tab-active"><a href="?page=wpzoom-recipe-card-settings&tab=<?php echo esc_attr( $setting['tab_id'] ); ?>"><?php echo esc_html( $setting['tab_title'] ); ?></a></li>
    						<?php else : ?>
    							<li><a href="?page=wpzoom-recipe-card-settings&tab=<?php echo esc_attr( $setting['tab_id'] ); ?>"><?php echo esc_html( $setting['tab_title'] ); ?></a></li>
    						<?php endif ?>
    					<?php endforeach ?>
    				</ul>
    				<?php foreach ( self::$settings as $setting ) : ?>
    					<?php if ( self::$active_tab === $setting['tab_id'] ) : ?>
    						<div class="wp-tab-panel" id="<?php echo esc_attr( $setting['tab_id'] ); ?>">
    							<?php
    								settings_fields( $setting['option_group'] );
    								do_settings_sections( $setting['option_group'] );
    							?>
    						</div>
    					<?php else : ?>
    						<div class="wp-tab-panel" id="<?php echo esc_attr( $setting['tab_id'] ); ?>" style="display: none;">
    							<?php
    								settings_fields( $setting['option_group'] );
    								do_settings_sections( $setting['option_group'] );
    							?>
    						</div>
    					<?php endif ?>
    				<?php endforeach ?>

                    <ul class="rcb_btns_bottom">
                        <li id="wpzoom_rcb_settings_save"><?php submit_button( 'Save Changes', 'primary', 'wpzoom_rcb_settings_save', false ); ?></li>
                        <li id="wpzoom_rcb_reset_settings"><input type="button" class="button button-secondary" name="wpzoom_rcb_reset_settings" id="wpzoom_rcb_reset_settings" value="Reset Settings"></li>
                    </ul>

    			</form>

                <div class="wpz_right-col">

                        <div id="mlb2-5662656" class="ml-form-embedContainer ml-subscribe-form ml-subscribe-form-5662656">
                          <div class="ml-form-align-center">
                            <div class="ml-form-embedWrapper embedForm">
                              <div class="ml-form-embedHeader">
                                <img src="https://bucket.mlcdn.com/a/3719/3719705/images/104575be896e33ca3f3a2b2f44b82f43eacd25d4.png" border="0" style="display:block">
                                <style>
                                  @media only screen and (max-width:460px){.ml-form-embedHeader{display:none!important}}
                                </style>
                              </div>
                              <div class="ml-form-embedBody ml-form-embedBodyDefault row-form">
                                <div class="ml-form-embedContent" style="">
                                  <h4><?php _e('Subscribe to our Newsletter!', 'recipe-card-blocks-by-wpzoom'); ?></h4>
                                  <p><?php _e('Receive plugin updates and get for <strong>free</strong> our email course <strong>"How to start, maintain & optimize a food blog"</strong>.', 'recipe-card-blocks-by-wpzoom'); ?><br></p>
                                 </div>
                                <form class="ml-block-form" action="https://static.mailerlite.com/webforms/submit/w5r9l0" data-code="w5r9l0" method="post" target="_blank">
                                  <div class="ml-form-formContent">
                                    <div class="ml-form-fieldRow ml-last-item">
                                      <div class="ml-field-group ml-field-email ml-validate-email ml-validate-required">
                                        <input aria-label="email" aria-required="true" type="email" class="form-control" data-inputmask="" name="fields[email]" placeholder="Enter your email address" autocomplete="email">
                                      </div>
                                    </div>
                                  </div>
                                  <input type="hidden" name="ml-submit" value="1">
                                  <div class="ml-form-embedSubmit">
                                    <button type="submit" class="primary"><?php _e('Subscribe', 'recipe-card-blocks-by-wpzoom'); ?></button>
                                    <button disabled="disabled" style="display:none" type="button" class="loading"> <div class="ml-form-embedSubmitLoad"></div> <span class="sr-only">Loading...</span> </button>
                                  </div>
                                  <input type="hidden" name="anticsrf" value="true">
                                </form>
                              </div>
                              <div class="ml-form-successBody row-success" style="display:none">
                                <div class="ml-form-successContent">
                                  <h4><?php _e('Thank you!', 'recipe-card-blocks-by-wpzoom'); ?></h4>
                                  <p><?php _e('You have successfully joined our subscriber list.', 'recipe-card-blocks-by-wpzoom'); ?></p>
                                </div>
                              </div>
                            </div>
                          </div>
                        </div>
                        <script>
                          function ml_webform_success_5662656(){var r=ml_jQuery||jQuery;r(".ml-subscribe-form-5662656 .row-success").show(),r(".ml-subscribe-form-5662656 .row-form").hide()}
                        </script>
                        <img src="https://track.mailerlite.com/webforms/o/5662656/w5r9l0?v1651224972" width="1" height="1" style="max-width:1px;max-height:1px;visibility:hidden;padding:0;margin:0;display:block" alt="." border="0">
                        <script src="https://static.mailerlite.com/js/w/webforms.min.js?v9b62042f798751c8de86a784eab23614" type="text/javascript"></script>

                    <div class="license-wrap">
                        <h2 class="headline"><?php _e( 'Follow us!', 'recipe-card-blocks-by-wpzoom' ); ?></h2>
                        <iframe src="https://www.facebook.com/plugins/like.php?href=https%3A%2F%2Fwww.facebook.com%2Frecipeblock&width=89&layout=button_count&action=like&size=large&show_faces=false&share=false&height=21&appId=610643215638351" width="129" height="30" style="border:none;overflow:hidden" scrolling="no" frameborder="0" allowTransparency="true"></iframe>

                        <br>
                        <br>

                        <a href="https://twitter.com/recipeblock" class="twitter-follow-button" data-size="large" data-show-count="true" data-show-screen-name="true">Follow @recipeblock</a><br/>
                        <script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+'://platform.twitter.com/widgets.js';fjs.parentNode.insertBefore(js,fjs);}}(document, 'script', 'twitter-wjs');</script>

                        <a href="https://instagram.com/recipecardblocks/" class="settings_wpz_btn" target="_blank"><span class="dashicons dashicons-instagram"></span> Follow on Instagram</a><br/>
                    </div>
                </div>
            </div>
		</div>
		<?php
	}

	/**
	 * Enqueue scripts and styles
	 *
	 * @param string $hook
	 */
	public function scripts( $hook ) {
		$pos = strpos( $hook, WPZOOM_RCB_SETTINGS_PAGE );

		wp_enqueue_style(
			'wpzoom-rcb-admin-css',
			untrailingslashit( WPZOOM_RCB_PLUGIN_URL ) . '/dist/assets/admin/css/admin.css',
			array(),
			WPZOOM_RCB_VERSION
		);

		if ( $pos !== false ) {
			// Add the color picker css file
			wp_enqueue_style( 'wp-color-picker' );

			wp_enqueue_style(
				'wpzoom-rcb-admin-style',
				untrailingslashit( WPZOOM_RCB_PLUGIN_URL ) . '/dist/assets/admin/css/style.css',
				array(),
				WPZOOM_RCB_VERSION
			);

			wp_enqueue_script(
				'wpzoom-rcb-admin-script',
				untrailingslashit( WPZOOM_RCB_PLUGIN_URL ) . '/dist/assets/admin/js/script.js',
				array( 'jquery', 'wp-color-picker' ),
				WPZOOM_RCB_VERSION
			);

			wp_localize_script(
				'wpzoom-rcb-admin-script',
				'WPZOOM_Settings',
				array(
					'ajaxUrl'    => admin_url( 'admin-ajax.php' ),
					'ajax_nonce' => wp_create_nonce( 'wpzoom-reset-settings-nonce' ),
				)
			);
		}
	}

	/**
	 * Reset settings to default values
	 *
	 * @since 1.1.0
	 * @return void
	 */
	public function reset_settings() {
		check_ajax_referer( 'wpzoom-reset-settings-nonce', 'security' );

		$defaults = self::get_defaults();

		if ( empty( $defaults ) ) {
			$response = array(
				'status'  => '304',
				'message' => 'NOT',
			);

			wp_send_json_error( $response );
		}

		$response = array(
			'status'  => '200',
			'message' => 'OK',
		);

		self::update_option( $defaults );

		wp_send_json_success( $response );
	}

	/**
	 * Close Welcome banner
	 *
	 * @since 1.2.0
	 * @return void
	 */
	public function welcome_banner_close() {
		check_ajax_referer( 'wpzoom-reset-settings-nonce', 'security' );

		if ( delete_transient( 'wpzoom_rcb_welcome_banner' ) ) {
			$response = array(
				'status'  => '200',
				'message' => 'OK',
			);

			wp_send_json_success( $response );
		} else {
			$response = array(
				'status'  => '304',
				'message' => 'NOT',
			);

			wp_send_json_error( $response );
		}
	}

	// section callbacks can accept an $args parameter, which is an array.
	// $args have the following keys defined: title, id, callback.
	// the values are defined at the add_settings_section() function.
	public function section_defaults_cb( $args ) {
		?>
		 <p id="<?php echo esc_attr( $args['id'] ); ?>"><?php esc_html_e( 'Default configurations for new recipes.', 'recipe-card-blocks-by-wpzoom' ); ?></p>
		<?php
	}

	public function section_rating_feature_cb( $args ) {
		?>
		 <p id="<?php echo esc_attr( $args['id'] ); ?>"><?php esc_html_e( 'Recipe Rating shown in the Recipe Card and Recipe Metadata.', 'recipe-card-blocks-by-wpzoom' ); ?></p>
		<?php
	}

    public function section_lightbox_cb( $args ) {
        ?>
         <p id="<?php echo esc_attr( $args['id'] ); ?>"><?php esc_html_e( 'With Lightbox, when a user clicks on the recipe and/or directions images, the image opens in a lightbox popup.', 'recipe-card-blocks-by-wpzoom' ); ?></p>
        <?php
    }

    public function section_equipment_feature_cb( $args ) {
        ?>
         <p id="<?php echo esc_attr( $args['id'] ); ?>"><?php esc_html_e( 'Display a list of utensils needed to cook a specific recipe.', 'recipe-card-blocks-by-wpzoom' ); ?></p>
        <?php
    }

	public function section_recipe_template_cb( $args ) {
		?>
		 <p id="<?php echo esc_attr( $args['id'] ); ?>"><?php esc_html_e( 'You will get access to more Recipe Templates with the Premium version.', 'recipe-card-blocks-by-wpzoom' ); ?></p>
		<?php
	}

    public function section_recipe_call_to_action_cb( $args ) {
        ?>
         <p id="<?php echo esc_attr( $args['id'] ); ?>"><?php esc_html_e( 'Add Instagram and/or Pinterest and/or Facebook CTA (call to action) in Recipe Card footer.', 'recipe-card-blocks-by-wpzoom' ); ?></p>
        <?php
    }

	public function section_recipe_snippets( $args ) {
		?>
		 <p id="<?php echo esc_attr( $args['id'] ); ?>"><?php esc_html_e( 'Display Jump to Recipe and Print Recipe buttons.', 'recipe-card-blocks-by-wpzoom' ); ?></p>
		<?php
	}

    public function section_prevent_sleep_cb( $args ) {
        ?>
         <p id="<?php echo esc_attr( $args['id'] ); ?>"><?php esc_html_e( 'Prevent Sleep mode will show the toggle to enable "Cook Mode", preventing the screen of their device from sleeping or going dark.', 'recipe-card-blocks-by-wpzoom' ); ?></p>
        <?php
    }
}

new WPZOOM_Settings();
