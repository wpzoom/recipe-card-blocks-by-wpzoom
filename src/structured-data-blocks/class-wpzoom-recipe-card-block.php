<?php
/**
 * Recipe Card Block
 *
 * @since   1.1.0
 * @package WPZOOM_Recipe_Card_Blocks
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main WPZOOM_Recipe_Card_Block Class.
 */
class WPZOOM_Recipe_Card_Block {
	/**
	 * The post Object.
	 *
	 * @since 1.1.0
	 */
	private static $recipe;

	/**
	 * Class instance Structured Data Helpers.
	 *
	 * @var WPZOOM_Structured_Data_Helpers
	 * @since 1.1.0
	 */
	public static $structured_data_helpers;

	/**
	 * Class instance Helpers.
	 *
	 * @var WPZOOM_Helpers
	 * @since 1.1.0
	 */
	public static $helpers;

	/**
	 * Recipe Block ID.
	 *
	 * @since 1.2.0
	 */
	public static $recipeBlockID;

	/**
	 * Block attributes.
	 *
	 * @since 1.1.0
	 */
	public static $attributes;

	/**
	 * Block settings.
	 *
	 * @since 1.1.0
	 */
	public static $settings;

	/**
	 * Block active style.
	 *
	 * @since 1.1.0
	 */
	public static $style;

	/**
	 * The Constructor.
	 */
	public function __construct() {
		self::$structured_data_helpers = new WPZOOM_Structured_Data_Helpers();
		self::$helpers                 = new WPZOOM_Helpers();

		add_action( 'the_content', array( $this, 'filter_the_content' ), 9 );
	}

	public function filter_the_content( $content ) {
		$content = WPZOOM_Helpers::fix_content_tasty_links_conflict( $content );

		if ( self::is_built_with_elementor() ) {
			return $content;
		}

		$has_recipe_card_block = strpos( $content, 'wp-block-wpzoom-recipe-card-block-recipe-card' );

		if ( false !== $has_recipe_card_block ) {
			$content = self::prepend_content_recipe_buttons( $content );
		}

		return $content;
	}

	public static function is_built_with_elementor( $post_id = false ) {
		$post_id = ! $post_id ? get_the_ID() : $post_id;
		return class_exists( '\Elementor\Plugin' ) && \Elementor\Plugin::$instance->db->is_built_with_elementor( $post_id );
	}

	/**
	 * Registers the recipe-card block as a server-side rendered block.
	 *
	 * @return void
	 */
	public function register_hooks() {
		if ( ! function_exists( 'register_block_type' ) ) {
			return;
		}

		if ( wpzoom_rcb_block_is_registered( 'wpzoom-recipe-card/block-recipe-card' ) ) {
			return;
		}

		$attributes = array(
			'id'                   => array(
				'type'    => 'string',
				'default' => 'wpzoom-recipe-card',
			),
			'style'                => array(
				'type'    => 'string',
				'default' => WPZOOM_Settings::get( 'wpzoom_rcb_settings_template' ),
			),
			'image'                => array(
				'type' => 'object',
			),
			'hasImage'             => array(
				'type'    => 'boolean',
				'default' => false,
			),
			'video'                => array(
				'type' => 'object',
			),
			'hasVideo'             => array(
				'type'    => 'boolean',
				'default' => false,
			),
			'videoTitle'           => array(
				'type'     => 'string',
				'selector' => '.video-title',
				'default'  => WPZOOM_Settings::get( 'wpzoom_rcb_settings_video_title' ),
			),
			'hasInstance'          => array(
				'type'    => 'boolean',
				'default' => false,
			),
			'recipeTitle'          => array(
				'type'     => 'string',
				'selector' => '.recipe-card-title',
			),
			'summary'              => array(
				'type'     => 'string',
				'selector' => '.recipe-card-summary',
				'default'  => '',
			),
			'jsonSummary'          => array(
				'type' => 'string',
			),
			'course'               => array(
				'type'  => 'array',
				'items' => array(
					'type' => 'string',
				),
			),
			'cuisine'              => array(
				'type'  => 'array',
				'items' => array(
					'type' => 'string',
				),
			),
			'difficulty'           => array(
				'type'  => 'array',
				'items' => array(
					'type' => 'string',
				),
			),
			'keywords'             => array(
				'type'  => 'array',
				'items' => array(
					'type' => 'string',
				),
			),
			'settings'             => array(
				'type'    => 'array',
				'default' => array(
					array(
						'primary_color'      => '#222222',
						'icon_details_color' => '#6d767f',
						'hide_header_image'  => false,
						'print_btn'          => WPZOOM_Settings::get( 'wpzoom_rcb_settings_display_print' ) === '1',
						'pin_btn'            => WPZOOM_Settings::get( 'wpzoom_rcb_settings_display_pin' ) === '1',
						'custom_author_name' => WPZOOM_Settings::get( 'wpzoom_rcb_settings_author_custom_name' ),
						'displayCourse'      => WPZOOM_Settings::get( 'wpzoom_rcb_settings_display_course' ) === '1',
						'displayCuisine'     => WPZOOM_Settings::get( 'wpzoom_rcb_settings_display_cuisine' ) === '1',
						'displayDifficulty'  => WPZOOM_Settings::get( 'wpzoom_rcb_settings_display_difficulty' ) === '1',
						'displayAuthor'      => WPZOOM_Settings::get( 'wpzoom_rcb_settings_display_author' ) === '1',
						'displayServings'    => WPZOOM_Settings::get( 'wpzoom_rcb_settings_display_servings' ) === '1',
						'displayPrepTime'    => WPZOOM_Settings::get( 'wpzoom_rcb_settings_display_preptime' ) === '1',
						'displayCookingTime' => WPZOOM_Settings::get( 'wpzoom_rcb_settings_display_cookingtime' ) === '1',
						'displayTotalTime'   => WPZOOM_Settings::get( 'wpzoom_rcb_settings_display_totaltime' ) === '1',
						'displayCalories'    => WPZOOM_Settings::get( 'wpzoom_rcb_settings_display_calories' ) === '1',
						'headerAlign'        => WPZOOM_Settings::get( 'wpzoom_rcb_settings_heading_content_align' ),
						'ingredientsLayout'  => '1-column',
					),
				),
				'items'   => array(
					'type' => 'object',
				),
			),
			'details'              => array(
				'type'    => 'array',
				'default' => self::get_details_default(),
				'items'   => array(
					'type' => 'object',
				),
			),
			'toInsert'             => array(
				'type' => 'integer',
			),
			'showModal'            => array(
				'type'    => 'boolean',
				'default' => false,
			),
			'icons'                => array(
				'type' => 'object',
			),
			'activeIconSet'        => array(
				'type'    => 'string',
				'default' => 'foodicons',
			),
			'searchIcon'           => array(
				'type'    => 'string',
				'default' => '',
			),
			'ingredientsTitle'     => array(
				'type'     => 'string',
				'selector' => '.ingredients-title',
				'default'  => WPZOOM_Settings::get( 'wpzoom_rcb_settings_ingredients_title' ),
			),
			'jsonIngredientsTitle' => array(
				'type' => 'string',
			),
			'ingredients'          => array(
				'type'    => 'array',
				'default' => self::get_ingredients_default(),
				'items'   => array(
					'type' => 'object',
				),
			),
			'directionsTitle'      => array(
				'type'     => 'string',
				'selector' => '.directions-title',
				'default'  => WPZOOM_Settings::get( 'wpzoom_rcb_settings_steps_title' ),
			),
			'jsonDirectionsTitle'  => array(
				'type' => 'string',
			),
			'steps'                => array(
				'type'    => 'array',
				'default' => self::get_steps_default(),
				'items'   => array(
					'type' => 'object',
				),
			),
			'notesTitle'           => array(
				'type'     => 'string',
				'selector' => '.notes-title',
				'default'  => WPZOOM_Settings::get( 'wpzoom_rcb_settings_notes_title' ),
			),
			'notes'                => array(
				'type'     => 'string',
				'selector' => '.recipe-card-notes-list',
				'default'  => '',
			),
			'blockAlignment'       => array(
				'type'    => 'string',
				'default' => is_rtl() ? 'right' : 'left',
			),
		);

		// Hook server side rendering into render callback
		register_block_type(
			'wpzoom-recipe-card/block-recipe-card',
			array(
				'attributes'      => $attributes,
				'render_callback' => array( $this, 'render' ),
			)
		);
	}

