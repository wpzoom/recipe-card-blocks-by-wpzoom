<?php
/**
 * Print Recipe Block
 *
 * @since   1.2.0
 * @package WPZOOM Print Recipe Block
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main WPZOOM_Print_Recipe_Block Class.
 */
class WPZOOM_Print_Recipe_Block {
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
	 * Registers the print-recipe block as a server-side rendered block.
	 *
	 * @return void
	 */
	public function register_hooks() {
		if ( ! function_exists( 'register_block_type' ) ) {
			return;
		}

		if ( wpzoom_rcb_block_is_registered( 'wpzoom-recipe-card/block-print-recipe' ) ) {
			return;
		}

		$attributes = array(
			'id'   => array(
				'type'    => 'string',
				'default' => 'wpzoom-recipe-card',
			),
			'text' => array(
				'type'    => 'string',
				'default' => WPZOOM_Settings::get( 'wpzoom_rcb_settings_print_recipe_text' ),
			),
		);

		// Hook server side rendering into render callback
		register_block_type(
			'wpzoom-recipe-card/block-print-recipe',
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
	 * @return string The block preceded by its JSON-LD script.
	 */
	public function render( $attributes, $content ) {
		global $post;

		if ( ! is_array( $attributes ) || ! is_singular() ) {
			return $content;
		}

		$recipe_ID  = $post ? $post->ID : 0;
		$attributes = self::$helpers->omit( $attributes, array() );
		// Import variables into the current symbol table from an array
		extract( $attributes );

		$class = 'wpzoom-recipe-snippet-button wp-block-wpzoom-recipe-card-block-print-recipe';

		$block_content = sprintf(
			'<a href="#%s" rel="nofollow" class="%s" data-recipe-id="%s">%s</a>',
			esc_attr( $id ),
			esc_attr( $class ),
			$recipe_ID,
			esc_html( $text )
		);

		return $block_content;
	}
}
