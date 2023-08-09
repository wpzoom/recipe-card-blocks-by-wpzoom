<?php

/**
 * Register the recipe custo post.
 *
 * @since   1.2.0
 * @package WPZOOM_Recipe_Card_Blocks
 */


if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Register the recipe custom post.
 *
 * @since 1.2.0
 */
class WPZOOM_Custom_Post {


	/**
	 * Instance
	 *
	 * @var WPZOOM_Custom_Post The single instance of the class.
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
	 * @return WPZOOM_Custom_Post An instance of the class.
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

		global $wp_version;

		add_action( 'init', array( __CLASS__, 'register_custom_post' ) );
		add_action( 'admin_menu', array( __CLASS__, 'add_admin_menu_submenu' ), 9 );
		add_filter( 'parent_file', array( __CLASS__, 'fix_admin_parent_file' ) );

		add_action( 'add_meta_boxes', array( $this, 'create_metabox'  )     );
		add_action( 'save_post',      array( $this, 'save_metabox' ), 10, 2 );

		if ( version_compare( $wp_version, '5.8', '<' ) ) {
			add_filter( 'allowed_block_types', array( __CLASS__, 'allowed_block_types' ), 10, 2 );
		} else {
			add_filter( 'allowed_block_types_all', array( __CLASS__, 'allowed_block_types' ), 10, 2 );
		}
		add_filter( 'default_content', array( __CLASS__, 'default_rcb_content' ), 10, 2 );

		add_action( 'admin_footer', array( $this, 'add_cpt_message' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_js_notification' ) );

		// Update the columns shown on the custom post type edit.php view - so we also have custom columns
		add_filter( 'manage_wpzoom_rcb_posts_columns' , array( $this, 'recipe_post_type_columns' ) );
		add_action( 'manage_wpzoom_rcb_posts_custom_column' , array( $this,'fill_custom_post_type_columns' ), 10, 2 );

		add_action( 'template_redirect', array( $this, 'redirect_single_recipe_to_404' ) );
		add_filter( 'post_row_actions', array( $this, 'filter_admin_row_actions' ), 11, 2 );

	}

	//Add ID of the recipe to the row actions
	public function filter_admin_row_actions( $actions, $post ) {

		// Check for your post type.
		if ( $post->post_type == 'wpzoom_rcb' ) {

			$recipe_id_to_actions = array( 'recipe-id' => sprintf(
				'<span class="recipe-id">#%1$d</span>',
				$post->ID
			) );

			$actions = array_merge( $recipe_id_to_actions, $actions );

		}

		return $actions;
	}


	public function recipe_post_type_columns( $columns ) {

		return array(
				'cb'              => '<input type="checkbox" />',
				'title'           => esc_html__( 'Recipe Title', 'recipe-card-blocks-by-wpzoom' ),
				'shortcode'       => esc_html__( 'Shortcode', 'recipe-card-blocks-by-wpzoom' ),
				'parent_post'     => esc_html__( 'Parent', 'recipe-card-blocks-by-wpzoom' ),
				'used_in'         => esc_html__( 'Posts containing this recipe', 'recipe-card-blocks-by-wpzoom' ),
				'date'            => esc_html__( 'Date', 'recipe-card-blocks-by-wpzoom' )
		);
		//return $columns;
	}

	public function fill_custom_post_type_columns( $column, $post_id ) {


		$parent_id = get_post_meta( $post_id, '_wpzoom_rcb_parent_post_id', true );

		if( 'trash' === get_post_status( $parent_id ) ) {
			$parent_id = $post_id;
		};
		
		// Fill in the columns with meta box info associated with each post
		switch ( $column ) {

			case 'shortcode' :
				$post = get_post();
				echo '<input type="text" size="22" id="wpz-cpt-rcb-shortcode" onClick="this.select();" value="' . $this->display_shortcode_string( $post->ID ) .'">';
			break;

			case 'parent_post' :
				$parent_title = get_the_title( $parent_id ) . '<br/>';
				$parent_edit  =  '<a href="' . get_edit_post_link( $parent_id ) . '">' . esc_html__( '(edit)','recipe-card-blocks-by-wpzoom' ) . '</a>';
				$parent_view  = 'wpzoom_rcb' !== get_post_type( $parent_id ) ? '<a href="' . get_the_permalink( $parent_id ) . '" target="_blank">' . esc_html__( '(view) ', 'recipe-card-blocks-by-wpzoom' ) . '</a>' : '';

				echo $parent_title . ' ' . $parent_edit . ' ' . $parent_view;
			break;

			case 'used_in' :
				$used_in = get_post_meta( $post_id, '_wpzoom_rcb_used_in', true );
				$used_in = explode( ",", $used_in );
				foreach( $used_in as $p ) {
					if( !empty( $p ) && 'publish' == get_post_status( $p ) ) {
						echo '<a href="' . get_edit_post_link( $p ) . '">' . get_the_title( $p ) . '</a><br/>';
					}
				}
			break;
		}
	}

	/**
	 * Display generated shortcode string
	 *
	 * @since 3.0.3
	 *
	 * @param int|string $post_id The post ID.
	 * @return void
	 */
	public function display_shortcode_string( $post_id ) {
		return esc_html( '[wpzoom_rcb_post id="' . $post_id . '"]' );
	}

