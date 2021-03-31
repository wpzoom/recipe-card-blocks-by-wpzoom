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

class WPZOOM_Elementor {
    public $reusable_block_id = 0;

    /**
     * Elementor load assets constructor.
     *
     * @since 2.7.6
     * @access public
     */
    public function __construct() {
        // Elementor compatibility using Reusable Blocks Extended
        add_action( 'elementor/preview/enqueue_styles',        array( $this, 'preview_enqueue_styles' ), 0 );
        add_action( 'elementor/frontend/widget/before_render', array( $this, 'before_render_widget' ) );
    }

    public function load_block_assets() {
        $assets_slug = WPZOOM_Assets_Manager::$_slug;

        wp_enqueue_script( $assets_slug . '-script' );
        wp_enqueue_script( $assets_slug . '-pinit' );

        // Styles.
        wp_enqueue_style( $assets_slug . '-style-css' );

        // Enable Google Fonts
        if ( '1' === WPZOOM_Settings::get('wpzoom_rcb_settings_enable_google_fonts') ) {
            wp_enqueue_style( $assets_slug . '-google-font' );
        }
    }

    public function preview_enqueue_styles() {
        $this->load_block_assets();
    }

    public function before_render_widget( $widget ) {
        if ( 'wp-widget-reblex-widget' === $widget->get_name() ) {
            $settings = $widget->get_settings();
            $block_id = isset( $settings['wp']['block_id'] ) ? absint( $settings['wp']['block_id'] ) : '';

            $this->reusable_block_id = $block_id;
            $this->load_block_assets();
        }
    }
}

new WPZOOM_Elementor();