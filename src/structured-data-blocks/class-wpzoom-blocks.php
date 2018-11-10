<?php
/**
 * WPZOOM Blocks
 *
 * Beacause blocks are separated, we need to show all on page to create Structured Data correctly.
 *
 * @since   1.0.1
 * @package WPZOOM Recipe Card Block
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main WPZOOM_Blocks Class.
 */
class WPZOOM_Blocks {
	/**
	 * The post Object.
	 *
	 * @since 1.0.1
	 */
	private $post;

	/**
	 * This variable is used to concat all blocks into one for output.
	 *
	 * @since 1.0.1
	 */
	private $aux_content = '';

	/**
	 * This variable stores all attributes of rendered blocks.
	 *
	 * @since 1.0.1
	 */
	private $aux_attributes = array();

	/**
	 * We need to know if all blocks is rendered on page, then to create Structured Data JSON-LD.
	 *
	 * @since 1.0.1
	 */
	private $blocks_render = 0;

	/**
	 * Registers the details block as a server-side rendered block.
	 *
	 * @return void
	 */
	public function register_hooks() {
		if ( ! function_exists( 'register_block_type' ) ) {
			return;
		}

		register_block_type( 'wpzoom-recipe-card/block-details', array(
			'render_callback' => array( $this, 'render' ),
		) );

		register_block_type( 'wpzoom-recipe-card/block-ingredients', array(
			'render_callback' => array( $this, 'render' ),
		) );

		register_block_type( 'wpzoom-recipe-card/block-directions', array(
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

		$this->post = get_post();

		if ( $this->blocks_render < 3 ) {
			$this->aux_content .= $content;
			$this->aux_attributes = array_merge( $this->aux_attributes, $attributes );
		}

		$this->blocks_render += 1;
		if ( $this->blocks_render === 3 ) {
			$json_ld = $this->get_json_ld( $this->aux_attributes );

			return '<script type="application/ld+json">' . wp_json_encode( $json_ld ) . '</script>' . '<div class="wpzoom-structured-data-render-blocks">' . $this->aux_content . '</div>';
		}
	}

	/**
	 * Returns the JSON-LD for a details block.
	 *
	 * @param array $attributes The attributes of the details block.
	 *
	 * @return array The JSON-LD representation of the details block.
	 */
	protected function get_json_ld( array $attributes ) {
		$tag_list = wp_get_post_terms( $this->post->ID, 'post_tag', array( 'fields' => 'names' ) );
		$cat_list = wp_get_post_terms( $this->post->ID, 'category', array( 'fields' => 'names' ) );

		$json_ld = array(
			'@context' 		=> 'https://schema.org',
			'@type'    		=> 'Recipe',
			'author' 		=> array(
				'@type'		=> 'Person',
				'name'		=> get_the_author()
			),
			// 'aggregateRating' => array(
			//     '@type'		  => 'AggregateRating',
			//     'ratingValue' => '',
			//     'reviewCount' => ''
			// ),
			'name'			=> $this->post->post_title,
			'description' 	=> $this->post->post_excerpt,
			'image'			=> get_the_post_thumbnail_url(),
			'recipeCategory' => $cat_list,
			'recipeCuisine'  => array(),
			'keywords'  	=> $tag_list,
			'datePublished' => get_the_time('c'),
			'nutrition' 	=> array(
				'@type' 	=> 'NutritionInformation'
			),
			'recipeIngredient'	 => array(),
			'recipeInstructions' => array(),
		);

		if ( ! empty( $attributes['course'] ) ) {
			$json_ld['recipeCategory'] = $attributes['course'];
		}

		if ( ! empty( $attributes['cuisine'] ) ) {
			$json_ld['recipeCuisine'] = $attributes['cuisine'];
		}

		if ( ! empty( $attributes['keywords'] ) ) {
			$json_ld['keywords'] = $attributes['keywords'];
		}

		if ( ! empty( $attributes['details'] ) && is_array( $attributes['details'] ) ) {
			$details = array_filter( $attributes['details'], 'is_array' );

			foreach ( $details as $key => $detail ) {
				if ( $key === 0 ) {
					$json_ld['nutrition']['servingSize'] = $detail['jsonValue'];
				}
				elseif ( $key === 3 ) {
					$json_ld['nutrition']['calories'] = $detail['jsonValue'];
				}
				elseif ( $key === 1 ) {
					$prepTime = $this->get_number_from_string( $detail['jsonValue'] );
				    $json_ld['prepTime'] = $this->get_period_time( $detail['jsonValue'] );
				}
				elseif ( $key === 2 ) {
					$cookTime = $this->get_number_from_string( $detail['jsonValue'] );
				    $json_ld['cookTime'] = $this->get_period_time( $detail['jsonValue'] );
				}
			}

			if ( isset( $prepTime, $cookTime ) && ( $prepTime + $cookTime ) > 0 ) {
				$json_ld['totalTime'] = $this->get_period_time( $prepTime + $cookTime );
			}
		}

		if ( ! empty( $attributes['items'] ) && is_array( $attributes['items'] ) ) {
			$ingredients = array_filter( $attributes['items'], 'is_array' );
			foreach ( $ingredients as $ingredient ) {
				$json_ld['recipeIngredient'][] = $this->get_ingredient_json_ld( $ingredient );
			}
		}

		if ( ! empty( $attributes['steps'] ) && is_array( $attributes['steps'] ) ) {
			$steps = array_filter( $attributes['steps'], 'is_array' );
			foreach ( $steps as $step ) {
				$json_ld['recipeInstructions'][] = $this->get_step_json_ld( $step );
			}
		}

		return $json_ld;
	}

	/**
	 * Returns the JSON-LD for a ingredient's name in a details block.
	 *
	 * @param array $ingredient The attributes of a ingredient in the details block.
	 *
	 * @return array The JSON-LD representation of the ingredient name in a details block.
	 */
	protected function get_ingredient_json_ld( array $ingredient ) {
		$ingredient_json_ld = '';

		if ( ! empty( $ingredient['jsonName'] ) ) {
			$ingredient_json_ld = $ingredient['jsonName'];
		} else {
			$ingredient_json_ld = $this->ingredient_name_to_JSON( $ingredient['name'] );
		}

		return $ingredient_json_ld;
	}

	/**
	 * Backward compatibility with ingredients that don't have jsonName attribute.
	 *
	 * @param array $ingredient_name The ingredient name array.
	 *
	 * @return string The json name generated from array.
	 */
	protected function ingredient_name_to_JSON( array $ingredient_name, string $jsonName = '' ) {
		foreach ( $ingredient_name as $name ) {
			if ( ! is_array( $name ) ) {
				$jsonName .= $name;
			} else {
				$jsonName = $this->ingredient_name_to_JSON( $name['props']['children'], $jsonName );
			}
		}

		return $jsonName;
	}

	/**
	 * Returns the JSON-LD for a step's description in a details block.
	 *
	 * @param array $step The attributes of a step(-section) in the details block.
	 *
	 * @return array The JSON-LD representation of the step's description in a details block.
	 */
	protected function get_step_json_ld( array $step ) {
		$step_json_ld = array(
			'@type' => 'HowToStep',
		);

		if ( ! empty( $step['jsonText'] ) ) {
			$step_json_ld['text'] = $step['jsonText'];
		} else {
			$step_json_ld['text'] = $this->step_text_to_JSON( $step['text'] );
		}

		return $step_json_ld;
	}

	/**
	 * Backward compatibility with steps that don't have jsonText attribute.
	 *
	 * @param array $step_text The step text array.
	 *
	 * @return string The json text generated from array.
	 */
	protected function step_text_to_JSON( array $step_text, string $jsonText = '' ) {
		foreach ( $step_text as $text ) {
			if ( ! is_array( $text ) ) {
				$jsonText .= $text;
			} else {
				$jsonText = $this->step_text_to_JSON( $text['props']['children'], $jsonText );
			}
		}

		return $jsonText;
	}

	/**
	 * Returns the date value in ISO 8601 date format.
	 *
	 * @param string $string The string value with number and unit.
	 *
	 * @return string A textual string indicating a time period in ISO 8601 time interval format.
	 */
	protected function get_period_time( string $string ) {
		$time = $this->get_number_from_string( $string );

		$hours = floor( $time / 60 );
		$days = round( $hours / 24 );
		$minutes = ( $time % 60 );
		$period = 'P';

		if ( $days ) {
			$hours = ( $hours % 24 );
			$period .= $days . 'D';
		}

		if ( $hours ) {
			$period .= 'T' . $hours . 'H';
		}

		if ( $minutes ) {
			$period .= $minutes . 'M';
		}

		return $period;
	}

	/**
	 * Returns the number from string.
	 *
	 * @param string $string The string value with number and unit.
	 *
	 * @return number The first number matched from string.
	 */
	protected function get_number_from_string( $string ) {
		$re = '/\d+/s';
		preg_match($re, $string, $matches);

		return isset($matches[0]) ? (int)$matches[0] : 0;
	}
}