<?php
/**
 * Premium Recipe Card Block
 *
 * @since   1.0.1
 * @package WPZOOM Recipe Card Block
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main WPZOOM_Premium_Recipe_Card_Block Class.
 */
class WPZOOM_Premium_Recipe_Card_Block {
	/**
	 * The post Object.
	 *
	 * @since 1.0.1
	 */
	private $recipe;

	/**
	 * Class instance Structured Data Helpers.
	 *
	 * @var WPZOOM_Structured_Data_Helpers
	 * @since 1.0.1
	 */
	private $structured_data_helpers;

	/**
	 * Class instance Helpers.
	 *
	 * @var WPZOOM_Helpers
	 * @since 1.0.1
	 */
	private $helpers;

	/**
	 * Class instance WPZOOM Rating Stars.
	 *
	 * @var WPZOOM_Rating_Stars
	 * @since 1.0.1
	 */
	private $wpzoom_rating;

	/**
	 * Block attributes.
	 *
	 * @since 1.0.1
	 */
	public $attributes;

	/**
	 * Block settings.
	 *
	 * @since 1.0.1
	 */
	public $settings;

	/**
	 * Block active style.
	 *
	 * @since 1.0.1
	 */
	public $style;

	/**
	 * The Constructor.
	 */
	public function __construct() {
		$this->structured_data_helpers = new WPZOOM_Structured_Data_Helpers();
		$this->helpers = new WPZOOM_Helpers();
		$this->wpzoom_rating = new WPZOOM_Rating_Stars();
	}

