<?php
/**
 * Register admin menu elements.
 *
 * @since   2.2.0
 * @package WPZOOM Recipe Card Block
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
			esc_html__( 'Recipe Card', 'wpzoom-recipe-card' ),
			esc_html__( 'Recipe Card', 'wpzoom-recipe-card' ),
			'manage_options',
			WPZOOM_RCB_SETTINGS_PAGE,
			array( $this, 'admin_page' ),
			'none',
			45
		);

		// WPZOOM Recipe Card sub menu item.
		add_submenu_page(
			WPZOOM_RCB_SETTINGS_PAGE,
			esc_html__( 'WPZOOM Recipe Card Settings', 'wpzoom-recipe-card' ),
			esc_html__( 'Settings', 'wpzoom-recipe-card' ),
			'manage_options',
			WPZOOM_RCB_SETTINGS_PAGE,
			array( $this, 'admin_page' )
		);

		add_submenu_page(
			WPZOOM_RCB_SETTINGS_PAGE,
			esc_html__( 'WPZOOM Recipe Card Lite vs PRO', 'wpzoom-recipe-card' ),
			'<span style="color:#FFA921">' . esc_html__( 'Lite vs PRO', 'wpzoom-recipe-card' ) . '</span>',
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
