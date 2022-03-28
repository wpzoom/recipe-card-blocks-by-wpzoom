<?php
/**
 * Recipes Post Saver
 *
 * @since   2.8.2
 * @package WPZOOM_Recipe_Card_Blocks
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WPZOOM_Recipe_Post_Saver' ) ) {
	/**
	 * Main WPZOOM_Recipe_Post_Saver Class.
	 *
	 * @since 2.8.2
	 */
	class WPZOOM_Recipe_Post_Saver {

		/**
		 * This plugin's instance.
		 *
		 * @var WPZOOM_Recipe_Post_Saver
		 * @since 2.8.2
		 */
		private static $instance;

		/**
		 * Provides singleton instance.
		 *
		 * @since 2.8.2
		 * @return self instance
		 */
		public static function instance() {			

			if ( null === self::$instance ) {
				self::$instance = new WPZOOM_Recipe_Post_Saver();
			}

			return self::$instance;
		}

		/**
		 * The Constructor.
		 */
		public function __construct() {
		
			add_action( 'save_post', array( __CLASS__, 'duplicate_recipe_to_custom_post' ), 10, 2 );
			add_action( 'save_post', array( __CLASS__, 'update_parent_recipe_card' ), 10, 2 );
			add_action( 'save_post', array( __CLASS__, 'check_cpt_block' ), 10, 2 );
			
		}

		/**
		 * Check if the page/post content has the CPT block.
		 *
		 * @since    1.0.0
		 * @param    array $post_id post ID with recipe card block in content.
		 */
		public static function check_cpt_block( $post_id, $post ) {

			// If this is a revision, get real post
			$revision_parent = wp_is_post_revision( $post );
			if ( $revision_parent ) {
				$post = get_post( $revision_parent );
			}	

			if( 'post' !== $post->post_type && 'page' !== $post->post_type ) {
				return;
			}

			//Check it's not an auto save routine
			if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) 
			return;

			//Perform permission checks! For example:
			if ( ! current_user_can( 'edit_post', $post_id ) ) 
			return;

			//Don't do anything if there is no recipe card
			if( ! has_block( 'wpzoom-recipe-card/recipe-block-from-posts', $post ) ) {
				//return;
			}

			$existing_recipes = get_posts(
				array(  
					'post_type'   => 'wpzoom_rcb',
					'numberposts' => -1
				)
			);

			foreach( $existing_recipes as $key => $recipe ) {
				$postsIds = get_post_meta( $recipe->ID, '_wpzoom_rcb_used_in', true );
				if( !empty( $postsIds ) ) {
					$postsIds = explode( ",", $postsIds );
					if ( ( $searchedId = array_search( $post->ID, $postsIds ) ) !== false ) {
						unset( $postsIds[ $searchedId ] );
					}
					$postsIds = implode( ',', $postsIds );
				}
				update_post_meta( $recipe->ID, '_wpzoom_rcb_used_in', $postsIds );
			}

			$gutenberg_matches = array();
			$gutenberg_patern = '/<!--\s+wp:(wpzoom\-recipe\-card\/recipe\-block\-from\-posts)(\s+(\{.*?\}))?\s+(\/)?-->/';
			preg_match_all( $gutenberg_patern, $post->post_content, $matches );

			if( isset( $matches[0] ) ) {
				$block = $matches[0];
			}
			if ( isset( $matches[3] ) ) {
				foreach ( $matches[3] as $block_attributes_json ) {
					if ( ! empty( $block_attributes_json ) ) {
						$attributes = json_decode( $block_attributes_json, true );
					}
				}
			}

			if( isset( $attributes['postId'] ) ) {

				$recipe_id = $attributes['postId'];

				$used_in = get_post_meta( $recipe_id, '_wpzoom_rcb_used_in', true );
				if( !empty( $used_in ) ) {
					$postsArr = explode( ",", $used_in );
				}
				$postsArr[] = $post->ID;
				$postsArr = array_unique( $postsArr );
				$meta_value = implode( ',', $postsArr );

				//print_r( array( $postsArr ) );
				update_post_meta( $recipe_id, '_wpzoom_rcb_used_in', $meta_value );
				
			}
			else {
				return;
			}
		
		}

		/**
		 * Duplicate Recipe Block to the Recipe Custom Post.
		 *
		 * @since    1.0.0
		 * @param    array $post_id post ID with recipe card block in content.
		 */
		public static function duplicate_recipe_to_custom_post( $post_id, $post ) {

			// If this is a revision, get real post
			$revision_parent = wp_is_post_revision( $post );
			if ( $revision_parent ) {
				$post = get_post( $revision_parent );
			}	

			if( 'post' !== $post->post_type && 'page' !== $post->post_type ) {
				return;
			}
			
			//If moved to trash
			if( 'trash' === get_post_status( $post_id ) ) {
				return;
			}

			//Check it's not an auto save routine
			if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) 
			return;

			//Perform permission checks! For example:
			if ( ! current_user_can('edit_post', $post_id) ) 
			return;

			//Don't do anything if there is no recipe card
			if( ! has_block( 'wpzoom-recipe-card/block-recipe-card', $post ) ) {
				return;
			}

			$recipes = array();

			$gutenberg_matches = array();
			$gutenberg_patern = '/<!--\s+wp:(wpzoom\-recipe\-card\/block\-recipe\-card)(\s+(\{.*?\}))?\s+(\/)?-->/';
			preg_match_all( $gutenberg_patern, $post->post_content, $matches );

			if( isset( $matches[0] ) ) {
				$block = $matches[0];
			}
			if ( isset( $matches[3] ) ) {
				foreach ( $matches[3] as $block_attributes_json ) {
					if ( ! empty( $block_attributes_json ) ) {
						$attributes = json_decode( $block_attributes_json, true );
					}
				}
			}
				
			$name = isset( $attributes['recipeTitle'] ) && $attributes['recipeTitle'] ? $attributes['recipeTitle'] : esc_html__( 'Unknown', 'recipe-card-blocks-by-wpzoom' );  
			$recipes[] = array(
				'name'     => $name,
				'recipe'   => $block[0],
				'url'      => get_edit_post_link( $post->ID ),
				'parentId' => $post->ID
			);
			
			//check if the recipe post already exists
			if( !empty( $recipes ) ) {
				foreach( $recipes as $recipe ) {
					// unhook this function so it doesn't loop infinitely
					remove_action( 'save_post', array( __CLASS__, 'duplicate_recipe_to_custom_post' ), 10 );
					if( self::recipe_post_exists( $post->ID ) ) {
						self::update_recipe_post( $recipe );
					}
					else {
						self::create_recipe_post( $recipe );
					}
					add_action( 'save_post', array( __CLASS__, 'duplicate_recipe_to_custom_post' ), 10, 2 );
				}
			}
			else {
				return;
			}
		}

	
		/**
		 * Sincronyze the content of the parent and CPT recipe card.
		 *
		 * @since    1.0.0
		 * @param    array $post_id post ID with recipe card block in content.
		 */
		public static function update_parent_recipe_card( $post_id, $post ) {

			// Don't create any post if option is off
			if ( '1' !== WPZOOM_Settings::get( 'wpzoom_rcb_settings_synchronize_recipe_post' ) ) {
				return null;
			}

			// If this is a revision, get real post
			$revision_parent = wp_is_post_revision( $post );
			if ( $revision_parent ) {
				$post = get_post( $revision_parent );
			}	

			if( 'wpzoom_rcb' !== $post->post_type ) {
				return;
			}
			
			if( 'trash' === get_post_status( $post_id ) ) {
				return;
			}

			//Check it's not an auto save routine
			if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) 
			return;

			//Perform permission checks! For example:
			if ( ! current_user_can('edit_post', $post_id) ) 
			return;

			//Don't do anything if there is no recipe card
			if( ! has_block( 'wpzoom-recipe-card/block-recipe-card', $post ) ) {
				return;
			}

			//Check if there is any parent post/page
			$parent_id = get_post_meta( $post_id, '_wpzoom_rcb_parent_post_id', true );
			if( empty( $parent_id ) || $parent_id === $post_id ) {
				return;
			}

			//Get parent content
			$parent_post = get_post( $parent_id );
			$parent_content = $parent_post->post_content;
			
			//print_r( $parent_content );

			$gutenberg_matches = array();
			$recipe_patern = '/<!--\s+wp:(wpzoom\-recipe\-card\/block\-recipe\-card)(\s+(\{.*?\}))?\s+(\/)?-->/';
			preg_match_all( $recipe_patern, $post->post_content, $matches );
			preg_match_all( $recipe_patern, $parent_content, $parent_matches );

			$recipeBlock = isset( $matches[0] ) ? $matches[0] : '';
			//$parentRecipeBlock = isset( $parent_matches[0] ) ? $parent_matches[0] : '';

			$update_parent_content = preg_replace( $recipe_patern, $recipeBlock[0], $parent_content );

			$updated_parent_args = array(
				'ID' => $parent_id,
				'post_content' => $update_parent_content,
			);

			remove_action( 'save_post', array( __CLASS__, 'update_parent_recipe_card' ), 10 );
			
			wp_update_post( $updated_parent_args );
			
			add_action( 'save_post', array( __CLASS__, 'update_parent_recipe_card' ), 10, 2 );				

		}

		/**
		 * Create a new custom post recipe.
		 *
		 * @since    1.0.0
		 * @param		 array $recipe Recipe fields to save.
		 */
		public static function create_recipe_post( $recipe, $scan = false ) {

			// Don't create any post if option is off	
			if ( !$scan && '1' !== WPZOOM_Settings::get('wpzoom_rcb_settings_create_recipe_post') ) {
				return null;
			}

			if( empty( $recipe ) ) {
				return null;
			}

			$post = array(
				'post_title'   => WPZOOM_Helpers::deserialize_block_attributes( $recipe['name'] ),
				'post_name'    => WPZOOM_Helpers::deserialize_block_attributes( $recipe['name'] ),
				'post_content' => $recipe['recipe'],
				'post_type'    => 'wpzoom_rcb',
				'post_status'  => 'publish',
			);
			
			
			$recipe_id = wp_insert_post( $post );
			update_post_meta( $recipe_id, '_wpzoom_rcb_parent_post_id', $recipe['parentId'] );
			update_post_meta( $recipe_id, '_wpzoom_rcb_has_parent', true );

			return $recipe_id;

		}

		/**
		 * Update the custom post recipe.
		 *
		 * @since    1.0.0
		 * @param		 array $recipe Recipe fields to save.
		 */
		public static function update_recipe_post( $recipe ) {

			// Don't update recipe post if option is off
			if ( '1' !== WPZOOM_Settings::get('wpzoom_rcb_settings_update_recipe_post') ) {
				return null;
			}

			if( empty( $recipe ) ) {
				return null;
			}

			$args = array(
				'meta_query' => array(
					array(
						'key' => '_wpzoom_rcb_parent_post_id',
						'value' => $recipe['parentId']
					)
				),
				'post_type' => 'wpzoom_rcb',
				'posts_per_page' => 1
			);
			
			$posts = get_posts( $args );

			// check results ##
			if ( ! $posts || is_wp_error( $posts ) ) return false;

			$recipe_post = array(
				'ID' => $posts[0]->ID,
				'post_title'   => WPZOOM_Helpers::deserialize_block_attributes( $recipe['name'] ),
				'post_name'    => WPZOOM_Helpers::deserialize_block_attributes( $recipe['name'] ),
				'post_content' => $recipe['recipe'],
			);
			
			$recipe_id = wp_update_post( $recipe_post );
			update_post_meta( $recipe_id, '_wpzoom_rcb_has_parent', true );
			//update_post_meta( $recipe_id, '_wpzoom_rcb_parent_post_id', $recipe['parentId'] );

			return $recipe_id;

		}

		public static function recipe_post_exists( $post_id ) {

			if( empty( $post_id ) ) {
				return false;
			}

			$recipes_ids = array();
			$existing_recipes = get_posts(
				array(  
					'post_type'   => 'wpzoom_rcb',
					'numberposts' => -1
				)
			);

			if( empty( $existing_recipes ) ) {
				return false;
			}

			foreach( $existing_recipes as $key => $recipe ) {
				$parent_id = get_post_meta( $recipe->ID, '_wpzoom_rcb_parent_post_id', true );
				$recipes_ids[] = $parent_id;
			}
			if( in_array( $post_id, $recipes_ids ) ) {
				return true;
			}

		}
	}
}

WPZOOM_Recipe_Post_Saver::instance();
