<?php
/**
 * Recipes Edit 
 *
 * @since 3.2.13
 * @package WPZOOM_Recipe_Card_Blocks
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WPZOOM_Recipe_Edit_Link' ) ) {
	/**
	 * Main WPZOOM_Recipe_Edit_Link Class.
	 *
	 * @since 3.2.13
	 */
	class WPZOOM_Recipe_Edit_Link {

		/**
		 * This class instance.
		 *
		 * @var WPZOOM_Recipe_Edit_Link
		 * @since 3.2.13
		 */
		private static $instance;

		/**
		 * Provides singleton instance.
		 *
		 * @since 3.2.13
		 * @return self instance
		 */
		public static function instance() {			

			if ( null === self::$instance ) {
				self::$instance = new WPZOOM_Recipe_Edit_Link();
			}

			return self::$instance;
		}

		/**
		 * The Constructor.
		 * 
		 * @since 3.2.13
		 */
		public function __construct() {
			
			add_action( 'admin_bar_menu', array( __CLASS__, 'add_edit_button' ), 99 );
			add_filter( 'post_row_actions', array( __CLASS__, 'add_edit_recipe_button_admin_row_actions' ), 99, 2 );
			add_filter( 'page_row_actions', array( __CLASS__, 'add_edit_recipe_button_admin_row_actions' ), 99, 2 );
			
		}

		/**
		 * Detect if the recipe block is in the content then add the button.
		 * 
		 * @since 3.2.13
		 */
		public static function add_edit_button( $wp_admin_bar ) {

			global $post;

			if ( $post ) {

				if( has_block( 'wpzoom-recipe-card/block-recipe-card', $post->post_content ) ) {
					
					$url = get_edit_post_link( self::get_recipe_id( $post->ID ) );
					
					$wp_admin_bar->add_node( 
						array(
							'id'    => 'edit-wpzoom-recipe',
							'title' => esc_html__( 'Edit Recipe', 'recipe-card-blocks-by-wpzoom' ),
							'href'  => $url . '&scroll=wpzoom_recipe_block',
						) 
					);
				}
				elseif( has_block( 'wpzoom-recipe-card/recipe-block-from-posts', $post->post_content ) ) {
					
					$atts = $gutenberg_matches = array();
					$gutenberg_patern = '/<!--\s+wp:(wpzoom\-recipe\-card\/recipe\-block\-from\-posts)(\s+(\{.*?\}))?\s+(\/)?-->/';
					preg_match_all( $gutenberg_patern, $post->post_content, $matches );
					if ( isset( $matches[3] ) ) {
						foreach ( $matches[3] as $block_attributes_json ) {
							if ( ! empty( $block_attributes_json ) ) {
								$atts = json_decode( $block_attributes_json, true );
							}
						}
					}
					if( isset( $atts['postId'] ) ) {
						$recipe_ID = self::get_recipe_id( $atts['postId'] );
					}
					$url = get_edit_post_link( $recipe_ID );
					$wp_admin_bar->add_node( 
						array(
							'id'    => 'edit-wpzoom-recipe',
							'title' => esc_html__( 'Edit Recipe', 'recipe-card-blocks-by-wpzoom' ),
							'href'  => $url . '&scroll=wpzoom_recipe_block',
						) 
					);
				}

			}

		}

		public static function add_edit_recipe_button_admin_row_actions( $actions, $post ) {

			// Check for your post type.
			if ( 'wpzoom_rcb' == $post->post_type  ) {
				return $actions;
			}

			$recipe_edit_url_action = array();

			if( has_block( 'wpzoom-recipe-card/block-recipe-card', $post->post_content ) ) {
				$url = get_edit_post_link( self::get_recipe_id( $post->ID ) );
				$recipe_edit_url_action = array(
					'edit-wpzoom-recipe' => sprintf(
						'<a href="%s">%s</a>',
						$url . '&scroll=wpzoom_recipe_block',
						esc_html__( 'Edit Recipe', 'recipe-card-blocks-by-wpzoom' ) )
				);

			}
			elseif( has_block( 'wpzoom-recipe-card/recipe-block-from-posts', $post->post_content ) ) {
				
				$atts = $gutenberg_matches = array();
				$gutenberg_patern = '/<!--\s+wp:(wpzoom\-recipe\-card\/recipe\-block\-from\-posts)(\s+(\{.*?\}))?\s+(\/)?-->/';
				preg_match_all( $gutenberg_patern, $post->post_content, $matches );
				if ( isset( $matches[3] ) ) {
					foreach ( $matches[3] as $block_attributes_json ) {
						if ( ! empty( $block_attributes_json ) ) {
							$atts = json_decode( $block_attributes_json, true );
						}
					}
				}
				if( isset( $atts['postId'] ) ) {
					$recipe_ID = self::get_recipe_id( $atts['postId'] );
				}
				$url = get_edit_post_link( $recipe_ID );
				$recipe_edit_url_action = array(
					'edit-wpzoom-recipe' => sprintf(
						'<a href="%s">%s</a>',
						$url . '&scroll=wpzoom_recipe_block',
						esc_html__( 'Edit Recipe', 'recipe-card-blocks-by-wpzoom' ) )
				);

			}

			$actions = array_merge( $actions, $recipe_edit_url_action  );

			return $actions;

		}

		public static function get_recipe_id( $id )  {

			if( ! $id ) {
				return;
			}	

			$args = array(
				'numberposts' => -1,
				'post_type'   => 'wpzoom_rcb',
				'meta_key'    => '_wpzoom_rcb_parent_post_id',
				'meta_value'  => $id

			);

			$posts = get_posts( $args );

			if( $posts ) {
				return $posts[0]->ID;
			}
			else {
				return $id;
			}

		}

	}

}

WPZOOM_Recipe_Edit_Link::instance();