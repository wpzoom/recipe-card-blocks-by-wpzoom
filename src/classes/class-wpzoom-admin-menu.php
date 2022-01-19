<?php
/**
 * Register admin menu elements.
 *
 * @since   2.2.0
 * @package WPZOOM_Recipe_Card_Blocks
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class for admin menu.
 */
class WPZOOM_Admin_Menu {

	/**
	 * The Constructor.
	 */
	public function __construct() {

		// Let's add menu item with subitems
		add_action( 'admin_menu', array( $this, 'register_menus' ) );
	}

	/**
	 * Register admin menus.
	 */
	public function register_menus() {
		add_menu_page(
			esc_html__( 'Recipe Card Settings', 'recipe-card-blocks-by-wpzoom' ),
			esc_html__( 'Recipe Cards', 'recipe-card-blocks-by-wpzoom' ),
			'manage_options',
			WPZOOM_RCB_SETTINGS_PAGE,
			array( $this, 'admin_page' ),
			'none',
			45
		);

		// WPZOOM Recipe Card sub menu item.
		add_submenu_page(
			WPZOOM_RCB_SETTINGS_PAGE,
			esc_html__( 'Recipe Card Settings', 'recipe-card-blocks-by-wpzoom' ),
			esc_html__( 'Settings', 'recipe-card-blocks-by-wpzoom' ),
			'manage_options',
			WPZOOM_RCB_SETTINGS_PAGE,
			array( $this, 'admin_page' )
		);

		add_submenu_page(
			WPZOOM_RCB_SETTINGS_PAGE,
			esc_html__( 'Recipe Card Free vs. PRO', 'recipe-card-blocks-by-wpzoom' ),
			'<span style="color:#FFA921">' . esc_html__( 'Free vs. PRO', 'recipe-card-blocks-by-wpzoom' ) . '</span>',
			'manage_options',
			'wpzoom-recipe-card-vs-pro',
			array( $this, 'admin_page' )
		);
	}

	/**
	 * Wrapper for the hook to render our custom settings pages.
	 *
	 * @since 2.2.0
	 */
	public function admin_page() {
		do_action( 'wpzoom_rcb_admin_page' );
	}
}

new WPZOOM_Admin_Menu();