	/**
	 * Renders the block.
	 *
	 * @param array  $attributes The attributes of the block.
	 * @param string $content    The HTML content of the block.
	 *
	 * @return string The block preceded by its JSON-LD script.
	 */
	public function render( $attributes, $content ) {
		if ( ! is_array( $attributes ) ) {
			return $content;
		}

		$attributes = self::$helpers->omit( $attributes, array( 'toInsert', 'activeIconSet', 'showModal', 'searchIcon', 'icons' ) );
		// Import variables into the current symbol table from an array
		extract( $attributes );

		$class = 'wp-block-wpzoom-recipe-card-block-recipe-card';

		// Recipe post variables
		self::$recipe              = get_post();
		$recipe_ID                 = get_the_ID( self::$recipe );
		$recipe_title              = get_the_title( self::$recipe );
		$recipe_thumbnail_url      = get_the_post_thumbnail_url( self::$recipe );
		$recipe_thumbnail_id       = get_post_thumbnail_id( self::$recipe );
		$recipe_permalink          = get_the_permalink( self::$recipe );
		$recipe_author_name        = get_the_author_meta( 'display_name', self::$recipe->post_author );
		$attachment_id             = isset( $image['id'] ) ? $image['id'] : $recipe_thumbnail_id;
		$tasty_pins_pinterest_text = get_post_meta( $attachment_id, 'tp_pinterest_text', true );

		// Variables from attributes
		// add default value if not exists
		$recipeTitle = isset( $recipeTitle ) ? $recipeTitle : '';
		$summary     = isset( $summary ) ? $summary : '';
		$className   = isset( $className ) ? $className : '';
		$hasImage    = isset( $hasImage ) ? $hasImage : false;
		$course      = isset( $course ) ? $course : array();
		$cuisine     = isset( $cuisine ) ? $cuisine : array();
		$difficulty  = isset( $difficulty ) ? $difficulty : array();
		$keywords    = isset( $keywords ) ? $keywords : array();
		$details     = isset( $details ) ? $details : array();
		$ingredients = isset( $ingredients ) ? $ingredients : array();
		$steps       = isset( $steps ) ? $steps : array();

		// Store variables
		self::$recipeBlockID = esc_attr( $id );
		self::$attributes    = $attributes;
		self::$style         = self::$helpers->get_block_style( $className );
		self::$settings      = self::$helpers->parse_block_settings( $attributes );

		self::$attributes['ingredientsTitle'] = isset( $ingredientsTitle ) ? $ingredientsTitle : WPZOOM_Settings::get( 'wpzoom_rcb_settings_ingredients_title' );
		self::$attributes['directionsTitle']  = isset( $directionsTitle ) ? $directionsTitle : WPZOOM_Settings::get( 'wpzoom_rcb_settings_steps_title' );
		self::$attributes['videoTitle']       = isset( $videoTitle ) ? $videoTitle : WPZOOM_Settings::get( 'wpzoom_rcb_settings_video_title' );

		$class .= strpos( $className, 'is-style' ) === false ? ' is-style-' . self::$style : '';
		$class .= ' header-content-align-' . self::$settings['headerAlign'];
		$class .= ' block-alignment-' . self::$attributes['blockAlignment'];
		$class .= $hasImage && isset( $image['url'] ) ? '' : ' recipe-card-noimage';
		$class .= self::$settings['hide_header_image'] ? ' recipe-card-noimage' : '';
		$class .= '0' == WPZOOM_Settings::get( 'wpzoom_rcb_settings_print_show_image' ) ? ' recipe-card-noimage-print' : '';

		$pin_description = strip_tags( $recipeTitle );
		if ( 'recipe_summary' === WPZOOM_Settings::get( 'wpzoom_rcb_settings_pin_description' ) ) {
			$pin_description = strip_tags( $summary );
		}

		// compatibility with Tasty Pins plugin
		if ( ! empty( $tasty_pins_pinterest_text ) ) {
			$pin_description = strip_tags( $tasty_pins_pinterest_text );
		}

		$custom_author_name = $recipe_author_name;
		if ( ! empty( self::$settings['custom_author_name'] ) ) {
			$custom_author_name = self::$settings['custom_author_name'];
		}

		$RecipeCardClassName = implode( ' ', array( $class, $className ) );

		$styles = '';

		if ( '' != self::$settings['primary_color'] ) {
			if ( 'default' === self::$style ) {
				$styles = array(
					'background-color' => self::$settings['primary_color'],
				);
			} elseif ( 'newdesign' === self::$style ) {
				$styles = array(
					'background-color' => self::$settings['primary_color'],
					'box-shadow'       => '0 5px 40px ' . self::$settings['primary_color'] . '',
				);
			} elseif ( 'simple' === self::$style ) {
				$styles = array(
					'background-color' => self::$settings['primary_color'],
				);
			}
		}
		$printStyles = self::$helpers->render_styles_attributes( $styles );

		$recipe_card_image = '';

		if ( $hasImage && isset( $image['url'] ) ) {
			$img_id     = $image['id'];
			$src        = $image['url'];
			$alt        = ( $recipeTitle ? strip_tags( $recipeTitle ) : strip_tags( $recipe_title ) );
			$sizes      = isset( $image['sizes'] ) ? $image['sizes'] : array();
			$size       = self::get_recipe_image_size( $sizes, $src );
			$img_class  = '0' == WPZOOM_Settings::get( 'wpzoom_rcb_settings_print_show_image' ) ? 'no-print' : '';
			$img_class .= ' wpzoom-recipe-card-image';

			// Check if attachment image is from imported content
			// in this case we don't have attachment in our upload directory
			$upl_dir = wp_upload_dir();
			$findpos = strpos( $src, $upl_dir['baseurl'] );

			if ( $findpos === false ) {
				$attachment = sprintf(
					'<img src="%s" alt="%s" class="%s"/>',
					$src,
					$alt,
					trim( $img_class )
				);
			} else {
				$attachment = wp_get_attachment_image(
					$img_id,
					$size,
					false,
					array(
						'alt'   => $alt,
						'id'    => $img_id,
						'class' => trim( $img_class ),
					)
				);
			}

			$recipe_card_image = '<div class="recipe-card-image">
				<figure>
					' . $attachment . '
					<figcaption>
						' .
							( self::$settings['pin_btn'] ? self::get_pinterest_button( $image, $recipe_permalink, $pin_description ) : '' ) .
							( self::$settings['print_btn'] ? self::get_print_button(
								$id,
								array(
									'title' => __( 'Print directions...', 'wpzoom-recipe-card' ),
									'style' => $printStyles,
								)
							) : '' )
						. '
		            </figcaption>
				</figure>
			</div>';
		} elseif ( ! $hasImage && ! empty( $recipe_thumbnail_url ) ) {
			$img_id     = $recipe_thumbnail_id;
			$src        = $recipe_thumbnail_url;
			$alt        = ( $recipeTitle ? strip_tags( $recipeTitle ) : strip_tags( $recipe_title ) );
			$sizes      = isset( $image['sizes'] ) ? $image['sizes'] : array();
			$size       = self::get_recipe_image_size( $sizes, $src );
			$img_class  = '0' == WPZOOM_Settings::get( 'wpzoom_rcb_settings_print_show_image' ) ? 'no-print' : '';
			$img_class .= ' wpzoom-recipe-card-image';

			// Check if attachment image is from imported content
			// in this case we don't have attachment in our upload directory
			$upl_dir = wp_upload_dir();
			$findpos = strpos( $src, $upl_dir['baseurl'] );

			if ( $findpos === false ) {
				$attachment = sprintf(
					'<img src="%s" alt="%s" class="%s"/>',
					$src,
					$alt,
					trim( $img_class )
				);
			} else {
				$attachment = wp_get_attachment_image(
					$img_id,
					$size,
					false,
					array(
						'alt'   => $alt,
						'id'    => $img_id,
						'class' => trim( $img_class ),
					)
				);
			}

			$recipe_card_image = '<div class="recipe-card-image">
				<figure>
					' . sprintf( '<img id="%s" src="%s" alt="%s" class="%s"/>', $img_id, $src, $alt, trim( $img_class ) ) . '
					<figcaption>
						' .
							( self::$settings['pin_btn'] ? self::get_pinterest_button( array( 'url' => $recipe_thumbnail_url ), $recipe_permalink, $pin_description ) : '' ) .
							( self::$settings['print_btn'] ? self::get_print_button(
								$id,
								array(
									'title' => __( 'Print directions...', 'wpzoom-recipe-card' ),
									'style' => $printStyles,
								)
							) : '' )
						. '
		            </figcaption>
				</figure>
			</div>';
		}

		$recipe_card_heading = '
			<div class="recipe-card-heading">
				' . sprintf( '<h2 class="%s">%s</h2>', 'recipe-card-title', ( $recipeTitle ? strip_tags( $recipeTitle ) : strip_tags( $recipe_title ) ) ) .
				( self::$settings['displayAuthor'] ? '<span class="recipe-card-author">' . __( 'Recipe by', 'wpzoom-recipe-card' ) . ' ' . $custom_author_name . '</span>' : '' ) .
				( self::$settings['displayCourse'] ? self::get_recipe_terms( 'wpzoom_rcb_courses' ) : '' ) .
				( self::$settings['displayCuisine'] ? self::get_recipe_terms( 'wpzoom_rcb_cuisines' ) : '' ) .
				( self::$settings['displayDifficulty'] ? self::get_recipe_terms( 'wpzoom_rcb_difficulties' ) : '' ) .
			'</div>';

		$summary_text = '';
		if ( ! empty( $summary ) ) {
			$summary_class  = 'recipe-card-summary';
			$summary_class .= '0' == WPZOOM_Settings::get( 'wpzoom_rcb_settings_print_show_summary_text' ) ? ' no-print' : '';
			$summary_text   = sprintf(
				'<p class="%s">%s</p>',
				esc_attr( $summary_class ),
				$summary
			);
		}

		$details_content     = self::get_details_content( $details );
		$ingredients_content = self::get_ingredients_content( $ingredients );
		$steps_content       = self::get_steps_content( $steps );
		$recipe_card_video   = self::get_video_content();

		$strip_tags_notes = isset( $notes ) ? strip_tags( $notes ) : '';
		$notes            = str_replace( '<li></li>', '', $notes ); // remove empty list item
		$notes_content    = ! empty( $strip_tags_notes ) ?
			sprintf(
				'<div class="recipe-card-notes">
					<h3 class="notes-title">%s</h3>
					<ul class="recipe-card-notes-list">%s</ul>
				</div>',
				@$notesTitle,
				@$notes
			) : '';

		$footer_copyright = '';

		if ( '1' !== WPZOOM_Settings::get( 'wpzoom_rcb_settings_footer_copyright' ) ) {
			$footer_copyright = '<div class="footer-copyright">
		        	<p>' . __( 'Recipe Card plugin by ', 'wpzoom-recipe-card' ) . '
		        		<a href="https://www.wpzoom.com/plugins/recipe-card-blocks/" target="_blank" rel="nofollow noopener noreferrer">WPZOOM</a>
		        	</p>
		        </div>';
		}

		if ( 'simple' === self::$style ) {
			// Wrap recipe card heading and details content into one div
			$recipe_card_image   = '<div class="recipe-card-header-wrap">' . $recipe_card_image;
			$recipe_card_heading = '<div class="recipe-card-along-image">' . $recipe_card_heading;
			$details_content     = $details_content . '</div></div><!-- /.recipe-card-header-wrap -->';
		}

		$json_ld              = self::get_json_ld( $attributes );
		$structured_data_json = '';

		if ( ! empty( $json_ld ) ) {
			$structured_data_json = '<script type="application/ld+json">' . wp_json_encode( $json_ld, JSON_UNESCAPED_UNICODE ) . '</script>';
		}

		$block_content = sprintf(
			'<div class="%1$s" id="%2$s">%3$s</div>',
			esc_attr( trim( $RecipeCardClassName ) ),
			esc_attr( $id ),
			$recipe_card_image .
			$recipe_card_heading .
			$details_content .
			$summary_text .
			$ingredients_content .
			$steps_content .
			$recipe_card_video .
			$notes_content .
			$footer_copyright .
			$structured_data_json
		);

		return $block_content;
	}

