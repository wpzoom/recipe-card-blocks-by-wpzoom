<?php
/**
 * Class Manage Ratings Page
 *
 * @since   3.2.0
 * @package WPZOOM_Recipe_Card_Blocks
 */

if ( ! class_exists( 'WPZoom_Admin_License' ) ) {

    class WPZoom_Admin_License {
        /**
         * Constructor.
         */
        public function __construct() {
            add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
            // Display HTML content for License page
			add_action( 'wpzoom_rcb_admin_license', array( $this, 'license_view' ) );
        }

        /**
         * Enqueue scripts and styles for admin.
         */
        public function admin_enqueue_scripts() {
	        wp_register_script( 'wpzoom-rcb-license-script', plugin_dir_url( __FILE__ ) . 'templates/assets/js/vcustom_js.js', [], WPZOOM_RCB_VERSION );
			wp_localize_script( 'wpzoom-rcb-license-script', 'licenseParams', array(
				'ajaxEndpointURL'        => esc_url( admin_url( 'admin-ajax.php' ) ),
				'checkPurchaseNonce'     => wp_create_nonce( 'check_purchase' ),
				'activateLicenseNonce'   => wp_create_nonce( 'activate_license' ),
				'deactivateLicenseNonce' => wp_create_nonce( 'deactivate_license' ),
			) );

	        wp_register_style( 'wpzoom-rcb-license-styles', plugin_dir_url( __FILE__ ) . 'templates/assets/css/license_style1.css', array(), '1.0' );
        
            wp_enqueue_style( 'inter-font', 'https://fonts.googleapis.com/css2?family=Inter' );
        }
        
        /**
         * Display method for handling the wpzoom_rcb_admin_license action hook.
         */
        public function license_view() {
            $template_path = plugin_dir_path(__FILE__) . 'templates/wpzoom-license.php';
            if (file_exists($template_path)){
                include $template_path;
            }
        }

    }

    // Instantiate the class.
    new WPZoom_Admin_License();
}
