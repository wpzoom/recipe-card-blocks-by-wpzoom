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
	 * Active Tab.
	 */
	public static $active_tab;

	/**
	 * The WPZOOM_Recipe_Card_Block_Gutenberg instance.
	 *
	 * @var WPZOOM_Recipe_Card_Block_Gutenberg
	 * @since 1.1.0
	 */
	private $_recipe_card_block;

	/**
	 * Class WPZOOM_Settings_Fields instance.
	 */
	public $_fields;

	/**
	 * Store Settings options.
	 */
	public $options = array();

	/**
	 * License key
	 */
	public $license_key = null;

	/**
	 * License status
	 */
	public $license_status = null;

	/**
	 * The Constructor.
	 */
	public function __construct() {
		if( is_admin() ) {
            global $pagenow;

            // retrieve our license key from the DB
            $this->options = get_option( 'wpzoom-recipe-card-settings' );
            $this->license_key = trim( @$this->options['wpzoom_rcb_plugin_license_key'] );
            $this->license_status = @$this->options['wpzoom_rcb_plugin_license_status'];

		    add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		    add_action( 'admin_init', array( $this, 'settings_init' ) );
		    add_action( 'admin_init', array( $this, 'set_defaults' ) );
		    add_action( 'admin_init', array( $this, 'activate_license' ) );
		    add_action( 'admin_init', array( $this, 'deactivate_license' ) );
		    add_action( 'admin_init', array( $this, 'initiate_updater_class' ) );

		    if( isset( $_GET['page'] ) && $_GET['page'] === WPZOOM_RCB_SETTINGS_PAGE ) {
		        if( $pagenow !== "options-general.php" ) {
		        	// Display admin notices
		        	add_action( 'admin_notices', array( $this, 'admin_notices' ) );
		        }
                // Include admin scripts & styles
                add_action( 'admin_enqueue_scripts', array( $this, 'scripts' ) );
            }

		    add_filter( 'wpzoom_rcb_before_register_settings', array( $this, 'settings_license' ) );

		    $this->_recipe_card_block = new WPZOOM_Recipe_Card_Block_Gutenberg();
		    $this->_fields = new WPZOOM_Settings_Fields();
		}
	}

	/**
	 * Add subitem to Settings admin menu.
	 */
	public function admin_menu() {
		add_options_page(
			__( 'WPZOOM Recipe Card Settings', 'wpzoom-recipe-card' ),
			__( 'WPZOOM Recipe Card', 'wpzoom-recipe-card' ),
			'manage_options',
			WPZOOM_RCB_SETTINGS_PAGE,
			array( $this, 'settings_page' )
		);
	}

	/**
	 * Set default values for setting options.
	 */
	public function set_defaults() {
		// Set active tab
		self::$active_tab = isset( $_GET['tab'] ) ? $_GET['tab'] : 'tab-general';

		foreach ( $this->settings as $key => $setting ) {
			if ( isset( $setting['sections'] ) && is_array( $setting['sections'] ) ) {
				foreach ( $setting['sections'] as $section ) {
					if ( isset( $section['fields'] ) && is_array( $section['fields'] ) ) {
						foreach ( $section['fields'] as $field ) {
							if ( isset( $field['args']['default'] ) ) {
								self::$defaults[ $field['id'] ] = (string)$field['args']['default'];
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
	 * Initiate the updater class.
	 * @since 1.1.0 
	 * @return void
	 */
	public function initiate_updater_class() {
		// setup the updater
		$plugin_updater = new EDD_SL_Plugin_Updater( WPZOOM_RCB_STORE_URL, WPZOOM_RCB_PLUGIN_DIR, array(
			'version' 	=> WPZOOM_RCB_VERSION,		// current version number
			'license' 	=> $this->license_key,		// license key (used get_option above to retrieve from DB)
			'item_id'   => WPZOOM_RCB_ITEM_ID,		// id of this plugin
			'author' 	=> 'Vicolas Petru',			// author of this plugin
			'url'       => home_url(),
		        'beta'  => false 					// set to true if you wish customers to receive update notifications of beta releases
		) );
	}

	/**
	 * Initilize all settings
	 */
	public function settings_init() {
		$this->settings = array(
			'general' => array(
				'tab_id' 		=> 'tab-general',
				'tab_title' 	=> __( 'General', 'wpzoom-recipe-card' ),
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
									'disabled'		=> true,
									'badge' 		=> '<span class="wpzoom-rcb-badge wpzoom-rcb-field-is_premium">'. __( 'Premium', 'wpzoom-recipe-card' ) .'</span>',
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
									'disabled'		=> true,
									'badge' 		=> '<span class="wpzoom-rcb-badge wpzoom-rcb-field-is_premium">'. __( 'Premium', 'wpzoom-recipe-card' ) .'</span>',
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
									'disabled'		=> true,
									'badge' 		=> '<span class="wpzoom-rcb-badge wpzoom-rcb-field-is_premium">'. __( 'Premium', 'wpzoom-recipe-card' ) .'</span>',
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
									'disabled'		=> true,
									'badge' 		=> '<span class="wpzoom-rcb-badge wpzoom-rcb-field-is_premium">'. __( 'Premium', 'wpzoom-recipe-card' ) .'</span>',
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
									'disabled'		=> true,
									'badge' 		=> '<span class="wpzoom-rcb-badge wpzoom-rcb-field-is_premium">'. __( 'Premium', 'wpzoom-recipe-card' ) .'</span>',
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
	 * Add License tab to Settings
	 * Apply to filter 'wpzoom_rcb_before_register_settings'
	 * 
	 * @since 1.1.0
	 * @param array $settings
	 * @return array
	 */
	public function settings_license( $settings ) {
		if ( ! $this->license_key ) {
			$message = __( 'The license key is not inserted.', 'wpzoom-recipe-card' );
		} else {
		    if ( ! get_transient( 'wpzoom_rcb_plugin_license_message' ) ) {
		        set_transient( 'wpzoom_rcb_plugin_license_message', $this->check_license(), (60 * 60 * 24) );
		    }
		    $message = get_transient( 'wpzoom_rcb_plugin_license_message' );
		}

		$section_license['license'] = array(
			'tab_id' 		=> 'tab-license',
			'tab_title' 	=> __( 'License', 'wpzoom-recipe-card' ),
			'option_group' 	=> 'wpzoom-recipe-card-settings-license',
			'option_name' 	=> 'wpzoom-recipe-card-settings',
			'sanitize_callback'	=> array( $this, 'sanitize_license' ),
			'sections' 		=> array(
				array(
					'id' 		=> 'wpzoom_section_license',
					'title' 	=> __( 'License', 'wpzoom-recipe-card' ),
					'page' 		=> 'wpzoom-recipe-card-settings-license',
					'callback' 	=> '__return_false',
					'fields' 	=> array(
						array(
							'id' 		=> 'wpzoom_rcb_plugin_license_key',
							'title' 	=> __( 'License Key', 'wpzoom-recipe-card' ),
							'type'		=> 'input',
							'args' 		=> array(
								'label_for' 	=> 'wpzoom_rcb_plugin_license_key',
								'class' 		=> 'wpzoom-rcb-field',
								'description' 	=> esc_html__( 'Enter your license key', 'wpzoom-recipe-card' ),
								'default'		=> '',
								'type'			=> 'text'
							)
						),
						array(
							'id' 		=> 'wpzoom_rcb_plugin_license_status',
							'title' 	=> __( 'License Status', 'wpzoom-recipe-card' ),
							'type'		=> 'input',
							'args' 		=> array(
								'label_for' 	=> 'wpzoom_rcb_plugin_license_status',
								'class' 		=> 'wpzoom-rcb-field',
								'default'		=> '',
								'type'			=> 'hidden',
								'badge' 		=> '<span class="wpzoom-rcb-badge wpzoom-rcb-field-'. ( !$this->license_status ? 'is_inactive' : 'is_active' ) .'">'. ( !$this->license_status ? __( 'inactive', 'wpzoom-recipe-card' ) : __( 'active', 'wpzoom-recipe-card' ) ) .'</span>' . $message,
							)
						)
					)
				)
			)
		);

		if ( false !== $this->license_key ) {
			$section_license['license']['sections'][0]['fields'][2] = array(
				'id' 		=> 'wpzoom_rcb_plugin_activate_license',
				'title' 	=> __( 'Activate License', 'wpzoom-recipe-card' ),
				'type'		=> 'button',
			);

			if ( $this->license_status !== false && $this->license_status == 'valid' ) {
				$section_license['license']['sections'][0]['fields'][2]['args'] = array(
					'label_for' 	=> 'wpzoom_rcb_plugin_license_deactivate',
					'class' 		=> 'wpzoom-rcb-field',
					'text' 			=> esc_html__( 'Deactivate License', 'wpzoom-recipe-card' ),
					'type'			=> 'secondary',
					'nonce'			=> array(
						'action' 	=> 'wpzoom_rcb_plugin_deactivate_license_nonce',
						'name'		=> '_wpzoom_rcb_plugin_license_deactivate_nonce'
					),
				);
			} else {
				$section_license['license']['sections'][0]['fields'][2]['args'] = array(
					'label_for' 	=> 'wpzoom_rcb_plugin_license_activate',
					'class' 		=> 'wpzoom-rcb-field',
					'text' 			=> esc_html__( 'Activate License', 'wpzoom-recipe-card' ),
					'type'			=> 'secondary',
					'nonce'			=> array(
						'action' 	=> 'wpzoom_rcb_plugin_activate_license_nonce',
						'name'		=> '_wpzoom_rcb_plugin_license_activate_nonce'
					),
				);
			}
		}

		return array_merge( $settings, $section_license );
	}

	/**
	 * Register all Setting options
	 * 
	 * @since 1.1.0
	 * @return boolean
	 */
	public function register_settings() {
		// filter hook
		$this->settings = apply_filters( 'wpzoom_rcb_before_register_settings', $this->settings );

		if ( empty( $this->settings ) ) {
			return;
		}

		foreach ( $this->settings as $key => $setting ) {
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
	?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

			<?php if ( ! $this->license_key || ! $this->license_status ): ?>
				<div class="notice notice-info">
					<p>
					    <?php echo sprintf( __( 'Your license key provides access to <strong>Automatic Updates and Premium addons</strong>. You can find your license in <a href="https://www.wpzoom.com/account/licenses/" target="_blank">WPZOOM Members Area &rarr; Licenses</a>.', 'wpzoom' ) );
					     ?>
					</p>
				</div>
			<?php endif ?>

			<form action="options.php" method="post">
				<ul class="wp-tab-bar">
					<?php foreach ( $this->settings as $setting ): ?>
						<?php if ( self::$active_tab === $setting['tab_id'] ): ?>
							<li class="wp-tab-active"><a href="?page=wpzoom-recipe-card-settings&tab=<?php echo $setting['tab_id'] ?>"><?php echo $setting['tab_title'] ?></a></li>
						<?php else: ?>
							<li><a href="?page=wpzoom-recipe-card-settings&tab=<?php echo $setting['tab_id'] ?>"><?php echo $setting['tab_title'] ?></a></li>
						<?php endif ?>
					<?php endforeach ?>
					<li id="wpzoom_rcb_settings_save"><?php submit_button( 'Save Settings', 'primary', 'wpzoom_rcb_settings_save', false ); ?></li>
				</ul>
				<?php foreach ( $this->settings as $setting ): ?>
					<?php if ( self::$active_tab === $setting['tab_id'] ): ?>
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

	/**
	 * Sanitize license
	 * 
	 * @since 1.1.0
	 * @param array $new 
	 * @return array
	 */
	public function sanitize_license( $new ) {
		$old_license = $this->license_key;
		$new_license = trim( @$new['wpzoom_rcb_plugin_license_key'] );

		if( $old_license && $old_license != $new_license ) {
			// Delete status from old option array
			// Update options
			if ( $this->license_status ) {
				unset( $this->options['wpzoom_rcb_plugin_license_status'] );
				update_option( 'wpzoom-recipe-card-settings', $this->options ); // new license has been entered, so must reactivate
			}
			// Delete status from new option array
			if ( isset($new['wpzoom_rcb_plugin_license_status']) ) {
				unset($new['wpzoom_rcb_plugin_license_status']);
			}
			// Delete transient
			delete_transient( 'wpzoom_rcb_plugin_license_message' );
		}
		return $new;
	}

	/**
	 * Constructs a renewal link
	 *
	 * @since 1.1.0
	 */
	public function get_renewal_link() {
	    // If a renewal link was passed in the config, use that
	    if ( '' != WPZOOM_RCB_RENEW_URL ) {
	        return WPZOOM_RCB_RENEW_URL;
	    }

	    if ( '' != WPZOOM_RCB_ITEM_ID && $this->license_key ) {
	        $url = esc_url( WPZOOM_RCB_STORE_URL );
	        $url .= '/checkout/?edd_license_key=' . $this->license_key . '&download_id=' . WPZOOM_RCB_ITEM_ID;
	        return $url;
	    }

	    // Otherwise return the WPZOOM_RCB_STORE_URL
	    return WPZOOM_RCB_STORE_URL;
	}

	/**
	 * Check if a license key is still valid
	 * 
	 * @since 1.1.0
	 * @return void
	 */
	public function check_license() {
		$api_params = array(
			'edd_action' 	=> 'check_license',
			'license' 		=> $this->license_key,
			'item_name' 	=> urlencode( WPZOOM_RCB_ITEM_NAME ),
			'url'       	=> home_url()
		);

		// Call the custom API.
		$response = wp_remote_post( WPZOOM_RCB_STORE_URL, array( 'timeout' => 15, 'sslverify' => false, 'body' => $api_params ) );

		if ( is_wp_error( $response ) )
			return false;

		$license_data = json_decode( wp_remote_retrieve_body( $response ) );

		// If response doesn't include license data, return
		if ( !isset( $license_data->license ) ) {
		    $message = __( 'Incorrect license key.', 'wpzoom-recipe-card' );
		    return $message;
		}

		// Get expire date
		$expires = false;
		if ( isset( $license_data->expires ) && 'lifetime' != $license_data->expires ) {
		    $expires = date_i18n( get_option( 'date_format' ), strtotime( $license_data->expires, current_time( 'timestamp' ) ) );
		    $renew_link = '<a href="' . esc_url( $this->get_renewal_link() ) . '" target="_blank">' . __( 'Renew?', 'wpzoom-recipe-card' ) . '</a>';
		}
		elseif ( isset( $license_data->expires ) && 'lifetime' == $license_data->expires ) {
		    $expires = 'lifetime';
		}

		if ( $license_data->license == 'valid' ) {
		    $message = __( 'License key is active.', 'wpzoom-recipe-card' ) . ' ';
		    if ( ! $this->license_status ) {
		    	$message = '';
		    }
		    if ( isset( $expires ) && 'lifetime' != $expires ) {
		        $message .= sprintf( __( 'Expires %s.', 'wpzoom-recipe-card' ), $expires ) . ' ';
		    }
		    if ( isset( $expires ) && 'lifetime' == $expires ) {
		        $message .= __( 'Lifetime License.', 'wpzoom-recipe-card' );
		    }
		}
		else if ( $license_data->license == 'expired' ) {
		    if ( $expires ) {
		        $message = sprintf( __( 'License key expired %s.', 'wpzoom-recipe-card' ), $expires );
		    } else {
		        $message = __( 'License key has expired.', 'wpzoom-recipe-card' );
		    }
		    if ( $renew_link ) {
		        $message .= ' ' . $renew_link;
		    }
		}
		else if ( $license_data->license == 'invalid' ) {
		    $message = __( 'License key do not match.', 'wpzoom-recipe-card' );
		}
		else if ( $license_data->license == 'inactive' ) {
		    $message = __( 'License is <strong>inactive</strong>. Click on the <strong>Activate License</strong> button to activate it.', 'wpzoom-recipe-card' );
		}
		else if ( $license_data->license == 'disabled' ) {
		    $message = __( 'License key is disabled.', 'wpzoom-recipe-card' );
		}
		else {
		    $message = __( 'Incorrect license key.', 'wpzoom-recipe-card' );
		}

		return $message;
	}

	/**
	 * Activate a License Key
	 * 
	 * @since 1.1.0
	 * @return void
	 */
	public function activate_license() {
		// listen for our activate button to be clicked
		if( isset( $_POST['wpzoom_rcb_plugin_license_activate'] ) ) {
			// run a quick security check
		 	if( ! check_admin_referer( 'wpzoom_rcb_plugin_activate_license_nonce', '_wpzoom_rcb_plugin_license_activate_nonce' ) )
				return; // get out if we didn't click the Activate button

			// data to send in our API request
			$api_params = array(
				'edd_action' => 'activate_license',
				'license'    => $this->license_key,
				'item_id'    => WPZOOM_RCB_ITEM_ID, // The ID of the item in EDD
				'url'        => home_url()
			);

			// Call the custom API.
			$response = wp_remote_post( WPZOOM_RCB_STORE_URL, array( 'timeout' => 15, 'sslverify' => false, 'body' => $api_params ) );

			// make sure the response came back okay
			if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
				$message =  ( is_wp_error( $response ) && ! empty( $response->get_error_message() ) ) ? $response->get_error_message() : __( 'An error occurred, please try again.', 'wpzoom-recipe-card' );
			} else {
				$license_data = json_decode( wp_remote_retrieve_body( $response ) );

				if ( false === $license_data->success ) {
					switch( $license_data->error ) {
						case 'expired' :
							$message = sprintf(
								__( 'Your license key expired on %s.', 'wpzoom-recipe-card' ),
								date_i18n( get_option( 'date_format' ), strtotime( $license_data->expires, current_time( 'timestamp' ) ) )
							);
							break;
						case 'revoked' :
							$message = __( 'Your license key has been disabled.', 'wpzoom-recipe-card' );
							break;
						case 'missing' :
							$message = __( 'Invalid license.', 'wpzoom-recipe-card' );
							break;
						case 'invalid' :
						case 'site_inactive' :
							$message = __( 'Your license is not active for this URL.', 'wpzoom-recipe-card' );
							break;
						case 'item_name_mismatch' :
							$message = sprintf( __( 'This appears to be an invalid license key for %s.', 'wpzoom-recipe-card' ), WPZOOM_RCB_ITEM_NAME );
							break;
						case 'no_activations_left':
							$message = __( 'Your license key has reached its activation limit.', 'wpzoom-recipe-card' );
							break;
						default :
							$message = __( 'An error occurred, please try again.', 'wpzoom-recipe-card' );
							break;
					}
				}
			}

			// Check if anything passed on a message constituting a failure
			if ( ! empty( $message ) ) {
				$base_url = admin_url( 'options-general.php?page=' . WPZOOM_RCB_SETTINGS_PAGE . '&tab=tab-license' );
				$redirect = add_query_arg( array( 'sl_activation' => 'false', 'message' => urlencode( $message ) ), $base_url );
				wp_redirect( $redirect );
				exit();
			}

			// $license_data->license will be either "valid" or "invalid"
			if ( $license_data && isset($license_data->license) ) {
				$this->options['wpzoom_rcb_plugin_license_status'] = $license_data->license;
				update_option( 'wpzoom-recipe-card-settings', $this->options );
				delete_transient( 'wpzoom_rcb_plugin_license_message' );
			}

			wp_redirect( admin_url( 'options-general.php?page=' . WPZOOM_RCB_SETTINGS_PAGE . '&tab=tab-license' ) );
			exit();
		}
	}

	/**
	 * Deactivate a License Key
	 * This will decrease the site count
	 * 
	 * @since 1.1.0
	 * @return void
	 */
	public function deactivate_license() {

		// listen for our activate button to be clicked
		if( isset( $_POST['wpzoom_rcb_plugin_license_deactivate'] ) ) {

			// run a quick security check
		 	if( ! check_admin_referer( 'wpzoom_rcb_plugin_deactivate_license_nonce', '_wpzoom_rcb_plugin_license_deactivate_nonce' ) )
				return; // get out if we didn't click the Activate button

			// data to send in our API request
			$api_params = array(
				'edd_action' => 'deactivate_license',
				'license'    => $this->license_key,
				'item_name'  => urlencode( WPZOOM_RCB_ITEM_NAME ), // the name of our product in EDD
				'url'        => home_url()
			);

			// Call the custom API.
			$response = wp_remote_post( WPZOOM_RCB_STORE_URL, array( 'timeout' => 15, 'sslverify' => false, 'body' => $api_params ) );

			// make sure the response came back okay
			if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {

				if ( is_wp_error( $response ) ) {
					$message = $response->get_error_message();
				} else {
					$message = __( 'An error occurred, please try again.', 'wpzoom-recipe-card' );
				}

				$base_url = admin_url( 'options-general.php?page=' . WPZOOM_RCB_SETTINGS_PAGE . '&tab=tab-license' );
				$redirect = add_query_arg( array( 'sl_activation' => 'false', 'message' => urlencode( $message ) ), $base_url );

				wp_redirect( $redirect );
				exit();
			}

			// decode the license data
			$license_data = json_decode( wp_remote_retrieve_body( $response ) );

			// $license_data->license will be either "deactivated" or "failed"
			if( $license_data->license == 'deactivated' ) {
				unset( $this->options['wpzoom_rcb_plugin_license_status'] );
				update_option( 'wpzoom-recipe-card-settings', $this->options );
				delete_transient( 'wpzoom_rcb_plugin_license_message' );
			}

			wp_redirect( admin_url( 'options-general.php?page=' . WPZOOM_RCB_SETTINGS_PAGE . '&tab=tab-license' ) );
			exit();

		}
	}

	/**
	 * This is a means of catching errors from the activation method above and displaying it to the customer
	 * 
	 * @since 1.1.0
	 */
	public function admin_notices() {
		if ( isset( $_GET['sl_activation'] ) && ! empty( $_GET['message'] ) ) {

			switch( $_GET['sl_activation'] ) {

				case 'false':
					$message = urldecode( $_GET['message'] );
					?>
					<div class="error">
						<p><?php echo $message; ?></p>
					</div>
					<?php
					break;

				case 'true':
				default:
					// Developers can put a custom success message here for when activation is successful if they way.
					break;

			}
		}
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

	public function section_license_cb( $args ) {
	?>
	 	<p id="<?php echo esc_attr( $args['id'] ); ?>"><?php esc_html_e( 'Here text.', 'wpzoom-recipe-card' ) ?></p>
	<?php
	}
}

new WPZOOM_Settings();
