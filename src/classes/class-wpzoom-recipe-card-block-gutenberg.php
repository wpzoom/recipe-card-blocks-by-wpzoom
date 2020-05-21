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
		add_filter( 'block_categories', 			__CLASS__ . '::add_custom_category', 10, 2 );
		add_filter( 'image_size_names_choose', 		__CLASS__ . '::custom_image_sizes_choose' );

		add_action( 'init', 						__CLASS__ . '::register_custom_image_sizes' );
		add_action( 'init', 						__CLASS__ . '::register_block_types' );
		add_action( 'init', 						__CLASS__ . '::load_textdomain' );
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
		add_image_size( 'wpzoom-rcb-block-header', 			800, 530, true );
		add_image_size( 'wpzoom-rcb-block-header-square', 	530, 530, true );
		add_image_size( 'wpzoom-rcb-block-step-image', 		750 );
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
	        'wpzoom-rcb-block-header' => __( 'Recipe Card Block', 'wpzoom-recipe-card' ),
	        'wpzoom-rcb-block-header-square' => __( 'Recipe Card Block Square', 'wpzoom-recipe-card' ),
	        'wpzoom-rcb-block-step-image' => __( 'Recipe Card Step Image', 'wpzoom-recipe-card' )
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
					'slug' => 'wpzoom-recipe-card',
					'title' => __( 'WPZOOM - Recipe Card', 'wpzoom-recipe-card' ),
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
	 * Load the plugin textdomain
	 *
	 * @since 1.1.0
	 */
	public static function load_textdomain() {
		load_plugin_textdomain(
			'wpzoom-recipe-card',
			false,
			dirname( plugin_basename( WPZOOM_RCB_PLUGIN_DIR ) ) . '/languages/'
		);
	}
}

WPZOOM_Recipe_Card_Block_Gutenberg::instance();
