<?php
/**
 * Structured Data Render
 *
 * @since   1.1.0
 * @package WPZOOM_Recipe_Card_Blocks
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class to load assets required for structured data blocks.
 */
class WPZOOM_Structured_Data_Render {
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
		$this->load_dependencies();
		$this->structured_data_helpers = new WPZOOM_Structured_Data_Helpers();

		add_filter( 'the_content', array( $this, 'filter_content_blocks' ) );
	}

	/**
	 * Load all plugin dependecies.
	 *
	 * @access private
	 * @since 1.1.0
	 * @return void
	 */
	private function load_dependencies() {
		require_once WPZOOM_RCB_PLUGIN_DIR . 'src/classes/class-wpzoom-structured-data-helpers.php';
		require_once WPZOOM_RCB_SD_BLOCKS_DIR . 'class-wpzoom-details.php';
		require_once WPZOOM_RCB_SD_BLOCKS_DIR . 'class-wpzoom-ingredients.php';
		require_once WPZOOM_RCB_SD_BLOCKS_DIR . 'class-wpzoom-steps.php';
		require_once WPZOOM_RCB_SD_BLOCKS_DIR . 'class-wpzoom-jump-to-recipe.php';
		require_once WPZOOM_RCB_SD_BLOCKS_DIR . 'class-wpzoom-print-recipe.php';
		require_once WPZOOM_RCB_SD_BLOCKS_DIR . 'class-wpzoom-recipe-card-block.php';

		if ( WPZOOM_RCB_HAS_PRO ) {
			require_once WPZOOM_RCB_SD_BLOCKS_DIR . 'class-wpzoom-premium-recipe-card-block.php';
		}
	}

	/**
	 * Registers hooks for Structured Data Blocks with WordPress.
	 */
	public function register_hooks() {
		$block_integrations = array(
			new WPZOOM_Details_Block(),
			new WPZOOM_Ingredients_Block(),
			new WPZOOM_Steps_Block(),
			new WPZOOM_Jump_To_Recipe_Block(),
			new WPZOOM_Print_Recipe_Block(),
			new WPZOOM_Recipe_Card_Block()
		);

		if ( WPZOOM_RCB_HAS_PRO ) {
			$block_integrations[] = new WPZOOM_Premium_Recipe_Card_Block();
		}

		foreach ( $block_integrations as $block_integration ) {
			$block_integration->register_hooks();
		}
	}

	/**
	 * Render Structured Data (Schema.org) for blocks Details, Ingredients, Directions.
	 * 
	 * @since 1.2.0
	 * @param string $content 
	 * @return string Rendered JSON-LD string and post content
	 */
	public function filter_content_blocks( $content ) {
		$post = get_post();
		$attributes = array(); // store all attributes of parsed blocks from post content

		// check if we are in the main loop
		if ( in_the_loop() ) {
			if ( has_blocks( $post->post_content ) ) {
			    $blocks = parse_blocks( $post->post_content );
			    $blocks_needed = array( 'wpzoom-recipe-card/block-details', 'wpzoom-recipe-card/block-ingredients', 'wpzoom-recipe-card/block-directions' );

			    foreach ( $blocks as $key => $block ) {
			    	// I we have recipe card block in post content then Schema.org already exists for this block
			    	// so we need to exit from this loop and return post content
			    	if ( $block['blockName'] === 'wpzoom-recipe-card/block-recipe-card' ) {
			    		return $content;
			    	}

			    	if ( in_array( $block['blockName'], $blocks_needed ) ) {
			    		$attributes = array_merge( $attributes, $block['attrs'] );
			    	}
			    }

			    if ( ! empty( $attributes ) ) {
			    	$json_ld = $this->get_json_ld( $post, $attributes );
			    	return '<script type="application/ld+json">' . wp_json_encode( $json_ld ) . '</script>' . $content;
			    }
			}
		}

		return $content;
	}

	/**
	 * Returns the JSON-LD for a details block.
	 *
	 * @since 1.2.0
	 * @param array $attributes The attributes of the details block.
	 * @return array The JSON-LD representation of the details block.
	 */
	protected function get_json_ld( $post, array $attributes ) {
		$tag_list = wp_get_post_terms( $post->ID, 'post_tag', array( 'fields' => 'names' ) );
		$cat_list = wp_get_post_terms( $post->ID, 'category', array( 'fields' => 'names' ) );

		$json_ld = array(
			'@context' 		=> 'https://schema.org',
			'@type'    		=> 'Recipe',
			'author' 		=> array(
				'@type'		=> 'Person',
				'name'		=> get_the_author()
			),
			'name'			=> $post->post_title,
			'description' 	=> $post->post_excerpt,
			'image'			=> get_the_post_thumbnail_url( $post ),
			'recipeCategory' => $cat_list,
			'recipeCuisine'  => array(),
			'keywords'  	=> $tag_list,
			'datePublished' => get_the_time( 'c', $post ),
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
