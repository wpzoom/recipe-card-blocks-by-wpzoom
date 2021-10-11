<?php
/**
 * Nutrition Block
 *
 * @since   2.3.2
 * @package WPZOOM_Recipe_Card_Block
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main WPZOOM_Nutrition_Block Class.
 */
class WPZOOM_Nutrition_Block {
	/**
	 * The post Object.
	 *
	 * @since 2.3.2
	 */
	private $recipe;

	/**
	 * Class instance Structured Data Helpers.
	 *
	 * @var WPZOOM_Structured_Data_Helpers
	 * @since 2.3.2
	 */
	private static $structured_data_helpers;

	/**
	 * Class instance Helpers.
	 *
	 * @var WPZOOM_Helpers
	 * @since 2.3.2
	 */
	private static $helpers;

	/**
	 * Block attributes.
	 *
	 * @since 2.3.2
	 */
	public static $attributes;

	/**
	 * Block data.
	 *
	 * @since 2.3.2
	 */
	public static $data;

	/**
	 * Block settings.
	 *
	 * @since 2.3.2
	 */
	public static $settings;

	/**
	 * Nutrition facts labels
	 *
	 * @since 2.3.2
	 */
	public static $labels;

	/**
	 * The Constructor.
	 */
	public function __construct() {
		self::$structured_data_helpers = new WPZOOM_Structured_Data_Helpers();
		self::$helpers                 = new WPZOOM_Helpers();

		self::set_labels();
	}

