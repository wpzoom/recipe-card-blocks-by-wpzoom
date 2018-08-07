<?php
/**
 * Plugin Name: WPZOOM Recipe Card
 * Plugin URI: https://wpzoom.com
 * Description: A beautiful recipe blocks for Gutenberg to help you add recipe cards.
 * Author: WPZOOM
 * Author URI: http://wpzoom.com
 * Version: 1.0.0
 * Copyright: (c) 2018 WPZOOM
 * License: GPL2+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: wpzoom-recipe-card
 *
 * @package WPZOOM RECIPE CARD
 */


/**
 * Exit if accessed directly
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Initialize the blocks
 */
function wpzoom_recipe_card_blocks_loader() {
	/**
	 * Load the blocks functionality
	 */
	require_once plugin_dir_path( __FILE__ ) . 'dist/init.php';

	/**
	 * Load Social Block PHP
	 */
	require_once plugin_dir_path( __FILE__ ) . 'dist/blocks/block-ingredients/index.php';

	/**
	 * Load Post Grid PHP
	 */
	require_once plugin_dir_path( __FILE__ ) . 'dist/blocks/block-directions/index.php';
}

add_action( 'plugins_loaded', 'wpzoom_recipe_card_blocks_loader' );


/**
 * Load the plugin textdomain
 */
function wpzoom_recipe_card_blocks_init() {
	load_plugin_textdomain(
		'wpzoom-recipe-card',
		false,
		basename( dirname( __FILE__ ) ) . '/languages'
	);
}
add_action( 'init', 'wpzoom_recipe_card_blocks_init' );


/**
 * Add a check for our plugin before redirecting
 */
function wpzoom_recipe_card_blocks_activate() {
    add_option( 'wpzoom_recipe_card_blocks_do_activation_redirect', true );
}
register_activation_hook( __FILE__, 'wpzoom_recipe_card_blocks_activate' );


/**
 * Redirect to the Atomic Blocks Getting Started page on single plugin activation
 */
function wpzoom_recipe_card_blocks_redirect() {
    if ( get_option( 'wpzoom_recipe_card_blocks_do_activation_redirect', false ) ) {
        delete_option( 'wpzoom_recipe_card_blocks_do_activation_redirect' );
        if( !isset( $_GET['activate-multi'] ) ) {
            wp_redirect( "admin.php?page=wpzoom-license" );
        }
    }
}
// add_action( 'admin_init', 'wpzoom_recipe_card_blocks_redirect' );
