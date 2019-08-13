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
			'data:image/svg+xml;base64,' . base64_encode('<svg id="Layer_1" data-name="Layer 1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 523.22 650.66"><defs><style>.cls-1{fill:#231f20;}</style></defs><title>rcb-recipe-inverted</title><path class="cls-1" d="M512.75,235.58c12.07,3.34,24.26,6.47,36,10.34,5.63,1.78,10.76,5.19,16,7.9-4.48-8.19-68.48-52.74-68.48-52.74l64.36,33c-9.56-22.46-23.37-40.58-46.11-50.39-17-7.41-35.37-12.31-48.74-28.69a119.05,119.05,0,0,0,7.81,44.18C480.51,218,493.51,230.18,512.75,235.58Z" transform="translate(-126.39 -62.24)"/><path class="cls-1" d="M581.75,62.25h-256A8.88,8.88,0,0,0,319,64.94a.29.29,0,0,0-.15.16C317.06,67,129,262.1,129,262.1a.58.58,0,0,0-.16.19,9.2,9.2,0,0,0-2.43,6.12v386.8c3,32.5,31.05,57.42,64.32,57.42L316,712.9H581.75c37.37,0,67.86-26.18,67.86-58.33v-534C649.61,88.37,619.12,62.25,581.75,62.25ZM257.32,619.61H201.06a9.23,9.23,0,1,1,0-18.46h56.26a9.23,9.23,0,1,1,0,18.46Zm0-57.36H201.06a9.26,9.26,0,1,1,0-18.52h56.26a9.26,9.26,0,0,1,0,18.52Zm0-57.29H201.06c-.2,0-.41,0-.61,0a9.27,9.27,0,0,1,.61-18.53h56.87a9.27,9.27,0,1,1-.61,18.53Zm0-57.37H201.06c-.2,0-.41,0-.61,0a9.27,9.27,0,0,1,.61-18.53h56.87a9.27,9.27,0,1,1-.61,18.53Zm0-57.36H200.45a9.27,9.27,0,0,1,.61-18.53h56.87a9.27,9.27,0,1,1-.61,18.53Zm-65.54-66.61a9.28,9.28,0,0,1,9.28-9.28h56.26a9.28,9.28,0,1,1,0,18.56H201.06A9.29,9.29,0,0,1,191.78,323.62ZM316.34,219.43c0,22-22.15,39.84-49.33,39.84H157.47L316.34,94.43Zm131.25-58.49a22.22,22.22,0,0,1,22.18-22.21h91a22.18,22.18,0,0,1,22.15,22.21v91a22.16,22.16,0,0,1-22.19,22.13h-91a22.2,22.2,0,0,1-22.15-22.25ZM330.9,429.06H573.63a9.27,9.27,0,0,1,0,18.53H330.9c-.2,0-.41,0-.61,0a9.27,9.27,0,0,1,.61-18.53Zm-9.57-48.4a9.27,9.27,0,0,1,9.57-9H573.63a9.27,9.27,0,0,1,0,18.53H330.29A9.27,9.27,0,0,1,321.33,380.66Zm9.57,105.77H573.63a9.27,9.27,0,0,1,0,18.53H330.9c-.2,0-.41,0-.61,0a9.27,9.27,0,0,1,.61-18.53ZM573.63,619.61H330.9a9.23,9.23,0,1,1,0-18.46H573.63a9.23,9.23,0,0,1,0,18.46Zm0-57.36H330.9a9.26,9.26,0,0,1,0-18.52H573.63a9.26,9.26,0,0,1,0,18.52Zm0-229.35H330.9a9.28,9.28,0,0,1,0-18.56H573.63a9.28,9.28,0,0,1,0,18.56Z" transform="translate(-126.39 -62.24)"/></svg>'),
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
