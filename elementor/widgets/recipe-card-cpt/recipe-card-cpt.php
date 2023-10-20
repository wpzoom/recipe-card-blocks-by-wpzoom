<?php
namespace WPZOOMElementorRecipeCard;

use Elementor\Widget_Base;
use Elementor\Group_Control_Background;
use Elementor\Repeater;
use Elementor\Control_Media;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Image_Size;
use Elementor\Group_Control_Css_Filter;
use Elementor\Group_Control_Text_Shadow;
use Elementor\Group_Control_Typography;
use Elementor\Core\Kits\Documents\Tabs\Global_Typography;
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
			'_section_recipe_card_cpt',
			array(
				'label' => esc_html__( 'Recipe Card Post', 'recipe-card-blocks-by-wpzoom' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$inline_style = 'style="
		color:#856404;
		font-size:12px;
		line-height:22px;
		margin-top:10px;
		font-weight:300 !important; 
		display:block; 
		background:#fff3cd;
		border:1px solid;
		border-color:#ffeeba;
		border-radius:5px;
		padding:10px 15px;
	"';

	$rec_note = sprintf(
		'<span %s>Use only one instance of this widget per page/post</span>',
		$inline_style
	);

	$this->add_control(
		'recomendation_note',
		array(
			'label'       => 'IMPORTANT!' . $rec_note,
			'type'        => Controls_Manager::HEADING,
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
				'label' => esc_html__( 'Title', 'recipe-card-blocks-by-wpzoom' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		// Title typography.
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'recipe_card_title_style_typography',
				'global' => [
					'default' => Global_Typography::TYPOGRAPHY_PRIMARY,
				],
				'selector' => '{{WRAPPER}} .wp-block-wpzoom-recipe-card-block-recipe-card .recipe-card-title',
			)
		);

		// Title color.
		$this->add_control(
			'recipe_card_title_style_color',
			array(
				'type'      => Controls_Manager::COLOR,
				'label'     => esc_html__( 'Color', 'recipe-card-blocks-by-wpzoom' ),
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
				'label' => esc_html__( 'Details', 'recipe-card-blocks-by-wpzoom' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		// Title typography.
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'recipe_card_detail_label_style_typography',
				'global' => [
					'default' => Global_Typography::TYPOGRAPHY_PRIMARY,
				],
				'selector' => '{{WRAPPER}} .wp-block-wpzoom-recipe-card-block-recipe-card .detail-item-label',
			)
		);

		// Title color.
		$this->add_control(
			'recipe_card_detail_label_style_color',
			array(
				'type'      => Controls_Manager::COLOR,
				'label'     => esc_html__( 'Color', 'recipe-card-blocks-by-wpzoom' ),
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .wp-block-wpzoom-recipe-card-block-recipe-card .detail-item-label' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'recipe_card_detail_icon_heading',
			array(
				'label'     => esc_html__( 'Icon', 'recipe-card-blocks-by-wpzoom' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_responsive_control(
			'recipe_card_detail_icon_icon_size',
			array(
				'label'      => esc_html__( 'Icon Size', 'recipe-card-blocks-by-wpzoom' ),
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
				'label'     => esc_html__( 'Icon Color', 'recipe-card-blocks-by-wpzoom' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .recipe-card-details .detail-item-icon' => 'color: {{VALUE}} !important;',
				),
			)
		);

		$this->add_responsive_control(
			'recipe_card_detail_icon_spacing',
			array(
				'label'      => esc_html__( 'Bottom Spacing', 'recipe-card-blocks-by-wpzoom' ),
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
				'label'     => esc_html__( 'Value', 'recipe-card-blocks-by-wpzoom' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		// Value typography.
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'recipe_card_detail_value_typography',
				'global' => [
					'default' => Global_Typography::TYPOGRAPHY_TEXT,
				],
				'selector' => '{{WRAPPER}} .recipe-card-details .detail-item-value',
			)
		);

		// Value color.
		$this->add_control(
			'recipe_card_detail_value_color',
			array(
				'type'      => Controls_Manager::COLOR,
				'label'     => esc_html__( 'Color', 'recipe-card-blocks-by-wpzoom' ),
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .recipe-card-details p.detail-item-value' => 'color: {{VALUE}} !important;',
				),
			)
		);

		$this->add_responsive_control(
			'recipe_card_detail_value_spacing',
			array(
				'label'      => esc_html__( 'Side Spacing', 'recipe-card-blocks-by-wpzoom' ),
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
				'label'     => esc_html__( 'Unit', 'recipe-card-blocks-by-wpzoom' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		// Value typography.
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'recipe_card_detail_unit_typography',
				'global' => [
					'default' => Global_Typography::TYPOGRAPHY_TEXT,
				],
				'selector' => '{{WRAPPER}} .recipe-card-details .detail-item-unit',
			)
		);

		// Value color.
		$this->add_control(
			'recipe_card_detail_unit_color',
			array(
				'type'      => Controls_Manager::COLOR,
				'label'     => esc_html__( 'Color', 'recipe-card-blocks-by-wpzoom' ),
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
				'label' => esc_html__( 'Summary', 'recipe-card-blocks-by-wpzoom' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		// Summary typography.
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'recipe_card_summary_typography',
				'global' => [
					'default' => Global_Typography::TYPOGRAPHY_TEXT,
				],
				'selector' => '{{WRAPPER}} .wp-block-wpzoom-recipe-card-block-recipe-card .recipe-card-summary',
			)
		);

		// Summary color.
		$this->add_control(
			'recipe_card_summary_color',
			array(
				'type'      => Controls_Manager::COLOR,
				'label'     => esc_html__( 'Color', 'recipe-card-blocks-by-wpzoom' ),
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
				'label' => esc_html__( 'Ingredients', 'recipe-card-blocks-by-wpzoom' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'recipe_card_ingredients_title_heading',
			array(
				'label'     => esc_html__( 'Title', 'recipe-card-blocks-by-wpzoom' ),
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
				'label'     => esc_html__( 'Color', 'recipe-card-blocks-by-wpzoom' ),
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .recipe-card-ingredients .ingredients-title' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'recipe_card_ingredients_group_title_heading',
			[
				'label' => esc_html__( 'Group Title', 'recipe-card-blocks-by-wpzoom' ),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		// Ingredients group title typography.
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'recipe_card_ingredients_group_title_typography',
				'selector' => '{{WRAPPER}} .recipe-card-ingredients .ingredient-item-group-title'
			)
		);

		// Ingredients group title color.
		$this->add_control(
			'recipe_card_ingredient_group_title_color',
			array(
				'type'      => Controls_Manager::COLOR,
				'label'     => esc_html__( 'Color', 'recipe-card-blocks-by-wpzoom' ),
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .recipe-card-ingredients .ingredient-item-group-title' => 'color: {{VALUE}};'
				)
			)
		);

		$this->add_control(
			'recipe_card_ingredients_item_heading',
			array(
				'label'     => esc_html__( 'Item', 'recipe-card-blocks-by-wpzoom' ),
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
				'label'     => esc_html__( 'Color', 'recipe-card-blocks-by-wpzoom' ),
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .recipe-card-ingredients .wpzoom-rcb-ingredient-name' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_responsive_control(
			'recipe_card_ingredient_item_margin',
			array(
				'label'      => esc_html__( 'Margin', 'recipe-card-blocks-by-wpzoom' ),
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
				'label'      => esc_html__( 'Padding', 'recipe-card-blocks-by-wpzoom' ),
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
				'label' => esc_html__( 'Directions', 'recipe-card-blocks-by-wpzoom' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'recipe_card_directions_title_heading',
			array(
				'label'     => esc_html__( 'Title', 'recipe-card-blocks-by-wpzoom' ),
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
				'label'     => esc_html__( 'Color', 'recipe-card-blocks-by-wpzoom' ),
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .recipe-card-directions .directions-title' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'recipe_card_directions_group_title_heading',
			array(
				'label' => esc_html__( 'Group Title', 'recipe-card-blocks-by-wpzoom' ),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		//Directions group title typography.
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'recipe_card_directions_group_title_typography',
				'selector' => '{{WRAPPER}} .recipe-card-directions .direction-step-group-title'
			)
		);

		//Directions group title color.
		$this->add_control(
			'recipe_card_direction_group_title_color',
			array(
				'type'      => Controls_Manager::COLOR,
				'label'     => esc_html__( 'Color', 'recipe-card-blocks-by-wpzoom' ),
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .recipe-card-directions .direction-step-group-title' => 'color: {{VALUE}};'
				)
			)
		);

		$this->add_control(
			'recipe_card_directions_item_heading',
			array(
				'label'     => esc_html__( 'Step', 'recipe-card-blocks-by-wpzoom' ),
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
				'label'     => esc_html__( 'Color', 'recipe-card-blocks-by-wpzoom' ),
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .directions-list .direction-step' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_responsive_control(
			'recipe_card_directions_step_margin',
			array(
				'label'      => esc_html__( 'Margin', 'recipe-card-blocks-by-wpzoom' ),
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
				'label'      => esc_html__( 'Padding', 'recipe-card-blocks-by-wpzoom' ),
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
				'label' => esc_html__( 'Notes', 'recipe-card-blocks-by-wpzoom' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'recipe_card_note_title_heading',
			array(
				'label'     => esc_html__( 'Title', 'recipe-card-blocks-by-wpzoom' ),
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
				'label'     => esc_html__( 'Color', 'recipe-card-blocks-by-wpzoom' ),
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .recipe-card-notes .notes-title' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'recipe_card_note_item_heading',
			array(
				'label'     => esc_html__( 'Note', 'recipe-card-blocks-by-wpzoom' ),
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
				'label'     => esc_html__( 'Color', 'recipe-card-blocks-by-wpzoom' ),
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .recipe-card-notes-list li' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_responsive_control(
			'recipe_card_note_item_margin',
			array(
				'label'      => esc_html__( 'Margin', 'recipe-card-blocks-by-wpzoom' ),
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
				'label'      => esc_html__( 'Padding', 'recipe-card-blocks-by-wpzoom' ),
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
			$this->fix_content_tags_conflict( render_block( $blocks[0] ) ),
			intval( $post_id ),
			intval( $parent_id )
		);

	}

	/**
	 * Fix tags convert '<' & '>' to unicode.
	 *
	 * @since 4.0.0
	 *
	 * @param string $content The content which should parse.
	 * @return string
	 */
	public function fix_content_tags_conflict( $content ) {
		$content = preg_replace_callback(
			'#(?<!\\\\)(u003c|u003e)#',
			function( $matches ) {
				if ( 'u003c' === $matches[1] ) {
					return '<';
				} else {
					return '>';
				}
			},
			$content
		);

		return $content;
	}

}