<?php
/**
 * Recipe Card PRO Version
 *
 * We are working to PRO version.
 *
 * @since   1.0.1
 * @package WPZOOM Recipe Card Block
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main WPZOOM_Recipe_Card_Block_PRO Class.
 *
 * @since 1.0.1
 */
class WPZOOM_Recipe_Card_Block_PRO {
	/**
	 * This plugin's instance.
	 *
	 * @var WPZOOM_Recipe_Card_Block_PRO
	 * @since 1.0.1
	 */
	private static $instance;

	/**
	 * Main WPZOOM_Recipe_Card_Block_PRO Instance.
	 *
	 * @since 1.0.1
	 * @static
	 * @uses WPZOOM_Recipe_Card_Block_PRO::load_dependencies() Include the required files.
	 * @return object|WPZOOM_Recipe_Card_Block_PRO The one true WPZOOM_Recipe_Card_Block_PRO
	 */
	public static function instance() {
		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof WPZOOM_Recipe_Card_Block_PRO ) ) {
			self::$instance = new WPZOOM_Recipe_Card_Block_PRO();
			self::$instance->init();
			self::$instance->load_dependencies();

		}
		return self::$instance;
	}

	/**
	 * Load actions
	 *
	 * @return void
	 */
	private function init() {
		
	}

	/**
	 * Load all plugin dependecies.
	 *
	 * @access private
	 * @since 1.0.1
	 * @return void
	 */
	private function load_dependencies() {
		return null;
	}
}

WPZOOM_Recipe_Card_Block_PRO::instance();