<?php
/**
 * Recipes Import Manager
 *
 * @since   5.0.2
 * @package WPZOOM_Recipe_Card_Blocks
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WPZOOM_Import_Manager' ) ) {

	/**
	 * Main WPZOOM_Import_Manager Class.
	 *
	 * @since   5.0.2
	 */
	class WPZOOM_Import_Manager {

		/**
		 * This plugin's instance.
		 *
		 * @var WPZOOM_Import_Manager
		 * @since   5.0.2
		 */
		private static $instance;

		/**
		 * Provides singleton instance.
		 *
		 * @since   5.0.2
		 * @return self instance
		 */
		public static function instance() {			

			if ( null === self::$instance ) {
				self::$instance = new WPZOOM_Import_Manager();
			}

			return self::$instance;
		}

		/**
		 * The Constructor.
		 */
		public function __construct() {
		
			add_action( 'admin_menu', array( __CLASS__, 'add_admin_menu_submenu' ) );
			add_action( 'admin_notices', array( $this, 'wprm_plugin_notice' ) );
			
		}

		/**
		 * Add submenu for Import Panel.
		 *
		 * @since   5.0.2
		 */
		public static function add_admin_menu_submenu() {

			global $submenu;

			add_submenu_page( 
				WPZOOM_RCB_SETTINGS_PAGE, 
				esc_html__( 'Import Recipes', 'recipe-card-blocks-by-wpzoom' ),
				esc_html__( 'Import Recipes', 'recipe-card-blocks-by-wpzoom' ),
				'edit_others_posts',
				'wpzoom_import_panel',
				array( __CLASS__, 'add_import_panel' ),
				5
			);

			if( isset( $submenu['wpzoom-recipe-card-settings'][2] ) ) {
				
				$pro_menu = $submenu['wpzoom-recipe-card-settings'][2];
				unset( $submenu['wpzoom-recipe-card-settings'][2] );
				
				$submenu['wpzoom-recipe-card-settings'][] = $pro_menu;
			}

			
		
		}

		/**
		 * Add Import Panel template.
		 *
		 * @since   5.0.2
		 */
		public static function add_import_panel() {
			require_once( WPZOOM_RCB_PLUGIN_DIR . 'templates/admin/import/import-panel.php' );
		}

		/**
		 * Add notice to activate or install the WP Recipe Maker Plugin.
		 *
		 * @since   5.2.2
		 */
		public function wprm_plugin_notice() {

			$current_screen = get_current_screen();

			if( 'recipe-cards_page_wpzoom_import_panel' !== $current_screen->id ) {
				return;
			}

			if( class_exists( 'WP_Recipe_Maker' ) ) {
				return;
			}

			if ( isset( $_GET['activate'] ) ) unset( $_GET['activate'] );

			$plugin = 'wp-recipe-maker/wp-recipe-maker.php';
			
			$installed_plugins = get_plugins();

			$is_wprm_installed = isset( $installed_plugins[ $plugin ] );

			if ( $is_wprm_installed ) {
			
				$message = sprintf(
					/* translators: 1: Plugin name 2: WP Recipe Maker */
					esc_html__( '"%1$s" requires "%2$s" to be activated.', 'recipe-card-blocks-by-wpzoom' ),
					'<strong>' . esc_html__( 'The WPZOOM Recipe Card Block Importer', 'recipe-card-blocks-by-wpzoom' ) . '</strong>',
					'<strong>' . esc_html__( 'WP Recipe Maker', 'recipe-card-blocks-by-wpzoom' ) . '</strong>'
				);

				$button_text = esc_html__( 'Activate WP Recipe Maker', 'recipe-card-blocks-by-wpzoom' );
				$button_link = wp_nonce_url( 'plugins.php?action=activate&amp;plugin=' . $plugin . '&amp;plugin_status=all&amp;paged=1&amp;s', 'activate-plugin_' . $plugin );

				
			
			} else {

				$message = sprintf(
					/* translators: 1: Plugin name 2: WP Recipe Maker */
					esc_html__( '"%1$s" requires "%2$s" to be installed.', 'recipe-card-blocks-by-wpzoom' ),
					'<strong>' . esc_html__( 'The WPZOOM Recipe Card Block Importer', 'recipe-card-blocks-by-wpzoom' ) . '</strong>',
					'<strong>' . esc_html__( 'WP Recipe Maker', 'recipe-card-blocks-by-wpzoom' ) . '</strong>'
				);

				$button_text = esc_html__( 'Install WP Recipe Maker', 'recipe-card-blocks-by-wpzoom' );

				$button_link = wp_nonce_url( self_admin_url( 'update.php?action=install-plugin&plugin=wp-recipe-maker' ), 'install-plugin_wp-recipe-maker' );

			}

			$button = sprintf(
				/* translators: 1: Button URL 2: Button text */
				'<a class="button button-primary" href="%1$s">%2$s</a>',
				esc_url( $button_link ),
				esc_html( $button_text )
			);

			printf( '<div class="notice notice-warning is-dismissible"><p>%1$s</p> <p>%2$s</p></div>', $message, $button );

		}

	}

}

WPZOOM_Import_Manager::instance();