	/**
	 * Registers the premium-recipe-card block as a server-side rendered block.
	 *
	 * @return void
	 */
	public function register_hooks() {
		if ( ! function_exists( 'register_block_type' ) ) {
			return;
		}

		register_block_type(
			'wpzoom-recipe-card/block-premium-recipe-card',
			array(
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
		
		// echo '<pre>';
		// print_r($attributes);
		// echo '</pre>';

		$attributes = $this->helpers->omit( $attributes, array( 'toInsert' ) );

		// Import variables into the current symbol table from an array
		extract( $attributes );

		$class = 'wp-block-wpzoom-recipe-card-block-premium-recipe-card';

		// Recipe post variables
		$this->recipe 			= get_post();
		$recipe_ID 				= get_the_ID( $this->recipe );
		$recipe_title 			= get_the_title( $this->recipe );
		$recipe_thumbnail_url 	= get_the_post_thumbnail_url( $this->recipe );
		$recipe_permalink 		= get_the_permalink( $this->recipe );
		$recipe_author_name 	= get_the_author_meta( 'display_name', $this->recipe->post_author );

		// Variables from attributes
		// add default value if not exists
		$recipeTitle 	= isset( $recipeTitle ) ? $recipeTitle : null;
		$summary 		= isset( $summary ) ? $summary : null;
		$jsonSummary 	= isset( $jsonSummary ) ? $jsonSummary : null;
		$style 			= isset( $style ) ? $style : 'default';
		$className 		= isset( $className ) ? $className : '';
		$hasImage 		= isset( $hasImage ) ? $hasImage : false;
		$course 		= isset( $course ) ? $course : array();
		$cuisine 		= isset( $cuisine ) ? $cuisine : array();
		$difficulty 	= isset( $difficulty ) ? $difficulty : array();
		$keywords 		= isset( $keywords ) ? $keywords : array();
		$details 		= isset( $details ) ? $details : array();
		$ingredients 	= isset( $ingredients ) ? $ingredients : array();
		$steps 			= isset( $steps ) ? $steps : array();
		$settings 		= isset( $settings ) ? $settings[0] : array();

		// Store variables
		$this->attributes 	= $attributes;
		$this->style 		= $style;
		$this->settings 	= $settings;

		$this->attributes['ingredientsTitle'] = isset( $ingredientsTitle ) ? $ingredientsTitle : __( "Ingredients", "wpzoom-recipe-card" );
		$this->attributes['directionsTitle'] = isset( $directionsTitle ) ? $directionsTitle : __( "Directions", "wpzoom-recipe-card" );

		$class .= strpos( $className, 'is-style' ) === false ? ' is-style-' . $style : '';

		$RecipeCardClassName 	= implode( ' ', array( $class, $className, @$settings['additionalClasses'] ) );
		$PrintClasses 			= implode( ' ', array( "wpzoom-recipe-card-print-link", @$settings['print_btn'] ) );
		$PinterestClasses 		= implode( ' ', array( "wpzoom-recipe-card-pinit", @$settings['pin_btn'] ) );
		$pinitURL 				= 'https://www.pinterest.com/pin/create/button/?url=' . $recipe_permalink .'/&media='. ( $hasImage ? $image['url'] : $recipe_thumbnail_url ) .'&description='. ( $recipeTitle ? strip_tags( $recipeTitle ) : $jsonSummary ? strip_tags( $jsonSummary ) : strip_tags( $recipe_title ) ) .'';

		$printStyles = '';
		if ( 'default' === $style ) {
			$styles = array(
				'background-color' => @$settings['primary_color'],
			);
			$printStyles = $this->helpers->render_styles_attributes( $styles );
		} else if ( 'newdesign' === $style ) {
			$styles = array(
				'background-color' => @$settings['primary_color'],
				'box-shadow' => '0 5px 40px '. @$settings['primary_color'] . ''
			);
			$printStyles = $this->helpers->render_styles_attributes( $styles );
		}

		$recipe_card_image = $hasImage ? 
			'<div class="recipe-card-image">
				<figure>
					<img src="'. $image['url'] .'" id="'. $image['id'] .'" alt="'. ( $recipeTitle ? strip_tags( $recipeTitle ) : strip_tags( $recipe_title ) ) .'"/>
					<figcaption>
						<div class="'. esc_attr( $PinterestClasses ) .'">
		                    <a class="btn-pinit-link no-print" data-pin-do="buttonPin" href="'. esc_url( $pinitURL ) .'" data-pin-custom="true">
		                    	<i class="fa fa-pinterest-p icon-pinit-link"></i>
		                    	<span>'. __( "Pin", "wpzoom-recipe-card" ) .'</span>
		                    </a>
		                </div>
						<div class="'. esc_attr( $PrintClasses ) .'">
		                    <a class="btn-print-link no-print" href="#'. $id .'" title="'. __( "Print directions...", "wpzoom-recipe-card" ) .'" style="'. $printStyles .'">
		                    	<i class="fa fa-print icon-print-link"></i>
		                        <span>'. __( "Print", "wpzoom-recipe-card" ) .'</span>
		                    </a>
		                </div>
		            </figcaption>
				</figure>
			</div>' : '';

		$recipe_card_heading = '
			<div class="recipe-card-heading">
				'. sprintf( '<h1 class="%s">%s</h1>', "recipe-card-title", ( $recipeTitle ? strip_tags( $recipeTitle ) : strip_tags( $recipe_title ) ) ) .
				$this->wpzoom_rating->get_rating_form( $recipe_ID ) .
				( @$settings['displayAuthor'] ? '<span class="recipe-card-author">'. __( "Recipe by", "wpzoom-recipe-card" ) . " " . @$settings['custom_author_name'] .'</span>' : '' ) .
				( @$settings['displayCourse'] ? '<span class="recipe-card-course">'. __( "Course:", "wpzoom-recipe-card" ) . ' <mark>' . implode( ', ', $course ) .'</mark></span>' : '' ) .
				( @$settings['displayCuisine'] ? '<span class="recipe-card-cuisine">'. __( "Cuisine:", "wpzoom-recipe-card" ) . ' <mark>' . implode( ', ', $cuisine ) .'</mark></span>' : '' ) .
				( @$settings['displayDifficulty'] ? '<span class="recipe-card-difficulty">'. __( "Difficulty:", "wpzoom-recipe-card" ) . ' <mark>' . implode( ', ', $difficulty ) .'</mark></span>' : '' ) .
			'</div>';

		$summary_text = ! empty( $jsonSummary ) ? 
			sprintf(
				'<p class="recipe-card-summary">%s</p>',
				$summary
			) : '';

		$details_content = $this->get_details_content( $details );
		$ingredients_content = $this->get_ingredients_content( $ingredients );
		$steps_content = $this->get_steps_content( $steps );

		$block_content = sprintf(
			'<div class="%1$s" id="%2$s">%3$s</div>',
			esc_attr( $RecipeCardClassName ),
			esc_attr( $id ),
			$recipe_card_image .
			$recipe_card_heading .
			$details_content .
			$summary_text .
			$ingredients_content .
			$steps_content
		);

		$json_ld = $this->get_json_ld( $attributes );

		return '<script type="application/ld+json">' . wp_json_encode( $json_ld ) . '</script>' . $block_content;
	}

	/**
	 * Returns the JSON-LD for a premium-recipe-card block.
	 *
	 * @param array $attributes The attributes of the premium-recipe-card block.
	 *
	 * @return array The JSON-LD representation of the premium-recipe-card block.
	 */
	protected function get_json_ld( array $attributes ) {
		$tag_list = wp_get_post_terms( $this->recipe->ID, 'post_tag', array( 'fields' => 'names' ) );
		$cat_list = wp_get_post_terms( $this->recipe->ID, 'category', array( 'fields' => 'names' ) );

		$json_ld = array(
			'@context' 		=> 'https://schema.org',
			'@type'    		=> 'Recipe',
			'author' 		=> array(
				'@type'		=> 'Person',
				'name'		=> get_the_author()
			),
			'aggregateRating' => array(
			    '@type'		  => 'AggregateRating',
			    'ratingValue' => $this->wpzoom_rating->get_rating_average( $this->recipe->ID ),
			    'reviewCount' => $this->wpzoom_rating->get_total_votes( $this->recipe->ID )
			),
			'name'			=> $this->recipe->post_title,
			'description' 	=> $this->recipe->post_excerpt,
			'image'			=> '',
			// 'video'			=> array(
			// 	'name'  	=> '',
			// 	'description' 	=> '',
			// 	'thumbnailUrl' 	=> '',
			// 	'contentUrl' 	=> '',
			// 	'embedUrl' 		=> '',
			// 	'uploadDate' 	=> '',
			// 	'duration' 		=> '',
			// ),
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

		if ( ! empty( $attributes['recipeTitle'] ) ) {
			$json_ld['name'] = $attributes['recipeTitle'];
		}

		if ( ! empty( $attributes['jsonSummary'] ) ) {
			$json_ld['description'] = $attributes['jsonSummary'];
		}

		if ( ! empty( $attributes['image'] ) && $attributes['hasImage'] === 'true' ) {
			$json_ld['image'] = $attributes['image']['url'];
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

	protected function get_details_content( array $details ) {
		$detail_items = $this->get_detail_items( $details );

		return sprintf(
			'<div class="recipe-card-details"><div class="details-items">%s</div></div>',
			$detail_items
		);
	}

	protected function get_detail_items( array $details ) {
		$output = '';
		$icon = $label = $value = $unit = '';

		foreach ( $details as $index => $detail ) {
			if ( ! empty( $detail[ 'icon' ] ) ) {
				$detail['iconSet'] = ! isset( $detail['iconSet'] ) ? 'oldicon' : $detail['iconSet'];
				$itemIconClasses = implode( ' ', array( 'detail-item-icon', $detail['icon'], $detail['iconSet'] . '-' . $detail['icon'] ) );

				$styles = array(
					'color' => $this->settings['primary_color']
				);
				$iconStyles = $this->helpers->render_styles_attributes( $styles );

				$icon = sprintf(
					'<span class="%s" icon-name="%s" iconset="%s" style="%s"></span>',
					$itemIconClasses,
					$detail['icon'],
					$detail['iconSet'],
					$iconStyles
				);
			}

			if ( ! empty( $detail[ 'jsonLabel' ] ) ) {
				$label = sprintf(
					'<span class="detail-item-label">%s</span>',
					$detail['jsonLabel']
				);
			}
			if ( ! empty( $detail[ 'jsonValue' ] ) ) {
				$value = sprintf(
					'<p class="detail-item-value">%s</p>',
					$detail['jsonValue']
				);
			}
			if ( ! empty( $detail[ 'jsonUnit' ] ) ) {
				$unit = sprintf(
					'<span class="detail-item-unit">%s</span>',
					$detail['jsonUnit']
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

	protected function get_ingredients_content( array $ingredients ) {
		$ingredient_items = $this->get_ingredient_items( $ingredients );

		$listClassNames = implode( ' ', array( 'ingredients-list', 'layout-' . @$this->settings['ingredientsLayout'] ) );

		return sprintf(
			'<div class="recipe-card-ingredients"><h3 class="ingredients-title">%s</h3><ul class="%s">%s</ul></div>',
			$this->attributes['ingredientsTitle'],
			$listClassNames,
			$ingredient_items
		);
	}

	protected function get_ingredient_items( array $ingredients ) {
		$output = '';
		$tick = $name = '';

		foreach ( $ingredients as $index => $ingredient ) {
			if ( 'newdesign' === $this->style ) {
				$styles = array(
					'border' => '2px solid ' . @$this->settings['primary_color']
				);
				$tickStyles = $this->helpers->render_styles_attributes( $styles );

				$tick = sprintf(
					'<span class="tick-circle" style="%s"></span>',
					$tickStyles
				);
			} else {
				$tick = '<span class="tick-circle"></span>';
			}

			if ( ! empty( $ingredient[ 'jsonName' ] ) ) {
				$name = sprintf(
					'<p class="ingredient-item-name">%s</p>',
					$ingredient['jsonName']
				);
			}

			$output .= sprintf(
				'<li class="ingredient-item">%s</li>',
				$tick . $name
			);
		}

		return force_balance_tags( $output );
	}

	protected function get_steps_content( array $steps ) {
		$direction_items = $this->get_direction_items( $steps );

		$listClassNames = implode( ' ', array( 'directions-list' ) );

		return sprintf(
			'<div class="recipe-card-directions"><h3 class="directions-title">%s</h3><ul class="%s">%s</ul></div>',
			$this->attributes['directionsTitle'],
			$listClassNames,
			$direction_items
		);
	}

	protected function get_direction_items( array $steps ) {
		$output = '';
		$text = '';

		foreach ( $steps as $index => $step ) {
			if ( ! empty( $step[ 'jsonText' ] ) ) {
				$text = sprintf(
					'<p class="direction-step-text">%s</p>',
					$step['jsonText']
				);
			}

			$output .= sprintf(
				'<li class="direction-step">%s</li>',
				$text
			);
		}

		return force_balance_tags( $output );
	}
}