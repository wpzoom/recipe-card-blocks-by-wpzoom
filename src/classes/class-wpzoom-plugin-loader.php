<?php

if ( ! class_exists( 'WPZOOM_Plugin_Loader' ) ) {

	/**
	 * Responsible for setting up plugin constants, classes and includes.
	 *
	 * @since 2.2.0
	 */
	final class WPZOOM_Plugin_Loader {
		/**
		 * Load the plugin if it's not already loaded, otherwise
		 * show an admin notice.
		 *
		 * @since 2.2.0
		 * @return void
		 */
		public static function init() {
			if ( ! function_exists( 'is_plugin_active' ) ) {
				include_once ABSPATH . 'wp-admin/includes/plugin.php';
			}

			$lite_dirname   = 'recipe-card-blocks-by-wpzoom';
			$lite_active    = is_plugin_active( $lite_dirname . '/wpzoom-recipe-card.php' );
			$plugin_dirname = basename( dirname( dirname( dirname( __FILE__ ) ) ) );
			$is_network     = is_network_admin();

			if ( $lite_active && $plugin_dirname != $lite_dirname ) {
				deactivate_plugins( array( $lite_dirname . '/wpzoom-recipe-card.php' ), false, $is_network );
				return;
			} elseif ( class_exists( 'WPZOOM_Recipe_Card_Block_Gutenberg' ) ) {
				add_action( 'admin_notices', __CLASS__ . '::double_install_admin_notice' );
				add_action( 'network_admin_notices', __CLASS__ . '::double_install_admin_notice' );
				return;
			}

			self::define_constants();
			self::load_dependencies();
		}

		/**
		 * Setup plugin constants.
		 *
		 * @access private
		 * @since 2.2.0
		 * @return void
		 */
		private static function define_constants() {
			define( 'WPZOOM_RCB_HAS_PRO', false );
			define( 'WPZOOM_RCB_PLUGIN_FILE', trailingslashit( dirname( dirname( dirname( __FILE__ ) ) ) ) . 'wpzoom-recipe-card.php' );
			define( 'WPZOOM_RCB_PLUGIN_DIR', plugin_dir_path( WPZOOM_RCB_PLUGIN_FILE ) );
			define( 'WPZOOM_RCB_PLUGIN_URL', plugins_url( '/', WPZOOM_RCB_PLUGIN_FILE ) );
			define( 'WPZOOM_RCB_SD_BLOCKS_DIR', WPZOOM_RCB_PLUGIN_DIR . 'src/structured-data-blocks/' );
			define( 'WPZOOM_RCB_REVIEW_URL', 'https://wordpress.org/support/plugin/recipe-card-blocks-by-wpzoom/reviews/' );

			// settings page url attribute
			define( 'WPZOOM_RCB_SETTINGS_PAGE', 'wpzoom-recipe-card-settings' );

			/**
			 * Parses the plugin contents to retrieve plugin’s metadata.
			 *
			 * @since 2.1.1
			 */
			if ( function_exists( 'get_plugin_data' ) ) {
				$plugin_data = get_plugin_data( WPZOOM_RCB_PLUGIN_FILE );
			} else {
				$plugin_data = get_file_data(
					WPZOOM_RCB_PLUGIN_FILE,
					array(
						'Version'    => 'Version',
						'TextDomain' => 'Text Domain',
						'AuthorURI'  => 'Author URI',
					),
					'plugin'
				);
			}

			define( 'WPZOOM_RCB_VERSION', $plugin_data['Version'] );
			define( 'WPZOOM_RCB_TEXT_DOMAIN', $plugin_data['TextDomain'] );

			// this is the URL our updater / license checker pings. This should be the URL of the site with EDD installed
			define( 'WPZOOM_RCB_STORE_URL', $plugin_data['AuthorURI'] );
			define( 'WPZOOM_RCB_RENEW_URL', $plugin_data['AuthorURI'] . '/account/licenses/' );
		}

		/**
		 * Load all plugin dependecies.
		 *
		 * @access private
		 * @since 2.2.0
		 * @return void
		 */
		private static function load_dependencies() {
			require_once WPZOOM_RCB_PLUGIN_DIR . 'src/classes/class-wpzoom-recipe-card-block-gutenberg.php';
			require_once WPZOOM_RCB_PLUGIN_DIR . 'src/classes/class-wpzoom-admin-menu.php';
			require_once WPZOOM_RCB_PLUGIN_DIR . 'src/classes/class-wpzoom-lite-vs-pro.php';
			require_once WPZOOM_RCB_PLUGIN_DIR . 'src/classes/class-wpzoom-settings-fields.php';
			require_once WPZOOM_RCB_PLUGIN_DIR . 'src/classes/class-wpzoom-settings.php';
			require_once WPZOOM_RCB_PLUGIN_DIR . 'src/classes/class-wpzoom-assets-manager.php';
			require_once WPZOOM_RCB_PLUGIN_DIR . 'src/classes/class-wpzoom-helpers.php';
			require_once WPZOOM_RCB_PLUGIN_DIR . 'src/classes/class-wpzoom-structured-data-render.php';
			require_once WPZOOM_RCB_PLUGIN_DIR . 'src/classes/class-wpzoom-plugin-activator.php';
			require_once WPZOOM_RCB_PLUGIN_DIR . 'src/classes/class-wpzoom-print-template-manager.php';
			require_once WPZOOM_RCB_PLUGIN_DIR . 'src/classes/class-wpzoom-print.php';

			//Added January 2022
			require_once WPZOOM_RCB_PLUGIN_DIR . 'src/classes/class-wpzoom-custom-post.php';
			require_once WPZOOM_RCB_PLUGIN_DIR . 'src/classes/class-wpzoom-recipe-scanner.php';
			require_once WPZOOM_RCB_PLUGIN_DIR . 'src/classes/class-wpzoom-recipe-post-saver.php';

			if ( class_exists( '\Elementor\Plugin' ) ) {
				require_once WPZOOM_RCB_PLUGIN_DIR . 'src/classes/class-wpzoom-elementor.php';
			}
			if ( function_exists( 'reblex_admin_init' ) ) {
				require_once WPZOOM_RCB_PLUGIN_DIR . 'src/classes/class-wpzoom-reusable-blocks-extended.php';
			}
		}

		/**
		 * Shows an admin notice if another version of the plugin
		 * has already been loaded before this one.
		 *
		 * @since 2.2.0
		 * @return void
		 */
		public static function double_install_admin_notice() {
			/* translators: %s: plugins page link */
			$message = __( 'You currently have two versions of Recipe Card Blocks active on this site. Please <a href="%s">deactivate one</a> before continuing.', 'recipe-card-blocks-by-wpzoom' );

			self::render_admin_notice( sprintf( $message, admin_url( 'plugins.php' ) ), 'error' );
		}

		/**
		 * Renders an admin notice.
		 *
		 * @since 2.2.0
		 * @access private
		 * @param string $message
		 * @param string $type
		 * @return void
		 */
		private static function render_admin_notice( $message, $type = 'update' ) {
			if ( ! is_admin() ) {
				return;
			} elseif ( ! is_user_logged_in() ) {
				return;
			} elseif ( ! current_user_can( 'update_plugins' ) ) {
				return;
			}

			echo '<div class="' . esc_attr( $type ) . '">';
			echo '<p>' . wp_kses_post( $message ) . '</p>';
			echo '</div>';
		}
	}
}

WPZOOM_Plugin_Loader::init();
