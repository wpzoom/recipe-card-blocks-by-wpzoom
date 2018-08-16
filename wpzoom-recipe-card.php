<?php
/**
 * Plugin Name: Recipe Card Blocks by WPZOOM
 * Plugin URI: https://www.wpzoom.com
 * Description: Beautiful recipe blocks for Gutenberg to help you to add recipe cards: Ingredients, Directions and more to come.
 * Author: WPZOOM
 * Author URI: https://wpzoom.com
 * Version: 1.0.0
 * Copyright: (c) 2018 WPZOOM
 * License: GPL2+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: wpzoom-recipe-card
 *
 * @package recipe-card-blocks-by-wpzoom
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Add a redirection check on activation.
 *
 * @since 1.0.0
 */
function wpzoom_rcb_activate() {
	add_option( 'wpzoom_rcb_do_activation_redirect', true );
}
register_activation_hook( __FILE__, 'wpzoom_rcb_activate' );


/**
 * Redirect to the WPZOOM Recipe Card Getting Started page on single plugin activation
 * TODO: make redirect works
 *
 * @since 1.0.0
 */
function wpzoom_rcb_redirect() {
	if ( get_option( 'wpzoom_rcb_do_activation_redirect', false ) ) {
		delete_option( 'wpzoom_rcb_do_activation_redirect' );
		if ( ! isset( $_GET['activate-multi'] ) ) {
			wp_redirect( 'admin.php?page=wpzoom-recipe-card' );
		}
	}
}
// add_action( 'admin_init', 'wpzoom_rcb_redirect' );


/**
 * Load the plugin textdomain
 *
 * @since 1.0.0
 */
function wpzoom_rcb_blocks_init() {
	load_plugin_textdomain(
		'wpzoom-recipe-card',
		false,
		basename( dirname( __FILE__ ) ) . '/languages'
	);
}
add_action( 'init', 'wpzoom_rcb_blocks_init' );


/**
 * Add custom block category
 *
 * @since 1.0.0
 */
function wpzoom_rcb_custom_category( $categories, $post ) {
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
add_filter( 'block_categories', 'wpzoom_rcb_custom_category', 10, 2 );


/**
 * Block Initializer.
 */
require_once plugin_dir_path( __FILE__ ) . 'src/init.php';
