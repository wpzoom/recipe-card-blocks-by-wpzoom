<?php
/**
 * Rating Block
 *
 * @since   1.2.0
 * @package WPZOOM Rating Block
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main WPZOOM_Rating_Block Class.
 */
class WPZOOM_Rating_Block {
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
	public function __construct() {
		self::$helpers = new WPZOOM_Helpers();
	}

	/**
	 * Registers the rating recipe block as a server-side rendered block.
	 *
	 * @return void
	 */
	public function register_hooks() {

		if ( ! function_exists( 'register_block_type' ) ) {
			return;
		}

		if ( wpzoom_rcb_block_is_registered( 'wpzoom-recipe-card/block-rating' ) ) {
			return;
		}

		$attributes = array(
			'recipeId'            => array(
				'type' => 'string',
			),
			'label' => array(
				'type'    => 'string',
				'default' => ''
			),
			'align' => array(
				'type'    => 'string',
				'default' => 'none'
			)
		);

		// Hook server side rendering into render callback
		register_block_type(
			'wpzoom-recipe-card/block-rating',
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
	 * @return string The rating block.
	 */
	public function render( $attributes, $content, $block ) {

		global $post;

		$recipe_ID = '';
		$blocks = array();
		$align = isset( $attributes['align'] ) && ! empty( $attributes['align'] ) ? $attributes['align'] : 'none';
		$align_class = ( 'none' !== $align ? ' align' . $align : '' );

		
		
		if ( ! $post instanceof WP_Post ) {
			return '';
		}

		$blocks = parse_blocks( $post->post_content );
				
		if( ! empty( $blocks ) && is_array( $blocks ) ) {
			foreach ( $blocks as $key => $block ) {
				if ( 'wpzoom-recipe-card/block-recipe-card' === $block['blockName'] ) {

					$rating_html = sprintf(
						'<div class="wpzoom-recipe-card-rating-block %1$s">%2$s</div>',
						esc_attr( $align_class ),
						WPZOOM_Rating_Stars::get_rating_form( $post->ID )
					);
					return $rating_html;
				}
				elseif( 'wpzoom-recipe-card/recipe-block-from-posts' === $block['blockName'] ) {
					if( isset( $block['attrs']['postId'] ) ) {
						$recipe_ID = $block['attrs']['postId'];
					}
				}
			}
		}

		if( ! $recipe_ID ) {
			$recipe_ID = isset( $attributes['recipeId'] ) ? $attributes['recipeId'] : null;
		}

		$parentRecipe_ID = get_post_meta( $recipe_ID, '_wpzoom_rcb_parent_post_id', true );
		if( !empty( $parentRecipe_ID ) ) {
			$i = $parentRecipe_ID;
		}
		else {
			$i = $recipe_ID;
		}

		$rating_html = sprintf(
			'<div class="wpzoom-recipe-card-rating-block %1$s">%2$s</div>',
			esc_attr( $align_class ),
			WPZOOM_Rating_Stars::get_rating_form( $i )
		);
		
		return $rating_html;

	}
	
}