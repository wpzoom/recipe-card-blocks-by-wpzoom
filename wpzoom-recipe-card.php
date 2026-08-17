<?php
/**
 * Plugin Name: Recipe Card Blocks
 * Plugin URI: https://recipecard.io/
 * Description: Recipe Card Blocks with Schema Markup — create SEO-optimized recipes with Gutenberg, Elementor & AMP support.
 * Author: WPZOOM
 * Author URI: https://recipecard.io/
 * Version: 3.4.19
 * Copyright: (c) 2026 WPZOOM
 * License: GPL2+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: recipe-card-blocks-by-wpzoom
 * Domain Path: /languages
 * Elementor tested up to: 4.16.6
 * Elementor Pro tested up to: 4.16.6
 *
 * @package   WPZOOM_Recipe_Card_Blocks
 * @author    WPZOOM
 * @license   GPL-2+
 */

require_once 'src/classes/class-wpzoom-plugin-loader.php';

/**
 * Redirect to the WPZOOM Recipe Card Getting Started page on single plugin activation
 *
 * @since 1.2.0
 */
if ( ! function_exists( 'recipe_card_block_plugin_activation_redirect' ) ) {
	function recipe_card_block_plugin_activation_redirect() {
		if ( get_option( 'wpzoom_rcb_do_activation_redirect', false ) ) {
			delete_option( 'wpzoom_rcb_do_activation_redirect' );
			$escaping_data = isset( $_GET['activate-multi'] ) ? sanitize_text_field( $_GET['activate-multi'] ) : '';
			if ( '' === $escaping_data ) {
				wp_redirect( 'admin.php?page=wpzoom-recipe-card-settings' );
			}
		}
	}
}
add_action( 'admin_init', 'recipe_card_block_plugin_activation_redirect' );


/**
 * Check block is registered.
 *
 * @since 2.0.1
 */
if ( ! function_exists( 'wpzoom_rcb_block_is_registered' ) ) {
	function wpzoom_rcb_block_is_registered( $name ) {
		$WP_Block_Type_Registry = new WP_Block_Type_Registry();
		return $WP_Block_Type_Registry->is_registered( $name );
	}
}

/**
 * Show the rating form or the plain rating number for a recipe.
 *
 * @since 3.5.0
 * @param int    $recipe_ID The recipe ID ratings are keyed to.
 * @param string $type      'form' for the interactive widget, 'number' for text.
 * @param string $label     Optional label used by the 'number' variant.
 * @return string
 */
if ( ! function_exists( 'wpzoom_rating_stars' ) ) {
	function wpzoom_rating_stars( $recipe_ID, $type = 'form', $label = '' ) {
		if ( ! class_exists( 'WPZOOM_Rating_Stars' ) || ! WPZOOM_Settings::get_rating_star_acces() ) {
			return '';
		}

		if ( 'number' === $type ) {
			return WPZOOM_Rating_Stars::get_rating_star( $recipe_ID, $label );
		}

		return WPZOOM_Rating_Stars::get_rating_form( $recipe_ID );
	}
}

add_action( 'init', 'WPZOOM_Recipe_Card_Shortcode::instance' );
add_action( 'init', 'WPZOOM_Recipe_Rating_Shortcode::instance' );

/**
 * Check if the Elementor Page Builder is enabled load the widget
 */
if ( defined( 'ELEMENTOR_VERSION' ) && is_callable( 'Elementor\Plugin::instance' ) ) {
	require_once 'elementor/wpzoom-elementor-recipe-card.php';
}

/**
 * Add Tasty Links plugin support
 */
if( ! function_exists( 'wpzoom_rcb_tasty_links_block' ) ) {
	function wpzoom_rcb_tasty_links_block( $blocks ) {
		$blocks[] = 'wpzoom-recipe-card/block-recipe-card';
		return $blocks;
	}
}
add_filter( 'tasty_links_enabled_rendered_blocks', 'wpzoom_rcb_tasty_links_block' );
