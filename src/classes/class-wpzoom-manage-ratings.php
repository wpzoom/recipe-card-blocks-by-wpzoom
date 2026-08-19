<?php
/**
 * Class Manage Ratings Page
 *
 * @since   3.2.0
 * @package WPZOOM_Recipe_Card_Blocks
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class for manage ratings.
 */
class WPZOOM_Manage_Ratings {

	/**
	 * The Manage Ratings page slug.
	 *
	 * @var string
	 */
	public $_page_slug = 'wpzoom-manage-ratings';

	/**
	 * The number of unapproved ratings.
	 *
	 * @var integer
	 */
	public $pending_count = 0;

	/**
	 * Statistics for ratings.
	 *
	 * @var array
	 */
	private $statistics = null;

	/**
	 * The current list of rating items.
	 *
	 * @var array
	 */
	private $ratings;

	/**
	 * Various information needed for displaying the pagination.
	 *
	 * @var array
	 */
	protected $_pagination_args = array();

	/**
	 * Cached pagination output.
	 *
	 * @var string
	 */
	private $_pagination;

	/**
	 * The Constructor.
	 */
	public function __construct() {
		$page = isset( $_GET['page'] ) ? $_GET['page'] : '';

		add_filter( 'wpzoom_manage_ratings_submenu_item', array( $this, 'submenu_item_bubble' ), 1 );
		add_action( 'admin_menu', array( $this, 'add_top_level_bubble' ), 999 );

		// Do ajax request
		add_action( 'wp_ajax_approverating', array( $this, 'approve_rating' ) );
		add_action( 'wp_ajax_unapproverating', array( $this, 'unapprove_rating' ) );
		add_action( 'wp_ajax_deleterating', array( $this, 'delete_rating' ) );
		add_action( 'wp_ajax_wpzoom_bulk_rating_action', array( $this, 'handle_bulk_action' ) );

		// Run only on Manage Ratings page
		if ( $this->_page_slug === $page ) {
			// Build Ratings query
			add_action( 'admin_init', array( $this, 'build_query' ) );

			// Add screen options
			add_action( 'current_screen', array( $this, 'add_screen_options' ) );
			add_filter( 'set-screen-option', array( $this, 'set_screen_options' ), 10, 3 );

			// Display HTML content for Manage Ratings page
			add_action( 'wpzoom_rcb_admin_manage_ratings', array( $this, 'display' ) );

			// Include scripts
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

			if ( get_option( 'show_avatars' ) ) {
				add_filter( 'wpzoom_manage_ratings_comment_author', array( $this, 'floated_comment_author_avatar' ), 10, 2 );
				add_filter( 'wpzoom_manage_ratings_author', array( $this, 'floated_rating_author_avatar' ), 10, 2 );
			}
		}
	}

	public function approve_rating() {
		$post_id = isset( $_POST['postId'] ) && wp_validate_boolean( $_POST['postId'] ) ? absint( $_POST['postId'] ) : 0;

		check_ajax_referer( "approve-rating_$post_id", 'nonce' );

		// current_user_can( 'edit_post', 0 ) is not a meaningful check, so when the
		// rating has no resolvable post fall back to a real capability.
		if ( ! $post_id && ! current_user_can( 'edit_others_posts' ) ) {
			wp_die( -1 );
		}

		if ( $post_id && ! current_user_can( 'edit_post', $post_id ) ) {
			wp_send_json_error();
		}

		$id      = isset( $_POST['id'] ) && wp_validate_boolean( $_POST['id'] ) ? absint( $_POST['id'] ) : 0;
		$user_id = isset( $_POST['userId'] ) && wp_validate_boolean( $_POST['userId'] ) ? absint( $_POST['userId'] ) : 0;
		$user_ip = isset( $_POST['userIp'] ) && wp_validate_boolean( $_POST['userIp'] ) ? esc_attr( $_POST['userIp'] ) : '';
		$rating  = isset( $_POST['rating'] ) ? absint( $_POST['rating'] ) : 0;
		$date    = isset( $_POST['date'] ) ? $_POST['date'] : current_time( 'mysql' );
		$time    = isset( $_POST['time'] ) ? $_POST['time'] : '';

		$rating_data = array(
			'id'        => $id,
			'rating'    => $rating,
			'rate_date' => "$date $time",
			'approved'  => 1,
			'recipe_id' => $post_id,
			'user_id'   => $user_id,
			'ip'        => $user_ip,
		);

		$result = WPZOOM_Rating_DB::add_or_update_rating( $rating_data );

		if ( ! $result ) {
			wp_send_json_error();
		}

		// Also approve linked comment if exists
		$rating_obj = WPZOOM_Rating_DB::get_rating( array( 'where' => 'id = ' . intval( $id ) ) );
		if ( $rating_obj && ! empty( $rating_obj->comment_id ) ) {
			wp_set_comment_status( $rating_obj->comment_id, 'approve' );
		}

		wp_send_json_success();
	}

	public function unapprove_rating() {
		$post_id = isset( $_POST['postId'] ) && wp_validate_boolean( $_POST['postId'] ) ? absint( $_POST['postId'] ) : 0;

		check_ajax_referer( "approve-rating_$post_id", 'nonce' );

		// current_user_can( 'edit_post', 0 ) is not a meaningful check, so when the
		// rating has no resolvable post fall back to a real capability.
		if ( ! $post_id && ! current_user_can( 'edit_others_posts' ) ) {
			wp_die( -1 );
		}

		if ( $post_id && ! current_user_can( 'edit_post', $post_id ) ) {
			wp_send_json_error();
		}

		$id      = isset( $_POST['id'] ) && wp_validate_boolean( $_POST['id'] ) ? absint( $_POST['id'] ) : 0;
		$user_id = isset( $_POST['userId'] ) && wp_validate_boolean( $_POST['userId'] ) ? absint( $_POST['userId'] ) : 0;
		$user_ip = isset( $_POST['userIp'] ) && wp_validate_boolean( $_POST['userIp'] ) ? esc_attr( $_POST['userIp'] ) : '';
		$rating  = isset( $_POST['rating'] ) ? absint( $_POST['rating'] ) : 0;
		$date    = isset( $_POST['date'] ) ? $_POST['date'] : current_time( 'mysql' );
		$time    = isset( $_POST['time'] ) ? $_POST['time'] : '';

		$rating_data = array(
			'id'        => $id,
			'rating'    => $rating,
			'rate_date' => "$date $time",
			'approved'  => 0,
			'recipe_id' => $post_id,
			'user_id'   => $user_id,
			'ip'        => $user_ip,
		);

		$result = WPZOOM_Rating_DB::add_or_update_rating( $rating_data );

		if ( ! $result ) {
			wp_send_json_error();
		}

		// Also unapprove linked comment if exists
		$rating_obj = WPZOOM_Rating_DB::get_rating( array( 'where' => 'id = ' . intval( $id ) ) );
		if ( $rating_obj && ! empty( $rating_obj->comment_id ) ) {
			wp_set_comment_status( $rating_obj->comment_id, 'hold' );
		}

		wp_send_json_success();
	}

	public function delete_rating() {
		$post_id = isset( $_POST['postId'] ) && wp_validate_boolean( $_POST['postId'] ) ? absint( $_POST['postId'] ) : 0;

		check_ajax_referer( "delete-rating_$post_id", 'nonce' );

		// current_user_can( 'edit_post', 0 ) is not a meaningful check, so when the
		// rating has no resolvable post fall back to a real capability.
		if ( ! $post_id && ! current_user_can( 'edit_others_posts' ) ) {
			wp_die( -1 );
		}

		if ( $post_id && ! current_user_can( 'edit_post', $post_id ) ) {
			wp_send_json_error();
		}

		$id = isset( $_POST['id'] ) && wp_validate_boolean( $_POST['id'] ) ? absint( $_POST['id'] ) : 0;

		if ( $id ) {
			WPZOOM_Rating_DB::delete_ratings( $id );
		} else {
			wp_send_json_error();
		}

		wp_send_json_success();
	}

	public function enqueue_scripts() {
		wp_enqueue_script( 'admin-comments' );

		wp_enqueue_script(
			'wpzoom-manage-ratings',
			untrailingslashit( WPZOOM_RCB_PLUGIN_URL ) . '/dist/assets/admin/js/manage-ratings.js',
			array( 'jquery' ),
			WPZOOM_RCB_VERSION,
			true
		);

		// Localize script data for bulk actions
		wp_localize_script(
			'wpzoom-manage-ratings',
			'wpzoomManageRatings',
			array(
				'ajaxurl'    => admin_url( 'admin-ajax.php' ),
				'bulkNonce'  => wp_create_nonce( 'wpzoom-manage-ratings-bulk' ),
				'strings'    => array(
					'confirmDelete'    => __( 'Are you sure you want to delete the selected ratings? This action cannot be undone.', 'recipe-card-blocks-by-wpzoom' ),
					'selectAction'     => __( 'Please select a bulk action.', 'recipe-card-blocks-by-wpzoom' ),
					'selectItems'      => __( 'Please select at least one rating.', 'recipe-card-blocks-by-wpzoom' ),
					'processing'       => __( 'Processing...', 'recipe-card-blocks-by-wpzoom' ),
					'confirmDeleteSingle' => __( 'Do you really want to delete rating for %s?', 'recipe-card-blocks-by-wpzoom' ),
				),
			)
		);

	}


	/**
	 * Add screen options.
	 */
	public function add_screen_options() {
		add_screen_option(
			'per_page',
			array(
				'option'  => str_replace( '-', '_', $this->_page_slug ) . '_per_page',
				'default' => 20,
				'label'   => esc_html__( 'Ratings per page', 'recipe-card-blocks-by-wpzoom' ),
			)
		);

		get_current_screen()->add_help_tab(
			array(
				'id'      => $this->_page_slug . '-overview',
				'title'   => __( 'Overview' ),
				'content' =>
						'<p>' . __( 'You can manage ratings made on your site similar to the way you manage posts and other content. This screen is customizable in the same ways as other management screens, and you can act on ratings using the on-hover action links.' ) . '</p>',
			)
		);
		get_current_screen()->add_help_tab(
			array(
				'id'      => $this->_page_slug . '-moderating-comments',
				'title'   => __( 'Moderating Ratings' ),
				'content' =>
							'<p>' . __( 'A red bar on the left means the rating is waiting for you to moderate it.' ) . '</p>' .
							'<p>' . __( 'In the <strong>Author</strong> column, the author&#8217;s name, email address, and blog URL is shown.' ) . '</p>' .
							'<p>' . __( 'In the <strong>Type</strong> column, are displayed the type of rating. There are two types of ratings available: User Rating and Comment Rating. Near the Type column, the <strong>IP</strong> address is shown in and by clicking on this link will show you all the ratings or comments made from this IP address.' ) . '</p>' .
							'<p>' . __( 'In the <strong>Rating or Comment</strong> column, hovering over any row item gives you options to approve, edit, spam mark, or trash that record from Database.' ) . '</p>' .
							'<p>' . __( 'In the <strong>Post</strong> column, there are two elements. The text is the name of the post that inspired the comment, and links to the post editor for that entry. The View Post link leads to that post on your live site.' ) . '</p>' .
							'<p>' . __( 'In the <strong>Submitted on</strong> column, the date and time the rating or comment was left on your site appears. Clicking on the date/time link will take you to that comment on your live site.' ) . '</p>',
			)
		);

		get_current_screen()->set_help_sidebar(
			'<p><strong>' . __( 'For more information:' ) . '</strong></p>' .
			'<p>' . sprintf( '<a href="https://recipecard.io/documentation/">%s</a>', __( 'Documentation on Ratings', 'recipe-card-blocks-by-wpzoom' ) ) . '</p>' .
			'<p>' . sprintf( '<a href="https://recipecard.io/support/">%s</a>', __( 'Support', 'recipe-card-blocks-by-wpzoom' ) ) . '</p>'
		);
	}