	/**
	 * Returns the JSON-LD for a recipe-card block.
	 *
	 * @return array The JSON-LD representation of the recipe-card block.
	 */
	protected static function get_json_ld() {
		$attributes = self::$attributes;
		$tag_list   = wp_get_post_terms( self::$recipe->ID, 'post_tag', array( 'fields' => 'names' ) );
		$cat_list   = wp_get_post_terms( self::$recipe->ID, 'category', array( 'fields' => 'names' ) );

		$json_ld = array(
			'@context'           => 'https://schema.org',
			'@type'              => 'Recipe',
			'name'               => isset( $attributes['recipeTitle'] ) ? $attributes['recipeTitle'] : self::$recipe->post_title,
			'image'              => '',
			'description'        => isset( $attributes['summary'] ) ? $attributes['summary'] : self::$recipe->post_excerpt,
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
			'video'              => array(
				'@type'        => 'VideoObject',
				'name'         => isset( $attributes['recipeTitle'] ) ? $attributes['recipeTitle'] : self::$recipe->post_title,
				'description'  => isset( $attributes['summary'] ) ? $attributes['summary'] : self::$recipe->post_excerpt,
				'thumbnailUrl' => '',
				'contentUrl'   => '',
				'embedUrl'     => '',
				'uploadDate'   => get_the_time( 'c' ), // by default is post plublish date
				'duration'     => '',
			),
		);

		if ( ! empty( $attributes['recipeTitle'] ) ) {
			$json_ld['name'] = $attributes['recipeTitle'];
		}

		if ( ! empty( $attributes['summary'] ) ) {
			$json_ld['description'] = strip_tags( $attributes['summary'] );
		}

		if ( ! empty( $attributes['image'] ) && isset( $attributes['hasImage'] ) && $attributes['hasImage'] ) {
			$image_id         = isset( $attributes['image']['id'] ) ? $attributes['image']['id'] : 0;
			$image_sizes      = isset( $attributes['image']['sizes'] ) ? $attributes['image']['sizes'] : array();
			$image_sizes_url  = array(
				self::get_image_size_url( $image_id, 'full', $image_sizes ),
				self::get_image_size_url( $image_id, 'wpzoom-rcb-structured-data-1_1', $image_sizes ),
				self::get_image_size_url( $image_id, 'wpzoom-rcb-structured-data-4_3', $image_sizes ),
				self::get_image_size_url( $image_id, 'wpzoom-rcb-structured-data-16_9', $image_sizes ),
			);
			$json_ld['image'] = array_values( array_unique( $image_sizes_url ) );
		}

		if ( isset( $attributes['video'] ) && ! empty( $attributes['video'] ) && isset( $attributes['hasVideo'] ) && $attributes['hasVideo'] ) {
			$video      = $attributes['video'];
			$video_id   = isset( $video['id'] ) ? $video['id'] : 0;
			$video_type = isset( $video['type'] ) ? $video['type'] : '';

			if ( 'self-hosted' === $video_type ) {
				$video_attachment = get_post( $video_id );

				if ( $video_attachment ) {
					$video_data = wp_get_attachment_metadata( $video_id );
					$video_url  = wp_get_attachment_url( $video_id );

					$image_id      = get_post_thumbnail_id( $video_id );
					$thumb         = wp_get_attachment_image_src( $image_id, 'full' );
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
				}
			}

			if ( isset( $video['title'] ) && ! empty( $video['title'] ) ) {
				$json_ld['video']['name'] = esc_html( $video['title'] );
			}
			if ( isset( $video['caption'] ) && ! empty( $video['caption'] ) ) {
				$json_ld['video']['description'] = esc_html( $video['caption'] );
			}
			if ( isset( $video['description'] ) && ! empty( $video['description'] ) ) {
				if ( is_string( $video['description'] ) ) {
					$json_ld['video']['description'] = esc_html( $video['description'] );
				}
			}
			if ( isset( $video['poster']['url'] ) ) {
				$json_ld['video']['thumbnailUrl'] = esc_url( $video['poster']['url'] );

				if ( isset( $video['poster']['id'] ) ) {
					$poster_id                        = $video['poster']['id'];
					$poster_sizes_url                 = array(
						self::get_image_size_url( $poster_id, 'full' ),
						self::get_image_size_url( $poster_id, 'wpzoom-rcb-structured-data-1_1' ),
						self::get_image_size_url( $poster_id, 'wpzoom-rcb-structured-data-4_3' ),
						self::get_image_size_url( $poster_id, 'wpzoom-rcb-structured-data-16_9' ),
					);
					$json_ld['video']['thumbnailUrl'] = array_values( array_unique( $poster_sizes_url ) );
				}
			}
			if ( isset( $video['url'] ) ) {
				$json_ld['video']['contentUrl'] = esc_url( $video['url'] );

				if ( 'embed' === $video_type ) {
					$video_embed_url = $video['url'];

					$json_ld['video']['@type'] = 'VideoObject';

					if ( ! empty( $attributes['image'] ) && isset( $attributes['hasImage'] ) && $attributes['hasImage'] ) {
						$image_id                         = isset( $attributes['image']['id'] ) ? $attributes['image']['id'] : 0;
						$image_sizes                      = isset( $attributes['image']['sizes'] ) ? $attributes['image']['sizes'] : array();
						$image_sizes_url                  = array(
							self::get_image_size_url( $image_id, 'full', $image_sizes ),
							self::get_image_size_url( $image_id, 'wpzoom-rcb-structured-data-1_1', $image_sizes ),
							self::get_image_size_url( $image_id, 'wpzoom-rcb-structured-data-4_3', $image_sizes ),
							self::get_image_size_url( $image_id, 'wpzoom-rcb-structured-data-16_9', $image_sizes ),
						);
						$json_ld['video']['thumbnailUrl'] = array_values( array_unique( $image_sizes_url ) );
					}

					if ( strpos( $video['url'], 'youtu' ) ) {
						$video_embed_url = self::$helpers->convert_youtube_url_to_embed( $video['url'] );
					} elseif ( strpos( $video['url'], 'vimeo' ) ) {
						$video_embed_url = self::$helpers->convert_vimeo_url_to_embed( $video['url'] );
					}

					$json_ld['video']['embedUrl'] = esc_url( $video_embed_url );
				}
			}
			if ( isset( $video['date'] ) && 'embed' === $video_type ) {
				$json_ld['video']['uploadDate'] = $video['date'];
			}
		} else {

			// we have no video added
			// removed video attribute from json_ld array
			unset( $json_ld['video'] );
		}

		if ( ! empty( $attributes['course'] ) && self::$settings['displayCourse'] ) {
			$json_ld['recipeCategory'] = $attributes['course'];
		}

		if ( ! empty( $attributes['cuisine'] ) && self::$settings['displayCuisine'] ) {
			$json_ld['recipeCuisine'] = $attributes['cuisine'];
		}

		if ( ! empty( $attributes['keywords'] ) ) {
			$json_ld['keywords'] = $attributes['keywords'];
		}

		if ( ! empty( $attributes['details'] ) && is_array( $attributes['details'] ) ) {
			$details = array_filter( $attributes['details'], 'is_array' );

			foreach ( $details as $key => $detail ) {
				if ( $key === 0 ) {
					if ( ! empty( $detail['value'] ) && self::$settings['displayServings'] ) {
						if ( ! is_array( $detail['value'] ) ) {
							$yield = array(
								$detail['value'],
							);

							if ( isset( $detail['unit'] ) && ! empty( $detail['unit'] ) ) {
								$yield[] = $detail['value'] . ' ' . $detail['unit'];
							}
						} elseif ( isset( $detail['jsonValue'] ) ) {
							$yield = array(
								$detail['jsonValue'],
							);

							if ( isset( $detail['unit'] ) && ! empty( $detail['unit'] ) ) {
								$yield[] = $detail['value'] . ' ' . $detail['unit'];
							}
						}

						if ( isset( $yield ) ) {
							$json_ld['recipeYield'] = $yield;
						}
					}
				} elseif ( $key === 3 ) {
					if ( ! empty( $detail['value'] ) && self::$settings['displayCalories'] ) {
						if ( ! is_array( $detail['value'] ) ) {
							$json_ld['nutrition']['calories'] = $detail['value'] . ' cal';
						} elseif ( isset( $detail['jsonValue'] ) ) {
							$json_ld['nutrition']['calories'] = $detail['jsonValue'] . ' cal';
						}
					}
				} elseif ( $key === 1 ) {
					if ( ! empty( $detail['value'] ) && self::$settings['displayPrepTime'] ) {
						if ( ! is_array( $detail['value'] ) ) {
							$prepTime            = self::$structured_data_helpers->get_number_from_string( $detail['value'] );
							$json_ld['prepTime'] = self::$structured_data_helpers->get_period_time( $detail['value'] );
						} elseif ( isset( $detail['jsonValue'] ) ) {
							$prepTime            = self::$structured_data_helpers->get_number_from_string( $detail['jsonValue'] );
							$json_ld['prepTime'] = self::$structured_data_helpers->get_period_time( $detail['jsonValue'] );
						}
					}
				} elseif ( $key === 2 ) {
					if ( ! empty( $detail['value'] ) && self::$settings['displayCookingTime'] ) {
						if ( ! is_array( $detail['value'] ) ) {
							$cookTime            = self::$structured_data_helpers->get_number_from_string( $detail['value'] );
							$json_ld['cookTime'] = self::$structured_data_helpers->get_period_time( $detail['value'] );
						} elseif ( isset( $detail['jsonValue'] ) ) {
							$cookTime            = self::$structured_data_helpers->get_number_from_string( $detail['jsonValue'] );
							$json_ld['cookTime'] = self::$structured_data_helpers->get_period_time( $detail['jsonValue'] );
						}
					}
				} elseif ( $key === 8 ) {
					if ( ! empty( $detail['value'] ) && self::$settings['displayTotalTime'] ) {
						if ( ! is_array( $detail['value'] ) ) {
							$json_ld['totalTime'] = self::$structured_data_helpers->get_period_time( $detail['value'] );
						} elseif ( isset( $detail['jsonValue'] ) ) {
							$json_ld['totalTime'] = self::$structured_data_helpers->get_period_time( $detail['jsonValue'] );
						}
					}
				}
			}

			if ( empty( $json_ld['totalTime'] ) ) {
				if ( isset( $prepTime, $cookTime ) && ( $prepTime + $cookTime ) > 0 ) {
					$json_ld['totalTime'] = self::$structured_data_helpers->get_period_time( $prepTime + $cookTime );
				}
			}
		}

		if ( ! empty( $attributes['ingredients'] ) && is_array( $attributes['ingredients'] ) ) {
			$ingredients = array_filter( $attributes['ingredients'], 'is_array' );
			foreach ( $ingredients as $ingredient ) {
				$isGroup = isset( $ingredient['isGroup'] ) ? $ingredient['isGroup'] : false;

				if ( ! $isGroup ) {
					$json_ld['recipeIngredient'][] = self::$structured_data_helpers->get_ingredient_json_ld( $ingredient );
				}
			}
		}

		if ( ! empty( $attributes['steps'] ) && is_array( $attributes['steps'] ) ) {
			$steps          = array_filter( $attributes['steps'], 'is_array' );
			$groups_section = array();
			$instructions   = array();

			foreach ( $steps as $key => $step ) {
				$isGroup          = isset( $step['isGroup'] ) ? $step['isGroup'] : false;
				$parent_permalink = get_the_permalink( self::$recipe );

				if ( $isGroup ) {
					$groups_section[ $key ] = array(
						'@type'           => 'HowToSection',
						'name'            => '',
						'itemListElement' => array(),
					);
					if ( ! empty( $step['jsonText'] ) ) {
						$groups_section[ $key ]['name'] = $step['jsonText'];
					} else {
						$groups_section[ $key ]['name'] = self::$structured_data_helpers->step_text_to_JSON( $step['text'] );
					}
				}

				if ( count( $groups_section ) > 0 ) {
					end( $groups_section );
					$last_key = key( $groups_section );

					if ( ! $isGroup && $key > $last_key ) {
						$groups_section[ $last_key ]['itemListElement'][] = self::$structured_data_helpers->get_step_json_ld( $step, $parent_permalink );
					}
				} else {
					$instructions[] = self::$structured_data_helpers->get_step_json_ld( $step, $parent_permalink );
				}
			}

			$groups_section                = array_merge( $instructions, $groups_section );
			$json_ld['recipeInstructions'] = $groups_section;
		}

		return $json_ld;
	}

