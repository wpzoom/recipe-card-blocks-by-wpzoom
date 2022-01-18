<?php
/**
 * Structured Data Render
 *
 * @since   1.1.0
 * @package WPZOOM_Recipe_Card_Blocks
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
	 * Class instance Structured Data Helpers.
	 *
	 * @var WPZOOM_Structured_Data_Helpers
	 * @since 1.2.0
	 */
	private static $structured_data_helpers;

	/**
	 * The Constructor.
	 */
	public function __construct() {
		$this->load_dependencies();
		self::$structured_data_helpers = new WPZOOM_Structured_Data_Helpers();
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
		require_once WPZOOM_RCB_SD_BLOCKS_DIR . 'class-wpzoom-details.php';
		require_once WPZOOM_RCB_SD_BLOCKS_DIR . 'class-wpzoom-ingredients.php';
		require_once WPZOOM_RCB_SD_BLOCKS_DIR . 'class-wpzoom-steps.php';
		require_once WPZOOM_RCB_SD_BLOCKS_DIR . 'class-wpzoom-jump-to-recipe.php';
		require_once WPZOOM_RCB_SD_BLOCKS_DIR . 'class-wpzoom-print-recipe.php';
		require_once WPZOOM_RCB_SD_BLOCKS_DIR . 'class-wpzoom-recipe-card-block.php';
		require_once WPZOOM_RCB_SD_BLOCKS_DIR . 'class-wpzoom-nutrition.php';
		require_once WPZOOM_RCB_SD_BLOCKS_DIR . 'class-wpzoom-recipe-block-from-posts.php';

	}

	/**
	 * Registers hooks for Structured Data Blocks with WordPress.
	 */
	public function register_hooks() {
		$block_integrations = array(
			new WPZOOM_Details_Block(),
			new WPZOOM_Ingredients_Block(),
			new WPZOOM_Steps_Block(),
			new WPZOOM_Jump_To_Recipe_Block(),
			new WPZOOM_Print_Recipe_Block(),
			new WPZOOM_Recipe_Card_Block(),
			new WPZOOM_Nutrition_Block(),
			new WPZOOM_Recipe_Block_From_Posts(),
		);

		if ( WPZOOM_RCB_HAS_PRO ) {
			$block_integrations[] = new WPZOOM_Premium_Recipe_Card_Block();
		}

		foreach ( $block_integrations as $block_integration ) {
			$block_integration->register_hooks();
		}
	}
}
