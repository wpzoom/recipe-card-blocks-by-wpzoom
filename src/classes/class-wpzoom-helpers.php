<?php
/**
 * Class Helpers functions
 *
 * @since   1.1.0
 * @package WPZOOM_Recipe_Card_Blocks
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class for helper functions for structured data render.
 */
class WPZOOM_Helpers {
	public function generateId( $prefix = '' ) {
		return $prefix !== '' ? uniqid( $prefix . '-' ) : uniqid();
	}

	/**
	 * Fix tasty links conflict when convert '<' & '>' to unicode.
	 *
	 * @since 2.7.5
	 *
	 * @param string $content The content which should parse.
	 * @return string
	 */
	public static function fix_content_tasty_links_conflict( $content ) {
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

	public function render_attributes( $attributes ) {
		if ( empty( $attributes ) ) {
			return '';
		}

		$render = '';

		if ( is_array( $attributes ) ) {
			foreach ( $attributes as $property => $value ) {
				$render .= sprintf( '%s="%s" ', $property, esc_attr( $value ) );
			}
		} elseif ( is_string( $attributes ) ) {
			$render = $attributes;
		}
		return trim( $render );
	}

	public function render_styles_attributes( $styles ) {
		if ( empty( $styles ) ) {
			return '';
		}

		$render = '';

		if ( is_array( $styles ) ) {
			foreach ( $styles as $property => $value ) {
				$render .= sprintf( '%s: %s; ', $property, $value );
			}
		} elseif ( is_string( $styles ) ) {
			$render = $styles;
		}
		return trim( $render );
	}

	public function get_block_style( $className ) {
		$style = WPZOOM_Settings::get( 'wpzoom_rcb_settings_template' );
		if ( strpos( $className, 'is-style' ) !== false ) {
			preg_match( '/is-style-(\S*)/', $className, $matches );
			$style = $matches ? $matches[1] : $style;
		}

		return $style;
	}

	public function parse_block_settings( $attrs ) {
		$settings   = isset( $attrs['settings'][0] ) ? $attrs['settings'][0] : array();
		$blockStyle = isset( $attrs['className'] ) ? $this->get_block_style( $attrs['className'] ) : WPZOOM_Settings::get( 'wpzoom_rcb_settings_template' );

		if ( ! isset( $settings['headerAlign'] ) ) {
			$settings['headerAlign'] = WPZOOM_Settings::get( 'wpzoom_rcb_settings_heading_content_align' );
		}

		if ( ! isset( $settings['custom_author_name'] ) ) {
			$settings['custom_author_name'] = WPZOOM_Settings::get( 'wpzoom_rcb_settings_author_custom_name' );
		}
		if ( ! isset( $settings['displayAuthor'] ) ) {
			$settings['displayAuthor'] = WPZOOM_Settings::get( 'wpzoom_rcb_settings_display_author' );
		}
		if ( ! isset( $settings['displayCourse'] ) ) {
			$settings['displayCourse'] = WPZOOM_Settings::get( 'wpzoom_rcb_settings_display_course' );
		}
		if ( ! isset( $settings['displayCuisine'] ) ) {
			$settings['displayCuisine'] = WPZOOM_Settings::get( 'wpzoom_rcb_settings_display_cuisine' );
		}
		if ( ! isset( $settings['displayDifficulty'] ) ) {
			$settings['displayDifficulty'] = WPZOOM_Settings::get( 'wpzoom_rcb_settings_display_difficulty' );
		}
		if ( ! isset( $settings['displayServings'] ) ) {
			$settings['displayServings'] = WPZOOM_Settings::get( 'wpzoom_rcb_settings_display_servings' );
		}
		if ( ! isset( $settings['displayPrepTime'] ) ) {
			$settings['displayPrepTime'] = WPZOOM_Settings::get( 'wpzoom_rcb_settings_display_preptime' );
		}
		if ( ! isset( $settings['displayCookingTime'] ) ) {
			$settings['displayCookingTime'] = WPZOOM_Settings::get( 'wpzoom_rcb_settings_display_cookingtime' );
		}
		if ( ! isset( $settings['displayTotalTime'] ) ) {
			$settings['displayTotalTime'] = WPZOOM_Settings::get( 'wpzoom_rcb_settings_display_totaltime' );
		}
		if ( ! isset( $settings['displayCalories'] ) ) {
			$settings['displayCalories'] = WPZOOM_Settings::get( 'wpzoom_rcb_settings_display_calories' );
		}
		if ( ! isset( $settings['ingredientsLayout'] ) ) {
			$settings['ingredientsLayout'] = '1-column';
		}

		if ( 'default' === $blockStyle ) {
			$settings['primary_color'] = '#222222';
		} elseif ( 'newdesign' === $blockStyle ) {
			$settings['primary_color'] = '#FFA921';
		} elseif ( 'simple' === $blockStyle ) {
			$settings['primary_color'] = '';
		}

		if ( ! isset( $settings['print_btn'] ) ) {
			$settings['print_btn'] = WPZOOM_Settings::get( 'wpzoom_rcb_settings_display_print' );
		}
		if ( ! isset( $settings['pin_btn'] ) ) {
			$settings['pin_btn'] = WPZOOM_Settings::get( 'wpzoom_rcb_settings_display_pin' );
		}
		if ( ! isset( $settings['yamidoo_btn'] ) ) {
			$settings['yamidoo_btn'] = filter_var( WPZOOM_Settings::get( 'wpzoom_rcb_settings_display_yamidoo_ask' ), FILTER_VALIDATE_BOOLEAN );
		}
		if ( ! isset( $settings['pin_has_custom_image'] ) ) {
			$settings['pin_has_custom_image'] = false;
		}
		if ( ! isset( $settings['pin_custom_image'] ) ) {
			$settings['pin_custom_image'] = array();
		}
		if ( ! isset( $settings['hide_header_image'] ) ) {
			$settings['hide_header_image'] = false;
		}
		if ( ! isset( $settings['foodLabels'] ) ) {
			$settings['foodLabels'] = array();
		}
		if ( ! isset( $settings['displayFoodLabels'] ) ) {
			$settings['displayFoodLabels'] = WPZOOM_Settings::get( 'wpzoom_rcb_settings_display_food_labels' );
		}
		if ( ! isset( $settings['locationToShowFoodLabels'] ) ) {
			$settings['locationToShowFoodLabels'] = WPZOOM_Settings::get( 'wpzoom_rcb_settings_location_to_show_food_labels' );
		}
		if ( ! isset( $settings['pinterest_bg_color'] ) ) {
			$settings['pinterest_bg_color'] = '#C62122';
		}
		if ( ! isset( $settings['pinterest_text_color'] ) ) {
			$settings['pinterest_text_color'] = '#FFFFFF';
		}
		if ( ! isset( $settings['print_bg_color'] ) ) {
			$settings['print_bg_color'] = '#222222';
		}
		if ( ! isset( $settings['print_text_color'] ) ) {
			$settings['print_text_color'] = '#FFFFFF';
		}
		if ( ! isset( $settings['icon_details_color'] ) ) {
			$settings['icon_details_color'] = '#6d767f';
		}

		return $settings;
	}

	public function omit( array $array, array $paths ) {
		foreach ( $array as $key => $value ) {
			if ( in_array( $key, $paths ) ) {
				unset( $array[ $key ] );
			}
		}

		return $array;
	}

	public function getNumberFromString( $string ) {
		if ( ! is_string( $string ) ) {
			return false;
		}
		preg_match( '/\d+/', $string, $matches );
		return $matches ? $matches[0] : 0;
	}

	public function convertMinutesToHours( $minutes, $returnArray = false ) {
		$output = '';
		$time   = $this->getNumberFromString( $minutes );

		if ( ! $time ) {
			return $minutes;
		}

		$hours = floor( $time / 60 );
		$mins  = ( $time % 60 );

		if ( $returnArray ) {
			if ( $hours ) {
				$array['hours']['value'] = $hours;
				$array['hours']['unit']  = _n( 'hour', 'hours', (int) $hours, 'recipe-card-blocks-by-wpzoom' );
			}
			if ( $mins ) {
				$array['minutes']['value'] = $mins;
				$array['minutes']['unit']  = _n( 'minute', 'minutes', (int) $mins, 'recipe-card-blocks-by-wpzoom' );
			}

			return $array;
		}

		if ( $hours ) {
			$output = $hours . ' ' . _n( 'hour', 'hours', (int) $hours, 'recipe-card-blocks-by-wpzoom' );
		}

		if ( $mins ) {
			$output .= ' ' . $mins . ' ' . _n( 'minute', 'minutes', (int) $mins, 'recipe-card-blocks-by-wpzoom' );
		}

		return $output;
	}

	public function convert_youtube_url_to_embed( $url ) {
		$embed_url = preg_replace( '/\s*[a-zA-Z\/\/:\.]*youtube.com\/watch\?v=([a-zA-Z0-9\-_]+)([a-zA-Z0-9\/\*\-\_\?\&\;\%\=\.]*)/i', 'https://www.youtube.com/embed/$1?feature=oembed', $url );
		return $embed_url;
	}

	public function convert_vimeo_url_to_embed( $url ) {
		$embed_url = preg_replace( '/\s*[a-zA-Z\/\/:\.]*vimeo.com\/([a-zA-Z0-9\-_]+)([a-zA-Z0-9\/\*\-\_\?\&\;\%\=\.]*)/i', 'https://player.vimeo.com/video/$1', $url );
		return $embed_url;
	}

	public static function deserialize_block_attributes( $encoded_attributes ) {

		if( ! $encoded_attributes ) {
			return $encoded_attributes;
		}
		
		$encoded_attributes = preg_replace( '/u002d/', '-', $encoded_attributes );
		$encoded_attributes = preg_replace( '/u003c/', '<', $encoded_attributes );
		$encoded_attributes = preg_replace( '/u003e/', '>', $encoded_attributes );
		$encoded_attributes = preg_replace( '/u0026/', '&', $encoded_attributes );
		// Regex: /\\"/
		$encoded_attributes = preg_replace( '/u0022/', '"', $encoded_attributes );
		$encoded_attributes = preg_replace( '/u00a0/', ' ', $encoded_attributes );
	 
		return $encoded_attributes;
	}

	/**
	 * Get the amount and unit stored in an ingredient's own fields.
	 *
	 * Recipes made with the Pro version (or imported) keep the amount and unit
	 * separate from the name. Returns nothing when the name already starts with
	 * them, as in recipes imported by older versions, so they aren't shown twice.
	 *
	 * @since 3.6.3
	 *
	 * @param array $ingredient Ingredient attributes.
	 * @return array { amount: string, unit: string }, empty strings when none.
	 */
	public static function get_ingredient_amount( $ingredient ) {
		$none = array( 'amount' => '', 'unit' => '' );

		if ( ! is_array( $ingredient ) || ! empty( $ingredient['isGroup'] ) || empty( $ingredient['parse'] ) || ! is_array( $ingredient['parse'] ) ) {
			return $none;
		}

		$amount = isset( $ingredient['parse']['amount'] ) ? trim( (string) $ingredient['parse']['amount'] ) : '';
		$unit   = isset( $ingredient['parse']['unit'] ) ? trim( (string) $ingredient['parse']['unit'] ) : '';

		if ( '' === $amount && '' === $unit ) {
			return $none;
		}

		$text = ! empty( $ingredient['jsonName'] ) && is_string( $ingredient['jsonName'] ) ? $ingredient['jsonName'] : '';
		$text = preg_replace( '/[\s\x{00A0}]+/u', ' ', trim( html_entity_decode( wp_strip_all_tags( $text ), ENT_QUOTES, 'UTF-8' ) ) );

		$prefix = str_replace( ' ', '\s*', preg_quote( trim( $amount . ' ' . $unit ), '/' ) );
		if ( '' !== $text && preg_match( '/^' . $prefix . '(\s|$)/iu', $text ) ) {
			return $none;
		}

		return array( 'amount' => $amount, 'unit' => $unit );
	}

	public static function deserialize_block_attributes_array( $encoded_attributes ) {

		$converted_attributes = array(); 

		if( is_string( $encoded_attributes ) ) {

			$encoded_attributes = self::deserialize_block_attributes( $encoded_attributes );
			return $encoded_attributes;
		
		}

		$encoded_attributes = call_user_func_array( 'self::deserialize_block_attributes', $encoded_attributes );

		return $encoded_attributes;
	}

	/**
	 * Parent post of a recipe post.
	 *
	 * The _wpzoom_rcb_parent_post_id meta links a wpzoom_rcb post to the post
	 * whose recipe card it was created from. Importers and post-duplication
	 * plugins copy that meta onto regular posts as well, where it points at an
	 * unrelated recipe; reading it there sends the card's title, ratings, print
	 * and edit links to that recipe. Only a recipe post can have a parent.
	 *
	 * @since 3.7.0
	 * @param int $post_id Post ID.
	 * @return int Parent post ID, or 0 when the post is not a recipe post, has no parent or is its own parent.
	 */
	public static function get_recipe_parent_id( $post_id ) {

		$post_id = (int) $post_id;

		if ( ! $post_id || 'wpzoom_rcb' !== get_post_type( $post_id ) ) {
			return 0;
		}

		$parent_id = (int) get_post_meta( $post_id, '_wpzoom_rcb_parent_post_id', true );

		if ( ! $parent_id || $parent_id === $post_id ) {
			return 0;
		}

		return $parent_id;

	}

	/**
	 * Meta key on a recipe post holding the key of the Recipe Card it mirrors.
	 *
	 * @since 3.7.0
	 */
	const CARD_KEY_META = '_wpzoom_rcb_card_key';

	/**
	 * Request cache for get_recipe_twins().
	 *
	 * @var array<int, int[]>
	 */
	private static $twins_cache = array();

	/**
	 * Recipe posts created from the Recipe Card blocks of a post.
	 *
	 * Order, which also decides the post's primary recipe (the first one, which
	 * its ratings and the "Edit Recipe" link belong to):
	 * 1. Recipe posts without a card key, newest first. Before 7.5.0 the
	 *    newest was the one kept in sync and opened by "Edit Recipe", and
	 *    many sites have older duplicates from repeated imports.
	 * 2. Recipe posts with a card key (7.5.0+), oldest first, so adding or
	 *    reordering cards never changes the primary one.
	 *
	 * @since 3.7.0
	 * @param int  $parent_id Post holding the Recipe Card blocks.
	 * @param bool $fresh     Skip the request cache (after creating a recipe post).
	 * @return int[] Recipe post IDs.
	 */
	public static function get_recipe_twins( $parent_id, $fresh = false ) {

		$parent_id = (int) $parent_id;

		if ( ! $parent_id ) {
			return array();
		}

		if ( ! $fresh && isset( self::$twins_cache[ $parent_id ] ) ) {
			return self::$twins_cache[ $parent_id ];
		}

		$ids = get_posts(
			array(
				'post_type'    => 'wpzoom_rcb',
				'post_status'  => 'any',
				'numberposts'  => -1,
				'fields'       => 'ids',
				'orderby'      => array(
					'date' => 'DESC',
					'ID'   => 'DESC',
				),
				'post__not_in' => array( $parent_id ),
				'meta_key'     => '_wpzoom_rcb_parent_post_id',
				'meta_value'   => $parent_id,
			)
		);

		$keyless = array();
		$keyed   = array();

		foreach ( array_unique( array_map( 'intval', $ids ) ) as $id ) {
			if ( '' === (string) get_post_meta( $id, self::CARD_KEY_META, true ) ) {
				$keyless[] = $id;
			} else {
				$keyed[] = $id;
			}
		}

		sort( $keyed );

		self::$twins_cache[ $parent_id ] = array_merge( $keyless, $keyed );

		return self::$twins_cache[ $parent_id ];

	}

	/**
	 * Forget cached twins of a post.
	 *
	 * @since 3.7.0
	 * @param int $parent_id Post ID; 0 forgets all.
	 */
	public static function flush_recipe_twins_cache( $parent_id = 0 ) {

		if ( $parent_id ) {
			unset( self::$twins_cache[ (int) $parent_id ] );
		} else {
			self::$twins_cache = array();
		}

	}

	/**
	 * Recipe post mirroring a Recipe Card of a post.
	 *
	 * With a card key: the recipe post created for that card. Without one: the
	 * post's primary recipe post (see get_recipe_twins()), which is what
	 * single-card posts and every post saved before 7.5.0 have.
	 *
	 * @since 3.7.0
	 * @param int    $parent_id Post holding the Recipe Card.
	 * @param string $card_key  The card's `recipeKey` attribute, if any.
	 * @return int Recipe post ID, or 0 when there is none.
	 */
	public static function get_recipe_twin_id( $parent_id, $card_key = '' ) {

		$twins = self::get_recipe_twins( $parent_id );

		if ( empty( $twins ) ) {
			return 0;
		}

		if ( '' === (string) $card_key ) {
			return $twins[0];
		}

		foreach ( $twins as $twin_id ) {
			if ( (string) get_post_meta( $twin_id, self::CARD_KEY_META, true ) === (string) $card_key ) {
				return $twin_id;
			}
		}

		return 0;

	}

}


/*
 Exclude specific images from being used in Jetpack's Lazy Load
=========================================== */
add_filter( 'jetpack_lazy_images_blacklisted_classes', 'wpzoom_rcb_exclude_custom_classes_from_lazy_load', 999, 1 );

if ( ! function_exists( 'wpzoom_rcb_exclude_custom_classes_from_lazy_load' ) ) {
	function wpzoom_rcb_exclude_custom_classes_from_lazy_load( $classes ) {
		$classes[] = 'wpzoom-recipe-card-image';
		$classes[] = 'direction-step-image';
		return $classes;
	}
}

