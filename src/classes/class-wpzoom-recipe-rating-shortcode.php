<?php
/**
 * Recipes Rating Shortcode
 *
 * @since   5.1.4
 * @package WPZOOM_Recipe_Card_Blocks
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WPZOOM_Recipe_Rating_Shortcode' ) ) {
	/**
	 * Main WPZOOM_Recipe_Rating_Shortcode Class.
	 *
	 * @since   5.1.4
	 */
	class WPZOOM_Recipe_Rating_Shortcode {

		/**
		 * This plugin's instance.
		 *
		 * @var WPZOOM_Recipe_Rating_Shortcode
		 * @since   5.1.4
		 */
		private static $instance;

		/**
		 * Provides singleton instance.
		 *
		 * @since   5.1.4
		 * @return self instance
		 */
		public static function instance() {			

			if ( null === self::$instance ) {
				self::$instance = new WPZOOM_Recipe_Rating_Shortcode();
			}

			return self::$instance;
		}

		/**
		 * The Constructor.
		 */
		public function __construct() {		
			add_shortcode( 'wpzoom_rcb_rating', array( __CLASS__, 'render_shortcode' ) );
		}

		/**
		 * Render Shortcode.
		 */
		public static function render_shortcode( $atts ) {

			global $post;
			$blocks = array();
			$recipe_ID = '';

			// Defining Shortcode's Attributes
			$shortcode_args = shortcode_atts(
				array(
					'recipe_id' => '',
				), $atts );

			if ( ! $post instanceof WP_Post ) {
				return '';
			}

			$blocks = parse_blocks( $post->post_content );
			
			//Check if it is block page content
			if( !empty( $blocks ) && is_array( $blocks ) ) {
				foreach ( $blocks as $key => $block ) {
					if ( 'wpzoom-recipe-card/block-recipe-card' === $block['blockName'] ) {
						return WPZOOM_Rating_Stars::get_rating_form( $post->ID ) ;
					}
					elseif( 'wpzoom-recipe-card/recipe-block-from-posts' === $block['blockName'] ) {
						if( isset( $block['attrs']['postId'] ) ) {
							$recipe_ID = $block['attrs']['postId'];
						}
					}
				}
			}

			if( has_shortcode( $post->post_content, 'wpzoom_rcb_post' ) ) {
				$shortcode_pattern = '/\[wpzoom_rcb_post(.*?)\]/';
				preg_match_all( $shortcode_pattern, $post->post_content, $matches );
				if( isset( $matches[1][0] ) ) {
					$shortcode_id = (int) filter_var( $matches[1][0], FILTER_SANITIZE_NUMBER_INT );
					$parentRecipe_ID = get_post_meta( $shortcode_id, '_wpzoom_rcb_parent_post_id', true );
					if( !empty( $parentRecipe_ID ) ) {
						$recipe_ID = $parentRecipe_ID;
					}
					else {
						$recipe_ID = $shortcode_id;
					}
				}
			}

			//Check if it is elementor page content
			if( WPZOOM_Recipe_Card_Block::is_built_with_elementor() ) {

				$elementor_data = get_post_meta( $post->ID, '_elementor_data' );
				if ( isset( $elementor_data[0] ) && is_string( $elementor_data[0] ) ) {
				
					$regExp = '/"widgetType":"([^"]*)/i';
					$regExpAttrs = '/"rcb_post_id":"([^"]*)/i';
					
					$outputArray = array();
			
					if ( preg_match_all( $regExp, $elementor_data[0], $outputArray, PREG_SET_ORDER) ) {}
					if ( preg_match_all( $regExpAttrs, $elementor_data[0], $outputArray2, PREG_SET_ORDER) ) {}				
	
					foreach( $outputArray as $found ) {
						if( in_array( 'wpzoom-elementor-recipe-card-widget', $found ) ) {
							$recipe_ID = $post->ID;
						} 
						elseif( in_array( 'wpzoom-elementor-recipe-card-widget-cpt', $found ) ) {
							if( isset( $outputArray2[0][1] ) ) {	
								$recipe_ID = $outputArray2[0][1];
							}					
						}
					}	
				}
			}
	
			if( ! $recipe_ID ) {
				$recipe_ID = isset( $shortcode_args['recipe_id'] ) ? $shortcode_args['recipe_id'] : null;
			}

			if( empty( $recipe_ID ) ) {
				return '';
			}
	
			$parentRecipe_ID = get_post_meta( $recipe_ID, '_wpzoom_rcb_parent_post_id', true );

			if( ! empty( $parentRecipe_ID ) && $parentRecipe_ID != get_the_ID() && has_block( 'wpzoom-recipe-card/block-recipe-card', $parentRecipe_ID ) ) {
				$i = $parentRecipe_ID;
			}
			else {
				$i = $recipe_ID;
			}

			return WPZOOM_Rating_Stars::get_rating_form( $i );

		}

	}
}