<?php
namespace WPZOOMElementorRecipeCard;

use Elementor\Widget_Base;
use Elementor\Group_Control_Background;
use Elementor\Repeater;
use Elementor\Control_Media;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Image_Size;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Css_Filter;
use Elementor\Group_Control_Text_Shadow;
use Elementor\Core\Schemes\Typography;
use Elementor\Plugin;
use Elementor\Utils;
use Elementor\Icons_Manager;
use Elementor\Modules\DynamicTags\Module as TagsModule;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * WPZOOM Elementor Recipe Card Widget
 *
 * Elementor widget that inserts a customizable recipe card.
 *
 * @since 1.0.0
 */
class Recipe_Card extends Widget_Base {

	/**
	 * The post Object.
	 *
	 * @since 1.1.0
	 */
	public static $recipe;

	/**
	 * Class instance Helpers.
	 *
	 * @var WPZOOM_Helpers
	 * @since 1.1.0
	 */
	public static $helpers;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function __construct( $data = array(), $args = null ) {
		parent::__construct( $data, $args );

		self::$recipe = get_post();

		wp_register_style( 'wpzoom-rcb-block-style-css', WPZOOM_RCB_PLUGIN_URL . '/dist/blocks.style.build.css', null, WPZOOM_RCB_VERSION );
		wp_register_style( 'wpzoom-rcb-elementor-css-frontend', plugins_url( 'frontend.css', __FILE__ ), null, WPZOOM_RCB_VERSION );

		// Register custom icons css
		wp_register_style( 'wpzoom-rcb-block-icons-css', WPZOOM_RCB_PLUGIN_URL . '/dist/assets/css/icon-fonts.build.css', null, WPZOOM_RCB_VERSION );
		wp_register_style( 'wpzoom-rcb-block-oldicon-css', WPZOOM_RCB_PLUGIN_URL . '/dist/assets/css/oldicon.min.css', null, WPZOOM_RCB_VERSION );
		wp_register_style( 'wpzoom-rcb-block-foodicons-css', WPZOOM_RCB_PLUGIN_URL . '/dist/assets/css/foodicons.min.css', null, WPZOOM_RCB_VERSION );
		wp_register_style( 'wpzoom-rcb-block-genericons-css', WPZOOM_RCB_PLUGIN_URL . '/dist/assets/css/genericons.min.css', null, WPZOOM_RCB_VERSION );

		wp_register_script( 'wpzoom-rcb-elementor-print-js', WPZOOM_RCB_PLUGIN_URL . 'elementor/assets/js/jQuery.print.min.js', array( 'jquery' ), WPZOOM_RCB_VERSION, true );
		wp_register_script( 'wpzoom-rcb-elementor-js-frontend', plugins_url( 'frontend.js', __FILE__ ), array( 'jquery' ), WPZOOM_RCB_VERSION, true );

		wp_localize_script(
			'wpzoom-rcb-elementor-js-frontend',
			'wpzoomRecipeCardPrint',
			array(
				'stylesheetPrintURL' => plugins_url( 'print.css', __FILE__ ),
			)
		);

		wp_register_script( 'wpzoom-rcb-script-js', WPZOOM_RCB_PLUGIN_URL . 'dist/assets/js/script.js', array( 'jquery' ), WPZOOM_RCB_VERSION, true );

		wp_localize_script(
			'wpzoom-rcb-script-js',
			'wpzoomRecipeCard',
			array(
				'pluginURL'  => WPZOOM_RCB_PLUGIN_URL,
				'homeURL'    => \WPZOOM_Assets_Manager::get_home_url(),
				'permalinks' => get_option( 'permalink_structure' ),
				'ajax_url'   => admin_url( 'admin-ajax.php' ),
				'nonce'      => wp_create_nonce( 'wpzoom_rcb' ),
				'api_nonce'  => wp_create_nonce( 'wp_rest' ),
				'strings'    => array(
					'loading-gallery-media' => esc_html__( 'Loading gallery media', 'wpzoom-recipe-card' ),
				),
			)
		);
	}

	public function preview_enqueue_styles() {
		$this->load_block_assets();
	}


	/**
	 * Get widget name.
	 *
	 * Retrieve widget name.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return string Widget name.
	 */
	public function get_name() {
		return 'wpzoom-elementor-recipe-card-widget';
	}

	/**
	 * Get widget title.
	 *
	 * Retrieve widget title.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return string Widget title.
	 */
	public function get_title() {
		return esc_html__( 'Recipe Card Block', 'wpzoom-recipe-card' );
	}

	/**
	 * Get widget icon.
	 *
	 * Retrieve widget icon.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return string Widget icon.
	 */
	public function get_icon() {
		return 'eicon-menu-card';
	}

	/**
	 * Get widget categories.
	 *
	 * Retrieve the list of categories the widget belongs to.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return array Widget categories.
	 */
	public function get_categories() {
		return array( 'wpzoom-elementor-recipe-card' );
	}

	/**
	 * Style Dependencies.
	 *
	 * Returns all the styles the widget depends on.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return array Style slugs.
	 */
	public function get_style_depends() {
		return array(
			'wpzoom-rcb-block-icons-css',
			'wpzoom-rcb-block-oldicon-css',
			'wpzoom-rcb-block-foodicons-css',
			'wpzoom-rcb-block-genericons-css',
			'wpzoom-rcb-block-style-css',
			'wpzoom-rcb-elementor-css-frontend',
		);
	}

	/**
	 * Script Dependencies.
	 *
	 * Returns all the scripts the widget depends on.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return array Script slugs.
	 */
	public function get_script_depends() {
		$assets_slug = \WPZOOM_Assets_Manager::$_slug;

		$deps = array(
			'jquery',
			'wpzoom-rcb-elementor-print-js',
			'wpzoom-rcb-elementor-js-frontend',
			'wpzoom-rcb-script-js',
			$assets_slug . '-script',
			$assets_slug . '-pinit',
		);

		return $deps;
	}

	/**
	 * Register Controls.
	 *
	 * Registers all the controls for this widget.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return void
	 */
	protected function register_controls() {
		$this->register_content_controls();
		$this->register_style_controls();
	}

