<?php
/**
 * Directions Block
 *
 * @since   1.2.0
 * @package WPZOOM Directions Block
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main WPZOOM_Steps_Block Class.
 */
class WPZOOM_Steps_Block {
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

		if ( wpzoom_rcb_block_is_registered( 'wpzoom-recipe-card/block-directions' ) ) {
			return;
		}

		$attributes = array(
			'id'               => array(
				'type' => 'string',
			),
			'title'            => array(
				'type'     => 'string',
				'selector' => '.directions-title',
				'default'  => WPZOOM_Settings::get( 'wpzoom_rcb_settings_steps_title' ),
			),
			'print_visibility' => array(
				'type'    => 'string',
				'default' => 'visible',
			),
			'jsonTitle'        => array(
				'type' => 'string',
			),
			'steps'            => array(
				'type'  => 'array',
				// 'default' => self::get_steps_default(),
				'items' => array(
					'type' => 'object',
				),
			),
		);

		// Hook server side rendering into render callback
		register_block_type(
			'wpzoom-recipe-card/block-directions',
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

		if ( ! is_array( $attributes ) ) {
			return $content;
		}

		if ( ! isset( $attributes['steps'] ) ) {
			return $content;
		}

		$attributes = self::$helpers->omit( $attributes, array() );
		// Import variables into the current symbol table from an array
		extract( $attributes );

		$class = 'wp-block-wpzoom-recipe-card-block-directions';

		$steps         = isset( $steps ) ? $steps : array();
		$steps_content = self::get_steps_content( $steps );

		$btn_attributes = array(
			'title' => esc_html__( 'Print directions...', 'recipe-card-blocks-by-wpzoom' ),
		);

		if ( $post ) {
			$btn_attributes = array_merge( $btn_attributes, array( 'data-recipe-id' => $post->ID ) );
		}

		$atts = self::$helpers->render_attributes( $btn_attributes );

		$block_content = sprintf(
			'<div id="%1$s" class="%2$s">
				<div class="wpzoom-recipe-card-print-link %3$s">
					<a class="btn-print-link no-print" href="#%1$s" %4$s>
						<img class="icon-print-link" src="%5$s" alt="%6$s"/>%6$s
					</a>
				</div>
				<h3 class="directions-title">%7$s</h3>
				%8$s
			</div>',
			esc_attr( $id ),
			esc_attr( $class ),
			esc_attr( $print_visibility ),
			$atts,
			esc_url( WPZOOM_RCB_PLUGIN_URL . 'dist/assets/images/printer.svg' ),
			esc_html__( 'Print', 'recipe-card-blocks-by-wpzoom' ),
			esc_html( $title ),
			$steps_content
		);

		return $block_content;
	}

	public static function get_steps_default() {
		return array(
			array(
				'id'   => self::$helpers->generateId( 'direction-step' ),
				'text' => array(),
			),
			array(
				'id'   => self::$helpers->generateId( 'direction-step' ),
				'text' => array(),
			),
			array(
				'id'   => self::$helpers->generateId( 'direction-step' ),
				'text' => array(),
			),
		);
	}

	public static function get_steps_content( array $steps ) {
		$direction_items = self::get_direction_items( $steps );

		$listClassNames = implode( ' ', array( 'directions-list' ) );

		return sprintf(
			'<ul class="%s">%s</ul>',
			$listClassNames,
			$direction_items
		);
	}

	public static function get_direction_items( array $steps ) {
		$output = '';

		foreach ( $steps as $index => $step ) {
			$text    = '';
			$isGroup = isset( $step['isGroup'] ) ? $step['isGroup'] : false;

			if ( ! $isGroup ) {
				if ( ! empty( $step['text'] ) ) {
					$text    = WPZOOM_Recipe_Card_Block::wrap_direction_text( $step['text'] );
					$output .= sprintf(
						'<li class="direction-step">%s</li>',
						$text
					);
				}
			} else {
				if ( ! empty( $step['text'] ) ) {
					$text    = sprintf(
						'<strong class="direction-step-group-title">%s</strong>',
						WPZOOM_Recipe_Card_Block::wrap_direction_text( $step['text'] )
					);
					$output .= sprintf(
						'<li class="direction-step direction-step-group">%s</li>',
						$text
					);
				}
			}
		}

		return force_balance_tags( $output );
	}
}
