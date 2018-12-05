<?php
/**
 * Recipe Card PRO Version
 *
 * We are working to PRO version.
 *
 * @since   1.1.0
 * @package WPZOOM Recipe Card Block
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main WPZOOM_Recipe_Card_Block_PRO Class.
 *
 * @since 1.1.0
 */
class WPZOOM_Recipe_Card_Block_PRO {
	/**
	 * Rating stars Class.
	 *
	 * @var WPZOOM_Rating_Stars
	 * @since 1.1.0
	 */
	private $rating_stars;

	/**
	 * The Constructor.
	 */
	public function __construct() {
		$this->load_dependencies();
		
		$this->rating_stars = new WPZOOM_Rating_Stars();
	}

	/**
	 * Registers hooks for Recipe Card PRO.
	 *
	 * @return void
	 */
	public function register_hooks() {
		// create table
		$this->rating_stars->create_table();
	}

	/**
	 * Load all plugin dependecies.
	 *
	 * @access private
	 * @since 1.1.0
	 * @return void
	 */
	private function load_dependencies() {
		require_once WPZOOM_RCB_PLUGIN_DIR . 'src/classes/class-wpzoom-rating-stars.php';
	}
}
