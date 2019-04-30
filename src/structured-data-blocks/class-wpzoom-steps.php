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
	 * Class instance Structured Data Helpers.
	 *
	 * @var WPZOOM_Structured_Data_Helpers
	 * @since 1.2.0
	 */
	private $structured_data_helpers;

	/**
	 * The Constructor.
	 */
	public function __construct() {
		self::$helpers = new WPZOOM_Helpers();
		$this->structured_data_helpers = new WPZOOM_Structured_Data_Helpers();
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
			'id' => array(
			    'type' => 'string',
			),
			'title' => array(
			    'type' => 'string',
			    'selector' => '.directions-title',
			    'default' => WPZOOM_Settings::get('wpzoom_rcb_settings_steps_title'),
			),
			'print_visibility' => array(
			    'type' => 'string',
			    'default' => 'visible'
			),
			'jsonTitle' => array(
			    'type' => 'string',
			),
			'steps' => array(
			    'type' => 'array',
			    // 'default' => self::get_steps_default(),
			    'items' => array(
			    	'type' => 'object'
			    )
			)
		);

		// Hook server side rendering into render callback
		register_block_type(
			'wpzoom-recipe-card/block-directions', array(
				'attributes' => $attributes,
				'render_callback' => array( $this, 'render' ),
		) );
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

		if ( ! isset($attributes['steps']) ) {
			return $content;
		}

		$attributes = self::$helpers->omit( $attributes, array() );
		// Import variables into the current symbol table from an array
		extract( $attributes );

		$class = 'wp-block-wpzoom-recipe-card-block-directions';

		$steps = isset( $steps ) ? $steps : array();
		$steps_content = $this->get_steps_content( $steps );

		$block_content = sprintf(
			'<div id="%1$s" class="%2$s">
				<div class="wpzoom-recipe-card-print-link %3$s">
					<a class="btn-print-link no-print" href="#%1$s" title="%4$s">
						<img class="icon-print-link" src="%5$s" alt="%6$s"/>%6$s
					</a>
				</div>
				<h3 class="directions-title">%7$s</h3>
				%8$s
			</div>',
			esc_attr( $id ),
			esc_attr( $class ),
			esc_attr( $print_visibility ),
			__( 'Print directions...', 'wpzoom-recipe-card' ),
			esc_url( WPZOOM_RCB_PLUGIN_URL . 'src/assets/images/printer.svg' ),
			__( 'Print', 'wpzoom-recipe-card' ),
			esc_html( $title ),
			$steps_content
		);

		return $block_content;
	}

	public static function get_steps_default() {
		return array(
			array(
				'id' 		=> self::$helpers->generateId( "direction-step" ), 
				'text' 		=> array(), 
			),
		    array(
		    	'id' 		=> self::$helpers->generateId( "direction-step" ), 
		    	'text' 		=> array(), 
		    ),
		    array(
		        'id' 		=> self::$helpers->generateId( "direction-step" ), 
		        'text' 		=> array(), 
		    )
		);
	}

	protected function get_steps_content( array $steps ) {
		$direction_items = $this->get_direction_items( $steps );

		$listClassNames = implode( ' ', array( 'directions-list' ) );

		return sprintf(
			'<ul class="%s">%s</ul>',
			$listClassNames,
			$direction_items
		);
	}

	protected function get_direction_items( array $steps ) {
		$output = '';

		foreach ( $steps as $index => $step ) {
			$text = '';
			$isGroup = isset($step['isGroup']) ? $step['isGroup'] : false;

			if ( !$isGroup ) {
				if ( ! empty( $step['text'] ) ) {
					$text = $this->wrap_direction_text( $step['text'] );
				}
				$output .= sprintf(
					'<li class="direction-step">%s</li>',
					$text
				);
			} else {
				if ( ! empty( $step['text'] ) ) {
					$text = sprintf(
						'<strong class="direction-step-group-title">%s</strong>',
						$this->wrap_direction_text( $step['text'] )
					);
				}
				$output .= sprintf(
					'<li class="direction-step direction-step-group">%s</li>',
					$text
				);
			}
		}

		return force_balance_tags( $output );
	}

	protected function wrap_direction_text( $nodes, $type = '' ) {
		if ( ! is_array( $nodes ) ) {
			return $nodes;
		}

		$output = '';
		foreach ( $nodes as $node ) {
			if ( ! is_array( $node ) ) {
				$output .= $node;
			} else {
				$type = isset( $node['type'] ) ? $node['type'] : null;
				$children = isset( $node['props']['children'] ) ? $node['props']['children'] : null;

				$start_tag = $type ? "<$type>" : "";
				$end_tag = $type ? "</$type>" : "";

				if ( 'img' === $type ) {
					$src = isset( $node['props']['src'] ) ? $node['props']['src'] : false;
					if ( $src ) {
						$alt = isset( $node['props']['alt'] ) ? $node['props']['alt'] : '';
						$class = '0' == WPZOOM_Settings::get('wpzoom_rcb_settings_print_show_steps_image') ? 'no-print' : '';
						$class .= ' direction-step-image';
						$img_style = isset($node['props']['style']) ? $node['props']['style'] : '';

						$start_tag = sprintf( '<%s src="%s" alt="%s" class="%s" style="%s"/>', $type, $src, $alt, trim($class), trim($img_style) );
					} else {
						$start_tag = "";
					}
					$end_tag = "";
				}
				elseif ( 'a' === $type ) {
					$rel 		= isset( $node['props']['rel'] ) ? $node['props']['rel'] : '';
					$aria_label = isset( $node['props']['aria-label'] ) ? $node['props']['aria-label'] : '';
					$href 		= isset( $node['props']['href'] ) ? $node['props']['href'] : '#';
					$target 	= isset( $node['props']['target'] ) ? $node['props']['target'] : '_blank';

					$start_tag = sprintf( '<%s rel="%s" aria-label="%s" href="%s" target="%s">', $type, $rel, $aria_label, $href, $target );
				}
				elseif ( 'br' === $type ) {
					$end_tag = "";
				}

				$output .= $start_tag . $this->wrap_direction_text( $children, $type ) . $end_tag;
			}
		}

		return $output;
	}
}