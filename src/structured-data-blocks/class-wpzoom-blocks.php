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
	 * Class instance Structured Data Helpers.
	 *
	 * @var WPZOOM_Structured_Data_Helpers
	 * @since 1.0.1
	 */
	private $structured_data_helpers;

	/**
	 * Output content.
	 *
	 * @since 1.0.1
	 */
	private $output = '';

	/**
	 * The Constructor.
	 */
	public function __construct() {
		$this->structured_data_helpers = new WPZOOM_Structured_Data_Helpers();
	}

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
		
		$blocks_count = isset( $attributes['blocks_count'] ) ? $attributes['blocks_count'] : 0;

		if ( $blocks_count === 0 ) {
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

			$this->output = '<script type="application/ld+json">' . wp_json_encode( $json_ld ) . '</script>' . '<div class="wpzoom-structured-data-render-blocks">' . $this->aux_content . '</div>';

			return force_balance_tags( $this->output );
		}
		elseif ( $this->blocks_render === $blocks_count ) {
			$this->output .= $content;

			return force_balance_tags( $this->output );
		}
		else {
			$this->output .= $content;
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

		if ( ! empty( $attributes['items'] ) && is_array( $attributes['items'] ) ) {
			$ingredients = array_filter( $attributes['items'], 'is_array' );
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