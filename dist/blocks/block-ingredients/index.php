<?php
/**
 * Server-side rendering for the ingredients block
 *
 * @since   1.0.0
 * @package WPZOOM Recipe Card
 */

/**
 * Registers the `wpzoom-recipe-card/block-ingredients` block on server.
 */
function wpzoom_recipe_card_gutenberg_boilerplate_block_ingredients() {
    // check if register function exists
    if ( ! function_exists('register_block_type') ) {
    	return;
    }

    wp_register_script(
        "wpzoom-recipe-card-block-ingredients",
        plugins_url( 'block.js', __FILE__ ),
        array( 'wp-blocks', 'wp-element' )
    );
    // wp_register_style(
    //     "wpzoom-recipe-card-block-ingredients-editor",
    //     plugins_url( 'editor.css', __FILE__ ),
    //     array( 'wp-edit-blocks' ),
    //     filemtime( plugin_dir_path( __FILE__ ) . "editor.css" )
    // );

    wp_register_style(
        'wpzoom-recipe-card-block-ingredients-style',
        plugins_url( 'style.css', __FILE__ ),
        array(),
        filemtime( plugin_dir_path( __FILE__ ) . "style.css" )
    );

    register_block_type( "wpzoom-recipe-card/block-ingredients", array(
        'editor_script' => "wpzoom-recipe-card-block-ingredients", // Editor script
        'style'         => "wpzoom-recipe-card-block-ingredients-style", // Styles
    ) );
}

add_action( 'init', 'wpzoom_recipe_card_gutenberg_boilerplate_block_ingredients' );

?>