<?php
/**
 * Responsible for the privacy policy.
 *
 * @since      3.5.0
 *
 * @package    WPZOOM_Recipe_Card_Blocks
 * @subpackage WPZOOM_Recipe_Card_Blocks/src/classes
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WPZOOM_Privacy_Policy' ) ) {

	/**
	 * Surfaces the plugin's privacy suggestions in the WordPress privacy tools.
	 *
	 * The templates/admin/privacy.php file shipped for years without anything
	 * registering it, so the text was never shown to anyone.
	 *
	 * @since 3.5.0
	 */
	class WPZOOM_Privacy_Policy {

		/**
		 * Register actions and filters.
		 *
		 * @since 3.5.0
		 * @return void
		 */
		public static function init() {
			add_action( 'admin_init', array( __CLASS__, 'privacy_policy' ) );
			add_filter( 'wp_privacy_personal_data_exporters', array( __CLASS__, 'register_exporter' ) );
			add_filter( 'wp_privacy_personal_data_erasers', array( __CLASS__, 'register_eraser' ) );
		}

		/**
		 * Add text to the privacy policy suggestions.
		 *
		 * @since 3.5.0
		 * @return void
		 */
		public static function privacy_policy() {
			if ( ! function_exists( 'wp_add_privacy_policy_content' ) ) {
				return;
			}

			ob_start();
			include WPZOOM_RCB_PLUGIN_DIR . 'templates/admin/privacy.php';
			$content = ob_get_clean();

			wp_add_privacy_policy_content(
				'Recipe Card Blocks',
				wp_kses_post( wpautop( $content, false ) )
			);
		}

		/**
		 * Register the personal data exporter.
		 *
		 * @since 3.5.0
		 * @param array $exporters Registered exporters.
		 * @return array
		 */
		public static function register_exporter( $exporters ) {
			$exporters['recipe-card-blocks-by-wpzoom'] = array(
				'exporter_friendly_name' => __( 'Recipe Card Blocks Ratings', 'recipe-card-blocks-by-wpzoom' ),
				'callback'               => array( __CLASS__, 'export_ratings' ),
			);

			return $exporters;
		}

		/**
		 * Register the personal data eraser.
		 *
		 * @since 3.5.0
		 * @param array $erasers Registered erasers.
		 * @return array
		 */
		public static function register_eraser( $erasers ) {
			$erasers['recipe-card-blocks-by-wpzoom'] = array(
				'eraser_friendly_name' => __( 'Recipe Card Blocks Ratings', 'recipe-card-blocks-by-wpzoom' ),
				'callback'             => array( __CLASS__, 'erase_ratings' ),
			);

			return $erasers;
		}

		/**
		 * Find the ratings belonging to an email address.
		 *
		 * Matches both ratings submitted with that address in the rating form and
		 * ratings left by the registered user who owns it.
		 *
		 * @since 3.5.0
		 * @param string $email_address The address being exported or erased.
		 * @return array
		 */
		protected static function get_ratings_for_email( $email_address ) {
			global $wpdb;

			if ( ! class_exists( 'WPZOOM_Rating_DB' ) ) {
				return array();
			}

			$user    = get_user_by( 'email', $email_address );
			$user_id = $user ? (int) $user->ID : 0;

			$where = $wpdb->prepare( 'author_email = %s', $email_address );

			if ( $user_id ) {
				$where .= $wpdb->prepare( ' OR user_id = %d', $user_id );
			}

			$ratings = WPZOOM_Rating_DB::get_ratings( array( 'where' => $where ) );

			return isset( $ratings['ratings'] ) ? $ratings['ratings'] : array();
		}

		/**
		 * Export all ratings tied to an email address.
		 *
		 * @since 3.5.0
		 * @param string $email_address The address being exported.
		 * @param int    $page          Page number (unused, everything is returned at once).
		 * @return array
		 */
		public static function export_ratings( $email_address, $page = 1 ) {
			$export_items = array();

			foreach ( self::get_ratings_for_email( $email_address ) as $rating ) {
				$data = array(
					array(
						'name'  => __( 'Rating', 'recipe-card-blocks-by-wpzoom' ),
						'value' => $rating->rating,
					),
					array(
						'name'  => __( 'Recipe', 'recipe-card-blocks-by-wpzoom' ),
						'value' => get_the_title( $rating->recipe_id ),
					),
					array(
						'name'  => __( 'Date', 'recipe-card-blocks-by-wpzoom' ),
						'value' => $rating->rate_date,
					),
					array(
						'name'  => __( 'IP address', 'recipe-card-blocks-by-wpzoom' ),
						'value' => $rating->ip,
					),
				);

				if ( ! empty( $rating->author_name ) ) {
					$data[] = array(
						'name'  => __( 'Name', 'recipe-card-blocks-by-wpzoom' ),
						'value' => $rating->author_name,
					);
				}

				if ( ! empty( $rating->review_text ) ) {
					$data[] = array(
						'name'  => __( 'Review', 'recipe-card-blocks-by-wpzoom' ),
						'value' => $rating->review_text,
					);
				}

				$export_items[] = array(
					'group_id'    => 'wpzoom-rcb-ratings',
					'group_label' => __( 'Recipe Ratings', 'recipe-card-blocks-by-wpzoom' ),
					'item_id'     => 'wpzoom-rcb-rating-' . $rating->id,
					'data'        => $data,
				);
			}

			return array(
				'data' => $export_items,
				'done' => true,
			);
		}

		/**
		 * Erase all ratings tied to an email address.
		 *
		 * @since 3.5.0
		 * @param string $email_address The address being erased.
		 * @param int    $page          Page number (unused, everything is removed at once).
		 * @return array
		 */
		public static function erase_ratings( $email_address, $page = 1 ) {
			$ids = wp_list_pluck( self::get_ratings_for_email( $email_address ), 'id' );

			if ( ! empty( $ids ) ) {
				WPZOOM_Rating_DB::delete_ratings( $ids );
			}

			return array(
				'items_removed'  => ! empty( $ids ),
				'items_retained' => false,
				'messages'       => array(),
				'done'           => true,
			);
		}
	}

	WPZOOM_Privacy_Policy::init();
}
