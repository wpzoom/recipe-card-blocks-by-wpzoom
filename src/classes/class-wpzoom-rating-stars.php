<?php
/**
 * Rating Stars Class
 *
 * Add rating stars to recipe card.
 *
 * @since   1.1.0
 * @package WPZOOM_Recipe_Card_Blocks
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WPZOOM_Rating_Stars {
	/**
	 * We need to create a table where to store all ratings for each single post.
	 *
	 * @var string
	 * @since 1.1.0
	 */
	public static $tablename;

	/**
	 * Loads scripts and styles.
	 *
	 * @var WPZOOM_Assets_Manager
	 * @since 1.1.0
	 */
	public static $assets_manager;

	/**
	 * Who can rate recipes
	 *
	 * @since 2.3.1
	 */
	public static $who_can_rate;

	/**
	 * Star glyphs used by the rating widget.
	 *
	 * Font Awesome 6 Free: 'fas fa-star', 'far fa-star' and 'far fa-star-half',
	 * taken from the PRO plugin's src/assets/icons/svg-registry.json. They are
	 * inlined rather than read through an icon class because that registry is
	 * ~616KB for 1,109 icons and the rating widget needs exactly these three.
	 * Sized in em so they scale with font-size, which is what the CSS expects.
	 *
	 * @var array
	 * @since 3.5.0
	 */
	private static $star_svgs = array(
		'full'  => '<svg viewBox="0 0 576 512" width="1em" height="1em" xmlns="http://www.w3.org/2000/svg"><path d="M309.5-18.9c-4.1-8-12.4-13.1-21.4-13.1s-17.3 5.1-21.4 13.1L193.1 125.3 33.2 150.7c-8.9 1.4-16.3 7.7-19.1 16.3s-.5 18 5.8 24.4l114.4 114.5-25.2 159.9c-1.4 8.9 2.3 17.9 9.6 23.2s16.9 6.1 25 2L288.1 417.6 432.4 491c8 4.1 17.7 3.3 25-2s11-14.2 9.6-23.2L441.7 305.9 556.1 191.4c6.4-6.4 8.6-15.8 5.8-24.4s-10.1-14.9-19.1-16.3L383 125.3 309.5-18.9z"/></svg>',
		'empty' => '<svg viewBox="0 0 576 512" width="1em" height="1em" xmlns="http://www.w3.org/2000/svg"><path d="M288.1-32c9 0 17.3 5.1 21.4 13.1L383 125.3 542.9 150.7c8.9 1.4 16.3 7.7 19.1 16.3s.5 18-5.8 24.4L441.7 305.9 467 465.8c1.4 8.9-2.3 17.9-9.6 23.2s-17 6.1-25 2L288.1 417.6 143.8 491c-8 4.1-17.7 3.3-25-2s-11-14.2-9.6-23.2L134.4 305.9 20 191.4c-6.4-6.4-8.6-15.8-5.8-24.4s10.1-14.9 19.1-16.3l159.9-25.4 73.6-144.2c4.1-8 12.4-13.1 21.4-13.1zm0 76.8L230.3 158c-3.5 6.8-10 11.6-17.6 12.8l-125.5 20 89.8 89.9c5.4 5.4 7.9 13.1 6.7 20.7l-19.8 125.5 113.3-57.6c6.8-3.5 14.9-3.5 21.8 0l113.3 57.6-19.8-125.5c-1.2-7.6 1.3-15.3 6.7-20.7l89.8-89.9-125.5-20c-7.6-1.2-14.1-6-17.6-12.8L288.1 44.8z"/></svg>',
		'half'  => '<svg viewBox="0 0 576 512" width="1em" height="1em" xmlns="http://www.w3.org/2000/svg"><path d="M285.7-15.8c10.8 2.6 18.4 12.2 18.4 23.3l0 387.1c0 9-5.1 17.3-13.1 21.4L143.8 491c-8 4.1-17.7 3.3-25-2s-11-14.2-9.6-23.2L134.4 305.9 20 191.4c-6.4-6.4-8.6-15.8-5.8-24.4s10.1-14.9 19.1-16.3L193.1 125.3 258.8-3.3c5-9.9 16.2-15 27-12.4zM256.1 107.4L230.3 158c-3.5 6.8-10 11.6-17.6 12.8l-125.5 20 89.8 89.9c5.4 5.4 7.9 13.1 6.7 20.7l-19.8 125.5 92.2-46.9 0-272.6z"/></svg>',
	);


	/**
	 * Register actions and filters.
	 */
	public static function init() {
		global $wpdb;

		self::$tablename = $wpdb->prefix . 'wpzoom_rating_stars';

		self::$who_can_rate   = WPZOOM_Settings::get( 'wpzoom_rcb_settings_who_can_rate' );
		self::$assets_manager = WPZOOM_Assets_Manager::instance();

		add_action( 'enqueue_block_assets', array( __CLASS__, 'frontend_register_scripts' ) );
		add_action( 'enqueue_block_assets', array( __CLASS__, 'block_assets' ) );

		// Output rating modal in footer
		add_action( 'wp_footer', array( __CLASS__, 'output_rating_modal' ) );

		// Do ajax request
		add_action( 'wp_ajax_wpzoom_user_vote_recipe', array( __CLASS__, 'set_rating' ), 10, 2 );
		add_action( 'wp_ajax_nopriv_wpzoom_user_vote_recipe', array( __CLASS__, 'set_rating' ), 10, 2 );

		// Admin notice for rating modal feature
		add_action( 'init', array( __CLASS__, 'dismiss_rating_modal_notice' ) );
		add_action( 'admin_notices', array( __CLASS__, 'display_rating_modal_notice' ) );
	}

	/**
	 * Registers Front-end block scripts.
	 *
	 * Fired by `enqueu_block_assets` action.
	 *
	 * @since 3.3.0
	 * @access public
	 */
	public static function frontend_register_scripts() {
		wp_register_script(
			'wpzoom-rating-stars-script',
			self::$assets_manager->asset_source( 'js', self::$assets_manager->resolve_frontend_js_filename( 'wpzoom-rating-stars.js' ) ),
			self::$assets_manager->get_dependencies( 'wpzoom-rating-stars-script' ),
			WPZOOM_RCB_VERSION,
			true
		);
	}

	/**
	 * Output the rating modal HTML in the footer.
	 *
	 * @since 3.3.0
	 */
	public static function output_rating_modal() {
		// Only output if not in admin and rating mode is not instant
		if ( is_admin() ) {
			return;
		}

		$rating_mode = self::get_rating_mode();

		// Don't output modal for instant mode
		if ( 'instant' === $rating_mode ) {
			return;
		}

		// Check if ratings are enabled
		if ( ! WPZOOM_Settings::get_rating_star_acces() ) {
			return;
		}

		// Check if we should load assets
		if ( '1' !== WPZOOM_Settings::get( 'wpzoom_rcb_settings_load_assets_on_all_pages' ) && ! is_singular() ) {
			return;
		}

		// Check if the page has recipe blocks
		$should_output = has_block( 'wpzoom-recipe-card/block-recipe-card' ) ||
						 has_block( 'wpzoom-recipe-card/recipe-block-from-posts' ) ||
						 WPZOOM_Assets_Manager::has_reusable_block( 'wpzoom-recipe-card/block-recipe-card' ) ||
						 WPZOOM_Assets_Manager::has_cpt_rcb_shortcode() ||
						 WPZOOM_Assets_Manager::has_cpt_rcb_elementor_widget();

		if ( ! $should_output ) {
			return;
		}

		// Include the modal template
		$template_path = WPZOOM_RCB_PLUGIN_DIR . 'templates/public/rating-modal.php';
		if ( file_exists( $template_path ) ) {
			include $template_path;
		}
	}

	/**
	 * Enqueue Gutenberg block assets for both frontend + backend.
	 *
	 * @since 1.1.0
	 */
	public static function block_assets() {
		if ( is_admin() ) {
			return false;
		}

		// Don't load scripts if the user voting is not allowed
		if ( ! WPZOOM_Settings::get_rating_star_acces() ) {
			return false;
		}

		/**
		 * Load Assets only on single page if option is unchecked
		 *
		 * @since 3.0.2
		 */
		if ( '1' !== WPZOOM_Settings::get( 'wpzoom_rcb_settings_load_assets_on_all_pages' ) && ! is_singular() ) {
			return false;
		}

		$should_enqueue = has_block( 'wpzoom-recipe-card/block-recipe-card' ) || has_block( 'wpzoom-recipe-card/recipe-block-from-posts' ) || WPZOOM_Assets_Manager::has_reusable_block( 'wpzoom-recipe-card/block-recipe-card' ) || WPZOOM_Assets_Manager::has_cpt_rcb_shortcode() || WPZOOM_Assets_Manager::has_cpt_rcb_elementor_widget();

		if ( ! $should_enqueue ) {
			return false;
		}

		$localize_data = self::get_localize_data();

		/**
		 * Load if recipe card block is present in post
		 *
		 * @since 3.0.3
		 */
		wp_enqueue_script( 'wpzoom-rating-stars-script' );

		// Localize variables
		wp_localize_script( 'wpzoom-rating-stars-script', 'wpzoomRatingStars', $localize_data );
	}

	/**
	 * Displays a notice to admin users about the new rating modal feature.
	 *
	 * @since 3.3.0
	 * @return void
	 */
	public static function display_rating_modal_notice() {
		// Show on the screens a free user actually visits. PRO limits this to the
		// Recipes list, which many sites never open.
		$current_screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;

		if ( ! $current_screen ) {
			return;
		}

		$allowed_screens = array( 'edit-wpzoom_rcb', 'plugins', 'dashboard' );

		if ( ! in_array( $current_screen->id, $allowed_screens, true )
			&& false === strpos( $current_screen->id, WPZOOM_RCB_SETTINGS_PAGE ) ) {
			return;
		}

		// Only display for a user with the right permissions
		if ( ! current_user_can( 'edit_posts' ) ) {
			return;
		}

		// Don't display if the user has already dismissed the notice
		$notice_dismissed = get_option( 'wpzoom_rating_modal_notice_v2_dismissed', 'false' );
		if ( 'true' === $notice_dismissed ) {
			return;
		}

		$plugin_settings_url = add_query_arg(
			array(
				'page'      => 'wpzoom-recipe-card-settings',
				'tab'       => 'tab-ratings',
				'highlight' => 'wpzoom_rcb_settings_user_ratings_mode',
			),
			admin_url( 'admin.php' )
		);

		$ignore_message_url = add_query_arg(
			array(
				'wpzoom_dismiss_rating_modal_notice' => 'true',
				'nonce'                              => wp_create_nonce( 'dismiss_rating_modal_notice' ),
			)
		);

		$message = __( '<strong>New in Recipe Card Blocks:</strong> your readers can now rate your recipes, and those ratings are added to your recipe structured data so <strong>Google can show star ratings in search results</strong>. Ratings are enabled by default - you can change how they work, or turn them off, in the settings.', 'recipe-card-blocks-by-wpzoom' );

		$plugin_settings_link = sprintf(
			wp_kses(
				/* translators: Placeholder is the url to the Plugin settings */
				__( '<a href="%s">Review Rating Settings</a>', 'recipe-card-blocks-by-wpzoom' ),
				array(
					'a' => array(
						'href' => array(),
					),
				)
			),
			esc_url( $plugin_settings_url )
		);

		?>
		<div class="notice notice-info is-dismissible wpzoom-rcb-rating-notice" data-dismiss-url="<?php echo esc_url( $ignore_message_url ); ?>">
			<p><?php echo wp_kses_post( $message ); ?></p>
			<p><?php echo $plugin_settings_link; ?></p>
		</div>
		<script>
		( function() {
			document.addEventListener( 'click', function( event ) {
				var button = event.target.closest( '.wpzoom-rcb-rating-notice .notice-dismiss' );

				if ( ! button ) {
					return;
				}

				var notice = button.closest( '.wpzoom-rcb-rating-notice' );

				if ( notice && notice.dataset.dismissUrl ) {
					window.fetch( notice.dataset.dismissUrl, { credentials: 'same-origin' } );
				}
			} );
		}() );
		</script>
		<?php
	}

	/**
	 * Dismiss the rating modal notice.
	 *
	 * @since 3.3.0
	 * @return void
	 */
	public static function dismiss_rating_modal_notice() {
		$dismiss_notice = isset( $_GET['wpzoom_dismiss_rating_modal_notice'] ) ? sanitize_text_field( $_GET['wpzoom_dismiss_rating_modal_notice'] ) : '';
		if ( ! $dismiss_notice ||
			! isset( $_GET['nonce'] ) ||
			! wp_verify_nonce( $_GET['nonce'], 'dismiss_rating_modal_notice' ) ||
			! current_user_can( 'edit_posts' )
		) {
			return;
		}

		update_option( 'wpzoom_rating_modal_notice_v2_dismissed', 'true' );
	}

	/**
	 * Insert rating for recipe into Database.
	 * Verifies the AJAX request, to prevent any processing of requests which are passed in by third-party sites or systems.
	 *
	 * @since 1.1.0
	 */
	public static function set_rating() {
		check_ajax_referer( 'wpzoom-rating-stars-nonce', 'security' );

		$rating = array();

		$rating['recipe_id']    = isset( $_POST['recipe_id'] ) ? intval( $_POST['recipe_id'] ) : 0;
		$rating['rating']       = isset( $_POST['rating'] ) ? intval( $_POST['rating'] ) : 0;
		$rating['user_id']      = get_current_user_id();
		$rating['ip']           = self::get_user_ip();
		$rating['author_name']  = isset( $_POST['rating_name'] ) ? sanitize_text_field( $_POST['rating_name'] ) : '';
		$raw_author_email       = isset( $_POST['rating_email'] ) ? sanitize_text_field( wp_unslash( $_POST['rating_email'] ) ) : '';
		$rating['author_email'] = $raw_author_email ? sanitize_email( $raw_author_email ) : '';
		$rating_comment         = isset( $_POST['rating_comment'] ) ? sanitize_textarea_field( $_POST['rating_comment'] ) : '';
		$private_review_text    = $rating_comment;
		$publish_review_comment = isset( $_POST['publish_review_comment'] ) ? ( '1' === sanitize_text_field( $_POST['publish_review_comment'] ) ) : true;

		// Get the post_id where the comment should be assigned (the page/post where the recipe is displayed)
		$comment_post_id        = isset( $_POST['post_id'] ) ? intval( $_POST['post_id'] ) : $rating['recipe_id'];

		if ( 0 >= $rating['rating'] && 5 < $rating['rating'] ) {
			$response = array(
				'status'  => '204',
				'message' => 'No response',
			);

			wp_send_json_error( $response );
		}

		if ( 'loggedin' === self::$who_can_rate && ! is_user_logged_in() ) {
			$response = array(
				'status'  => '403',
				'message' => __( 'Only logged in users can rate recipes', 'recipe-card-blocks-by-wpzoom' ),
			);

			wp_send_json_error( $response );
		}

		$rating_mode          = self::get_rating_mode();
		$is_modal_rating_mode = ( 'modal' === $rating_mode );
		$force_comment        = 'disabled';
		$anonymous_review_mode = false;
		$rating['review_text'] = '';

		// Modal-only validation and review/comment handling.
		if ( $is_modal_rating_mode ) {
			$show_name     = WPZOOM_Settings::get( 'wpzoom_rcb_settings_rating_modal_show_name' );
			$show_email    = WPZOOM_Settings::get( 'wpzoom_rcb_settings_rating_modal_show_email' );
			$require_name  = WPZOOM_Settings::get( 'wpzoom_rcb_settings_rating_modal_require_name' );
			$require_email = WPZOOM_Settings::get( 'wpzoom_rcb_settings_rating_modal_require_email' );
			$force_comment = WPZOOM_Settings::get( 'wpzoom_rcb_settings_rating_modal_force_comment' );
			$show_name     = false === $show_name ? '1' : (string) $show_name;
			$show_email    = false === $show_email ? '1' : (string) $show_email;
			$require_name  = (string) $require_name;
			$require_email = (string) $require_email;
			$anonymous_review_mode = ( '1' !== $show_name && '1' !== $show_email );

			// Backward compatibility: check old setting if new one is not set.
			if ( empty( $force_comment ) ) {
				$require_comment_old = WPZOOM_Settings::get( 'wpzoom_rcb_settings_rating_modal_require_comment' );
				if ( '1' === $require_comment_old ) {
					$force_comment = 'always';
				} else {
					$force_comment = 'disabled';
				}
			}

			// In anonymous mode (both Name/Email hidden), keep review text enabled but force moderation.
			if ( $anonymous_review_mode ) {
				$publish_review_comment = true;
			}

			if ( ! $publish_review_comment ) {
				$force_comment  = 'disabled';
				$rating_comment = '';
			}

			$rating['review_text'] = ( ! $publish_review_comment && ! empty( $private_review_text ) ) ? $private_review_text : '';

			if ( '1' === $show_name && '1' === $require_name && empty( $rating['author_name'] ) ) {
				$response = array(
					'status'  => '400',
					'message' => __( 'Please enter your name', 'recipe-card-blocks-by-wpzoom' ),
				);
				wp_send_json_error( $response );
			}

			if ( '1' === $show_email && '1' === $require_email && empty( $rating['author_email'] ) ) {
				$response = array(
					'status'  => '400',
					'message' => __( 'Please enter your email', 'recipe-card-blocks-by-wpzoom' ),
				);
				wp_send_json_error( $response );
			}

			if ( '1' === $show_email && ! empty( $raw_author_email ) && ! is_email( $raw_author_email ) ) {
				$response = array(
					'status'  => '400',
					'message' => __( 'Please enter a valid email', 'recipe-card-blocks-by-wpzoom' ),
				);
				wp_send_json_error( $response );
			}
		} else {
			// Instant and Jump-to-comments modes should never require modal identity fields.
			$rating['author_name']  = '';
			$rating['author_email'] = '';
			$rating_comment         = '';
			$publish_review_comment = false;
		}

		// Check if comment is required based on force_comment setting and rating value
		$comment_required = false;
		if ( 'always' === $force_comment ) {
			$comment_required = true;
		} elseif ( is_numeric( $force_comment ) && $rating['rating'] <= intval( $force_comment ) ) {
			$comment_required = true;
		}

		if ( $comment_required && empty( $rating_comment ) ) {
			$response = array(
				'status'  => '400',
				'message' => __( 'Please leave a comment', 'recipe-card-blocks-by-wpzoom' ),
			);
			wp_send_json_error( $response );
		}

		// Try resolving comment post from referrer when recipe block is embedded in another post/page.
		$referer_post_id = 0;
		if ( $comment_post_id === $rating['recipe_id'] ) {
			$referer_url = wp_get_referer();
			if ( $referer_url ) {
				$referer_post_id = url_to_postid( $referer_url );
				if ( $referer_post_id > 0 && get_post( $referer_post_id ) ) {
					$comment_post_id = $referer_post_id;
				}
			}
		}

		// If a comment is provided or required, check if comments are open on the target post
		if ( ! empty( $rating_comment ) || $comment_required ) {
			if ( ! comments_open( $comment_post_id ) ) {
				$response = array(
					'status'  => '403',
					'message' => __( 'Comments are closed on this page. Your review text cannot be published.', 'recipe-card-blocks-by-wpzoom' ),
				);
				wp_send_json_error( $response );
			}
		}

		// Basic abuse mitigation. Akismet-backed spam filtering is a PRO feature;
		// free caps how often a single IP may submit instead.
		if ( ! self::check_rate_limit() ) {
			$response = array(
				'status'  => '429',
				'message' => __( 'Too many rating submissions. Please try again later.', 'recipe-card-blocks-by-wpzoom' ),
			);
			wp_send_json_error( $response );
		}

		// If comment text is provided, create a WordPress comment
		if ( ! empty( $rating_comment ) ) {
			$comment_id = self::create_rating_comment( $rating, $rating_comment, $comment_post_id, $anonymous_review_mode );
			if ( ! $comment_id ) {
				$response = array(
					'status'  => '500',
					'message' => __( 'Your rating was received, but we could not save your review text. Please try again.', 'recipe-card-blocks-by-wpzoom' ),
				);
				wp_send_json_error( $response );
			}
			$rating['comment_id'] = $comment_id;
		}

		$result = WPZOOM_Rating_DB::add_or_update_rating( $rating );

		if ( $result ) {
			$response = array(
				'status'       => '200',
				'message'      => 'OK',
				'rating_avg'   => self::get_rating_average( $rating['recipe_id'] ),
				'rating_total' => self::get_total_votes( $rating['recipe_id'] ),
			);

			// Set or update cookie for easy access.
			if ( ! headers_sent() ) {
				setcookie(
					'wpzoom-user-rating-recipe-' . $rating['recipe_id'],
					$rating['rating'],
					array(
						'expires'  => time() + 60 * 60 * 24 * 30,
						'path'     => '/',
						'secure'   => is_ssl(),
						'samesite' => 'Lax',
					)
				);
			}

			//Cler cache for this post
			//WP Fastest Cache
			if( function_exists( 'wpfc_clear_post_cache_by_id' ) ) {
				wpfc_clear_post_cache_by_id( $rating['recipe_id'] );
			}

			//WP Rocket
			if( function_exists( 'rocket_clean_post' ) ) {
				rocket_clean_post( $rating['recipe_id'] );
			}

			//W3 Total Cache
			if( function_exists( 'w3tc_flush_post' ) ) {
				w3tc_flush_post( $rating['recipe_id'] );
			}

			//WP Super Cache
			if( function_exists( 'wpsc_delete_post_cache' ) ) {
				wpsc_delete_post_cache( $rating['recipe_id'] );
			}

			//LiteSpeed Cache plugin
			if ( function_exists( 'is_plugin_active' ) && is_plugin_active( 'litespeed-cache/litespeed-cache.php' ) ) {
				do_action( 'litespeed_purge_post', $rating['recipe_id'] );
			}

			/**
			 * WP-Optimize only exposes a whole-site purge, far too heavy to run
			 * on every vote. Opt in through this filter if you need it.
			 *
			 * @since 3.5.0
			 */
			if ( class_exists( 'WPO_Page_Cache' ) && function_exists( 'WP_Optimize' )
				&& apply_filters( 'wpzoom_rcb_rating_purge_wp_optimize', false, $rating['recipe_id'] ) ) {
				WP_Optimize()->get_page_cache()->purge();
			}

			wp_send_json_success( $response );
		}
	}

	/**
	 * Create a WordPress comment from a rating submission.
	 *
	 * @since 3.3.0
	 * @param array    $rating           The rating data.
	 * @param string   $comment_text     The comment text from the modal.
	 * @param int|null $comment_post_id  The post ID where the comment should be assigned.
	 * @param bool     $force_unapproved Force comment to be saved as pending moderation.
	 * @return int|false The comment ID on success, false on failure.
	 */
	public static function create_rating_comment( $rating, $comment_text, $comment_post_id = null, $force_unapproved = false ) {
		// Use provided post_id, or fall back to recipe_id
		$post_id = $comment_post_id ? intval( $comment_post_id ) : $rating['recipe_id'];

		// Validate the post exists
		$target_post = get_post( $post_id );
		if ( ! $target_post ) {
			// If provided post doesn't exist, fall back to recipe_id
			$post_id = $rating['recipe_id'];
			$target_post = get_post( $post_id );
			if ( ! $target_post ) {
				return false;
			}
		}

		// Check if comments are open on the target post
		if ( ! comments_open( $post_id ) ) {
			// Comments are closed, don't create a comment
			return false;
		}

		// Prepare comment data
		$author_name  = ! empty( $rating['author_name'] ) ? $rating['author_name'] : __( 'Guest', 'recipe-card-blocks-by-wpzoom' );
		$author_email = ! empty( $rating['author_email'] ) ? $rating['author_email'] : '';

		// If user is logged in, use their info
		if ( $rating['user_id'] ) {
			$user = get_user_by( 'id', $rating['user_id'] );
			if ( $user ) {
				$author_name  = $user->display_name;
				$author_email = $user->user_email;
			}
		}

		$comment_data = array(
			'comment_post_ID'      => $post_id,
			'comment_content'      => $comment_text,
			'comment_author'       => $author_name,
			'comment_author_email' => $author_email,
			'comment_author_url'   => '',
			'comment_author_IP'    => $rating['ip'],
			'comment_type'         => 'comment',
			'comment_parent'       => 0,
			'comment_date'         => current_time( 'mysql' ),
			'comment_date_gmt'     => current_time( 'mysql', 1 ),
			'user_id'              => $rating['user_id'],
		);

		// In anonymous mode, always keep review pending so site owner can moderate safely.
		if ( $force_unapproved ) {
			$comment_data['comment_approved'] = 0;
		} else {
			// Use WordPress comment moderation settings
			$comment_approved = wp_allow_comment( $comment_data, true );

			// Handle WP_Error from wp_allow_comment
			if ( is_wp_error( $comment_approved ) ) {
				// Default to pending moderation if there's an error
				$comment_data['comment_approved'] = 0;
			} else {
				$comment_data['comment_approved'] = $comment_approved;
			}
		}

		// Insert the comment
		$comment_id = wp_insert_comment( $comment_data );

		if ( $comment_id && ! is_wp_error( $comment_id ) ) {
			// Store the rating in comment meta for display
			update_comment_meta( $comment_id, 'wpzoom-rcb-comment-rating', $rating['rating'] );
			return $comment_id;
		}

		return false;
	}

	/**
	 * Get user ratings for a specific recipe.
	 *
	 * @since   3.2.0
	 * @param   int $recipe_ID ID of the recipe.
	 */
	public static function get_ratings_for( $recipe_ID ) {
		$recipe_ID = intval( $recipe_ID );

		$ratings = array();

		if ( $recipe_ID ) {
			$user_ratings = WPZOOM_Rating_DB::get_ratings(
				array(
					'where' => 'recipe_id = ' . $recipe_ID,
				)
			);

			$ratings = $user_ratings['ratings'];
		}

		return $ratings;
	}

	/**
	 * Get star SVG markup for rating display (from registry: far fa-star, fas fa-star, far fa-star-half).
	 *
	 * @param string $type One of 'full', 'empty', 'half'.
	 * @return string SVG markup or empty string if not in registry.
	 */
	public static function get_rating_star_svg( $type ) {
		if ( ! isset( self::$star_svgs[ $type ] ) ) {
			return '';
		}

		return self::with_default_star_svg_size( self::$star_svgs[ $type ] );
	}

	/**
	 * Ensure rating star SVGs have intrinsic dimensions to avoid oversized rendering before CSS loads.
	 *
	 * @param string $svg SVG markup.
	 * @return string SVG markup with width/height attributes.
	 */
	private static function with_default_star_svg_size( $svg ) {
		if ( ! is_string( $svg ) || $svg === '' || stripos( $svg, '<svg' ) === false ) {
			return '';
		}

		if ( ! preg_match( '/<svg\b[^>]*\bwidth\s*=/i', $svg ) ) {
			$svg = preg_replace( '/<svg\b/i', '<svg width="1em"', $svg, 1 );
		}
		if ( ! preg_match( '/<svg\b[^>]*\bheight\s*=/i', $svg ) ) {
			$svg = preg_replace( '/<svg\b/i', '<svg height="1em"', $svg, 1 );
		}

		return $svg;
	}

	/**
	 * Build one rating star <li> with optional inline SVG (full, empty, half, or partial).
	 *
	 * @param string $kind     One of 'full', 'empty', 'half', 'one-fourth', 'three-quarters'.
	 * @param string $svg_full Full star SVG markup.
	 * @param string $svg_empty Empty star SVG markup.
	 * @param string $svg_half Half star SVG markup.
	 * @param bool   $use_svg  Whether to output SVG (otherwise font-based fallback).
	 * @return string HTML for one <li>.
	 */
	private static function rating_star_li( $kind, $svg_full, $svg_empty, $svg_half, $use_svg ) {
		$class = 'wpz-star-icon';
		if ( $kind === 'full' ) {
			$class .= ' wpz-full-star';
		} elseif ( $kind === 'empty' ) {
			$class .= ' wpz-empty-star';
		} elseif ( $kind === 'half' ) {
			$class .= ' wpz-one-half-star wpz-full-star';
		} elseif ( $kind === 'one-fourth' ) {
			$class .= ' wpz-one-fourth-star wpz-full-star';
		} elseif ( $kind === 'three-quarters' ) {
			$class .= ' wpz-three-quarters-star wpz-full-star';
		}

		if ( ! $use_svg ) {
			return '<li class="' . esc_attr( $class ) . '"></li>';
		}

		// Each li has both full and empty SVGs so JS hover (syncRating) can toggle .wpz-full-star / .wpz-empty-star and CSS shows the right one.
		$partial_kind = in_array( $kind, array( 'half', 'one-fourth', 'three-quarters' ), true ) ? $kind : 'half';
		$partial_inner = '<span class="wpzoom-rating-star-outline">' . $svg_empty . '</span><span class="wpzoom-rating-star-filled">' . $svg_full . '</span>';
		$partial_markup = '<span class="wpzoom-rating-star-svg wpzoom-rating-star-partial wpzoom-rating-star-' . esc_attr( $partial_kind ) . '">' . $partial_inner . '</span>';

		$inner = '<span class="wpzoom-rating-star-svg wpzoom-rating-star-empty-svg">' . $svg_empty . '</span>';
		$inner .= '<span class="wpzoom-rating-star-svg wpzoom-rating-star-full-svg">' . $svg_full . '</span>';
		$inner .= $partial_markup;

		return '<li class="' . esc_attr( $class ) . '">' . $inner . '</li>';
	}

	/**
	 * Get rating form HTML.
	 *
	 * @param string|number $recipe_ID The recipe id.
	 * @since 1.1.0
	 */
	public static function get_rating_form( $recipe_ID ) {
		$output             = '';
		$rating_stars_items = '';
		$tooltip_message    = '';
		$data_user_can_rate = 'data-user-can-rate="1"';

		// Check if ratings are enabled before making database queries
		if ( ! WPZOOM_Settings::get_rating_star_acces() ) {
			return '';
		}

		// Get the average vote number and check if user has voted for this post
		$average     = self::get_rating_average( $recipe_ID );
		$total_votes = self::get_total_votes( $recipe_ID );
		$user_voted  = self::check_user_rating( $recipe_ID );

		// Important: do not merge current post totals here.
		// get_rating_average/get_total_votes already aggregate with:
		// (recipe_id = X OR post_id = X)
		// Merging again causes double counting for embedded recipe cards.

		//$average = '4.45';

		$integer_average = intval( $average );
		$float_average   = ( $average - $integer_average );

		$svg_full  = self::get_rating_star_svg( 'full' );
		$svg_empty = self::get_rating_star_svg( 'empty' );
		$svg_half  = self::get_rating_star_svg( 'half' );
		$use_svg   = ( $svg_full !== '' && $svg_empty !== '' );

		if( 0 < $float_average ) {

			//Set the full stars
			for ( $i = 1; $i <= $integer_average; $i++ ) {
				$rating_stars_items .= self::rating_star_li( 'full', $svg_full, $svg_empty, $svg_half, $use_svg );
			}

			// Check if the average is between 0.05 and 0.35
			if ( 0.05 < $float_average && 0.35 >= $float_average ) {
				$rating_stars_items .= self::rating_star_li( 'one-fourth', $svg_full, $svg_empty, $svg_half, $use_svg );
			}
			elseif ( 0.35 < $float_average && 0.65 >= $float_average ) {
				$rating_stars_items .= self::rating_star_li( 'half', $svg_full, $svg_empty, $svg_half, $use_svg );
			}
			elseif ( 0.65 < $float_average && 0.95 >= $float_average ) {
				$rating_stars_items .= self::rating_star_li( 'three-quarters', $svg_full, $svg_empty, $svg_half, $use_svg );
			}
			elseif( 0.95 < $float_average ) {
				$rating_stars_items .= self::rating_star_li( 'full', $svg_full, $svg_empty, $svg_half, $use_svg );
			}
			for ( $i = 1; $i <= ( 4 - $integer_average ) ; $i++ ) {
				$rating_stars_items .= self::rating_star_li( 'empty', $svg_full, $svg_empty, $svg_half, $use_svg );
			}
		}
		else {
			for ( $i = 1; $i <= 5; $i++ ) {
				if ( $i <= $average ) {
					$rating_stars_items .= self::rating_star_li( 'full', $svg_full, $svg_empty, $svg_half, $use_svg );
				} else {
					$rating_stars_items .= self::rating_star_li( 'empty', $svg_full, $svg_empty, $svg_half, $use_svg );
				}
			}
		}

		$average_content = sprintf(
			'<span class="wpzoom-rating-average">%.1f</span> %s <span class="wpzoom-rating-total-votes">%d</span> %s',
			$average,
			__( 'from', 'recipe-card-blocks-by-wpzoom' ),
			intval( $total_votes ),
			_n( 'vote', 'votes', intval( $total_votes ), 'recipe-card-blocks-by-wpzoom' )
		);

		if ( 'loggedin' === self::$who_can_rate && ! is_user_logged_in() ) {
			$data_user_can_rate = 'data-user-can-rate="0"';
			$tooltip_message    = __( 'Only logged in users can rate recipes', 'recipe-card-blocks-by-wpzoom' );
		}

		$rating_stars_classnames = 'wpzoom-rating-stars';

		if ( $user_voted ) {
			$rating_stars_classnames .= ' wpzoom-recipe-user-rated';
		}	

		// Get the current post ID for comment assignment (different from recipe_id for embedded recipes).
		// Only trust get_queried_object_id() on singular views - on an archive it returns a
		// term or post-type-archive ID, which would attach comments to a bogus target.
		$current_post_id = is_singular() ? get_queried_object_id() : 0;
		if ( ! $current_post_id ) {
			$current_post_id = get_the_ID();
		}
		// Fallback to recipe_id if we still don't have a valid post ID
		if ( ! $current_post_id ) {
			$current_post_id = $recipe_ID;
		}

		$output = sprintf(
			'<div class="%1$s-container" data-rating="%6$s" data-rating-total="%7$d" data-recipe-id="%8$d" data-post-id="%9$d" %4$s>
				<ul class="%10$s">%2$s</ul><span class="%1$s-average">%3$s</span>
				<em class="%1$s-tooltip">%5$s</em>
			</div>',
			'wpzoom-rating-stars',
			$rating_stars_items,
			$average_content,
			$data_user_can_rate,
			$tooltip_message,
			$average,
			intval( $total_votes ),
			intval( $recipe_ID ),
			intval( $current_post_id ),
			esc_attr( $rating_stars_classnames )
		);

		// Display only average content for AMP template
		if ( WPZOOM_Recipe_Card_Block_Gutenberg::is_AMP() ) {
			$output = self::get_rating_star( $recipe_ID, __( 'Recipe rating: ', 'recipe-card-blocks-by-wpzoom' ), true );
		}

		return $output;
	}

	/**
	 * Get rating star HTML.
	 *
	 * @param string|number $recipe_ID The recipe id.
	 * @param string        $label The custom label text for rating.
	 * @param boolean       $container Wrap rating to div container?
	 * @since 1.1.0
	 */
	public static function get_rating_star( $recipe_ID, $label = '', $container = false ) {
		// Check if ratings are enabled before making database queries
		if ( ! WPZOOM_Settings::get_rating_star_acces() ) {
			return '';
		}

		// Check if user voted, use the full icon or outline icon if not
		$user_vote = self::check_user_rating( $recipe_ID );

		if ( $user_vote ) {
			$rate_icon = ' icon-star-full';
		} else {
			$rate_icon = ' icon-star';
		}

		$average         = self::get_rating_average( $recipe_ID );
		$total_votes     = self::get_total_votes( $recipe_ID );
		$average_content = $average > 0 ? sprintf( __( '%1$s from %2$s votes', 'recipe-card-blocks-by-wpzoom' ), "<i class=\"wpzoom-rating-average\">{$average}</i>", "<i class=\"wpzoom-rating-total-votes\">{$total_votes}</i>" ) : 'N/A';

		$output = sprintf(
			'<span class="%s-average %s">%s</span>',
			'wpzoom-rating-stars',
			$rate_icon,
			$label . $average_content
		);

		if ( $container ) {
			$output = sprintf(
				'<div class="%s-container">%s</div>',
				'wpzoom-rating-stars',
				$output
			);
		}

		return $output;
	}

	/**
	 * Get rating average.
	 *
	 * @param string|number $recipe_ID The recipe id.
	 * @since 1.1.0
	 * @return number The average number of sql results.
	 */
	public static function get_rating_average( $recipe_ID ) {
		if ( ! $recipe_ID ) {
			return;
		}

		$rating_average = WPZOOM_Rating_DB::get_rating_average(
			array(
				'where' => '(recipe_id = ' . $recipe_ID . ' OR post_id = ' . $recipe_ID . ') AND approved = 1',
			)
		);

		return $rating_average;
	}

	/**
	 * Get total number of recipe votes.
	 *
	 * @param string|number $recipe_ID The recipe id.
	 * @since 1.1.0
	 * @return number The total number of sql results.
	 */
	public static function get_total_votes( $recipe_ID ) {
		if ( ! $recipe_ID ) {
			return;
		}

		$ratings = WPZOOM_Rating_DB::get_ratings(
			array(
				'where' => '(recipe_id = ' . $recipe_ID . ' OR post_id = ' . $recipe_ID . ') AND approved = 1',
			)
		);

		return $ratings['total'];
	}

	/**
	 * Get total number of recipe reviews (ratings with comments).
	 *
	 * @param string|number $recipe_ID The recipe id.
	 * @since 3.3.0
	 * @return number The total number of comment-based ratings.
	 */
	public static function get_review_count( $recipe_ID ) {
		if ( ! $recipe_ID ) {
			return 0;
		}

		$ratings = WPZOOM_Rating_DB::get_ratings(
			array(
				'where' => '(recipe_id = ' . $recipe_ID . ' OR post_id = ' . $recipe_ID . ') AND approved = 1 AND comment_id > 0',
			)
		);

		return $ratings['total'];
	}

	/**
	 * Check if user has rated recipe.
	 *
	 * @param string|number $recipe_ID The recipe id.
	 * @since 1.1.0
	 * @return boolean
	 */
	public static function check_user_rating( $recipe_ID ) {
		if ( isset( $_COOKIE[ 'wpzoom-user-rating-recipe-' . $recipe_ID ] ) ) {
			return intval( $_COOKIE[ 'wpzoom-user-rating-recipe-' . $recipe_ID ] );
		}

		$rating = false;

		$ip   = self::get_user_ip();
		$user = get_current_user_id();

		$user_ratings = self::get_ratings_for( $recipe_ID );

		foreach ( $user_ratings as $user_rating ) {
			if ( ! $user && 'unknown' !== $ip && $ip === $user_rating->ip ) {
				$rating = $user_rating->rating;
			} elseif ( $user && $user === $user_rating->user_id ) {
				$rating = $user_rating->rating;
			}
		}

		return $rating;
	}

	/**
	 * Localize variables to script
	 *
	 * @since 2.3.1
	 * @return array
	 */
	public static function get_localize_data() {
		global $post;

		$current_user = wp_get_current_user();

		// Get force_comment setting with backward compatibility
		$force_comment = WPZOOM_Settings::get( 'wpzoom_rcb_settings_rating_modal_force_comment' );
		if ( empty( $force_comment ) ) {
			$require_comment_old = WPZOOM_Settings::get( 'wpzoom_rcb_settings_rating_modal_require_comment' );
			$force_comment = ( '1' === $require_comment_old ) ? 'always' : 'disabled';
		}

		$localize_data = array(
			'ajaxurl'         => admin_url( 'admin-ajax.php' ),
			'ajax_nonce'      => wp_create_nonce( 'wpzoom-rating-stars-nonce' ),
			'current_post_id' => isset( $post->ID ) ? intval( $post->ID ) : 0,
			'rating_mode'     => self::get_rating_mode(),
			'show_name'       => false === WPZOOM_Settings::get( 'wpzoom_rcb_settings_rating_modal_show_name' ) ? '1' : WPZOOM_Settings::get( 'wpzoom_rcb_settings_rating_modal_show_name' ),
			'show_email'      => false === WPZOOM_Settings::get( 'wpzoom_rcb_settings_rating_modal_show_email' ) ? '1' : WPZOOM_Settings::get( 'wpzoom_rcb_settings_rating_modal_show_email' ),
			'require_name'    => (string) WPZOOM_Settings::get( 'wpzoom_rcb_settings_rating_modal_require_name' ),
			'require_email'   => (string) WPZOOM_Settings::get( 'wpzoom_rcb_settings_rating_modal_require_email' ),
			'force_comment'   => $force_comment,
			'user_name'       => $current_user->ID ? $current_user->display_name : '',
			'user_email'      => $current_user->ID ? $current_user->user_email : '',
			'strings'         => array(
				'recipe_rating'    => __( 'Recipe rating', 'recipe-card-blocks-by-wpzoom' ),
				'top_rated'        => __( 'Top rated', 'recipe-card-blocks-by-wpzoom' ),
				'rating_required'  => __( 'Please select a rating', 'recipe-card-blocks-by-wpzoom' ),
				'name_required'    => __( 'Please enter your name', 'recipe-card-blocks-by-wpzoom' ),
				'email_required'   => __( 'Please enter your email', 'recipe-card-blocks-by-wpzoom' ),
				'email_invalid'    => __( 'Please enter a valid email', 'recipe-card-blocks-by-wpzoom' ),
				'comment_required' => __( 'Please leave a comment', 'recipe-card-blocks-by-wpzoom' ),
			),
		);

		return $localize_data;
	}

	/**
	 * The active rating mode.
	 *
	 * The modal and jump-to-comments flows are PRO features, so free always
	 * reports 'instant'. Gating the settings field alone is not enough: the
	 * anti-tamper write-back only resets a disabled field when the settings
	 * page is rendered, so a value stored earlier would otherwise keep working.
	 *
	 * @since 3.5.0
	 * @return string One of 'instant', 'modal', 'jump_to_comments'.
	 */
	public static function get_rating_mode() {
		if ( ! WPZOOM_RCB_HAS_PRO ) {
			return 'instant';
		}

		$mode = WPZOOM_Settings::get( 'wpzoom_rcb_settings_user_ratings_mode' );

		return $mode ? $mode : 'modal';
	}

	/**
	 * Cap how many rating submissions a single IP may make per hour.
	 *
	 * PRO screens submissions with Akismet; free relies on this together with
	 * the per-IP de-duplication in WPZOOM_Rating_DB::add_or_update_rating().
	 * The counter tracks submissions rather than stored ratings, so it cannot be
	 * sidestepped by re-rating the same recipe repeatedly.
	 *
	 * @since 3.5.0
	 * @return boolean True when the submission is within the limit.
	 */
	public static function check_rate_limit() {
		/**
		 * Number of rating submissions allowed per IP per hour.
		 *
		 * @since 3.5.0
		 * @param int $limit Defaults to 20. Return 0 to disable the limit.
		 */
		$limit = (int) apply_filters( 'wpzoom_rcb_rating_rate_limit', 20 );

		if ( $limit <= 0 ) {
			return true;
		}

		$key  = 'wpzoom_rcb_rate_' . md5( self::get_user_ip() );
		$hits = (int) get_transient( $key );

		if ( $hits >= $limit ) {
			return false;
		}

		set_transient( $key, $hits + 1, HOUR_IN_SECONDS );

		return true;
	}

	/**
	 * Get the IP address of the current user.
	 * Source: http://stackoverflow.com/questions/6717926/function-to-get-user-ip-address
	 *
	 * @since    3.2.0
	 */
	public static function get_user_ip() {
		foreach ( array( 'REMOTE_ADDR', 'HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED' ) as $key ) {
			if ( array_key_exists( $key, $_SERVER ) === true ) {
				foreach ( array_map( 'trim', explode( ',', $_SERVER[ $key ] ) ) as $ip ) { // Input var ok.
					if ( filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE ) !== false ) {
						return $ip;
					}
				}
			}
		}
		return 'unknown';
	}
}

WPZOOM_Rating_Stars::init();
