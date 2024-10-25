<?php
/**
 * Handle the plugin marketing stuff.
 *
 * @since   3.4.0
 * @package WPZOOM_Recipe_Card_Blocks
 */
if ( ! class_exists( 'WPZOOM_Marketing_Banner' ) ) {
	class WPZOOM_Marketing_Banner {
		const BTN_UPGRADE_NOW_LINK = '#';
		const BF_START_DATE = '2024-10-24 00:00:00';
		const BF_END_DATE = '2024-10-25 23:59:59';

		public static function init() {
			add_action('admin_notices', [__CLASS__, 'show_black_friday_banner']);
		}

		/**
		 * Display the Black Friday banner if the conditions are met.
		 */
		public static function show_black_friday_banner() {
			if (self::is_black_friday_period() && !self::has_dismissed_banner()) {
				self::inspiro_display_black_friday_banner();
			}
		}

		/**
		 * Check if the Black Friday period is ongoing.
		 *
		 * @return bool
		 */
		private static function is_black_friday_period() {
			$today = current_time('Y-m-d H:i:s');
			return $today >= self::BF_START_DATE && $today <= self::BF_END_DATE;
		}

		/**
		 * Check if the user has dismissed the Black Friday banner.
		 *
		 * @return bool
		 */
		private static function has_dismissed_banner() {
			return (bool) get_user_meta(get_current_user_id(), 'inspiro_dismiss_black_friday_banner', true);
		}

		private static function show_current_left_time() {
			// - Functionality for render first date/time - //
			$today = new DateTime();
			$endDay = new DateTime( self::BF_END_DATE );

			// Calculate the difference in days between today and the end date
			return $today->diff( $endDay );
		}

		/**
		 * Render Banner Layout
		 *
		 * @return mixed
		 */
		private static function inspiro_display_black_friday_banner() {
			$interval = self::show_current_left_time();
		?>
			<div class="wpzoom-banner-container-wrapper">
				<div class="wpzoom-bf-banner-container notice is-dismissible">

					<img src="<?php echo untrailingslashit( WPZOOM_RCB_PLUGIN_URL ) . '/src/classes/templates/assets/img/bf-recipe-card-block-pro.png'; ?>"
						 class="bf-wpzoom-banner-image"
						 alt="WPZOOM Recipe Card Block Pro Deal"
					>

					<div class="recipe-banner-text-container">
						<h2>Upgrade to <span class="orange-text">Recipe Card Blocks Pro</span></h2>
						<span class="banner-text">Unlock the full potential of your recipes with our PRO version! Get advanced features like:</span>

						<div class="recipe-banner-promo-btns">
							<div class="banner-btn">Premium Block Skins</div>
							<div class="banner-btn">Recipe Index Block</div>
							<div class="banner-btn">Star Rating</div>
							<div class="banner-btn">+ many more</div>
						</div>
					</div>

					<div class="recipe-upgrade-banner">
						<div class="banner-clock">
							<span class="hurry-up">Hurry Up!</span>
							<div class="clock-digits">
								<span><i id="bf-days"><?php echo $interval->days ?></i>d</span>
								<span><i id="bf-hours"><?php echo $interval->h ?></i>h</span>
								<span><i id="bf-minutes"><?php echo $interval->i ?></i>m</span>
								<span><i id="bf-seconds"><?php echo $interval->s ?></i>s</span>
							</div>
						</div>
						<a href="<?php echo self::BTN_UPGRADE_NOW_LINK ?>" target="_blank" class="btn-upgrade-now">Upgrade now &rarr;</a>
					</div>
				</div>
			</div>
			<style>
				.wpzoom-banner-container-wrapper {
					margin: 10px 20px 0 2px;
				}
				.wpzoom-bf-banner-container {
					display: flex;
					align-items: center;
					color: #000;
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
				}

				/* text container */
				.recipe-banner-text-container h2{
					color: #000;
					font-size: 30px;
					line-height: 1.2;
					margin: 0 0 20px;
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
					.recipe-banner-text-container{
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

					// don't working now
					jQuery(document).on('click', '.wpzoom-bf-banner-container .notice-dismiss', function () {
						jQuery.post(ajaxurl, {
							action: 'wpzoom_recipe_plugin_dismiss_black_friday_banner'
						});
					});

					// Set the date we're counting down to
					(function () {
						const countDownDate = new Date("<?php echo self::BF_END_DATE; ?>").getTime();

						// Update the countdown every 1 second
						const x = setInterval(function () {

							// Get today's date and time
							let now = new Date().getTime();

							// Find the distance between now and the countdown date
							let distance = countDownDate - now;

							// Time calculations for days, hours, minutes and seconds
							let days, hours, minutes, seconds;

							if (distance > 0) {
								days = Math.floor(distance / (1000 * 60 * 60 * 24));
								hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
								minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
								seconds = Math.floor((distance % (1000 * 60)) / 1000);
							} else {
								days = hours = minutes = seconds = 0;
							}

							// Display the result in the elements with respective ids
							document.getElementById("bf-days").innerText = days;
							document.getElementById("bf-hours").innerText = hours;
							document.getElementById("bf-minutes").innerText = minutes;
							document.getElementById("bf-seconds").innerText = seconds;

							// If the countdown is finished, clear the interval
							if (distance < 0) {
								clearInterval(x);
							}
						}, 1000);
					})();
			</script>
		<?php }
	}
}
WPZOOM_Marketing_Banner::init();
