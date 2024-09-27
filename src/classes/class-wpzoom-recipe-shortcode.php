<?php
/**
 * Recipes Card Shortcode
 *
 * @since   3.5.0
 * @package WPZOOM_Recipe_Card_Blocks
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WPZOOM_Recipe_Card_Shortcode' ) ) {
	/**
	 * Main WPZOOM_Recipes_Scanner Class.
	 *
	 * @since 3.5.0
	 */
	class WPZOOM_Recipe_Card_Shortcode {

		/**
		 * This plugin's instance.
		 *
		 * @var WPZOOM_Recipe_Card_Shortcode
		 * @since 3.5.0
		 */
		private static $instance;

		/**
		 * Provides singleton instance.
		 *
		 * @since 3.5.0
		 * @return self instance
		 */
		public static function instance() {			

			if ( null === self::$instance ) {
				self::$instance = new WPZOOM_Recipe_Card_Shortcode();
			}

			return self::$instance;
		}

		/**
		 * The Constructor.
		 */
		public function __construct() {		
			add_shortcode( 'wpzoom_rcb_post', array( __CLASS__, 'render_shortcode' ) );
		}

		/**
		 * Render Shortcode.
		 */
		public static function render_shortcode( $atts ) {

			global $post;
			static $i;
			$blocks = array();

			// Defining Shortcode's Attributes
			$shortcode_args = shortcode_atts(
				array(
					'id' => '',
				), $atts );

			$recipe_id = isset( $shortcode_args['id'] ) ? $shortcode_args['id'] : null;

			if( !$recipe_id ) {
				return '';	
			}

			$parentRecipe_ID = get_post_meta( $recipe_id, '_wpzoom_rcb_parent_post_id', true );
			if( ! empty( $parentRecipe_ID ) && $parentRecipe_ID != get_the_ID() ) {
				$i = $parentRecipe_ID;
			}
			else {
				$i = $recipe_id;
			}

			$recipe = get_post( intval( $recipe_id ) );

			if ( has_blocks( $recipe->post_content ) ) {
				$blocks = parse_blocks( $recipe->post_content );
			}
			
			$output = '';
			foreach( $blocks as $block ) {
				$output .= render_block( $block );
			}

			return sprintf( 
				'<div class="wpzoom-custom-recipe-card-post wpzoom-rcb-post-shortcode" data-parent-id="%3$d" data-recipe-post="%2$d">%1$s</div>',
				$output,
				intval( $recipe_id ),
				intval( $i )
			);

		}

	}
}