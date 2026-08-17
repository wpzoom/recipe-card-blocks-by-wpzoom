<?php
/**
 * Allow users to add rating for recipe from comment form.
 *
 * @since 3.5.0
 * @package WPZOOM_Recipe_Card_Blocks
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Wrap the definition so the class is only declared when the PRO version has not declared it already.
// NOTE: the conditional prevents PHP compile-time early binding, so the init() call below is skipped too.
if ( ! class_exists( 'WPZOOM_Comment_Rating' ) ) {

class WPZOOM_Comment_Rating {
	/**
	 * Loads scripts and styles.
	 *
	 * @var WPZOOM_Assets_Manager
	 */
	public static $assets_manager;

	/**
	 * Register actions and filters.
	 */
	public static function init() {
		self::$assets_manager = WPZOOM_Assets_Manager::instance();

		add_filter( 'comment_text', array( __CLASS__, 'add_rating_stars_to_comment' ), 10, 3 );
		add_filter( 'comment_form_field_comment', array( __CLASS__, 'add_rating_stars_to_comment_form' ), 10, 1 );

		add_action( 'enqueue_block_assets', array( __CLASS__, 'frontend_register_scripts' ) );
		add_action( 'enqueue_block_assets', array( __CLASS__, 'block_assets' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'admin_comment_list_assets' ) );
		add_action( 'add_meta_boxes_comment', array( __CLASS__, 'add_rating_field_to_comments_edit_page' ) );

		add_action( 'comment_post', array( __CLASS__, 'save_comment_rating' ) );
		add_action( 'edit_comment', array( __CLASS__, 'save_admin_comment_rating' ) );

		add_action( 'trashed_comment', array( __CLASS__, 'update_comment_rating_on_change' ) );
		add_action( 'untrashed_comment', array( __CLASS__, 'update_comment_rating_on_change' ) );
		add_action( 'spammed_comment', array( __CLASS__, 'update_comment_rating_on_change' ) );
		add_action( 'unspammed_comment', array( __CLASS__, 'update_comment_rating_on_change' ) );
		add_action( 'wp_set_comment_status', array( __CLASS__, 'update_comment_rating_on_change' ) );
	}

	/**
	 * Get the star SVG markup used for ratings.
	 *
	 * The Lite version ships two inline Font Awesome star icons instead of
	 * the full PRO icon registry.
	 *
	 * @param string $type The star type: 'full' or 'empty'.
	 * @return string The SVG markup.
	 */
	public static function get_rating_star_svg( $type ) {
		if ( 'full' === $type ) {
			return '<svg width="1em" height="1em" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512" fill="currentColor" aria-hidden="true" focusable="false"><path d="M259.3 17.8L194 150.2 47.9 171.5c-26.2 3.8-36.7 36.1-17.7 54.6l105.7 103-25 145.5c-4.5 26.3 23.2 46 46.4 33.7L288 439.6l130.7 68.7c23.2 12.2 50.9-7.4 46.4-33.7l-25-145.5 105.7-103c19-18.5 8.5-50.8-17.7-54.6L382 150.2 316.7 17.8c-11.7-23.6-45.6-23.9-57.4 0z"/></svg>';
		}

		if ( 'empty' === $type ) {
			return '<svg width="1em" height="1em" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512" fill="currentColor" aria-hidden="true" focusable="false"><path d="M528.1 171.5L382 150.2 316.7 17.8c-11.7-23.6-45.6-23.9-57.4 0L194 150.2 47.9 171.5c-26.2 3.8-36.7 36.1-17.7 54.6l105.7 103-25 145.5c-4.5 26.3 23.2 46 46.4 33.7L288 439.6l130.7 68.7c23.2 12.2 50.9-7.4 46.4-33.7l-25-145.5 105.7-103c19-18.5 8.5-50.8-17.7-54.6zM388.6 312.3l23.7 138.4L288 385.4l-124.3 65.3 23.7-138.4-100.6-98 139-20.2 62.2-126 62.2 126 139 20.2-100.6 98z"/></svg>';
		}

		return '';
	}

	/**
	 * Get rating average for a recipe.
	 *
	 * @param string|number $recipe_ID The recipe id.
	 * @return number The average rating.
	 */
	public static function get_rating_average( $recipe_ID ) {
		if ( ! $recipe_ID ) {
			return;
		}

		return WPZOOM_Rating_DB::get_rating_average(
			array(
				'where' => '(recipe_id = ' . absint( $recipe_ID ) . ' OR post_id = ' . absint( $recipe_ID ) . ') AND approved = 1',
			)
		);
	}

	/**
	 * Get total number of recipe votes.
	 *
	 * @param string|number $recipe_ID The recipe id.
	 * @return number The total number of votes.
	 */
	public static function get_total_votes( $recipe_ID ) {
		if ( ! $recipe_ID ) {
			return;
		}

		$ratings = WPZOOM_Rating_DB::get_ratings(
			array(
				'where' => '(recipe_id = ' . absint( $recipe_ID ) . ' OR post_id = ' . absint( $recipe_ID ) . ') AND approved = 1',
			)
		);

		return $ratings['total'];
	}

	/**
	 * Get total number of recipe reviews (ratings with comments).
	 *
	 * @param string|number $recipe_ID The recipe id.
	 * @return number The total number of comment-based ratings.
	 */
	public static function get_review_count( $recipe_ID ) {
		if ( ! $recipe_ID ) {
			return 0;
		}

		$ratings = WPZOOM_Rating_DB::get_ratings(
			array(
				'where' => '(recipe_id = ' . absint( $recipe_ID ) . ' OR post_id = ' . absint( $recipe_ID ) . ') AND approved = 1 AND comment_id > 0',
			)
		);

		return $ratings['total'];
	}

	/**
	 * Add or update rating for a specific comment.
	 *
	 * @param int $comment_id       ID of the comment.
	 * @param int $comment_rating   Rating to add for this comment.
	 */
	public static function update_comment_rating( $comment_id, $comment_rating ) {
		$comment_id     = intval( $comment_id );
		$comment_rating = intval( $comment_rating );

		if ( $comment_id ) {
			$comment = get_comment( $comment_id );

			if ( $comment ) {
				if ( $comment_rating ) {
					// Get the recipe ID for this post to enable duplicate rating check
					$recipe_id = self::get_recipe_id_for_post( $comment->comment_post_ID );

					$rating = array(
						'rating'     => $comment_rating,
						'rate_date'  => $comment->comment_date,
						'comment_id' => $comment->comment_ID,
						'user_id'    => $comment->user_id,
						'ip'         => $comment->comment_author_IP,
						'recipe_id'  => $recipe_id,
					);

					WPZOOM_Rating_DB::add_or_update_rating( $rating );
				} else {
					WPZOOM_Rating_DB::delete_ratings_for_comment( $comment_id );
				}
			} else {
				WPZOOM_Rating_DB::delete_ratings_for_comment( $comment_id );
			}
		}
	}

	/**
	 * Get the recipe ID associated with a post for rating purposes.
	 * This must match the logic used in WPZOOM_Recipe_Card_Block to determine the rating recipe ID.
	 *
	 * @param int $post_id The post ID.
	 * @return int The recipe ID for rating purposes.
	 */
	public static function get_recipe_id_for_post( $post_id ) {
		$post_id = intval( $post_id );

		if ( ! $post_id ) {
			return 0;
		}

		$post = get_post( $post_id );
		if ( ! $post ) {
			return $post_id;
		}

		// Default to the post ID itself
		$recipe_id_rating = $post_id;

		// Check if this is a recipe CPT with a parent post
		$parent_recipe_id = get_post_meta( $post_id, '_wpzoom_rcb_parent_post_id', true );
		if ( ! empty( $parent_recipe_id ) ) {
			// This post IS a recipe CPT, use the parent post's logic
			return intval( $parent_recipe_id );
		}

		// Check if the post has a recipe-block-from-posts block (Insert Existing Recipe)
		if ( has_block( 'wpzoom-recipe-card/recipe-block-from-posts', $post->post_content ) ) {
			$gutenberg_pattern = '/<!--\s+wp:(wpzoom\-recipe\-card\/recipe\-block\-from\-posts)(\s+(\{.*?\}))?\s+(\/)?-->/';
			preg_match_all( $gutenberg_pattern, $post->post_content, $matches );

			if ( isset( $matches[3] ) && is_array( $matches[3] ) ) {
				foreach ( $matches[3] as $block_attributes_json ) {
					if ( ! empty( $block_attributes_json ) ) {
						$atts = json_decode( $block_attributes_json, true );
						if ( isset( $atts['postId'] ) ) {
							$embedded_recipe_id = intval( $atts['postId'] );
							// Check if the embedded recipe has a parent
							$embedded_parent = get_post_meta( $embedded_recipe_id, '_wpzoom_rcb_parent_post_id', true );
							if ( ! empty( $embedded_parent ) && $embedded_parent != $post_id && has_block( 'wpzoom-recipe-card/block-recipe-card', $embedded_parent ) ) {
								$recipe_id_rating = intval( $embedded_parent );
							} else {
								$recipe_id_rating = $embedded_recipe_id;
							}
							break; // Use the first recipe found
						}
					}
				}
			}
		}

		// Check if the post has a wpzoom_rcb_post shortcode
		if ( has_shortcode( $post->post_content, 'wpzoom_rcb_post' ) ) {
			$shortcode_pattern = '/\[wpzoom_rcb_post(.*?)\]/';
			preg_match_all( $shortcode_pattern, $post->post_content, $matches );

			if ( isset( $matches[1][0] ) ) {
				$shortcode_id = (int) filter_var( $matches[1][0], FILTER_SANITIZE_NUMBER_INT );
				if ( $shortcode_id ) {
					$shortcode_parent = get_post_meta( $shortcode_id, '_wpzoom_rcb_parent_post_id', true );
					if ( ! empty( $shortcode_parent ) && $shortcode_parent != $post_id && has_block( 'wpzoom-recipe-card/block-recipe-card', $shortcode_parent ) ) {
						$recipe_id_rating = intval( $shortcode_parent );
					} else {
						$recipe_id_rating = $shortcode_id;
					}
				}
			}
		}

		return $recipe_id_rating;
	}

	/**
	 * Add rating stars to the text of a comment.
	 *
	 * @param string $comment_text Text of the current comment.
	 * @param object $comment      The comment object.
	 * @param array  $args         An array of arguments.
	 */
	public static function add_rating_stars_to_comment( $comment_text, $comment = null, $args = array() ) {
		// Check if comment ratings are enabled
		if ( ! WPZOOM_Settings::get( 'wpzoom_rcb_settings_comment_ratings' ) ) {
			return $comment_text;
		}

		if ( null !== $comment ) {
			$rating_stars_html       = '';
			$rating                  = self::get_rating_by_comment_id( $comment->comment_ID );
			$comment_rating_template = apply_filters( 'wpzoom_rcb_comment_rating_template', WPZOOM_RCB_PLUGIN_DIR . 'templates/public/comment-rating.php' );

			if ( $rating ) {
				ob_start();
				require $comment_rating_template;
				$rating_stars_html = ob_get_contents();
				ob_end_clean();
			}

			$comment_text = $rating_stars_html . $comment_text;
		}

		return $comment_text;
	}

	/**
	 * Add rating stars to the comment field form.
	 *
	 * @param string $args_comment_field The content of the comment textarea field.
	 */
	public static function add_rating_stars_to_comment_form( $args_comment_field ) {
		global $post, $comment;

		// Check if comment ratings are enabled
		if ( ! WPZOOM_Settings::get( 'wpzoom_rcb_settings_comment_ratings' ) ) {
			return $args_comment_field;
		}

		if ( 'loggedin' === WPZOOM_Settings::get( 'wpzoom_rcb_settings_who_can_rate' ) && ! is_user_logged_in() ) {
			return $args_comment_field;
		}

		$rating_stars_html = '';
		$comment_id        = is_object( $comment ) && isset( $comment->comment_ID ) ? $comment->comment_ID : 0;

		// Pass variables to comment-rating-form template
		$post_ID = $post->ID;
		$rating  = self::get_rating_by_comment_id( $comment_id );

		$comment_rating_template = apply_filters( 'wpzoom_rcb_comment_rating_form_template', WPZOOM_RCB_PLUGIN_DIR . 'templates/public/comment-rating-form.php' );

		ob_start();
		require $comment_rating_template;
		$rating_stars_html = ob_get_contents();
		ob_end_clean();

		$args_comment_field = $rating_stars_html . $args_comment_field;

		return $args_comment_field;
	}

	/**
	 * Get rating for a comment by specified comment ID.
	 *
	 * @param  int $comment_id The comment ID.
	 * @return int             The rating average.
	 */
	public static function get_rating_by_comment_id( $comment_id ) {
		$rating     = 0;
		$comment_id = intval( $comment_id );

		// Check if comment ratings are enabled
		if ( ! WPZOOM_Settings::get( 'wpzoom_rcb_settings_comment_ratings' ) ) {
			return $rating;
		}

		if ( $comment_id ) {
			$rating_found = get_comment_meta( $comment_id, 'wpzoom-rcb-comment-rating', true );

			// Cache rating for this comment if none can be found.
			if ( '' === $rating_found ) {
				$rating_found = WPZOOM_Rating_DB::get_rating(
					array(
						'where' => 'comment_id = ' . $comment_id,
					)
				);

				if ( $rating_found ) {
					$rating = intval( $rating_found->rating );
				} else {
					$rating = 0;
				}

				self::update_comment_meta_rating( $comment_id, $rating );
			} else {
				$rating = intval( $rating_found );
			}
		}

		return $rating;
	}

	/**
	 * Add comment rating meta box to the comment edit page.
	 */
	public static function add_rating_field_to_comments_edit_page() {
		global $comment;

		if ( ! $comment ) {
			return;
		}

		if ( has_block( 'wpzoom-recipe-card/block-recipe-card', $comment->comment_post_ID ) || WPZOOM_Assets_Manager::has_reusable_block( 'wpzoom-recipe-card/block-recipe-card', $comment->comment_post_ID ) || WPZOOM_Assets_Manager::has_cpt_rcb_elementor_widget( $comment->comment_post_ID ) ) {
			add_meta_box(
				'wpzoom-rcb-comment-rating',
				__( 'Change comment rating', 'recipe-card-blocks-by-wpzoom' ),
				array( __CLASS__, 'add_rating_field_to_admin_comments_form' ),
				'comment',
				'normal',
				'high'
			);
		}
	}

	/**
	 * Add rating field to the admin comment form.
	 *
	 * @param object $comment The comment being edited.
	 */
	public static function add_rating_field_to_admin_comments_form( $comment ) {
		$post_ID = $comment->comment_post_ID;
		$rating  = self::get_rating_by_comment_id( $comment->comment_ID );

		wp_nonce_field( 'comment-rating-' . $comment->comment_ID, 'wpzoom-rcb-comment-rating-nonce', false );

		$template = apply_filters( 'wpzoom_rcb_comment_rating_form_template', WPZOOM_RCB_PLUGIN_DIR . 'templates/public/comment-rating-form.php' );

		require $template;
	}

	/**
	 * Save the comment rating from admin edit page.
	 *
	 * @param  int $comment_id The comment id being saved.
	 */
	public static function save_admin_comment_rating( $comment_id ) {
		$wpzoom_rcb_comment_rating_nonce = isset( $_POST['wpzoom-rcb-comment-rating-nonce'] ) ? $_POST['wpzoom-rcb-comment-rating-nonce'] : false;

		if ( $wpzoom_rcb_comment_rating_nonce && wp_verify_nonce( sanitize_key( $wpzoom_rcb_comment_rating_nonce ), 'comment-rating-' . $comment_id ) ) {
			$rating = isset( $_POST['wpzoom-rcb-comment-rating'] ) ? intval( $_POST['wpzoom-rcb-comment-rating'] ) : 0;
			self::update_comment_rating( $comment_id, $rating );
		}
	}

	/**
	 * Save the comment rating.
	 *
	 * @param int $comment_id ID of the comment being saved.
	 */
	public static function save_comment_rating( $comment_id ) {
		$rating = isset( $_POST['wpzoom-rcb-comment-rating'] ) ? intval( $_POST['wpzoom-rcb-comment-rating'] ) : 0;
		self::update_comment_rating( $comment_id, $rating );
	}

	/**
	 * Update the comment rating meta that is used as a cache.
	 *
	 * @param   int $comment_id ID of the comment.
	 * @param   int $rating     Rating to set for this comment.
	 */
	public static function update_comment_meta_rating( $comment_id, $rating ) {
		$comment_id = intval( $comment_id );
		$rating     = intval( $rating );

		if ( $comment_id ) {
			$comment = get_comment( $comment_id );

			if ( $comment ) {
				update_comment_meta( $comment_id, 'wpzoom-rcb-comment-rating', $rating );
			}
		}
	}

	/**
	 * Update recipe rating when comment changes.
	 *
	 * @param int $comment_id ID of the comment being changed.
	 */
	public static function update_comment_rating_on_change( $comment_id ) {
		// Force update in case approval state changed
		$rating = self::get_rating_by_comment_id( $comment_id );
		self::update_comment_rating( $comment_id, $rating );
	}

	/**
	 * Registers Front-end block scripts.
	 *
	 * Fired by `enqueue_block_assets` action.
	 *
	 * @access public
	 */
	public static function frontend_register_scripts() {
		wp_register_script(
			'wpzoom-comment-rating-script',
			self::$assets_manager->asset_source( 'js', 'wpzoom-comment-rating.js' ),
			self::$assets_manager->get_dependencies( 'wpzoom-comment-rating-script' ),
			WPZOOM_RCB_VERSION,
			true
		);

		wp_register_style(
			'wpzoom-rcb-block-rating-css',
			self::$assets_manager->asset_source( 'css', 'rating.build.css' ),
			array(),
			WPZOOM_RCB_VERSION
		);

		// Apply the custom star color from settings.
		$stars_color = sanitize_hex_color( (string) WPZOOM_Settings::get( 'wpzoom_rcb_settings_rating_stars_color' ) );
		if ( $stars_color && '#f2a123' !== strtolower( $stars_color ) ) {
			wp_add_inline_style(
				'wpzoom-rcb-block-rating-css',
				'span.wpz-star-icon,' .
				'.wpzoom-rcb-comment-rating .wpzoom-rcb-comment-rating-stars > span.wpz-star-icon,' .
				'.wpzoom-rcb-comment-rating .wpzoom-rcb-comment-rating-stars > span.wpz-star-icon.wpz-empty-star,' .
				'.wpzoom-rcb-comment-rating-stars > label span.wpz-star-icon,' .
				'.wpzoom-rcb-comment-rating-stars > label span.wpz-star-icon:hover,' .
				'.wpzoom-rcb-comment-rating-stars > label span.wpz-star-icon.wpz-full-star,' .
				'ul.wpzoom-rating-stars > li.wpz-star-icon,' .
				'ul.wpzoom-rating-stars > li.wpz-full-star,' .
				'ul.wpzoom-rating-stars:hover > li' .
				'{ color: ' . $stars_color . ' !important; }'
			);
		}
	}

	/**
	 * Enqueue comment rating scripts.
	 */
	public static function block_assets() {
		if ( is_admin() ) {
			return false;
		}

		// Don't load scripts if the comment rating is not allowed
		if ( '1' !== WPZOOM_Settings::get( 'wpzoom_rcb_settings_comment_ratings' ) ) {
			return false;
		}

		if ( ! is_singular() ) {
			return false;
		}

		$should_enqueue =
			has_block( 'wpzoom-recipe-card/block-recipe-card' ) ||
			has_block( 'wpzoom-recipe-card/recipe-block-from-posts' ) ||
			WPZOOM_Assets_Manager::has_reusable_block( 'wpzoom-recipe-card/block-recipe-card' ) ||
			WPZOOM_Assets_Manager::has_cpt_rcb_shortcode() ||
			WPZOOM_Assets_Manager::has_cpt_rcb_elementor_widget();

		if ( ! $should_enqueue ) {
			return false;
		}

		/**
		 * Load only on single page and if recipe card block is present in post
		 */
		wp_enqueue_script( 'wpzoom-comment-rating-script' );
		wp_enqueue_style( 'wpzoom-rcb-block-rating-css' );
	}

	/**
	 * Enqueue the rating block CSS on the WP comments list and comment edit
	 * screens so the SVG stars added via `comment_text` are styled correctly.
	 *
	 * @param string $hook Current admin page hook.
	 */
	public static function admin_comment_list_assets( $hook ) {
		if ( 'edit-comments.php' !== $hook && 'comment.php' !== $hook ) {
			return;
		}

		if ( ! WPZOOM_Settings::get( 'wpzoom_rcb_settings_comment_ratings' ) ) {
			return;
		}

		wp_enqueue_style(
			'wpzoom-rcb-block-rating-css',
			self::$assets_manager->asset_source( 'css', 'rating.build.css' ),
			array(),
			WPZOOM_RCB_VERSION
		);
	}
}

WPZOOM_Comment_Rating::init();

}
