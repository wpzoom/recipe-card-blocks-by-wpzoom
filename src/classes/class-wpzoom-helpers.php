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
    public function generateId( $prefix = '') {
		return $prefix !== '' ? uniqid( $prefix . '-' ) : uniqid();
	}

	public function render_attributes( $attributes ) {
		if ( empty( $attributes ) )
			return '';

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
		if ( empty( $styles ) )
			return '';

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
		$style = WPZOOM_Settings::get('wpzoom_rcb_settings_template');
		if ( strpos( $className, 'is-style' ) !== false ) {
			preg_match('/is-style-(\S*)/', $className, $matches);
			$style = $matches ? $matches[1] : $style;
		}

		return $style;
	}

	public function parse_block_settings( $attrs ) {
		$settings = isset( $attrs['settings'][0] ) ? $attrs['settings'][0] : array();
		$blockStyle = isset($attrs['className']) ? $this->get_block_style( $attrs['className'] ) : WPZOOM_Settings::get('wpzoom_rcb_settings_template');

		if ( !isset( $settings['headerAlign'] ) ) {
			$settings['headerAlign'] = WPZOOM_Settings::get('wpzoom_rcb_settings_heading_content_align');
		}
		if ( $blockStyle === 'simple' ) {
			$settings['headerAlign'] = 'left';
		}
		
		if ( !isset( $settings['custom_author_name'] ) ) {
			$settings['custom_author_name'] = WPZOOM_Settings::get('wpzoom_rcb_settings_author_custom_name');
		}
		if ( !isset( $settings['displayServings'] ) ) {
			$settings['displayServings'] = WPZOOM_Settings::get('wpzoom_rcb_settings_display_servings');
		}
		if ( !isset( $settings['displayPrepTime'] ) ) {
			$settings['displayPrepTime'] = WPZOOM_Settings::get('wpzoom_rcb_settings_display_preptime');
		}
		if ( !isset( $settings['displayCookingTime'] ) ) {
			$settings['displayCookingTime'] = WPZOOM_Settings::get('wpzoom_rcb_settings_display_cookingtime');
		}
		if ( !isset( $settings['displayTotalTime'] ) ) {
			$settings['displayTotalTime'] = WPZOOM_Settings::get('wpzoom_rcb_settings_display_totaltime');
		}
		if ( !isset( $settings['displayCalories'] ) ) {
			$settings['displayCalories'] = WPZOOM_Settings::get('wpzoom_rcb_settings_display_calories');
		}
		if ( !isset( $settings['ingredientsLayout'] ) ) {
			$settings['ingredientsLayout'] = '1-column';
		}

		if ( 'default' === $blockStyle ) {
			$settings['primary_color'] = '#222222';
		}
		elseif ( 'newdesign' === $blockStyle ) {
			$settings['primary_color'] = '#FFA921';
		}
		elseif ( 'simple' === $blockStyle ) {
			$settings['primary_color'] = '';
		}

		if ( !isset( $settings['print_btn'] ) ) {
			$settings['print_btn'] = WPZOOM_Settings::get('wpzoom_rcb_settings_display_print');
		}
		if ( !isset( $settings['pin_btn'] ) ) {
			$settings['pin_btn'] = WPZOOM_Settings::get('wpzoom_rcb_settings_display_pin');
		}
		if ( !isset( $settings['pin_has_custom_image'] ) ) {
			$settings['pin_has_custom_image'] = false;
		}
		if ( !isset( $settings['pin_custom_image'] ) ) {
			$settings['pin_custom_image'] = array();
		}
		if ( !isset( $settings['hide_header_image'] ) ) {
			$settings['hide_header_image'] = false;
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
		preg_match('/\d+/', $string, $matches);
		return $matches ? $matches[0] : 0;
	}

	public function convertMinutesToHours( $minutes, $returnArray = false ) {
		$output = '';
		$time = $this->getNumberFromString( $minutes );

		if ( ! $time ) {
			return $minutes;
		}
		
		$hours = floor( $time / 60 );
		$mins = ( $time % 60 );

		if ( $returnArray ) {
			if ( $hours ) {
				$array['hours']['value'] = $hours;
				$array['hours']['unit'] = _n( "hour", "hours", (int)$hours, "wpzoom-recipe-card" );
			}
			if ( $mins ) {
				$array['minutes']['value'] = $mins;
				$array['minutes']['unit'] = _n( "minute", "minutes", (int)$mins, "wpzoom-recipe-card" );
			}

			return $array;
		}

		if ( $hours ) {
			$output = $hours + ' ' + _n( "hour", "hours", (int)$hours, "wpzoom-recipe-card" );
		}

		if ( $mins ) {
			$output .= ' ' + $mins . ' ' + _n( "minute", "minutes", (int)$mins, "wpzoom-recipe-card" );
		}

		return $output;
	}

	public function convert_youtube_url_to_embed( $url ) {
		$embed_url = preg_replace("/\s*[a-zA-Z\/\/:\.]*youtube.com\/watch\?v=([a-zA-Z0-9\-_]+)([a-zA-Z0-9\/\*\-\_\?\&\;\%\=\.]*)/i","https://www.youtube.com/embed/$1?feature=oembed", $url );
		return $embed_url;
	}

	public function convert_vimeo_url_to_embed( $url ) {
		$embed_url = preg_replace("/\s*[a-zA-Z\/\/:\.]*vimeo.com\/([a-zA-Z0-9\-_]+)([a-zA-Z0-9\/\*\-\_\?\&\;\%\=\.]*)/i","https://player.vimeo.com/video/$1", $url );
		return $embed_url;
	}
}


/* Exclude specific images from being used in Jetpack's Lazy Load
=========================================== */
add_filter( 'jetpack_lazy_images_blacklisted_classes', 'wpzoom_rcb_exclude_custom_classes_from_lazy_load', 999, 1 );

if ( ! function_exists( 'wpzoom_rcb_exclude_custom_classes_from_lazy_load' ) ) {
    function wpzoom_rcb_exclude_custom_classes_from_lazy_load( $classes ) {
        $classes[] = 'wpzoom-recipe-card-image';
        $classes[] = 'direction-step-image';
        return $classes;
    }
}

