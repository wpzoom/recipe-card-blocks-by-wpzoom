<?php
/**
 * Enqueue CSS/JS of block to Elementor.
 *
 * @since   2.7.6
 * @package WPZOOM_Recipe_Card_Blocks
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WPZOOM_Elementor {
	public static $reusable_block_id = 0;

	/**
	 * Elementor load assets constructor.
	 *
	 * @since 2.7.6
	 * @access public
	 */
	public function __construct() {
		// Elementor compatibility using Reusable Blocks Extended
		add_action( 'elementor/preview/enqueue_styles', array( $this, 'preview_enqueue_styles' ), 0 );
		add_action( 'elementor/frontend/widget/before_render', array( $this, 'before_render_widget' ) );
		add_filter( 'wpzoom/recipe_card/print_button/attributes', array( $this, 'print_button_attributes' ) );
	}

	public function load_block_assets() {
		$assets_slug = WPZOOM_Assets_Manager::$_slug;

		wp_enqueue_script( $assets_slug . '-script' );
		wp_enqueue_script( $assets_slug . '-pinit' );

		// Styles.
		wp_enqueue_style( $assets_slug . '-style-css' );

		// Enable Google Fonts
		if ( '1' === WPZOOM_Settings::get( 'wpzoom_rcb_settings_enable_google_fonts' ) ) {
			wp_enqueue_style( $assets_slug . '-google-font' );
		}
	}

	public function preview_enqueue_styles() {
		$this->load_block_assets();
	}

	public function before_render_widget( $widget ) {
		if ( 'wp-widget-reblex-widget' === $widget->get_name() || 'shortcode' === $widget->get_name() ) {
			$settings           = $widget->get_settings();
			$reusable_block_id  = 0;
			$has_reusable_block = false;
			$whitelist_blocks   = array( 'wpzoom-recipe-card/block-recipe-card', 'wpzoom-recipe-card/block-ingredients', 'wpzoom-recipe-card/block-directions' );

			if ( 'wp-widget-reblex-widget' === $widget->get_name() ) {
				$reusable_block_id = isset( $settings['wp']['block_id'] ) ? absint( $settings['wp']['block_id'] ) : 0;
			} else {
				$shortcode_text    = isset( $settings['shortcode'] ) ? $settings['shortcode'] : '';
				$atts              = shortcode_parse_atts( str_replace( ']', ' ]', $shortcode_text ) ); // Add whitespace before shortcode close bracket to parse attribute in right way.
				$reusable_block_id = isset( $atts['id'] ) ? absint( $atts['id'] ) : 0;
			}

			if ( $reusable_block_id ) {
				foreach ( $whitelist_blocks as $block_name ) {
					if ( ! $has_reusable_block ) {
						$has_reusable_block = WPZOOM_Assets_Manager::has_reusable_block( $block_name, $reusable_block_id );
					}
				}
			}

			if ( $has_reusable_block ) {
				self::$reusable_block_id = $reusable_block_id;
				$this->load_block_assets();
			}
		}
	}

	/**
	 * Pass reusable block id attribute to print button
	 *
	 * @since 2.7.12
	 * @see filter hook `wpzoom/recipe_card/print_button/attributes`
	 *
	 * @param array $attributes The print button attributes.
	 * @return array
	 */
	public function print_button_attributes( $attributes ) {
		if ( self::$reusable_block_id > 0 ) {
			$attributes['data-reusable-block-id'] = self::$reusable_block_id;
		}
		return $attributes;
	}
}

new WPZOOM_Elementor();
