<?php
/**
 * Recipes Import
 *
 * @since   5.0.2
 * @package WPZOOM_Recipe_Card_Blocks
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WPZOOM_Import' ) ) {

	/**
	 * Main WPZOOM_Import Class.
	 *
	 * @since   5.0.2
	 */
	abstract class WPZOOM_Import {

		/**
		 * Get the number of the recipes to import
		 *
		 * @since   5.0.2
		 */
		abstract public function get_recipes_count();

		/**
		 * Search for recipes and get the list of them all
		 *
		 * @since   5.0.2
		 * @param	 int $page Page of recipes to get.
		 */
		abstract public function search_recipes();

		/**
		 * Replace the original recipe with the newly imported WPRM one.
		 *
		 * @since   5.0.2
		 * @param		 $post_id ID of the post where to replace the recipe.
		 * @param		 $wprm_recipe_id ID of the WPRM recipe to replace.
		 * @param		 $block_index Block index in the post to replace.
		 */
		abstract public function replace_recipe( $post_id, $wprm_recipe_id, $block_index ); 

	}
}