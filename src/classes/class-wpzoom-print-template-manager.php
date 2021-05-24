<?php
/**
 * Print Template Manager
 *
 * @since   2.7.2
 * @package WPZOOM_Recipe_Card_Blocks
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WPZOOM_Print_Template_Manager' ) ) {
	/**
	 * Main WPZOOM_Print_Template_Manager Class.
	 *
	 * @since 2.7.2
	 */
	class WPZOOM_Print_Template_Manager {
		/**
		 * This plugin's instance.
		 *
		 * @var WPZOOM_Print_Template_Manager
		 * @since 2.7.2
		 */
		private static $instance;

		/**
		 * Provides singleton instance.
		 *
		 * @since 2.7.2
		 * @return self instance
		 */
		public static function instance() {
			if ( null === self::$instance ) {
				self::$instance = new WPZOOM_Print_Template_Manager();
			}

			return self::$instance;
		}

		/**
		 * Class instance Helpers.
		 *
		 * @var WPZOOM_Helpers
		 * @since 2.7.2
		 */
		public static $helpers;

		/**
		 * The Constructor.
		 */
		private function __construct() {
			self::$helpers = new WPZOOM_Helpers();
		}

		public static function get_template( $attributes, $recipe, $blockType ) {
			switch ( $blockType ) {
				case 'recipe-card':
					return self::recipe_card_block( $attributes, $recipe );
					break;
				case 'ingredients-block':
					return self::ingredients_block( $attributes );
					break;
				case 'directions-block':
					return self::directions_block( $attributes );
					break;
				default:
					return $content;
					break;
			}
		}

		public static function recipe_card_block( $attributes, $recipe ) {
			$attributes = self::$helpers->omit( $attributes, array( 'toInsert', 'activeIconSet', 'showModal', 'searchIcon', 'icons' ) );
			// Import variables into the current symbol table from an array
			extract( $attributes );

			$class = 'wpzoom-print-recipe-card-block';

			// Recipe post variables
			$recipe_id            = $recipe->ID;
			$recipe_title         = get_the_title( $recipe );
			$recipe_thumbnail_url = get_the_post_thumbnail_url( $recipe );
			$recipe_thumbnail_id  = get_post_thumbnail_id( $recipe );
			$recipe_permalink     = get_the_permalink( $recipe );
			$recipe_author_name   = get_the_author_meta( 'display_name', $recipe->post_author );
			$attachment_id        = isset( $image['id'] ) ? $image['id'] : $recipe_thumbnail_id;

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
			$settings = self::$helpers->parse_block_settings( $attributes );

			WPZOOM_Recipe_Card_Block::$recipeBlockID = isset( $id ) ? esc_attr( $id ) : 'wpzoom-recipe-card';
			WPZOOM_Recipe_Card_Block::$attributes    = $attributes;
			WPZOOM_Recipe_Card_Block::$settings      = $settings;

			WPZOOM_Recipe_Card_Block::$attributes['ingredientsTitle'] = isset( $ingredientsTitle ) ? $ingredientsTitle : WPZOOM_Settings::get( 'wpzoom_rcb_settings_ingredients_title' );
			WPZOOM_Recipe_Card_Block::$attributes['directionsTitle']  = isset( $directionsTitle ) ? $directionsTitle : WPZOOM_Settings::get( 'wpzoom_rcb_settings_steps_title' );
			WPZOOM_Recipe_Card_Block::$attributes['videoTitle']       = isset( $videoTitle ) ? $videoTitle : WPZOOM_Settings::get( 'wpzoom_rcb_settings_video_title' );

			$class .= $hasImage && isset( $image['url'] ) ? '' : ' recipe-card-noimage';
			$class .= $settings['hide_header_image'] ? ' recipe-card-noimage' : '';
			$class .= '0' == WPZOOM_Settings::get( 'wpzoom_rcb_settings_print_show_image' ) ? ' recipe-card-noimage-print' : '';

			$custom_author_name = $recipe_author_name;
			if ( ! empty( $settings['custom_author_name'] ) ) {
				$custom_author_name = $settings['custom_author_name'];
			}

			$RecipeCardClassName = implode( ' ', array( $class, $className ) );

			$recipe_card_image = '';

			if ( '1' === WPZOOM_Settings::get( 'wpzoom_rcb_settings_print_show_image' ) ) {
				if ( $hasImage && isset( $image['url'] ) ) {
					$img_id    = $image['id'];
					$src       = $image['url'];
					$alt       = ( $recipeTitle ? strip_tags( $recipeTitle ) : strip_tags( $recipe_title ) );
					$img_class = ' wpzoom-recipe-card-image';

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
							'wpzoom-rcb-block-header-square',
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
                        </figure>
                    </div>';
				} elseif ( ! $hasImage && ! empty( $recipe_thumbnail_url ) ) {
					$img_id    = $recipe_thumbnail_id;
					$src       = $recipe_thumbnail_url;
					$alt       = ( $recipeTitle ? strip_tags( $recipeTitle ) : strip_tags( $recipe_title ) );
					$img_class = ' wpzoom-recipe-card-image';

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
							'wpzoom-rcb-block-header-square',
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
                        </figure>
                    </div>';
				}
			}

			$recipe_card_heading = '
                <div class="recipe-card-heading">
                    ' . sprintf( '<h2 class="%s">%s</h2>', 'recipe-card-title', ( $recipeTitle ? strip_tags( $recipeTitle ) : strip_tags( $recipe_title ) ) ) .
					( $settings['displayAuthor'] ? '<span class="recipe-card-author">' . __( 'Recipe by', 'wpzoom-recipe-card' ) . ' ' . $custom_author_name . '</span>' : '' ) .
					'<div class="recipe-card-terms">' .
					( $settings['displayCourse'] ? WPZOOM_Recipe_Card_Block::get_recipe_terms( 'wpzoom_rcb_courses' ) : '' ) .
					( $settings['displayCuisine'] ? WPZOOM_Recipe_Card_Block::get_recipe_terms( 'wpzoom_rcb_cuisines' ) : '' ) .
					( $settings['displayDifficulty'] ? WPZOOM_Recipe_Card_Block::get_recipe_terms( 'wpzoom_rcb_difficulties' ) : '' ) .
					'</div>' .
				'</div>';

			$summary_text = '';

			if ( '1' === WPZOOM_Settings::get( 'wpzoom_rcb_settings_print_show_summary_text' ) ) {
				if ( ! empty( $summary ) ) {
					$summary_class = 'recipe-card-summary';
					$summary_text  = sprintf(
						'<p class="%s">%s</p>',
						esc_attr( $summary_class ),
						$summary
					);
				}
			}

			$details_content     = WPZOOM_Recipe_Card_Block::get_details_content( $details );
			$ingredients_content = WPZOOM_Recipe_Card_Block::get_ingredients_content( $ingredients );
			$steps_content       = WPZOOM_Recipe_Card_Block::get_steps_content( $steps );

			$strip_tags_notes = isset( $notes ) ? strip_tags( $notes ) : '';
			$notes            = isset( $notes ) ? str_replace( '<li></li>', '', $notes ) : ''; // remove empty list item
			$notesTitle       = isset( $notesTitle ) ? $notesTitle : WPZOOM_Settings::get( 'wpzoom_rcb_settings_notes_title' );
			$notes_content    = ! empty( $strip_tags_notes ) ?
				sprintf(
					'<div class="recipe-card-notes">
                        <h3 class="notes-title">%s</h3>
                        <ul class="recipe-card-notes-list">%s</ul>
                    </div>',
					$notesTitle,
					$notes
				) : '';

			$footer_copyright = '<div class="footer-copyright">
                <p>' . __( 'Recipe Card plugin by ', 'wpzoom-recipe-card' ) . '
                    <a href="https://www.wpzoom.com/plugins/recipe-card-blocks/" target="_blank" rel="nofollow noopener noreferrer">WPZOOM</a>
                </p>
            </div>';

			// Wrap recipe card heading and details content into one div
			$recipe_card_image   = '<div class="recipe-card-header-wrap">' . $recipe_card_image;
			$recipe_card_heading = '<div class="recipe-card-along-image">' . $recipe_card_heading;
			$details_content     = $details_content . '</div></div><!-- /.recipe-card-header-wrap -->';

			$block_content = sprintf(
				'<div class="%1$s" id="%2$s">%3$s</div>',
				esc_attr( trim( $RecipeCardClassName ) ),
				esc_attr( WPZOOM_Recipe_Card_Block::$recipeBlockID ),
				$recipe_card_image .
				$recipe_card_heading .
				$details_content .
				$summary_text .
				$ingredients_content .
				$steps_content .
				$notes_content .
				$footer_copyright
			);

			return $block_content;
		}

		public static function ingredients_block( $attributes ) {
			$attributes = self::$helpers->omit( $attributes, array() );
			// Import variables into the current symbol table from an array
			extract( $attributes );

			$class = 'wpzoom-print-ingredients-block';

			$title               = isset( $title ) ? $title : WPZOOM_Settings::get( 'wpzoom_rcb_settings_ingredients_title' );
			$items               = isset( $items ) ? $items : array();
			$ingredients_content = WPZOOM_Ingredients_Block::get_ingredients_content( $items );

			$block_content = sprintf(
				'<div id="%s" class="%s">
                    <h3 class="ingredients-title">%s</h3>
                    %s
                </div>',
				esc_attr( $id ),
				esc_attr( $class ),
				esc_html( $title ),
				$ingredients_content
			);

			return $block_content;
		}

		public static function directions_block( $attributes ) {
			// Import variables into the current symbol table from an array
			extract( $attributes );

			$class = 'wpzoom-print-directions-block';

			$title         = isset( $title ) ? $title : WPZOOM_Settings::get( 'wpzoom_rcb_settings_steps_title' );
			$steps         = isset( $steps ) ? $steps : array();
			$steps_content = WPZOOM_Steps_Block::get_steps_content( $steps );

			$block_content = sprintf(
				'<div id="%s" class="%s">
                    <h3 class="directions-title">%s</h3>
                    %s
                </div>',
				esc_attr( $id ),
				esc_attr( $class ),
				esc_html( $title ),
				$steps_content
			);

			return $block_content;
		}
	}
}

WPZOOM_Print_Template_Manager::instance();
