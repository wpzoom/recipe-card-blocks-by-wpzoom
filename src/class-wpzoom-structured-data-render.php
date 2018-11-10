<?php
/**
 * Structured Data Render
 *
 * @since   1.0.1
 * @package WPZOOM Recipe Card Block
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class to load assets required for structured data blocks.
 */
class WPZOOM_Structured_Data_Render {
	/**
	 * This plugin's instance.
	 *
	 * @var WPZOOM_Structured_Data_Render
	 * @since 1.0.1
	 */
	private static $instance;

	/**
	 * The WPZOOM_Recipe_Card_Block instance.
	 *
	 * @var WPZOOM_Recipe_Card_Block
	 * @since 1.0.1
	 */
	private $_recipe_card_block;

	/**
	 * Registers the plugin.
	 */
	public static function register() {
		if ( null === self::$instance ) {
			self::$instance = new WPZOOM_Structured_Data_Render();
			self::$instance->load_dependencies();
			self::$instance->register_hooks();
		}
	}

	/**
	 * The Constructor.
	 */
	private function __construct() {
		$this->_recipe_card_block = new WPZOOM_Recipe_Card_Block();
	}

	/**
	 * Load all plugin dependecies.
	 *
	 * @access private
	 * @since 1.0.1
	 * @return void
	 */
	private function load_dependencies() {
		require_once WPZOOM_RCB_SD_BLOCKS_DIR . 'class-wpzoom-blocks.php';

		if ( $this->_recipe_card_block->has_pro() ) {
			require_once WPZOOM_RCB_SD_BLOCKS_DIR . 'class-wpzoom-premium-recipe-card-block.php';
		}
	}

	/**
	 * Registers hooks for Structured Data Blocks with WordPress.
	 */
	public function register_hooks() {
		$block_integrations = array(
			new WPZOOM_Blocks()
		);

		if ( $this->_recipe_card_block->is_pro() ) {
			$block_integrations[] = new WPZOOM_Premium_Recipe_Card_Block();
		}

		foreach ( $block_integrations as $block_integration ) {
			$block_integration->register_hooks();
		}
	}
}

WPZOOM_Structured_Data_Render::register();
