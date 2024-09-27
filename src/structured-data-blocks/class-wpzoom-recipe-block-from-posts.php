<?php
/**
 * Recipe Block from Posts
 *
 * @since   1.2.0
 * @package WPZOOM Recipe Block from posts
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main WPZOOM_Recipe_Block_From_Posts Class.
 */
class WPZOOM_Recipe_Block_From_Posts {
	/**
	 * Class instance Helpers.
	 *
	 * @var WPZOOM_Helpers
	 * @since 1.2.0
	 */
	private static $helpers;

	/**
	 * The Constructor.
	 */
	function __construct() {
		self::$helpers = new WPZOOM_Helpers();
	}

	/**
	 * Registers the recipe-block-from-posts block as a server-side rendered block.
	 *
	 * @return void
	 */
	public function register_hooks() {
		if ( ! function_exists( 'register_block_type' ) ) {
			return;
		}

		if ( wpzoom_rcb_block_is_registered( 'wpzoom-recipe-card/recipe-block-from-posts' ) ) {
			return;
		}

		$attributes = array(
			'postId' => array(
				'type'    => 'string',
				'default' => '-1'
			)
		);

		// Hook server side rendering into render callback
		register_block_type(
			'wpzoom-recipe-card/recipe-block-from-posts',
			array(
				'attributes'      => $attributes,
				'render_callback' => array( $this, 'render' ),
			)
		);

	}

	/**
	 * Renders the block.
	 *
	 * @param array  $attributes The attributes of the block.
	 * @param string $content    The HTML content of the block.
	 *
	 * @return string The block preceded by its script.
	 */
	public function render( $attributes, $content ) {

		$post_id = isset( $attributes['postId'] ) ? $attributes['postId'] : null;

		if( ! $post_id ) {
			return '';	
		}

		$parentRecipe_ID = get_post_meta( $post_id, '_wpzoom_rcb_parent_post_id', true );
		if( ! empty( $parentRecipe_ID ) && $parentRecipe_ID != get_the_ID() ) {
			$i = $parentRecipe_ID;
		}
		else {
			$i = $post_id;
		}

		$recipe = get_post( intval( $post_id ) );
		$recipe_content = $recipe->post_content;

		$blocks = parse_blocks( $recipe_content );

		$recipe_output = '';

		foreach( $blocks as $block ) {
			$recipe_output .= render_block( $block );
		}

		return sprintf( 
			'<div class="wpzoom-custom-recipe-card-post" data-parent-id="%3$d" data-recipe-post="%2$d">%1$s</div>',
			$recipe_output,
			intval( $post_id ),
			intval( $i )
	 );
		
	}

}