<?php
/**
 * Blocks Initializer
 *
 * Enqueue CSS/JS of all the blocks.
 *
 * @since   1.1.0
 * @package WPZOOM_Recipe_Card_Blocks
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
		 * The base URL path.
		 *
		 * @var string $_url
		 */
		public $_url;

		/**
		 * The Plugin version.
		 *
		 * @var string $_slug
		 */
		public static $_slug = 'wpzoom-rcb-block';

		/**
		 * The Constructor.
		 */
		private function __construct() {
			add_action( 'init', array( $this, 'init' ) );

			add_action( 'enqueue_block_assets', array( $this, 'frontend_register_scripts' ), 5 );
			add_action( 'enqueue_block_assets', array( $this, 'frontend_register_styles' ), 5 );
			add_action( 'enqueue_block_assets', array( $this, 'block_assets' ) );
			add_action( 'enqueue_block_assets', array( $this, 'load_icon_fonts' ) );

			add_action( 'enqueue_block_editor_assets', array( $this, 'editor_register_scripts' ), 5 );
			add_action( 'enqueue_block_editor_assets', array( $this, 'editor_register_styles' ), 5 );
			add_action( 'enqueue_block_editor_assets', array( $this, 'editor_assets' ) );

			add_action( 'amp_post_template_css', array( $this, 'amp_for_wp_include_css_template' ) );
		}

		public function init() {
			$this->_url = untrailingslashit( WPZOOM_RCB_PLUGIN_URL );
		}

		/**
		 * Registers Front-end block scripts.
		 *
		 * Fired by `enqueu_block_assets` action.
		 *
		 * @access public
		 */
		public function frontend_register_scripts() {
			wp_register_script(
				self::$_slug . '-script',
				$this->asset_source( 'js', 'script.js' ),
				$this->get_dependencies( self::$_slug . '-script' ),
				WPZOOM_RCB_VERSION,
				true
			);

			wp_register_script(
				self::$_slug . '-pinit',
				'https://assets.pinterest.com/js/pinit.js',
				array(),
				false,
				true
			);
		}

		/**
		 * Registers Front-end block styles.
		 *
		 * Fired by `enqueu_block_assets` action.
		 *
		 * @access public
		 */
		public function frontend_register_styles() {
			wp_register_style(
				self::$_slug . '-style-css',
				$this->asset_source( '', 'blocks.style.build.css' ),
				$this->get_dependencies( self::$_slug . '-style-css' ),
				WPZOOM_RCB_VERSION
			);

			wp_register_style(
				self::$_slug . '-icon-fonts-css',
				$this->asset_source( 'css', 'icon-fonts.build.css' ),
				$this->get_dependencies( self::$_slug . '-icon-fonts-css' ),
				WPZOOM_RCB_VERSION
			);

		}

		/**
		 * Registers Editor block scripts.
		 *
		 * Fired by `enqueue_block_editor_assets` action.
		 *
		 * @access public
		 */
		public function editor_register_scripts() {
			wp_register_script(
				self::$_slug . '-js',
				$this->asset_source( '', 'blocks.build.js' ),
				$this->get_dependencies( self::$_slug . '-js' ),
				WPZOOM_RCB_VERSION,
				true
			);
		}

		/**
		 * Registers Editor block styles.
		 *
		 * Fired by `enqueue_block_editor_assets` action.
		 *
		 * @access public
		 */
		public function editor_register_styles() {
			wp_register_style(
				self::$_slug . '-editor-css',
				$this->asset_source( '', 'blocks.editor.build.css' ),
				$this->get_dependencies( self::$_slug . '-editor-css' ),
				WPZOOM_RCB_VERSION
			);
		}

		/**
		 * Check the post content has reusable block
		 *
		 * @since  2.7.2
		 * @param  string      $block_name The block name.
		 * @param  int         $reusable_block_id The reusable block post ID.
		 * @param  boolean|int $content The post content.
		 * @return boolean     Return true if post content has provided block name as reusable block, else return false.
		 */
		public static function has_reusable_block( $block_name, $reusable_block_id = 0, $content = '' ) {
			$has_reusable_block = false;

			/**
			 * Loop reusable blocks to get needed block
			 *
			 * @since 2.7.12
			 */
			if ( ! empty( self::get_reusable_block( absint( $reusable_block_id ) ) ) ) {
				$args  = array(
					'post_type'      => 'wp_block',
					'posts_per_page' => -1,
					'post_status'    => 'publish',
				);
				$query = new WP_Query( $args );

				while ( $query->have_posts() ) {
					$query->the_post();
					if ( absint( $reusable_block_id ) === get_the_ID() ) {
						$content = get_post_field( 'post_content', get_the_ID() );
						if ( has_block( $block_name, $content ) ) {
							$has_reusable_block = true;
							return $has_reusable_block;
						}
					}
				}

				// Reset global post variable. After this point, we are back to the Main Query object.
				wp_reset_postdata();
			}

			// Early return if $has_reusable_block is true.
			if ( true === $has_reusable_block ) {
				return;
			}

			if ( empty( $content ) ) {
				$content = get_post_field( 'post_content', get_the_ID() );
			}

			if ( $content ) {
				if ( has_block( 'block', $content ) ) {
					// Check reusable blocks.
					$blocks = parse_blocks( $content );

					if ( ! is_array( $blocks ) || empty( $blocks ) ) {
						return false;
					}

					foreach ( $blocks as $block ) {
						if ( $block['blockName'] === 'core/block' && ! empty( $block['attrs']['ref'] ) ) {
							$reusable_block_id = absint( $block['attrs']['ref'] );

							if ( has_block( $block_name, $reusable_block_id ) ) {
								return true;
							} elseif ( ! empty( self::get_reusable_block( $reusable_block_id ) ) ) {
								return true;
							}
						}
					}
				} elseif ( has_block( $block_name, $content ) ) {
					return true;
				} elseif ( has_shortcode( $content, 'reblex' ) ) {
					return true;
				} else {
					return false;
				}
			}

			return false;
		}

		public static function get_reusable_block( $id ) {
			$post = '';

			if ( ! is_string( $id ) && $id > 0 ) {
				$wp_post = get_post( $id );
				if ( $wp_post instanceof WP_Post ) {
					$post = $wp_post->post_content;
				}
			}

			return $post;
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

			if ( self::$_slug . '-js' === $handle ) {
				$dependencies = array( 'wp-components', 'wp-blocks', 'wp-i18n', 'wp-element', 'wp-compose' );
			} elseif ( self::$_slug . '-editor-css' === $handle ) {
				$dependencies = array( 'wp-edit-blocks' );
			} elseif ( self::$_slug . '-script' === $handle ) {
				$dependencies = array( 'jquery' );
			} elseif ( self::$_slug . '-icon-fonts-css' === $handle ) {
				$dependencies = array();
			} elseif ( 'wpzoom-rating-stars-script' === $handle ) {
				$dependencies = array( 'jquery' );
			} elseif ( 'wpzoom-comment-rating-script' === $handle ) {
				$dependencies = array( 'jquery' );
			} elseif ( self::$_slug . '-masonry-gallery' === $handle ) {
				$dependencies = array( 'jquery-masonry', 'imagesloaded' );
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
			if ( is_admin() ) {
				wp_enqueue_style( self::$_slug . '-style-css' );

			} else {

				/**
				 * Load Assets only on single page if option is unchecked
				 *
				 * @since 2.7.3
				 */
				if ( '1' !== WPZOOM_Settings::get( 'wpzoom_rcb_settings_load_assets_on_all_pages' ) && ! is_single() ) {
					return false;
				}

				$should_enqueue =
					has_block( 'wpzoom-recipe-card/block-details' ) ||
					has_block( 'wpzoom-recipe-card/block-ingredients' ) ||
					has_block( 'wpzoom-recipe-card/block-directions' ) ||
					has_block( 'wpzoom-recipe-card/block-print-recipe' ) ||
					has_block( 'wpzoom-recipe-card/block-jump-to-recipe' ) ||
					has_block( 'wpzoom-recipe-card/block-recipe-card' ) ||
					has_block( 'wpzoom-recipe-card/recipe-block-from-posts' ) ||
					has_block( 'wpzoom-recipe-card/block-nutrition' ) ||
					self::has_cpt_rcb_shortcode() ||
					self::has_cpt_rcb_elementor_widget();

				$has_reusable_block =
					self::has_reusable_block( 'wpzoom-recipe-card/block-details' ) ||
					self::has_reusable_block( 'wpzoom-recipe-card/block-ingredients' ) ||
					self::has_reusable_block( 'wpzoom-recipe-card/block-directions' ) ||
					self::has_reusable_block( 'wpzoom-recipe-card/block-print-recipe' ) ||
					self::has_reusable_block( 'wpzoom-recipe-card/block-jump-to-recipe' ) ||
					self::has_reusable_block( 'wpzoom-recipe-card/block-recipe-card' ) ||
					self::has_reusable_block( 'wpzoom-recipe-card/recipe-block-from-posts' ) ||
					self::has_reusable_block( 'wpzoom-recipe-card/block-nutrition' );

				$posts_loop_page = is_home() || is_archive() || is_search();

				if ( $should_enqueue || $has_reusable_block || $posts_loop_page ) {
					wp_enqueue_script( self::$_slug . '-script' );

					// Load Pinterest script
					if ( '1' === WPZOOM_Settings::get( 'wpzoom_rcb_settings_load_pinterest_script' ) ) {
						wp_enqueue_script( self::$_slug . '-pinit' );
					}

					wp_enqueue_style( self::$_slug . '-style-css' );

				}

				/**
				 * Localize script data.
				 */
				$this->localize_script(
					self::$_slug . '-script',
					'wpzoomRecipeCard',
					array(
						'pluginURL'  => WPZOOM_RCB_PLUGIN_URL,
						'homeURL'    => self::get_home_url(),
						'permalinks' => get_option( 'permalink_structure' ),
						'ajax_url'   => admin_url( 'admin-ajax.php' ),
						'nonce'      => wp_create_nonce( 'wpzoom_rcb' ),
						'api_nonce'  => wp_create_nonce( 'wp_rest' ),
					)
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
			$options = WPZOOM_Settings::get_settings();

			wp_enqueue_script( self::$_slug . '-js' );

			// Tell to WordPress that our script contains translations
			// this function was added in 5.0 version
			if ( function_exists( 'wp_set_script_translations' ) ) {
				wp_set_script_translations(
					self::$_slug . '-js',
					WPZOOM_RCB_TEXT_DOMAIN,
					WPZOOM_RCB_PLUGIN_DIR . 'languages'
				);
			}

			wp_enqueue_style( self::$_slug . '-editor-css' );

			/**
			 * Localize script data.
			 */
			$this->localize_script(
				self::$_slug . '-js',
				'wpzoomRecipeCard',
				array(
					'version'             => WPZOOM_RCB_VERSION,
					'textdomain'          => WPZOOM_RCB_TEXT_DOMAIN,
					'pluginURL'           => WPZOOM_RCB_PLUGIN_URL,
					'is_pro'              => WPZOOM_Recipe_Card_Block_Gutenberg::is_pro(),
					'setting_options'     => ( ! empty( $options ) ? $options : WPZOOM_Settings::get_defaults() ),
					'nutritionFactsLabel' => WPZOOM_Nutrition_Block::$labels,
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
			// enqueue all icon fonts only in admin panel
			if ( is_admin() ) {
				wp_enqueue_style( self::$_slug . '-icon-fonts-css' );
			}

			/**
			 * Load Assets only on single page if option is unchecked
			 *
			 * @since 2.7.3
			 */
			if ( '1' !== WPZOOM_Settings::get( 'wpzoom_rcb_settings_load_assets_on_all_pages' ) && ! is_single() ) {
				return false;
			}

			if (
				! is_admin() &&
				( has_block( 'wpzoom-recipe-card/block-details' ) || has_block( 'wpzoom-recipe-card/block-recipe-card' ) || has_block( 'wpzoom-recipe-card/recipe-block-from-posts' ) || self::has_reusable_block( 'wpzoom-recipe-card/block-details' ) || self::has_reusable_block( 'wpzoom-recipe-card/block-recipe-card' ) || self::has_cpt_rcb_elementor_widget() || self::has_cpt_rcb_shortcode() )
			) {
				wp_enqueue_style( self::$_slug . '-icon-fonts-css' );
			}

			if ( is_home() || is_archive() || is_search() ) {
				wp_enqueue_style( self::$_slug . '-icon-fonts-css' );
			}
		}

		/**
		 * Check the post content has cpt rcb shortcode
		 *
		 * @since  3.0.3
		 * @param  int         $post_id The post ID.
		 * @param  boolean|int $content The post content.
		 * @return boolean     Return true if post content has cpt rcb shortcode, else return false.
		 */
		public static function has_cpt_rcb_shortcode( $post_id = 0, $content = '' ) {
			
			$post_id = $post_id > 0 ? $post_id : get_the_ID();
			
			if ( empty( $content ) ) {
				$content = get_post_field( 'post_content', $post_id );
			}

			if ( $content ) {			
				if ( has_shortcode( $content, 'wpzoom_rcb_post' ) ) {
					return true;
				}
			}
			return false;
		}

		/**
		 * Check the content has cpt rcb elementor widget
		 *
		 * @since  2.9.1
		 * @param  int         $post_id The post ID.
		 * @param  boolean|int $content The post content.
		 * @return boolean     Return true if post content has cpt rcb elementor widget, else return false.
		 */
		public static function has_cpt_rcb_elementor_widget( $post_id = 0, $content = '' ) {

			if ( !defined( 'ELEMENTOR_VERSION' ) && !is_callable( 'Elementor\Plugin::instance' ) ) {
				return false;
			}

			$post_id = $post_id > 0 ? $post_id : get_the_ID();
			
			$elementor_data = get_post_meta( $post_id, '_elementor_data' );	

			if ( isset( $elementor_data[0] ) && is_string( $elementor_data[0] ) ) {

				$regExp = '/"widgetType":"([^"]*)/i';
				$outputArray = array();
		
				if ( preg_match_all( $regExp, $elementor_data[0], $outputArray, PREG_SET_ORDER) ) {}
				foreach( $outputArray as $found ) {
					if( in_array( 'wpzoom-elementor-recipe-card-widget-cpt', $found ) ) {
						return true;
					}
				}	
			}
			
			return false;
		}

		/**
		 * Include block CSS to AMP for WP template
		 *
		 * @since 2.6.5
		 * @return string Combined CSS content from block build file and inline CSS
		 */
		public function amp_for_wp_include_css_template() {
			$style    = WPZOOM_Recipe_Card_Block::$style;
			$settings = WPZOOM_Recipe_Card_Block::$settings;

			$block_class_name = ".wp-block-wpzoom-recipe-card-block-recipe-card.is-style-{$style}";
			$primary_color    = $settings['primary_color'];

			$fa_regular_400_woff2 = $this->asset_source( 'webfonts', 'fa-regular-400.woff2' );
			$fa_solid_900_woff2 = $this->asset_source( 'webfonts', 'fa-solid-900.woff2' );
			$foodicons_woff = $this->asset_source( 'webfonts', 'Foodicons.woff' );
			$genericons_woff = $this->asset_source( 'webfonts', 'Genericons.woff' );
			$oldicon_woff = $this->asset_source( 'webfonts', 'Oldicon.woff' );

			$inline_CSS  = file_get_contents( $this->asset_source( '', 'blocks.style.build.css' ) );
			$inline_CSS .= file_get_contents( $this->asset_source( 'css', 'amp-icon-fonts.build.css' ) );

			$inline_CSS .= '.artl-cnt ul li:before {display: none}';
			$inline_CSS .= "{$block_class_name} .recipe-card-notes .recipe-card-notes-list>li::before {
                background-color: {$primary_color};
            }";
			$inline_CSS .= "@font-face{font-family:\"Font Awesome 5 Free\";font-style:normal;font-weight:400;font-display:block;src:url({$fa_regular_400_woff2}) format(\"woff2\")}.far{font-weight:400}";
			$inline_CSS .= "@font-face{font-family:\"Font Awesome 5 Free\";font-style:normal;font-weight:900;font-display:block;src:url({$fa_solid_900_woff2}) format(\"woff2\")}";
			$inline_CSS .= "@font-face{font-family:FoodIcons;src:url({$foodicons_woff}) format(\"woff\");font-weight:400;font-display:block;font-style:normal}";
			$inline_CSS .= "@font-face{font-family:Genericons;src:url({$genericons_woff}) format(\"woff\");font-weight:400;font-display:block;font-style:normal}";
			$inline_CSS .= "@font-face{font-family:Oldicon;src:url({$oldicon_woff}) format(\"woff\");font-weight:400;font-display:block;font-style:normal}";

			if ( $style === 'default' ) {
				if ( ! empty( $primary_color ) ) {
					$inline_CSS .= "{$block_class_name} .recipe-card-image .wpzoom-recipe-card-print-link .btn-print-link {
                        background-color: {$primary_color};
                    }";
					$inline_CSS .= "{$block_class_name} .details-items .detail-item .detail-item-icon {
                        color: {$primary_color};
                    }";
				}
			}

			if ( $style === 'newdesign' ) {
				if ( ! empty( $primary_color ) ) {
					$inline_CSS .= "{$block_class_name} .recipe-card-image .wpzoom-recipe-card-print-link .btn-print-link {
                        background-color: {$primary_color};
                        box-shadow: 0 5px 40px {$primary_color};
                    }";
					$inline_CSS .= "{$block_class_name} .details-items .detail-item .detail-item-icon {
                        color: {$primary_color};
                    }";
					$inline_CSS .= "{$block_class_name} .ingredients-list>li .tick-circle {
                        border: 2px solid {$primary_color};
                    }";
				}
				$inline_CSS .= "{$block_class_name} .ingredients-list > li .tick-circle {
                    border-color: {$primary_color};
                }";
				$inline_CSS .= "{$block_class_name} .ingredients-list > li.ticked .tick-circle {
                    border-color: {$primary_color} !important;
                    background-color: {$primary_color};
                }";
			}

			if ( $style === 'simple' ) {
				if ( ! empty( $primary_color ) ) {
					$inline_CSS .= "{$block_class_name} .recipe-card-image .wpzoom-recipe-card-print-link .btn-print-link {
                        background-color: {$primary_color};
                    }";
					$inline_CSS .= "{$block_class_name} .details-items .detail-item .detail-item-icon {
                        color: {$primary_color};
                    }";
					$inline_CSS .= "{$block_class_name} .ingredients-list>li .tick-circle {
                        border: 2px solid {$primary_color};
                    }";
				}
			}

			printf( '/* WPZOOM Recipe Card Inline styles */ %s', $inline_CSS );
		}

		/**
		 * Source assets.
		 *
		 * @since 1.1.0
		 * @param string|string $type The type of resource.
		 * @param string|string $directory Any extra directories needed.
		 */
		public function asset_source( $type = 'js', $directory = null ) {
			if ( 'js' === $type || 'css' === $type || 'webfonts' === $type || 'images' === $type ) {
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

		/**
		 * Compatibility with multilingual plugins for home URL.
		 *
		 * @since 2.6.3
		 */
		public static function get_home_url() {
			$home_url = home_url();

			// Polylang Compatibility.
			if ( function_exists( 'pll_home_url' ) ) {
				$home_url = pll_home_url();
			}

			// Add trailing slash unless there are query parameters.
			if ( false === strpos( $home_url, '?' ) ) {
				$home_url = trailingslashit( $home_url );
			}

			return $home_url;
		}
	}
}

WPZOOM_Assets_Manager::instance();