	public static function get_details_default() {
		return array(
			array(
				'id'      => self::$helpers->generateId( 'detail-item' ),
				'iconSet' => 'oldicon',
				'icon'    => 'food',
				'label'   => __( 'Servings', 'wpzoom-recipe-card' ),
				'unit'    => __( 'servings', 'wpzoom-recipe-card' ),
				'value'   => '4',
			),
			array(
				'id'      => self::$helpers->generateId( 'detail-item' ),
				'iconSet' => 'oldicon',
				'icon'    => 'clock',
				'label'   => __( 'Prep time', 'wpzoom-recipe-card' ),
				'unit'    => __( 'minutes', 'wpzoom-recipe-card' ),
				'value'   => '30',
			),
			array(
				'id'      => self::$helpers->generateId( 'detail-item' ),
				'iconSet' => 'foodicons',
				'icon'    => 'cooking-food-in-a-hot-casserole',
				'label'   => __( 'Cooking time', 'wpzoom-recipe-card' ),
				'unit'    => __( 'minutes', 'wpzoom-recipe-card' ),
				'value'   => '40',
			),
			array(
				'id'      => self::$helpers->generateId( 'detail-item' ),
				'iconSet' => 'foodicons',
				'icon'    => 'fire-flames',
				'label'   => __( 'Calories', 'wpzoom-recipe-card' ),
				'unit'    => __( 'kcal', 'wpzoom-recipe-card' ),
				'value'   => '300',
			),
			array(
				'id'      => self::$helpers->generateId( 'detail-item' ),
				'iconSet' => 'fa',
				'_prefix' => 'far',
				'icon'    => 'clock',
			),
			array(
				'id'      => self::$helpers->generateId( 'detail-item' ),
				'iconSet' => 'oldicon',
				'icon'    => 'chef-cooking',
			),
			array(
				'id'      => self::$helpers->generateId( 'detail-item' ),
				'iconSet' => 'oldicon',
				'icon'    => 'food-1',
			),
			array(
				'id'      => self::$helpers->generateId( 'detail-item' ),
				'iconSet' => 'fa',
				'_prefix' => 'fas',
				'icon'    => 'sort-amount-down',
			),
			array(
				'id'      => self::$helpers->generateId( 'detail-item' ),
				'iconSet' => 'fa',
				'_prefix' => 'far',
				'icon'    => 'clock',
				'label'   => __( 'Total time', 'wpzoom-recipe-card' ),
				'unit'    => __( 'minutes', 'wpzoom-recipe-card' ),
				'value'   => '0',
			),
		);
	}

