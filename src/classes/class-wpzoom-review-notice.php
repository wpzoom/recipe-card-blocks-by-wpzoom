<?php
/**
 * Milestone-triggered review request notice.
 *
 * Celebrates the user's progress (published recipes) and asks for a
 * wordpress.org review at the moment the plugin has proven its value.
 *
 * @since   3.5.0
 * @package WPZOOM_Recipe_Card_Blocks
 */

if ( ! class_exists( 'WPZOOM_Review_Notice' ) ) {
	class WPZOOM_Review_Notice {

		const REVIEW_URL        = 'https://wordpress.org/support/plugin/recipe-card-blocks-by-wpzoom/reviews/?filter=5#new-post';
		const DISMISS_META_KEY  = 'wpzoom_rcb_review_notice_dismissed';
		const SNOOZE_META_KEY   = 'wpzoom_rcb_review_notice_snooze_until';
		const FIRST_SEEN_OPTION = 'wpzoom_rcb_first_seen';
		const MIN_RECIPES       = 5;
		const MIN_DAYS_ACTIVE   = 7;
		const SNOOZE_DAYS       = 30;

		/**
		 * Initialize the notice hooks.
		 */
		public static function init() {
			// Track when the plugin was first seen, so we don't ask brand-new users.
			if ( is_admin() && ! get_option( self::FIRST_SEEN_OPTION ) ) {
				add_option( self::FIRST_SEEN_OPTION, time() );
			}

			global $pagenow;

			$is_dashboard     = 'index.php' === $pagenow;
			$is_recipes_page  = 'edit.php' === $pagenow && isset( $_GET['post_type'] ) && 'wpzoom_rcb' === $_GET['post_type'];
			$is_settings_page = 'admin.php' === $pagenow && isset( $_GET['page'] ) && 'wpzoom-recipe-card-settings' === $_GET['page'];

			if ( is_admin() && ( $is_dashboard || $is_recipes_page || $is_settings_page ) ) {
				add_action( 'admin_notices', array( __CLASS__, 'maybe_show_notice' ) );
			}

			add_action( 'wp_ajax_rcb_dismiss_review_notice', array( __CLASS__, 'dismiss_notice' ) );
		}

		/**
		 * Show the notice when all milestone conditions are met.
		 */
		public static function maybe_show_notice() {
			if ( ! current_user_can( 'manage_options' ) ) {
				return;
			}

			$user_id = get_current_user_id();

			if ( get_user_meta( $user_id, self::DISMISS_META_KEY, true ) ) {
				return;
			}

			$snooze_until = (int) get_user_meta( $user_id, self::SNOOZE_META_KEY, true );
			if ( $snooze_until && time() < $snooze_until ) {
				return;
			}

			$first_seen = (int) get_option( self::FIRST_SEEN_OPTION );
			if ( ! $first_seen || ( time() - $first_seen ) < self::MIN_DAYS_ACTIVE * DAY_IN_SECONDS ) {
				return;
			}

			$recipe_count = wp_count_posts( 'wpzoom_rcb' );
			$total        = isset( $recipe_count->publish ) ? (int) $recipe_count->publish : 0;

			if ( $total < self::MIN_RECIPES ) {
				return;
			}

			self::render_notice( $total );
		}

		/**
		 * Handle the AJAX dismiss request.
		 */
		public static function dismiss_notice() {
			check_ajax_referer( 'rcb_dismiss_review_notice', 'security' );

			$user_id = get_current_user_id();
			$type    = isset( $_GET['dismiss_type'] ) ? sanitize_key( $_GET['dismiss_type'] ) : 'later';

			if ( 'permanent' === $type ) {
				update_user_meta( $user_id, self::DISMISS_META_KEY, true );
			} else {
				update_user_meta( $user_id, self::SNOOZE_META_KEY, time() + self::SNOOZE_DAYS * DAY_IN_SECONDS );
			}

			wp_send_json_success();
		}

		/**
		 * Render the review request notice.
		 *
		 * @param int $recipe_count Number of published recipes.
		 */
		private static function render_notice( $recipe_count ) {
			$nonce = wp_create_nonce( 'rcb_dismiss_review_notice' );
			?>
			<div id="wpzoom-rcb-review-notice" class="notice notice-info is-dismissible">
				<div class="wpzoom-rcb-review-notice-inner">
					<div class="wpzoom-rcb-review-notice-icon">🎉</div>
					<div class="wpzoom-rcb-review-notice-content">
						<h3>
							<?php
							printf(
								/* translators: %d: number of published recipes */
								esc_html( _n( 'You\'ve published %d recipe with Recipe Card Blocks!', 'You\'ve published %d recipes with Recipe Card Blocks!', $recipe_count, 'recipe-card-blocks-by-wpzoom' ) ),
								absint( $recipe_count )
							);
							?>
						</h3>
						<p><?php esc_html_e( 'Your recipes include structured data and ratings, so they\'re eligible for stars in Google search results. If the plugin is helping your food blog, would you mind taking a moment to leave a quick review? It really helps other food bloggers discover it — and keeps us improving the free version.', 'recipe-card-blocks-by-wpzoom' ); ?></p>
						<p class="wpzoom-rcb-review-notice-actions">
							<a href="<?php echo esc_url( self::REVIEW_URL ); ?>" target="_blank" rel="noopener noreferrer" class="button button-primary wpzoom-rcb-review-btn" data-dismiss="permanent"><?php esc_html_e( 'Yes, I\'ll leave a review', 'recipe-card-blocks-by-wpzoom' ); ?> ★★★★★</a>
							<a href="#" class="button wpzoom-rcb-review-later" data-dismiss="later"><?php esc_html_e( 'Maybe later', 'recipe-card-blocks-by-wpzoom' ); ?></a>
							<a href="#" class="wpzoom-rcb-review-done" data-dismiss="permanent"><?php esc_html_e( 'I already left a review', 'recipe-card-blocks-by-wpzoom' ); ?></a>
						</p>
					</div>
				</div>
			</div>
			<style>
				#wpzoom-rcb-review-notice {
					border-left-color: #E1581A;
					padding: 0;
				}

				.wpzoom-rcb-review-notice-inner {
					display: flex;
					align-items: flex-start;
					padding: 14px 12px;
					gap: 14px;
				}

				.wpzoom-rcb-review-notice-icon {
					font-size: 32px;
					line-height: 1.2;
				}

				.wpzoom-rcb-review-notice-content h3 {
					margin: 0 0 6px;
					font-size: 14px;
				}

				.wpzoom-rcb-review-notice-content p {
					margin: 0 0 10px;
					font-size: 13px;
					color: #50575e;
					max-width: 760px;
				}

				.wpzoom-rcb-review-notice-actions {
					display: flex;
					align-items: center;
					gap: 12px;
				}

				.wpzoom-rcb-review-btn.button.button-primary {
					background: #E1581A;
					border-color: #c94e16;
				}

				.wpzoom-rcb-review-btn.button.button-primary:hover {
					background: #c94e16;
					border-color: #b0430f;
				}

				.wpzoom-rcb-review-done {
					font-size: 12px;
					color: #50575e;
				}
			</style>
			<script type="text/javascript">
				jQuery( document ).ready( function( $ ) {
					function rcbDismissReviewNotice( type ) {
						$.ajax( {
							url: ajaxurl,
							type: 'GET',
							data: {
								action: 'rcb_dismiss_review_notice',
								security: '<?php echo esc_js( $nonce ); ?>',
								dismiss_type: type
							}
						} );
					}

					$( document ).on( 'click', '#wpzoom-rcb-review-notice .notice-dismiss, #wpzoom-rcb-review-notice .wpzoom-rcb-review-later', function( e ) {
						if ( $( this ).hasClass( 'wpzoom-rcb-review-later' ) ) {
							e.preventDefault();
						}
						rcbDismissReviewNotice( 'later' );
						$( '#wpzoom-rcb-review-notice' ).fadeOut();
					} );

					$( document ).on( 'click', '#wpzoom-rcb-review-notice .wpzoom-rcb-review-btn, #wpzoom-rcb-review-notice .wpzoom-rcb-review-done', function( e ) {
						if ( $( this ).hasClass( 'wpzoom-rcb-review-done' ) ) {
							e.preventDefault();
						}
						rcbDismissReviewNotice( 'permanent' );
						$( '#wpzoom-rcb-review-notice' ).fadeOut();
					} );
				} );
			</script>
			<?php
		}
	}

	WPZOOM_Review_Notice::init();
}
