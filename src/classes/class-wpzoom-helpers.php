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
	public function generateId( string $prefix = '' ) {
		return $prefix !== '' ? uniqid( $prefix . '-' ) : uniqid();
	}

	public function render_styles_attributes( array $styles ) {
		$render = '';
		foreach ( $styles as $property => $value ) {
			$render .= sprintf( '%s: %s; ', $property, $value );
		}
		return trim( $render );
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
		preg_match('/\d+/', $string, $matches);
		return $matches ? $matches[0] : 0;
	}

	public function convertMinutesToHours( $minutes, $returnArray = false ) {
		$output = '';
		$time = $this->getNumberFromString( $minutes );
		$hours = floor( $time / 60 );
		$mins = ( $time % 60 );

		if ( ! $time ) {
			return $minutes;
		}

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