	public static function get_ingredients_default() {
		return array(
			array(
				'id'   => self::$helpers->generateId( 'ingredient-item' ),
				'name' => array(),
			),
			array(
				'id'   => self::$helpers->generateId( 'ingredient-item' ),
				'name' => array(),
			),
			array(
				'id'   => self::$helpers->generateId( 'ingredient-item' ),
				'name' => array(),
			),
			array(
				'id'   => self::$helpers->generateId( 'ingredient-item' ),
				'name' => array(),
			),
		);
	}

	public static function get_steps_default() {
		return array(
			array(
				'id'   => self::$helpers->generateId( 'direction-step' ),
				'text' => array(),
			),
			array(
				'id'   => self::$helpers->generateId( 'direction-step' ),
				'text' => array(),
			),
			array(
				'id'   => self::$helpers->generateId( 'direction-step' ),
				'text' => array(),
			),
			array(
				'id'   => self::$helpers->generateId( 'direction-step' ),
				'text' => array(),
			),
		);
	}

	public static function get_details_content( array $details ) {
		$detail_items   = self::get_detail_items( $details );
		$details_class  = 'recipe-card-details';
		$details_class .= '0' == WPZOOM_Settings::get( 'wpzoom_rcb_settings_print_show_details' ) ? ' no-print' : '';

		if ( ! empty( $detail_items ) ) {
			return sprintf(
				'<div class="%s"><div class="details-items">%s</div></div>',
				esc_attr( $details_class ),
				$detail_items
			);
		} else {
			return '';
		}
	}

	public static function get_detail_items( array $details ) {
		$output = '';

		foreach ( $details as $index => $detail ) {
			$icon               = $label = $value = $unit = '';
			$isRestingTimeField = 4 === $index && isset( $detail['isRestingTimeField'] ) ? $detail['isRestingTimeField'] : false;

			if ( 0 === $index && self::$settings['displayServings'] != '1' ) {
				continue;
			} elseif ( 1 === $index && self::$settings['displayPrepTime'] != '1' ) {
				continue;
			} elseif ( 2 === $index && self::$settings['displayCookingTime'] != '1' ) {
				continue;
			} elseif ( 8 === $index && self::$settings['displayTotalTime'] != '1' ) {
				continue;
			} elseif ( 3 === $index && self::$settings['displayCalories'] != '1' ) {
				continue;
			} elseif ( ( 4 === $index || 5 === $index || 6 === $index || 7 === $index ) && empty( $detail['label'] ) ) {
				continue;
			}

			if ( ! empty( $detail['icon'] ) ) {
				$icon            = $detail['icon'];
				$iconSet         = isset( $detail['iconSet'] ) ? $detail['iconSet'] : 'oldicon';
				$_prefix         = isset( $detail['_prefix'] ) && ! empty( $detail['_prefix'] ) ? $detail['_prefix'] : $iconSet;
				$itemIconClasses = implode( ' ', array( 'detail-item-icon', $_prefix, $iconSet . '-' . $detail['icon'] ) );
				$styles          = array();

				if ( '' != self::$settings['icon_details_color'] ) {
					if ( 'default' === self::$style ) {
						$styles = array(
							'color' => self::$settings['icon_details_color'],
						);
					} elseif ( 'simple' === self::$style ) {
						$styles = array(
							'color' => self::$settings['icon_details_color'],
						);
					}
				}

				if ( '' != self::$settings['primary_color'] ) {
					if ( 'newdesign' === self::$style ) {
						$styles = array(
							'color' => self::$settings['primary_color'],
						);
					}
				}

				$iconStyles = self::$helpers->render_styles_attributes( $styles );

				$icon = sprintf(
					'<span class="%s" style="%s"></span>',
					$itemIconClasses,
					$iconStyles
				);
			}

			if ( ! empty( $detail['label'] ) ) {
				$label = sprintf(
					'<span class="detail-item-label">%s</span>',
					$detail['label']
				);
			}

			if ( ! empty( $detail['value'] ) ) {
				if ( ! is_array( $detail['value'] ) ) {
					$value = sprintf(
						'<p class="detail-item-value">%s</p>',
						$detail['value']
					);
				} elseif ( isset( $detail['jsonValue'] ) ) {
					$value = sprintf(
						'<p class="detail-item-value">%s</p>',
						$detail['jsonValue']
					);
				}
			}
			if ( ! empty( $detail['unit'] ) ) {
				$unit = sprintf(
					'<span class="detail-item-unit">%s</span>',
					$detail['unit']
				);
			}

			// convert minutes to hours for 'prep time', 'cook time' and 'total time'
			if ( 1 === $index || 2 === $index || $isRestingTimeField || 8 === $index ) {
				if ( ! empty( $detail['value'] ) ) {
					$converts = self::$helpers->convertMinutesToHours( $detail['value'], true );
					if ( ! empty( $converts ) ) {
						$value = $unit = '';
						if ( isset( $converts['hours'] ) ) {
							$value .= sprintf(
								'<p class="detail-item-value">%s</p>',
								$converts['hours']['value']
							);
							$value .= sprintf(
								'<span class="detail-item-unit">%s&nbsp;</span>',
								$converts['hours']['unit']
							);
						}
						if ( isset( $converts['minutes'] ) ) {
							$unit .= sprintf(
								'<p class="detail-item-value">%s</p>',
								$converts['minutes']['value']
							);
							$unit .= sprintf(
								'<span class="detail-item-unit">%s</span>',
								$converts['minutes']['unit']
							);
						}
					}
				}
			}

			$output .= sprintf(
				'<div class="%1$s %1$s-%2$s">%3$s</div>',
				'detail-item',
				$index,
				$icon . $label . $value . $unit
			);
		}

		return force_balance_tags( $output );
	}

