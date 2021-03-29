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

    public $prepend_before_specified_id = '';

    /**
     * Elementor load assets constructor.
     *
     * @since 2.7.6
     * @access public
     */
    public function __construct() {
        // Elementor compatibility using Reusable Blocks Extended
        add_action( 'elementor/frontend/before_render',        array( $this, 'display_before_specified_id' ) );
        add_action( 'elementor/frontend/the_content',          array( $this, 'render_content' ) );
        add_action( 'elementor/preview/enqueue_styles',        array( $this, 'preview_enqueue_styles' ), 0 );
        add_action( 'elementor/frontend/widget/before_render', array( $this, 'before_render_widget' ) );

        add_filter( 'wpzoom_rcb_before_register_settings',     array( $this, 'add_option_to_settings' ) );
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

    public function display_before_specified_id( $element ) {
        if ( 'section' === $element->get_type() ) {
            $settings = $element->get_settings();
            $section_id = isset( $settings['_element_id'] ) ? esc_attr( $settings['_element_id'] ) : '';
            $specified_id = WPZOOM_Settings::get('wpzoom_rcb_settings_display_snippets_before_id');

            // Display Recipe buttons before specified id
            if ( esc_attr( $specified_id ) === $section_id ) {
                $this->prepend_before_specified_id = esc_attr( $specified_id );
                echo WPZOOM_Recipe_Card_Block::prepend_content_recipe_buttons();
            }
        }
    }

    public function add_option_to_settings( $settings ) {
        $settings['appearance']['sections'][1]['fields'][] = array(
            'id'        => 'wpzoom_rcb_settings_display_snippets_before_id',
            'title'     => __( 'Display Buttons before section id', 'wpzoom-recipe-card' ),
            'type'      => 'input',
            'args'      => array(
                'label_for'     => 'wpzoom_rcb_settings_display_snippets_before_id',
                'class'         => 'wpzoom-rcb-field',
                'description'   => esc_html__( 'Enter the id of the section before which you want to display the buttons in a post/page built with Elementor.', 'wpzoom-recipe-card' ),
                'default'       => '',
                'type'          => 'text'
            )
        );

        return $settings;
    }

    public function render_content( $content ) {
        // Return early if id was found in the post content
        if ( ! empty( $this->prepend_before_specified_id ) ) {
            return $content;
        }

        $block_content = WPZOOM_Assets_Manager::get_reusable_block( $this->reusable_block_id );
        $has_reusable_block = WPZOOM_Assets_Manager::has_reusable_block( 'wpzoom-recipe-card/block-recipe-card', $block_content );

        if ( $has_reusable_block ) {
            $content = WPZOOM_Recipe_Card_Block::prepend_content_recipe_buttons( $content );
        }

        return $content;
    }
}

new WPZOOM_Elementor();