<?php
/**
 * Display an upgrade notice on the Recipes admin page for free version users.
 *
 * @since   3.4.14
 * @package WPZOOM_Recipe_Card_Blocks
 */

if ( ! class_exists( 'WPZOOM_Recipes_Page_Notice' ) ) {
	class WPZOOM_Recipes_Page_Notice {

		const UPGRADE_LINK = 'https://recipecard.io/pricing/?utm_source=wpadmin&utm_medium=recipes-page-notice&utm_campaign=rcb-upsell';
		const DISMISS_META_KEY = 'wpzoom_rcb_dismiss_recipes_page_notice';
		const MIN_RECIPES = 10;

		/**
		 * Initialize the notice hooks.
		 */
		public static function init() {
			// Only show for free version users.
			if ( defined( 'WPZOOM_RCB_HAS_PRO' ) && WPZOOM_RCB_HAS_PRO ) {
				return;
			}

			global $pagenow;

			$is_recipes_page = $pagenow === 'edit.php' && isset( $_GET['post_type'] ) && $_GET['post_type'] === 'wpzoom_rcb';
			$is_settings_page = $pagenow === 'admin.php' && isset( $_GET['page'] ) && $_GET['page'] === 'wpzoom-recipe-card-settings';

			if ( is_admin() && ( $is_recipes_page || $is_settings_page ) ) {
				add_action( 'admin_notices', array( __CLASS__, 'maybe_show_notice' ) );
			}

			add_action( 'wp_ajax_rcb_dismiss_recipes_page_notice', array( __CLASS__, 'dismiss_notice' ) );
		}

		/**
		 * Show the notice if not dismissed and user has enough recipes.
		 */
		public static function maybe_show_notice() {
			if ( get_user_meta( get_current_user_id(), self::DISMISS_META_KEY, true ) ) {
				return;
			}

			$recipe_count = wp_count_posts( 'wpzoom_rcb' );
			$total        = isset( $recipe_count->publish ) ? (int) $recipe_count->publish : 0;
			$total       += isset( $recipe_count->draft ) ? (int) $recipe_count->draft : 0;

			if ( $total < self::MIN_RECIPES ) {
				return;
			}

			self::render_notice( $total );
		}

		/**
		 * Handle the AJAX dismiss request.
		 */
		public static function dismiss_notice() {
			update_user_meta( get_current_user_id(), self::DISMISS_META_KEY, true );
			wp_send_json_success();
		}

		/**
		 * Render the upgrade notice.
		 *
		 * @param int $recipe_count Number of recipes.
		 */
		private static function render_notice( $recipe_count ) {
			$upgrade_url = self::UPGRADE_LINK;
			?>
			<div id="wpzoom-rcb-recipes-page-notice" class="wpzoom-rcb-upgrade-notice notice notice-warning is-dismissible">
				<div class="wpzoom-rcb-upgrade-notice-inner">
					<div class="wpzoom-rcb-upgrade-notice-icon">
						<span class="dashicons dashicons-warning"></span>
					</div>
					<div class="wpzoom-rcb-upgrade-notice-content">
						<h3><?php esc_html_e( 'Action Required: Your recipes are missing key SEO features', 'recipe-card-blocks-by-wpzoom' ); ?></h3>
						<p>
							<?php
							printf(
								/* translators: %d: number of recipes */
								esc_html__( 'You have %d recipes but you\'re using the free version of Recipe Card Blocks, which doesn\'t include Star Ratings in Google search results. Without ratings, your recipes are less likely to stand out and get clicks. Upgrade to PRO to unlock:', 'recipe-card-blocks-by-wpzoom' ),
								$recipe_count
							);
							?>
						</p>
						<ul>
							<li><strong><?php esc_html_e( 'Star Ratings in Google', 'recipe-card-blocks-by-wpzoom' ); ?></strong> &mdash; <?php esc_html_e( 'show star ratings directly in search results to boost click-through rates', 'recipe-card-blocks-by-wpzoom' ); ?></li>
							<li><strong><?php esc_html_e( 'Recipe Index', 'recipe-card-blocks-by-wpzoom' ); ?></strong> &mdash; <?php esc_html_e( 'a searchable recipe catalog that keeps visitors on your site longer', 'recipe-card-blocks-by-wpzoom' ); ?></li>
							<li><strong><?php esc_html_e( 'Adjustable Servings & Unit Conversion', 'recipe-card-blocks-by-wpzoom' ); ?></strong> &mdash; <?php esc_html_e( 'let readers scale ingredients and switch between US/Metric', 'recipe-card-blocks-by-wpzoom' ); ?></li>
						</ul>
					</div>
					<div class="wpzoom-rcb-upgrade-notice-cta">
						<a href="<?php echo esc_url( $upgrade_url ); ?>" target="_blank" class="button button-primary wpzoom-rcb-upgrade-btn"><?php esc_html_e( 'Upgrade to PRO', 'recipe-card-blocks-by-wpzoom' ); ?> &rarr;</a>
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpzoom-recipe-card-vs-pro' ) ); ?>" class="wpzoom-rcb-compare-link"><?php esc_html_e( 'See all PRO features', 'recipe-card-blocks-by-wpzoom' ); ?></a>
					</div>
				</div>
			</div>
			<style>
				#wpzoom-rcb-recipes-page-notice {
					border-left-color: #dba617;
					padding: 0;
				}

				#wpzoom-rcb-recipes-page-notice.notice-warning {
					border-left-width: 4px;
				}

				.wpzoom-rcb-upgrade-notice-inner {
					display: flex;
					align-items: flex-start;
					padding: 16px 12px;
					gap: 16px;
				}

				.wpzoom-rcb-upgrade-notice-icon {
					flex-shrink: 0;
				}

				.wpzoom-rcb-upgrade-notice-icon .dashicons {
					font-size: 36px;
					width: 36px;
					height: 36px;
					color: #dba617;
				}

				.wpzoom-rcb-upgrade-notice-content {
					flex: 1;
				}

				.wpzoom-rcb-upgrade-notice-content h3 {
					margin: 0 0 6px;
					font-size: 14px;
					color: #1d2327;
				}

				.wpzoom-rcb-upgrade-notice-content p {
					margin: 0 0 10px;
					font-size: 13px;
					color: #50575e;
				}

				.wpzoom-rcb-upgrade-notice-content ul {
					margin: 0;
					padding: 0;
					list-style: none;
				}

				.wpzoom-rcb-upgrade-notice-content ul li {
					font-size: 13px;
					color: #50575e;
					padding: 2px 0;
				}

				.wpzoom-rcb-upgrade-notice-content ul li::before {
					content: "\2713";
					color: #dba617;
					font-weight: bold;
					margin-right: 6px;
				}

				.wpzoom-rcb-upgrade-notice-cta {
					flex-shrink: 0;
					display: flex;
					flex-direction: column;
					align-items: center;
					gap: 8px;
					padding-top: 4px;
                    margin-top: 20px;
				}

				.wpzoom-rcb-upgrade-btn.button.button-primary {
					background: #E1581A;
					border-color: #c94e16;
					font-size: 14px;
					padding: 6px 20px;
					height: auto;
					line-height: 1.6;
					white-space: nowrap;
				}

				.wpzoom-rcb-upgrade-btn.button.button-primary:hover {
					background: #c94e16;
					border-color: #b0430f;
				}

				.wpzoom-rcb-compare-link {
					font-size: 12px;
					color: #50575e;
					text-decoration: none;
				}

				.wpzoom-rcb-compare-link:hover {
					color: #E1581A;
				}

				@media screen and (max-width: 782px) {
					.wpzoom-rcb-upgrade-notice-inner {
						flex-direction: column;
					}

					.wpzoom-rcb-upgrade-notice-icon {
						display: none;
					}

					.wpzoom-rcb-upgrade-notice-cta {
						flex-direction: row;
						align-items: center;
					}
				}
			</style>
			<script type="text/javascript">
				jQuery(document).ready(function($) {
					$(document).on('click', '#wpzoom-rcb-recipes-page-notice .notice-dismiss', function() {
						$.ajax({
							url: ajaxurl,
							type: 'GET',
							data: {
								action: 'rcb_dismiss_recipes_page_notice'
							}
						});
					});
				});
			</script>
			<?php
		}
	}
}

WPZOOM_Recipes_Page_Notice::init();
