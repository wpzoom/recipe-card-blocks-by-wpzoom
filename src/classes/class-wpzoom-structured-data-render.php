<?php
/**
 * Structured Data Render
 *
 * @since   1.1.0
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
	 * The Constructor.
	 */
	public function __construct() {
		$this->load_dependencies();
	}

	/**
	 * Load all plugin dependecies.
	 *
	 * @access private
	 * @since 1.1.0
	 * @return void
	 */
	private function load_dependencies() {
		require_once WPZOOM_RCB_PLUGIN_DIR . 'src/classes/class-wpzoom-structured-data-helpers.php';
		require_once WPZOOM_RCB_SD_BLOCKS_DIR . 'class-wpzoom-blocks.php';
		require_once WPZOOM_RCB_SD_BLOCKS_DIR . 'class-wpzoom-jump-to-recipe.php';
		require_once WPZOOM_RCB_SD_BLOCKS_DIR . 'class-wpzoom-print-recipe.php';
		require_once WPZOOM_RCB_SD_BLOCKS_DIR . 'class-wpzoom-recipe-card-block.php';

		if ( WPZOOM_RCB_HAS_PRO ) {
			require_once WPZOOM_RCB_SD_BLOCKS_DIR . 'class-wpzoom-premium-recipe-card-block.php';
		}
	}

	/**
	 * Registers hooks for Structured Data Blocks with WordPress.
	 */
	public function register_hooks() {
		$block_integrations = array(
			new WPZOOM_Blocks(),
			new WPZOOM_Jump_To_Recipe_Block(),
			new WPZOOM_Print_Recipe_Block(),
			new WPZOOM_Recipe_Card_Block()
		);

		if ( WPZOOM_RCB_HAS_PRO ) {
			$block_integrations[] = new WPZOOM_Premium_Recipe_Card_Block();
		}

		foreach ( $block_integrations as $block_integration ) {
			$block_integration->register_hooks();
		}
	}
}