	public static function get_ingredients_content( array $ingredients ) {
		$ingredient_items = self::get_ingredient_items( $ingredients );

		$listClassNames = implode( ' ', array( 'ingredients-list', 'layout-' . self::$settings['ingredientsLayout'] ) );

		return sprintf(
			'<div class="recipe-card-ingredients"><h3 class="ingredients-title">%s</h3><ul class="%s">%s</ul></div>',
			self::$attributes['ingredientsTitle'],
			$listClassNames,
			$ingredient_items
		);
	}

	public static function get_ingredient_items( array $ingredients ) {
		$output        = '';
		$strikethrough = WPZOOM_Settings::get( 'wpzoom_rcb_settings_ingredients_strikethrough' ) === '1' ? ' is-strikethrough-active' : '';

		foreach ( $ingredients as $index => $ingredient ) {
			$tick          = $name = '';
			$styles        = array();
			$isGroup       = isset( $ingredient['isGroup'] ) ? $ingredient['isGroup'] : false;
			$ingredient_id = isset( $ingredient['id'] ) ? 'wpzoom-rcb-' . $ingredient['id'] : '';

			if ( ! $isGroup ) {
				if ( 'newdesign' === self::$style || 'simple' === self::$style ) {
					if ( '' != self::$settings['primary_color'] ) {
						$styles = array(
							'border' => '2px solid ' . self::$settings['primary_color'],
						);
					}

					$tickStyles = self::$helpers->render_styles_attributes( $styles );

					$tick = sprintf(
						'<span class="tick-circle" style="%s"></span>',
						$tickStyles
					);
				} else {
					$tick = '<span class="tick-circle"></span>';
				}

				if ( ! empty( $ingredient['name'] ) ) {
					$name = sprintf( '<span class="wpzoom-rcb-ingredient-name">%s</span>', self::wrap_ingredient_name( $ingredient['name'] ) );

					$name    = sprintf(
						'<p class="ingredient-item-name%s">%s</p>',
						$strikethrough,
						$name
					);
					$output .= sprintf(
						'<li id="%s" class="ingredient-item">%s</li>',
						esc_attr( $ingredient_id ),
						$tick . $name
					);
				}
			} else {
				if ( ! empty( $ingredient['name'] ) ) {
					$name    = sprintf(
						'<strong class="ingredient-item-group-title">%s</strong>',
						self::wrap_ingredient_name( $ingredient['name'] )
					);
					$output .= sprintf(
						'<li id="%s" class="ingredient-item ingredient-item-group">%s</li>',
						esc_attr( $ingredient_id ),
						$tick . $name
					);
				}
			}
		}

		return force_balance_tags( $output );
	}

	public static function get_steps_content( array $steps ) {
		$direction_items = self::get_direction_items( $steps );

		$listClassNames = implode( ' ', array( 'directions-list' ) );

		return sprintf(
			'<div class="recipe-card-directions"><h3 class="directions-title">%s</h3><ul class="%s">%s</ul></div>',
			self::$attributes['directionsTitle'],
			$listClassNames,
			$direction_items
		);
	}

	public static function get_direction_items( array $steps ) {
		$output = '';

		foreach ( $steps as $index => $step ) {
			$text    = '';
			$isGroup = isset( $step['isGroup'] ) ? $step['isGroup'] : false;
			$step_id = isset( $step['id'] ) ? 'wpzoom-rcb-' . $step['id'] : '';

			if ( ! $isGroup ) {
				if ( ! empty( $step['text'] ) ) {
					$text    = self::wrap_direction_text( $step['text'] );
					$output .= sprintf(
						'<li id="%s" class="direction-step">%s</li>',
						esc_attr( $step_id ),
						$text
					);
				}
			} else {
				if ( ! empty( $step['text'] ) ) {
					$text    = sprintf(
						'<strong class="direction-step-group-title">%s</strong>',
						self::wrap_direction_text( $step['text'] )
					);
					$output .= sprintf(
						'<li id="%s" class="direction-step direction-step-group">%s</li>',
						esc_attr( $step_id ),
						$text
					);
				}
			}
		}

		return force_balance_tags( $output );
	}

	public static function get_recipe_terms( $taxonomy ) {
		$attributes = self::$attributes;
		$render     = true;

		$className = $label = $terms_output = '';

		extract( $attributes );

		$course     = isset( $course ) ? $course : array();
		$cuisine    = isset( $cuisine ) ? $cuisine : array();
		$difficulty = isset( $difficulty ) ? $difficulty : array();

		if ( 'wpzoom_rcb_courses' === $taxonomy ) {
			if ( empty( $course ) ) {
				$render = false;
			}
			$terms     = $course;
			$className = 'recipe-card-course';
			$label     = __( 'Course:', 'wpzoom-recipe-card' );
		} elseif ( 'wpzoom_rcb_cuisines' === $taxonomy ) {
			if ( empty( $cuisine ) ) {
				$render = false;
			}
			$terms     = $cuisine;
			$className = 'recipe-card-cuisine';
			$label     = __( 'Cuisine:', 'wpzoom-recipe-card' );
		} elseif ( 'wpzoom_rcb_difficulties' === $taxonomy ) {
			if ( empty( $difficulty ) ) {
				$render = false;
			}
			$terms     = $difficulty;
			$className = 'recipe-card-difficulty';
			$label     = __( 'Difficulty:', 'wpzoom-recipe-card' );
		}

		if ( $render ) {
			$terms_output = sprintf( '<span class="%s">%s <mark>%s</mark></span>', $className, $label, implode( ', ', $terms ) );
		}

		return $terms_output;
	}

