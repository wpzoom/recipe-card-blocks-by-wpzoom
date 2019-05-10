<?php
/**
 * Class Helpers functions
 *
 * @since   1.1.0
 * @package WPZOOM Recipe Card Block
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

	public function render_styles_attributes( array $styles ) {
		$render = '';
		foreach ( $styles as $property => $value ) {
			$render .= sprintf( '%s: %s; ', $property, $value );
		}
		return trim( $render );
	}

	public function get_block_style( $className ) {
		$style = 'default';
		if ( strpos( $className, 'is-style' ) !== false ) {
			preg_match('/is-style-(\S*)/', $className, $matches);
			$style = $matches ? $matches[1] : $style;
		}

		return $style;
	}

	public function parse_block_settings( array $attrs ) {
		$settings = isset( $attrs['settings'][0] ) ? $attrs['settings'][0] : array();
		$blockStyle = isset($attrs['className']) ? $this->get_block_style( $attrs['className'] ) : 'default';

		if ( $blockStyle === 'default' ) {
			$settings['primary_color'] = '#222';
		} elseif ( $blockStyle === 'newdesign' ) {
			$settings['primary_color'] = '#FFA921';
		}

		if ( !isset( $settings['pin_has_custom_image'] ) ) {
			$settings['pin_has_custom_image'] = false;
		}
		elseif ( !isset( $settings['pin_custom_image'] ) ) {
			$settings['pin_custom_image'] = array();
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

