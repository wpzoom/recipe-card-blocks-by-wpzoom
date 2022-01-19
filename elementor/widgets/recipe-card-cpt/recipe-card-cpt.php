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
class Recipe_Card_Cpt extends Widget_Base {


	/**
	 * @var \WP_Query
	 */
	private $query = null;

	/**
	 * $post_type
	 * @var string
	 */
	private $post_type = 'wpzoom_rcb';	

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function __construct( $data = array(), $args = null ) {
		parent::__construct( $data, $args );
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
		return 'wpzoom-elementor-recipe-card-widget-cpt';
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
		return esc_html__( 'Insert existing Recipe', 'recipe-card-blocks-by-wpzoom' );
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
	 * Get the query
	 *
	 * Returns the current query.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return \WP_Query The current query.
	 */
	public function get_query() {
		return $this->query;
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
			'_section_recipe_card_cpt',
			array(
				'label' => esc_html__( 'Recipe Card Post', 'recipe-card-blocks-by-wpzoom' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'rcb_post_id',
			array(
				'label'    => esc_html__( 'Select a Recipe', 'recipe-card-blocks-by-wpzoom' ),
				'type'     => Controls_Manager::SELECT2,
				'label_block' => true,
				'options'  => $this->get_rcb_posts(),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Get rcb posts.
	 *
	 * Retrieve a list of all recipe card posts.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return array All rcb posts.
	 */
	protected function get_rcb_posts() {

		$rcb_posts = array();

		$args = array(
			'post_type'   => $this->post_type,
			'numberposts' => -1
		);

		$posts = get_posts( $args );

		if ( !empty( $posts ) && !is_wp_error( $posts ) ) {
			foreach ( $posts as $key => $post ) {
				if ( is_object( $post ) && property_exists( $post, 'ID' ) ) {
					$rcb_posts[ $post->ID ] = get_the_title( $post );
				}
			}
		}

		return $rcb_posts;

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

		$settings = $this->get_settings_for_display();

		$post_id = isset( $settings['rcb_post_id'] ) ? $settings['rcb_post_id'] : null;

		if( !$post_id ) {
			return;
		}

		$parentRecipe_ID = get_post_meta( $post_id, '_wpzoom_rcb_parent_post_id', true );

		if( !empty( $parentRecipe_ID ) ) {
			$parent_id = $parentRecipe_ID;
		}
		else {
			$parent_id = $post_id;
		}

		$recipe = get_post( intval( $post_id ) );

		if ( has_blocks( $recipe->post_content ) ) {
			$blocks = parse_blocks( $recipe->post_content );
		}

		printf( 
			'<div class="wpzoom-custom-recipe-card-post wpzoom-rcb-post-shortcode" data-parent-id="%3$d" data-recipe-post="%2$d">%1$s</div>',
			render_block( $blocks[0] ),
			intval( $post_id ),
			intval( $parent_id )
		);

	}

}