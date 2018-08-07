<?php
/**
 * Blocks Initializer
 *
 * Enqueue CSS/JS of all the blocks.
 *
 * @since 	1.0.0
 * @package WPZOOM Recipe Card
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enqueue assets for frontend and backend
 *
 * @since 1.0.0
 */
function wpzoom_recipe_card_blocks_assets() {
	
	// Enqueue block JS
    wp_enqueue_script(
        'wpzoo-recipe-card-ingredients-script',
        plugins_url( 'blocks/block-ingredients/script.js', __FILE__ ),
        array('jquery'),
        filemtime( plugin_dir_path( __FILE__ ) . "blocks/block-ingredients/script.js" )
    );
}

add_action( 'enqueue_block_assets', 'wpzoom_recipe_card_blocks_assets' );


/**
 * Add custom block category
 *
 * @since 1.0.0
 */
function wpzoom_recipe_card_custom_category( $categories, $post ) {
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
add_filter( 'block_categories', 'wpzoom_recipe_card_custom_category', 10, 2 );