	/**
	 * Registers the nutrition block as a server-side rendered block.
	 *
	 * @return void
	 */
	public function register_hooks() {
		if ( ! function_exists( 'register_block_type' ) ) {
			return;
		}

		if ( wpzoom_rcb_block_is_registered( 'wpzoom-recipe-card/block-nutrition' ) ) {
			return;
		}

		$attributes = array(
			'id'       => array(
				'type'    => 'string',
				'default' => 'wpzoom-recipe-nutrition',
			),
			'data'     => array(
				'type' => 'object',
			),
			'settings' => array(
				'type'    => 'object',
				'default' => array(
					'layout-orientation' => WPZOOM_Settings::get( 'wpzoom_rcb_settings_nutrition_layout' ),
				),
			),
		);

		// Hook server side rendering into render callback
		register_block_type(
			'wpzoom-recipe-card/block-nutrition',
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
		if ( ! is_array( $attributes ) ) {
			return $content;
		}

		// Import variables into the current symbol table from an array
		extract( $attributes );

		self::$data       = $data;
		self::$settings   = $settings;
		self::$attributes = $attributes;

		$class     = 'wp-block-wpzoom-recipe-card-block-nutrition';
		$className = isset( $className ) ? $className : '';

		$layout_orientation = isset( $settings['layout-orientation'] ) ? $settings['layout-orientation'] : 'vertical';
		$daily_value_text   = esc_html__( 'The % Daily Value tells you how much a nutrient in a serving of food contributes to a daily diet. 2,000 calories a day is used for general nutrition advice.', 'recipe-card-blocks-by-wpzoom' );

		$blockClassNames = implode( ' ', array( $class, $className ) );

		$block_content = sprintf(
			'<div id="%s" class="layout-orientation-%s">
				<div class="%s">%s<p class="nutrition-facts-daily-value-text">* %s</p></div>
			</div>',
			esc_attr( $id ),
			esc_attr( $layout_orientation ),
			esc_attr( $blockClassNames ),
			self::get_nutrition_facts(),
			$daily_value_text
		);

		return $block_content;
	}

	public static function get_nutrition_facts() {
		if ( 'vertical' === self::$settings['layout-orientation'] ) {
			return self::get_vertical_layout();
		} else {
			return self::get_horizontal_layout();
		}
	}

	public static function get_nutrients_list() {
		$output = '';

		foreach ( self::$labels as $key => $label ) {
			$value = isset( self::$data[ $label['id'] ] ) && ! empty( self::$data[ $label['id'] ] ) ? self::$data[ $label['id'] ] : false;

			if ( $key <= 12 || ! $value ) {
				continue;
			}

			$output .= '<li><strong>' . esc_html( $label['label'] ) . ' <span class="nutrition-facts-right"><span class="nutrition-facts-percent nutrition-facts-label">' . floatval( $value ) . '</span>%</span></strong></li>';
		}

		return $output;
	}

	public static function get_vertical_layout() {
		$output = '';

		$measurements = array(
			'g'  => esc_html__( 'g', 'recipe-card-blocks-by-wpzoom' ),
			'mg' => esc_html__( 'mg', 'recipe-card-blocks-by-wpzoom' ),
		);

		$output .= '<h2>' . esc_html__( 'Nutrition Facts', 'recipe-card-blocks-by-wpzoom' ) . '</h2>';

		if ( isset( self::$data['servings'] ) && ! empty( self::$data['servings'] ) ) {
			$output .= '<p><span class="nutrition-facts-serving">' . sprintf( esc_html__( '%s servings per container', 'recipe-card-blocks-by-wpzoom' ), floatval( self::$data['servings'] ) ) . '</span></p>';
		}
		if ( isset( self::$data['serving-size'] ) && ! empty( self::$data['serving-size'] ) ) {
			$output .= '<p><strong class="nutrition-facts-serving-size">' . self::get_label_title( 'serving-size' ) . '</strong><strong class="nutrition-facts-label nutrition-facts-right">' . floatval( self::$data['serving-size'] ) . $measurements['g'] . '</strong></p>';
		}

		$output .= '<hr class="nutrition-facts-hr"/>';
		$output .= '<ul>';

		if ( isset( self::$data['calories'] ) && ! empty( self::$data['calories'] ) ) {
			$output .= '<li>';
			$output .= '<strong class="nutrition-facts-amount-per-serving">' . esc_html__( 'Amount Per Serving', 'recipe-card-blocks-by-wpzoom' ) . '</strong>';
			$output .= '<strong class="nutrition-facts-calories">' . self::get_label_title( 'calories' ) . '</strong><strong class="nutrition-facts-label nutrition-facts-right">' . floatval( self::$data['calories'] ) . '</strong>';
			$output .= '</li>';
		}

		$output .= '<li class="nutrition-facts-spacer"></li>';
		$output .= '<li class="nutrition-facts-no-border"><strong class="nutrition-facts-right">% ' . esc_html__( 'Daily Value', 'recipe-card-blocks-by-wpzoom' ) . ' *</strong></li>';

		if ( isset( self::$data['total-fat'] ) && ! empty( self::$data['total-fat'] ) ) {
			$output .= '<li>';
			$output .= '<strong class="nutrition-facts-heading">' . self::get_label_title( 'total-fat' ) . '</strong>
		                <strong class="nutrition-facts-label">' . floatval( self::$data['total-fat'] ) . '</strong><strong class="nutrition-facts-label">' . $measurements['g'] . '</strong>
		                <strong class="nutrition-facts-right"><span class="nutrition-facts-percent">' . ceil( ( floatval( self::$data['total-fat'] ) / self::get_label_pdv( 'total-fat' ) ) * 100 ) . '</span>%</strong>';

			$output .= '<ul>';

			if ( isset( self::$data['saturated-fat'] ) && ! empty( self::$data['saturated-fat'] ) ) {
				$output .= '<li>';
				$output .= '<strong class="nutrition-facts-label">' . self::get_label_title( 'saturated-fat' ) . '</strong>
		                        <strong class="nutrition-facts-label">' . floatval( self::$data['saturated-fat'] ) . '</strong><strong class="nutrition-facts-label">' . $measurements['g'] . '</strong>
		                        <strong class="nutrition-facts-right"><span class="nutrition-facts-percent">' . ceil( ( floatval( self::$data['saturated-fat'] ) / self::get_label_pdv( 'saturated-fat' ) ) * 100 ) . '</span>%</strong>';
				$output .= '</li>';
			}
			if ( isset( self::$data['trans-fat'] ) && ! empty( self::$data['trans-fat'] ) ) {
				$output .= '<li>';
				$output .= '<strong class="nutrition-facts-label">' . self::get_label_title( 'trans-fat' ) . '</strong>
		                        <strong class="nutrition-facts-label">' . floatval( self::$data['trans-fat'] ) . '</strong><strong class="nutrition-facts-label">' . $measurements['g'] . '</strong>';
				$output .= '</li>';
			}

			$output .= '</ul></li>';
		}

		if ( isset( self::$data['cholesterol'] ) && ! empty( self::$data['cholesterol'] ) ) {
			$output .= '<li>';
			$output .= '<strong class="nutrition-facts-heading">' . self::get_label_title( 'cholesterol' ) . '</strong>
		                <strong class="nutrition-facts-label">' . floatval( self::$data['cholesterol'] ) . '</strong><strong class="nutrition-facts-label">' . $measurements['mg'] . '</strong>
		                <strong class="nutrition-facts-right"><span class="nutrition-facts-percent">' . ceil( ( floatval( self::$data['cholesterol'] ) / self::get_label_pdv( 'cholesterol' ) ) * 100 ) . '</span>%</strong>';
			$output .= '</li>';
		}
		if ( isset( self::$data['sodium'] ) && ! empty( self::$data['sodium'] ) ) {
			$output .= '<li>';
			$output .= '<strong class="nutrition-facts-heading">' . self::get_label_title( 'sodium' ) . '</strong>
		                <strong class="nutrition-facts-label">' . floatval( self::$data['sodium'] ) . '</strong><strong class="nutrition-facts-label">' . $measurements['mg'] . '</strong>
		                <strong class="nutrition-facts-right"><span class="nutrition-facts-percent">' . ceil( ( floatval( self::$data['sodium'] ) / self::get_label_pdv( 'sodium' ) ) * 100 ) . '</span>%</strong>';
			$output .= '</li>';
		}
		if ( isset( self::$data['potassium'] ) && ! empty( self::$data['potassium'] ) ) {
			$output .= '<li>';
			$output .= '<strong class="nutrition-facts-heading">' . self::get_label_title( 'potassium' ) . '</strong>
		                <strong class="nutrition-facts-label">' . floatval( self::$data['potassium'] ) . '</strong><strong class="nutrition-facts-label">' . $measurements['mg'] . '</strong>
		                <strong class="nutrition-facts-right"><span class="nutrition-facts-percent">' . ceil( ( floatval( self::$data['potassium'] ) / self::get_label_pdv( 'potassium' ) ) * 100 ) . '</span>%</strong>';
			$output .= '</li>';
		}

		if ( isset( self::$data['total-carbohydrate'] ) && ! empty( self::$data['total-carbohydrate'] ) ) {
			$output .= '<li>';
			$output .= '<strong class="nutrition-facts-heading">' . self::get_label_title( 'total-carbohydrate' ) . '</strong>
		                <strong class="nutrition-facts-label">' . floatval( self::$data['total-carbohydrate'] ) . '</strong><strong class="nutrition-facts-label">' . $measurements['g'] . '</strong>
		                <strong class="nutrition-facts-right"><span class="nutrition-facts-percent">' . ceil( ( floatval( self::$data['total-carbohydrate'] ) / self::get_label_pdv( 'total-carbohydrate' ) ) * 100 ) . '</span>%</strong>';

			$output .= '<ul>';

			if ( isset( self::$data['dietary-fiber'] ) && ! empty( self::$data['dietary-fiber'] ) ) {
				$output .= '<li>';
				$output .= '<strong class="nutrition-facts-label">' . self::get_label_title( 'dietary-fiber' ) . '</strong>
		                    <strong class="nutrition-facts-label">' . floatval( self::$data['dietary-fiber'] ) . '</strong><strong class="nutrition-facts-label">' . $measurements['g'] . '</strong>
		                    <strong class="nutrition-facts-right"><span class="nutrition-facts-percent">' . ceil( ( floatval( self::$data['dietary-fiber'] ) / self::get_label_pdv( 'dietary-fiber' ) ) * 100 ) . '</span>%</strong>';
				$output .= '</li>';
			}
			if ( isset( self::$data['sugars'] ) && ! empty( self::$data['sugars'] ) ) {
				$output .= '<li>';
				$output .= '<strong class="nutrition-facts-label">' . self::get_label_title( 'sugars' ) . '</strong>
		                    <strong class="nutrition-facts-label">' . floatval( self::$data['sugars'] ) . '</strong><strong class="nutrition-facts-label">' . $measurements['g'] . '</strong>';
				$output .= '</li>';
			}

			$output .= '</ul></li>';
		}

		if ( isset( self::$data['protein'] ) && ! empty( self::$data['protein'] ) ) {
			$output .= '<li>';
			$output .= '<strong class="nutrition-facts-heading">' . self::get_label_title( 'protein' ) . '</strong>
		                <strong class="nutrition-facts-label">' . floatval( self::$data['protein'] ) . '</strong><strong class="nutrition-facts-label">' . $measurements['g'] . '</strong>
		                <strong class="nutrition-facts-right"><span class="nutrition-facts-percent">' . ceil( ( floatval( self::$data['protein'] ) / self::get_label_pdv( 'protein' ) ) * 100 ) . '</span>%</strong>';
			$output .= '</li>';
		}

		$output .= '</ul>';
		$output .= '<hr class="nutrition-facts-hr"/>';

		$output .= '<ul class="nutrition-facts-bottom">' . self::get_nutrients_list() . '</ul>';

		return $output;
	}

	public static function get_horizontal_layout() {
		$output = '';

		$measurements = array(
			'g'  => esc_html__( 'g', 'recipe-card-blocks-by-wpzoom' ),
			'mg' => esc_html__( 'mg', 'recipe-card-blocks-by-wpzoom' ),
		);

		$output .= '<div class="horizontal-column-1">';

		$output .= '<h2>' . esc_html__( 'Nutrition Facts', 'recipe-card-blocks-by-wpzoom' ) . '</h2>';

		if ( isset( self::$data['servings'] ) && ! empty( self::$data['servings'] ) ) {
			$output .= '<p><span class="nutrition-facts-serving">' . sprintf( esc_html__( '%s servings per container', 'recipe-card-blocks-by-wpzoom' ), self::$data['servings'] ) . '</span></p>';
		}
		if ( isset( self::$data['serving-size'] ) && ! empty( self::$data['serving-size'] ) ) {
			$output .= '<p><strong class="nutrition-facts-serving-size">' . self::get_label_title( 'serving-size' ) . '</strong><strong class="nutrition-facts-label nutrition-facts-right">' . floatval( self::$data['serving-size'] ) . $measurements['g'] . '</strong></p>';
		}

		$output .= '<hr class="nutrition-facts-hr"/>';

		if ( isset( self::$data['calories'] ) && ! empty( self::$data['calories'] ) ) {
			$output .= '<p><strong class="nutrition-facts-calories">' . self::get_label_title( 'calories' ) . '</strong><strong class="nutrition-facts-label nutrition-facts-right">' . floatval( self::$data['calories'] ) . '</strong></p>';
		}

		$output .= '</div><!-- /.horizontal-column-1 -->';

		$output .= '<div class="horizontal-column-2">';

		$output .= '<ul>';
		$output .= '<li class="nutrition-facts-no-border"><strong class="nutrition-facts-amount-per-serving">' . esc_html__( 'Amount Per Serving', 'recipe-card-blocks-by-wpzoom' ) . '</strong><strong class="nutrition-facts-right">% ' . esc_html__( 'Daily Value', 'recipe-card-blocks-by-wpzoom' ) . ' *</strong></li>';
		$output .= '<li class="nutrition-facts-spacer"></li>';

		if ( isset( self::$data['total-fat'] ) && ! empty( self::$data['total-fat'] ) ) {
			$output .= '<li class="nutrition-facts-no-border">';
			$output .= '<strong class="nutrition-facts-heading">' . self::get_label_title( 'total-fat' ) . '</strong>
		                <strong class="nutrition-facts-label">' . floatval( self::$data['total-fat'] ) . '</strong><strong class="nutrition-facts-label">' . $measurements['g'] . '</strong>
		                <strong class="nutrition-facts-right"><span class="nutrition-facts-percent">' . ceil( ( floatval( self::$data['total-fat'] ) / self::get_label_pdv( 'total-fat' ) ) * 100 ) . '</span>%</strong>';

			$output .= '<ul>';

			if ( isset( self::$data['saturated-fat'] ) && ! empty( self::$data['saturated-fat'] ) ) {
				$output .= '<li>';
				$output .= '<strong class="nutrition-facts-label">' . self::get_label_title( 'saturated-fat' ) . '</strong>
		                        <strong class="nutrition-facts-label">' . floatval( self::$data['saturated-fat'] ) . '</strong><strong class="nutrition-facts-label">' . $measurements['g'] . '</strong>
		                        <strong class="nutrition-facts-right"><span class="nutrition-facts-percent">' . ceil( ( floatval( self::$data['saturated-fat'] ) / self::get_label_pdv( 'saturated-fat' ) ) * 100 ) . '</span>%</strong>';
				$output .= '</li>';
			}
			if ( isset( self::$data['trans-fat'] ) && ! empty( self::$data['trans-fat'] ) ) {
				$output .= '<li>';
				$output .= '<strong class="nutrition-facts-label">' . self::get_label_title( 'trans-fat' ) . '</strong>
		                        <strong class="nutrition-facts-label">' . floatval( self::$data['trans-fat'] ) . '</strong><strong class="nutrition-facts-label">' . $measurements['g'] . '</strong>';
				$output .= '</li>';
			}

			$output .= '</ul></li>';
		}

		if ( isset( self::$data['cholesterol'] ) && ! empty( self::$data['cholesterol'] ) ) {
			$output .= '<li>';
			$output .= '<strong class="nutrition-facts-heading">' . self::get_label_title( 'cholesterol' ) . '</strong>
		                <strong class="nutrition-facts-label">' . floatval( self::$data['cholesterol'] ) . '</strong><strong class="nutrition-facts-label">' . $measurements['mg'] . '</strong>
		                <strong class="nutrition-facts-right"><span class="nutrition-facts-percent">' . ceil( ( floatval( self::$data['cholesterol'] ) / self::get_label_pdv( 'cholesterol' ) ) * 100 ) . '</span>%</strong>';
			$output .= '</li>';
		}
		if ( isset( self::$data['sodium'] ) && ! empty( self::$data['sodium'] ) ) {
			$output .= '<li>';
			$output .= '<strong class="nutrition-facts-heading">' . self::get_label_title( 'sodium' ) . '</strong>
		                <strong class="nutrition-facts-label">' . floatval( self::$data['sodium'] ) . '</strong><strong class="nutrition-facts-label">' . $measurements['mg'] . '</strong>
		                <strong class="nutrition-facts-right"><span class="nutrition-facts-percent">' . ceil( ( floatval( self::$data['sodium'] ) / self::get_label_pdv( 'sodium' ) ) * 100 ) . '</span>%</strong>';
			$output .= '</li>';
		}

		$output .= '<li class="nutrition-facts-spacer"></li>';
		$output .= '</ul>';
		$output .= '</div><!-- /.horizontal-column-2 -->';

		$output .= '<div class="horizontal-column-3">';

		$output .= '<ul>';
		$output .= '<li class="nutrition-facts-no-border"><strong class="nutrition-facts-amount-per-serving">' . esc_html__( 'Amount Per Serving', 'recipe-card-blocks-by-wpzoom' ) . '</strong><strong class="nutrition-facts-right">% ' . esc_html__( 'Daily Value', 'recipe-card-blocks-by-wpzoom' ) . ' *</strong></li>';
		$output .= '<li class="nutrition-facts-spacer"></li>';

		if ( isset( self::$data['potassium'] ) && ! empty( self::$data['potassium'] ) ) {
			$output .= '<li class="nutrition-facts-no-border">';
			$output .= '<strong class="nutrition-facts-heading">' . self::get_label_title( 'potassium' ) . '</strong>
		                <strong class="nutrition-facts-label">' . floatval( self::$data['potassium'] ) . '</strong><strong class="nutrition-facts-label">' . $measurements['mg'] . '</strong>
		                <strong class="nutrition-facts-right"><span class="nutrition-facts-percent">' . ceil( ( floatval( self::$data['potassium'] ) / self::get_label_pdv( 'potassium' ) ) * 100 ) . '</span>%</strong>';
			$output .= '</li>';
		}

		if ( isset( self::$data['total-carbohydrate'] ) && ! empty( self::$data['total-carbohydrate'] ) ) {
			$output .= '<li>';
			$output .= '<strong class="nutrition-facts-heading">' . self::get_label_title( 'total-carbohydrate' ) . '</strong>
		                <strong class="nutrition-facts-label">' . floatval( self::$data['total-carbohydrate'] ) . '</strong><strong class="nutrition-facts-label">' . $measurements['g'] . '</strong>
		                <strong class="nutrition-facts-right"><span class="nutrition-facts-percent">' . ceil( ( floatval( self::$data['total-carbohydrate'] ) / self::get_label_pdv( 'total-carbohydrate' ) ) * 100 ) . '</span>%</strong>';

			$output .= '<ul>';

			if ( isset( self::$data['dietary-fiber'] ) && ! empty( self::$data['dietary-fiber'] ) ) {
				$output .= '<li>';
				$output .= '<strong class="nutrition-facts-label">' . self::get_label_title( 'dietary-fiber' ) . '</strong>
		                    <strong class="nutrition-facts-label">' . floatval( self::$data['dietary-fiber'] ) . '</strong><strong class="nutrition-facts-label">' . $measurements['g'] . '</strong>
		                    <strong class="nutrition-facts-right"><span class="nutrition-facts-percent">' . ceil( ( floatval( self::$data['dietary-fiber'] ) / self::get_label_pdv( 'dietary-fiber' ) ) * 100 ) . '</span>%</strong>';
				$output .= '</li>';
			}
			if ( isset( self::$data['sugars'] ) && ! empty( self::$data['sugars'] ) ) {
				$output .= '<li>';
				$output .= '<strong class="nutrition-facts-label">' . self::get_label_title( 'sugars' ) . '</strong>
		                    <strong class="nutrition-facts-label">' . floatval( self::$data['sugars'] ) . '</strong><strong class="nutrition-facts-label">' . $measurements['g'] . '</strong>';
				$output .= '</li>';
			}

			$output .= '</ul></li>';
		}

		if ( isset( self::$data['protein'] ) && ! empty( self::$data['protein'] ) ) {
			$output .= '<li>';
			$output .= '<strong class="nutrition-facts-heading">' . self::get_label_title( 'protein' ) . '</strong>
		                <strong class="nutrition-facts-label">' . floatval( self::$data['protein'] ) . '</strong><strong class="nutrition-facts-label">' . $measurements['g'] . '</strong>
		                <strong class="nutrition-facts-right"><span class="nutrition-facts-percent">' . ceil( ( floatval( self::$data['protein'] ) / self::get_label_pdv( 'protein' ) ) * 100 ) . '</span>%</strong>';
			$output .= '</li>';
		}

		$output .= '<li class="nutrition-facts-spacer"></li>';
		$output .= '</ul>';
		$output .= '</div><!-- /.horizontal-column-3 -->';

		$output .= '<ul class="nutrition-facts-bottom">' . self::get_nutrients_list() . '</ul>';

		return $output;
	}

	public static function get_labels() {
		$labels = array(
			array(
				'id'    => 'serving-size',
				'label' => esc_html__( 'Serving Size', 'recipe-card-blocks-by-wpzoom' ),
			),
			array(
				'id'    => 'servings',
				'label' => esc_html__( 'Servings', 'recipe-card-blocks-by-wpzoom' ),
			),
			array(
				'id'    => 'calories',
				'label' => esc_html__( 'Calories', 'recipe-card-blocks-by-wpzoom' ),
			),
			array(
				'id'    => 'total-fat',
				'label' => esc_html__( 'Total Fat', 'recipe-card-blocks-by-wpzoom' ),
				'pdv'   => 65,
			),
			array(
				'id'    => 'saturated-fat',
				'label' => esc_html__( 'Saturated Fat', 'recipe-card-blocks-by-wpzoom' ),
				'pdv'   => 20,
			),
			array(
				'id'    => 'trans-fat',
				'label' => esc_html__( 'Trans Fat', 'recipe-card-blocks-by-wpzoom' ),
			),
			array(
				'id'    => 'cholesterol',
				'label' => esc_html__( 'Cholesterol', 'recipe-card-blocks-by-wpzoom' ),
				'pdv'   => 300,
			),
			array(
				'id'    => 'sodium',
				'label' => esc_html__( 'Sodium', 'recipe-card-blocks-by-wpzoom' ),
				'pdv'   => 2400,
			),
			array(
				'id'    => 'potassium',
				'label' => esc_html__( 'Potassium', 'recipe-card-blocks-by-wpzoom' ),
				'pdv'   => 3500,
			),
			array(
				'id'    => 'total-carbohydrate',
				'label' => esc_html__( 'Total Carbohydrate', 'recipe-card-blocks-by-wpzoom' ),
				'pdv'   => 300,
			),
			array(
				'id'    => 'dietary-fiber',
				'label' => esc_html__( 'Dietary Fiber', 'recipe-card-blocks-by-wpzoom' ),
				'pdv'   => 25,
			),
			array(
				'id'    => 'sugars',
				'label' => esc_html__( 'Sugars', 'recipe-card-blocks-by-wpzoom' ),
			),
			array(
				'id'    => 'protein',
				'label' => esc_html__( 'Protein', 'recipe-card-blocks-by-wpzoom' ),
				'pdv'   => 50,
			),
			array(
				'id'    => 'vitamin-a',
				'label' => esc_html__( 'Vitamin A', 'recipe-card-blocks-by-wpzoom' ),
			),
			array(
				'id'    => 'vitamin-c',
				'label' => esc_html__( 'Vitamin C', 'recipe-card-blocks-by-wpzoom' ),
			),
			array(
				'id'    => 'calcium',
				'label' => esc_html__( 'Calcium', 'recipe-card-blocks-by-wpzoom' ),
			),
			array(
				'id'    => 'iron',
				'label' => esc_html__( 'Iron', 'recipe-card-blocks-by-wpzoom' ),
			),
			array(
				'id'    => 'vitamin-d',
				'label' => esc_html__( 'Vitamin D', 'recipe-card-blocks-by-wpzoom' ),
			),
			array(
				'id'    => 'vitamin-e',
				'label' => esc_html__( 'Vitamin E', 'recipe-card-blocks-by-wpzoom' ),
			),
			array(
				'id'    => 'vitamin-k',
				'label' => esc_html__( 'Vitamin K', 'recipe-card-blocks-by-wpzoom' ),
			),
			array(
				'id'    => 'thiamin',
				'label' => esc_html__( 'Thiamin', 'recipe-card-blocks-by-wpzoom' ),
			),
			array(
				'id'    => 'riboflavin',
				'label' => esc_html__( 'Riboflavin', 'recipe-card-blocks-by-wpzoom' ),
			),
			array(
				'id'    => 'niacin',
				'label' => esc_html__( 'Niacin', 'recipe-card-blocks-by-wpzoom' ),
			),
			array(
				'id'    => 'vitamin-b6',
				'label' => esc_html__( 'Vitamin B6', 'recipe-card-blocks-by-wpzoom' ),
			),
			array(
				'id'    => 'vitamin-b12',
				'label' => esc_html__( 'Vitamin B12', 'recipe-card-blocks-by-wpzoom' ),
			),
			array(
				'id'    => 'folate',
				'label' => esc_html__( 'Folate', 'recipe-card-blocks-by-wpzoom' ),
			),
			array(
				'id'    => 'biotin',
				'label' => esc_html__( 'Biotin', 'recipe-card-blocks-by-wpzoom' ),
			),
			array(
				'id'    => 'pantothenic-acid',
				'label' => esc_html__( 'Pantothenic Acid', 'recipe-card-blocks-by-wpzoom' ),
			),
			array(
				'id'    => 'phosphorus',
				'label' => esc_html__( 'Phosphorus', 'recipe-card-blocks-by-wpzoom' ),
			),
			array(
				'id'    => 'iodine',
				'label' => esc_html__( 'Iodine', 'recipe-card-blocks-by-wpzoom' ),
			),
			array(
				'id'    => 'magnesium',
				'label' => esc_html__( 'Magnesium', 'recipe-card-blocks-by-wpzoom' ),
			),
			array(
				'id'    => 'zinc',
				'label' => esc_html__( 'Zinc', 'recipe-card-blocks-by-wpzoom' ),
			),
			array(
				'id'    => 'selenium',
				'label' => esc_html__( 'Selenium', 'recipe-card-blocks-by-wpzoom' ),
			),
			array(
				'id'    => 'copper',
				'label' => esc_html__( 'Copper', 'recipe-card-blocks-by-wpzoom' ),
			),
			array(
				'id'    => 'manganese',
				'label' => esc_html__( 'Manganese', 'recipe-card-blocks-by-wpzoom' ),
			),
			array(
				'id'    => 'chromium',
				'label' => esc_html__( 'Chromium', 'recipe-card-blocks-by-wpzoom' ),
			),
			array(
				'id'    => 'molybdenum',
				'label' => esc_html__( 'Molybdenum', 'recipe-card-blocks-by-wpzoom' ),
			),
			array(
				'id'    => 'chloride',
				'label' => esc_html__( 'Chloride', 'recipe-card-blocks-by-wpzoom' ),
			),
		);

		return $labels;
	}

	public static function set_labels() {
		self::$labels = self::get_labels();
	}

	public static function get_label_title( $label ) {
		$key = array_search( $label, array_column( self::$labels, 'id' ) );

		return self::$labels[ $key ]['label'];
	}

	public static function get_label_pdv( $label ) {
		$key = array_search( $label, array_column( self::$labels, 'id' ) );

		return floatval( self::$labels[ $key ]['pdv'] );
	}
}
