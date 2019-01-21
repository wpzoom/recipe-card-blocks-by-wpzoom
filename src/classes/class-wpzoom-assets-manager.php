<?php
/**
 * Blocks Initializer
 *
 * Enqueue CSS/JS of all the blocks.
 *
 * @since   1.1.0
 * @package WPZOOM Recipe Card Block
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WPZOOM_Assets_Manager' ) ) {
	/**
	 * Main WPZOOM_Assets_Manager Class.
	 *
	 * @since 1.1.0
	 */
	class WPZOOM_Assets_Manager {
		/**
		 * This plugin's instance.
		 *
		 * @var WPZOOM_Assets_Manager
		 * @since 1.1.0
		 */
		private static $instance;

		/**
		 * Provides singleton instance.
		 *
		 * @since 1.1.0
		 * @return self instance
		 */
		public static function instance() {
			if ( null === self::$instance ) {
				self::$instance = new WPZOOM_Assets_Manager();
			}

			return self::$instance;
		}

		/**
		 * The WPZOOM_Recipe_Card_Block_Gutenberg instance.
		 *
		 * @var WPZOOM_Recipe_Card_Block_Gutenberg
		 * @since 1.1.0
		 */
		private $_recipe_card_block;

		/**
		 * The base directory path.
		 *
		 * @var string $_dir
		 */
		private $_dir;

		/**
		 * The base URL path.
		 *
		 * @var string $_url
		 */
		private $_url;

		/**
		 * The Plugin text domain.
		 *
		 * @var string $_textdomain
		 */
		public $_textdomain;

		/**
		 * The Plugin version.
		 *
		 * @var string $_version
		 */
		public $_version;

		/**
		 * The Plugin version.
		 *
		 * @var string $_slug
		 */
		public $_slug;

		/**
		 * The Post Object.
		 *
		 * @var string $post
		 */
		public $post;

		/**
		 * The Constructor.
		 */
		private function __construct() {
			$this->_version 	= WPZOOM_RCB_VERSION;
			$this->_textdomain 	= WPZOOM_RCB_TEXT_DOMAIN;
			$this->_slug    	= 'wpzoom-rcb-block';
			$this->_url     	= untrailingslashit( WPZOOM_RCB_PLUGIN_URL );

			$this->_recipe_card_block = new WPZOOM_Recipe_Card_Block_Gutenberg();

			add_action( 'enqueue_block_assets', array( $this, 'block_assets' ) );
			add_action( 'enqueue_block_assets', array( $this, 'load_icon_fonts' ) );
			add_action( 'enqueue_block_assets', array( $this, 'load_jed_text_domain' ) );
			add_action( 'enqueue_block_editor_assets', array( $this, 'editor_assets' ) );
		}

		/**
		 * Get array of dependencies.
		 *
		 * @param string|string $handle The handle slug.
		 *
		 * @since 1.1.0
		 */
		public function get_dependencies( $handle ) {
			$dependencies = array();

			if ( $this->_slug . '-js' === $handle ) {
				$dependencies = array( 'wp-editor', 'wp-components', 'wp-blocks', 'wp-i18n', 'wp-element', 'wp-compose' );
			}
			elseif ( $this->_slug . '-editor-css' === $handle ) {
				$dependencies = array( 'wp-edit-blocks' );
			}
			elseif ( $this->_slug . '-script' === $handle ) {
				$dependencies = array( 'jquery' );
			}
			elseif ( $this->_slug . '-oldicon-css' === $handle || $this->_slug . '-foodicons-css' === $handle || $this->_slug . '-font-awesome-css' === $handle || $this->_slug . '-genericons-css' === $handle ) {
				$dependencies = array( 'wp-edit-blocks' );
			}
			elseif ( 'wpzoom-rating-stars-script' === $handle ) {
				$dependencies = array( 'jquery', 'wp-blocks', 'wp-i18n' );
			}

			return $dependencies;
		}

		/**
		 * Enqueue Gutenberg block assets for both frontend + backend.
		 *
		 * `wp-blocks`: includes block type registration and related functions.
		 *
		 * @since 1.1.0
		 */
		public function block_assets() {
			$options = get_option( 'wpzoom-recipe-card-settings' );

			// Scripts.
			wp_enqueue_script(
			    $this->_slug . '-script',
			    $this->asset_source( 'js', 'script.js' ),
			    $this->get_dependencies( $this->_slug . '-script' ),
			    $this->_version,
			    true
			);

			wp_enqueue_script(
			    $this->_slug . '-pinit',
			    'https://assets.pinterest.com/js/pinit.js',
			    array(),
			    false,
			    true
			);

			// Styles.
			wp_enqueue_style(
				$this->_slug . '-style-css', // Handle.
				$this->asset_source( '', 'blocks.style.build.css' ), // Block style CSS.
				$this->get_dependencies( $this->_slug . '-style-css' ), // Dependency to include the CSS after it.
				$this->_version
			);

			wp_enqueue_style(
		    	$this->_slug . '-google-font',
		    	'https://fonts.googleapis.com/css?family=Roboto+Condensed:400,400i,700,700i',
		    	false
		    );

		    $this->post = get_post();

		    /**
		     * Localize script data.
		     */
		    $this->localize_script(
		    	$this->_slug . '-script',
		    	'wpzoomRecipeCard',
		    	array(
		    		'version' => $this->_version,
		    		'textdomain' => $this->_textdomain,
		    		'pluginURL' => WPZOOM_RCB_PLUGIN_URL,
		    		'post_permalink' => str_replace( '?p=', '', get_the_permalink( $this->post ) ),
		    		'post_thumbnail_url' => get_the_post_thumbnail_url( $this->post ),
		    		'post_title' => $this->post->post_title,
		    		'post_author_name' => get_the_author_meta( 'display_name', $this->post->post_author ),
		    		'is_pro' => $this->_recipe_card_block->is_pro(),
		    		'setting_options' => ( ! empty( $options ) ? $options : WPZOOM_Settings::get_defaults() )
		    	)
		    );
		}

		/**
		 * Load Jed-formatted localization text domain.
		 *
		 * @since 1.1.0
		 */
		public function load_jed_text_domain() {
			if ( function_exists( 'gutenberg_get_jed_locale_data' ) ) {
				wp_add_inline_script(
					'wp-i18n',
					'wp.i18n.setLocaleData( ' . wp_json_encode( gutenberg_get_jed_locale_data( $this->_textdomain ) ) . ', "' . $this->_textdomain . '" );',
					'after'
				);
			} elseif ( function_exists( 'wp_get_jed_locale_data' ) )  {
				wp_add_inline_script(
					'wp-i18n',
					'wp.i18n.setLocaleData( ' . wp_json_encode( wp_get_jed_locale_data( $this->_textdomain ) ) . ', "' . $this->_textdomain . '" );',
					'after'
				);
			}
		}

		/**
		 * Enqueue Gutenberg block assets for backend editor.
		 *
		 * `wp-blocks`: includes block type registration and related functions.
		 * `wp-element`: includes the WordPress Element abstraction for describing the structure of your blocks.
		 * `wp-i18n`: To internationalize the block's text.
		 *
		 * @since 1.1.0
		 */

        public function editor_assets() {
        	$options = get_option( 'wpzoom-recipe-card-settings' );

            // Scripts.
            wp_enqueue_script(
                $this->_slug . '-js', // Handle.
                $this->asset_source( '', 'blocks.build.js' ), // Block.build.js: We register the block here. Built with Webpack.
                $this->get_dependencies( $this->_slug . '-js' ), // Dependencies, defined above.
                $this->_version,
                true // Enqueue the script in the footer.
            );

            // Tell to WordPress that our script contains translations
            // this function was added in 5.0 version
            if ( function_exists( 'wp_set_script_translations' ) ) {
	            wp_set_script_translations( $this->_slug . '-js', $this->_textdomain );
            }

            // Styles.
            wp_enqueue_style(
                $this->_slug . '-editor-css', // Handle.
                $this->asset_source( '', 'blocks.editor.build.css' ), // Block editor CSS.
                $this->get_dependencies( $this->_slug . '-editor-css' ), // Dependency to include the CSS after it.
                $this->_version
            );
            $this->post = get_post();

            /**
             * Localize script data.
             */
            $this->localize_script(
                $this->_slug . '-js',
                'wpzoomRecipeCard',
                array(
                    'version' => $this->_version,
                    'textdomain' => $this->_textdomain,
                    'pluginURL' => WPZOOM_RCB_PLUGIN_URL,
                    'post_permalink' => str_replace( '?p=', '', get_the_permalink( $this->post ) ),
                    'post_thumbnail_url' => get_the_post_thumbnail_url( $this->post ),
                    'post_title' => $this->post->post_title,
                    'post_author_name' => get_the_author_meta( 'display_name', $this->post->post_author ),
                    'block_style' => 'default',
                    'is_pro' => $this->_recipe_card_block->is_pro(),
                    'setting_options' => ( !empty( $options ) ? $options : WPZOOM_Settings::get_defaults() )
                )
            );
        }


		/**
		 * Load icon fonts.
		 *
		 * To make backward compatibility we include icons from version 1.1.0
		 * That's why we named it 'oldicon'
		 *
		 * @since 1.1.0
		 */
		public function load_icon_fonts() {
			$icon_fonts = array( 'oldicon', 'foodicons', 'font-awesome', 'genericons' );

			foreach ( $icon_fonts as $icon ) {
				wp_enqueue_style(
					$this->_slug . '-' . $icon . '-css', // Handle.
					$this->asset_source( 'css', $icon .'.min.css' ), // Block editor CSS.
					$this->get_dependencies( $this->_slug . '-' . $icon . '_css' ), // Dependency to include the CSS after it.
					$this->_version
				);
			}
		}

		/**
		 * Source assets.
		 *
		 * @since 1.1.0
		 * @param string|string $type The type of resource.
		 * @param string|string $directory Any extra directories needed.
		 */
		public function asset_source( $type = 'js', $directory = null ) {
			if ( 'js' === $type || 'css' === $type ) {
				return $this->_url . '/dist/assets/' . $type . '/' . $directory;
			} else {
				return $this->_url . '/dist/' . $directory;
			}
		}

		/**
		 * Enqueue localization data.
		 *
		 * @since 1.1.0
		 * @access public
		 */
		public function localize_script( $handle, $name, $data ) {
			wp_localize_script( $handle, $name, $data );
		}
	}
}

WPZOOM_Assets_Manager::instance();
