<?php
/**
 * Auto-inserted rating stars for Elementor-built posts.
 *
 * The block editor path hangs its the_content filter off the recipe card
 * block's render callback, which never runs on an Elementor page. Elementor
 * therefore needs its own hook to honour the Rating Display settings.
 *
 * @since   3.5.0
 * @package WPZOOM_Recipe_Card_Blocks
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Instance the class
WPZOOM_Elementor_Rating_Stars::instance();

/**
 * Class WPZOOM_Elementor_Rating_Stars
 */
class WPZOOM_Elementor_Rating_Stars {

	/**
	 * Instance
	 *
	 * @var WPZOOM_Elementor_Rating_Stars The single instance of the class.
	 * @since 3.5.0
	 * @access private
	 * @static
	 */
	private static $_instance = null;

	/**
	 * Instance
	 *
	 * Ensures only one instance of the class is loaded or can be loaded.
	 *
	 * @since 3.5.0
	 * @access public
	 * @static
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * The Constructor.
	 */
	public function __construct() {
		add_filter( 'the_content', array( $this, 'add_rating_stars_to_content' ) );
	}

	/**
	 * Optionally add the rating stars above and/or below an Elementor post.
	 *
	 * Runs at the default priority, so Elementor has already swapped its
	 * builder output into $content by the time we wrap it.
	 *
	 * @since 3.5.0
	 * @param string $content The post content.
	 * @return string
	 */
	public static function add_rating_stars_to_content( $content ) {
		if ( ! is_singular() ) {
			return $content;
		}

		if ( '1' !== WPZOOM_Settings::get( 'wpzoom_rcb_settings_display_rating_stars' ) ) {
			return $content;
		}

		if ( ! class_exists( 'WPZOOM_Recipe_Card_Block' ) || ! WPZOOM_Recipe_Card_Block::is_built_with_elementor() ) {
			return $content;
		}

		// The shortcode resolves the recipe ID for both Elementor recipe card
		// widgets, and returns an empty string when the page holds neither.
		$output = do_shortcode( '[wpzoom_rcb_rating]' );

		if ( empty( $output ) ) {
			return $content;
		}

		return WPZOOM_Recipe_Card_Block::place_rating_stars( $content, $output );
	}
}
