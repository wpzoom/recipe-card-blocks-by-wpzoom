<?php
/**
 * Fired during plugin activation.
 *
 * @since   1.2.0
 * @package WPZOOM_Recipe_Card_Blocks
 */

/**
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since 1.2.0
 */
final class WPZOOM_Plugin_Activator {

	/**
	 * Initialize hooks.
	 *
	 * @since 2.2.0
	 * @return void
	 */
	static public function init() {
		$basename = plugin_basename( WPZOOM_RCB_PLUGIN_FILE );

		// Activation
		register_activation_hook( WPZOOM_RCB_PLUGIN_FILE, __CLASS__ . '::activate' );

		// Deactivation
		register_deactivation_hook( WPZOOM_RCB_PLUGIN_FILE, __CLASS__ . '::deactivate' );

		// Filters
		add_filter( 'plugin_action_links_' . $basename, __CLASS__ . '::render_plugin_action_links' );
	}

	/**
	 * Execute this on activation of the plugin.
	 *
	 * @since 1.2.0
	 */
	public static function activate() {
		/**
		 * Allow developers to hook activation.
		 * @see wpzoom_recipe_card_activate
		 */
		$activate = apply_filters( 'wpzoom_recipe_card_activate', true );

		if ( $activate ) {
			add_option( 'wpzoom_rcb_do_activation_redirect', true );
			set_transient( 'wpzoom_rcb_welcome_banner', true, 12 * HOUR_IN_SECONDS );
			
			flush_rewrite_rules();
		}
	}

	/**
	 * Execute this on deactivation of the plugin.
	 *
	 * @since 2.2.0
	 */
	public static function deactivate() {
		/**
		 * Allow developers to hook deactivation.
		 * @see wpzoom_recipe_card_deactivate
		 */
		$deactivate = apply_filters( 'wpzoom_recipe_card_deactivate', true );

		if ( $deactivate ) {
			delete_option( 'wpzoom_rcb_do_activation_redirect' );
			delete_transient( 'wpzoom_rcb_welcome_banner' );

			flush_rewrite_rules();
		}
	}

	/**
	 * Renders the link for the row actions on the plugins page.
	 *
	 * @since 2.2.0
	 * @param array $actions An array of row action links.
	 * @return array
	 */
	static public function render_plugin_action_links( $actions ) {
		// Is Lite version?
		if ( WPZOOM_RCB_HAS_PRO === false ) {
			$url = self::get_upgrade_url();
			$actions[] = '<a href="' . $url . '" style="color:#FFA921;font-weight:bold;" target="_blank">' . _x( 'Go Premium', 'Plugin action link label.', 'wpzoom-recipe-card' ) . '</a>';
		}

		return $actions;
	}

	/**
	 * Returns the URL to upgrade the plugin to the premium version.
	 *
	 * @since 2.2.0
	 * @param array $params An array of key/value params to add to the query string.
	 * @return string
	 */
	static public function get_upgrade_url( $params = array() ) {
		/**
		 * Use this filter to modify the upgrade URL in Recipe Card Blocks by WPZOOM Lite.
		 * @see wpzoom_recipe_card_upgrade_url
		 */
		return apply_filters( 'wpzoom_recipe_card_upgrade_url', self::get_store_url( 'plugins/recipe-card-blocks/', $params ) );
	}

	/**
	 * Returns a URL that points to the WPZOOM store.
	 *
	 * @since 2.2.0
	 * @param string $path A URL path to append to the store URL.
	 * @param array $params An array of key/value params to add to the query string.
	 * @return string
	 */
	static public function get_store_url( $path = '', $params = array() ) {
		if ( ! empty( $params ) ) {
			$url = trailingslashit( WPZOOM_RCB_STORE_URL . $path ) . '?' . http_build_query( $params, '', '&' );
		}
		else {
			$url = trailingslashit( WPZOOM_RCB_STORE_URL . $path );
		}

		return apply_filters( 'wpzoom_recipe_card_store_url', $url, $path );
	}
}

WPZOOM_Plugin_Activator::init();