	public static function wrap_direction_text( $nodes, $type = '' ) {
		$attributes = self::$attributes;

		if ( ! is_array( $nodes ) ) {
			return $nodes;
		}

		$output = '';
		foreach ( $nodes as $node ) {
			if ( ! is_array( $node ) ) {
				$output .= $node;
			} else {
				$type     = isset( $node['type'] ) ? $node['type'] : null;
				$children = isset( $node['props']['children'] ) ? $node['props']['children'] : null;

				$start_tag = $type ? "<$type>" : '';
				$end_tag   = $type ? "</$type>" : '';

				if ( 'img' === $type ) {
					$src = isset( $node['props']['src'] ) ? $node['props']['src'] : false;
					if ( $src ) {
						$attachment_id = isset( $node['key'] ) ? $node['key'] : 0;
						$alt           = isset( $node['props']['alt'] ) ? $node['props']['alt'] : '';
						$title         = isset( $node['props']['title'] ) ? $node['props']['title'] : ( isset( $attributes['recipeTitle'] ) ? $attributes['recipeTitle'] : self::$recipe->post_title );
						$class         = '0' == WPZOOM_Settings::get( 'wpzoom_rcb_settings_print_show_steps_image' ) ? 'no-print' : '';
						$class        .= ' direction-step-image';
						$img_style     = isset( $node['props']['style'] ) ? $node['props']['style'] : '';

						// Try to get attachment ID by image url if attribute `key` is not found in $node array
						if ( ! $attachment_id ) {
							$new_src = $src;

							$re = '/-\d+[Xx]\d+\./m';
							preg_match_all( $re, $src, $matches );

							// Remove image size from url to be able to get attachment id
							// e.g. .../wp-content/uploads/sites/30/2019/10/image-example-1-500x375.jpg
							// => .../wp-content/uploads/sites/30/2019/10/image-example-1.jpg
							if ( isset( $matches[0][0] ) ) {
								$new_src = str_replace( $matches[0][0], '.', $new_src );
							}

							// The found post ID, or 0 on failure.
							$attachment_id = attachment_url_to_postid( $new_src );

							if ( $attachment_id ) {
								$attachment = wp_get_attachment_image(
									$attachment_id,
									'wpzoom_rcb_block_step_image',
									false,
									array(
										'title' => $title,
										'alt'   => $alt,
										'class' => trim( $class ),
										'style' => self::parseTagStyle( $img_style ),
									)
								);
							}
						} else {
							$attachment = wp_get_attachment_image(
								$attachment_id,
								'wpzoom_rcb_block_step_image',
								false,
								array(
									'title' => $title,
									'alt'   => $alt,
									'class' => trim( $class ),
									'style' => self::parseTagStyle( $img_style ),
								)
							);
						}

						if ( $attachment ) {
							$start_tag = $attachment;
						} else {
							$start_tag = sprintf(
								'<%s src="%s" title="%s" alt="%s" class="%s" style="%s"/>',
								$type,
								$src,
								$title,
								$alt,
								trim( $class ),
								self::parseTagStyle( $img_style )
							);
						}
					} else {
						$start_tag = '';
					}
					$end_tag = '';
				} elseif ( 'a' === $type ) {
					$rel        = isset( $node['props']['rel'] ) ? $node['props']['rel'] : '';
					$aria_label = isset( $node['props']['aria-label'] ) ? $node['props']['aria-label'] : '';
					$href       = isset( $node['props']['href'] ) ? $node['props']['href'] : '#';
					$target     = isset( $node['props']['target'] ) ? $node['props']['target'] : '_blank';

					$start_tag = sprintf( '<%s rel="%s" aria-label="%s" href="%s" target="%s">', $type, $rel, $aria_label, $href, $target );
				} elseif ( 'br' === $type ) {
					$end_tag = '';
				}

				$output .= $start_tag . self::wrap_direction_text( $children, $type ) . $end_tag;
			}
		}

		return $output;
	}

	public static function wrap_ingredient_name( $nodes, $type = '' ) {
		$attributes = self::$attributes;

		if ( ! is_array( $nodes ) ) {
			return $nodes;
		}

		$output = '';
		foreach ( $nodes as $node ) {
			if ( ! is_array( $node ) ) {
				$output .= $node;
			} else {
				$type     = isset( $node['type'] ) ? $node['type'] : null;
				$children = isset( $node['props']['children'] ) ? $node['props']['children'] : null;

				$start_tag = $type ? "<$type>" : '';
				$end_tag   = $type ? "</$type>" : '';

				if ( 'img' === $type ) {
					$src = isset( $node['props']['src'] ) ? $node['props']['src'] : false;
					if ( $src ) {
						$alt       = isset( $node['props']['alt'] ) ? $node['props']['alt'] : '';
						$title     = isset( $node['props']['title'] ) ? $node['props']['title'] : ( isset( $attributes['recipeTitle'] ) ? $attributes['recipeTitle'] : self::$recipe->post_title );
						$class     = '0' == WPZOOM_Settings::get( 'wpzoom_rcb_settings_print_show_steps_image' ) ? 'no-print' : '';
						$class    .= ' direction-step-image';
						$img_style = isset( $node['props']['style'] ) ? $node['props']['style'] : '';

						$start_tag = sprintf( '<%s src="%s" title="%s" alt="%s" class="%s" style="%s"/>', $type, $src, $title, $alt, trim( $class ), self::parseTagStyle( $img_style ) );
					} else {
						$start_tag = '';
					}
					$end_tag = '';
				} elseif ( 'a' === $type ) {
					$rel        = isset( $node['props']['rel'] ) ? $node['props']['rel'] : '';
					$aria_label = isset( $node['props']['aria-label'] ) ? $node['props']['aria-label'] : '';
					$href       = isset( $node['props']['href'] ) ? $node['props']['href'] : '#';
					$target     = isset( $node['props']['target'] ) ? $node['props']['target'] : '_blank';

					$start_tag = sprintf( '<%s rel="%s" aria-label="%s" href="%s" target="%s">', $type, $rel, $aria_label, $href, $target );
				} elseif ( 'br' === $type ) {
					$end_tag = '';
				}

				$output .= $start_tag . self::wrap_ingredient_name( $children, $type ) . $end_tag;
			}
		}

		return $output;
	}

	/**
	 * Get HTML content for recipe video
	 *
	 * @since 2.1.1
	 * @return void
	 */
	public static function get_video_content() {
		$attributes = self::$attributes;
		$hasVideo   = isset( $attributes['hasVideo'] ) && $attributes['hasVideo'];
		$output     = '';

		if ( ! $hasVideo ) {
			return '';
		}

		$video          = isset( $attributes['video'] ) && ! empty( $attributes['video'] ) ? $attributes['video'] : array();
		$video_type     = isset( $video['type'] ) ? $video['type'] : '';
		$video_id       = isset( $video['id'] ) ? $video['id'] : 0;
		$video_url      = isset( $video['url'] ) ? esc_url( $video['url'] ) : '';
		$video_poster   = isset( $video['poster']['url'] ) ? esc_url( $video['poster']['url'] ) : '';
		$video_settings = isset( $video['settings'] ) ? $video['settings'] : array();

		if ( 'embed' === $video_type ) {
			$output = wp_oembed_get( $video_url );
		} elseif ( 'self-hosted' === $video_type ) {
			$attrs = array();
			foreach ( $video_settings as $attribute => $value ) {
				if ( $value ) {
					$attrs[] = $attribute;
				}
			}
			$attrs = implode( ' ', $attrs );

			if ( empty( $video_url ) && 0 !== $video_id ) {
				$video_url = wp_get_attachment_url( $video_id );
			}

			$output = sprintf(
				'<video %s src="%s" poster="%s"></video>',
				esc_attr( $attrs ),
				$video_url,
				$video_poster
			);
		}

		return sprintf( '<div class="recipe-card-video no-print"><h3 class="video-title">%s</h3>%s</div>', $attributes['videoTitle'], $output );
	}

	/**
	 * Add snippets at the top of post content
	 *
	 * @since 1.2.0
	 * @param string $content Main post content
	 * @return string HTML of post content
	 */
	public static function prepend_content_recipe_buttons( $content ) {
		$output = '';

		// Automatically display snippets at the top of post content.
		if ( '1' === WPZOOM_Settings::get( 'wpzoom_rcb_settings_display_snippets' ) ) {
			$custom_blocks = array(
				'wpzoom-recipe-card/block-jump-to-recipe',
				'wpzoom-recipe-card/block-print-recipe',
			);

			$output .= '<div class="wpzoom-recipe-card-buttons">';

			foreach ( $custom_blocks as $block_name ) {
				if ( 'wpzoom-recipe-card/block-jump-to-recipe' === $block_name ) {
					$attrs   = array(
						'id'   => self::$recipeBlockID,
						'text' => WPZOOM_Settings::get( 'wpzoom_rcb_settings_jump_to_recipe_text' ),
					);
					$block   = array(
						'blockName'    => $block_name,
						'attrs'        => $attrs,
						'innerBlocks'  => array(),
						'innerHTML'    => '',
						'innerContent' => array(),
					);
					$output .= render_block( $block );
				}
				if ( 'wpzoom-recipe-card/block-print-recipe' === $block_name ) {
					$attrs   = array(
						'id'   => self::$recipeBlockID,
						'text' => WPZOOM_Settings::get( 'wpzoom_rcb_settings_print_recipe_text' ),
					);
					$block   = array(
						'blockName'    => $block_name,
						'attrs'        => $attrs,
						'innerBlocks'  => array(),
						'innerHTML'    => '',
						'innerContent' => array(),
					);
					$output .= render_block( $block );
				}
			}

			$output .= '</div>';
		}

		$content = $output . $content;

		return $content;
	}

