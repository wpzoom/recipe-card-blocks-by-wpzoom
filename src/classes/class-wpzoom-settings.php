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

		$this->register_ai_credits_ajax_endpoints();
		add_action( 'rest_api_init', array( $this, 'register_custom_rest_route' ) );
	}

	/**
	 * Register the custom rest route
	 */
	public function register_custom_rest_route() {
		register_rest_route(
			'wpzoomRCB/v1', '/saveGeneratedImage',
			array(
				'methods'  => 'POST',
				'callback' => array( $this, 'save_generated_image' ),
				'permission_callback' => '__return_true',
			)
		);
		register_rest_route(
			'wpzoomRCB/v1', '/updateCredits',
			array(
				'methods'  => 'POST',
				'callback' => array( $this, 'update_credits' ),
				'permission_callback' => '__return_true',
			)
		);
		register_rest_route(
			'wpzoomRCB/v1', '/getCredits',
			array(
				'methods'  => 'GET',
				'callback' => array( $this, 'get_credits' ),
				'permission_callback' => '__return_true',
			)
		);
		register_rest_route(
			'wpzoomRCB/v1', '/getLicenseData',
			array(
				'methods'  => 'GET',
				'callback' => array( $this, 'get_license_data' ),
				'permission_callback' => '__return_true',
			)
		);
	}

	/**
	 * Get credits
	 */
	public function get_credits() {
		return get_option( 'wpzoom_credits', [] );
	}

	/**
	 * Update credits
	 */
	public function update_credits( $request ) {
		$params = $request->get_params();

		$credits_data = get_option( 'wpzoom_credits', [] );
		if( isset( $params['free_credits'] ) ) {
			$credits_data['free_credits'] = $params['free_credits'];
		}
		$credits_data['remaining'] = $params['remaining'];
		$credits_data['total'] = $params['total'];

		update_option( 'wpzoom_credits', $credits_data );

		return $params;
	}

	public function get_license_data() {
		$license_data = get_transient( 'wpzoom_rcb_plugin_user_data' );

		if ( ! $license_data ) {
			$license_data = (object) [];
		}

		$license_data->endpoint_url = WPZOOM_RCB_STORE_URL;

		$license_data->chat_model = WPZOOM_Settings::get( 'wpzoom_rcb_settings_recipe_data_ai_chat_model' );

		$license_data->prepend_recipe_data_prompt = WPZOOM_Settings::get( 'wpzoom_rcb_settings_recipe_data_ai_prompt_prepend' );
		$license_data->append_recipe_data_prompt = WPZOOM_Settings::get( 'wpzoom_rcb_settings_recipe_data_ai_prompt_append' );

		$license_data->prepend_recipe_image_prompt = WPZOOM_Settings::get( 'wpzoom_rcb_settings_recipe_image_ai_prompt_prepend' );
		$license_data->append_recipe_image_prompt = WPZOOM_Settings::get( 'wpzoom_rcb_settings_recipe_image_ai_prompt_append' );

		return $license_data;
	}

	/**
	 * Save generated image
	 */
	public function save_generated_image( $request ) {
		require_once( ABSPATH . 'wp-admin/includes/media.php' );
		require_once( ABSPATH . 'wp-admin/includes/file.php' );
		require_once( ABSPATH . 'wp-admin/includes/image.php' );

		$image_url = $request->get_json_params();
		if ( ! filter_var( $image_url, FILTER_VALIDATE_URL ) ) {
			return $image_url;
		}
		$image_id  = media_sideload_image( $image_url, 0, __( 'AI generated image of a recipe card.', 'recipe-card-blocks-by-wpzoom' ), 'id' );
		$image     = [];
		if ( $image_id ) {
			$image_url = wp_get_attachment_image_url( $image_id, 'full' );
			$image_title = get_the_title( $image_id );

			$image['id']    = $image_id;
			$image['title'] = $image_title;
			$image['url']   = $image_url;
		}

		return $image;
	}

	/**
	 * Register the ajax endpoints for AI Credits
	 */
	public function register_ai_credits_ajax_endpoints() {
		add_action( 'wp_ajax_get_user_info_ai_credits', [ $this, 'get_user_info_ai_credits' ] );
		add_action( 'wp_ajax_logout_user_ai_credits', [ $this, 'logout_user_ai_credits' ] );
		add_action( 'wp_ajax_refresh_ai_credits', [ $this, 'refresh_ai_credits' ] );
	}

	/**
	 * Refresh AI Credits
	 */
	public function refresh_ai_credits() {

		if ( ! check_ajax_referer( 'refresh_user_ai_credits', 'nonce', false ) ) {
			wp_send_json( [
				'success' => false,
				'message' => 'Security verification failed.',
			] );
		}

		$license_data = get_transient( 'wpzoom_rcb_plugin_user_data' );

		if ( ! $license_data ) {
			$license_data = (object) [];
		}

		$credits_data = get_option( 'wpzoom_credits' );
		if ( ! $credits_data ) {
			$credits_data = array(
				'total'     => 0,
				'remaining' => 0,
				'ID'        => '',
			);
		}

		$credits_id = $credits_data['ID'] ?? '';

		if ( empty( $credits_id ) && ! empty( $license_data->user->ID ) ) {
			$credits_id = $license_data->user->ID;
		}

		if ( ! empty( $credits_id ) ) {
			$credits_api_params = array(
				'ID' => $credits_id,
			);
			$credits_response = wp_remote_post(
				esc_url( WPZOOM_RCB_STORE_URL . 'wp-json/wpzoomRCB/v1/checkCredits' ),
				array(
					'timeout'   => 15,
					'body'      => $credits_api_params,
				)
			);

			if ( ! is_wp_error( $credits_response ) ) {
				$credits_resp = json_decode( wp_remote_retrieve_body( $credits_response ) );
				if ( ! empty( $credits_resp->success ) ) {

					// Update credits
					if ( isset( $credits_resp->free_credits ) ) {
						$credits_data['free_credits']     = $credits_resp->free_credits;
					}

					$credits_data['remaining'] = isset( $credits_resp->remaining ) ? $credits_resp->remaining : $credits_data['remaining'];
					$credits_data['total']     = isset( $credits_resp->total ) ? $credits_resp->total : $credits_data['total'];
					$credits_data['ID']        = $credits_id;
					update_option( 'wpzoom_credits', $credits_data );
				}
				wp_send_json( $credits_resp );
			}
		}

	}

	/**
	 * Ajax request to get user info
	 */
	public function get_user_info_ai_credits() {

		if ( ! check_ajax_referer( 'get_user_info_ai_credits', 'nonce', false ) ) {
			wp_send_json( [
				'success' => false,
				'message' => 'Security verification failed.',
			] );
		}

		delete_transient( 'wpzoom_rcb_plugin_user_data' );

		$endpoint = WPZOOM_RCB_STORE_URL . 'wp-json/wpzoomRCB/v1/getUser';

		// data to send in our API request
		$api_params = [
			'username' => sanitize_text_field( $_POST['username'] ?? '' ),
			'password' => sanitize_text_field( $_POST['password'] ?? '' ),
		];

		// Call the custom API.
		$response = wp_remote_post( $endpoint, [
			'timeout' => 60,
			'body'    => $api_params,
		] );

		// make sure the response came back okay
		if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
			if ( is_wp_error( $response ) ) {
				$message = $response->get_error_message();
			} else {
				$message = __( 'An error occurred, please try again.', 'recipe-card-blocks-by-wpzoom' );
			}

			wp_send_json( [
				'success' => false,
				'message' => $message,
			] );
		}

		$user_data = json_decode( wp_remote_retrieve_body( $response ) );

		if ( ! $user_data || ! isset( $user_data->success ) || ! $user_data->success ) {
			wp_send_json( [
				'success' => false,
				'message' => $user_data->message ?? __( 'An error occurred, please try again.', 'recipe-card-blocks-by-wpzoom' ),
			] );
		}

		set_transient( 'wpzoom_rcb_plugin_user_data', $user_data, 60 * 60 * 24 );

		if( isset( $user_data->user->credits ) ) {
			update_option( 'wpzoom_credits', [
				'total'     => $user_data->user->credits->total,
				'remaining' => $user_data->user->credits->remaining,
				'ID'        => $user_data->user->ID,
			] );
		}

		wp_send_json( json_decode( wp_remote_retrieve_body( $response ) ) );
	
	}

	/**
	 * Ajax request to logout user
	 */
	public function logout_user_ai_credits() {

		if ( ! check_ajax_referer( 'logout_user_ai_credits', 'nonce', false ) ) {
			wp_send_json( [
				'success' => false,
				'message' => 'Security verification failed.',
			] );
		}

		// Delete user data
		delete_transient( 'wpzoom_rcb_plugin_user_data' );
		delete_option( 'wpzoom_credits' );

		wp_send_json( [
			'success' => true,
			'message' => 'User data has been deleted.',
		] );

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
					<a href="https://recipecard.io/pricing/?utm_source=wpadmin&utm_medium=rcbfree&utm_campaign=btnsettings" target="_blank" class="wpzoom-pro-link" style="color:#FFA921;"><strong><?php _e( 'Get the PRO version &rarr;', 'recipe-card-blocks-by-wpzoom' ); ?></strong></a>
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
						'id'       => 'wpzoom_section_recipe_recipe_heading',
						'title'    => __( 'Recipe Title', 'recipe-card-blocks-by-wpzoom' ),
						'page'     => 'wpzoom-recipe-card-settings-appearance',
						'callback' => '__return_false',
						'fields'   => array(
							array(
								'id'    => 'wpzoom_rcb_settings_recipe_title_tag',
								'title' => __( 'Recipe Title HTML Heading', 'recipe-card-blocks-by-wpzoom' ),
								'type'  => 'select',
								'args'  => array(
									'label_for'   => 'wpzoom_rcb_settings_recipe_title_tag',
									'class'       => 'wpzoom-rcb-field',
									'description' => esc_html__( 'Default HTML tag for Recipe Title.', 'recipe-card-blocks-by-wpzoom' ),
									'default'     => 'h2',
									'options'     => array(
										'h1' => __( 'H1', 'recipe-card-blocks-by-wpzoom' ),
										'h2' => __( 'H2', 'recipe-card-blocks-by-wpzoom' ),
										'h3' => __( 'H3', 'recipe-card-blocks-by-wpzoom' ),
										'h4' => __( 'H4', 'recipe-card-blocks-by-wpzoom' ),
									),
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
                        'id'       => 'wpzoom_section_pinterest_script',
                        'title'    => __( 'Pinterest Script', 'recipe-card-blocks-by-wpzoom' ),
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
			'ai' => array(
				'tab_id'       => 'tab-ai',
				'tab_title'    => __( 'AI Settings', 'recipe-card-blocks-by-wpzoom' ),
				'option_group' => 'wpzoom-recipe-card-settings-ai',
				'option_name'  => self::$option,
				'sections'     => array(
					array(
						'id'       => 'wpzoom_section_ai_chat_model_recipe_data',
						'title'    => __( 'OpenAI Model', 'recipe-card-blocks-by-wpzoom' ),
						'page'     => 'wpzoom-recipe-card-settings-ai',
						'callback' => '__return_false',
						'fields'   => array(
							array(
								'id'    => 'wpzoom_rcb_settings_recipe_data_ai_chat_model',
								'title' => __( 'Select the AI model to use', 'recipe-card-blocks-by-wpzoom' ),
								'type'  => 'select',
								'args'  => array(
									'label_for'   => 'wpzoom_rcb_settings_recipe_data_ai_chat_model',
									'class'       => 'wpzoom-rcb-field',
									'description' => __( '<strong>GPT 4o</strong> - Fastest and most advanced model<br/><strong>GPT 3.5 Turbo</strong> - Legacy model, good for simple recipes.', 'recipe-card-blocks-by-wpzoom' ),
									'default'     => 'gpt-4o',
									'options'     => array(
										'gpt-4o'        => esc_html__( 'GPT-4o', 'recipe-card-blocks-by-wpzoom' ),
										'gpt-3.5-turbo' => esc_html__( 'GPT-3.5 Turbo', 'recipe-card-blocks-by-wpzoom' ),
									),
								),
							),
						),
					),
					array(
						'id'       => 'wpzoom_section_ai_prompt_recipe_data',
						'title'    => __( 'Recipe Data Prompt', 'recipe-card-blocks-by-wpzoom' ),
						'page'     => 'wpzoom-recipe-card-settings-ai',
						'callback' => '__return_false',
						'fields'   => array(
							array(
								'id'    => 'wpzoom_rcb_settings_recipe_data_ai_prompt_prepend',
								'title' => __( 'Prepend', 'recipe-card-blocks-by-wpzoom' ),
								'type'  => 'input',
								'args'  => array(
									'label_for'   => 'wpzoom_rcb_settings_recipe_data_ai_prompt_prepend',
									'class'       => 'wpzoom-rcb-field',
									'description' => esc_html__( 'e.g.: generate the recipe in the metric system', 'recipe-card-blocks-by-wpzoom' ),
									'default'     => '',
									'type'        => 'text',
								),
							),
							array(
								'id'    => 'wpzoom_rcb_settings_recipe_data_ai_prompt_append',
								'title' => __( 'Append', 'recipe-card-blocks-by-wpzoom' ),
								'type'  => 'input',
								'args'  => array(
									'label_for'   => 'wpzoom_rcb_settings_recipe_data_ai_prompt_append',
									'class'       => 'wpzoom-rcb-field',
									'description' => esc_html__( 'e.g.: generate the recipe in Spanish language', 'recipe-card-blocks-by-wpzoom' ),
									'default'     => '',
									'type'        => 'text',
								),
							),
						),
					),
					array(
						'id'       => 'wpzoom_section_ai_prompt_recipe_image',
						'title'    => __( 'Recipe Image Prompt', 'recipe-card-blocks-by-wpzoom' ),
						'page'     => 'wpzoom-recipe-card-settings-ai',
						'callback' => '__return_false',
						'fields'   => array(
							array(
								'id'    => 'wpzoom_rcb_settings_recipe_image_ai_prompt_prepend',
								'title' => __( 'Prepend', 'recipe-card-blocks-by-wpzoom' ),
								'type'  => 'input',
								'args'  => array(
									'label_for'   => 'wpzoom_rcb_settings_recipe_image_ai_prompt_prepend',
									'class'       => 'wpzoom-rcb-field',
									'description' => esc_html__( 'Prepend prompt for recipe image to AI.', 'recipe-card-blocks-by-wpzoom' ),
									'default'     => 'Generate realistic photography for:',
									'type'        => 'text',
								),
							),
							array(
								'id'    => 'wpzoom_rcb_settings_recipe_image_ai_prompt_append',
								'title' => __( 'Append', 'recipe-card-blocks-by-wpzoom' ),
								'type'  => 'input',
								'args'  => array(
									'label_for'   => 'wpzoom_rcb_settings_recipe_image_ai_prompt_append',
									'class'       => 'wpzoom-rcb-field',
									'description' => esc_html__( 'Append prompt for recipe image to AI.', 'recipe-card-blocks-by-wpzoom' ),
									'default'     => '',
									'type'        => 'text',
								),
							),
						),
					),
				),
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
		<div class="wrap" style="margin:0">
			<?php do_action( 'wpzoom_rcb_welcome_banner' ); ?>

			<div class="wpz-onboard_wrapper">
				<h2 style="display:none;"></h2>
				<div class="wpz-onboard_header">
					<div class="wpz-onboard_title-wrapper">
						<h1 class="wpz-onboard_title"><svg width="30" height="30" viewBox="0 0 500 500" fill="none" xmlns="http://www.w3.org/2000/svg"><g clip-path="url(#clip0_408_204)"><circle cx="250" cy="250" r="250" fill="#E1581A"/><path fill-rule="evenodd" clip-rule="evenodd" d="M252.597 100C221.304 100 193.611 117.861 178.955 146.232C140.058 146.561 110 180.667 110 221.822C110 251.163 125.719 277.528 149.758 289.881V381.396C149.758 390.834 156.879 399 166.3 399H333.775C343.196 399 350.317 390.834 350.317 381.396V267.062C374.304 254.662 390 228.316 390 199.037C390 157.678 358.917 123.483 320.067 123.483C316.597 123.483 313.132 123.77 309.694 124.334C294.308 108.794 274.007 100 252.597 100ZM204.614 170.955C212.223 149.098 231.113 135.208 252.597 135.208C267.726 135.208 281.464 142.141 291.342 154.94C295.598 160.449 302.673 162.77 309.186 160.524C312.757 159.302 316.424 158.691 320.067 158.691C340.096 158.691 356.916 176.495 356.916 199.037C356.916 217.48 345.452 233.354 329.465 237.933C322.085 240.053 317.233 247.147 317.233 254.931V302.003H182.842V277.716C182.842 269.917 177.976 262.803 170.558 260.702C154.57 256.173 143.084 240.307 143.084 221.822C143.084 199.629 160.141 181.436 179.524 181.436C181.644 181.436 183.782 181.663 185.883 182.105L185.89 182.107C194.008 183.801 201.858 178.878 204.614 170.955ZM182.842 363.792V337.211H317.233V363.792H182.842Z" fill="white"/></g><defs><clipPath id="clip0_408_204"><rect width="500" height="500" fill="white"/></clipPath></defs></svg> Recipe Card Blocks <span>Free</span></h1>
						<h2 class="wpz-onboard_framework-version">
							<?php printf( esc_html__( 'v. %s', 'recipe-card-blocks-by-wpzoom' ), WPZOOM_RCB_VERSION ); ?>
						</h2>
					</div>
					<div class="wpz-onboard_quick_links">                    
						<ul class="wpz-onboard_tabs">
						<li class="ui-tabs-active"><a href="<?php echo esc_url( admin_url( 'admin.php?page=wpzoom-recipe-card-settings') ); ?>"><svg width="18" height="18" viewBox="0 0 36 36" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path opacity="0.3" d="M28.9201 12.9L27.8701 11.085L25.9651 11.85L24.3751 12.495L23.0101 11.445C22.4251 10.995 21.8101 10.635 21.1651 10.38L19.5751 9.735L19.3351 8.04L19.0501 6H16.9501L16.6651 8.025L16.4251 9.72L14.8351 10.38C14.2201 10.635 13.6051 10.995 12.9601 11.475L11.6101 12.495L10.0351 11.865L8.13008 11.085L7.08008 12.9L8.70008 14.16L10.0351 15.21L9.82508 16.905C9.78008 17.355 9.75008 17.7 9.75008 18C9.75008 18.3 9.78008 18.645 9.82508 19.095L10.0351 20.79L8.70008 21.84L7.08008 23.1L8.13008 24.915L10.0351 24.15L11.6251 23.505L12.9901 24.555C13.5751 25.005 14.1901 25.365 14.8351 25.62L16.4251 26.265L16.6651 27.96L16.9501 30H19.0351L19.3201 27.975L19.5601 26.28L21.1501 25.635C21.7651 25.38 22.3801 25.02 23.0251 24.54L24.3751 23.52L25.9351 24.15L27.8401 24.915L28.8901 23.1L27.2701 21.84L25.9351 20.79L26.1451 19.095C26.2051 18.63 26.2201 18.315 26.2201 18C26.2201 17.685 26.1901 17.355 26.1451 16.905L25.9351 15.21L27.2701 14.16L28.9201 12.9ZM18.0001 24C14.6851 24 12.0001 21.315 12.0001 18C12.0001 14.685 14.6851 12 18.0001 12C21.3151 12 24.0001 14.685 24.0001 18C24.0001 21.315 21.3151 24 18.0001 24Z" fill="black" />
				<path d="M29.1452 19.47C29.2052 18.99 29.2502 18.51 29.2502 18C29.2502 17.49 29.2052 17.01 29.1452 16.53L32.3102 14.055C32.5952 13.83 32.6702 13.425 32.4902 13.095L29.4902 7.905C29.3552 7.665 29.1002 7.53 28.8302 7.53C28.7402 7.53 28.6502 7.545 28.5752 7.575L24.8402 9.075C24.0602 8.475 23.2202 7.98 22.3052 7.605L21.7352 3.63C21.6902 3.27 21.3752 3 21.0002 3H15.0002C14.6252 3 14.3102 3.27 14.2652 3.63L13.6952 7.605C12.7802 7.98 11.9402 8.49 11.1602 9.075L7.42525 7.575C7.33525 7.545 7.24525 7.53 7.15525 7.53C6.90025 7.53 6.64525 7.665 6.51025 7.905L3.51025 13.095C3.31525 13.425 3.40525 13.83 3.69025 14.055L6.85525 16.53C6.79525 17.01 6.75025 17.505 6.75025 18C6.75025 18.495 6.79525 18.99 6.85525 19.47L3.69025 21.945C3.40525 22.17 3.33025 22.575 3.51025 22.905L6.51025 28.095C6.64525 28.335 6.90025 28.47 7.17025 28.47C7.26025 28.47 7.35025 28.455 7.42525 28.425L11.1602 26.925C11.9402 27.525 12.7802 28.02 13.6952 28.395L14.2652 32.37C14.3102 32.73 14.6252 33 15.0002 33H21.0002C21.3752 33 21.6902 32.73 21.7352 32.37L22.3052 28.395C23.2202 28.02 24.0602 27.51 24.8402 26.925L28.5752 28.425C28.6652 28.455 28.7552 28.47 28.8452 28.47C29.1002 28.47 29.3552 28.335 29.4902 28.095L32.4902 22.905C32.6702 22.575 32.5952 22.17 32.3102 21.945L29.1452 19.47ZM26.1752 16.905C26.2352 17.37 26.2502 17.685 26.2502 18C26.2502 18.315 26.2202 18.645 26.1752 19.095L25.9652 20.79L27.3002 21.84L28.9202 23.1L27.8702 24.915L25.9652 24.15L24.4052 23.52L23.0552 24.54C22.4102 25.02 21.7952 25.38 21.1802 25.635L19.5902 26.28L19.3502 27.975L19.0502 30H16.9502L16.6652 27.975L16.4252 26.28L14.8352 25.635C14.1902 25.365 13.5902 25.02 12.9902 24.57L11.6252 23.52L10.0352 24.165L8.13025 24.93L7.08025 23.115L8.70025 21.855L10.0352 20.805L9.82525 19.11C9.78025 18.645 9.75025 18.3 9.75025 18C9.75025 17.7 9.78025 17.355 9.82525 16.905L10.0352 15.21L8.70025 14.16L7.08025 12.9L8.13025 11.085L10.0352 11.85L11.5952 12.48L12.9452 11.46C13.5902 10.98 14.2052 10.62 14.8202 10.365L16.4102 9.72L16.6502 8.025L16.9502 6H19.0352L19.3202 8.025L19.5602 9.72L21.1502 10.365C21.7952 10.635 22.3952 10.98 22.9952 11.43L24.3602 12.48L25.9502 11.835L27.8552 11.07L28.9052 12.885L27.3002 14.16L25.9652 15.21L26.1752 16.905ZM18.0002 12C14.6852 12 12.0002 14.685 12.0002 18C12.0002 21.315 14.6852 24 18.0002 24C21.3152 24 24.0002 21.315 24.0002 18C24.0002 14.685 21.3152 12 18.0002 12ZM18.0002 21C16.3502 21 15.0002 19.65 15.0002 18C15.0002 16.35 16.3502 15 18.0002 15C19.6502 15 21.0002 16.35 21.0002 18C21.0002 19.65 19.6502 21 18.0002 21Z" fill="black" />
				</svg><?php  esc_html_e( 'Settings', 'recipe-card-blocks-by-wpzoom' ); ?></a></li>
						<li><a href="<?php echo esc_url( admin_url( 'admin.php?page=admin-license') ); ?>"><svg width="20" height="18" viewBox="0 0 20 18" fill="none" xmlns="http://www.w3.org/2000/svg">
<path fill-rule="evenodd" clip-rule="evenodd" d="M13.33 13.9924C17.0427 13.82 19.9999 10.7553 19.9999 7C19.9999 3.13401 16.8659 0 12.9999 0C10.2045 0 7.79182 1.63858 6.66992 4.00764C6.69726 4.00637 6.72464 4.00526 6.75206 4.00431C3.0009 4.13489 0 7.217 0 11C0 14.866 3.13401 18 7 18C9.7954 18 12.2081 16.3614 13.33 13.9924ZM13.9412 11.9115C16.2526 11.4712 17.9999 9.43966 17.9999 7C17.9999 4.23858 15.7613 2 12.9999 2C11.2581 2 9.7243 2.89066 8.82912 4.24139C11.808 5.04564 14 7.76684 14 11C14 11.3089 13.98 11.6132 13.9412 11.9115ZM7.11834 4.00098C7.07895 4.00033 7.03947 4 6.99991 4L7.11834 4.00098ZM6.50708 11.6027L6.88329 10.5131C6.94745 10.3275 7.21926 10.3275 7.28342 10.5131L7.65963 11.6027C7.74243 11.8422 7.88195 12.0598 8.06712 12.2383C8.25229 12.4168 8.47803 12.5512 8.72643 12.6309L9.85564 12.9937C10.0481 13.0556 10.0481 13.3177 9.85564 13.3796L8.72584 13.7424C8.47749 13.8223 8.25183 13.9568 8.06676 14.1354C7.88169 14.314 7.7423 14.5317 7.65963 14.7713L7.28342 15.8603C7.26958 15.9009 7.24279 15.9363 7.20687 15.9614C7.17094 15.9865 7.12771 16 7.08335 16C7.03899 16 6.99577 15.9865 6.95984 15.9614C6.92392 15.9363 6.89713 15.9009 6.88329 15.8603L6.50708 14.7707C6.42433 14.5312 6.2849 14.3137 6.09984 14.1352C5.91477 13.9567 5.68916 13.8222 5.44086 13.7424L4.31107 13.3796C4.26892 13.3663 4.23222 13.3404 4.2062 13.3058C4.18018 13.2712 4.16617 13.2295 4.16617 13.1867C4.16617 13.1439 4.18018 13.1022 4.2062 13.0676C4.23222 13.0329 4.26892 13.0071 4.31107 12.9937L5.44086 12.6309C5.68916 12.5511 5.91477 12.4167 6.09984 12.2382C6.2849 12.0597 6.42433 11.8421 6.50708 11.6027ZM4.63012 7.64491C4.63848 7.62055 4.65459 7.59935 4.67616 7.58433C4.69773 7.5693 4.72366 7.56122 4.75027 7.56122C4.77688 7.56122 4.80281 7.5693 4.82438 7.58433C4.84595 7.59935 4.86206 7.62055 4.87042 7.64491L5.09615 8.29854C5.19706 8.58991 5.43386 8.81829 5.736 8.9156L6.41376 9.13329C6.43902 9.14136 6.461 9.1569 6.47658 9.1777C6.49216 9.1985 6.50054 9.22351 6.50054 9.24917C6.50054 9.27483 6.49216 9.29984 6.47658 9.32064C6.461 9.34144 6.43902 9.35698 6.41376 9.36504L5.736 9.58273C5.5869 9.63045 5.45141 9.71109 5.34033 9.81822C5.22925 9.92534 5.14563 10.056 5.09615 10.1998L4.87042 10.8534C4.86206 10.8778 4.84595 10.899 4.82438 10.914C4.80281 10.929 4.77688 10.9371 4.75027 10.9371C4.72366 10.9371 4.69773 10.929 4.67616 10.914C4.65459 10.899 4.63848 10.8778 4.63012 10.8534L4.40439 10.1998C4.35491 10.056 4.2713 9.92534 4.16021 9.81822C4.04913 9.71109 3.91364 9.63045 3.76454 9.58273L3.08678 9.36504C3.06152 9.35698 3.03954 9.34144 3.02396 9.32064C3.00839 9.29984 3 9.27483 3 9.24917C3 9.22351 3.00839 9.1985 3.02396 9.1777C3.03954 9.1569 3.06152 9.14136 3.08678 9.13329L3.76454 8.9156C3.91364 8.86788 4.04913 8.78725 4.16021 8.68012C4.2713 8.57299 4.35491 8.44233 4.40439 8.29854L4.63012 7.64491ZM8.75326 7.05485C8.75902 7.03883 8.76981 7.02494 8.78413 7.01511C8.79845 7.00528 8.81559 7 8.83317 7C8.85074 7 8.86788 7.00528 8.88221 7.01511C8.89653 7.02494 8.90732 7.03883 8.91307 7.05485L9.06356 7.49022C9.13063 7.68485 9.2887 7.83729 9.49051 7.90197L9.94196 8.0471C9.95857 8.05265 9.97297 8.06306 9.98317 8.07687C9.99336 8.09068 9.99883 8.10721 9.99883 8.12416C9.99883 8.14111 9.99336 8.15764 9.98317 8.17146C9.97297 8.18527 9.95857 8.19567 9.94196 8.20123L9.49051 8.34635C9.39115 8.37841 9.30086 8.43229 9.22677 8.50375C9.15268 8.5752 9.0968 8.66227 9.06356 8.7581L8.91307 9.19348C8.90732 9.2095 8.89653 9.22338 8.88221 9.23321C8.86788 9.24304 8.85074 9.24833 8.83317 9.24833C8.81559 9.24833 8.79845 9.24304 8.78413 9.23321C8.76981 9.22338 8.75902 9.2095 8.75326 9.19348L8.60277 8.7581C8.56953 8.66227 8.51366 8.5752 8.43957 8.50375C8.36547 8.43229 8.27519 8.37841 8.17582 8.34635L7.72495 8.20123C7.70834 8.19567 7.69394 8.18527 7.68375 8.17146C7.67356 8.15764 7.66808 8.14111 7.66808 8.12416C7.66808 8.10721 7.67356 8.09068 7.68375 8.07687C7.69394 8.06306 7.70834 8.05265 7.72495 8.0471L8.1764 7.90197C8.37821 7.83729 8.53628 7.68485 8.60336 7.49022L8.75326 7.05485Z" fill="#242628"/>
</svg> <?php  esc_html_e( 'AI Credits', 'recipe-card-blocks-by-wpzoom' ); ?></a></li>
						<li><a href="<?php echo esc_url( admin_url( 'admin.php?page=wpzoom-recipe-card-vs-pro' ) ) ?>" title="Quick Start"><svg width="18" height="18" viewBox="0 0 13 15" fill="none" xmlns="https://www.w3.org/2000/svg"><path d="M0.166992 14.5V0.333332H7.66699L8.00033 2H12.667V10.3333H6.83366L6.50033 8.66667H1.83366V14.5H0.166992ZM8.20866 8.66667H11.0003V3.66667H6.62533L6.29199 2H1.83366V7H7.87533L8.20866 8.66667Z" fill="#000"></path></svg> <?php esc_html_e( 'PRO Features', 'recipe-card-blocks-by-wpzoom' ); ?></a></li>
						<li><a href="<?php echo esc_url( admin_url( 'admin.php?page=wpzoom-recipe-card-vs-pro#vs-pro' ) ) ?>" title="Free vs. PRO"><svg fill="none" height="24" viewBox="0 0 24 24" width="24" xmlns="https://www.w3.org/2000/svg"><path clip-rule="evenodd" d="M15 5.75C11.5482 5.75 8.75 8.54822 8.75 12C8.75 15.4518 11.5482 18.25 15 18.25C15.9599 18.25 16.8674 18.0341 17.6782 17.6489C18.0523 17.4712 18.4997 17.6304 18.6774 18.0045C18.8552 18.3787 18.696 18.8261 18.3218 19.0038C17.3141 19.4825 16.1873 19.75 15 19.75C10.7198 19.75 7.25 16.2802 7.25 12C7.25 7.71979 10.7198 4.25 15 4.25C19.2802 4.25 22.75 7.71979 22.75 12C22.75 12.7682 22.638 13.5115 22.429 14.2139C22.3108 14.6109 21.8932 14.837 21.4962 14.7188C21.0992 14.6007 20.8731 14.1831 20.9913 13.7861C21.1594 13.221 21.25 12.6218 21.25 12C21.25 8.54822 18.4518 5.75 15 5.75Z" fill="black" fill-rule="evenodd"/><path clip-rule="evenodd" d="M5.25 5C5.25 4.58579 5.58579 4.25 6 4.25H15C15.4142 4.25 15.75 4.58579 15.75 5C15.75 5.41421 15.4142 5.75 15 5.75H6C5.58579 5.75 5.25 5.41421 5.25 5Z" fill="black" fill-rule="evenodd"/><path clip-rule="evenodd" d="M4.75 8.5C4.75 8.08579 5.08579 7.75 5.5 7.75H8.5C8.91421 7.75 9.25 8.08579 9.25 8.5C9.25 8.91421 8.91421 9.25 8.5 9.25H5.5C5.08579 9.25 4.75 8.91421 4.75 8.5Z" fill="black" fill-rule="evenodd"/><path clip-rule="evenodd" d="M1.25 8.5C1.25 8.08579 1.58579 7.75 2 7.75H3.5C3.91421 7.75 4.25 8.08579 4.25 8.5C4.25 8.91421 3.91421 9.25 3.5 9.25H2C1.58579 9.25 1.25 8.91421 1.25 8.5Z" fill="black" fill-rule="evenodd"/><path clip-rule="evenodd" d="M3.25 12.5C3.25 12.0858 3.58579 11.75 4 11.75H8C8.41421 11.75 8.75 12.0858 8.75 12.5C8.75 12.9142 8.41421 13.25 8 13.25H4C3.58579 13.25 3.25 12.9142 3.25 12.5Z" fill="black" fill-rule="evenodd"/><path clip-rule="evenodd" d="M12.376 8.58397C12.5151 8.37533 12.7492 8.25 13 8.25H17C17.2508 8.25 17.4849 8.37533 17.624 8.58397L19.624 11.584C19.792 11.8359 19.792 12.1641 19.624 12.416L17.624 15.416C17.4849 15.6247 17.2508 15.75 17 15.75H13C12.7492 15.75 12.5151 15.6247 12.376 15.416L10.376 12.416C10.208 12.1641 10.208 11.8359 10.376 11.584L12.376 8.58397ZM13.4014 9.75L11.9014 12L13.4014 14.25H16.5986L18.0986 12L16.5986 9.75H13.4014Z" fill="black" fill-rule="evenodd"/></svg> <?php esc_html_e( 'Free vs. PRO', 'recipe-card-blocks-by-wpzoom' ); ?></a></li>
						</ul>
					</div>
				</div>
			</div>



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


                   <div class="wpz-onboard_wrapper">
                       <div class="wpz-onboard_content-wrapper">
                           <div class="wpz-onboard_content">
                               <div class="wpz-onboard_content-side">

                                   <div class="wpz-onboard_content-side-section discover-premium">
                                       <h3 class="wpz-onboard_content-side-section-title icon-docs">
                                           <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="https://www.w3.org/2000/svg">
                                           <mask id="mask0_3409_3568" style="mask-type:alpha" maskUnits="userSpaceOnUse" x="0" y="0" width="24" height="24">
                                           <rect width="24" height="24" fill="#D9D9D9"/>
                                           </mask>
                                           <g mask="url(#mask0_3409_3568)">
                                           <path d="M19 9L17.75 6.25L15 5L17.75 3.75L19 1L20.25 3.75L23 5L20.25 6.25L19 9ZM19 23L17.75 20.25L15 19L17.75 17.75L19 15L20.25 17.75L23 19L20.25 20.25L19 23ZM9 20L6.5 14.5L1 12L6.5 9.5L9 4L11.5 9.5L17 12L11.5 14.5L9 20ZM9 15.15L10 13L12.15 12L10 11L9 8.85L8 11L5.85 12L8 13L9 15.15Z" fill="white"/>
                                           </g>
                                           </svg> <?php esc_html_e( 'Recipe Card Blocks PRO', 'recipe-card-blocks-by-wpzoom' ); ?></h3>
                                       <p class="wpz-onboard_content-side-section-content"><?php esc_html_e( 'Unlock advanced customization options with the PRO version to make your recipe cards truly unique. Add videos, nutritional facts, and more to engage your readers like never before!', 'recipe-card-blocks-by-wpzoom' ); ?></p>

                                       <ul>
                                           <li><span class="dashicons dashicons-yes"></span> Adjustable Servings</li>
                                           <li><span class="dashicons dashicons-yes"></span> Unit Conversion</li>
                                           <li><span class="dashicons dashicons-yes"></span> Recipe Roundups</li>
                                           <li><span class="dashicons dashicons-yes"></span> Recipe Index Block</li>
                                           <li><span class="dashicons dashicons-yes"></span> Star Rating</li>
                                           <li><span class="dashicons dashicons-yes"></span> Equipment</li>
                                           <li><span class="dashicons dashicons-yes"></span> ...and many more</li>
                                       </ul>
                                       <div class="wpz-onboard_content-side-section-button">
                                           <a href="<?php echo esc_url( __( 'https://recipecard.io/features/?utm_source=wpadmin&utm_medium=rcb-settings&utm_campaign=btn-right-col', 'recipe-card-blocks-by-wpzoom' ) ); ?>" target="_blank" class="button button-primary"><?php esc_html_e( 'Discover All PRO Features &rarr;', 'recipe-card-blocks-by-wpzoom' ); ?></a>

                                       </div>

                                   </div>
                               </div>
                           </div>
                       </div>
                   </div>



                    <?php
                    $current_user = wp_get_current_user();
                    ?>
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
                                        <input aria-label="email" aria-required="true" type="email" value="<?php echo esc_attr($current_user->user_email); ?>" class="form-control" data-inputmask="" name="fields[email]" placeholder="Enter your email address" autocomplete="email">
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