	/**
	 * Register Content Controls.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return void
	 */
	protected function register_content_controls() {
		$this->start_controls_section(
			'_section_recipe_card',
			array(
				'label' => esc_html__( 'Recipe Details', 'wpzoom-recipe-card' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'title',
			array(
				'label'       => esc_html__( 'Recipe Title', 'wpzoom-recipe-card' ),
				'type'        => Controls_Manager::TEXT,
				'dynamic'     => array(
					'active' => true,
				),
				'label_block' => true,
				'placeholder' => esc_html__( 'Recipe Title', 'wpzoom-recipe-card' ),
			)
		);
		$this->add_control(
			'recipe_card_summary',
			array(
				'label'       => esc_html__( 'Recipe Summary', 'wpzoom-recipe-card' ),
				'type'        => Controls_Manager::TEXTAREA,
				'dynamic'     => array(
					'active' => true,
				),
				'placeholder' => esc_html__( 'Enter your recipe card summary here', 'wpzoom-recipe-card' ),
			)
		);

		$this->add_control(
			'image',
			array(
				'label'       => esc_html__( 'Recipe Card Image (required)', 'wpzoom-recipe-card' ),
				'description' => esc_html__( 'Upload image for Recipe Card.', 'wpzoom-recipe-card' ),
				'type'        => Controls_Manager::MEDIA,
				'dynamic'     => array(
					'active' => true,
				),
				'default'     => array(
					'url' => Utils::get_placeholder_image_src(),
				),
			)
		);

		$this->add_group_control(
			Group_Control_Image_Size::get_type(),
			array(
				'name'      => 'thumbnail', // Usage: `{name}_size` and `{name}_custom_dimension`, in this case `thumbnail_size` and `thumbnail_custom_dimension`.
				'default'   => 'wpzoom-rcb-block-header',
				'separator' => 'none',
			)
		);
		$this->add_control(
			'show_image',
			array(
				'label'       => esc_html__( 'Show Image', 'wpzoom-recipe-card' ),
				'description' => esc_html__( 'Show Recipe Image on Front-End', 'wpzoom-recipe-card' ),
				'type'        => Controls_Manager::SWITCHER,
				'label_on'    => esc_html__( 'Show', 'wpzoom-recipe-card' ),
				'label_off'   => esc_html__( 'Hide', 'wpzoom-recipe-card' ),
				'default'     => 'yes',
			)
		);
		$this->add_control(
			'show_print',
			array(
				'label'       => esc_html__( 'Print Button', 'wpzoom-recipe-card' ),
				'description' => esc_html__( 'Display Print Button', 'wpzoom-recipe-card' ),
				'type'        => Controls_Manager::SWITCHER,
				'label_on'    => esc_html__( 'Show', 'wpzoom-recipe-card' ),
				'label_off'   => esc_html__( 'Hide', 'wpzoom-recipe-card' ),
				'default'     => 'yes',
			)
		);
		$this->add_control(
			'show_pintereset',
			array(
				'label'       => esc_html__( 'Pinterest Button', 'wpzoom-recipe-card' ),
				'description' => esc_html__( 'Display Pinterest Button', 'wpzoom-recipe-card' ),
				'type'        => Controls_Manager::SWITCHER,
				'label_on'    => esc_html__( 'Show', 'wpzoom-recipe-cards' ),
				'label_off'   => esc_html__( 'Hide', 'wpzoom-recipe-card' ),
				'default'     => 'yes',
			)
		);

		$this->add_control(
			'header_align',
			array(
				'label'   => esc_html__( 'Header Content Align', 'wpzoom-recipe-card' ),
				'type'    => Controls_Manager::CHOOSE,
				'options' => array(
					'left'   => array(
						'title' => esc_html__( 'Left', 'wpzoom-recipe-card' ),
						'icon'  => 'eicon-text-align-left',
					),
					'center' => array(
						'title' => esc_html__( 'Center', 'wpzoom-recipe-card' ),
						'icon'  => 'eicon-text-align-center',
					),
					'right'  => array(
						'title' => esc_html__( 'Right', 'wpzoom-recipe-card' ),
						'icon'  => 'eicon-text-align-right',
					),
				),
				'default' => 'left',
			)
		);
		$this->add_control(
			'show_author',
			array(
				'label'       => esc_html__( 'Author', 'wpzoom-recipe-card' ),
				'description' => esc_html__( 'Display Author', 'wpzoom-recipe-card' ),
				'type'        => Controls_Manager::SWITCHER,
				'label_on'    => esc_html__( 'Show', 'wpzoom-recipe-cards' ),
				'label_off'   => esc_html__( 'Hide', 'wpzoom-recipe-card' ),
			)
		);

		$this->add_control(
			'custom_author',
			array(
				'label'       => esc_html__( 'Custom author name', 'wpzoom-recipe-card' ),
				'description' => esc_html__( 'Default: Post author name', 'wpzoom-recipe-card' ),
				'type'        => Controls_Manager::TEXT,
				'dynamic'     => array(
					'active' => true,
				),
				'placeholder' => esc_html__( 'Custom author name', 'wpzoom-recipe-card' ),
				'condition'   => array(
					'show_author' => 'yes',
				),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'_section_recipe_card_seo',
			array(
				'label' => esc_html__( 'Recipe Schema Markup', 'wpzoom-recipe-card' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'show_course',
			array(
				'label'     => esc_html__( 'Course (required)', 'wpzoom-recipe-card' ),
				'type'      => Controls_Manager::SWITCHER,
				'label_on'  => esc_html__( 'Show', 'wpzoom-recipe-cards' ),
				'label_off' => esc_html__( 'Hide', 'wpzoom-recipe-card' ),
			)
		);
		$this->add_control(
			'recipe_course',
			array(
				'label'       => esc_html__( 'Course', 'wpzoom-recipe-card' ),
				'description' => esc_html__( 'Separate with commas or the Enter key.', 'wpzoom-recipe-card' ),
				'type'        => 'wpzoom_tagfield',
				'label_block' => true,
				'placeholder' => esc_html__( 'Course', 'wpzoom-recipe-card' ),
				'condition'   => array(
					'show_course' => 'yes',
				),
			)
		);

		$this->add_control(
			'show_cuisine',
			array(
				'label'     => esc_html__( 'Cuisine (required)', 'wpzoom-recipe-card' ),
				'type'      => Controls_Manager::SWITCHER,
				'label_on'  => esc_html__( 'Show', 'wpzoom-recipe-cards' ),
				'label_off' => esc_html__( 'Hide', 'wpzoom-recipe-card' ),
			)
		);
		$this->add_control(
			'recipe_cuisine',
			array(
				'label'       => esc_html__( 'Cuisine', 'wpzoom-recipe-card' ),
				'description' => esc_html__( 'Separate with commas or the Enter key.', 'wpzoom-recipe-card' ),
				'type'        => 'wpzoom_tagfield',
				'label_block' => true,
				'placeholder' => esc_html__( 'Cuisine', 'wpzoom-recipe-card' ),
				'condition'   => array(
					'show_cuisine' => 'yes',
				),
			)
		);

		$this->add_control(
			'show_difficulty',
			array(
				'label'     => esc_html__( 'Display Difficulty', 'wpzoom-recipe-card' ),
				'type'      => Controls_Manager::SWITCHER,
				'label_on'  => esc_html__( 'Show', 'wpzoom-recipe-cards' ),
				'label_off' => esc_html__( 'Hide', 'wpzoom-recipe-card' ),
			)
		);
		$this->add_control(
			'recipe_difficulty',
			array(
				'label'       => esc_html__( 'Add difficulty level', 'wpzoom-recipe-card' ),
				'description' => esc_html__( 'Separate with commas or the Enter key.', 'wpzoom-recipe-card' ),
				'type'        => 'wpzoom_tagfield',
				'label_block' => true,
				'condition'   => array(
					'show_difficulty' => 'yes',
				),
			)
		);

		$this->add_control(
			'recipe_keywords',
			array(
				'label'       => esc_html__( 'Keywords (recommended)', 'wpzoom-recipe-card' ),
				'description' => esc_html__( 'Separate with commas or the Enter key.', 'wpzoom-recipe-card' ),
				'type'        => 'wpzoom_tagfield',
				'label_block' => true,
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'_section_recipe_card_details',
			array(
				'label' => esc_html__( 'Additional Details', 'wpzoom-recipe-card' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$details_repeater = new Repeater();

		$details_repeater->add_control(
			'show_detail_item',
			array(
				'label'     => esc_html__( 'Display Detail Item', 'wpzoom-recipe-card' ),
				'type'      => Controls_Manager::SWITCHER,
				'label_on'  => esc_html__( 'Show', 'wpzoom-recipe-cards' ),
				'label_off' => esc_html__( 'Hide', 'wpzoom-recipe-card' ),
			)
		);

		$details_repeater->add_control(
			'detail_item_label',
			array(
				'label'       => esc_html__( 'Label', 'wpzoom-recipe-card' ),
				'type'        => Controls_Manager::TEXT,
				'label_block' => true,
			)
		);

		$details_repeater->add_control(
			'detail_item_icon',
			array(
				'label'            => esc_html__( 'Icon', 'wpzoom-recipe-card' ),
				'type'             => Controls_Manager::ICONS,
				'fa4compatibility' => 'icon',
				'default'          => array(
					'value'   => 'fas fa-check',
					'library' => 'fa-solid',
				),
				'recommended'      => array(
					'fa-regular' => array(
						'check-square',
						'window-close',
					),
					'fa-solid'   => array(
						'check',
					),
				),
			)
		);

		$details_repeater->add_control(
			'detail_item_value',
			array(
				'label'       => esc_html__( 'Value', 'wpzoom-recipe-card' ),
				'type'        => Controls_Manager::TEXT,
				'label_block' => true,
			)
		);

		$details_repeater->add_control(
			'detail_item_unit',
			array(
				'label'       => esc_html__( 'Unit', 'wpzoom-recipe-card' ),
				'type'        => Controls_Manager::TEXT,
				'label_block' => true,
			)
		);

		$this->add_control(
			'recipe_details_list',
			array(
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $details_repeater->get_controls(),
				'default'     => array(
					array(
						'show_detail_item'  => 'yes',
						'detail_item_label' => esc_html__( 'Servings', 'wpzoom-recipe-card' ),
						'detail_item_icon'  => array(
							'value'   => 'oldicon-food',
							'library' => 'oldicon',
						),
						'detail_item_value' => '4',
						'detail_item_unit'  => 'servings',
					),
					array(
						'show_detail_item'  => 'yes',
						'detail_item_label' => esc_html__( 'Prep time', 'wpzoom-recipe-card' ),
						'detail_item_icon'  => array(
							'value'   => 'oldicon-clock',
							'library' => 'oldicon',
						),
						'detail_item_value' => '30',
						'detail_item_unit'  => 'minutes',
					),
					array(
						'show_detail_item'  => 'yes',
						'detail_item_label' => esc_html__( 'Cooking time', 'wpzoom-recipe-card' ),
						'detail_item_icon'  => array(
							'value'   => 'foodicons-cooking-food-in-a-hot-casserole',
							'library' => 'foodicons',
						),
						'detail_item_value' => '40',
						'detail_item_unit'  => 'minutes',

					),
					array(
						'show_detail_item'  => 'yes',
						'detail_item_label' => esc_html__( 'Calories', 'wpzoom-recipe-card' ),
						'detail_item_icon'  => array(
							'value'   => 'foodicons-fire-flames',
							'library' => 'foodicons',
						),
						'detail_item_value' => '300',
						'detail_item_unit'  => 'kcal',
					),
					array(
						'show_detail_item'  => '',
						'detail_item_label' => esc_html__( 'Resting Time', 'wpzoom-recipe-card' ),
						'detail_item_icon'  => array(
							'value'   => 'far fa-clock',
							'library' => 'fa-regular',
						),
					),
					array(
						'show_detail_item'  => '',
						'detail_item_label' => esc_html__( 'Baking Time', 'wpzoom-recipe-card' ),
						'detail_item_icon'  => array(
							'value'   => 'oldicon-chef-cooking',
							'library' => 'oldicon',
						),
					),
					array(
						'show_detail_item'  => '',
						'detail_item_label' => esc_html__( 'Serving Size', 'wpzoom-recipe-card' ),
						'detail_item_icon'  => array(
							'value'   => 'oldicon-food-1',
							'library' => 'oldicon',
						),
					),
					array(
						'show_detail_item'  => '',
						'detail_item_label' => esc_html__( 'Net Carbs', 'wpzoom-recipe-card' ),
						'detail_item_icon'  => array(
							'value'   => 'fas fa-sort-amount-down',
							'library' => 'fa-solid',
						),
					),
				),
				'title_field' => '{{{ elementor.helpers.renderIcon( this, detail_item_icon, {}, "i", "panel" ) || \'<i class="{{ icon }}" aria-hidden="true"></i>\' }}} {{{ detail_item_label }}}',
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'_section_recipe_card_ingredients',
			array(
				'label' => esc_html__( 'Ingredients', 'wpzoom-recipe-card' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'ingredients_title',
			array(
				'label'       => esc_html__( 'Title', 'wpzoom-recipe-card' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => esc_html__( 'Ingredients', 'wpzoom-recipe-card' ),
				'label_block' => true,
			)
		);

		$ingredient_repeater = new Repeater();

		$ingredient_repeater->add_control(
			'ingredient_item_amount',
			array(
				'label' => esc_html__( 'Amount', 'wpzoom-recipe-card' ),
				'type'  => Controls_Manager::TEXT,
			)
		);
		$ingredient_repeater->add_control(
			'ingredient_item_unit',
			array(
				'label' => esc_html__( 'Unit', 'wpzoom-recipe-card' ),
				'type'  => Controls_Manager::TEXT,
			)
		);

		$ingredient_repeater->add_control(
			'ingredient_item_label',
			array(
				'label'       => esc_html__( 'Ingredient Name', 'wpzoom-recipe-card' ),
				'type'        => Controls_Manager::WYSIWYG,
				'label_block' => true,
			)
		);

		$this->add_control(
			'recipe_ingredients_list',
			array(
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $ingredient_repeater->get_controls(),
				'default'     => array(
					array(
						'ingredient_item_amount' => '',
						'ingredient_item_unit'   => '',
						'ingredient_item_label'  => esc_html__( '1 Ingredient', 'wpzoom-recipe-card' ),
					),
					array(
						'ingredient_item_amount' => '',
						'ingredient_item_unit'   => '',
						'ingredient_item_label'  => esc_html__( '2 Ingredient', 'wpzoom-recipe-card' ),
					),
					array(
						'ingredient_item_amount' => '',
						'ingredient_item_unit'   => '',
						'ingredient_item_label'  => esc_html__( '3 Ingredient', 'wpzoom-recipe-card' ),
					),
					array(
						'ingredient_item_amount' => '',
						'ingredient_item_unit'   => '',
						'ingredient_item_label'  => esc_html__( '4 Ingredient', 'wpzoom-recipe-card' ),
					),
				),
				'title_field' => '{{{ ingredient_item_label }}}',
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'_section_recipe_card_directions',
			array(
				'label' => esc_html__( 'Directions', 'wpzoom-recipe-card' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'directions_title',
			array(
				'label'       => esc_html__( 'Title', 'wpzoom-recipe-card' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => esc_html__( 'Directions', 'wpzoom-recipe-card' ),
				'label_block' => true,
			)
		);

		$directions_repeater = new Repeater();

		$directions_repeater->add_control(
			'directions_step_text',
			array(
				'label'       => esc_html__( 'Step description', 'wpzoom-recipe-card' ),
				'type'        => Controls_Manager::WYSIWYG,
				'label_block' => true,
			)
		);

		$directions_repeater->start_controls_tabs( '_tab_directions_step_image_gallery' );
		$directions_repeater->start_controls_tab(
			'_tab_direction_step_image',
			array(
				'label' => esc_html__( 'Image', 'wpzoom-recipe-card' ),
			)
		);

		$directions_repeater->add_control(
			'image',
			array(
				'label'   => esc_html__( 'Photo', 'wpzoom-recipe-card' ),
				'type'    => Controls_Manager::MEDIA,
				'default' => array(
					'url' => Utils::get_placeholder_image_src(),
				),
				'dynamic' => array(
					'active' => true,
				),
			)
		);

		$directions_repeater->end_controls_tab();
		$directions_repeater->start_controls_tab(
			'_tab_direction_step_image_gallery',
			array(
				'label' => esc_html__( 'Gallery', 'wpzoom-recipe-card' ),
			)
		);

		$directions_repeater->add_control(
			'wp_gallery',
			array(
				'label'      => __( 'Add Images', 'elementor' ),
				'type'       => Controls_Manager::GALLERY,
				'show_label' => false,
				'dynamic'    => array(
					'active' => true,
				),
			)
		);

		$directions_repeater->end_controls_tab();
		$directions_repeater->end_controls_tabs();

		$this->add_control(
			'recipe_directions_list',
			array(
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $directions_repeater->get_controls(),
				'default'     => array(
					array(
						'directions_step_text' => esc_html__( 'Directions Step', 'wpzoom-recipe-card' ),
					),
					array(
						'directions_step_text' => esc_html__( 'Directions Step', 'wpzoom-recipe-card' ),
					),
					array(
						'directions_step_text' => esc_html__( 'Directions Step', 'wpzoom-recipe-card' ),
					),
					array(
						'directions_step_text' => esc_html__( 'Directions Step', 'wpzoom-recipe-card' ),
					),
				),
				'title_field' => '{{{ directions_step_text }}}',
			)
		);

		$this->end_controls_section();

		// Recipe Card Video Options
		$this->start_controls_section(
			'_section_recipe_card_video',
			array(
				'label' => esc_html__( 'Video', 'wpzoom-recipe-card' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'video_type',
			array(
				'label'              => esc_html__( 'Source', 'wpzoom-recipe-card' ),
				'type'               => Controls_Manager::SELECT,
				'default'            => 'embed',
				'options'            => array(
					'embed'  => esc_html__( 'Embed', 'wpzoom-recipe-card' ),
					'hosted' => esc_html__( 'Self Hosted', 'wpzoom-recipe-card' ),
				),
				'frontend_available' => true,
			)
		);

		$this->add_control(
			'video_url',
			array(
				'label'              => esc_html__( 'Link', 'wpzoom-recipe-card' ),
				'type'               => Controls_Manager::TEXT,
				'dynamic'            => array(
					'active'     => true,
					'categories' => array(
						TagsModule::POST_META_CATEGORY,
						TagsModule::URL_CATEGORY,
					),
				),
				'placeholder'        => esc_html__( 'Enter your URL', 'wpzoom-recipe-card' ),
				'default'            => 'https://www.youtube.com/watch?v=TehuLXQXNi8',
				'label_block'        => true,
				'condition'          => array(
					'video_type' => 'embed',
				),
				'frontend_available' => true,
			)
		);

		$this->add_control(
			'hosted_url',
			array(
				'label'      => esc_html__( 'Choose File', 'wpzoom-recipe-card' ),
				'type'       => Controls_Manager::MEDIA,
				'dynamic'    => array(
					'active'     => true,
					'categories' => array(
						TagsModule::MEDIA_CATEGORY,
					),
				),
				'media_type' => 'video',
				'condition'  => array(
					'video_type' => 'hosted',
				),
			)
		);

		$this->add_control(
			'video_title',
			array(
				'label'              => esc_html__( 'Video Title', 'wpzoom-recipe-card' ),
				'type'               => Controls_Manager::TEXT,
				'dynamic'            => array(
					'active' => true,
				),
				'default'            => '',
				'label_block'        => true,
				'frontend_available' => true,
			)
		);

		$this->add_control(
			'video_description',
			array(
				'label'              => esc_html__( 'Video Description', 'wpzoom-recipe-card' ),
				'type'               => Controls_Manager::TEXTAREA,
				'dynamic'            => array(
					'active' => true,
				),
				'default'            => '',
				'frontend_available' => true,
			)
		);

		$this->add_control(
			'video_options',
			array(
				'label'     => esc_html__( 'Video Options', 'wpzoom-recipe-card' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => array(
					'video_type' => 'hosted',
				),
			)
		);

		$this->add_control(
			'autoplay',
			array(
				'label'              => esc_html__( 'Autoplay', 'wpzoom-recipe-card' ),
				'type'               => Controls_Manager::SWITCHER,
				'frontend_available' => true,
				'condition'          => array(
					'video_type' => 'hosted',
				),
			)
		);

		$this->add_control(
			'mute',
			array(
				'label'              => esc_html__( 'Mute', 'wpzoom-recipe-card' ),
				'type'               => Controls_Manager::SWITCHER,
				'frontend_available' => true,
				'condition'          => array(
					'video_type' => 'hosted',
				),
			)
		);

		$this->add_control(
			'loop',
			array(
				'label'              => esc_html__( 'Loop', 'wpzoom-recipe-card' ),
				'type'               => Controls_Manager::SWITCHER,
				'condition'          => array(
					'video_type' => 'hosted',
				),
				'frontend_available' => true,
			)
		);

		$this->add_control(
			'controls',
			array(
				'label'              => esc_html__( 'Player Controls', 'wpzoom-recipe-card' ),
				'type'               => Controls_Manager::SWITCHER,
				'label_off'          => esc_html__( 'Hide', 'wpzoom-recipe-card' ),
				'label_on'           => esc_html__( 'Show', 'wpzoom-recipe-card' ),
				'condition'          => array(
					'video_type' => 'hosted',
				),
				'default'            => 'yes',
				'frontend_available' => true,
			)
		);

		$this->add_control(
			'poster',
			array(
				'label'     => esc_html__( 'Poster', 'wpzoom-recipe-card' ),
				'type'      => Controls_Manager::MEDIA,
				'dynamic'   => array(
					'active' => true,
				),
				'condition' => array(
					'video_type' => 'hosted',
				),
			)
		);

		$this->add_control(
			'view',
			array(
				'label'   => esc_html__( 'View', 'wpzoom-recipe-card' ),
				'type'    => Controls_Manager::HIDDEN,
				'default' => 'youtube',
			)
		);

		$this->end_controls_section();

		// Recipe Card Notes Options
		$this->start_controls_section(
			'_section_recipe_card_note',
			array(
				'label' => esc_html__( 'Notes', 'wpzoom-recipe-card' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'notes_title',
			array(
				'label'       => esc_html__( 'Title', 'wpzoom-recipe-card' ),
				'type'        => Controls_Manager::TEXT,
				'label_block' => true,
			)
		);

		$note_repeater = new Repeater();

		$note_repeater->add_control(
			'note_text',
			array(
				'label'       => esc_html__( 'Note text', 'wpzoom-recipe-card' ),
				'type'        => Controls_Manager::TEXTAREA,
				'label_block' => true,
			)
		);

		$this->add_control(
			'notes_list',
			array(
				'type'   => Controls_Manager::REPEATER,
				'fields' => $note_repeater->get_controls(),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Register Style Controls.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return void
	 */
	protected function register_style_controls() {
		$this->start_controls_section(
			'_section_style_recipe_card',
			array(
				'label' => esc_html__( 'Title', 'wpzoom-recipe-card' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		// Title typography.
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'recipe_card_title_style_typography',
				'scheme'   => Typography::TYPOGRAPHY_1,
				'selector' => '{{WRAPPER}} .wp-block-wpzoom-recipe-card-block-recipe-card .recipe-card-title',
			)
		);

		// Title color.
		$this->add_control(
			'recipe_card_title_style_color',
			array(
				'type'      => Controls_Manager::COLOR,
				'label'     => esc_html__( 'Color', 'wpzoom-recipe-card' ),
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .wp-block-wpzoom-recipe-card-block-recipe-card .recipe-card-title' => 'color: {{VALUE}};',
				),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'_section_style_recipe_card_details',
			array(
				'label' => esc_html__( 'Details', 'wpzoom-recipe-card' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		// Title typography.
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'recipe_card_detail_label_style_typography',
				'scheme'   => Typography::TYPOGRAPHY_1,
				'selector' => '{{WRAPPER}} .wp-block-wpzoom-recipe-card-block-recipe-card .detail-item-label',
			)
		);

		// Title color.
		$this->add_control(
			'recipe_card_detail_label_style_color',
			array(
				'type'      => Controls_Manager::COLOR,
				'label'     => esc_html__( 'Color', 'wpzoom-recipe-card' ),
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .wp-block-wpzoom-recipe-card-block-recipe-card .recipe-card-title' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'recipe_card_detail_icon_heading',
			array(
				'label'     => esc_html__( 'Icon', 'wpzoom-recipe-card' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_responsive_control(
			'lrecipe_card_detail_icon_icon_size',
			array(
				'label'      => esc_html__( 'Icon Size', 'wpzoom-recipe-card' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'selectors'  => array(
					'{{WRAPPER}} .recipe-card-details .detail-item-icon:before,{{WRAPPER}} .recipe-card-details .detail-item-icon:after' => 'font-size: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_control(
			'recipe_card_detail_icon_color',
			array(
				'label'     => esc_html__( 'Icon Color', 'wpzoom-recipe-card' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .recipe-card-details .detail-item-icon' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_responsive_control(
			'recipe_card_detail_icon_spacing',
			array(
				'label'      => esc_html__( 'Bottom Spacing', 'wpzoom-recipe-card' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'selectors'  => array(
					'{{WRAPPER}} .recipe-card-details .detail-item-icon' => 'margin-bottom: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_control(
			'recipe_card_detail_value_heading',
			array(
				'label'     => esc_html__( 'Value', 'wpzoom-recipe-card' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		// Value typography.
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'recipe_card_detail_value_typography',
				'scheme'   => Typography::TYPOGRAPHY_3,
				'selector' => '{{WRAPPER}} .recipe-card-details .detail-item-value',
			)
		);

		// Value color.
		$this->add_control(
			'recipe_card_detail_value_color',
			array(
				'type'      => Controls_Manager::COLOR,
				'label'     => esc_html__( 'Color', 'wpzoom-recipe-card' ),
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .recipe-card-details p.detail-item-value' => 'color: {{VALUE}} !important;',
				),
			)
		);

		$this->add_responsive_control(
			'recipe_card_detail_value_spacing',
			array(
				'label'      => esc_html__( 'Side Spacing', 'wpzoom-recipe-card' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'selectors'  => array(
					'{{WRAPPER}} .recipe-card-details .detail-item-value' => 'margin-right: {{SIZE}}{{UNIT}} !important;',
				),
			)
		);

		$this->add_control(
			'recipe_card_detail_unit_heading',
			array(
				'label'     => esc_html__( 'Unit', 'wpzoom-recipe-card' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		// Value typography.
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'recipe_card_detail_unit_typography',
				'scheme'   => Typography::TYPOGRAPHY_3,
				'selector' => '{{WRAPPER}} .recipe-card-details .detail-item-unit',
			)
		);

		// Value color.
		$this->add_control(
			'recipe_card_detail_unit_color',
			array(
				'type'      => Controls_Manager::COLOR,
				'label'     => esc_html__( 'Color', 'wpzoom-recipe-card' ),
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .recipe-card-details .detail-item-unit' => 'color: {{VALUE}} !important;',
				),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'_section_style_recipe_card_summary',
			array(
				'label' => esc_html__( 'Summary', 'wpzoom-recipe-card' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		// Summary typography.
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'recipe_card_summary_typography',
				'scheme'   => Typography::TYPOGRAPHY_3,
				'selector' => '{{WRAPPER}} .wp-block-wpzoom-recipe-card-block-recipe-card .recipe-card-summary',
			)
		);

		// Summary color.
		$this->add_control(
			'recipe_card_summary_color',
			array(
				'type'      => Controls_Manager::COLOR,
				'label'     => esc_html__( 'Color', 'wpzoom-recipe-card' ),
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .wp-block-wpzoom-recipe-card-block-recipe-card .recipe-card-summary' => 'color: {{VALUE}};',
				),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'_section_style_recipe_card_ingredients',
			array(
				'label' => esc_html__( 'Ingredients', 'wpzoom-recipe-card' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'recipe_card_ingredients_title_heading',
			array(
				'label'     => esc_html__( 'Title', 'wpzoom-recipe-card' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		// Ingredients title typography.
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'recipe_card_ingredients_title_typography',
				'selector' => '{{WRAPPER}} .recipe-card-ingredients .ingredients-title',
			)
		);

		// Ingredients title color.
		$this->add_control(
			'recipe_card_ingredient_title_color',
			array(
				'type'      => Controls_Manager::COLOR,
				'label'     => esc_html__( 'Color', 'wpzoom-recipe-card' ),
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .recipe-card-ingredients .ingredients-title' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'recipe_card_ingredients_item_heading',
			array(
				'label'     => esc_html__( 'Item', 'wpzoom-recipe-card' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		// Ingredients item typography.
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'recipe_card_ingredients_item_typography',
				'selector' => '{{WRAPPER}} .recipe-card-ingredients .wpzoom-rcb-ingredient-name',
			)
		);

		// Ingredients item color.
		$this->add_control(
			'recipe_card_ingredient_item_color',
			array(
				'type'      => Controls_Manager::COLOR,
				'label'     => esc_html__( 'Color', 'wpzoom-recipe-card' ),
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .recipe-card-ingredients .wpzoom-rcb-ingredient-name' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_responsive_control(
			'recipe_card_ingredient_item_margin',
			array(
				'label'      => esc_html__( 'Margin', 'wpzoom-recipe-card' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .recipe-card-ingredients .ingredient-item' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}',
				),
			)
		);

		$this->add_responsive_control(
			'recipe_card_ingredient_item_padding',
			array(
				'label'      => esc_html__( 'Padding', 'wpzoom-recipe-card' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .recipe-card-ingredients .ingredient-item' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}',
				),
			)
		);

		$this->end_controls_section();

		// Directions
		$this->start_controls_section(
			'_section_style_recipe_card_directions',
			array(
				'label' => esc_html__( 'Directions', 'wpzoom-recipe-card' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'recipe_card_directions_title_heading',
			array(
				'label'     => esc_html__( 'Title', 'wpzoom-recipe-card' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		// Directions title typography.
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'recipe_card_directions_title_typography',
				'selector' => '{{WRAPPER}} .recipe-card-directions .directions-title',
			)
		);

		// Directions title color.
		$this->add_control(
			'recipe_card_direction_title_color',
			array(
				'type'      => Controls_Manager::COLOR,
				'label'     => esc_html__( 'Color', 'wpzoom-recipe-card' ),
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .recipe-card-directions .directions-title' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'recipe_card_directions_item_heading',
			array(
				'label'     => esc_html__( 'Step', 'wpzoom-recipe-card' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		// Directions Step typography.
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'recipe_card_directions_item_typography',
				'selector' => '{{WRAPPER}} .directions-list .direction-step',
			)
		);

		// Directions Step color.
		$this->add_control(
			'recipe_card_directions_step_color',
			array(
				'type'      => Controls_Manager::COLOR,
				'label'     => esc_html__( 'Color', 'wpzoom-recipe-card' ),
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .directions-list .direction-step' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_responsive_control(
			'recipe_card_directions_step_margin',
			array(
				'label'      => esc_html__( 'Margin', 'wpzoom-recipe-card' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .directions-list .direction-step' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}',
				),
			)
		);

		$this->add_responsive_control(
			'recipe_card_directions_step_padding',
			array(
				'label'      => esc_html__( 'Padding', 'wpzoom-recipe-card' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .directions-list .direction-step' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}',
				),
			)
		);

		$this->end_controls_section();

		// Note
		$this->start_controls_section(
			'_section_style_recipe_card_note',
			array(
				'label' => esc_html__( 'Notes', 'wpzoom-recipe-card' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'recipe_card_note_title_heading',
			array(
				'label'     => esc_html__( 'Title', 'wpzoom-recipe-card' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		// Note title typography.
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'recipe_card_note_title_typography',
				'selector' => '{{WRAPPER}} .recipe-card-notes .notes-title',
			)
		);

		// Note title color.
		$this->add_control(
			'recipe_card_note_title_color',
			array(
				'type'      => Controls_Manager::COLOR,
				'label'     => esc_html__( 'Color', 'wpzoom-recipe-card' ),
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .recipe-card-notes .notes-title' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'recipe_card_note_item_heading',
			array(
				'label'     => esc_html__( 'Note', 'wpzoom-recipe-card' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		// Note typography.
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'recipe_card_note_item_typography',
				'selector' => '{{WRAPPER}} .recipe-card-notes-list li',
			)
		);

		// Note color.
		$this->add_control(
			'recipe_card_note_item_color',
			array(
				'type'      => Controls_Manager::COLOR,
				'label'     => esc_html__( 'Color', 'wpzoom-recipe-card' ),
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .recipe-card-notes-list li' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_responsive_control(
			'recipe_card_note_item_margin',
			array(
				'label'      => esc_html__( 'Margin', 'wpzoom-recipe-card' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .recipe-card-notes-list li' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}',
				),
			)
		);

		$this->add_responsive_control(
			'recipe_card_note_item_padding',
			array(
				'label'      => esc_html__( 'Padding', 'wpzoom-recipe-card' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .recipe-card-notes-list li' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}',
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Get Recipe Terms
	 *
	 * Renders the recipe terms on frontend.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return void
	 */
	public function get_recipe_terms( $terms, $type ) {
		if ( empty( $terms ) ) {
			return;
		}
		$className = $label = $termsValues = '';
		if ( ! is_array( $terms ) ) {
			$terms = explode( ',', $terms );
		}
		$termsValues = implode( ', ', $terms );

		if ( 'courses' === $type ) {
			$className = 'recipe-card-course';
			$label     = esc_html__( 'Course:', 'wpzoom-recipe-card' );
		} elseif ( 'cuisines' === $type ) {
			$className = 'recipe-card-cuisine';
			$label     = esc_html__( 'Cuisine:', 'wpzoom-recipe-card' );
		} elseif ( 'difficulties' === $type ) {
			$className = 'recipe-card-difficulty';
			$label     = esc_html__( 'Difficulty:', 'wpzoom-recipe-card' );
		}
		$terms_output = sprintf( '<span class="%s">%s <mark>%s</mark></span>', $className, $label, $termsValues );

		return $terms_output;
	}


	/**
	 * Render the Widget.
	 *
	 * Renders the widget on the frontend.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return void
	 */
	protected function render() {
		self::$helpers = new \WPZOOM_Helpers();

		$settings = $this->get_settings_for_display();

		$id = 'wpzoom-recipe-card';

		$this->add_render_attribute( '_wrapper_recipe_card', 'id', $id );
		$this->add_render_attribute( '_wrapper_recipe_card', 'class', 'wp-block-wpzoom-recipe-card-block-recipe-card' );
		$this->add_render_attribute( '_wrapper_recipe_card', 'class', 'is-style-default' );
		$this->add_render_attribute( '_wrapper_recipe_card', 'class', 'header-content-align-' . $settings['header_align'] );

		$this->add_render_attribute( 'title', 'class', 'recipe-card-title' );

		$html = '<div ' . $this->get_render_attribute_string( '_wrapper_recipe_card' ) . '>';

		if ( ! empty( $settings['image']['url'] ) ) {
			$html .= '<div class="recipe-card-image">';

			$this->add_render_attribute( 'image', 'src', $settings['image']['url'] );
			// $this->add_render_attribute( 'image', 'class', 'wpzoom-recipe-card-image' );
			$this->add_render_attribute( 'image', 'alt', Control_Media::get_image_alt( $settings['image'] ) );
			$this->add_render_attribute( 'image', 'title', Control_Media::get_image_title( $settings['image'] ) );

			$attachment_size = array(
				// Defaults sizes
				0           => null, // Width.
				1           => null, // Height.

				'bfi_thumb' => true,
				'crop'      => true,
			);

			if ( ! empty( $settings['thumbnail_size'] ) ) {
				if ( 'custom' == $settings['thumbnail_size'] ) {
					if ( ! empty( $settings['thumbnail_custom_dimension']['width'] ) ) {
						$attachment_size[0] = (int) $settings['thumbnail_custom_dimension']['width'];
					}
					if ( ! empty( $settings['thumbnail_custom_dimension']['height'] ) ) {
						$attachment_size[1] = (int) $settings['thumbnail_custom_dimension']['height'];
					}
					$imageSize = $attachment_size;
				} else {
					$imageSize = $settings['thumbnail_size'];
				}
			} else {
				$imageSize = 'thumbnail';
			}

			$alt = ! empty( $settings['title'] ) ? esc_attr( $settings['title'] ) : Control_Media::get_image_alt( $settings['image'] );

			$image_html = wp_get_attachment_image(
				$settings['image']['id'],
				$imageSize,
				false,
				array(
					'alt'   => $alt,
					'id'    => $settings['image']['id'],
					'class' => 'wpzoom-recipe-card-image',
				)
			);

			$html .= '<figure>';
			$html .= $image_html;
			$html .= '<figcaption>';
			if ( 'yes' === $settings['show_pintereset'] ) {
				$html .= \WPZOOM_Recipe_Card_Block::get_pinterest_button( array( 'url' => $settings['image']['url'] ), get_the_permalink(), wp_kses_post( $settings['recipe_card_summary'] ) );
			}
			if ( 'yes' === $settings['show_print'] ) {
				$html .= $this->get_print_button();
			}
			$html .= '</figcaption>';
			$html .= '</figure>';
			$html .= '</div><!-- /.recipe-card-image -->';
		}

		// Recipe Card Heading
		$html .= '<div class="recipe-card-heading">';

		if ( ! empty( $settings['title'] ) ) {
			$this->add_inline_editing_attributes( 'title' );
			$html .= '<h2 ' . $this->get_render_attribute_string( 'title' ) . '>' . esc_html( $settings['title'] ) . '</h2>';
		} else {
			$html .= '<h2 class="recipe-card-title">' . esc_html( get_the_title() ) . '</h2>';
		}

		if ( 'yes' === $settings['show_author'] ) {
			$recipe_author = ! empty( $settings['custom_author'] ) ? esc_html( $settings['custom_author'] ) : get_the_author_meta( 'display_name' );
			$html         .= '<span class="recipe-card-author">' . esc_html__( 'Recipe by', 'wpzoom-recipe-card' ) . ' ' . $recipe_author . '</span>';
		}

		if ( ! empty( $settings['recipe_course'] ) && $settings['show_course'] ) {
			$html .= $this->get_recipe_terms( $settings['recipe_course'], 'courses' );
		}
		if ( ! empty( $settings['recipe_cuisine'] ) && $settings['show_cuisine'] ) {
			$html .= $this->get_recipe_terms( $settings['recipe_cuisine'], 'cuisines' );
		}
		if ( ! empty( $settings['recipe_difficulty'] ) && $settings['show_difficulty'] ) {
			$html .= $this->get_recipe_terms( $settings['recipe_difficulty'], 'difficulties' );
		}

		$html .= '</div><!-- /.recipe-card-heading -->';

		// Recipe Card Details
		if ( is_array( $settings['recipe_details_list'] ) ) :
			$this->add_render_attribute( 'recipe_details_list', 'class', 'recipe-card-details' );

			$html     .= '<div ' . $this->get_render_attribute_string( 'recipe_details_list' ) . '>';
				$html .= '<div class="details-items">';
			foreach ( $settings['recipe_details_list'] as $index => $detail_item ) :
				if ( 'yes' == $detail_item['show_detail_item'] ) {
					$html .= '<div class="detail-item detail-item-' . $index . '">';

					$label_key = $this->get_repeater_setting_key( 'detail_item_label', 'recipe_details_list', $index );

					$this->add_render_attribute( $label_key, 'class', 'detail-item-label' );
					// $this->add_inline_editing_attributes( $label_key, 'basic' );

					if ( ! empty( $detail_item['detail_item_icon']['value'] ) ) {
						$html .= '<span class="detail-item-icon  ' . $detail_item['detail_item_icon']['library'] . ' ' . $detail_item['detail_item_icon']['value'] . '"></span>';
					}
					if ( ! empty( $detail_item['detail_item_label'] ) ) {
						$html .= '<span ' . $this->get_render_attribute_string( $label_key ) . '>' . esc_html( $detail_item['detail_item_label'] ) . '</span>';
					}

					if ( ! empty( $detail_item['detail_item_value'] ) ) {
						$html .= '<p class="detail-item-value">' . esc_html( $detail_item['detail_item_value'] ) . '</p>';
					}
					if ( ! empty( $detail_item['detail_item_unit'] ) ) {
						$html .= '<span class="detail-item-unit">' . esc_html( $detail_item['detail_item_unit'] ) . '</span>';
					}
					$html .= '</div>';
				}
				endforeach;

				$html .= '</div><!-- /.details-items -->';
			$html     .= '</div><!-- /.recipe-card-details -->';
		endif;

		// Recipe Card Summary
		if ( ! Utils::is_empty( $settings['recipe_card_summary'] ) ) {
			$this->add_render_attribute( 'recipe_card_summary', 'class', 'recipe-card-summary' );
			$this->add_inline_editing_attributes( 'recipe_card_summary' );
			$html .= sprintf(
				'<p %s>%s</p>',
				$this->get_render_attribute_string( 'recipe_card_summary' ),
				$settings['recipe_card_summary']
			);
		}

		// Recipe Card Ingridients
		if ( is_array( $settings['recipe_ingredients_list'] ) ) :
			$html .= '<div class="recipe-card-ingredients">';
			if ( ! empty( $settings['ingredients_title'] ) ) {
				$this->add_render_attribute( 'ingredients_title', 'class', 'ingredients-title' );
				$this->add_inline_editing_attributes( 'ingredients_title' );
				$html .= sprintf( '<h3 %s>%s</h3>', $this->get_render_attribute_string( 'ingredients_title' ), esc_html( $settings['ingredients_title'] ) );
			}
			$html .= '<ul class="ingredients-list layout-1-column">';
			foreach ( $settings['recipe_ingredients_list'] as $index => $item ) :
				$id        = self::$helpers->generateId( 'ingredient-item' );
				$html     .= '<li id="wpzoom-rcb-' . $id . '" class="ingredient-item"><span class="tick-circle"></span>';
					$html .= '<div class="ingredient-item-name is-strikethrough-active">';
				if ( ! empty( $item['ingredient_item_amount'] ) ) {
					$html .= '<span class="wpzoom-rcb-ingredient-amount">' . esc_html( $item['ingredient_item_amount'] ) . '</span> ';
				}
				if ( ! empty( $item['ingredient_item_unit'] ) ) {
					$html .= '<span class="wpzoom-rcb-ingredient-unit">' . esc_html( $item['ingredient_item_unit'] ) . '</span> ';
				}
				if ( ! empty( $item['ingredient_item_label'] ) ) {
					$html .= '<span class="wpzoom-rcb-ingredient-name">' . wp_kses_post( $item['ingredient_item_label'] ) . '</span>';
				}
					$html .= '</div>';
				$html     .= '</li>';
			endforeach;
			$html .= '</ul>';
			$html .= '</div><!-- /.recipe-card-ingredients -->';
		endif;

		// Recipe Card Directions
		if ( is_array( $settings['recipe_directions_list'] ) ) :
			$html .= '<div class="recipe-card-directions">';
			if ( ! empty( $settings['directions_title'] ) ) {
				$this->add_render_attribute( 'directions_title', 'class', 'directions-title' );
				$this->add_inline_editing_attributes( 'directions_title' );
				$html .= sprintf( '<h3 %s>%s</h3>', $this->get_render_attribute_string( 'directions_title' ), esc_html( $settings['directions_title'] ) );
			}
			$html .= '<ul class="directions-list">';
			foreach ( $settings['recipe_directions_list'] as $index => $item ) :
				$id    = self::$helpers->generateId( 'direction-step' );
				$html .= '<li id="wpzoom-rcb-' . $id . '" class="direction-step">';
				if ( ! empty( $item['directions_step_text'] ) ) {
					$html .= wp_kses_post( $item['directions_step_text'] );
				}
				if ( ! empty( $item['image']['url'] ) ) {
					$image_html = wp_get_attachment_image(
						$item['image']['id'],
						'medium_large',
						false,
						array(
							'alt'   => Control_Media::get_image_alt( $item['image'] ),
							'id'    => $item['image']['id'],
							'class' => 'direction-step-image',
						)
					);
					$html      .= $image_html;
				}

				if ( $item['wp_gallery'] ) {
					$html     .= '<div class="direction-step-gallery columns-2" data-grid-columns="2">';
						$html .= '<ul class="direction-step-gallery-grid" data-gallery-masonry-grid="true">';
					foreach ( $item['wp_gallery'] as $key => $image ) {
						$html .= '<li class="direction-step-gallery-item">';
						$html .= '<figure>';
						$html .= wp_get_attachment_image(
							$image['id'],
							'medium_large',
							false,
							array(
								'alt' => Control_Media::get_image_alt( $image ),
								'id'  => 'direction-step-gallery-image-' . $item['image']['id'],
							)
						);
						$html .= '</figure>';
						$html .= '</li>';
					};
						$html .= '</ul>';
					$html     .= '</div>';
				}
				$html .= '</li>';
			endforeach;
			$html .= '</ul>';
			$html .= '</div><!-- /.recipe-card-directions -->';
		endif;

		// Recipe Card Video
		$html .= $this->get_video_content();

		// Recipe Card Directions
		if ( is_array( $settings['notes_list'] ) ) :
			$html .= '<div class="recipe-card-notes">';
			if ( ! empty( $settings['notes_title'] ) ) {
				$this->add_render_attribute( 'notes_title', 'class', 'notes-title' );
				$this->add_inline_editing_attributes( 'notes_title' );
				$html .= sprintf( '<h3 %s>%s</h3>', $this->get_render_attribute_string( 'notes_title' ), esc_html( $settings['notes_title'] ) );
			}
			$html .= '<ul class="recipe-card-notes-list">';
			foreach ( $settings['notes_list'] as $index => $item ) :
				$note_key = $this->get_repeater_setting_key( 'note_text', 'notes_list', $index );
					// $this->add_inline_editing_attributes( $note_key );
					$this->add_render_attribute( $note_key, 'class', 'wpzoom-rc-note-text' );

				if ( ! empty( $item['note_text'] ) ) {
					$html .= '<li ' . $this->get_render_attribute_string( $note_key ) . '>';
					$html .= wp_kses_post( $item['note_text'] );
					$html .= '</li>';
				}
			endforeach;
			$html .= '</ul>';
			$html .= '</div><!-- /.recipe-card-notes -->';
		endif;

		$html .= '<script type="application/ld+json">' . wp_json_encode( $this->get_json_ld() ) . '</script>';
		$html .= '</div><!-- /.wp-block-wpzoom-recipe-card-block-recipe-card -->';

		echo apply_filters( 'wpzoom_recipe_card_output', $html );
	}

	public function get_video() {
		$settings   = $this->get_settings_for_display();
		$video_url  = isset( $settings['video_url'] ) ? esc_url( $settings['video_url'] ) : '';
		$hosted_url = isset( $settings['hosted_url']['url'] ) ? esc_url( $settings['hosted_url']['url'] ) : '';

		$output = '';

		if ( 'embed' === $settings['video_type'] ) {
			$output = wp_oembed_get( $video_url );
		} elseif ( 'hosted' === $settings['video_type'] ) {
			$video_params = $this->get_hosted_params() ? $this->get_hosted_params() : array();

			$output = sprintf(
				'<video %s src="%s"></video>',
				Utils::render_html_attributes( $video_params ),
				$hosted_url
			);
		}

		return $output;
	}

	public function get_video_content() {
		$settings = $this->get_settings_for_display();

		$output = $this->get_video();
		return sprintf( '<div class="recipe-card-video no-print"><h3 class="video-title">%s</h3>%s</div>', $settings['video_title'], $output );
	}

	/**
	 * @since 2.1.0
	 * @access private
	 */
	private function get_hosted_params() {
		$settings = $this->get_settings_for_display();

		$video_params = array();

		foreach ( array( 'autoplay', 'loop', 'controls' ) as $option_name ) {
			if ( $settings[ $option_name ] ) {
				$video_params[ $option_name ] = '';
			}
		}

		if ( $settings['mute'] ) {
			$video_params['muted'] = 'muted';
		}

		if ( $settings['poster']['url'] ) {
			$video_params['poster'] = $settings['poster']['url'];
		}

		return $video_params;
	}

	protected function get_json_ld() {
		$settings = $this->get_settings_for_display();

		$tag_list = wp_get_post_terms( self::$recipe->ID, 'post_tag', array( 'fields' => 'names' ) );
		$cat_list = wp_get_post_terms( self::$recipe->ID, 'category', array( 'fields' => 'names' ) );

		$rating_average = $rating_count = '';

		$json_ld = array(
			'@context'           => 'https://schema.org',
			'@type'              => 'Recipe',
			'name'               => ! empty( $settings['title'] ) ? $settings['title'] : self::$recipe->post_title,
			'image'              => '',
			'description'        => ! empty( $settings['recipe_card_summary'] ) ? $settings['recipe_card_summary'] : self::$recipe->post_excerpt,
			'keywords'           => $tag_list,
			'author'             => array(
				'@type' => 'Person',
				'name'  => get_the_author(),
			),
			'datePublished'      => get_the_time( 'c' ),
			'prepTime'           => '',
			'cookTime'           => '',
			'totalTime'          => '',
			'recipeCategory'     => $cat_list,
			'recipeCuisine'      => array(),
			'recipeYield'        => '',
			'nutrition'          => array(
				'@type' => 'NutritionInformation',
			),
			'recipeIngredient'   => array(),
			'recipeInstructions' => array(),
			'aggregateRating'    => array(
				'@type'       => 'AggregateRating',
				'ratingValue' => $rating_average,
				'reviewCount' => $rating_count,
			),
			'video'              => array(
				'@type'        => 'VideoObject',
				'name'         => isset( $settings['title'] ) ? $settings['title'] : self::$recipe->post_title,
				'description'  => isset( $settings['recipe_card_summary'] ) ? $settings['recipe_card_summary'] : self::$recipe->post_excerpt,
				'thumbnailUrl' => '',
				'contentUrl'   => '',
				'embedUrl'     => '',
				'uploadDate'   => get_the_time( 'c' ), // by default is post plublish date
				'duration'     => '',
			),
		);

		// Remove aggregateRating from json_ld if number of ratings is zero
		if ( $rating_count <= 0 ) {
			unset( $json_ld['aggregateRating'] );
		}

		if ( ! empty( $settings['image']['url'] ) ) {
			$image_id = isset( $settings['image']['id'] ) ? $settings['image']['id'] : 0;

			if ( ! empty( $settings['thumbnail_size'] ) ) {
				if ( 'custom' == $settings['thumbnail_size'] ) {
					if ( ! empty( $settings['thumbnail_custom_dimension']['width'] ) ) {
						$attachment_size[0] = (int) $settings['thumbnail_custom_dimension']['width'];
					}
					if ( ! empty( $settings['thumbnail_custom_dimension']['height'] ) ) {
						$attachment_size[1] = (int) $settings['thumbnail_custom_dimension']['height'];
					}
					$imageSize = $attachment_size;
				} else {
					$imageSize = $settings['thumbnail_size'];
				}
			} else {
				$imageSize = 'thumbnail';
			}
			$json_ld['image'] = wp_get_attachment_image_url( $settings['image']['id'], $imageSize, false );
		}

		if ( 'embed' === $settings['video_type'] ) {
			if ( ! empty( $settings['video_url'] ) ) {
				$video_url                      = esc_url( $settings['video_url'] );
				$json_ld['video']['contentUrl'] = esc_url( $video_url );

				if ( strpos( $video_url, 'youtu' ) ) {
					$video_embed_url = self::$helpers->convert_youtube_url_to_embed( $video_url );
				} elseif ( strpos( $video_url, 'vimeo' ) ) {
					$video_embed_url = self::$helpers->convert_vimeo_url_to_embed( $video_url );
				}
				$json_ld['video']['embedUrl'] = esc_url( $video_embed_url );
			} else {
				// we have no video added
				// removed video attribute from json_ld array
				unset( $json_ld['video'] );
			}
		} elseif ( 'hosted' === $settings['video_type'] ) {
			$video_id         = ! empty( $settings['hosted_url']['id'] ) ? $settings['hosted_url']['id'] : 0;
			$video_attachment = get_post( $video_id );

			if ( $video_attachment ) {
				$video_data = wp_get_attachment_metadata( $video_id );
				$video_url  = wp_get_attachment_url( $video_id );

				$image_id      = get_post_thumbnail_id( $video_id );
				$thumb         = wp_get_attachment_image_src( $image_id, 'wpzoom-rcb-block-header' );
				$thumbnail_url = $thumb && isset( $thumb[0] ) ? $thumb[0] : '';

				$json_ld['video'] = array_merge(
					$json_ld['video'],
					array(
						'name'         => $video_attachment->post_title,
						'description'  => $video_attachment->post_content,
						'thumbnailUrl' => $thumbnail_url,
						'contentUrl'   => $video_url,
						'uploadDate'   => date( 'c', strtotime( $video_attachment->post_date ) ),
						'duration'     => 'PT' . $video_data['length'] . 'S',
					)
				);

				if ( isset( $settings['video_title'] ) && ! empty( $settings['video_title'] ) ) {
					$json_ld['video']['name'] = esc_html( $settings['video_title'] );
				}
				if ( isset( $settings['video_description'] ) && ! empty( $settings['video_description'] ) ) {
					$json_ld['video']['video_description'] = esc_html( $settings['video_description'] );
				}
				if ( isset( $settings['poster']['url'] ) ) {
					$json_ld['video']['thumbnailUrl'] = esc_url( $settings['poster']['url'] );
				}
			} else {
				// we have no video added
				// removed video attribute from json_ld array
				unset( $json_ld['video'] );
			}
		}

		if ( ! empty( $settings['recipe_course'] ) && 'yes' == $settings['show_course'] ) {
			$json_ld['recipeCategory'] = explode( ',', $settings['recipe_course'] );
		}

		if ( ! empty( $settings['recipe_cuisine'] ) && 'yes' == $settings['show_cuisine'] ) {
			$json_ld['recipeCuisine'] = explode( ',', $settings['recipe_cuisine'] );
		}

		if ( ! empty( $settings['recipe_keywords'] ) ) {
			$json_ld['keywords'] = explode( ',', $settings['recipe_keywords'] );
		}

		if ( is_array( $settings['recipe_details_list'] ) ) {
			foreach ( $settings['recipe_details_list'] as $key => $detail ) {
				if ( $key === 0 ) {
					if ( isset( $detail['detail_item_value'] ) && 'yes' == $detail['show_detail_item'] ) {
						$yield = array(
							$detail['detail_item_value'],
						);
						if ( isset( $detail['detail_item_unit'] ) && ! empty( $detail['detail_item_unit'] ) ) {
							$yield[] = $detail['detail_item_value'] . ' ' . $detail['detail_item_unit'];
						}
					}
					if ( isset( $yield ) ) {
						$json_ld['recipeYield'] = $yield;
					}
				} elseif ( $key === 3 ) {
					if ( isset( $detail['detail_item_value'] ) && 'yes' == $detail['show_detail_item'] ) {
						if ( ! is_array( $detail['detail_item_value'] ) ) {
							$json_ld['nutrition']['calories'] = $detail['detail_item_value'] . ' ' . $detail['detail_item_unit'];
						}
					}
				} elseif ( $key === 1 ) {
					if ( isset( $detail['detail_item_value'] ) && 'yes' == $detail['show_detail_item'] ) {
						if ( ! is_array( $detail['detail_item_value'] ) ) {
							$prepTime            = $this->get_number_from_string( $detail['detail_item_value'] );
							$json_ld['prepTime'] = $this->get_period_time( $detail['detail_item_value'] );
						}
					}
				} elseif ( $key === 2 ) {
					if ( isset( $detail['detail_item_value'] ) && 'yes' == $detail['show_detail_item'] ) {
						if ( ! is_array( $detail['detail_item_value'] ) ) {
							$cookTime            = $this->get_number_from_string( $detail['detail_item_value'] );
							$json_ld['cookTime'] = $this->get_period_time( $detail['detail_item_value'] );
						}
					}
				} elseif ( $key === 8 ) {
					if ( isset( $detail['detail_item_value'] ) && 'yes' == $detail['show_detail_item'] ) {
						if ( ! is_array( $detail['detail_item_value'] ) ) {
							$json_ld['totalTime'] = $this->get_period_time( $detail['detail_item_value'] );
						}
					}
				}
			}

			if ( empty( $json_ld['totalTime'] ) ) {
				if ( isset( $prepTime, $cookTime ) && ( $prepTime + $cookTime ) > 0 ) {
					$json_ld['totalTime'] = $this->get_period_time( $prepTime + $cookTime );
				}
			}
		}

		return $json_ld;
	}

	/**
	 * Returns the date value in ISO 8601 date format.
	 *
	 * @param string $value The string value with number and unit.
	 *
	 * @return string A textual string indicating a time period in ISO 8601 time interval format.
	 */
	public function get_period_time( $value ) {
		$time    = $this->get_number_from_string( $value );
		$days    = floor( $time / 1440 );
		$hours   = floor( ( $time - $days * 1440 ) / 60 );
		$minutes = $time - ( $days * 1440 ) - ( $hours * 60 );
		$period  = '';

		if ( $days > 0 ) {
			$hours   = ( $hours % 24 );
			$period .= $days . 'D';
		}

		if ( $hours > 0 ) {
			$period .= 'T' . $hours . 'H';
		}

		if ( $minutes > 0 ) {
			if ( intval( $hours ) === 0 ) {
				$period .= 'T' . $minutes . 'M';
			} else {
				$period .= $minutes . 'M';
			}
		}

		$period = 'P' . $period;

		return $period;
	}

	/**
	 * Returns the number from string.
	 *
	 * @param string $string The string value with number and unit.
	 *
	 * @return number The first number matched from string.
	 */
	public function get_number_from_string( $string ) {
		if ( is_numeric( $string ) ) {
			return $string;
		}

		$re = '/\d+/s';
		preg_match( $re, $string, $matches );

		return isset( $matches[0] ) ? (int) $matches[0] : 0;
	}

	public function get_print_button() {
		$output = sprintf(
			'<div class="wpzoom-recipe-card-print-link">
	            <a class="btn-print-link elementor-rcb-print-button no-print" href="#">
	            	<SVG class="wpzoom-rcb-icon-print-link" viewBox="0 0 32 32" width="32" height="32" xmlns="http://www.w3.org/2000/svg">
	            	    <g data-name="Layer 55" id="Layer_55">
	            	        <Path class="wpzoom-rcb-print-icon" d="M28,25H25a1,1,0,0,1,0-2h3a1,1,0,0,0,1-1V10a1,1,0,0,0-1-1H4a1,1,0,0,0-1,1V22a1,1,0,0,0,1,1H7a1,1,0,0,1,0,2H4a3,3,0,0,1-3-3V10A3,3,0,0,1,4,7H28a3,3,0,0,1,3,3V22A3,3,0,0,1,28,25Z" />
	            	        <Path class="wpzoom-rcb-print-icon" d="M25,31H7a1,1,0,0,1-1-1V20a1,1,0,0,1,1-1H25a1,1,0,0,1,1,1V30A1,1,0,0,1,25,31ZM8,29H24V21H8Z" />
	            	        <Path class="wpzoom-rcb-print-icon" d="M25,9a1,1,0,0,1-1-1V3H8V8A1,1,0,0,1,6,8V2A1,1,0,0,1,7,1H25a1,1,0,0,1,1,1V8A1,1,0,0,1,25,9Z" />
	            	        <rect class="wpzoom-rcb-print-icon" height="2" width="2" x="24" y="11" />
	            	        <rect class="wpzoom-rcb-print-icon" height="2" width="4" x="18" y="11" />
	            	    </g>
	            	</SVG>
	                <span>%s</span>
	            </a>
	        </div>',
			esc_html__( 'Print', 'wpzoom-recipe-card' )
		);

		return $output;
	}


}
