<?php
/**
 * Jump To Recipe Block
 *
 * @since   1.2.0
 * @package WPZOOM Jump To Recipe Block
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main WPZOOM_Jump_To_Recipe_Block Class.
 */
class WPZOOM_Jump_To_Recipe_Block {
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
	 * Registers the jump-to-recipe block as a server-side rendered block.
	 *
	 * @return void
	 */
	public function register_hooks() {
		if ( ! function_exists( 'register_block_type' ) ) {
			return;
		}

		if ( wpzoom_rcb_block_is_registered( 'wpzoom-recipe-card/block-jump-to-recipe' ) ) {
			return;
		}

		$attributes = array(
			'id'   => array(
				'type'    => 'string',
				'default' => 'wpzoom-recipe-card',
			),
			'text' => array(
				'type'    => 'string',
				'default' => WPZOOM_Settings::get( 'wpzoom_rcb_settings_jump_to_recipe_text' ),
			),
		);

		// Hook server side rendering into render callback
		register_block_type(
			'wpzoom-recipe-card/block-jump-to-recipe',
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
		if ( ! is_array( $attributes ) || ! is_singular() ) {
			return $content;
		}

		$attributes = self::$helpers->omit( $attributes, array() );
		// Import variables into the current symbol table from an array
		extract( $attributes );

		$class     = 'wpzoom-recipe-snippet-button wp-block-wpzoom-recipe-card-block-jump-to-recipe';
		$className = isset( $className ) ? $className : '';

		$blockClassNames = implode( ' ', array( $class, $className ) );

		$block_content = sprintf(
            '<a href="#%s" rel="nofollow" class="%s"><svg width="16" height="16" viewBox="0 0 384 512" xmlns="http://www.w3.org/2000/svg"><path d="M374.6 310.6l-160 160C208.4 476.9 200.2 480 192 480s-16.38-3.125-22.62-9.375l-160-160c-12.5-12.5-12.5-32.75 0-45.25s32.75-12.5 45.25 0L160 370.8V64c0-17.69 14.33-31.1 31.1-31.1S224 46.31 224 64v306.8l105.4-105.4c12.5-12.5 32.75-12.5 45.25 0S387.1 298.1 374.6 310.6z"/></svg>%s</a>',
			esc_attr( $id ),
			esc_attr( $blockClassNames ),
			esc_html( $text )
		);

		return $block_content;
	}
}
