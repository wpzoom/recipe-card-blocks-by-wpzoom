<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main WPZOOM_Recipe_Card_Block_Gutenberg Class.
 *
 * @since 1.1.0
 */
final class WPZOOM_Recipe_Card_Block_Gutenberg {
	/**
	 * This plugin's instance.
	 *
	 * @var WPZOOM_Recipe_Card_Block_Gutenberg
	 * @since 1.1.0
	 */
	private static $instance;

	/**
	 * Main WPZOOM_Recipe_Card_Block_Gutenberg Instance.
	 *
	 * Insures that only one instance of WPZOOM_Recipe_Card_Block_Gutenberg exists in memory at any one
	 * time. Also prevents needing to define globals all over the place.
	 *
	 * @since 1.1.0
	 * @static
	 * @uses WPZOOM_Recipe_Card_Block_Gutenberg::action_hooks() Load actions hooks.
	 * @return object|WPZOOM_Recipe_Card_Block_Gutenberg The one true WPZOOM_Recipe_Card_Block_Gutenberg
	 */
	public static function instance() {
		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof WPZOOM_Recipe_Card_Block_Gutenberg ) ) {
			self::$instance = new WPZOOM_Recipe_Card_Block_Gutenberg();
			self::action_hooks();
		}
		return self::$instance;
	}

	/**
	 * Load actions
	 *
	 * @access private
	 * @return void
	 */
	private static function action_hooks() {
		global $wp_version;

		if ( version_compare( $wp_version, '5.8', '<' ) ) {
			add_filter( 'block_categories', __CLASS__ . '::add_custom_category', 10, 2 );
		} else {
			add_filter( 'block_categories_all', __CLASS__ . '::add_custom_category', 10, 2 );
		}
		add_filter( 'image_size_names_choose', __CLASS__ . '::custom_image_sizes_choose' );

		add_action( 'after_setup_theme', __CLASS__ . '::register_custom_image_sizes' );
		add_action( 'init', __CLASS__ . '::register_block_types' );
		add_action( 'init', __CLASS__ . '::load_textdomain' );
	}

	/**
	 * Register Block Types
	 */
	public static function register_block_types() {
		$integrations   = array();
		$integrations[] = new WPZOOM_Structured_Data_Render();

		foreach ( $integrations as $integration ) {
			$integration->register_hooks();
		}
	}

	/**
	 * Register custom image size
	 *
	 * @since 2.1.1
	 */
	public static function register_custom_image_sizes() {
		if ( function_exists( 'fly_add_image_size' ) ) {
			fly_add_image_size( 'wpzoom-rcb-block-header', 800, 530, true );
			fly_add_image_size( 'wpzoom-rcb-block-header-square', 530, 530, true );
			fly_add_image_size( 'wpzoom-rcb-block-step-image', 750 );

			// Add image size for recipe Schema.org markup
			fly_add_image_size( 'wpzoom-rcb-structured-data-1_1', 500, 500, true );
			fly_add_image_size( 'wpzoom-rcb-structured-data-4_3', 500, 375, true );
			fly_add_image_size( 'wpzoom-rcb-structured-data-16_9', 480, 270, true );
		} else {
			add_image_size( 'wpzoom-rcb-block-header', 800, 530, true );
			add_image_size( 'wpzoom-rcb-block-header-square', 530, 530, true );
			add_image_size( 'wpzoom-rcb-block-step-image', 750 );

			// Add image size for recipe Schema.org markup
			add_image_size( 'wpzoom-rcb-structured-data-1_1', 500, 500, true );
			add_image_size( 'wpzoom-rcb-structured-data-4_3', 500, 375, true );
			add_image_size( 'wpzoom-rcb-structured-data-16_9', 480, 270, true );
		}
	}

	/**
	 * Make custom sizes selectable from your WordPress admin
	 *
	 * @since 2.1.1
	 * @param array $size_names  The list of registered sizes
	 * @return array
	 */
	public static function custom_image_sizes_choose( $size_names ) {
		$new_sizes = array(
			'wpzoom-rcb-block-header'        => __( 'Recipe Card Block', 'recipe-card-blocks-by-wpzoom' ),
			'wpzoom-rcb-block-header-square' => __( 'Recipe Card Block Square', 'recipe-card-blocks-by-wpzoom' ),
			'wpzoom-rcb-block-step-image'    => __( 'Recipe Card Step Image', 'recipe-card-blocks-by-wpzoom' ),
		);
		return array_merge( $size_names, $new_sizes );
	}

	/**
	 * Add custom block category
	 *
	 * @since 1.1.0
	 */
	public static function add_custom_category( $categories, $post ) {
		return array_merge(
			$categories,
			array(
				array(
					'slug'  => 'wpzoom-recipe-card',
					'title' => __( 'Recipe Card Blocks', 'recipe-card-blocks-by-wpzoom' ),
				),
			)
		);
	}

	/**
	 * Check if pro exists.
	 *
	 * @since 1.1.0
	 * @access public
	 */
	public static function has_pro() {
		if ( true === WPZOOM_RCB_HAS_PRO ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Check if pro is activated.
	 *
	 * @since 1.1.0
	 * @access public
	 */
	public static function is_pro() {
		if ( class_exists( 'WPZOOM_Premium_Recipe_Card_Block' ) ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Check if is AMP endpoint
	 *
	 * @since 2.6.5
	 * @return boolean
	 */
	public static function is_AMP() {
		$ampforwp_is_amp_endpoint = function_exists( 'ampforwp_is_amp_endpoint' ) && ampforwp_is_amp_endpoint();
		$is_amp_endpoint          = function_exists( 'is_amp_endpoint' ) && is_amp_endpoint();

		return $ampforwp_is_amp_endpoint || $is_amp_endpoint;
	}

	/**
	 * Load the plugin textdomain
	 *
	 * @since 1.1.0
	 */
	public static function load_textdomain() {
		load_plugin_textdomain(
			'recipe-card-blocks-by-wpzoom',
			false,
			dirname( plugin_basename( WPZOOM_RCB_PLUGIN_DIR ) ) . '/languages/'
		);
	}
}

WPZOOM_Recipe_Card_Block_Gutenberg::instance();
