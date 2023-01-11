<?php

namespace WPZOOMElementorRecipeCard;

use Elementor\Plugin;

// Instance the plugin
WPZOOM_Elementor_Recipe_Card::instance();

/**
 * Class WPZOOM_Elementor_Recipe_Card
 */

class WPZOOM_Elementor_Recipe_Card {

	/**
	 * Instance
	 *
	 * @var WPZOOM_Elementor_Recipe_Card The single instance of the class.
	 * @since 1.0.0
	 * @access private
	 * @static
	 */
	private static $_instance = null;

	/**
	 * Instance
	 *
	 * Ensures only one instance of the class is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @access public
	 * @static
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function __construct() {
		add_action( 'elementor/init', array( $this, 'init' ), 9 );
	}

	/**
	 * Initialize the plugin
	 *
	 * Load the plugin only after Elementor (and other plugins) are loaded.
	 * Load the files required to run the plugin.
	 *
	 * Fired by `plugins_loaded` action hook.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function init() {

		// Add Plugin actions
		add_action( 'elementor/elements/categories_registered', array( $this, 'add_widget_categories' ) );
		add_action( 'elementor/widgets/register', array( $this, 'init_widgets' ) );
		add_action( 'elementor/controls/register', array( $this, 'register_controls' ) );

		add_action( 'elementor/editor/before_enqueue_scripts', array( $this, 'plugin_css' ) );

		// Load Custom icons
		add_filter( 'elementor/icons_manager/additional_tabs', array( $this, 'add_custom_icons' ) );
	}

	/**
	 * Enqueue plugin styles.
	 */
	public function plugin_css() {
		wp_enqueue_style( 'wpzoom-recipe-card-elementor', WPZOOM_RCB_PLUGIN_URL . 'elementor/assets/css/wpzoom-recipe-card-elementor.css', WPZOOM_RCB_VERSION );
	}

	/**
	 * Init Widgets
	 *
	 * Include widgets files and register them
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function init_widgets() {

		// Include Widget files
		$widgets = glob( __DIR__ . '/widgets/*', GLOB_ONLYDIR | GLOB_NOSORT );
		foreach ( $widgets as $path ) {
			$slug  = str_replace( __DIR__ . '/widgets/', '', $path );
			$slug_ = str_replace( '-', '_', $slug );
			$file  = trailingslashit( $path ) . $slug . '.php';

			if ( file_exists( $file ) ) {
				require_once $file;
				$class_name = '\WPZOOMElementorRecipeCard\\' . ucwords( $slug_, '_' );
				if ( class_exists( $class_name ) ) {
					// Register widget
					Plugin::instance()->widgets_manager->register( new $class_name() );
				}
			}
		}
	}

	/**
	 * Add Widget Categories
	 *
	 * Add custom widget categories to Elementor.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	function add_widget_categories( $elements_manager ) {
		$elements_manager->add_category(
			'wpzoom-elementor-recipe-card',
			array(
				'title' => esc_html__( 'Recipe Card Blocks', 'recipe-card-blocks-by-wpzoom' ),
				'icon'  => 'fa fa-plug',
			)
		);
	}

	/**
	 * Register controls
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 */
	public function register_controls( $controls_manager ) {
		$controls = array(
			'wpzoom_tagfield' => array(
				'file'  => __DIR__ . '/controls/tagfield.php',
				'class' => 'Controls\WPZOOM_Tagfield',
			),
			'wpzoom_image_picker' => array(
				'file'  => __DIR__ . '/controls/image-picker.php',
				'class' => 'Controls\WPZOOM_Image_Picker'
			)
		);

		foreach ( $controls as $control_type => $control_info ) {
			if ( ! empty( $control_info['file'] ) && ! empty( $control_info['class'] ) ) {
				include_once $control_info['file'];
				if ( class_exists( $control_info['class'] ) ) {
					$class_name = $control_info['class'];
				} elseif ( class_exists( __NAMESPACE__ . '\\' . $control_info['class'] ) ) {
					$class_name = __NAMESPACE__ . '\\' . $control_info['class'];
				}
				$controls_manager->register( new $class_name() );
			}
		}
	}

	/**
	 * Add custom icons to Elementor registry
	 *
	 * @param object $controls_registry
	 * @return void
	 */
	public function icons_filters( $controls_registry ) {

		// Get existing icons
		$icons = $controls_registry->get_control( 'icon' )->get_settings( 'options' );

		$wpzoomIcons = array(
			'food',
			'room-service',
		);

		$icons = array_merge( $wpzoomIcons, $icons );

		// send back new array
		$controls_registry->get_control( 'icon' )->set_settings( 'options', $icons );
	}

	/**
	 * Add custom icons to Elementor Icons tabs (new in v2.6+)
	 *
	 * @param array $tabs Additional tabs for new icon interface.
	 * @return array $tabs
	 */
	public function add_custom_icons( $tabs = array() ) {
		$wpzoomIcons = array(
			'dashicons'  => array(
				'name'          => 'dashicons',
				'label'         => 'Dashicons',
				'url'           => '',
				'enqueue'       => '',
				'prefix'        => 'dashicons-',
				'displayPrefix' => 'dashicons',
				'labelIcon'     => 'dashicons dashicons-food',
				'ver'           => WPZOOM_RCB_VERSION,
				'fetchJson'     => WPZOOM_RCB_PLUGIN_URL . 'elementor/controls/assets/js/icons/dashicons.json',
			),
			'foodicons'  => array(
				'name'          => 'foodicons',
				'label'         => 'Foodicons',
				'url'           => WPZOOM_RCB_PLUGIN_URL . 'dist/assets/css/foodicons.min.css',
				'enqueue'       => '',
				'prefix'        => 'foodicons-',
				'displayPrefix' => 'foodicons',
				'labelIcon'     => 'foodicons foodicons-apple-and-grapes-on-a-bowl',
				'ver'           => WPZOOM_RCB_VERSION,
				'fetchJson'     => WPZOOM_RCB_PLUGIN_URL . 'elementor/controls/assets/js/icons/foodicons.json',
			),
			'genericons' => array(
				'name'          => 'genericons',
				'label'         => 'Genericons',
				'url'           => WPZOOM_RCB_PLUGIN_URL . 'dist/assets/css/genericons.min.css',
				'enqueue'       => '',
				'prefix'        => 'genericons-',
				'displayPrefix' => 'genericons',
				'labelIcon'     => 'genericons genericons-aside',
				'ver'           => WPZOOM_RCB_VERSION,
				'fetchJson'     => WPZOOM_RCB_PLUGIN_URL . 'elementor/controls/assets/js/icons/genericons.json',
			),
			'oldicon'    => array(
				'name'          => 'oldicon',
				'label'         => 'Old Food icons',
				'url'           => WPZOOM_RCB_PLUGIN_URL . 'dist/assets/css/oldicon.min.css',
				'enqueue'       => '',
				'prefix'        => 'oldicon-',
				'displayPrefix' => 'oldicon',
				'labelIcon'     => 'oldicon oldicon-food',
				'ver'           => WPZOOM_RCB_VERSION,
				'fetchJson'     => WPZOOM_RCB_PLUGIN_URL . 'elementor/controls/assets/js/icons/oldicon.json',

			),
		);

		return array_merge( $tabs, $wpzoomIcons );
	}

}