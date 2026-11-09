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

			// Where the embedded recipe's ratings are stored; also used for Print (3.7.0).
			$i = WPZOOM_Recipe_Card_Block::resolve_rating_parent( intval( $recipe_id ) );

			$recipe = get_post( intval( $recipe_id ) );

			if ( ! WPZOOM_Helpers::can_view_recipe( $recipe ) ) {
				return '';
			}

			if ( has_blocks( $recipe->post_content ) ) {
				$blocks = parse_blocks( $recipe->post_content );
			}
			
			$output = '';
			// The card rates under the recipe this shortcode shows (3.7.0, as PRO 7.5.0).
			WPZOOM_Recipe_Card_Block::push_render_context( $i );
			foreach( $blocks as $block ) {
				$output .= render_block( $block );
			}
			WPZOOM_Recipe_Card_Block::pop_render_context();

			return sprintf( 
				'<div class="wpzoom-custom-recipe-card-post wpzoom-rcb-post-shortcode" data-parent-id="%3$d" data-recipe-post="%2$d">%1$s</div>',
				$output,
				intval( $recipe_id ),
				intval( $i )
			);

		}

	}
}