	//Force recipe card ctp singular to go to 404
	public function redirect_single_recipe_to_404() {

		if ( is_singular( 'wpzoom_rcb' ) ) {
			global $wp_query;
			$wp_query->posts = [];
			$wp_query->post = null;
			$wp_query->set_404();
			status_header(404);
			nocache_headers();
		}
	}

	public function add_cpt_message() {

		global $pagenow;

		$box_transient = get_transient( 'wpzoom_rcb_search_recipe_box' );

		if ( $box_transient ) {
			return;
		}

		

		if( 'post-new.php' !== $pagenow && isset( $_GET['post_type'] ) && $_GET['post_type'] == 'wpzoom_rcb' ) {

			$message = '<div id="wpzoom_cpt_empty_message">
							<h3>' . wp_kses_post( __( 'Old Recipes Are Missing?<br/>Scan all your posts to add them here', 'recipe-card-blocks-by-wpzoom' ) ) . '</h3>
							<input type="button" name="wpzoom_rcb_settings_search_recipes" id="wpzoom_rcb_settings_search_recipes" class="button button-primary" value="' . __('Search Recipes', 'recipe-card-blocks-by-wpzoom' ) . '">
							<div class="wpzoom-recipe-card-search-note" style="line-height:30px;margin-top:10px;display:none;">
								<strong id="wpzoom_recipe_cards_result_amount">0</strong>
								' . esc_html__( 'Recipes found!', 'recipe-card-blocks-by-wpzoom' ) . '
							</div>
							<a href="#wpzoom_cpt_empty_message" class="wpzoom-rcb-scanner-close"><i class="dashicons dashicons-no-alt"></i>' . __( 'Close', 'recipe-card-blocks-by-wpzoom' ) . '</a>
						</div>';

			echo $message;
			?>
			<style>
				#wpzoom_cpt_empty_message {
					text-align:center;
					background:#fff;
					padding:35px;
					margin:10px auto;
					max-width:450px;
					width:100%;
					border-radius:6px;
					position: relative;
				}
				#wpzoom_cpt_empty_message h3 {
					line-height:26px;
					font-size:1.2em;
					font-weight:normal;
				}
				.wpzoom-rcb-scanner-close {
					position: absolute;
					top:20px;
					right: 20px;
					text-decoration:none;
				}
			</style>
			<script>
				var cpt_message = jQuery('#wpzoom_cpt_empty_message');
				jQuery('#ajax-response').html( cpt_message );
			</script>
		<?php
		}
	}

	/**
	 * Register custom post.
	 *
	 * @since 1.2.0
	 */
	public static function register_custom_post() {

		$wpzoom_rcb_slug = 'recipe-cards';

		$labels = array(
			'name'                  => esc_html_x( 'Recipes', 'Post Type General Name', 'recipe-card-blocks-by-wpzoom' ),
			'singular_name'         => esc_html_x( 'Recipe', 'Post Type Singular Name', 'recipe-card-blocks-by-wpzoom' ),
			'menu_name'             => esc_html__( 'All Recipes', 'recipe-card-blocks-by-wpzoom' ),
			'name_admin_bar'        => esc_html__( 'Recipes', 'recipe-card-blocks-by-wpzoom' ),
			'archives'              => esc_html__( 'Recipe Archives', 'recipe-card-blocks-by-wpzoom' ),
			'parent_item_colon'     => esc_html__( 'Parent Item:', 'recipe-card-blocks-by-wpzoom' ),
			'all_items'             => esc_html__( 'All Recipes', 'recipe-card-blocks-by-wpzoom' ),
			'add_new_item'          => esc_html__( 'Add New Recipe', 'recipe-card-blocks-by-wpzoom' ),
			'add_new'               => esc_html__( 'Add New', 'recipe-card-blocks-by-wpzoom' ),
			'new_item'              => esc_html__( 'New Recipe', 'recipe-card-blocks-by-wpzoom' ),
			'edit_item'             => esc_html__( 'Edit Recipe', 'recipe-card-blocks-by-wpzoom' ),
			'update_item'           => esc_html__( 'Update Recipe', 'recipe-card-blocks-by-wpzoom' ),
			'view_item'             => esc_html__( 'View Recipe', 'recipe-card-blocks-by-wpzoom' ),
			'search_items'          => esc_html__( 'Search Recipes', 'recipe-card-blocks-by-wpzoom' ),
			'not_found'             => esc_html__( 'Not found', 'recipe-card-blocks-by-wpzoom' ),
			'not_found_in_trash'    => esc_html__( 'Not found in Trash', 'recipe-card-blocks-by-wpzoom' ),
			'featured_image'        => esc_html__( 'Featured Image', 'recipe-card-blocks-by-wpzoom' ),
			'set_featured_image'    => esc_html__( 'Set featured image', 'recipe-card-blocks-by-wpzoom' ),
			'remove_featured_image' => esc_html__( 'Remove featured image', 'recipe-card-blocks-by-wpzoom' ),
			'use_featured_image'    => esc_html__( 'Use as featured image', 'recipe-card-blocks-by-wpzoom' ),
			'insert_into_item'      => esc_html__( 'Insert into Recipe', 'recipe-card-blocks-by-wpzoom' ),
			'uploaded_to_this_item' => esc_html__( 'Uploaded to this Recipe', 'recipe-card-blocks-by-wpzoom' ),
			'items_list'            => esc_html__( 'Items list', 'recipe-card-blocks-by-wpzoom' ),
			'items_list_navigation' => esc_html__( 'Items list navigation', 'recipe-card-blocks-by-wpzoom' ),
			'filter_items_list'     => esc_html__( 'Filter items list', 'recipe-card-blocks-by-wpzoom' ),
		);
		$rewrite = array(
			'slug'                  => $wpzoom_rcb_slug,
			'with_front'            => true,
			'pages'                 => true,
			'feeds'                 => false,
		);
		$args = array(
			'label'                 => esc_html__( 'Recipe Card', 'recipe-card-blocks-by-wpzoom' ),
			'description'           => esc_html__( 'Custom Recipe Card post', 'recipe-card-blocks-by-wpzoom' ),
			'labels'                => $labels,
			'supports'              => array( 'title', 'editor', 'thumbnail', 'custom-fields', 'revisions' ),
			'taxonomies'            => array( 'wpzoom_rcb_courses', 'wpzoom_rcb_cuisines', 'wpzoom_rcb_difficulties' ),
			'hierarchical'          => false,
			'public'                => false,
			'show_ui'               => true,
			'show_in_menu'          => false,
			'menu_position'         => 5,
			//'show_in_menu'          => 'admin.php?page=wpzoom-recipe-card-settings',
			'menu_icon'             => 'dashicons-format-image',
			'show_in_admin_bar'     => true,
			'show_in_nav_menus'     => true,
			'can_export'            => true,
			'has_archive'           => false,
			'exclude_from_search'   => true,
			'publicly_queryable'    => false,
			'rewrite'               => $rewrite,
			'show_in_rest'          => true,
			'capability_type'       => 'post',
		);

		register_post_type( 'wpzoom_rcb', $args );

	}

	/**
	 * Add submenu for CPT Recipe Card.
	 *
	 * @since 1.2.0
	 */
	public static function add_admin_menu_submenu() {

		add_submenu_page( 
			WPZOOM_RCB_SETTINGS_PAGE, 
			esc_html__( 'All Recipes', 'recipe-card-blocks-by-wpzoom' ),
			esc_html__( 'All Recipes', 'recipe-card-blocks-by-wpzoom' ),
			'edit_posts',
			'edit.php?post_type=wpzoom_rcb'
		);
	
	}

	/**
	 * Fixin issue with active submenu for CPT Recipe Card.
	 *
	 * @since 1.2.0
	 */
	public static function fix_admin_parent_file( $parent_file ){
    
		global $submenu_file, $current_screen;

		if( self::get_current_post_type( 'wpzoom_rcb' ) ) {

			$submenu_file = 'edit.php?post_type=wpzoom_rcb';
			$parent_file = WPZOOM_RCB_SETTINGS_PAGE;
		
		}
		
		return $parent_file;
	
	}

	/**
	 * Set default content for the recipe card CPT.
	 *
	 * @since 1.2.0
	 */
	public static function default_rcb_content( $content, $post ) {

		if ( self::get_current_post_type( 'wpzoom_rcb' ) ) {
			return '<!-- wp:wpzoom-recipe-card/block-recipe-card {"recipeTitle":"","details":[{"id":"detail-item-6140a7455f6c8","iconSet":"oldicon","icon":"food","label":"Servings","unit":"servings","value":"4"},{"id":"detail-item-6140a7455f6d7","iconSet":"oldicon","icon":"clock","label":"Prep time","unit":"minutes","value":"30"},{"id":"detail-item-6140a7455f6e2","iconSet":"foodicons","icon":"cooking-food-in-a-hot-casserole","label":"Cooking time","unit":"minutes","value":"40"},{"id":"detail-item-6140a7455f6ec","iconSet":"foodicons","icon":"fire-flames","label":"Calories","unit":"kcal","value":"300"},{"id":"detail-item-6140a7455f6f7","iconSet":"fa","_prefix":"far","icon":"clock"},{"id":"detail-item-6140a7455f6f8","iconSet":"oldicon","icon":"chef-cooking"},{"id":"detail-item-6140a7455f6f9","iconSet":"oldicon","icon":"food-1"},{"id":"detail-item-6140a7455f6fb","iconSet":"fa","_prefix":"fas","icon":"sort-amount-down"},{"id":"detail-item-6140a7455f6fc","iconSet":"fa","_prefix":"far","icon":"clock","label":"Total time","unit":"minutes","value":"0"}]} /-->';
		}
		return $content;

	}


	/**
	 * Set allowed block types for the recipe card CPT.
	 *
	 * @since 1.2.0
	 */
	public static function allowed_block_types( $allowed_block_types, $post ) {
		
		if ( self::get_current_post_type( 'wpzoom_rcb' ) ) {
			return array(
				'wpzoom-recipe-card/block-recipe-card',
				'wpzoom-recipe-card/block-ingredients',
				'wpzoom-recipe-card/block-nutrition',
				'wpzoom-recipe-card/block-details',
				'wpzoom-recipe-card/block-directions'
			);
		}
		return $allowed_block_types;
		
	}

	/**
	 * Get current post type.
	 *
	 * @since 1.2.0
	 */
	public static function get_current_post_type( $post_type = '' ) {
		
		$type = false;
	
		if( isset( $_GET['post'] ) ) {
			$id = $_GET['post'];
			$post = get_post( $id );
			if( is_object( $post ) && $post->post_type == $post_type ) {
				$type = true;
			}
		} elseif ( isset( $_GET['post_type'] ) && $_GET['post_type'] == $post_type ) {
			$type = true;
		}
		
		return $type;	
	}

	public function enqueue_js_notification() {

		global $post; 
		$screen = get_current_screen();

		//Check if it is CPT
		if( 'wpzoom_rcb' !== $screen->id ) {
			return;
		}

		//Check if there is any parent post/page
		$parent_id = get_post_meta( get_the_ID(), '_wpzoom_rcb_parent_post_id', true );
		if( empty( $parent_id ) || $parent_id == get_the_ID() ) {
			return;
		}

		//Check if it is enabled from settings
		if ( '1' !== WPZOOM_Settings::get( 'wpzoom_rcb_settings_synchronize_recipe_post' ) ) {
			return;
		}

	}

	public static function get_parent_data() {
		
		global $post;
		$post_id = get_post_meta( $post->ID, '_wpzoom_rcb_parent_post_id', true );
		
		return array(
			'parent_link_label' => get_the_title( $post_id ),
			'parent_url' => get_edit_post_link( $post_id )
		);

	}


	public function the_cpt_message() {

		global $post;
		$post_id = get_post_meta( $post->ID, '_wpzoom_rcb_parent_post_id', true );

		return '<div style="margin-bottom:20px;" class="wpzoom-cpt-metabox">' 
		. esc_html__( 'The parent post of this Recipe is: ', 'recipe-card-blocks-by-wpzoom' )
		. '<a href="' . get_edit_post_link( $post_id ) . '">' . get_the_title( $post_id ) . ' </a>
		</div>';

	}

	/**
	 * Adds the meta box.
	 */
	public function create_metabox() {
		add_meta_box(
			'wpzoom_rcb_metabox_parent_post_id',
			esc_html__( 'Recipe Card Post Details', 'recipe-card-blocks-by-wpzoom' ),
			array( $this, 'render_metabox' ),
			'wpzoom_rcb',
			'side',
			'high'
		);
	}

	public function render_metabox( $post ) {

		$value      = get_post_meta( $post->ID, '_wpzoom_rcb_parent_post_id', true );
		$used_in    = get_post_meta( $post->ID, '_wpzoom_rcb_used_in', true );
		$has_parent = get_post_meta( $post->ID, '_wpzoom_rcb_has_parent', true );

		if( !empty( $value ) ) {
			if( 'publish' !== get_post_status( $value ) ) {
				$value = $post->ID;
			}			
		} else {
			$value = $post->ID;
		}

		if( $value != $post->ID ) {
			echo $this->the_cpt_message();
		}
		else {
			echo '<div style="margin-bottom:20px;" class="wpzoom-cpt-metabox">' . esc_html__( 'This Recipe Card Post is created by', 'recipe-card-blocks-by-wpzoom' ) . ' ' . get_the_author_link() . '</div>';
		}
		echo '<p>
				<label><strong>' . esc_html__( 'Shortcode:', 'recipe-card-blocks-by-wpzoom' ) .'</strong></label>
				<input type="text" id="wpz-cpt-rcb-shortcode" onClick="this.select();" value="' . $this->display_shortcode_string( $post->ID ) . '">
			</p>';

		echo '<input type="hidden" name="wpzoom_rcb_parent_post_id" value="' . esc_attr( $value )  . '"/>';
		echo '<input type="hidden" name="wpzoom_rcb_has_parent" value="' . esc_attr( $has_parent ) . '"/>';
		echo '<input type="hidden" name="wpzoom_rcb_used_in"    value="' . esc_attr( $used_in ) . '"/>';
	
	}

	/**
	 * Handles saving the meta box.
	 *
	 * @param int     $post_id Post ID.
	 * @param WP_Post $post    Post object.
	 * @return null
	 */
	public function save_metabox( $post_id ) {

		// Check if user has permissions to save data.
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		// Check if not an autosave.
		if ( wp_is_post_autosave( $post_id ) ) {
			return;
		}

		// Check if not a revision.
		if ( wp_is_post_revision( $post_id ) ) {
			return;
		}

		if( isset( $_POST['wpzoom_rcb_parent_post_id'] ) ) {
			update_post_meta( $post_id, '_wpzoom_rcb_parent_post_id', $_POST['wpzoom_rcb_parent_post_id'] );
		}

		if( isset( $_POST['wpzoom_rcb_has_parent'] ) ) {
			update_post_meta( $post_id, '_wpzoom_rcb_has_parent', $_POST['wpzoom_rcb_has_parent'] );
		}

		if( isset( $_POST['wpzoom_rcb_used_in'] ) ) {
			update_post_meta( $post_id, '_wpzoom_rcb_used_in', $_POST['wpzoom_rcb_used_in'] );
		}

	}

}

// Instance the class
WPZOOM_Custom_Post::instance();