	/**
	 * Set screen options.
	 *
	 * @param bool|int $status Screen option value. Default false to skip.
	 * @param string   $option The option name.
	 * @param int      $value  The number of rows to use.
	 */
	public function set_screen_options( $status, $option, $value ) {
		$screen_option_id_per_page = str_replace( '-', '_', $this->_page_slug ) . '_per_page';

		if ( $screen_option_id_per_page === $option ) {
			return min( $value, 999 );
		}

		return $status;
	}

	/**
	 * Set pending ratings count.
	 */
	public function set_pending_count() {
		// Already counted during this request.
		if ( is_array( $this->pending_count ) ) {
			return;
		}

		// Check if ratings are enabled before querying
		if ( ! WPZOOM_Settings::get_rating_star_acces() && ! WPZOOM_Settings::get( 'wpzoom_rcb_settings_comment_ratings' ) ) {
			$this->pending_count = array( 'total' => 0, 'ratings' => array() );
			return;
		}

		$this->pending_count = WPZOOM_Rating_DB::get_ratings(
			array(
				'where' => 'approved = 0',
			)
		);
	}

	/**
	 * Get ratings statistics for the overview box.
	 *
	 * @return array Statistics data.
	 */
	public function get_ratings_statistics() {
		if ( null !== $this->statistics ) {
			return $this->statistics;
		}

		global $wpdb;
		$table = WPZOOM_Rating_DB::get_table_name();

		// Check if table exists
		$table_exists = $wpdb->get_var( "SHOW TABLES LIKE '$table'" );
		if ( ! $table_exists ) {
			$this->statistics = array(
				'total'           => 0,
				'approved'        => 0,
				'pending'         => 0,
				'average'         => 0,
				'distribution'    => array(),
				'user_ratings'    => 0,
				'comment_ratings' => 0,
				'past_7_days'     => array_fill( 0, 7, 0 ),
				'past_30_days'    => 0,
				'previous_30_days' => 0,
			);
			return $this->statistics;
		}

		$this->statistics = array(
			'total'           => intval( $wpdb->get_var( "SELECT COUNT(*) FROM $table" ) ),
			'approved'        => intval( $wpdb->get_var( "SELECT COUNT(*) FROM $table WHERE approved = 1" ) ),
			'pending'         => intval( $wpdb->get_var( "SELECT COUNT(*) FROM $table WHERE approved = 0" ) ),
			'average'         => floatval( $wpdb->get_var( "SELECT AVG(rating) FROM $table WHERE approved = 1" ) ),
			'user_ratings'    => intval( $wpdb->get_var( "SELECT COUNT(*) FROM $table WHERE comment_id = 0" ) ),
			'comment_ratings' => intval( $wpdb->get_var( "SELECT COUNT(*) FROM $table WHERE comment_id > 0" ) ),
			'distribution'    => array(),
			'past_7_days'     => array_fill( 0, 7, 0 ),
			'past_30_days'    => 0,
			'previous_30_days' => 0,
		);

		// Get rating distribution
		$distribution_results = $wpdb->get_results(
			"SELECT rating, COUNT(*) as count FROM $table WHERE approved = 1 GROUP BY rating ORDER BY rating DESC"
		);

		foreach ( $distribution_results as $row ) {
			$this->statistics['distribution'][ intval( $row->rating ) ] = intval( $row->count );
		}

		// Fill in missing ratings with 0
		for ( $i = 5; $i >= 1; $i-- ) {
			if ( ! isset( $this->statistics['distribution'][ $i ] ) ) {
				$this->statistics['distribution'][ $i ] = 0;
			}
		}
		krsort( $this->statistics['distribution'] );

		// Get time-based statistics using WordPress timezone
		$current_time    = current_time( 'timestamp' );
		$thirty_days_ago = strtotime( '-30 days', $current_time );
		$sixty_days_ago  = strtotime( '-60 days', $current_time );
		// Get today's date (Y-m-d only) for calendar day comparison
		$today_date      = wp_date( 'Y-m-d', $current_time );

		// Get all ratings from last 60 days for calculations
		$recent_ratings = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT rate_date FROM $table WHERE approved = 1 AND rate_date >= %s ORDER BY rate_date DESC",
				date( 'Y-m-d H:i:s', $sixty_days_ago )
			)
		);

		if ( ! empty( $recent_ratings ) ) {
			foreach ( $recent_ratings as $rating ) {
				$ratetime = strtotime( $rating->rate_date );
				// Get just the date portion (Y-m-d) for calendar day comparison
				$rating_date = substr( $rating->rate_date, 0, 10 );
				// Calculate calendar days difference (not 24-hour periods)
				$days_diff = (int) floor( ( strtotime( $today_date ) - strtotime( $rating_date ) ) / DAY_IN_SECONDS );

				// Count for 7-day chart (index 0 = today, 6 = 6 days ago)
				if ( $days_diff >= 0 && $days_diff <= 6 ) {
					$this->statistics['past_7_days'][ $days_diff ]++;
				}

				// Count for 30-day periods
				if ( $ratetime >= $thirty_days_ago ) {
					$this->statistics['past_30_days']++;
				} elseif ( $ratetime >= $sixty_days_ago ) {
					$this->statistics['previous_30_days']++;
				}
			}
		}

		return $this->statistics;
	}

	/**
	 * Handle bulk actions via AJAX.
	 */
	public function handle_bulk_action() {
		check_ajax_referer( 'wpzoom-manage-ratings-bulk', 'nonce' );

		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'recipe-card-blocks-by-wpzoom' ) ) );
		}

		$action     = isset( $_POST['bulk_action'] ) ? sanitize_text_field( $_POST['bulk_action'] ) : '';
		$rating_ids = isset( $_POST['rating_ids'] ) ? array_map( 'intval', (array) $_POST['rating_ids'] ) : array();

		if ( empty( $action ) || empty( $rating_ids ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid request.', 'recipe-card-blocks-by-wpzoom' ) ) );
		}

		$processed = 0;

		foreach ( $rating_ids as $id ) {
			if ( $id <= 0 ) {
				continue;
			}

			switch ( $action ) {
				case 'approve':
					$result = WPZOOM_Rating_DB::update_rating( $id, array( 'approved' => 1 ) );
					if ( false !== $result ) {
						// Also approve linked comment if exists
						$rating = WPZOOM_Rating_DB::get_rating( array( 'where' => 'id = ' . intval( $id ) ) );
						if ( $rating && ! empty( $rating->comment_id ) ) {
							wp_set_comment_status( $rating->comment_id, 'approve' );
						}
						$processed++;
					}
					break;

				case 'unapprove':
					$result = WPZOOM_Rating_DB::update_rating( $id, array( 'approved' => 0 ) );
					if ( false !== $result ) {
						// Also unapprove linked comment if exists
						$rating = WPZOOM_Rating_DB::get_rating( array( 'where' => 'id = ' . intval( $id ) ) );
						if ( $rating && ! empty( $rating->comment_id ) ) {
							wp_set_comment_status( $rating->comment_id, 'hold' );
						}
						$processed++;
					}
					break;

				case 'delete':
					WPZOOM_Rating_DB::delete_ratings( $id );
					$processed++;
					break;
			}
		}

		// Get updated pending count
		$this->set_pending_count();
		$pending = isset( $this->pending_count['total'] ) ? $this->pending_count['total'] : 0;

		wp_send_json_success(
			array(
				'processed'     => $processed,
				'pending_count' => $pending,
				'message'       => sprintf(
					/* translators: %d: Number of ratings processed */
					_n( '%d rating updated.', '%d ratings updated.', $processed, 'recipe-card-blocks-by-wpzoom' ),
					$processed
				),
			)
		);
	}

	/**
	 * An internal method that sets all the necessary pagination arguments
	 *
	 * @see https://github.com/WordPress/WordPress/blob/727922c8eb60c6011888d6700052090a9de43286/wp-admin/includes/class-wp-list-table.php#L276
	 *
	 * @param array|string $args Array or string of arguments with information about the pagination.
	 */
	protected function set_pagination_args( $args ) {
		$args = wp_parse_args(
			$args,
			array(
				'total_items' => 0,
				'total_pages' => 0,
				'per_page'    => 0,
			)
		);

		if ( ! $args['total_pages'] && $args['per_page'] > 0 ) {
			$args['total_pages'] = ceil( $args['total_items'] / $args['per_page'] );
		}

		// Redirect if page number is invalid and headers are not already sent.
		if ( ! headers_sent() && $args['total_pages'] > 0 && $this->get_pagenum() > $args['total_pages'] ) {
			wp_redirect( add_query_arg( 'paged', $args['total_pages'] ) );
			exit;
		}

		$this->_pagination_args = $args;
	}

	/**
	 * Build ratings query depending on url arguments.
	 */
	public function build_query() {
		global $wpdb;

		// Check if ratings are enabled before querying
		if ( ! WPZOOM_Settings::get_rating_star_acces() && ! WPZOOM_Settings::get( 'wpzoom_rcb_settings_comment_ratings' ) ) {
			$this->ratings = array( 'total' => 0, 'ratings' => array() );
			$this->set_pagination_args(
				array(
					'total_items' => 0,
					'per_page'    => $this->get_per_page(),
				)
			);
			return;
		}

		$query_args = array();

		$search        = isset( $_GET['s'] ) ? sanitize_text_field( $_GET['s'] ) : '';
		$order         = isset( $_GET['order'] ) ? sanitize_text_field( $_GET['order'] ) : 'desc';
		$orderby       = isset( $_GET['orderby'] ) ? sanitize_text_field( $_GET['orderby'] ) : 'rate_date';
		$rating_status = isset( $_GET['rating_status'] ) ? sanitize_text_field( $_GET['rating_status'] ) : '';
		$rating_type   = isset( $_GET['rating_type'] ) ? sanitize_text_field( $_GET['rating_type'] ) : '';
		$rating_value  = isset( $_GET['rating_value'] ) ? intval( $_GET['rating_value'] ) : 0;

		// Base where clause
		$query_args['where'] = "1=1";

		// Status filter
		if ( 'approved' === $rating_status ) {
			$query_args['where'] .= " AND approved = '1'";
		} elseif ( 'pending' === $rating_status ) {
			$query_args['where'] .= " AND approved = '0'";
		} else {
			// All - show both approved and pending
			$query_args['where'] .= " AND ( approved = '0' OR approved = '1' )";
		}

		// Type filter (user ratings vs comment ratings)
		if ( 'user' === $rating_type ) {
			$query_args['where'] .= " AND comment_id = 0";
		} elseif ( 'comment' === $rating_type ) {
			$query_args['where'] .= " AND comment_id > 0";
		}

		// Rating value filter
		if ( $rating_value >= 1 && $rating_value <= 5 ) {
			$query_args['where'] .= " AND rating = " . $rating_value;
		}

		// Enhanced search - search in IP, author_name, and author_email
		if ( ! empty( $search ) ) {
			$search_escaped = esc_sql( $wpdb->esc_like( $search ) );
			$query_args['where'] .= " AND ( ip LIKE '%{$search_escaped}%' OR author_name LIKE '%{$search_escaped}%' OR author_email LIKE '%{$search_escaped}%' )";
		}

		// Sorting
		if ( $order || $orderby ) {
			$query_args['order']   = $order;
			$query_args['orderby'] = $orderby;
		}

		$ratings_per_page = $this->get_per_page();
		$page             = $this->get_pagenum();

		if ( isset( $_GET['start'] ) ) {
			$start = intval( $_GET['start'] );
		} else {
			$start = ( $page - 1 ) * $ratings_per_page;
		}

		$query_args['offset'] = $start;
		$query_args['limit']  = $ratings_per_page;

		$this->ratings = WPZOOM_Rating_DB::get_ratings( $query_args );

		$this->set_pagination_args(
			array(
				'total_items' => $this->ratings['total'],
				'per_page'    => $ratings_per_page,
			)
		);
	}

	/**
	 * Displays search results subtitle.
	 */
	public function subtitle_search_results() {
		$search = isset( $_GET['s'] ) ? $_GET['s'] : '';

		if ( ! empty( $search ) ) {
			printf( '<span class="subtitle">%s “%s”</span>', __( 'Search results for' ), esc_html( $search ) );
		}
	}

	/**
	 * Displays admin menu item bubble with pending count total value.
	 *
	 * @param  string $menu_title The menu item title.
	 * @return string             The menu item bubble.
	 */
	public function submenu_item_bubble( $menu_title ) {
		// Check if ratings are enabled before showing bubble
		if ( ! WPZOOM_Settings::get_rating_star_acces() && ! WPZOOM_Settings::get( 'wpzoom_rcb_settings_comment_ratings' ) ) {
			return $menu_title;
		}

		if ( current_user_can( 'edit_posts' ) ) {
			$this->set_pending_count();

			$awaiting_mod      = $this->pending_count['total'];
			$awaiting_mod_i18n = number_format_i18n( $awaiting_mod );
			/* translators: %s: Number of ratings. */
			$awaiting_mod_text = sprintf( _n( '%s Rating in moderation', '%s Ratings in moderation', $awaiting_mod ), $awaiting_mod_i18n );

			/* translators: %s: Number of ratings. */
			$output = sprintf( '%s %s', $menu_title, '<span class="awaiting-mod count-' . absint( $awaiting_mod ) . '"><span class="pending-count" aria-hidden="true">' . $awaiting_mod_i18n . '</span><span class="comments-in-moderation-text screen-reader-text">' . $awaiting_mod_text . '</span></span>' );

			return $output;
		}

		return $menu_title;
	}

	/**
	 * Append the moderation bubble to the top-level "Recipe Cards" menu item.
	 *
	 * Runs on admin_menu after the pages are registered so that
	 * $admin_page_hooks keeps the plain 'recipe-cards' prefix that the submenu
	 * page hooks are derived from.
	 *
	 * @since 3.5.0
	 */
	public function add_top_level_bubble() {
		global $menu;

		if ( ! is_array( $menu ) ) {
			return;
		}

		foreach ( $menu as $index => $item ) {
			if ( isset( $item[2] ) && WPZOOM_RCB_SETTINGS_PAGE === $item[2] ) {
				$menu[ $index ][0] = $this->submenu_item_bubble( $item[0] );
				break;
			}
		}
	}

	/**
	 * Adds avatars to comment author names.
	 *
	 * @param string $name       Comment author name.
	 * @param int    $comment_ID Comment ID.
	 * @return string Avatar with the user name.
	 */
	public function floated_comment_author_avatar( $name, $comment_ID ) {
		$comment = get_comment( $comment_ID );
		$avatar  = get_avatar( $comment, 32, 'mystery' );
		return "$avatar $name";
	}

	/**
	 * Adds avatars to rating author names.
	 *
	 * @param string $name       User author name.
	 * @param int    $user_id    User ID.
	 * @return string Avatar with the user name.
	 */
	public function floated_rating_author_avatar( $name, $user_id ) {
		$avatar = get_avatar( $user_id, 32, 'mystery' );
		return "$avatar $name";
	}

	/**
	 * Generate and display row actions links for comment.
	 *
	 * @global string $comment_status Status for the current listed comments.
	 *
	 * @param WP_Comment $comment     The comment object.
	 * @return string Row actions output for comments. An empty string
	 *                if the current user cannot edit the comment.
	 */
	protected function handle_comment_row_actions( $comment ) {
		global $comment_status;

		if ( ! current_user_can( 'edit_comment', $comment->comment_ID ) ) {
			return '';
		}

		$the_comment_status = wp_get_comment_status( $comment );

		$out = '';

		$del_nonce     = esc_html( '_wpnonce=' . wp_create_nonce( "delete-comment_$comment->comment_ID" ) );
		$approve_nonce = esc_html( '_wpnonce=' . wp_create_nonce( "approve-comment_$comment->comment_ID" ) );

		$url = "comment.php?c=$comment->comment_ID";

		$approve_url   = esc_url( $url . "&action=approvecomment&$approve_nonce" );
		$unapprove_url = esc_url( $url . "&action=unapprovecomment&$approve_nonce" );
		$spam_url      = esc_url( $url . "&action=spamcomment&$del_nonce" );
		$unspam_url    = esc_url( $url . "&action=unspamcomment&$del_nonce" );
		$trash_url     = esc_url( $url . "&action=trashcomment&$del_nonce" );
		$untrash_url   = esc_url( $url . "&action=untrashcomment&$del_nonce" );
		$delete_url    = esc_url( $url . "&action=deletecomment&$del_nonce" );

		// Preorder it: Approve | Edit | Spam | Trash.
		$actions = array(
			'approve'   => '',
			'unapprove' => '',
			'edit'      => '',
			'spam'      => '',
			'unspam'    => '',
			'trash'     => '',
			'untrash'   => '',
			'delete'    => '',
		);

		// Not looking at all comments.
		if ( $comment_status && 'all' !== $comment_status ) {
			if ( 'approved' === $the_comment_status ) {
				$actions['unapprove'] = sprintf(
					'<a href="%s" data-wp-lists="%s" class="vim-u vim-destructive aria-button-if-js" aria-label="%s">%s</a>',
					$unapprove_url,
					"delete:the-comment-list:comment-{$comment->comment_ID}:e7e7d3:action=dim-comment&amp;new=unapproved",
					esc_attr__( 'Unapprove this comment' ),
					__( 'Unapprove' )
				);
			} elseif ( 'unapproved' === $the_comment_status ) {
				$actions['approve'] = sprintf(
					'<a href="%s" data-wp-lists="%s" class="vim-a vim-destructive aria-button-if-js" aria-label="%s">%s</a>',
					$approve_url,
					"delete:the-comment-list:comment-{$comment->comment_ID}:e7e7d3:action=dim-comment&amp;new=approved",
					esc_attr__( 'Approve this comment' ),
					__( 'Approve' )
				);
			}
		} else {
			$actions['approve'] = sprintf(
				'<a href="%s" data-wp-lists="%s" class="vim-a aria-button-if-js" aria-label="%s">%s</a>',
				$approve_url,
				"dim:the-comment-list:comment-{$comment->comment_ID}:unapproved:e7e7d3:e7e7d3:new=approved",
				esc_attr__( 'Approve this comment' ),
				__( 'Approve' )
			);

			$actions['unapprove'] = sprintf(
				'<a href="%s" data-wp-lists="%s" class="vim-u aria-button-if-js" aria-label="%s">%s</a>',
				$unapprove_url,
				"dim:the-comment-list:comment-{$comment->comment_ID}:unapproved:e7e7d3:e7e7d3:new=unapproved",
				esc_attr__( 'Unapprove this comment' ),
				__( 'Unapprove' )
			);
		}

		if ( 'trash' === $the_comment_status || 'untrash' === $the_comment_status || 'spam' === $the_comment_status || 'unspam' === $the_comment_status ) {
			$actions['approve']   = '';
			$actions['unapprove'] = '';
		}

		if ( 'spam' !== $the_comment_status ) {
			$actions['spam'] = sprintf(
				'<a href="%s" data-wp-lists="%s" class="vim-s vim-destructive aria-button-if-js" aria-label="%s">%s</a>',
				$spam_url,
				"delete:the-comment-list:comment-{$comment->comment_ID}::spam=1",
				esc_attr__( 'Mark this comment as spam' ),
				/* translators: "Mark as spam" link. */
				_x( 'Spam', 'verb' )
			);
		} elseif ( 'spam' === $the_comment_status ) {
			$actions['unspam'] = sprintf(
				'<a href="%s" data-wp-lists="%s" class="vim-z vim-destructive aria-button-if-js" aria-label="%s">%s</a>',
				$unspam_url,
				"delete:the-comment-list:comment-{$comment->comment_ID}:66cc66:unspam=1",
				esc_attr__( 'Restore this comment from the spam' ),
				_x( 'Not Spam', 'comment' )
			);
		}

		if ( 'trash' === $the_comment_status ) {
			$actions['untrash'] = sprintf(
				'<a href="%s" data-wp-lists="%s" class="vim-z vim-destructive aria-button-if-js" aria-label="%s">%s</a>',
				$untrash_url,
				"delete:the-comment-list:comment-{$comment->comment_ID}:66cc66:untrash=1",
				esc_attr__( 'Restore this comment from the Trash' ),
				__( 'Restore' )
			);
		}

		if ( 'spam' === $the_comment_status || 'trash' === $the_comment_status || ! EMPTY_TRASH_DAYS ) {
			$actions['delete'] = sprintf(
				'<a href="%s" data-wp-lists="%s" class="delete vim-d vim-destructive aria-button-if-js" aria-label="%s">%s</a>',
				$delete_url,
				"delete:the-comment-list:comment-{$comment->comment_ID}::delete=1",
				esc_attr__( 'Delete this comment permanently' ),
				__( 'Delete Permanently' )
			);
		} else {
			$actions['trash'] = sprintf(
				'<a href="%s" data-wp-lists="%s" class="delete vim-d vim-destructive aria-button-if-js" aria-label="%s">%s</a>',
				$trash_url,
				"delete:the-comment-list:comment-{$comment->comment_ID}::trash=1",
				esc_attr__( 'Move this comment to the Trash' ),
				_x( 'Trash', 'verb' )
			);
		}

		if ( 'spam' !== $the_comment_status && 'trash' !== $the_comment_status ) {
			$actions['edit'] = sprintf(
				'<a href="%s" aria-label="%s">%s</a>',
				"comment.php?action=editcomment&amp;c={$comment->comment_ID}",
				esc_attr__( 'Edit this comment' ),
				__( 'Edit' )
			);

			$format = '<button type="button" data-comment-id="%d" data-post-id="%d" data-action="%s" class="%s button-link" aria-expanded="false" aria-label="%s">%s</button>';
		}

		$actions        = array_filter( $actions );
		$always_visible = false;

		$mode = get_user_setting( 'posts_list_mode', 'list' );

		if ( 'excerpt' === $mode ) {
			$always_visible = true;
		}

		$out .= '<div class="' . ( $always_visible ? 'row-actions visible' : 'row-actions' ) . '">';

		$i = 0;

		foreach ( $actions as $action => $link ) {
			++$i;

			if ( ( ( 'approve' === $action || 'unapprove' === $action ) && 2 === $i )
				|| 1 === $i
			) {
				$sep = '';
			} else {
				$sep = ' | ';
			}

			if ( ( 'untrash' === $action && 'trash' === $the_comment_status ) || ( 'unspam' === $action && 'spam' === $the_comment_status )
			) {
				if ( '1' == get_comment_meta( $comment->comment_ID, '_wp_trash_meta_status', true ) ) {
					$action .= ' approve';
				} else {
					$action .= ' unapprove';
				}
			}

			$out .= "<span class='$action'>$sep$link</span>";
		}

		$out .= '</div>';

		$out .= '<button type="button" class="toggle-row"><span class="screen-reader-text">' . __( 'Show more details' ) . '</span></button>';

		return $out;
	}

	/**
	 * Generate and display row actions links for user rating.
	 *
	 * @param Object  $rating    The rating object.
	 * @param WP_Post $post     The post object.
	 * @return string Row actions output for user rating. An empty string
	 *                if the current user cannot edit the post.
	 */
	protected function handle_user_rating_row_actions( $rating, $post ) {
		if ( ! current_user_can( 'edit_post', $post->ID ) ) {
			return '';
		}

		$user_id = $user_ip = '';

		$the_rating_status = $rating->approved ? 'approved' : 'unapproved';
		$user              = $rating->user_id ? get_userdata( $rating->user_id ) : 0;

		$out = '';

		$del_nonce     = esc_html( '_wpnonce=' . wp_create_nonce( "delete-rating_$post->ID" ) );
		$approve_nonce = esc_html( '_wpnonce=' . wp_create_nonce( "approve-rating_$post->ID" ) );

		$current_url = set_url_scheme( 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );

		$current_url = remove_query_arg(
			array(
				'action',
				'_wpnonce',
			),
			$current_url
		);

		if ( $user ) {
			$user_id = $user->ID;
		}
		if ( $rating->ip ) {
			$user_ip = $rating->ip;
		}

		$url = add_query_arg(
			array(
				'id'     => $rating->id,
				'post'   => $post->ID,
				'user'   => $user_id,
				'ip'     => $user_ip,
				'rating' => absint( $rating->rating ),
				'date'   => date( 'Y-m-d', strtotime( $rating->rate_date ) ),
				'time'   => date( 'H:i:s', strtotime( $rating->rate_date ) ),
			),
			$current_url
		);

		$approve_url   = esc_url( $url . "&action=approverating&$approve_nonce" );
		$unapprove_url = esc_url( $url . "&action=unapproverating&$approve_nonce" );
		$delete_url    = esc_url( $url . "&action=deleterating&$del_nonce" );

		// Preorder it: Approve | Edit | Delete.
		$actions = array(
			'approve'   => '',
			'unapprove' => '',
			'edit'      => '',
			'delete'    => '',
		);

		$actions['approve'] = sprintf(
			'<a href="%s" class="approve aria-button-if-js" aria-label="%s">%s</a>',
			$approve_url,
			esc_attr__( 'Approve this rating', 'recipe-card-blocks-by-wpzoom' ),
			__( 'Approve' )
		);

		$actions['unapprove'] = sprintf(
			'<a href="%s" class="unapprove aria-button-if-js" aria-label="%s">%s</a>',
			$unapprove_url,
			esc_attr__( 'Unapprove this rating', 'recipe-card-blocks-by-wpzoom' ),
			__( 'Unapprove' )
		);

		$actions['delete'] = sprintf(
			'<a href="%s" class="delete aria-button-if-js" aria-label="%s">%s</a>',
			$delete_url,
			esc_attr__( 'Delete this rating permanently', 'recipe-card-blocks-by-wpzoom' ),
			__( 'Delete Permanently' )
		);

		$actions = array_filter( $actions );

		$out .= '<div class="row-actions">';

		$i = 0;

		foreach ( $actions as $action => $link ) {
			++$i;

			if ( ( ( 'approve' === $action || 'unapprove' === $action ) && 2 === $i )
				|| 1 === $i
			) {
				$sep = '';
			} else {
				$sep = ' | ';
			}

			if ( ( 'untrash' === $action && 'trash' === $the_comment_status ) || ( 'unspam' === $action && 'spam' === $the_comment_status )
			) {
				if ( '1' == get_comment_meta( $comment->comment_ID, '_wp_trash_meta_status', true ) ) {
					$action .= ' approve';
				} else {
					$action .= ' unapprove';
				}
			}

			$out .= "<span class='$action'>$sep$link</span>";
		}

		$out .= '</div>';

		$out .= '<button type="button" class="toggle-row"><span class="screen-reader-text">' . __( 'Show more details' ) . '</span></button>';

		return $out;
	}

	/**
	 * Displays the pagination.
	 *
	 * @see https://github.com/WordPress/WordPress/blob/727922c8eb60c6011888d6700052090a9de43286/wp-admin/includes/class-wp-list-table.php#L858
	 *
	 * @param string $which
	 */
	protected function pagination( $which ) {
		if ( empty( $this->_pagination_args ) ) {
			return;
		}

		$total_items = $this->_pagination_args['total_items'];
		$total_pages = $this->_pagination_args['total_pages'];

		$output = '<span class="displaying-num">' . sprintf(
			/* translators: %s: Number of items. */
			_n( '%s item', '%s items', $total_items ),
			number_format_i18n( $total_items )
		) . '</span>';

		$current              = $this->get_pagenum();
		$removable_query_args = wp_removable_query_args();

		$current_url = set_url_scheme( 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );

		$current_url = remove_query_arg( $removable_query_args, $current_url );

		$page_links = array();

		$total_pages_before = '<span class="paging-input">';
		$total_pages_after  = '</span></span>';

		$disable_first = false;
		$disable_last  = false;
		$disable_prev  = false;
		$disable_next  = false;

		if ( 1 == $current ) {
			$disable_first = true;
			$disable_prev  = true;
		}
		if ( 2 == $current ) {
			$disable_first = true;
		}
		if ( $total_pages == $current ) {
			$disable_last = true;
			$disable_next = true;
		}
		if ( $total_pages - 1 == $current ) {
			$disable_last = true;
		}

		if ( $disable_first ) {
			$page_links[] = '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&laquo;</span>';
		} else {
			$page_links[] = sprintf(
				"<a class='first-page button' href='%s'><span class='screen-reader-text'>%s</span><span aria-hidden='true'>%s</span></a>",
				esc_url( remove_query_arg( 'paged', $current_url ) ),
				__( 'First page' ),
				'&laquo;'
			);
		}

		if ( $disable_prev ) {
			$page_links[] = '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&lsaquo;</span>';
		} else {
			$page_links[] = sprintf(
				"<a class='prev-page button' href='%s'><span class='screen-reader-text'>%s</span><span aria-hidden='true'>%s</span></a>",
				esc_url( add_query_arg( 'paged', max( 1, $current - 1 ), $current_url ) ),
				__( 'Previous page' ),
				'&lsaquo;'
			);
		}

		if ( 'bottom' === $which ) {
			$html_current_page  = $current;
			$total_pages_before = '<span class="screen-reader-text">' . __( 'Current Page' ) . '</span><span id="table-paging" class="paging-input"><span class="tablenav-paging-text">';
		} else {
			$html_current_page = sprintf(
				"%s<input class='current-page' id='current-page-selector' type='text' name='paged' value='%s' size='%d' aria-describedby='table-paging' /><span class='tablenav-paging-text'>",
				'<label for="current-page-selector" class="screen-reader-text">' . __( 'Current Page' ) . '</label>',
				$current,
				strlen( $total_pages )
			);
		}
		$html_total_pages = sprintf( "<span class='total-pages'>%s</span>", number_format_i18n( $total_pages ) );
		$page_links[]     = $total_pages_before . sprintf(
			/* translators: 1: Current page, 2: Total pages. */
			_x( '%1$s of %2$s', 'paging' ),
			$html_current_page,
			$html_total_pages
		) . $total_pages_after;

		if ( $disable_next ) {
			$page_links[] = '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&rsaquo;</span>';
		} else {
			$page_links[] = sprintf(
				"<a class='next-page button' href='%s'><span class='screen-reader-text'>%s</span><span aria-hidden='true'>%s</span></a>",
				esc_url( add_query_arg( 'paged', min( $total_pages, $current + 1 ), $current_url ) ),
				__( 'Next page' ),
				'&rsaquo;'
			);
		}

		if ( $disable_last ) {
			$page_links[] = '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&raquo;</span>';
		} else {
			$page_links[] = sprintf(
				"<a class='last-page button' href='%s'><span class='screen-reader-text'>%s</span><span aria-hidden='true'>%s</span></a>",
				esc_url( add_query_arg( 'paged', $total_pages, $current_url ) ),
				__( 'Last page' ),
				'&raquo;'
			);
		}

		$pagination_links_class = 'pagination-links';
		$output                .= "\n<span class='$pagination_links_class'>" . implode( "\n", $page_links ) . '</span>';

		if ( $total_pages ) {
			$page_class = $total_pages < 2 ? ' one-page' : '';
		} else {
			$page_class = ' no-pages';
		}
		$this->_pagination = "<div class='tablenav-pages{$page_class}'>$output</div>";

		echo $this->_pagination;
	}

	/**
	 * @see https://github.com/WordPress/WordPress/blob/0e3147c40e91f6eb1f57585724be173e3c04a719/wp-admin/includes/class-wp-comments-list-table.php#L452
	 *
	 * @return array
	 */
	public function get_columns() {
		$columns = array();

		$columns['author']   = __( 'Author' );
		$columns['type']     = _x( 'Type', 'column name' );
		$columns['visibility'] = _x( 'Visibility', 'column name' );
		$columns['ip']       = _x( 'IP', 'column name' );
		$columns['comment']  = _x( 'Rating', 'column name' );
		$columns['response'] = __( 'Recipe' );
		$columns['date']     = _x( 'Submitted on', 'column name' );

		return $columns;
	}

	/**
	 * @see https://github.com/WordPress/WordPress/blob/0e3147c40e91f6eb1f57585724be173e3c04a719/wp-admin/includes/class-wp-comments-list-table.php#L527
	 *
	 * @return array
	 */
	protected function get_sortable_columns() {
		return array(
			'comment' => array( 'rating', false ),
			'date'    => array( 'rate_date', false ),
		);
	}

	/**
	 * Gets the current page number.
	 *
	 * @see https://github.com/WordPress/WordPress/blob/727922c8eb60c6011888d6700052090a9de43286/wp-admin/includes/class-wp-list-table.php#L799
	 *
	 * @return int
	 */
	public function get_pagenum() {
		$pagenum = isset( $_GET['paged'] ) ? absint( $_GET['paged'] ) : 0;

		if ( isset( $this->_pagination_args['total_pages'] ) && $pagenum > $this->_pagination_args['total_pages'] ) {
			$pagenum = $this->_pagination_args['total_pages'];
		}

		return max( 1, $pagenum );
	}

	/**
	 * @return int
	 */
	public function get_per_page() {
		$ratings_per_page = $this->get_items_per_page( 'wpzoom_manage_ratings_per_page' );
		return $ratings_per_page;
	}

	/**
	 * Gets the number of items to display on a single page.
	 *
	 * @see https://github.com/WordPress/WordPress/blob/727922c8eb60c6011888d6700052090a9de43286/wp-admin/includes/class-wp-list-table.php#L818
	 *
	 * @param string $option
	 * @param int    $default
	 * @return int
	 */
	protected function get_items_per_page( $option, $default = 20 ) {
		$per_page = (int) get_user_option( $option );
		if ( empty( $per_page ) || $per_page < 1 ) {
			$per_page = $default;
		}

		/**
		 * Filters the number of items to be displayed on each page of the list table.
		 *
		 * The dynamic hook name, `$option`, refers to the `per_page` option depending
		 * on the type of list table in use. Possible filter names include:
		 *
		 *  - `wpzoom_manage_ratings_per_page`
		 *
		 * @param int $per_page Number of items to be displayed. Default 20.
		 */
		return (int) apply_filters( "{$option}", $per_page );
	}

	/**
	 * Prints column headers, accounting for hidden and sortable columns.
	 *
	 * @see https://github.com/WordPress/WordPress/blob/727922c8eb60c6011888d6700052090a9de43286/wp-admin/includes/class-wp-list-table.php#L1172
	 *
	 * @param bool $with_id Whether to set the ID attribute or not
	 */
	public function print_column_headers( $with_id = true ) {
		$columns  = $this->get_columns();
		$sortable = $this->get_sortable_columns();

		$current_url = set_url_scheme( 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );
		$current_url = remove_query_arg( 'paged', $current_url );

		if ( isset( $_GET['orderby'] ) ) {
			$current_orderby = $_GET['orderby'];
		} else {
			$current_orderby = '';
		}

		if ( isset( $_GET['order'] ) && 'desc' === $_GET['order'] ) {
			$current_order = 'desc';
		} else {
			$current_order = 'asc';
		}

		if ( ! empty( $columns['cb'] ) ) {
			static $cb_counter = 1;
			$columns['cb']     = '<label class="screen-reader-text" for="cb-select-all-' . $cb_counter . '">' . __( 'Select All' ) . '</label>'
				. '<input id="cb-select-all-' . $cb_counter . '" type="checkbox" />';
			$cb_counter++;
		}

		foreach ( $columns as $column_key => $column_display_name ) {
			$class = array( 'manage-column', "column-$column_key" );

			if ( 'cb' === $column_key ) {
				$class[] = 'check-column';
			} elseif ( in_array( $column_key, array( 'posts', 'comments', 'links' ), true ) ) {
				$class[] = 'num';
			}

			if ( isset( $sortable[ $column_key ] ) ) {
				list( $orderby, $desc_first ) = $sortable[ $column_key ];

				if ( $current_orderby === $orderby ) {
					$order = 'asc' === $current_order ? 'desc' : 'asc';

					$class[] = 'sorted';
					$class[] = $current_order;
				} else {
					$order = strtolower( $desc_first );

					if ( ! in_array( $order, array( 'desc', 'asc' ), true ) ) {
						$order = $desc_first ? 'desc' : 'asc';
					}

					$class[] = 'sortable';
					$class[] = 'desc' === $order ? 'asc' : 'desc';
				}

				$column_display_name = sprintf(
					'<a href="%s"><span>%s</span><span class="sorting-indicator"></span></a>',
					esc_url( add_query_arg( compact( 'orderby', 'order' ), $current_url ) ),
					$column_display_name
				);
			}

			$tag   = ( 'cb' === $column_key ) ? 'td' : 'th';
			$scope = ( 'th' === $tag ) ? 'scope="col"' : '';
			$id    = $with_id ? "id='$column_key'" : '';

			if ( ! empty( $class ) ) {
				$class = "class='" . implode( ' ', $class ) . "'";
			}

			echo "<$tag $scope $id $class>$column_display_name</$tag>";
		}
	}

	/**
	 * @param WP_Comment $comment The comment object.
	 */
	public function column_author_comment( $comment ) {
		$author     = get_comment_author( $comment );
		$author_url = get_comment_author_url( $comment );

		$author_url_display = untrailingslashit( preg_replace( '|^http(s)?://(www\.)?|i', '', $author_url ) );
		if ( strlen( $author_url_display ) > 50 ) {
			$author_url_display = wp_html_excerpt( $author_url_display, 49, '&hellip;' );
		}

		echo '<strong>';
		echo apply_filters( 'wpzoom_manage_ratings_comment_author', $author, $comment->comment_ID );
		echo '</strong><br />';
		if ( ! empty( $author_url_display ) ) {
			printf( '<a href="%s">%s</a><br />', esc_url( $author_url ), esc_html( $author_url_display ) );
		}

		if ( current_user_can( 'edit_comment', $comment->comment_ID ) ) {
			if ( ! empty( $comment->comment_author_email ) ) {
				/** This filter is documented in wp-includes/comment-template.php */
				$email = apply_filters( 'comment_email', $comment->comment_author_email, $comment );

				if ( ! empty( $email ) && '@' !== $email ) {
					printf( '<a href="%1$s">%2$s</a><br />', esc_url( 'mailto:' . $email ), esc_html( $email ) );
				}
			}
		}
	}

	/**
	 * @param WP_User $user The user object.
	 */
	public function column_author_user( $user ) {
		$author_url  = isset( $user->user_url ) ? $user->user_url : '';
		$author_name = isset( $user->display_name ) && ! empty( $user->display_name ) ? $user->display_name : $user->user_nicename;

		$author_url_display = untrailingslashit( preg_replace( '|^http(s)?://(www\.)?|i', '', $author_url ) );
		if ( strlen( $author_url_display ) > 50 ) {
			$author_url_display = wp_html_excerpt( $author_url_display, 49, '&hellip;' );
		}

		echo '<strong>';
		echo apply_filters( 'wpzoom_manage_ratings_author', $author_name, $user->ID );
		echo '</strong><br />';
		if ( ! empty( $author_url_display ) ) {
			printf( '<a href="%s">%s</a><br />', esc_url( $author_url ), esc_html( $author_url_display ) );
		}

		if ( current_user_can( 'edit_posts' ) ) {
			if ( ! empty( $user->user_email ) ) {
				$email = apply_filters( 'wpzoom_manage_ratings_author_email', $user->user_email, $user );

				if ( ! empty( $email ) && '@' !== $email ) {
					printf( '<a href="%1$s">%2$s</a><br />', esc_url( 'mailto:' . $email ), esc_html( $email ) );
				}
			}
		}
	}

	/**
	 * Displays author info for guest users (non-logged-in users who submitted via modal).
	 *
	 * @param Object $rating The rating object.
	 */
	public function column_author_guest( $rating ) {
		$author_name  = ! empty( $rating->author_name ) ? $rating->author_name : __( 'Guest', 'recipe-card-blocks-by-wpzoom' );
		$author_email = ! empty( $rating->author_email ) ? $rating->author_email : '';

		// Get avatar from email if available, otherwise use default mystery avatar
		$avatar = get_avatar( $author_email ? $author_email : 0, 32, 'mystery' );

		echo '<strong>';
		// Output avatar and name directly (don't use filter to avoid double avatar)
		echo $avatar . ' ' . esc_html( $author_name );
		echo '</strong><br />';

		if ( current_user_can( 'edit_posts' ) && ! empty( $author_email ) ) {
			printf( '<a href="%1$s">%2$s</a><br />', esc_url( 'mailto:' . $author_email ), esc_html( $author_email ) );
		}
	}

	/**
	 * Displays only avatar for unknown user.
	 *
	 * @deprecated 3.3.0 Use column_author_guest() instead.
	 */
	public function column_author_unknown() {
		$avatar = get_avatar( 0, 32, 'gravatar_default' );
		$name   = __( 'Guest', 'recipe-card-blocks-by-wpzoom' );
		echo "$avatar $name";
	}

	/**
	 * Displays the ip of comment author or get from rating object.
	 *
	 * @param WP_Comment $comment The comment object.
	 * @param Object     $rating The rating object.
	 */
	public function column_ip( $comment, $rating ) {
		$current_url = set_url_scheme( 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );
		$current_url = remove_query_arg( 'paged', $current_url );

		if ( $comment ) {
			$comment_status = wp_get_comment_status( $comment );
			$author_ip      = get_comment_author_IP( $comment );

			if ( $author_ip ) {
				$author_ip_url = add_query_arg(
					array(
						'page' => $this->_page_slug,
						's'    => $author_ip,
					),
					$current_url
				);
				if ( 'spam' === $comment_status ) {
					$author_ip_url = add_query_arg( 'comment_status', 'spam', $author_ip_url );
				}
				printf( '<a href="%1$s">%2$s</a>', esc_url( $author_ip_url ), esc_html( $author_ip ) );
			}
		} elseif ( $rating->ip ) {
			$author_ip_url = add_query_arg(
				array(
					'page' => $this->_page_slug,
					's'    => $rating->ip,
				),
				$current_url
			);
			printf( '<a href="%1$s">%2$s</a>', esc_url( $author_ip_url ), esc_html( $rating->ip ) );
		} else {
			_e( 'n/a', 'recipe-card-blocks-by-wpzoom' );
		}
	}

	/**
	 * Displays the type of rating.
	 *
	 * @param WP_Comment $comment The comment object.
	 */
	public function column_type( $comment ) {
		if ( $comment ) {
			printf( '<span class="type-badge type-comment">%s</span>', esc_html__( 'Comment Rating', 'recipe-card-blocks-by-wpzoom' ) );
		} else {
			printf( '<span class="type-badge type-user">%s</span>', esc_html__( 'User Rating', 'recipe-card-blocks-by-wpzoom' ) );
		}
	}

	/**
	 * Displays review visibility status.
	 *
	 * @param WP_Comment|false $comment Comment object when review is public.
	 * @param object           $rating  Rating object.
	 */
	public function column_visibility( $comment, $rating ) {
		if ( $comment ) {
			printf( '<span class="type-badge type-comment">%s</span>', esc_html__( 'Public', 'recipe-card-blocks-by-wpzoom' ) );
			return;
		}

		if ( ! empty( $rating->review_text ) ) {
			printf( '<span class="type-badge type-user">%s</span>', esc_html__( 'Private', 'recipe-card-blocks-by-wpzoom' ) );
			return;
		}

		echo esc_html__( 'Rating only', 'recipe-card-blocks-by-wpzoom' );
	}

	/**
	 * Displays the type of rating.
	 *
	 * @param WP_Comment $comment The comment object.
	 */
	public function column_comment( $comment ) {
		comment_text( $comment );
		echo $this->handle_comment_row_actions( $comment );
	}

	/**
	 * Displays the type of rating.
	 *
	 * @param Object  $rating The rating object.
	 * @param WP_Post $post The post object.
	 */
	public function column_rating_stars( $rating, $post = 0 ) {
		$out                = '';
		$rating_stars_items = '';

		$out .= '<div class="wpzoom-rcb-comment-rating"><div class="wpzoom-rcb-comment-rating-stars" style="color:#ffb900">';

		for ( $i = 1; $i <= 5; $i++ ) {
			if ( $i <= $rating->rating ) {
				$rating_stars_items .= '<span class="dashicons dashicons-star-filled"></span>';
			} else {
				$rating_stars_items .= '<span class="dashicons dashicons-star-empty"></span>';
			}
		}

		$out .= $rating_stars_items;
		$out .= '</div></div>';

		if ( ! empty( $rating->review_text ) ) {
			$out .= '<p class="wpzoom-rating-private-review"><strong>' . esc_html__( 'Private review:', 'recipe-card-blocks-by-wpzoom' ) . '</strong> ' . esc_html( $rating->review_text ) . '</p>';
		}

		if ( $post ) {
			$out .= '<p></p>';
			$out .= $this->handle_user_rating_row_actions( $rating, $post );
		}

		echo $out;
	}

	/**
	 * @param WP_Comment $comment The comment object.
	 * @param Object     $rating The rating object.
	 */
	public function column_date( $comment, $rating ) {
		if ( $comment ) {
			$submitted = sprintf(
				/* translators: 1: Comment date, 2: Comment time. */
				__( '%1$s at %2$s' ),
				/* translators: Comment date format. See https://www.php.net/manual/datetime.format.php */
				get_comment_date( __( 'Y/m/d' ), $comment ),
				/* translators: Comment time format. See https://www.php.net/manual/datetime.format.php */
				get_comment_date( __( 'g:i a' ), $comment )
			);
			echo '<div class="submitted-on">';
			if ( 'approved' === wp_get_comment_status( $comment ) && ! empty( $comment->comment_post_ID ) ) {
				printf(
					'<a href="%s">%s</a>',
					esc_url( get_comment_link( $comment ) ),
					$submitted
				);
			} else {
				echo $submitted;
			}
			echo '</div>';
		} else {
			$submitted = sprintf(
				/* translators: 1: Rating date, 2: Rating time. */
				__( '%1$s at %2$s' ),
				/* translators: Rating date format. See https://www.php.net/manual/datetime.format.php */
				date( __( 'Y/m/d' ), strtotime( $rating->rate_date ) ),
				/* translators: Rating time format. See https://www.php.net/manual/datetime.format.php */
				date( __( 'g:i a' ), strtotime( $rating->rate_date ) )
			);

			echo '<div class="submitted-on">' . $submitted . '</div>';
		}
	}

	/**
	 * @param WP_Post $post The post object.
	 */
	public function column_response( $post ) {
		if ( ! $post ) {
			return;
		}

		if ( current_user_can( 'edit_post', $post->ID ) ) {
			$post_link  = "<a href='" . get_edit_post_link( $post->ID ) . "' class='comments-edit-item-link'>";
			$post_link .= esc_html( get_the_title( $post->ID ) ) . '</a>';
		} else {
			$post_link = esc_html( get_the_title( $post->ID ) );
		}

		echo '<div class="response-links">';
		if ( 'attachment' === $post->post_type ) {
			$thumb = wp_get_attachment_image( $post->ID, array( 80, 60 ), true );
			if ( $thumb ) {
				echo $thumb;
			}
		}
		echo $post_link;
		$post_type_object = get_post_type_object( $post->post_type );

		// For recipe CPT, link to parent post instead (recipe CPT is not public)
		if ( 'wpzoom_rcb' === $post->post_type ) {
			$parent_id = get_post_meta( $post->ID, '_wpzoom_rcb_parent_post_id', true );
			$view_link = ( $parent_id && $parent_id != $post->ID ) ? get_permalink( $parent_id ) : '';
			if ( $view_link ) {
				echo "<a href='" . esc_url( $view_link ) . "' class='comments-view-item-link' target='_blank'>" . esc_html__( 'View Post', 'recipe-card-blocks-by-wpzoom' ) . '</a>';
			}
		} else {
			echo "<a href='" . get_permalink( $post->ID ) . "' class='comments-view-item-link'>" . $post_type_object->labels->view_item . '</a>';
		}
		echo '</div>';
	}

	/**
	 * Display status tabs (All, Pending, Approved).
	 */
	public function display_status_tabs() {
		$stats         = $this->get_ratings_statistics();
		$current_status = isset( $_GET['rating_status'] ) ? sanitize_text_field( $_GET['rating_status'] ) : '';
		$base_url      = admin_url( 'admin.php?page=' . $this->_page_slug );

		// Preserve other filters in the URL
		$preserved_params = array( 'rating_type', 'rating_value', 's' );
		foreach ( $preserved_params as $param ) {
			if ( ! empty( $_GET[ $param ] ) ) {
				$base_url = add_query_arg( $param, sanitize_text_field( $_GET[ $param ] ), $base_url );
			}
		}

		$tabs = array(
			''         => array(
				'label' => __( 'All', 'recipe-card-blocks-by-wpzoom' ),
				'count' => $stats['total'],
			),
			'pending'  => array(
				'label' => __( 'Pending', 'recipe-card-blocks-by-wpzoom' ),
				'count' => $stats['pending'],
			),
			'approved' => array(
				'label' => __( 'Approved', 'recipe-card-blocks-by-wpzoom' ),
				'count' => $stats['approved'],
			),
		);

		echo '<ul class="subsubsub">';
		$tab_links = array();
		foreach ( $tabs as $status => $tab ) {
			$url   = $status ? add_query_arg( 'rating_status', $status, $base_url ) : remove_query_arg( 'rating_status', $base_url );
			$class = ( $current_status === $status ) ? 'current' : '';
			$tab_links[] = sprintf(
				'<li><a href="%s" class="%s">%s <span class="count">(%s)</span></a></li>',
				esc_url( $url ),
				esc_attr( $class ),
				esc_html( $tab['label'] ),
				number_format_i18n( $tab['count'] )
			);
		}
		echo implode( ' | ', $tab_links );
		echo '</ul>';
	}

	/**
	 * Upsell shown in place of the ratings analytics.
	 *
	 * @since 3.5.0
	 * @return void
	 */
	private function display_analytics_upsell() {
		$total = 0;

		if ( class_exists( 'WPZOOM_Rating_DB' ) ) {
			$ratings = WPZOOM_Rating_DB::get_ratings( array( 'where' => 'approved = 1' ) );
			$total   = isset( $ratings['total'] ) ? (int) $ratings['total'] : 0;
		}

		$upgrade_url = class_exists( 'WPZOOM_Plugin_Activator' )
			? WPZOOM_Plugin_Activator::get_store_url( 'pricing/?utm_source=wpadmin&utm_medium=manage-ratings&utm_campaign=ratings-analytics' )
			: 'https://recipecard.io/pricing/';
		?>
		<div class="wpzoom-ratings-analytics-upsell">
			<div class="wpzoom-ratings-analytics-upsell-body">
				<h3>
					<span class="dashicons dashicons-chart-bar"></span>
					<?php esc_html_e( 'Ratings Analytics', 'recipe-card-blocks-by-wpzoom' ); ?>
					<span class="wpzoom-rcb-badge wpzoom-rcb-field-is_premium"><?php esc_html_e( 'PRO Feature', 'recipe-card-blocks-by-wpzoom' ); ?></span>
				</h3>
				<p>
					<?php
					printf(
						/* translators: %s: number of ratings collected so far */
						esc_html( _n( 'You have collected %s rating so far. Upgrade to see how your recipes are performing:', 'You have collected %s ratings so far. Upgrade to see how your recipes are performing:', $total, 'recipe-card-blocks-by-wpzoom' ) ),
						'<strong>' . esc_html( number_format_i18n( $total ) ) . '</strong>'
					);
					?>
				</p>
				<ul>
					<li><?php esc_html_e( 'Total, approved, pending and average rating at a glance', 'recipe-card-blocks-by-wpzoom' ); ?></li>
					<li><?php esc_html_e( '7-day activity chart with period-on-period change', 'recipe-card-blocks-by-wpzoom' ); ?></li>
					<li><?php esc_html_e( 'Rating distribution across 1 to 5 stars', 'recipe-card-blocks-by-wpzoom' ); ?></li>
					<li><?php esc_html_e( 'Top rated recipes and most voted recipes, by date range', 'recipe-card-blocks-by-wpzoom' ); ?></li>
				</ul>
				<a href="<?php echo esc_url( $upgrade_url ); ?>" class="button button-primary" target="_blank" rel="noopener noreferrer">
					<?php esc_html_e( 'Upgrade to PRO', 'recipe-card-blocks-by-wpzoom' ); ?>
				</a>
			</div>
		</div>
		<?php
	}

	/**
	 * Display statistics summary box.
	 *
	 * Ratings analytics - the counts, the 7-day chart, the distribution and the
	 * Top Rated list - are a PRO feature. The moderation list itself stays
	 * available so free users can still deal with spam.
	 */
	public function display_statistics_box() {
		if ( ! WPZOOM_RCB_HAS_PRO ) {
			$this->display_analytics_upsell();
			return;
		}

		$stats = $this->get_ratings_statistics();

		if ( $stats['total'] <= 0 ) {
			return;
		}

		$average = $stats['average'] ? number_format( $stats['average'], 1 ) : '0.0';

		// Calculate 30-day percentage change
		$percent_change      = 0;
		$percent_change_text = '';
		if ( $stats['previous_30_days'] > 0 ) {
			$percent_change = round( ( ( $stats['past_30_days'] - $stats['previous_30_days'] ) / $stats['previous_30_days'] ) * 100 );
		} elseif ( $stats['past_30_days'] > 0 ) {
			$percent_change = 100; // If no previous period but has current, it's 100% increase
		}

		if ( $percent_change > 0 ) {
			$percent_change_text = '+' . $percent_change . '%';
		} elseif ( $percent_change < 0 ) {
			$percent_change_text = $percent_change . '%';
		}

		// Prepare 7-day chart data (reverse so oldest is first)
		$past_7_days = array_reverse( $stats['past_7_days'] );
		$max_day     = max( $past_7_days );

		// Generate day labels using WordPress timezone (oldest to newest: 6 days ago to today)
		$current_time = current_time( 'timestamp' );
		$day_labels   = array();
		for ( $i = 6; $i >= 0; $i-- ) {
			$day_labels[] = wp_date( 'D', strtotime( "-$i days", $current_time ) );
		}
		?>
		<div class="wpzoom-ratings-stats-box">
			<div class="stats-row stats-counts">
				<span class="stat-item">
					<strong><?php echo number_format_i18n( $stats['total'] ); ?></strong>
					<?php _e( 'Total', 'recipe-card-blocks-by-wpzoom' ); ?>
				</span>
				<span class="stat-divider">|</span>
				<span class="stat-item stat-approved">
					<strong><?php echo number_format_i18n( $stats['approved'] ); ?></strong>
					<?php _e( 'Approved', 'recipe-card-blocks-by-wpzoom' ); ?>
				</span>
				<span class="stat-divider">|</span>
				<span class="stat-item stat-pending">
					<strong><?php echo number_format_i18n( $stats['pending'] ); ?></strong>
					<?php _e( 'Pending', 'recipe-card-blocks-by-wpzoom' ); ?>
				</span>
				<span class="stat-divider">|</span>
				<span class="stat-item">
					<strong><?php echo esc_html( $average ); ?></strong>
					<span class="dashicons dashicons-star-filled" style="color: #ffb900; font-size: 14px; width: 14px; height: 14px;"></span>
					<?php _e( 'Average', 'recipe-card-blocks-by-wpzoom' ); ?>
				</span>
			</div>

			<div class="stats-row stats-details">
				<div class="stats-activity-section">
					<div class="stats-activity-header">
						<span class="stats-activity-title"><?php _e( 'Last 7 Days', 'recipe-card-blocks-by-wpzoom' ); ?></span>
						<span class="stats-activity-30day">
							<?php
							printf(
								/* translators: %s: number of ratings */
								__( 'Past 30 days: %s', 'recipe-card-blocks-by-wpzoom' ),
								'<strong>' . number_format_i18n( $stats['past_30_days'] ) . '</strong>'
							);
							if ( $percent_change_text ) :
								$change_class = $percent_change > 0 ? 'positive' : 'negative';
								?>
								<span class="stats-percent-change <?php echo esc_attr( $change_class ); ?>"><?php echo esc_html( $percent_change_text ); ?></span>
							<?php endif; ?>
						</span>
					</div>
					<div class="stats-chart-bars">
						<?php foreach ( $past_7_days as $index => $count ) :
							$bar_height = $max_day > 0 ? ( $count / $max_day ) * 100 : 0;
							$bar_height = max( $bar_height, 4 ); // Minimum height for visibility
							$tooltip = sprintf(
								/* translators: 1: day name, 2: number of ratings */
								_n( '%1$s: %2$d rating', '%1$s: %2$d ratings', $count, 'recipe-card-blocks-by-wpzoom' ),
								$day_labels[ $index ],
								$count
							);
							?>
							<div class="stats-chart-bar-wrapper" data-tooltip="<?php echo esc_attr( $tooltip ); ?>">
								<div class="stats-chart-bar" style="height: <?php echo esc_attr( $bar_height ); ?>%;"></div>
								<span class="stats-chart-label"><?php echo esc_html( substr( $day_labels[ $index ], 0, 1 ) ); ?></span>
							</div>
						<?php endforeach; ?>
					</div>
				</div>
				<?php if ( $stats['approved'] > 0 ) : ?>
				<div class="stats-distribution-section">
					<span class="stats-distribution-title"><?php _e( 'Rating Distribution', 'recipe-card-blocks-by-wpzoom' ); ?></span>
					<div class="stats-distribution-items">
						<?php
						foreach ( $stats['distribution'] as $rating => $count ) :
							$percentage = $stats['approved'] > 0 ? round( ( $count / $stats['approved'] ) * 100 ) : 0;
							?>
							<div class="dist-item" title="<?php echo esc_attr( sprintf( __( '%d ratings', 'recipe-card-blocks-by-wpzoom' ), $count ) ); ?>">
								<span class="dist-stars"><?php echo esc_html( $rating ); ?>★</span>
								<span class="dist-bar"><span class="dist-fill" style="width: <?php echo esc_attr( $percentage ); ?>%;"></span></span>
								<span class="dist-percent"><?php echo esc_html( $percentage ); ?>%</span>
							</div>
						<?php endforeach; ?>
					</div>
				</div>
				<?php endif; ?>
			</div>
		</div>
		<?php

		// Display top rated recipes section
		$this->display_top_rated_recipes();
	}

	/**
	 * Get top rated recipes data.
	 *
	 * @param int    $limit       Number of recipes to return.
	 * @param string $date_filter Date filter: 'all', '7days', '30days', 'month', 'year'.
	 * @return array Array with 'by_rating' and 'by_votes' keys.
	 */
	private function get_top_rated_recipes( $limit = 5, $date_filter = 'all' ) {
		global $wpdb;

		$recipes_data = array(
			'by_rating' => array(),
			'by_votes'  => array(),
		);

		// Build date condition for SQL
		$date_condition = $this->get_date_condition( $date_filter );

		// Get all published recipes
		$recipes = get_posts( array(
			'post_type'   => 'wpzoom_rcb',
			'post_status' => 'publish',
			'numberposts' => -1,
		) );

		if ( empty( $recipes ) ) {
			return $recipes_data;
		}

		$table_name    = WPZOOM_Rating_DB::get_table_name();
		$rated_recipes = array();

		foreach ( $recipes as $recipe ) {
			$parent_id = get_post_meta( $recipe->ID, '_wpzoom_rcb_parent_post_id', true );
			$parent_id = $parent_id ? $parent_id : $recipe->ID;

			// Get ratings with date filter (check both recipe_id and post_id like dashboard widget)
			$where = $wpdb->prepare( '(recipe_id = %d OR post_id = %d) AND approved = 1', $parent_id, $parent_id );
			if ( $date_condition ) {
				$where .= ' AND ' . $date_condition;
			}

			$ratings_result = WPZOOM_Rating_DB::get_ratings( array( 'where' => $where ) );
			$total_ratings  = $ratings_result['total'];

			if ( $total_ratings > 0 ) {
				// Calculate average from filtered ratings
				$sum = 0;
				foreach ( $ratings_result['ratings'] as $rating ) {
					$sum += intval( $rating->rating );
				}
				$average_rating = $sum / $total_ratings;

				$rated_recipes[] = array(
					'post'           => $recipe,
					'parent_id'      => $parent_id,
					'total_votes'    => $total_ratings,
					'average_rating' => $average_rating,
				);
			}
		}

		if ( empty( $rated_recipes ) ) {
			return $recipes_data;
		}

		// Sort by highest rating
		$by_rating = $rated_recipes;
		usort( $by_rating, function( $a, $b ) {
			if ( $a['average_rating'] == $b['average_rating'] ) {
				return $b['total_votes'] <=> $a['total_votes']; // Tie-breaker: more votes
			}
			return $b['average_rating'] <=> $a['average_rating'];
		});
		$recipes_data['by_rating'] = array_slice( $by_rating, 0, $limit );

		// Sort by most votes
		$by_votes = $rated_recipes;
		usort( $by_votes, function( $a, $b ) {
			if ( $a['total_votes'] == $b['total_votes'] ) {
				return $b['average_rating'] <=> $a['average_rating']; // Tie-breaker: higher rating
			}
			return $b['total_votes'] <=> $a['total_votes'];
		});
		$recipes_data['by_votes'] = array_slice( $by_votes, 0, $limit );

		return $recipes_data;
	}

	/**
	 * Get SQL date condition for filtering.
	 *
	 * @param string $filter Date filter type.
	 * @return string SQL condition or empty string.
	 */
	private function get_date_condition( $filter ) {
		switch ( $filter ) {
			case '7days':
				return 'rate_date >= DATE_SUB(NOW(), INTERVAL 7 DAY)';
			case '30days':
				return 'rate_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)';
			case 'month':
				return 'MONTH(rate_date) = MONTH(CURRENT_DATE()) AND YEAR(rate_date) = YEAR(CURRENT_DATE())';
			case 'year':
				return 'YEAR(rate_date) = YEAR(CURRENT_DATE())';
			case 'all':
			default:
				return '';
		}
	}

	/**
	 * Display the top rated recipes section.
	 */
	private function display_top_rated_recipes() {
		// Get the current date filter from URL parameter
		$current_filter = isset( $_GET['top_rated_filter'] ) ? sanitize_text_field( $_GET['top_rated_filter'] ) : 'all';
		$valid_filters  = array( 'all', '7days', '30days', 'month', 'year' );
		if ( ! in_array( $current_filter, $valid_filters, true ) ) {
			$current_filter = 'all';
		}

		// Get the current limit from URL parameter (default 10)
		$current_limit = isset( $_GET['top_rated_limit'] ) ? absint( $_GET['top_rated_limit'] ) : 10;
		$valid_limits  = array( 10, 20, 30, 40 );
		if ( ! in_array( $current_limit, $valid_limits, true ) ) {
			$current_limit = 10;
		}

		$top_rated = $this->get_top_rated_recipes( $current_limit, $current_filter );

		// Build base URL for filter links (preserve limit)
		$base_url = admin_url( 'admin.php?page=wpzoom-manage-ratings' );
		if ( $current_limit !== 10 ) {
			$base_url = add_query_arg( 'top_rated_limit', $current_limit, $base_url );
		}

		// Filter labels
		$filter_labels = array(
			'all'    => __( 'All Time', 'recipe-card-blocks-by-wpzoom' ),
			'7days'  => __( 'Last 7 Days', 'recipe-card-blocks-by-wpzoom' ),
			'30days' => __( 'Last 30 Days', 'recipe-card-blocks-by-wpzoom' ),
			'month'  => __( 'This Month', 'recipe-card-blocks-by-wpzoom' ),
			'year'   => __( 'This Year', 'recipe-card-blocks-by-wpzoom' ),
		);
		?>
		<div class="wpzoom-top-rated-section">
			<div class="section-header">
				<div class="section-header-left">
					<h3><?php _e( 'Top Rated Recipes', 'recipe-card-blocks-by-wpzoom' ); ?></h3>
					<span class="section-subtitle"><?php _e( 'Based on user ratings', 'recipe-card-blocks-by-wpzoom' ); ?></span>
				</div>
				<div class="section-header-right">
					<div class="wpzoom-top-rated-filters">
						<?php foreach ( $filter_labels as $filter_key => $filter_label ) :
							$filter_url   = add_query_arg( 'top_rated_filter', $filter_key, $base_url );
							$active_class = ( $current_filter === $filter_key ) ? ' active' : '';
							?>
							<a href="<?php echo esc_url( $filter_url ); ?>" class="filter-btn<?php echo esc_attr( $active_class ); ?>">
								<?php echo esc_html( $filter_label ); ?>
							</a>
						<?php endforeach; ?>
					</div>
					<select class="wpzoom-top-rated-limit" onchange="window.location.href=this.value;">
						<?php foreach ( $valid_limits as $limit ) :
							$limit_url = add_query_arg( array(
								'top_rated_filter' => $current_filter,
								'top_rated_limit'  => $limit,
							), admin_url( 'admin.php?page=wpzoom-manage-ratings' ) );
							?>
							<option value="<?php echo esc_url( $limit_url ); ?>" <?php selected( $current_limit, $limit ); ?>>
								<?php printf( __( 'Show %d', 'recipe-card-blocks-by-wpzoom' ), $limit ); ?>
							</option>
						<?php endforeach; ?>
					</select>
				</div>
			</div>
			<div class="wpzoom-top-rated-columns">
				<div class="wpzoom-top-rated-column">
					<h4><span class="dashicons dashicons-star-filled"></span> <?php _e( 'Highest Rated', 'recipe-card-blocks-by-wpzoom' ); ?></h4>
					<?php if ( ! empty( $top_rated['by_rating'] ) ) : ?>
						<ul class="wpzoom-top-rated-list">
							<?php foreach ( $top_rated['by_rating'] as $index => $recipe ) :
								$post = $recipe['post'];
								$parent_id = $recipe['parent_id'];
								$view_link = ( $parent_id && $parent_id != $post->ID ) ? get_permalink( $parent_id ) : get_permalink( $post->ID );
								?>
								<li>
									<span class="rank"><?php echo esc_html( $index + 1 ); ?></span>
									<div class="recipe-info">
										<a href="<?php echo esc_url( $view_link ); ?>" class="recipe-title" title="<?php echo esc_attr( $post->post_title ); ?>" target="_blank">
											<?php echo esc_html( $post->post_title ); ?>
										</a>
										<div class="recipe-meta">
											<span class="stars"><?php echo esc_html( number_format( $recipe['average_rating'], 1 ) ); ?>★</span>
											<span class="votes">(<?php echo esc_html( $recipe['total_votes'] ); ?> <?php echo _n( 'vote', 'votes', $recipe['total_votes'], 'recipe-card-blocks-by-wpzoom' ); ?>)</span>
										</div>
									</div>
								</li>
							<?php endforeach; ?>
						</ul>
					<?php else : ?>
						<p class="no-recipes">
							<?php
							if ( 'all' === $current_filter ) {
								_e( 'No rated recipes yet.', 'recipe-card-blocks-by-wpzoom' );
							} else {
								_e( 'No ratings in this period.', 'recipe-card-blocks-by-wpzoom' );
							}
							?>
						</p>
					<?php endif; ?>
				</div>
				<div class="wpzoom-top-rated-column">
					<h4><span class="dashicons dashicons-chart-bar"></span> <?php _e( 'Most Votes', 'recipe-card-blocks-by-wpzoom' ); ?></h4>
					<?php if ( ! empty( $top_rated['by_votes'] ) ) : ?>
						<ul class="wpzoom-top-rated-list">
							<?php foreach ( $top_rated['by_votes'] as $index => $recipe ) :
								$post = $recipe['post'];
								$parent_id = $recipe['parent_id'];
								$view_link = ( $parent_id && $parent_id != $post->ID ) ? get_permalink( $parent_id ) : get_permalink( $post->ID );
								?>
								<li>
									<span class="rank"><?php echo esc_html( $index + 1 ); ?></span>
									<div class="recipe-info">
										<a href="<?php echo esc_url( $view_link ); ?>" class="recipe-title" title="<?php echo esc_attr( $post->post_title ); ?>" target="_blank">
											<?php echo esc_html( $post->post_title ); ?>
										</a>
										<div class="recipe-meta">
											<span class="votes"><?php echo esc_html( $recipe['total_votes'] ); ?> <?php echo _n( 'vote', 'votes', $recipe['total_votes'], 'recipe-card-blocks-by-wpzoom' ); ?></span>
											<span class="stars">(<?php echo esc_html( number_format( $recipe['average_rating'], 1 ) ); ?>★)</span>
										</div>
									</div>
								</li>
							<?php endforeach; ?>
						</ul>
					<?php else : ?>
						<p class="no-recipes">
							<?php
							if ( 'all' === $current_filter ) {
								_e( 'No rated recipes yet.', 'recipe-card-blocks-by-wpzoom' );
							} else {
								_e( 'No ratings in this period.', 'recipe-card-blocks-by-wpzoom' );
							}
							?>
						</p>
					<?php endif; ?>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Display filter dropdowns.
	 */
	public function display_filters() {
		$current_type  = isset( $_GET['rating_type'] ) ? sanitize_text_field( $_GET['rating_type'] ) : '';
		$current_value = isset( $_GET['rating_value'] ) ? intval( $_GET['rating_value'] ) : 0;
		?>
		<div class="wpzoom-ratings-filters alignleft actions">
			<select name="rating_type" id="filter-by-rating-type">
				<option value=""><?php _e( 'All Types', 'recipe-card-blocks-by-wpzoom' ); ?></option>
				<option value="user" <?php selected( $current_type, 'user' ); ?>><?php _e( 'User Ratings', 'recipe-card-blocks-by-wpzoom' ); ?></option>
				<option value="comment" <?php selected( $current_type, 'comment' ); ?>><?php _e( 'Comment Ratings', 'recipe-card-blocks-by-wpzoom' ); ?></option>
			</select>
			<select name="rating_value" id="filter-by-rating-value">
				<option value=""><?php _e( 'All Ratings', 'recipe-card-blocks-by-wpzoom' ); ?></option>
				<option value="5" <?php selected( $current_value, 5 ); ?>>★★★★★ (5)</option>
				<option value="4" <?php selected( $current_value, 4 ); ?>>★★★★☆ (4)</option>
				<option value="3" <?php selected( $current_value, 3 ); ?>>★★★☆☆ (3)</option>
				<option value="2" <?php selected( $current_value, 2 ); ?>>★★☆☆☆ (2)</option>
				<option value="1" <?php selected( $current_value, 1 ); ?>>★☆☆☆☆ (1)</option>
			</select>
			<?php submit_button( __( 'Filter', 'recipe-card-blocks-by-wpzoom' ), '', 'filter_action', false ); ?>
		</div>
		<?php
	}

	/**
	 * Display bulk actions dropdown.
	 */
	public function display_bulk_actions() {
		?>
		<div class="wpzoom-bulk-actions alignleft actions bulkactions">
			<select name="bulk_action_select" id="bulk-action-selector">
				<option value=""><?php _e( 'Bulk Actions', 'recipe-card-blocks-by-wpzoom' ); ?></option>
				<option value="approve"><?php _e( 'Approve', 'recipe-card-blocks-by-wpzoom' ); ?></option>
				<option value="unapprove"><?php _e( 'Unapprove', 'recipe-card-blocks-by-wpzoom' ); ?></option>
				<option value="delete"><?php _e( 'Delete Permanently', 'recipe-card-blocks-by-wpzoom' ); ?></option>
			</select>
			<button type="button" id="doaction" class="button action"><?php _e( 'Apply', 'recipe-card-blocks-by-wpzoom' ); ?></button>
		</div>
		<?php
	}

	/**
	 * Displays the table with all ratings.
	 *
	 * @return string Ratings table
	 */
	public function display() {
		// check user capabilities
		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_die(
				'<h1>' . __( 'You need a higher level of permission.' ) . '</h1>' .
				'<p>' . __( 'Sorry, you are not allowed to manage ratings.' ) . '</p>',
				403
			);
		}

		// Check if ratings are enabled
		if ( ! WPZOOM_Settings::get_rating_star_acces() && ! WPZOOM_Settings::get( 'wpzoom_rcb_settings_comment_ratings' ) ) {
			?>
			<div class="wrap">
				<h1 class="wp-heading-inline"><?php _e( 'Ratings', 'recipe-card-blocks-by-wpzoom' ); ?></h1>
				<hr class="wp-header-end">
				<p><?php _e( 'User ratings and comment ratings are currently disabled. Please enable them in the Ratings settings to manage ratings.', 'recipe-card-blocks-by-wpzoom' ); ?></p>
			</div>
			<?php
			return;
		}

		$total_ratings = $this->ratings['total'];
		?>
		<div class="wrap wpzoom-manage-ratings-wrap">
			<h1 class="wp-heading-inline"><?php _e( 'Ratings & Analytics', 'recipe-card-blocks-by-wpzoom' ); ?></h1>
			<?php $this->subtitle_search_results(); ?>
			<hr class="wp-header-end">

			<?php $this->display_statistics_box(); ?>
			<?php $this->display_status_tabs(); ?>

			<form id="wpzoom-ratings-form" method="get">
				<input type="hidden" name="page" value="<?php echo esc_attr( $this->_page_slug ); ?>" />
				<?php
				// Preserve status filter
				if ( ! empty( $_GET['rating_status'] ) ) {
					echo '<input type="hidden" name="rating_status" value="' . esc_attr( sanitize_text_field( $_GET['rating_status'] ) ) . '" />';
				}
				?>

				<div class="tablenav top">
					<?php $this->display_bulk_actions(); ?>
					<?php $this->display_filters(); ?>
					<?php $this->pagination( 'top' ); ?>
					<br class="clear" />
				</div>

				<?php if ( ! $this->ratings['ratings'] ) : ?>
					<p><?php _e( 'No ratings found.', 'recipe-card-blocks-by-wpzoom' ); ?></p>
				<?php else : ?>

				<table class="wp-list-table widefat striped table-view-list wpzoom-ratings">
					<thead>
						<tr>
							<td id="cb" class="manage-column column-cb check-column">
								<label class="screen-reader-text" for="cb-select-all-1"><?php _e( 'Select All', 'recipe-card-blocks-by-wpzoom' ); ?></label>
								<input id="cb-select-all-1" type="checkbox" />
							</td>
							<?php $this->print_column_headers(); ?>
						</tr>
					</thead>

					<tbody id="the-comment-list" data-wp-lists="list:comment">
						<?php foreach ( $this->ratings['ratings'] as $rating ) : ?>
							<?php
								$comment     = $post = $user = false;
								$row_classes = array();

							if ( $rating->user_id ) {
								$user = get_userdata( $rating->user_id );
							}
							if ( $rating->comment_id ) {
								$comment        = get_comment( $rating->comment_id );
								$comment_status = wp_get_comment_status( $comment );
							}
							if ( $rating->post_id ) {
								$post = get_post( $rating->post_id );
							}
							if ( $rating->recipe_id ) {
								$post = get_post( $rating->recipe_id );
							}

							if ( $comment ) {
								$row_classes['rating-type'] = 'comment-rating';
							} else {
								$row_classes['rating-type'] = 'user-rating';
							}
							if ( wp_validate_boolean( $rating->approved ) ) {
								$row_classes['status'] = 'approved';
							} else {
								$row_classes['status'] = 'unapproved';
							}
							if ( $rating->comment_id && ( isset( $comment_status ) && false !== $comment_status ) ) {
								$row_classes['status'] = $comment_status;
							}
							if ( $total_ratings % 2 === 0 ) {
								$row_classes[] = 'even';
							} else {
								$row_classes[] = 'odd';
							}
							?>
							<tr id="<?php echo ( $comment ? 'comment-' . $comment->comment_ID : 'rating-' . $rating->id ); ?>" class="wpzoom-recipe-rating <?php echo implode( ' ', $row_classes ); ?>" data-rating-id="<?php echo esc_attr( $rating->id ); ?>">
								<th scope="row" class="check-column">
									<label class="screen-reader-text" for="cb-select-<?php echo esc_attr( $rating->id ); ?>">
										<?php _e( 'Select rating', 'recipe-card-blocks-by-wpzoom' ); ?>
									</label>
									<input id="cb-select-<?php echo esc_attr( $rating->id ); ?>" type="checkbox" name="rating_ids[]" value="<?php echo esc_attr( $rating->id ); ?>" />
								</th>
								<td class="author column-author" data-colname="Author">
									<?php
									if ( $user ) {
										$this->column_author_user( $user );
									} elseif ( $comment ) {
										$this->column_author_comment( $comment );
									} else {
										$this->column_author_guest( $rating );
									}
									?>
								</td>
								<td data-colname="Type">
									<?php $this->column_type( $comment ); ?>
								</td>
								<td data-colname="Visibility">
									<?php $this->column_visibility( $comment, $rating ); ?>
								</td>
								<td data-colname="IP">
									<?php $this->column_ip( $comment, $rating ); ?>
								</td>
								<td class="comment column-comment has-row-actions column-primary" data-colname="Rating or Comment">
									<?php
									if ( $comment ) {
										$this->column_comment( $comment );
									} else {
										$this->column_rating_stars( $rating, $post );
									}
									?>
								</td>
								<td class="response column-response" data-colname="Post">
									<?php $this->column_response( $post ); ?>
								</td>
								<td class="date column-date" data-colname="Submitted on">
									<?php $this->column_date( $comment, $rating ); ?>
								</td>
							</tr>
							<?php --$total_ratings; ?>
						<?php endforeach ?>
					</tbody>

					<tfoot>
						<tr>
							<td class="manage-column column-cb check-column">
								<label class="screen-reader-text" for="cb-select-all-2"><?php _e( 'Select All', 'recipe-card-blocks-by-wpzoom' ); ?></label>
								<input id="cb-select-all-2" type="checkbox" />
							</td>
							<?php $this->print_column_headers( false ); ?>
						</tr>
					</tfoot>
				</table>

				<?php endif; ?>

				<?php $this->display_tablenav( 'bottom' ); ?>

			</form>
		</div>
		<div id="ajax-response"></div>
		<?php
		wp_comment_trashnotice();
	}

	/**
	 * Generates the table navigation above or below the table
	 *
	 * @see https://github.com/WordPress/WordPress/blob/727922c8eb60c6011888d6700052090a9de43286/wp-admin/includes/class-wp-list-table.php#L1313
	 * @param string $which
	 */
	protected function display_tablenav( $which ) {
		?>
		<div class="tablenav <?php echo esc_attr( $which ); ?>">
			<?php $this->pagination( $which ); ?>
			<br class="clear" />
		</div>
		<?php
	}

}

new WPZOOM_Manage_Ratings();
