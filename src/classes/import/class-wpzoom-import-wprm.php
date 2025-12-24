<?php
/**
 * Recipes Importer from WPRM
 *
 * @since   5.0.2
 * @package WPZOOM_Recipe_Card_Blocks
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WPZOOM_Import_Wprm' ) ) {

	/**
	 * Main WPZOOM_Import_Wprm Class.
	 *
	* @since   5.0.2
	 */
	class WPZOOM_Import_Wprm extends WPZOOM_Import {

		/**
		 * The post types.
		 *
		 * @var WPZOOM_Recipes_Converter
		 * @since   5.0.2
		 */
		private static $post_types = array( 'post', 'page' );

		/**
		 * This plugin's instance.
		 *
		 * @var WPZOOM_Import_Wprm
		 * @since   5.0.2
		 */
		private static $instance;

		/**
		 * Provides singleton instance.
		 *
		 * @since   5.0.2
		 * @return self instance
		 */
		public static function instance() {			

			if ( null === self::$instance ) {
				self::$instance = new WPZOOM_Import_Wprm();
			}

			return self::$instance;
		}

		/**
		 * The Constructor.
		 */
		public function __construct() {
		
			add_action( 'wp_ajax_wpzoom_scan_recipes', array( $this, 'search_recipes' ) );
			add_action( 'wp_ajax_wpzoom_import_recipes', array( $this, 'import_recipes' ) );
			
		}

		public function get_recipes_count() {

		}

		/**
		 * Get the total number of recipes to import.
		 *
		 * @since   5.0.2
		 */
		public function get_recipe_count() {
			$recipes_found = get_option( 'wpzoom_import_wprm_recipes', array() );
			return count( $recipes_found );
		}

		/**
		 * Search for recipes.
		 *
		 * @since   5.0.2
		 * @param	 int $page Page of recipes to add.
		 */
		public function search_recipes() {

			check_ajax_referer( 'wpzoom-recipe-scanner-nonce', 'security' );

			if ( ! current_user_can( 'edit_posts' ) ) {
				wp_send_json_error( array( 'message' => __( 'You do not have permission to perform this action.', 'recipe-card-blocks-by-wpzoom' ) ) );
			}

			$post_types = array( 'post', 'page' );
			$custom_post_types = WPZOOM_Settings::get( 'wpzoom_rcb_settings_types_recipe_post' );

			if( !empty( $custom_post_types ) ) {
				$post_types = array_merge( $post_types, $users_post_types );
			}

			$recipes = array();
			$limit = 4000;
	
			$args = array(
				'post_type'      => $post_types,
				'post_status'    => 'any',
				'orderby'        => 'date',
				'order'          => 'DESC',
				'posts_per_page' => $limit,
			);

			$query = new WP_Query( $args );

			if ( $query->have_posts() ) {
				$posts = $query->posts;
	
				foreach ( $posts as $post ) {

					$blocks = parse_blocks( $post->post_content );
	
					foreach ( $blocks as $index => $block ) {
						 if ( 'wp-recipe-maker/recipe' === $block['blockName'] ) {

							$id   = isset( $block['attrs']['id'] ) && $block['attrs']['id'] ? $block['attrs']['id'] : null;
							$name = isset( $block['attrs']['id'] ) && $block['attrs']['id'] ? get_the_title( $block['attrs']['id'] ) : 'Unknown';

							$recipes[] = array(
								'name'           => $name,
								'id'             => $post->ID,
								'wprm_recipe_id' => $id,
								'block_index'    => $index, 
								'url'            => get_edit_post_link( $post->ID ),
							);
						}
					}
				}
			}

			//$found_recipes = 0 === $page ? array() : get_option( 'wpzoom_import_wprm_recipes', array() );
			$found_recipes = array();
			$found_recipes = array_merge( $found_recipes, $recipes );
	
			//update_option( 'wpzoom_import_wprm_recipes', $found_recipes, false );
	
			$search_result = array(
				'recipes' => $found_recipes,
				'amount'  => count( $found_recipes ),
			);

			wp_send_json_success( $search_result );
		
		}

		public function import_recipes() {

			check_ajax_referer( 'wpzoom-recipe-scanner-nonce', 'security' );

			if ( ! current_user_can( 'edit_posts' ) ) {
				wp_send_json_error( array( 'message' => __( 'You do not have permission to perform this action.', 'recipe-card-blocks-by-wpzoom' ) ) );
			}

			$recipes_data = isset( $_POST['recipes'] ) && is_array( $_POST['recipes'] ) ? $_POST['recipes'] : array();

			$imported_recipes = array();

			foreach( $recipes_data as $recipe_data ) {

				$imported_recipes_data = array();

				$recipe_post_id = isset( $recipe_data['post_id'] ) ? absint( $recipe_data['post_id'] ) : 0;
				$recipe_wprm_id = isset( $recipe_data['recipe_id'] ) ? absint( $recipe_data['recipe_id'] ) : 0;
				$recipe_block_i = isset( $recipe_data['block_index'] ) ? absint( $recipe_data['block_index'] ) : 0;

				$this->replace_recipe( $recipe_post_id, $recipe_wprm_id, $recipe_block_i );

				$imported_recipes[] = array(
					'wprm_id' => $recipe_wprm_id
				);

				//Add imported data for backup
				$imported_recipes_data = array(
					'import_status' => 'imported',
					'wprm_id'      => $recipe_wprm_id,
					'block_index'  => $recipe_block_i
				);
				update_post_meta( $recipe_post_id, 'wpzoom_rcb_imported_wprm_data', $imported_recipes_data );

			}

			$import_result = array(
				'amount'  => count( $imported_recipes )
			);


			wp_send_json_success( $import_result );



		}

		/**
		 * Replace Recipe.
		 *
		 * @since   5.0.2
		 * @param	 int $page Page of recipes to add.
		 */
		public function replace_recipe( $post_id, $wprm_recipe_id, $block_index ) {

			$recipe_block = array(
				'blockName'    => 'wpzoom-recipe-card/block-recipe-card',
				'innerBlocks'  => array(),
				'innerHTML'    => '',
				'innerContent' => array(),
			);

			$atts = array();
			
			$recipe      = get_post( $wprm_recipe_id );
			$recipe_data = get_post_custom( $wprm_recipe_id );

			//Get recipe image ID
			$image_id    = get_post_thumbnail_id( $wprm_recipe_id );
			$image_url   = wp_get_attachment_image_url( $image_id, 'wpzoom-rcb-block-header' );
			$image_alt   = get_post_meta( $image_id, '_wp_attachment_image_alt', TRUE );
			$image_title = get_the_title( $image_id );
			$wp_image_sizes = array(
				'thumbnail',
				'medium',
				'wpzoom-rcb-block-header',
				'wpzoom-rcb-block-header-square',
				'wpzoom-rcb-block-step-image',
				'full'
			);
			$image_sizes = array();
			foreach( $wp_image_sizes as $image_size ) {

				$image_src = wp_get_attachment_image_src( $image_id, $image_size );

				$height = isset( $image_src[2] ) ? $image_src[2] : '';
				$width  = isset( $image_src[1] ) ? $image_src[1] : '';
				$url    = isset( $image_src[0] ) ? $image_src[0] : '';

				$orientation = $width >= $height ? 'landscape' : 'portrait';

				$image_sizes[ $image_size ] = array(
					'height'      => $height,
					'width'       => $width,
					'url'         => $url,
					'orientation' => $orientation
				);
			}

			// Recipe Image.
			if ( ! empty( $image_id ) ) {

				$atts['image']['id']    = $image_id;
				$atts['image']['url']   = $image_url;
				$atts['image']['alt']   = $image_alt;
				$atts['image']['alt']   = $image_alt;
				$atts['image']['title'] = $image_title;
				$atts['image']['sizes'] = $image_sizes;
				
				$atts['hasImage'] = true;

			}

			// Recipe Title.
			$recipe_title = get_the_title( $wprm_recipe_id );
			$atts['recipeTitle'] = $recipe_title;

			//Recipe Summary.
			$recipe_summary = $recipe->post_content;
			$atts['summary']     = $recipe_summary;
			$atts['jsonSummary'] = wp_strip_all_tags( $recipe_summary );

			//Course
			$courses = get_the_terms( $wprm_recipe_id, 'wprm_course' );
			if( !empty(  $courses ) ) {
				foreach( $courses as $course ) {
					$atts['course'][] = $course->name;
				}
			}

			//Cousine
			$cousines = get_the_terms( $wprm_recipe_id, 'wprm_cuisine' );
			if( !empty(  $cousines ) ) {
				foreach( $cousines as $cousine ) {
					$atts['cuisine'][] = $cousine->name;
				}
			}

			//Keywords
			$keywords = get_the_terms( $wprm_recipe_id, 'wprm_keyword' );
			if( !empty(  $keywords ) ) {
				foreach( $keywords as $keyword ) {
					$atts['keywords'][] = $keyword->name;
				}
			}

			$atts['settings'][0] = array(
				'primary_color'        => WPZOOM_Settings::get( 'wpzoom_rcb_settings_primary_color' ),
				'icon_details_color'   => '#6d767f',
				'show_image_caption'   => false,
				'hide_header_image'    => false,
				'print_btn'            => WPZOOM_Settings::get( 'wpzoom_rcb_settings_display_print' ) === '1',
				'pin_btn'              => WPZOOM_Settings::get( 'wpzoom_rcb_settings_display_pin' ) === '1',
				'pin_has_custom_image' => false,
				'pin_custom_image'     => array(),
				'pin_custom_text'      => '',
				'custom_author_name'   => WPZOOM_Settings::get( 'wpzoom_rcb_settings_author_custom_name' ),
				'displayAuthor'        => WPZOOM_Settings::get( 'wpzoom_rcb_settings_display_author' ) === '1',
				'headerAlign'          => WPZOOM_Settings::get( 'wpzoom_rcb_settings_heading_content_align' ),
				'ingredientsLayout'    => '1-column',
				'adjustableServings'   => true,
				'displayEquipment'     => WPZOOM_Settings::get( 'wpzoom_rcb_settings_display_equipment' ) === '1',
				'equipmentLocation'    => WPZOOM_Settings::get( 'wpzoom_rcb_settings_equipment_location' ),
				'displayUnitSystem'    => WPZOOM_Settings::get( 'wpzoom_rcb_settings_display_unit_system' ) === '1',
				'defaultUnitSystem'    => WPZOOM_Settings::get( 'wpzoom_rcb_settings_default_unit_system' ),
			);


			//Recipe Video.
			$video_embed = isset( $recipe_data['wprm_video_embed'][0] ) ? $recipe_data['wprm_video_embed'][0] : null;
			$video_id    = isset( $recipe_data['wprm_video_id'][0] ) ? $recipe_data['wprm_video_id'][0] : null;

			if( !empty( $video_embed ) || !empty( $video_id ) ) {
				$atts['hasVideo'] = true;
				if( !empty( $video_embed ) && isset( $video_embed ) ) {
					$atts['video']['type'] = 'embed';
					$atts['video']['url'] = $video_embed;
				}
				if( !empty( $video_id ) && isset( $video_id ) ) {
					$atts['video']['type'] = 'self-hosted';
					$atts['video']['id']   = $video_id;
					$atts['video']['url']  = wp_get_attachment_url( $video_id );
					$atts['video']['settings'] = array(
						'autoplay' => '',
						'loop'     => '',
						'muted'    => '',
						'controls' => true
					);
					
				}
			}

			//Recipe Details
			$recipe_servings           = isset( $recipe_data['wprm_servings'][0] ) ? $recipe_data['wprm_servings'][0] : null;
			$recipe_servings_unit      = isset( $recipe_data['wprm_servings_unit'][0] ) ? $recipe_data['wprm_servings_unit'][0] : null;
			$recipe_prep_time          = isset( $recipe_data['wprm_prep_time'][0] ) ? $recipe_data['wprm_prep_time'][0] : null;
			$recipe_cook_time          = isset( $recipe_data['wprm_cook_time'][0] ) ? $recipe_data['wprm_cook_time'][0] : null;
			$recipe_total_time         = isset( $recipe_data['wprm_total_time'][0] ) ? $recipe_data['wprm_total_time'][0] : null;
			$recipe_nutrition_calories = isset( $recipe_data['wprm_nutrition_calories'][0] ) ? $recipe_data['wprm_nutrition_calories'][0] : null;

			$recipe_custom_time       = isset( $recipe_data['wprm_custom_time'][0] ) ? $recipe_data['wprm_custom_time'][0] : null;
			$recipe_custom_time_label = isset( $recipe_data['wprm_custom_time_label'][0] ) ? $recipe_data['wprm_custom_time_label'][0] : null;
			
			if( !empty( $recipe_servings ) ) {
				$atts['settings'][0]['displayServings'] = true;
			}
			if( !empty( $recipe_prep_time ) ) {
				$atts['settings'][0]['displayPrepTime'] = true;
			}
			if( !empty( $recipe_cook_time ) ) {
				$atts['settings'][0]['displayCookingTime'] = true;
			}
			if( !empty( $recipe_total_time ) ) {
				$atts['settings'][0]['displayTotalTime'] = true;
			}
			if( !empty( $recipe_nutrition_calories ) ) {
				$atts['settings'][0]['displayCalories'] = true;
			}

			$atts['details'] = array(
				array(
					'id'      => uniqid( 'detail-item-' ),
					'iconSet' => 'oldicon',
					'icon'    => 'food',
					'label'   => esc_html__( 'Servings', 'recipe-card-blocks-by-wpzoom' ),
					'unit'    => $recipe_servings_unit,
					'value'   => $recipe_servings
				),
				array(
					'id'      => uniqid( 'detail-item-' ),
					'iconSet' => 'oldicon',
					'icon'    => 'clock',
					'label'   => esc_html__( 'Prep time', 'recipe-card-blocks-by-wpzoom' ),
					'unit'    => 'minutes',
					'value'   => $recipe_prep_time
				),
				array(
					'id'      => uniqid( 'detail-item-' ),
					'iconSet' => 'foodicons',
					'icon'    => 'cooking-food-in-a-hot-casserole',
					'label'   => esc_html__( 'Cooking time', 'recipe-card-blocks-by-wpzoom' ),
					'unit'    => 'minutes',
					'value'   => $recipe_cook_time
				),
				array(
					'id'      => uniqid( 'detail-item-' ),
					'iconSet' => 'foodicons',
					'icon'    => 'fire-flames',
					'label'   => esc_html__( 'Calories', 'recipe-card-blocks-by-wpzoom' ),
					'unit'    => 'kcal',
					'value'   => $recipe_nutrition_calories
				),
				array(
					'id'      => uniqid( 'detail-item-' ),
					'iconSet' => 'fa',
					'_prefix' => 'far',
					'icon'    => 'clock',
				),
				array(
					'id'      => uniqid( 'detail-item-' ),
					'iconSet' => 'oldicon',
					'icon'    => 'chef-cooking',
				),
				array(
					'id'      => uniqid( 'detail-item-' ),
					'iconSet' => 'oldicon',
					'icon'    => 'food-1',
				),
				array(
					'id'      => uniqid( 'detail-item-' ),
					'iconSet' => 'fa',
					'_prefix' => 'fas',
					'icon'    => 'sort-amount-down',
				),
				array(
					'id'      => uniqid( 'detail-item-' ),
					'iconSet' => 'fa',
					'_prefix' => 'far',
					'icon'    => 'clock',
					'label'   => esc_html__( 'Total time', 'recipe-card-blocks-by-wpzoom' ),
					'unit'    => 'minutes',
					'value'   => $recipe_total_time
				)
			);

			if( !empty( $recipe_custom_time ) ) {
				
				//Is it resting time
				if( 'Resting Time' == $recipe_custom_time_label || 'Resting time' == $recipe_custom_time_label || 'resting time' == $recipe_custom_time_label || 'resting Time' == $recipe_custom_time_label ) {
					$atts['details'][4] = array(
						'id'      => uniqid( 'detail-item-' ),
						'iconSet' => 'fa',
						'_prefix' => 'far',
						'icon'    => 'clock',
						'isRestingTimeField' => true, 
						'label'     => $recipe_custom_time_label,
						'jsonLabel' => $recipe_custom_time_label,
						'value'     => $recipe_custom_time,
						'jsonValue' => $recipe_custom_time,
						'unit'      => 'minutes',
						'jsonUnit' => 'minutes'
					);
				}
				//Baking time
				if( 'Baking Time' == $recipe_custom_time_label || 'Baking time' == $recipe_custom_time_label || 'baking time' == $recipe_custom_time_label || 'baking Time' == $recipe_custom_time_label ) {
					$atts['details'][5] = array(
						'id'      => uniqid( 'detail-item-' ),
						'iconSet' => 'oldicon',
						'icon'    => 'chef-cooking',
						'label'     => $recipe_custom_time_label,
						'jsonLabel' => $recipe_custom_time_label,
						'value'     => $recipe_custom_time,
						'jsonValue' => $recipe_custom_time,
						'unit'      => 'minutes',
						'jsonUnit' => 'minutes'
					);
				}

			}

			//Recipe Ingredients
			$ingredients = array();
			$recipe_ingredients = isset( $recipe_data['wprm_ingredients'][0] ) ? unserialize( $recipe_data['wprm_ingredients'][0] ) : array();
			
			if( !empty( $recipe_ingredients ) ) {
				foreach( $recipe_ingredients as $recipe_ingredient ) {
					
					if( isset( $recipe_ingredient['name'] ) && ! empty( $recipe_ingredient['name'] ) ) {
						$ingredients[] = array(
							'id'       => uniqid( 'ingredient-item-' ),
							'name'     => array( $recipe_ingredient['name'] ),
							'jsonName' => $recipe_ingredient['name'],
							'isGroup'  => true
						);
					}
					if( isset( $recipe_ingredient['ingredients'] ) && ! empty( $recipe_ingredient['ingredients'] ) ) {
						
						$recipe_ingredients_data = $recipe_ingredient['ingredients'];
						
						foreach( $recipe_ingredients_data as $ingredient ) {

							$ingredient_note = array();

							// Build the amount and unit prefix
							$amount = isset( $ingredient['amount'] ) ? trim( $ingredient['amount'] ) : '';
							$unit   = isset( $ingredient['unit'] ) ? trim( $ingredient['unit'] ) : '';
							$amount_unit_prefix = '';

							if ( ! empty( $amount ) || ! empty( $unit ) ) {
								$amount_unit_prefix = trim( $amount . ' ' . $unit ) . ' ';
							}

							//Check if there is no global link
							$globalLink = class_exists( 'WPRMP_Ingredient_Links' ) ? WPRMP_Ingredient_Links::get_ingredient_link( $ingredient['id'] ) : array();

							//Check if the link is not set here
							if( isset( $ingredient['link']['url'] ) && !empty( $ingredient['link']['url'] ) ) {
								$ingredient_name = array(
									'type'  => 'a',
									'props' => array(
										'href'      => $ingredient['link']['url'],
										'data-type' => 'URL',
										'data-id'   => $ingredient['link']['url'],
										'children'  => array(
											$ingredient['name']
										)
									)

								);
							}
							elseif( isset( $globalLink['url'] ) && !empty( $globalLink['url'] ) ) {
								$ingredient_name = array(
									'type'  => 'a',
									'props' => array(
										'href'      => $globalLink['url'],
										'data-type' => 'URL',
										'data-id'   => $globalLink['url'],
										'children'  => array(
											$ingredient['name']
										)
									)

								);
							}
							else {
								$ingredient_name = $ingredient['name'];
							}

							//Check if notes exist and add them
							if( isset( $ingredient['notes'] ) && !empty( $ingredient['notes'] ) ) {
								$ingredient_note = array(
									'type'  => 'em',
									'props' => array(
										'children'  => array(
											' ' . $ingredient['notes']
										)
									)
								);
							}

							// Build the full ingredient name with amount, unit, name and notes
							$full_json_name = trim( $amount_unit_prefix . $ingredient['name'] );

							$ingredients[] = array(
								'id'       => uniqid( 'ingredient-item-' ),
								'name'     => array(
									$amount_unit_prefix,
									$ingredient_name,
									$ingredient_note
								),
								'parse'    => array(
									'amount' => $amount,
									'unit'   => $unit
								),
								'jsonName' => $full_json_name,
								'isGroup'  => false
							);	
						}
					
					}

				}
			}

			$atts['ingredients'] = $ingredients;

			//Recipe Steps
			$steps = array();
			$recipe_steps = isset( $recipe_data['wprm_instructions'][0] ) ? unserialize( $recipe_data['wprm_instructions'][0] ) : array();

			if( !empty( $recipe_steps ) ) {
				foreach( $recipe_steps as $recipe_step ) {
					
					//Check if it is not group name
					if( isset( $recipe_step['name'] ) && ! empty( $recipe_step['name'] ) ) {
						$steps[] = array(
							'id'       => uniqid( 'direction-step-' ),
							'text'     => array( wp_strip_all_tags( $recipe_step['name'] ) ),
							'jsonText' => wp_strip_all_tags( $recipe_step['name'] ),
							'isGroup'  => true
						);
					}

					if( isset( $recipe_step['instructions'] ) && ! empty( $recipe_step['instructions'] ) ) {
						$recipe_steps_data = $recipe_step['instructions'];
						foreach( $recipe_steps_data as $step ) {
							$images = array();
							if( isset( $step['image'] ) && ! empty( $step['image'] ) ) {
								$images = array(
									'images' => array(
										array(
											'alt'     => '',
											'id'      => $step['image'],
											'link'    => get_attachment_link( $step['image'] ),
											'url'     => wp_get_attachment_image_url( $step['image'], 'medium_large' ),
											'fullUrl' => wp_get_attachment_image_url( $step['image'], 'full' ),
										)
									),
									'ids' => array(
										$step['image']
									)
								);
							}
							$steps[] = array(
								'id'       => uniqid( 'direction-step-' ),
								'text'     => array( wp_strip_all_tags( $step['text'] ) ),
								'jsonText' => wp_strip_all_tags( $step['text'] ),
								'isGroup'  => false,
								'gallery'  => $images
							);	
						}
					}

				}
			}

			$atts['steps'] = $steps;

			//Recipe Equipment
			$equipment = array();
			$recipe_equipment = isset( $recipe_data['wprm_equipment'][0] ) ? unserialize( $recipe_data['wprm_equipment'][0] ) : array();
			
			if( !empty( $recipe_equipment ) ) {
				foreach( $recipe_equipment as $recipe_equipment_item ) {
					
					$eq_term_meta  = get_term_meta( $recipe_equipment_item['id'] );

					$height = $width = $url = $orientation = $eq_term_image_id = '';

					$eq_term_image_id   = isset( $eq_term_meta['wprmp_equipment_image_id'][0] ) ? $eq_term_meta['wprmp_equipment_image_id'][0] : null;
					
					if( $eq_term_image_id ) {
						
						$eq_term_image_src = wp_get_attachment_image_src( $eq_term_image_id, 'medium', false );

						$height = isset( $eq_term_image_src[2] ) ? $eq_term_image_src[2] : '';
						$width  = isset( $eq_term_image_src[1] ) ? $eq_term_image_src[1] : '';
						$url    = isset( $eq_term_image_src[0] ) ? $eq_term_image_src[0] : '';
		
						$orientation = $width >= $height ? 'landscape' : 'portrait';
					
					}

					$name = isset( $recipe_equipment_item['amount'] ) && ! empty( $recipe_equipment_item['amount'] ) ? $recipe_equipment_item['name'] . ' (' . $recipe_equipment_item['amount'] . ')' : $recipe_equipment_item['name'];
					$equipment[] = array(
						'id'   => uniqid( 'equipment-item-' ),
						'name' =>  $name,
						'link' => array(
							'url'   => isset( $eq_term_meta['wprmp_equipment_link'][0] ) ? esc_url( $eq_term_meta['wprmp_equipment_link'][0] ) : null,
							'label' => esc_html__( 'Buy Now', 'recipe-card-blocks-by-wpzoom' ), 
							'newWindow' => '',
							'noFollow'  => isset( $eq_term_meta['wprmp_equipment_link_nofollow'][0] ) ? true : false,
						),
						'image' => array(
							'id'          => $eq_term_image_id,
							'url'         => $url,
							'width'       => $width,
							'height'      => $height,
							'orientation' => $orientation
						),
						'favoriteId' => -1,
						'jsonName'   => $recipe_equipment_item['name']
					);
				}
			}

			// Display Equipment
			if( !empty( $equipment ) ) {
				$atts['settings'][0]['displayEquipment'] = true;
			}
			$atts['equipment'] = $equipment;

			//Recipe Notes.
			$notes = isset( $recipe_data['wprm_notes'][0] ) ? trim( $recipe_data['wprm_notes'][0] ) : '';

			$notes = str_ireplace( '<p><ul>', '', $notes );
			$notes = str_ireplace( '</ul></p>', '', $notes );

			$notes = str_ireplace( '<p><ol>', '', $notes );
			$notes = str_ireplace( '</ol></p>', '', $notes );

			$notes = str_ireplace( '<ul>', '', $notes );
			$notes = str_ireplace( '</ul>', '', $notes );

			$notes = str_ireplace( '<ol>', '', $notes );
			$notes = str_ireplace( '</ol>', '', $notes );

			$notes = str_ireplace( '<p>', '<li>', $notes );
			$notes = str_ireplace( '</p>', '</li>', $notes );

			$notes = trim( preg_replace('/\s+/', ' ', $notes ) );

			$atts['notes'] = $notes;

			//Nutrion
			$nutrition_mapping = array(
				'serving-size'        => 'serving_size',
				'calories'            => 'calories',
				'total-carbohydrate'  => 'carbohydrates',
				'protein'             => 'protein',
				'total-fat'           => 'fat',
				'saturated-fat'       => 'saturated_fat',
				'polyunsaturated-fat' => 'polyunsaturated_fat',
				'monounsaturated-fat' => 'monounsaturated_fat',
				'trans-fat'           => 'trans_fat',
				'cholesterol'         => 'cholesterol',
				'sodium'              => 'sodium',
				'potassium'           => 'potassium',
				'dietary-fiber'       => 'fiber',
				'sugars'              => 'sugar',
				'vitamin-a'           => 'vitamin_a',
				'vitamin-c'           => 'vitamin_c',
				'calcium'             => 'calcium',
				'iron'                => 'iron',
				'net-carbs'           => 'net_carbs',
				'serving-size-unit'   => 'serving_unit'
			);

			$nutrition = array();

			foreach( $nutrition_mapping as $key => $nutrition_value ) {
				$nutrition[ $key ] = isset( $recipe_data[ 'wprm_nutrition_' . $nutrition_value ][0] ) ? $recipe_data[ 'wprm_nutrition_' . $nutrition_value ][0] : '';
			}
			if( !empty( $nutrition ) ) {
				$atts['settings'][0]['displayNutrition'] = true;
			}

			$atts['nutrition'] = $nutrition;

			//Add attributes to the recipe block
			$recipe_block['attrs'] = $atts;

			// Migrate ratings.
			global $wpdb;
			
			$table_name = $wpdb->prefix . 'wprm_ratings';
			$query_ratings = $wpdb->prepare( "SELECT * FROM $table_name WHERE recipe_id = %d OR post_id = %d", $wprm_recipe_id, $wprm_recipe_id );
			$ratings = $wpdb->get_results( $query_ratings );			

			foreach ( $ratings as $rating ) {
				
				if ( '1' === $rating->approved ) {
				
					$comment_id = intval( $rating->comment_id );
					$user_id = intval( $rating->user_id );
					$rating_value = intval( $rating->rating );

					// Only use recipe ID if there is no comment ID.
					$recipe_id = 0 < $comment_id ? 0 : $post_id;

					$wpzoom_recipe_rating = array(
						'date'       => $rating->rate_date,
						'recipe_id'  => $recipe_id,
						'post_id'    => $recipe_id,
						'comment_id' => $comment_id,
						'user_id'    => $user_id,
						'ip'         => $rating->ip,
						'rating'     => $rating_value,
					);

					WPZOOM_Rating_DB::add_or_update_rating( $wpzoom_recipe_rating );
				}
			}

			$post = get_post( $post_id );
			$blocks = parse_blocks( $post->post_content );

			foreach( $blocks as $key => $block ) {
				if ( 'wp-recipe-maker/recipe' === $block['blockName'] ) {
					$blocks[ $key ] = $recipe_block;				
				}
			}

			$content = serialize_blocks( $blocks );

			$update_content = array(
				'ID'           => $post_id,
				'post_content' => $content,
			);
			
			wp_update_post( $update_content );

		}

	}

}

WPZOOM_Import_Wprm::instance();