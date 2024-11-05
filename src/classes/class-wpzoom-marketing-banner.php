<?php
/**
 * Handle the plugin marketing stuff.
 *
 * @since   3.4.0
 * @package WPZOOM_Recipe_Card_Blocks
 */
if ( ! class_exists( 'WPZOOM_Marketing_Banner' ) ) {
	class WPZOOM_Marketing_Banner {
		const BTN_UPGRADE_NOW_LINK = 'https://recipecard.io/pricing/?utm_source=wpadmin&utm_medium=bf-rcb-banner-btn&utm_campaign=bf-rcb';
		const BF_START_DATE = '2024-11-27 00:00:00';
		const BF_END_DATE = '2024-12-03 23:59:59';

		public static function init() {

			global $pagenow;

			// add notices only on specific pages
			if ( ( is_admin() && $pagenow === 'index.php' ) || $pagenow === 'plugins.php' ||
				 ( $pagenow === 'edit.php' && $_SERVER['QUERY_STRING'] === 'post_type=wpzoom_rcb') ||
				 ( $pagenow === 'admin.php' && $_SERVER['QUERY_STRING'] === 'page=wpzoom-recipe-card-settings') ) {

				add_action( 'admin_notices', [ __CLASS__, 'show_black_friday_banner' ] );
			}

			add_action( 'wp_ajax_rcb_dismiss_bf_banner', [
				__CLASS__,
				'dismiss_black_friday_banner'
			] );
		}

		/**
		 * Display the Black Friday banner if the conditions are met.
		 */
		public static function show_black_friday_banner() {
			if ( self::is_black_friday_period() && ! self::has_dismissed_banner() ) {
				self::inspiro_display_black_friday_banner();
			}
		}

		/**
		 * Check if the Black Friday period is ongoing.
		 *
		 * @return bool
		 */
		private static function is_black_friday_period() {
			$today = current_time( 'Y-m-d H:i:s' );

			return $today >= self::BF_START_DATE && $today <= self::BF_END_DATE;
		}

		/**
		 * Check if the user has dismissed the Black Friday banner.
		 *
		 * @return bool
		 */
		private static function has_dismissed_banner() {
			return (bool) get_user_meta( get_current_user_id(), 'wpzoom_rcb_dismiss_black_friday_banner', true );
		}

		/**
		 * Handle the AJAX request to dismiss the Banner.
		 */
		public static function dismiss_black_friday_banner() {
//			check_ajax_referer('my_nonce_action', 'security');
			update_user_meta( get_current_user_id(), 'wpzoom_rcb_dismiss_black_friday_banner', true );
			wp_send_json_success();
		}

		/**
		 * Render Banner Layout
		 *
		 * @return mixed
		 */
		private static function inspiro_display_black_friday_banner() { ?>
			<div class="wpzoom-banner-container-wrapper">
				<div id="wpzoom-rcb-bf-banner-container" class="wpzoom-bf-banner-container notice is-dismissible">
					<a href="https://recipecard.io/pricing/?utm_source=wpadmin&utm_medium=bf-rcb-banner-image&utm_campaign=bf-rcb"><img src="<?php echo untrailingslashit( WPZOOM_RCB_PLUGIN_URL ) . '/src/classes/templates/assets/img/bf-recipe-card-block-pro.png'; ?>" class="bf-wpzoom-banner-image" alt="WPZOOM Recipe Card Block Pro Deal" ></a>
					<div class="recipe-banner-text-container">
						<h2>Upgrade to <span class="orange-text">Recipe Card Blocks Pro</span></h2>
						<span class="banner-text">Unlock the full potential of your recipes and get more traffic with the PRO version! Get advanced features like:</span>
						<div class="recipe-banner-promo-btns">
							<div class="banner-btn">Premium Recipe Card Skins</div>
							<div class="banner-btn">Recipe Index</div>
							<div class="banner-btn">Star Rating</div>
                            <div class="banner-btn">Equipment</div>
							<div class="banner-btn">+ many more</div>
						</div>
					</div>
					<div class="recipe-upgrade-banner">
						<div class="banner-clock">
							<span class="hurry-up">Hurry Up! Offer ends soon!</span>
							<div class="clock-digits">
								<span><i id="bf-days"></i>d</span>
								<span><i id="bf-hours"></i>h</span>
								<span><i id="bf-minutes"></i>m</span>
								<span><i id="bf-seconds"></i>s</span>
							</div>
						</div>
						<a href="<?php echo self::BTN_UPGRADE_NOW_LINK ?>" target="_blank" class="btn-upgrade-now">Upgrade
							now &rarr;</a>
					</div>
				</div>
			</div>
			<style>
				.wpzoom-banner-container-wrapper {
					margin: 10px 20px 10px 2px;
				}

				.wpzoom-bf-banner-container {
					display: flex;
					align-items: center;
					color: #242628;
					background: #FFE7D4;
				}

				/*	rewrite WP core rule */
				.wpzoom-bf-banner-container.notice {
					border: unset;
					padding: 0 12px 0 0;
				}

				.wpzoom-bf-banner-container.notice.is-dismissible {
					padding-right: 14px;
					margin: 5px 0 15px;
					border-radius: 3px;
				}

				/* start banner content */
				.bf-wpzoom-banner-image {
					margin-right: 30px;
                    max-width: 150px;
				}

				/* text container */
				.recipe-banner-text-container h2 {
					color: #242628;
					font-size: 30px;
					line-height: 1.2;
					margin: 10px 0;
					font-weight: 800;
				}

				.recipe-banner-text-container .orange-text {
					color: #E1581A;
				}

				.recipe-banner-text-container .banner-text {
					font-size: 14px;
					font-weight: 500;
					margin-bottom: 10px;
					display: inline-block;
				}

				.recipe-banner-promo-btns .banner-btn {
					padding: 4px 16px;
					color: #242628;
					font-weight: 600;
					background: #EAAB90;
					border-radius: 30px;
					display: inline-block;
					margin: 0 5px 8px 0;
					font-size: 11px;
				}

				/* CTA btn */
				.recipe-upgrade-banner .banner-clock {
					display: flex;
					flex-direction: column;
				}

				.recipe-upgrade-banner .hurry-up {
					font-size: 14px;
					font-weight: 500;
				}

				.recipe-upgrade-banner .clock-digits {
					display: flex;
					font-size: 14px;
					font-weight: 300;
					margin-bottom: 10px;
				}

				.recipe-upgrade-banner .clock-digits span {
					margin-right: 8px;
				}

				.recipe-upgrade-banner .clock-digits i {
					font-style: normal !important;
					margin-right: 2px;
					font-weight: 600;
					font-size: 20px;
				}

				.recipe-upgrade-banner a.btn-upgrade-now {
					color: #fff;
					font-size: 18px;
					line-height: 20px;
					padding: 15px 29px;
					font-weight: 600;
					background: #E1581A;
					text-decoration: none;
					text-transform: uppercase;
					display: inline-block;
					z-index: 999;
					position: relative;
					border-radius: 5px;
					box-shadow: rgba(0, 0, 0, .1) 0 1px 3px 0, rgba(0, 0, 0, .06) 0 1px 2px 0;
					white-space: nowrap;
				}

                .recipe-upgrade-banner a.btn-upgrade-now:hover {
                    background: #242628;
                }

				/* Responsive */
				@media screen and (max-width: 550px) {
					.wpzoom-bf-banner-container {
						flex-direction: column;
					}

					.recipe-banner-text-container h2 .orange-text {
						display: block;
					}

					.recipe-upgrade-banner a.btn-upgrade-now {
						margin-bottom: 20px;
					}
				}

				@media screen and (max-width: 700px) {
					.bf-wpzoom-banner-image {
						display: none;
					}
				}

				@media screen and (max-width: 782px) {
					.wpzoom-bf-banner-container.notice {
						line-height: unset;
					}
				}

				@media screen and (max-width: 1023px) {
					.recipe-banner-promo-btns,
					.recipe-upgrade-banner .banner-clock {
						display: none;
					}
				}

				@media screen and (max-width: 1200px) {
					.wpzoom-banner-container-wrapper {
						margin-right: 10px;
					}

					.wpzoom-bf-banner-container.notice.is-dismissible {
						padding-right: 0;
					}

					.bf-wpzoom-banner-image {
						margin-right: 0;
					}

					.recipe-banner-text-container {
						margin: 14px 14px 15px 14px;
					}

					.recipe-banner-text-container .orange-text {
						line-height: 30px;
					}
				}

				@media screen and (min-width: 1201px) {
					.recipe-upgrade-banner {
						margin-left: auto;
						margin-right: 20px;
					}

					.recipe-upgrade-banner .hurry-up {
						margin-bottom: 5px;
					}
				}

				@media screen and (min-width: 1201px) and (max-width: 1300px) {
					.recipe-banner-text-container .banner-text {
						max-width: 520px;
					}
				}

				@media screen and (max-width: 1230px) {
					.recipe-banner-text-container h2 {
						font-size: 28px;
					}

					.recipe-upgrade-banner a.btn-upgrade-now {
						font-size: 16px;
						padding: 13px 25px;
						margin-right: 18px;
					}
				}
			</style>
			<script type="text/javascript">
				jQuery(document).ready(function () {
					jQuery(document).on('click', '#wpzoom-rcb-bf-banner-container button.notice-dismiss', function (e) {
						jQuery.ajax({
							url: ajaxurl,
							type: 'GET',
							data: {
								action: 'rcb_dismiss_bf_banner',
							},
							// data: Your Data Here,
							// success: function(response) {
							// 	console.log('Success:', response);
							// },
							// error: function(jqXHR, textStatus, errorThrown) {
							// 	console.log('Error:', textStatus, errorThrown);
							// 	console.log('Response Text:', jqXHR.responseText);
							// }
						});
					});
				});

				// Set the date we're counting down to
				(function () {
					// Constants
					const COUNTDOWN_END_DATE = new Date("<?php echo self::BF_END_DATE; ?>").getTime();

					// Element references
					const daysContainer = document.getElementById("bf-days");
					const hoursContainer = document.getElementById("bf-hours");
					const minutesContainer = document.getElementById("bf-minutes");
					const secondsContainer = document.getElementById("bf-seconds");

					// Function to calculate the time difference
					function calculateTimeDifference(targetDate) {
						const now = new Date().getTime();
						const distance = targetDate - now;

						if (distance > 0) {
							return {
								days: Math.floor(distance / (1000 * 60 * 60 * 24)),
								hours: Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60)),
								minutes: Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60)),
								seconds: Math.floor((distance % (1000 * 60)) / 1000)
							};
						} else {
							return {days: 0, hours: 0, minutes: 0, seconds: 0};
						}
					}

					// Function to update the HTML elements with the calculated time
					function updateCountdownDisplay(time) {
						daysContainer.innerText = time.days;
						hoursContainer.innerText = time.hours;
						minutesContainer.innerText = time.minutes;
						secondsContainer.innerText = time.seconds;
					}

					// Render the countdown initially
					updateCountdownDisplay(calculateTimeDifference(COUNTDOWN_END_DATE));

					// Update the countdown every 1 second
					const intervalId = setInterval(function () {
						const timeDifference = calculateTimeDifference(COUNTDOWN_END_DATE);
						updateCountdownDisplay(timeDifference);

						// Clear interval if the countdown is over
						if (timeDifference.days === 0 && timeDifference.hours === 0 &&
							timeDifference.minutes === 0 && timeDifference.seconds === 0) {
							clearInterval(intervalId);
						}
					}, 1000);
				})();
			</script>
		<?php }
	}
}
WPZOOM_Marketing_Banner::init();
