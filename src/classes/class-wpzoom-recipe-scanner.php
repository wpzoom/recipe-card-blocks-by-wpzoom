<?php
/**
 * Recipes Scanner
 *
 * @since   2.8.2
 * @package WPZOOM_Recipe_Card_Blocks
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WPZOOM_Recipes_Scanner' ) ) {
	/**
	 * Main WPZOOM_Recipes_Scanner Class.
	 *
	 * @since 2.8.2
	 */
	class WPZOOM_Recipes_Scanner {

		/**
		 * This plugin's instance.
		 *
		 * @var WPZOOM_Recipes_Scanner
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
				self::$instance = new WPZOOM_Recipes_Scanner();
			}

			return self::$instance;
		}

		/**
		 * The Constructor.
		 */
		public function __construct() {
		
			
			// Include admin scripts & styles
			add_action( 'admin_enqueue_scripts', array( $this, 'scripts' ) );
			add_action( 'wp_ajax_wpzoom_search_recipes', array( $this, 'search_recipes' ) );
			add_action( 'wp_ajax_wpzoom_search_recipes_box_close', array( $this, 'search_recipes_box_close' ) );
			
		}


		public function scripts( $hook ) {

			wp_enqueue_script(
				'wpzoom-rcb-scan-script',
				untrailingslashit( WPZOOM_RCB_PLUGIN_URL ) . '/dist/assets/admin/js/scanner.js',
				array( 'jquery' ),
				WPZOOM_RCB_VERSION
			);
	
			wp_localize_script(
				'wpzoom-rcb-scan-script',
				'WPZOOM_Scanner',
				array(
					'ajaxUrl'    => admin_url( 'admin-ajax.php' ),
					'ajax_nonce' => wp_create_nonce( 'wpzoom-reset-settings-nonce' ),
				)
			);
		}

		/**
		 * Close Welcome banner
		 *
		 * @since 1.2.0
		 * @return void
		 */
		public function search_recipes_box_close() {
			
			check_ajax_referer( 'wpzoom-reset-settings-nonce', 'security' );

			if ( set_transient( 'wpzoom_rcb_search_recipe_box', true, 12 * HOUR_IN_SECONDS ) ) {
				$response = array(
					'status'  => '200',
					'message' => 'OK',
				);

				wp_send_json_success( $response );
			} else {
				$response = array(
					'status'  => '304',
					'message' => 'NOT',
				);

				wp_send_json_error( $response );
			}
		}


		/**
		 * Search for recipes.
		 *
		 * @since   2.8.2
		 * @param	 int $page Page of recipes to add.
		 */
		public function search_recipes( $page = 0 ) {

			check_ajax_referer( 'wpzoom-reset-settings-nonce', 'security' );
			
			$recipes = array();
			$finished = false;
	
			$limit = -1;
	
			$args = array(
				'post_type'      => array( 'post', 'page' ),
				'post_status'    => 'any',
				'orderby'        => 'date',
				'order'          => 'DESC',
				'posts_per_page' => $limit,
			);

			$query = new WP_Query( $args );

			if ( $query->have_posts() ) {
				$posts = $query->posts;
	
				foreach ( $posts as $post ) {
					if( WPZOOM_Recipe_Post_Saver::recipe_post_exists( $post->ID ) ) {
						continue;
					}
					$blocks = parse_blocks( $post->post_content );
	
					foreach ( $blocks as $index => $block ) {
						 if ( 'wpzoom-recipe-card/block-recipe-card' === $block['blockName'] ) {
							$name = isset( $block['attrs']['recipeTitle'] ) && $block['attrs']['recipeTitle'] ? $block['attrs']['recipeTitle'] : esc_html__( 'Unknown', 'recipe-card-blocks-by-wpzoom' );
	
							$recipe_id = $post->ID . '-' . $index;
							$recipes[ $recipe_id ] = array(
								'name'     => $name,
								'recipe'   => serialize_blocks( array( $block ) ),
								'url'      => get_edit_post_link( $post->ID ),
								'parentId' => $post->ID
							);
						
						}
					}
				}
			} else {
				$finished = true;
			}
			
			if( !empty( $recipes ) ) {
				foreach( $recipes as $recipe ) {
					WPZOOM_Recipe_Post_Saver::create_recipe_post( $recipe );
				}
			}

			$search_time = date( 'F j, Y g:i a' );
			update_option( 'wpzoom_search_recipes_cards_time', $search_time );

			$search_result = array(
				'process' => esc_html__( 'Done!', 'recipe-card-blocks-by-wpzoom' ),
				'recipes' => count( $recipes ),
				'searchTime' => $search_time
			);

			wp_send_json_success( $search_result );
		
		}

	}
}

WPZOOM_Recipes_Scanner::instance();
