<?php
/**
 * Recipe Card Block
 *
 * @since   1.0.1
 * @package WPZOOM Recipe Card Block
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main WPZOOM_Recipe_Card_Block Class.
 */
class WPZOOM_Recipe_Card_Block {
	/**
	 * The post Object.
	 *
	 * @since 1.0.1
	 */
	private $post;

	/**
	 * Class instance Structured Data Helpers.
	 *
	 * @var WPZOOM_Structured_Data_Helpers
	 * @since 1.0.1
	 */
	private $structured_data_helpers;

	/**
	 * The Constructor.
	 */
	public function __construct() {
		$this->structured_data_helpers = new WPZOOM_Structured_Data_Helpers();
	}

	/**
	 * Registers the recipe-card block as a server-side rendered block.
	 *
	 * @return void
	 */
	public function register_hooks() {
		if ( ! function_exists( 'register_block_type' ) ) {
			return;
		}

		register_block_type( 'wpzoom-recipe-card/block-recipe-card', array(
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

		$json_ld = $this->get_json_ld( $attributes );

		return '<script type="application/ld+json">' . wp_json_encode( $json_ld ) . '</script>' . $content;
	}

	/**
	 * Returns the JSON-LD for a recipe-card block.
	 *
	 * @param array $attributes The attributes of the recipe-card block.
	 *
	 * @return array The JSON-LD representation of the recipe-card block.
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
			'image'			=> '',
			'video'			=> array(
				'name'  	=> '',
				'description' 	=> '',
				'thumbnailUrl' 	=> '',
				'contentUrl' 	=> '',
				'embedUrl' 		=> '',
				'uploadDate' 	=> '',
				'duration' 		=> '',
			),
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

		if ( ! empty( $attributes['jsonName'] ) ) {
			$json_ld['name'] = $attributes['jsonName'];
		}

		if ( ! empty( $attributes['jsonSummary'] ) ) {
			$json_ld['description'] = $attributes['jsonSummary'];
		}

		if ( ! empty( $attributes['image'] ) && $attributes['hasImage'] ) {
			$json_ld['image'] = $attributes['image']['url'];
		}

		if ( ! empty( $attributes['video'] ) && $attributes['hasVideo'] ) {
			if ( isset( $attributes['video']['id'] ) ) {
				$videoObject = get_post( $attributes['video']['id'] );

				$json_ld['video']['name'] 			= $videoObject->post_name;
				$json_ld['video']['description'] 	= isset( $attributes['video']['caption'] ) ? $attributes['video']['caption'] : $videoObject->post_excerpt;
				$json_ld['video']['uploadDate'] 	= get_the_time( 'c', $videoObject );
				$json_ld['video']['thumbnailUrl'] 	= isset( $attributes['video']['poster'] ) ? $attributes['video']['poster'] : '';
				$json_ld['video']['contentUrl'] 	= isset( $attributes['video']['src'] ) ? $attributes['video']['src'] : '';
			} else {
				$json_ld['video']['description'] 	= isset( $attributes['video']['caption'] ) ? $attributes['video']['caption'] : '';
				$json_ld['video']['contentUrl'] 	= isset( $attributes['video']['url'] ) ? $attributes['video']['url'] : '';
			}
		}

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
					if ( ! empty( $detail['jsonValue'] ) ) {
						$json_ld['nutrition']['servingSize'] = $detail['jsonValue'];
					}
				}
				elseif ( $key === 3 ) {
					if ( ! empty( $detail['jsonValue'] ) ) {
						$json_ld['nutrition']['calories'] = $detail['jsonValue'];
					}
				}
				elseif ( $key === 1 ) {
					if ( ! empty( $detail['jsonValue'] ) ) {
						$prepTime = $this->structured_data_helpers->get_number_from_string( $detail['jsonValue'] );
					    $json_ld['prepTime'] = $this->structured_data_helpers->get_period_time( $detail['jsonValue'] );
					}
				}
				elseif ( $key === 2 ) {
					if ( ! empty( $detail['jsonValue'] ) ) {
						$cookTime = $this->structured_data_helpers->get_number_from_string( $detail['jsonValue'] );
					    $json_ld['cookTime'] = $this->structured_data_helpers->get_period_time( $detail['jsonValue'] );
					}
				}
			}

			if ( isset( $prepTime, $cookTime ) && ( $prepTime + $cookTime ) > 0 ) {
				$json_ld['totalTime'] = $this->structured_data_helpers->get_period_time( $prepTime + $cookTime );
			}
		}

		if ( ! empty( $attributes['ingredients'] ) && is_array( $attributes['ingredients'] ) ) {
			$ingredients = array_filter( $attributes['ingredients'], 'is_array' );
			foreach ( $ingredients as $ingredient ) {
				$json_ld['recipeIngredient'][] = $this->structured_data_helpers->get_ingredient_json_ld( $ingredient );
			}
		}

		if ( ! empty( $attributes['steps'] ) && is_array( $attributes['steps'] ) ) {
			$steps = array_filter( $attributes['steps'], 'is_array' );
			foreach ( $steps as $step ) {
				$json_ld['recipeInstructions'][] = $this->structured_data_helpers->get_step_json_ld( $step );
			}
		}

		return $json_ld;
	}
}