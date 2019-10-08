<?php
/**
 * Plugin Name: Recipe Card Blocks by WPZOOM
 * Plugin URI: https://www.wpzoom.com/plugins/recipe-card-blocks/
 * Description: Beautiful Recipe Card Blocks for Food Bloggers with Schema Markup for the new WordPress editor (Gutenberg).
 * Author: WPZOOM
 * Author URI: https://www.wpzoom.com/
 * Version: 2.3.0
 * Copyright: (c) 2019 WPZOOM
 * License: GPL2+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: wpzoom-recipe-card
 * Domain Path: /languages
 *
 * @package   WPZOOM_Recipe_Card_Blocks
 * @author    Vicolas Petru
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
			if ( ! isset( $_GET['activate-multi'] ) ) {
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
if ( ! function_exists('wpzoom_rcb_block_is_registered') ) {
	function wpzoom_rcb_block_is_registered( $name ) {
		$WP_Block_Type_Registry = new WP_Block_Type_Registry();
		return $WP_Block_Type_Registry->is_registered( $name );
	}
}