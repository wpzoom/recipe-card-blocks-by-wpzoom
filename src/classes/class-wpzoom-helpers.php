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
		return uniqid( $prefix );
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
}
