<?php
/**
 * Enqueue CSS/JS of block to Elementor.
 *
 * @since   2.7.6
 * @package WPZOOM_Recipe_Card_Blocks
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WPZOOM_Reusable_Blocks_Extended {
    /**
     * Elementor load assets constructor.
     *
     * @since 2.7.6
     * @access public
     */
    public function __construct() {
        if ( isset( $_GET['post_type'] ) && 'wp_block' === esc_attr( $_GET['post_type'] ) ) {
            add_action( 'admin_init', array( $this, 'reblex_merge_stylesheets' ) );
        }
    }

    public function reblex_merge_stylesheets() {
        if ( false !== get_transient( 'reblex_reusable_registered_stylesheets' ) ) {
            $stylesheets = json_decode( get_transient( 'reblex_reusable_registered_stylesheets' ) );

            $stylesheets[] = untrailingslashit( WPZOOM_RCB_PLUGIN_URL ) . '/dist/blocks.style.build.css';
            $stylesheets[] = untrailingslashit( WPZOOM_RCB_PLUGIN_URL ) . '/dist/assets/css/icon-fonts.build.css';

            set_transient( 'reblex_reusable_registered_stylesheets', wp_json_encode( $stylesheets ), DAY_IN_SECONDS );
        }
    }
}

new WPZOOM_Reusable_Blocks_Extended();