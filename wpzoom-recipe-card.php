<?php
/**
 * Plugin Name: Recipe Card Blocks by WPZOOM
 * Plugin URI: https://www.wpzoom.com/plugins/recipe-card-blocks-pro/
 * Description: Beautiful Recipe Card Blocks for Food Bloggers with Schema Markup for the new WordPress editor (Gutenberg).
 * Author: WPZOOM
 * Author URI: https://www.wpzoom.com/
 * Version: 2.0.0
 * Copyright: (c) 2019 WPZOOM
 * License: GPL2+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: wpzoom-recipe-card
 * Domain Path: /languages
 *
 * @package   WPZOOM Recipe Card Block
 * @author    Vicolas Petru
 * @license   GPL-2+
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WPZOOM_Recipe_Card_Block_Gutenberg' ) ) :
	/**
	 * Main WPZOOM_Recipe_Card_Block_Gutenberg Class.
	 *
	 * @since 1.1.0
	 */
	final class WPZOOM_Recipe_Card_Block_Gutenberg {
		/**
		 * This plugin's instance.
		 *
		 * @var WPZOOM_Recipe_Card_Block_Gutenberg
		 * @since 1.1.0
		 */
		private static $instance;

		/**
		 * Main WPZOOM_Recipe_Card_Block_Gutenberg Instance.
		 *
		 * Insures that only one instance of WPZOOM_Recipe_Card_Block_Gutenberg exists in memory at any one
		 * time. Also prevents needing to define globals all over the place.
		 *
		 * @since 1.1.0
		 * @static
		 * @uses WPZOOM_Recipe_Card_Block_Gutenberg::define_constants() Setup the constants needed.
		 * @uses WPZOOM_Recipe_Card_Block_Gutenberg::load_dependencies() Include the required files.
		 * @see WIDGETOPTS()
		 * @return object|WPZOOM_Recipe_Card_Block_Gutenberg The one true WPZOOM_Recipe_Card_Block_Gutenberg
		 */
		public static function instance() {
			if ( ! isset( self::$instance ) && ! ( self::$instance instanceof WPZOOM_Recipe_Card_Block_Gutenberg ) ) {
				self::$instance = new WPZOOM_Recipe_Card_Block_Gutenberg();
				self::$instance->define_constants();
				self::$instance->load_dependencies();
				self::$instance->init();

			}
			return self::$instance;
		}

		/**
		 * Setup plugin constants.
		 *
		 * @access private
		 * @since 1.1.0
		 * @return void
		 */
		private function define_constants() {
			$this->define( 'WPZOOM_RCB_VERSION', '2.0.0' );
			$this->define( 'WPZOOM_RCB_TEXT_DOMAIN', 'wpzoom-recipe-card' );
			$this->define( 'WPZOOM_RCB_HAS_PRO', false );
			$this->define( 'WPZOOM_RCB_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
			$this->define( 'WPZOOM_RCB_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
			$this->define( 'WPZOOM_RCB_SD_BLOCKS_DIR', WPZOOM_RCB_PLUGIN_DIR . 'src/structured-data-blocks/' );
			$this->define( 'WPZOOM_RCB_PLUGIN_FILE', __FILE__ );
			$this->define( 'WPZOOM_RCB_PLUGIN_BASE', plugin_basename( __FILE__ ) );
			$this->define( 'WPZOOM_RCB_REVIEW_URL', 'https://wordpress.org/support/plugin/recipe-card-blocks-by-wpzoom/reviews/' );

			// settings ?page url attribute
			$this->define( 'WPZOOM_RCB_SETTINGS_PAGE', 'wpzoom-recipe-card-settings' );
			// this is the URL our updater / license checker pings. This should be the URL of the site with EDD installed
			$this->define( 'WPZOOM_RCB_STORE_URL', 'https://www.wpzoom.com/' );
			$this->define( 'WPZOOM_RCB_RENEW_URL', 'https://www.wpzoom.com/account/licenses/' );
			// the download ID. This is the ID of your product in EDD and should match the download ID visible in your Downloads list
			$this->define( 'WPZOOM_RCB_ITEM_ID', 197189 );
			$this->define( 'WPZOOM_RCB_ITEM_NAME', 'Recipe Card Block PRO' );
		}

		/**
		 * Load actions
		 *
		 * @return void
		 */
		private function init() {
			add_filter( 'block_categories', array( $this, 'add_custom_category' ), 10, 2 );

			add_action( 'init', array( $this, 'register_block_types' ) );
			add_action( 'init', array( $this, 'load_textdomain' ) );
		}

		/**
		 * Define constant if not already set.
		 *
		 * @param  string|string $name Name of the definition.
		 * @param  string|bool   $value Default value.
		 */
		private function define( $name, $value ) {
			if ( ! defined( $name ) ) {
				define( $name, $value );
			}
		}

		/**
		 * Load all plugin dependecies.
		 *
		 * @access private
		 * @since 1.1.0
		 * @return void
		 */
		private function load_dependencies() {
			require_once WPZOOM_RCB_PLUGIN_DIR . 'src/classes/class-wpzoom-settings-fields.php';
			require_once WPZOOM_RCB_PLUGIN_DIR . 'src/classes/class-wpzoom-settings.php';

			require_once WPZOOM_RCB_PLUGIN_DIR . 'src/classes/class-wpzoom-assets-manager.php';
			require_once WPZOOM_RCB_PLUGIN_DIR . 'src/classes/class-wpzoom-helpers.php';

			if ( self::has_pro() ) {
				require_once WPZOOM_RCB_PLUGIN_DIR . 'src/classes/class-wpzoom-recipe-card-pro.php';
			}

			require_once WPZOOM_RCB_PLUGIN_DIR . 'src/classes/class-wpzoom-structured-data-render.php';
		}

		public function register_block_types() {
			$integrations   = array();
			$integrations[] = new WPZOOM_Structured_Data_Render();

			if ( self::is_pro() ) {
				$integrations[] = new WPZOOM_Recipe_Card_Block_PRO();
			}

			foreach ( $integrations as $integration ) {
				$integration->register_hooks();
			}
		}

		/**
		 * Add custom block category
		 *
		 * @since 1.1.0
		 */
		public function add_custom_category( $categories, $post ) {
			return array_merge(
				$categories,
				array(
					array(
						'slug' => 'wpzoom-recipe-card',
						'title' => __( 'WPZOOM - Recipe Card', 'wpzoom-recipe-card' ),
					),
				)
			);
		}

		/**
		 * Check if pro exists.
		 *
		 * @since 1.1.0
		 * @access public
		 */
		public static function has_pro() {
			if ( true === WPZOOM_RCB_HAS_PRO ) {
				return true;
			} else {
				return false;
			}
		}

		/**
		 * Check if pro is activated.
		 *
		 * @since 1.1.0
		 * @access public
		 */
		public static function is_pro() {
			if ( class_exists( 'WPZOOM_Recipe_Card_Block_PRO' ) ) {
				return true;
			} else {
				return false;
			}
		}

		/**
		 * Load the plugin textdomain
		 *
		 * @since 1.1.0
		 */
		public function load_textdomain() {
			load_plugin_textdomain(
				'wpzoom-recipe-card',
				false,
				dirname( plugin_basename( WPZOOM_RCB_PLUGIN_DIR ) ) . '/languages/'
			);
		}
	}
endif;

/**
 * The main function for that returns WPZOOM_Recipe_Card_Block_Gutenberg
 *
 * Example: <?php $recipe_card_block = new WPZOOM_Recipe_Card_Block_Gutenberg(); ?>
 *
 * @since 1.1.0
 * @return object|WPZOOM_Recipe_Card_Block_Gutenberg The one true WPZOOM_Recipe_Card_Block_Gutenberg Instance.
 */
if ( ! function_exists('recipe_card_block') ) {
	function recipe_card_block() {
		return WPZOOM_Recipe_Card_Block_Gutenberg::instance();
	}
}

// Get the plugin running. Load on plugins_loaded action to avoid issue on multisite.
if ( function_exists( 'is_multisite' ) && is_multisite() ) {
	add_action( 'plugins_loaded', 'recipe_card_block', 90 );
} else {
	recipe_card_block();
}

register_activation_hook( __FILE__, 'recipe_card_block_plugin_activation' );
add_action( 'admin_init', 'recipe_card_block_plugin_activation_redirect' );

/**
 * Add a redirection check on activation.
 *
 * @since 1.2.0
 */
function recipe_card_block_plugin_activation() {
	add_option( 'wpzoom_rcb_do_activation_redirect', true );
	set_transient( 'wpzoom_rcb_welcome_banner', true, 12 * HOUR_IN_SECONDS );
}

/**
 * Redirect to the WPZOOM Recipe Card Getting Started page on single plugin activation
 *
 * @since 1.2.0
 */
function recipe_card_block_plugin_activation_redirect() {
	if ( get_option( 'wpzoom_rcb_do_activation_redirect', false ) ) {
		delete_option( 'wpzoom_rcb_do_activation_redirect' );
		if ( ! isset( $_GET['activate-multi'] ) ) {
			wp_redirect( 'options-general.php?page=wpzoom-recipe-card-settings' );
		}
	}
}

