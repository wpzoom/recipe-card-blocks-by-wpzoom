<?php
/**
 * Fired during plugin activation.
 *
 * @since   1.2.0
 * @package WPZOOM Recipe Card Block
 */

/**
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since 1.2.0
 */
class WPZOOM_Plugin_Activator {

	/**
	 * Execute this on activation of the plugin.
	 *
	 * @since 1.2.0
	 */
	public static function activate() {
		add_option( 'wpzoom_rcb_do_activation_redirect', true );
		set_transient( 'wpzoom_rcb_welcome_banner', true, 12 * HOUR_IN_SECONDS );
		
		flush_rewrite_rules();
	}

	/**
	 * Execute this on deactivation of the plugin.
	 *
	 * @since 2.2.0
	 */
	public static function deactivate() {
		flush_rewrite_rules();
	}
}