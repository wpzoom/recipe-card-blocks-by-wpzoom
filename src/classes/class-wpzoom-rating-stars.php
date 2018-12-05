<?php
/**
 * Rating Stars Class
 *
 * Add rating stars to recipe card.
 *
 * @since   1.1.0
 * @package WPZOOM Recipe Card Block
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WPZOOM_Rating_Stars' ) ):

	final class WPZOOM_Rating_Stars {
		/**
		 * We need to create a table where to store all ratings for each single post.
		 *
		 * @var string
		 * @since 1.1.0
		 */
		private static $tablename;

		/**
		 * Loads scripts and styles.
		 *
		 * @var WPZOOM_Assets_Manager
		 * @since 1.1.0
		 */
		private $assets_manager;

		/**
		 * Current user ID.
		 * If user is logged in, set current user ID, otherwise generate new random ID
		 *
		 * @since 1.1.0
		 */
		private $user_ID;

		/**
		 * WPZOOM_Rating_Stars constructor.
		 * @since 1.1.0
		 */
		public function __construct() {
			global $wpdb;

			self::$tablename = $wpdb->prefix . 'wpzoom_rating_stars';

			$this->assets_manager = WPZOOM_Assets_Manager::instance();
			$this->user_ID = $this->random_number();

			add_action( 'enqueue_block_assets', array( $this, 'block_assets' ) );

			// Do ajax request
			add_action( 'wp_ajax_wpzoom_user_rate_recipe', array( &$this, 'set_rating'), 10, 2 );
			add_action( 'wp_ajax_nopriv_wpzoom_user_rate_recipe', array( &$this, 'set_rating'), 10, 2 );
		}

		/**
		 * Create table to store all rating for each single post.
		 *
		 * @since 1.1.0
		 */
		public static function create_table() {
			global $wpdb;

			$tablename = self::$tablename;
			$charset_collate = $wpdb->get_charset_collate();

			$sql = "CREATE TABLE `$tablename` (
				id mediumint(9) NOT NULL AUTO_INCREMENT,
				recipe_id smallint(15) NOT NULL,
				user_id varchar(20) NOT NULL,
				rating int(50) NOT NULL,
				rate_date datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
				update_date datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
				UNIQUE KEY id (id),
				UNIQUE KEY post_user (recipe_id, user_id),
				KEY recipe_id (recipe_id),
				KEY user_id (user_id)
			) $charset_collate;";

			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			dbDelta( $sql );
		}

		/**
		 * Generate random number.
		 *
		 * @param number $length The length of returned value.
		 * @since 1.1.0
		 */
		public function random_number( $length = 10 ) {
		    $characters = '0123456789';
		    $charactersLength = strlen($characters);
		    $randomNumber = '';
		    for ($i = 0; $i < $length; $i++) {
		        $randomNumber .= $characters[rand(0, $charactersLength - 1)];
		    }
		    return $randomNumber;
		}

		/**
		 * Enqueue Gutenberg block assets for both frontend + backend.
		 *
		 * @since 1.1.0
		 */
		public function block_assets() {
			$localize_data = array(
				'recipe_ID'    	   	=> $this->assets_manager->post->ID,
				'user_ID'    	   	=> $this->get_user_ID(),
				'ajaxurl'    	   	=> admin_url('admin-ajax.php'),
				'ajax_nonce' 	   	=> wp_create_nonce( "wpzoom-rating-stars-nonce" ),
				'user_rated'		=> $this->check_user_rate( $this->assets_manager->post->ID ),
				'top_rated'			=> $this->get_toprated_recipes(),
				'rating_average'	=> $this->get_rating_average( $this->assets_manager->post->ID ),
				'rating_total'		=> $this->get_total_votes( $this->assets_manager->post->ID ),
				'strings'			=> array(
					'recipe_rating'	=> __( "Recipe rating", "wpzoom-recipe-card" ),
					'top_rated'		=> __( "Top rated", "wpzoom-recipe-card" ),
				)
			);

			wp_enqueue_script(
			    'wpzoom-rating-stars-script',
			    $this->assets_manager->asset_source( 'js', 'wpzoom-rating-stars.js' ),
			    $this->assets_manager->get_dependencies( 'wpzoom-rating-stars-script' ),
			    $this->assets_manager->_version,
			    true
			);

			// Localize variables
			wp_localize_script( 'wpzoom-rating-stars-script', 'wpzoomRatingStars', $localize_data );
		}

		/**
		 * Insert rating for recipe into Database.
		 * Verifies the AJAX request, to prevent any processing of requests which are passed in by third-party sites or systems.
		 *
		 * @since 1.1.0
		 */
		public function set_rating() {
			check_ajax_referer( 'wpzoom-rating-stars-nonce', 'security' );

			global $wpdb;

			$recipe_ID   = isset($_POST['recipe_id']) ? (int)$_POST['recipe_id'] : 0;
			$user_ID 	 = $this->get_user_ID();
			$rating      = isset($_POST['rating']) ? (int)$_POST['rating'] : 0;
			$tablename 	 = self::$tablename;

			if ( $rating == 0 ) {
				$response = array(
				    'status' => '204',
				    'message' => 'No response',
				);

				// normally, the script expects a json respone
				header( 'Content-Type: application/json; charset=utf-8' );
				echo json_encode( $response );

				exit;
			}

			$rate_date   = current_time( 'mysql' );
			$update_date = current_time( 'mysql' );

			$sql = $wpdb->prepare(
				"INSERT INTO `$tablename`
				(recipe_id, user_id, rating, rate_date, update_date) VALUES (%d, %d, %d, %s, %s)
				ON DUPLICATE KEY 
					UPDATE rating = $rating, update_date = '$update_date';
				",
				$recipe_ID,
				$user_ID,
				$rating,
				$rate_date,
				$update_date
			);

			$result = $wpdb->query( $sql );

			if ( $result ) {
				$response = array(
					'status' => '200',
					'message' => 'OK',
					'rating_avg' => $this->get_rating_average( $recipe_ID ),
					'rating_total' => $this->get_total_votes( $recipe_ID ),
				);

				// set cookie
				$this->set_user_rate( $recipe_ID, $rating );

				// normally, the script expects a json respone
				header( 'Content-Type: application/json; charset=utf-8' );
				echo json_encode( $response );

				exit;
			}
		}

		/**
		 * Get rating form HTML.
		 *
		 * @param string|number $recipe_ID The recipe id.
		 * @since 1.1.0
		 */
		public function get_rating_form( $recipe_ID ) {
			$output = '';
			$rating_stars_items = '';

			for ( $i = 1; $i <= 5; $i++ ) {
				$rating_stars_items .= '<li class="fa fa-star-o"></li>';
			}

			// Get the average vote number and check if user has voted for this post
			$average = $this->get_rating_average( $recipe_ID );
			$total_votes = $this->get_total_votes( $recipe_ID );
			$user_rate = $this->check_user_rate( $recipe_ID );

			$average_content = sprintf(
				'<small class="wpzoom-rating-average">%d</small> <small>%s</small> <small class="wpzoom-rating-total-votes">%d</small> <small>%s</small>',
				number_format( $average, 2 ),
				__( "from", "wpzoom-recipe-card" ), 
				(int)$total_votes,
				_n( "vote", "votes", (int)$total_votes, "wpzoom-recipe-card" )
			);

			$output = sprintf(
				'<div class="%1$s-container"><ul class="%1$s">%2$s</ul><span class="%1$s-average">%3$s</span></div>',
				'wpzoom-rating-stars',
				$rating_stars_items,
				$average_content
			);
			
			return $output;
		}

		/**
		 * Get rating star HTML.
		 *
		 * @param string|number $recipe_ID The recipe id.
		 * @since 1.1.0
		 */
		public function get_rating_star( $recipe_ID ) {
			// Check if user voted, use the full icon or outline icon if not
			$user_rate = $this->check_user_rate( $recipe_ID );

			if ( $user_rate ) {
				$rate_icon = ' icon-star-full';
			} else {
				$rate_icon = ' icon-star';
			}

			$average = $this->get_rating_average( $recipe_ID );
			$total_votes = $this->get_total_votes( $recipe_ID );
			$average_content = $average > 0 ? sprintf( __( "%s from %s votes", "wpzoom-recipe-card" ), "<i class=\"wpzoom-rating-average\"{$average}</i>", "<i class=\"wpzoom-rating-total-votes\">{$total_votes}</i>" ) : 'N/A';

			return '<span class="wpzoom-rating-stars-average' . $rate_icon . '">' . $average_content . '</span>';
		}

		/**
		 * Get rating average.
		 *
		 * @param string|number $recipe_ID The recipe id.
		 * @since 1.1.0
		 * @return number The average number of sql results.
		 */
		public function get_rating_average( $recipe_ID ) {
			global $wpdb;

			$tablename = self::$tablename;
			$sql_select = $wpdb->prepare( "SELECT AVG(rating) as rating FROM `$tablename` WHERE recipe_id = %s;", $recipe_ID );
			$sql_results = $wpdb->get_row( $sql_select, ARRAY_N );

			return number_format( $sql_results[0], 1 );
		}

		/**
		 * Get total number of recipe votes.
		 *
		 * @param string|number $recipe_ID The recipe id.
		 * @since 1.1.0
		 * @return number The total number of sql results.
		 */
		public function get_total_votes( $recipe_ID ) {
			global $wpdb;

			$tablename = self::$tablename;
			$sql_select = $wpdb->prepare( "SELECT COUNT(id) FROM `$tablename` WHERE recipe_id = %s;", $recipe_ID );

			$sql_results = $wpdb->get_row( $sql_select, ARRAY_N );

			return (int)$sql_results[0];
		}

		/**
		 * Get top rated recipes.
		 *
		 * @param array $args The query arguments.
		 * @since 1.1.0
		 * @return array The top rated recipes.
		 */
		public function get_toprated_recipes( $args = array() ) {
			global $wpdb;

			$tablename = self::$tablename;

			// Defaults
			$_where = array(); $where = '';
			$limit = isset( $args['posts_per_page'] ) ? $args['posts_per_page'] : get_option( 'posts_per_page' );

			if ( $limit == '-1' ) {
				$limit = 9999;
			}

			$sql_select = $wpdb->prepare( "
					SELECT recipe_id AS ID, AVG(rating) AS rating
					FROM `$tablename`
					GROUP BY recipe_id
					ORDER BY rating DESC
					LIMIT %d
				",
				$limit
			);

			$sql_results = $wpdb->get_results( $sql_select );

			return $sql_results;
		}

		/**
		 * Get top rated recipes IDs.
		 *
		 * @param object $sql_results The sql object query.
		 * @since 1.1.0
		 * @return array The array of top rated recipe ids.
		 */
		public function get_toprated_recipe_ids( $sql_results ) {
			$recipe_ids = array();

			if ( is_array( $sql_results ) ) {
				foreach ( $sql_results as $key => $post ) {
					array_push( $recipe_ids, $post->ID );
				}
			}

			return $recipe_ids;
		}

		/**
		 * Get user ID.
		 *
		 * @since 1.1.0
		 * @return string|number Current user ID or new generated ID.
		 */
		public function get_user_ID() {
			$user_ID = '';
			$current_user_id = (int)get_current_user_id();

			// Check for logged in users
			if ( $current_user_id !== 0 ) {
				return $current_user_id;
			}

			if ( ! isset( $_COOKIE[ "wpzoom-not-logged-user-id" ] ) ) {
				$this->set_user_ID(); // set user id in cookie
				$user_ID = $this->user_ID;
			} else {
				$user_ID = $_COOKIE[ "wpzoom-not-logged-user-id" ];
			}

			return esc_attr( $user_ID );
		}

		/**
		 * Set user ID as cookie.
		 *
		 * @since 1.1.0
		 */
		public function set_user_ID() {
			$cookie_name = "wpzoom-not-logged-user-id";
			$cookie_value = $this->user_ID;
			return setcookie( $cookie_name, $cookie_value, time() + (86400 * 7), "/" ); // expires in 7 days
		}

		/**
		 * Set user rate as cookie.
		 *
		 * @since 1.1.0
		 */
		public function set_user_rate( $recipe_ID, $rating ) {
			$cookie_name = "wpzoom-user-rating-recipe-$recipe_ID";
			$cookie_value = $rating;
			return setcookie( $cookie_name, $cookie_value, time() + (365 * 24 * 60 * 60), "/" ); // expires in one year
		}

		/**
		 * Check if user has rated recipe.
		 *
		 * @param string|number $recipe_ID The recipe id.
		 * @since 1.1.0
		 * @return boolean
		 */
		public function check_user_rate( $recipe_ID ) {
			if ( ! isset( $_COOKIE[ "wpzoom-user-rating-recipe" ] ) ) {
				return false;
			} else {
				return isset($_COOKIE[ "wpzoom-user-rating-recipe-$recipe_ID" ]);
			}
		}
	}

endif;

/**
 * Function to show the rating form or number
 */
function wpzoom_rating_stars( $recipe_ID, $type = 'form' ) {
	$wpzoom_rating_stars = new WPZOOM_Rating_Stars();

	if ( $type == 'number' ) {
		return $wpzoom_rating_stars->get_rating_star( $recipe_ID );
	} else {
		return $wpzoom_rating_stars->get_rating_form( $recipe_ID );
	}
}

?>