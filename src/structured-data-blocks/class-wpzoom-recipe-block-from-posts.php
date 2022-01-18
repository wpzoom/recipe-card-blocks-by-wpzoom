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

		$recipeContent = get_post_field( 'post_content', intval( $attributes[ 'postId' ] ), 'display' );

		return sprintf( 
			'<div class="wpzoom-custom-recipe-card-post" data-recipe-post="%2$d">%1$s</div>',
			apply_filters( 'the_content', $recipeContent ),
			intval( $attributes[ 'postId' ] )
	 );
		
	}

}