	/**
	 * Parse HTML tag styles
	 *
	 * @since 2.1.0
	 * @param string|array $style Tag styles to parse
	 * @return string             CSS styles
	 */
	public static function parseTagStyle( $styles ) {
		$css = '';
		if ( is_array( $styles ) ) {
			foreach ( $styles as $property => $value ) {
				$css .= $property . ': ' . $value . ';';
			}
		} elseif ( is_string( $styles ) ) {
			$css = $styles;
		}
		return $css;
	}

	/**
	 * Get HTML for print button
	 *
	 * @since 2.2.0
	 *
	 * @param array  $media        The recipe media image array which include 'url'
	 * @param string $url         The recipe permalink url
	 * @param string $description The description to display on pinterest board
	 * @param array  $attributes   Additional html attributes like ('style' => 'color: red; font-size: inherit')
	 * @return string
	 */
	public static function get_print_button( $content_id, $attributes = array() ) {
		if ( empty( $content_id ) ) {
			return '';
		}

		$print_classes = implode( ' ', array( 'wpzoom-recipe-card-print-link' ) );

		/**
		 * Add additional attributes to print button
		 * [serving-size, recipe-id]
		 *
		 * @since 2.6.3
		 */
		$servings   = isset( self::$attributes['details'][0]['value'] ) ? self::$attributes['details'][0]['value'] : 0;
		$attributes = array_merge( $attributes, array( 'data-servings-size' => $servings ) );

		if ( self::$recipe ) {
			$attributes = array_merge( $attributes, array( 'data-recipe-id' => self::$recipe->ID ) );
		}

		$attributes = apply_filters( 'wpzoom/recipe_card/print_button/attributes', $attributes );
		$atts       = self::$helpers->render_attributes( $attributes );

		$output = sprintf(
			'<div class="%s">
	            <a class="btn-print-link no-print" href="#%s" %s>
	            	<SVG class="wpzoom-rcb-icon-print-link" viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg">
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
			esc_attr( $print_classes ),
			esc_attr( $content_id ),
			$atts,
			__( 'Print', 'wpzoom-recipe-card' )
		);

		return $output;
	}

	/**
	 * Get HTML for pinterest button
	 *
	 * @since 2.2.0
	 *
	 * @param array        $media        The recipe media image array which include 'url'
	 * @param string       $url         The recipe permalink url
	 * @param string       $description The description to display on pinterest board
	 * @param array|string $attributes   Additional html attributes: array('style' => 'color: red; font-size: inherit') or
	 *                                   string 'style="color: red; font-size: inherit"'
	 * @return string
	 */
	public static function get_pinterest_button( $media, $url, $description = '', $attributes = '' ) {
		if ( ! isset( $media['url'] ) ) {
			return '';
		}

		$PinterestClasses = implode( ' ', array( 'wpzoom-recipe-card-pinit' ) );
		$pinitURL         = 'https://www.pinterest.com/pin/create/button/?url=' . esc_url( $url ) . '&media=' . esc_url( $media['url'] ) . '&description=' . esc_html( $description ) . '';

		$atts = self::$helpers->render_attributes( $attributes );

		$output = sprintf(
			'<div class="%s">
	            <a class="btn-pinit-link no-print" data-pin-do="buttonPin" href="%s" data-pin-custom="true" %s>
	            	<SVG class="wpzoom-rcb-icon-pinit-link" enable-background="new 0 0 30 30" height="30px" id="Pinterest" version="1.1" viewBox="0 0 30 30" width="30px" xmlns="http://www.w3.org/2000/svg">
 	            	    <Path class="wpzoom-rcb-pinit-icon" d="M16,0C7.813,0,3,6.105,3,11c0,2.964,2,6,3,6s2,0,2-1s-2-2-2-5c0-4.354,4.773-8,10-8c4.627,0,7,3.224,7,7  c0,4.968-2.735,9-6,9c-1.803,0-3.433-1.172-3-3c0.519-2.184,1-2,2-6c0.342-1.368-0.433-3-2-3c-1.843,0-4,1.446-4,4c0,1.627,1,3,1,3  s-2.245,7.863-2.576,9.263C7.766,26.049,6.938,30,7.938,30S10,28,12,23c0.295-0.738,1-3,1-3c0.599,1.142,3.14,2,5,2  c5.539,0,9-5.24,9-12C27,4.888,22.58,0,16,0z" />
 	            	</SVG>
	            	<span>%s</span>
	            </a>
	        </div>',
			esc_attr( $PinterestClasses ),
			esc_url( $pinitURL ),
			$atts,
			__( 'Pin', 'wpzoom-recipe-card' )
		);

		return $output;
	}

	/**
	 * Get recipe card image size name
	 *
	 * @since 2.6.3
	 *
	 * @return object
	 */
	public static function get_recipe_image_size( $sizes, $src ) {
		if ( is_array( $sizes ) && ! empty( $sizes ) ) {
			foreach ( $sizes as $size_name => $size_attrs ) {
				if ( isset( $size_attrs['url'] ) ) {
					if ( $size_attrs['url'] === $src ) {
						$size = $size_name;
					}
				} elseif ( isset( $size_attrs['source_url'] ) ) {
					if ( $size_attrs['source_url'] === $src ) {
						$size = $size_name;
					}
				}
			}
		}

		if ( ! isset( $size ) ) {
			$size = 'full';

			if ( 'simple' === self::$style ) {
				$size = 'wpzoom-rcb-block-header-square';
			}
			if ( 'default' === self::$style || 'newdesign' === self::$style ) {
				$size = 'wpzoom-rcb-block-header';
			}
		}

		return $size;
	}

	/**
	 * Get image url by specified $size
	 *
	 * @since 2.6.3
	 *
	 * @param  string|number $image_id      The image id to get url
	 * @param  string        $size                 The specific image size
	 * @param  array         $image_sizes          Available image sizes for specified image id
	 * @return string                       The image url
	 */
	public static function get_image_size_url( $image_id, $size = 'full', $image_sizes = array() ) {
		if ( isset( $image_sizes[ $size ] ) ) {
			if ( isset( $image_sizes[ $size ]['url'] ) ) {
				$image_url = $image_sizes[ $size ]['url'];
			} elseif ( isset( $image_sizes[ $size ]['source_url'] ) ) {
				$image_url = $image_sizes[ $size ]['source_url'];
			}
		}

		if ( function_exists( 'fly_get_attachment_image_src' ) ) {
			$thumb = fly_get_attachment_image_src( $image_id, $size );

			if ( $thumb ) {
				$image_url = isset( $thumb[0] ) ? $thumb[0] : $thumb['src'];
			}
		}

		if ( ! isset( $image_url ) ) {
			$thumb     = wp_get_attachment_image_src( $image_id, $size );
			$image_url = $thumb && isset( $thumb[0] ) ? $thumb[0] : '';
		}

		return $image_url;
	}

	/**
	 * Check whether a url is a blob url.
	 *
	 * @since 2.6.3
	 *
	 * @param string $url   The URL.
	 *
	 * @return boolean      Is the url a blob url?
	 */
	public static function is_blob_URL( $url ) {
		if ( ! is_string( $url ) || empty( $url ) ) {
			return false;
		}
		return strpos( $url, 'blob:' ) === 0;
	}
}
