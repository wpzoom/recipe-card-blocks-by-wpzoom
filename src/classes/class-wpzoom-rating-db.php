<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Rating DB class
 *
 * @since 3.0.0
 * @package WPZOOM_Recipe_Card_Blocks
 */

// Wrap the definition so the class is only declared when the PRO version has not declared it already.
// NOTE: the conditional prevents PHP compile-time early binding, so the init() call below is skipped too.
if ( ! class_exists( 'WPZOOM_Rating_DB' ) ) {

class WPZOOM_Rating_DB {
	/**
	 * The version of the rating database table.
	 *
	 * @access private
	 * @var string $version
	 */
	private static $version = '1.2';

	/**
	 * The fields in the rating database.
	 *
	 * @access private
	 * @var array $fields
	 */
	private static $fields = array( 'id', 'recipe_id', 'user_id', 'comment_id', 'rating', 'rate_date', 'update_date', 'ip', 'approved', 'author_name', 'author_email', 'review_text' );

	/**
	 * Register actions and filters.
	 */
	public static function init() {
		add_action( 'plugins_loaded', array( __CLASS__, 'compare_database_version' ), 1 );
	}

	/**
	 * Compare the database version.
	 * If the $current_version is lower than $version then update database version
	 */
	public static function compare_database_version() {
		$current_version = get_option( 'wpzoom_rcb_rating_db_version', '0.0' );

		if ( version_compare( $current_version, self::$version ) < 0 ) {
			self::create_or_update_database( $current_version );
		}

		// Always ensure required columns exist (handles failed migrations)
		self::ensure_columns_exist();
	}

	/**
	 * Ensure all required columns exist in the database table.
	 * This catches cases where dbDelta or migrations failed.
	 */
	private static function ensure_columns_exist() {
		global $wpdb;
		$table_name = self::get_table_name();

		// Check if table exists
		$table_exists = $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" );
		if ( ! $table_exists ) {
			return;
		}

		// Required columns added in version 1.1
		$required_columns = array(
			'author_name'  => "varchar(100) DEFAULT '' NOT NULL",
			'author_email' => "varchar(100) DEFAULT '' NOT NULL",
			'review_text'  => 'longtext',
		);

		foreach ( $required_columns as $column_name => $column_def ) {
			$column_exists = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
					WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s AND COLUMN_NAME = %s",
					DB_NAME,
					$table_name,
					$column_name
				)
			);

			if ( ! $column_exists ) {
				$wpdb->query( "ALTER TABLE `$table_name` ADD COLUMN `$column_name` $column_def" );
			}
		}
	}

	/**
	 * Get the prefixed name of rating database table.
	 */
	public static function get_table_name() {
		global $wpdb;
		return $wpdb->prefix . 'wpzoom_rating_stars';
	}

	/**
	 * Create or update the rating database.
	 *
	 * @param mixed $from Database version to update from.
	 */
	public static function create_or_update_database( $from ) {
		global $wpdb;

		$table_name              = self::get_table_name();
		$charset_collate         = $wpdb->get_charset_collate();
		$drop_deprecated_indexes = get_option( 'wpzoom_rcb_rating_db_drop_deprecated_indexes', false );

		if ( ! $drop_deprecated_indexes ) {
			self::drop_deprecated_indexes();
		}

		$sql = "CREATE TABLE `$table_name` (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            recipe_id bigint(20) unsigned NOT NULL,
            user_id bigint(20) unsigned NOT NULL DEFAULT '0',
            comment_id bigint(20) unsigned NOT NULL,
            post_id bigint(20) unsigned NOT NULL,
            rating tinyint(1) DEFAULT '0' NOT NULL,
            rate_date datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            update_date datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            ip varchar(39) DEFAULT '' NOT NULL,
            approved tinyint(1) DEFAULT '1' NOT NULL,
            author_name varchar(100) DEFAULT '' NOT NULL,
            author_email varchar(100) DEFAULT '' NOT NULL,
            review_text longtext,
            PRIMARY KEY (id),
            KEY recipe_id (recipe_id),
            KEY post_id (post_id),
            KEY comment_id (comment_id),
            KEY rate_date (rate_date)
        ) $charset_collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );

		// Migration from version 1.0 to 1.1: Add author_name and author_email columns
		// dbDelta doesn't reliably add columns to existing tables, so we do it manually
		if ( version_compare( $from, '1.1', '<' ) ) {
			self::migrate_to_1_1();
		}

		if ( version_compare( $from, '1.2', '<' ) ) {
			self::migrate_to_1_2();
		}

		update_option( 'wpzoom_rcb_rating_db_version', self::$version );
	}

	/**
	 * Migrate database to version 1.1
	 * Adds author_name and author_email columns for guest ratings from modal
	 */
	private static function migrate_to_1_1() {
		global $wpdb;
		$table_name = self::get_table_name();

		// Check if table exists
		$table_exists = $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" );
		if ( ! $table_exists ) {
			return;
		}

		// Check if author_name column exists
		$column_exists = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
				WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s AND COLUMN_NAME = %s",
				DB_NAME,
				$table_name,
				'author_name'
			)
		);

		if ( ! $column_exists ) {
			$wpdb->query( "ALTER TABLE `$table_name` ADD COLUMN `author_name` varchar(100) DEFAULT '' NOT NULL" );
		}

		// Check if author_email column exists
		$column_exists = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
				WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s AND COLUMN_NAME = %s",
				DB_NAME,
				$table_name,
				'author_email'
			)
		);

		if ( ! $column_exists ) {
			$wpdb->query( "ALTER TABLE `$table_name` ADD COLUMN `author_email` varchar(100) DEFAULT '' NOT NULL" );
		}
	}

	/**
	 * Migrate database to version 1.2
	 * Adds review_text column for storing private modal reviews
	 */
	private static function migrate_to_1_2() {
		global $wpdb;
		$table_name = self::get_table_name();

		$table_exists = $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" );
		if ( ! $table_exists ) {
			return;
		}

		$column_exists = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
				WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s AND COLUMN_NAME = %s",
				DB_NAME,
				$table_name,
				'review_text'
			)
		);

		if ( ! $column_exists ) {
			$wpdb->query( "ALTER TABLE `$table_name` ADD COLUMN `review_text` longtext" );
		}
	}

	public static function drop_deprecated_indexes() {
		global $wpdb;

		$table_name = self::get_table_name();

		// Check if table exists before attempting to alter it
		$table_exists = $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" );
		
		if ( ! $table_exists ) {
			// Table doesn't exist, mark as completed and return
			update_option( 'wpzoom_rcb_rating_db_drop_deprecated_indexes', true );
			return;
		}

		// Check if the deprecated index exists before trying to drop it
		$index_exists = $wpdb->get_var( 
			$wpdb->prepare( 
				"SELECT COUNT(1) as index_exists FROM INFORMATION_SCHEMA.STATISTICS 
				WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s AND INDEX_NAME = %s", 
				DB_NAME,
				$table_name,
				'post_user'
			)
		);

		if ( $index_exists ) {
			// Only drop index and modify columns if the deprecated index exists
			$sql = $wpdb->prepare(
				"ALTER TABLE `$table_name`
	            DROP INDEX post_user,
	            CHANGE `rate_date` `rate_date` datetime NOT NULL DEFAULT %s,
	            CHANGE `update_date` `update_date` datetime NOT NULL DEFAULT %s;",
				current_time( 'mysql' ),
				current_time( 'mysql' )
			);
		} else {
			// Just modify the columns without dropping the index
			$sql = $wpdb->prepare(
				"ALTER TABLE `$table_name`
	            CHANGE `rate_date` `rate_date` datetime NOT NULL DEFAULT %s,
	            CHANGE `update_date` `update_date` datetime NOT NULL DEFAULT %s;",
				current_time( 'mysql' ),
				current_time( 'mysql' )
			);
		}

		$wpdb->hide_errors();
		$result = $wpdb->query( $sql );
		$wpdb->show_errors();

		update_option( 'wpzoom_rcb_rating_db_drop_deprecated_indexes', $result );
	}

	/**
	 * Add or update the rating in the database.
	 *
	 * @param array $rating_data The unsanitized rating data to add in the database.
	 */
	public static function add_or_update_rating( $rating_data ) {
		$rating = array();

		// Sanitize rating data
		$rating['id']           = isset( $rating_data['id'] ) ? absint( $rating_data['id'] ) : 0;
		$rating['recipe_id']    = isset( $rating_data['recipe_id'] ) ? absint( $rating_data['recipe_id'] ) : 0;
		$rating['user_id']      = isset( $rating_data['user_id'] ) ? absint( $rating_data['user_id'] ) : 0;
		$rating['comment_id']   = isset( $rating_data['comment_id'] ) ? absint( $rating_data['comment_id'] ) : 0;
		$rating['post_id']      = isset( $rating_data['post_id'] ) ? absint( $rating_data['post_id'] ) : 0;
		$rating['rating']       = isset( $rating_data['rating'] ) ? absint( $rating_data['rating'] ) : 0;
		$rating['rate_date']    = isset( $rating_data['rate_date'] ) && $rating_data['rate_date'] ? $rating_data['rate_date'] : current_time( 'mysql' );
		$rating['update_date']  = isset( $rating_data['update_date'] ) && $rating_data['update_date'] ? $rating_data['update_date'] : current_time( 'mysql' );
		$rating['ip']           = isset( $rating_data['ip'] ) && $rating_data['ip'] ? esc_attr( $rating_data['ip'] ) : '';
		$rating['approved']     = isset( $rating_data['approved'] ) ? $rating_data['approved'] : 1;
		$rating['author_name']  = isset( $rating_data['author_name'] ) ? sanitize_text_field( $rating_data['author_name'] ) : '';
		$rating['author_email'] = isset( $rating_data['author_email'] ) ? sanitize_email( $rating_data['author_email'] ) : '';
		$rating['review_text']  = isset( $rating_data['review_text'] ) ? sanitize_textarea_field( $rating_data['review_text'] ) : '';

		// We have comment ID
		if ( $rating['comment_id'] ) {
			$comment = get_comment( $rating['comment_id'] );

			if ( $comment ) {
				$rating['post_id']  = $comment->comment_post_ID;
				$rating['approved'] = '1' === $comment->comment_approved || 'approve' === $comment->comment_approved ? 1 : 0;
			} else {
				$rating['approved'] = 0;
			}
		}

		// Check if rating is between 1 and 5
		if ( 0 < $rating['rating'] && 5 >= $rating['rating'] ) {
			global $wpdb;
			$table_name = self::get_table_name();

			// Check if table exists before trying to insert/update
			$table_exists = $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" );
			if ( ! $table_exists ) {
				// Try to create the table
				self::create_or_update_database( '0.0' );
				// Check again
				$table_exists = $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" );
				if ( ! $table_exists ) {
					return false;
				}
			}

			$where = false;

			// Check for existing ratings from this user/ip for this recipe/comment
			if ( $rating['id'] ) {
				$where = 'id = ' . $rating['id'] . ' AND ip = "' . $rating['ip'] . '"';
			} elseif ( $rating['recipe_id'] ) {
				if ( $rating['user_id'] ) {
					$where = 'recipe_id = ' . $rating['recipe_id'] . ' AND user_id = ' . $rating['user_id'];
				} elseif ( $rating['ip'] ) {
					$where = 'recipe_id = ' . $rating['recipe_id'] . ' AND ip = "' . $rating['ip'] . '"';
				} else {
					$where = 'recipe_id = ' . $rating['recipe_id'] . ' AND rate_date = "' . $rating['rate_date'] . '" AND ip = "' . $rating['ip'] . '"';
				}
			} elseif ( $rating['comment_id'] ) {
				$where = 'comment_id = ' . $rating['comment_id'];
			}

			// Only continue if it was a valid rating
			if ( $where ) {

				// Delete existing ratings
				if ( ! $rating['id'] ) {
					$existing_ratings     = self::get_ratings(
						array(
							'where' => $where,
						)
					);
					$existing_ratings_ids = wp_list_pluck( $existing_ratings['ratings'], 'id' );

					if ( 0 < count( $existing_ratings_ids ) ) {
						self::delete_ratings( $existing_ratings_ids );
					}
				} else {
					self::delete_ratings( $rating['id'] );
				}

				// Insert new rating
				$wpdb->insert( $table_name, $rating );

				if ( ! $rating['recipe_id'] ) {
					WPZOOM_Comment_Rating::update_comment_meta_rating( $rating['comment_id'], $rating['rating'] );
				}

				return true;
			}
		}

		return false;
	}

	/**
	 * Delete ratings for a specific comment
	 *
	 * @param int $comment_id   The comment id for which to delete ratings.
	 */
	public static function delete_ratings_for_comment( $comment_id ) {
		global $wpdb;
		$table_name = self::get_table_name();

		$wpdb->delete( $table_name, array( 'comment_id' => $comment_id ), array( '%d' ) );

		// Update cached rating
		WPZOOM_Comment_Rating::update_comment_meta_rating( $comment_id, 0 );
	}

	/**
	 * Query ratings.
	 *
	 * @param mixed $args Arguments for the query.
	 */
	public static function get_ratings( $args = array() ) {
		global $wpdb;

		$table_name = self::get_table_name();

		// Check if table exists before querying
		$table_exists = $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" );
		if ( ! $table_exists ) {
			return array(
				'total'   => 0,
				'ratings' => array(),
			);
		}

		// Sanitize arguments.
		$order = isset( $args['order'] ) ? strtoupper( $args['order'] ) : '';
		$order = in_array( $order, array( 'ASC', 'DESC' ), true ) ? $order : 'DESC';

		$orderby = isset( $args['orderby'] ) ? strtolower( $args['orderby'] ) : '';
		$orderby = in_array( $orderby, self::$fields, true ) ? $orderby : 'rate_date';

		$offset = isset( $args['offset'] ) ? intval( $args['offset'] ) : 0;
		$limit  = isset( $args['limit'] ) ? intval( $args['limit'] ) : 0;

		$where = isset( $args['where'] ) ? trim( $args['where'] ) : '';

		// Query ratings.
		$query_where = $where ? ' WHERE ' . $where : '';
		$query_order = ' ORDER BY ' . $orderby . ' ' . $order;
		$query_limit = $limit ? ' LIMIT ' . $offset . ',' . $limit : '';

		// Count without limit.
		$query_count = 'SELECT count(*) FROM ' . $table_name . $query_where;
		$count       = $wpdb->get_var( $query_count );

		// Query ratings.
		$query_ratings = 'SELECT * FROM ' . $table_name . $query_where . $query_order . $query_limit;
		$ratings       = $wpdb->get_results( $query_ratings );

		return array(
			'total'   => intval( $count ),
			'ratings' => $ratings,
		);
	}

	/**
	 * Query for 1 specific rating.
	 *
	 * @param mixed $args Arguments for the query.
	 */
	public static function get_rating( $args ) {
		$ratings = self::get_ratings( $args );

		if ( 0 < $ratings['total'] ) {
			return $ratings['ratings'][0];
		} else {
			return false;
		}
	}

	public static function get_rating_average( $args ) {
		$average = 0;
		$ratings = self::get_ratings( $args );

		if ( 0 < $ratings['total'] ) {
			foreach ( $ratings['ratings'] as $key => $rating ) {
				if ( $rating->approved && 0 < $rating->rating ) {
					$average += intval( $rating->rating );
				}
			}

			$average = $average / $ratings['total'];
		}

		return number_format( $average, 1 );
	}

	/**
	 * Delete a set of ratings.
	 *
	 * @param array $ids Rating IDs to delete.
	 */
	public static function delete_ratings( $ids ) {
		global $wpdb;
		$table_name = self::get_table_name();

		if ( is_array( $ids ) ) {
			// Delete all these rating IDs.
			$ids = implode( ',', array_map( 'intval', $ids ) );
			$wpdb->query( 'DELETE FROM ' . $table_name . ' WHERE ID IN (' . $ids . ')' );
		} else {
			// Delete only one rating ID.
			$wpdb->query( 'DELETE FROM ' . $table_name . ' WHERE id = ' . $ids );
		}
	}

	/**
	 * Update a rating by ID.
	 *
	 * @since 3.3.0
	 * @param int   $id   Rating ID.
	 * @param array $data Data to update (key => value pairs).
	 * @return bool True on success, false on failure.
	 */
	public static function update_rating( $id, $data ) {
		global $wpdb;
		$table_name = self::get_table_name();

		$id = absint( $id );
		if ( ! $id ) {
			return false;
		}

		// Sanitize data
		$update_data = array();
		$format      = array();

		if ( isset( $data['approved'] ) ) {
			$update_data['approved'] = absint( $data['approved'] );
			$format[]                = '%d';
		}

		if ( isset( $data['rating'] ) ) {
			$update_data['rating'] = absint( $data['rating'] );
			$format[]              = '%d';
		}

		if ( isset( $data['author_name'] ) ) {
			$update_data['author_name'] = sanitize_text_field( $data['author_name'] );
			$format[]                   = '%s';
		}

		if ( isset( $data['author_email'] ) ) {
			$update_data['author_email'] = sanitize_email( $data['author_email'] );
			$format[]                    = '%s';
		}

		if ( isset( $data['review_text'] ) ) {
			$update_data['review_text'] = sanitize_textarea_field( $data['review_text'] );
			$format[]                   = '%s';
		}

		if ( empty( $update_data ) ) {
			return false;
		}

		// Always update the update_date
		$update_data['update_date'] = current_time( 'mysql' );
		$format[]                   = '%s';

		$result = $wpdb->update(
			$table_name,
			$update_data,
			array( 'id' => $id ),
			$format,
			array( '%d' )
		);

		return false !== $result;
	}
}

WPZOOM_Rating_DB::init();

}

