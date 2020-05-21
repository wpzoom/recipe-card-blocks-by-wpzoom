<?php
/**
 * Details Block
 *
 * @since   1.2.0
 * @package WPZOOM Details Block
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main WPZOOM_Details_Block Class.
 */
class WPZOOM_Details_Block {
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

		if ( wpzoom_rcb_block_is_registered( 'wpzoom-recipe-card/block-details' ) ) {
			return;
		}

		$attributes = array(
			'id' => array(
			    'type' => 'string',
			),
			'title' => array(
			    'type' => 'string',
			    'selector' => '.details-title',
			    'default' => WPZOOM_Settings::get('wpzoom_rcb_settings_details_title'),
			),
			'jsonTitle' => array(
			    'type' => 'string',
			),
			'course' => array(
			    'type' => 'array',
			    'items' => array(
			    	'type' => 'string'
			    )
			),
			'cuisine' => array(
			    'type' => 'array',
			    'items' => array(
			    	'type' => 'string'
			    )
			),
			'difficulty' => array(
			    'type' => 'array',
			    'items' => array(
			    	'type' => 'string'
			    )
			),
			'keywords' => array(
			    'type' => 'array',
			    'items' => array(
			    	'type' => 'string'
			    )
			),
			'details' => array(
			    'type' => 'array',
			    // 'default' => self::get_details_default(),
			    'items' => array(
			    	'type' => 'object'
			    )
			),
			'columns' => array(
			    'type' => 'integer',
			    'default' => 4
			),
			'toInsert' => array(
			    'type' => 'integer',
			),
			'showModal' => array(
			    'type' => 'boolean',
			    'default' => false
			),
			'icons' => array(
		        'type' => 'object',
		    ),
			'activeIconSet' => array(
			    'type' => 'string',
			    'default' => 'foodicons'
			),
			'searchIcon' => array(
			    'type' => 'string',
			    'default' => ''
			),
		);

		// Hook server side rendering into render callback
		register_block_type(
			'wpzoom-recipe-card/block-details', array(
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
		if ( ! is_array( $attributes ) ) {
			return $content;
		}

		if ( ! isset($attributes['details']) ) {
			return $content;
		}

		$attributes = self::$helpers->omit( $attributes, array( 'toInsert', 'activeIconSet', 'showModal', 'searchIcon', 'icons' ) );
		// Import variables into the current symbol table from an array
		extract( $attributes );

		$class = 'wp-block-wpzoom-recipe-card-block-details';
		$class .= ' col-' . $columns;

		$className = isset( $className ) ? $className : '';
		$details = isset( $details ) ? $details : array();
		$details_content = $this->get_details_content( $details );

		$blockClassNames = implode( ' ', array( $class, $className ) );

		$block_content = sprintf(
			'<div id="%1$s" class="%2$s">
				<h3 class="details-title">%3$s</h3>
				%4$s
			</div>',
			esc_attr( $id ),
			esc_attr( $blockClassNames ),
			esc_html( $title ),
			$details_content
		);

		return $block_content;
	}

	public static function get_details_default() {
		return array(
			array(
				'id' 		=> self::$helpers->generateId( "detail-item" ), 
				'iconSet' 	=> 'oldicon', 
				'icon' 		=> 'food', 
				'label' 	=> __( "Servings", "wpzoom-recipe-card" ), 
				'unit' 		=> __( "servings", "wpzoom-recipe-card" ) 
			),
		    array(
		    	'id' 		=> self::$helpers->generateId( "detail-item" ), 
		    	'iconSet' 	=> 'oldicon', 
		    	'icon' 		=> 'room-service', 
		    	'label' 	=> __( "Prep time", "wpzoom-recipe-card" ), 
		    	'unit' 		=> __( "minutes", "wpzoom-recipe-card" ) 
		    ),
		    array(
		        'id' 		=> self::$helpers->generateId( "detail-item" ), 
		        'iconSet' 	=> 'oldicon', 
		        'icon' 		=> 'cook',
		        'label' 	=> __( "Cooking time", "wpzoom-recipe-card" ), 
		        'unit' 		=> __( "minutes", "wpzoom-recipe-card" ) 
		    ),
		    array(
		        'id' 		=> self::$helpers->generateId( "detail-item" ), 
		        'iconSet' 	=> 'oldicon', 
		        'icon' 		=> 'shopping-basket',
		        'label' 	=> __( "Calories", "wpzoom-recipe-card" ), 
		        'unit' 		=> __( "kcal", "wpzoom-recipe-card" )
		    )
		);
	}

	protected function get_details_content( array $details ) {
		$detail_items = $this->get_detail_items( $details );

		return sprintf(
			'<div class="details-items">%s</div>',
			$detail_items
		);
	}

	protected function get_detail_items( array $details ) {
		$output = '';
		$defaults = self::get_details_default();

		foreach ( $details as $index => $detail ) {
			$icon = $label = $value = $unit = '';

			if ( ! empty( $detail[ 'icon' ] ) ) {
				$icon 	 			= $detail['icon'];
 				$iconSet 			= isset( $detail['iconSet'] ) ? $detail['iconSet'] : 'oldicon';
 				$_prefix 			= isset( $detail['_prefix'] ) && ! empty( $detail['_prefix'] ) ? $detail['_prefix'] : $iconSet;
 				$itemIconClasses 	= implode( ' ', array( 'detail-item-icon', $_prefix, $iconSet . '-' . $detail['icon'] ) );

				$icon = sprintf(
					'<span class="%s" icon-name="%s" iconset="%s"></span>',
					$itemIconClasses,
					$icon,
					$iconSet
				);
			}

			if ( ! empty( $detail[ 'label' ] ) ) {
				if ( !is_array( $detail['label'] ) ) {
					$label = sprintf(
						'<span class="detail-item-label">%s</span>',
						$detail['label']
					);
				} elseif( isset( $detail['jsonLabel'] ) ) {
					$label = sprintf(
						'<span class="detail-item-label">%s</span>',
						$detail['jsonLabel']
					);
				}
			}

			if ( ! empty( $detail[ 'value' ] ) ) {
				if ( !is_array( $detail['value'] ) ) {
					$value = sprintf(
						'<p class="detail-item-value">%s</p>',
						$detail['value']
					);
				} elseif ( isset( $detail['jsonValue'] ) ) {
					$value = sprintf(
						'<p class="detail-item-value">%s</p>',
						$detail['jsonValue']
					);
				}
			}
			if ( ! empty( $detail[ 'unit' ] ) ) {
				$unit = sprintf(
					'<span class="detail-item-unit">%s</span>',
					$detail['unit']
				);
			}

			$output .= sprintf(
				'<div class="%1$s %1$s-%2$s">%3$s</div>',
				'detail-item',
				$index,
				$icon . $label . $value . $unit
			);
		}

		return force_balance_tags( $output );
	}
}