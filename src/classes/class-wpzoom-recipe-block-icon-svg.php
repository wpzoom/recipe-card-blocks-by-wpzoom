<?php
/**
 * Recipe Block Icon SVG Registry
 *
 * Provides SVG markup for detail icons so they can be rendered without loading icon fonts.
 * Falls back to font-based icons when no SVG is in the registry (backward compatibility).
 *
 * Same class and registry file as the PRO plugin (there since 5.0.0).
 *
 * @since   3.8.0
 * @package WPZOOM_Recipe_Card_Blocks
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WPZOOM_Recipe_Block_Icon_SVG
 */
class WPZOOM_Recipe_Block_Icon_SVG {

	/**
	 * Cached registry (iconSet => iconName => svg markup).
	 *
	 * @var array|null
	 */
	private static $registry = null;

	/**
	 * Allowed SVG tags and attributes for sanitization.
	 *
	 * @var array
	 */
	private static $allowed_svg = array(
		'svg'  => array(
			'xmlns'        => true,
			'viewbox'      => true,
			'viewBox'      => true,
			'width'        => true,
			'height'       => true,
			'fill'         => true,
			'class'        => true,
			'aria-hidden'  => true,
			'role'         => true,
		),
		'path' => array(
			'd'     => true,
			'fill'  => true,
			'class' => true,
		),
		'circle' => array(
			'cx'    => true,
			'cy'    => true,
			'r'     => true,
			'fill'  => true,
		),
		'rect'  => array(
			'x'      => true,
			'y'      => true,
			'width'  => true,
			'height' => true,
			'fill'   => true,
		),
	);

	/**
	 * Load the SVG registry from JSON file.
	 *
	 * @return array Registry array keyed by iconSet, then icon name.
	 */
	private static function get_registry() {
		if ( self::$registry !== null ) {
			return self::$registry;
		}

		$path = WPZOOM_RCB_PLUGIN_DIR . 'src/assets/icons/svg-registry.json';
		if ( ! file_exists( $path ) || ! is_readable( $path ) ) {
			self::$registry = array();
			return self::$registry;
		}

		$json = file_get_contents( $path );
		if ( $json === false ) {
			self::$registry = array();
			return self::$registry;
		}

		$decoded = json_decode( $json, true );
		self::$registry = is_array( $decoded ) ? $decoded : array();
		return self::$registry;
	}

	/**
	 * Get the lookup key for an icon (for FA, uses prefix + " fa-" + icon name).
	 *
	 * @param string      $icon_set Icon set (oldicon, foodicons, dashicons, fa).
	 * @param string      $icon     Icon name (e.g. 'clock', or for FA the suffix like 'clock').
	 * @param string|null $prefix   For Font Awesome: 'fas' or 'far'. Ignored for other sets.
	 * @return string Key used in the registry for that set.
	 */
	private static function get_lookup_key( $icon_set, $icon, $prefix = null ) {
		$reg   = self::get_registry();
		$icons = isset( $reg[ $icon_set ] ) && is_array( $reg[ $icon_set ] ) ? $reg[ $icon_set ] : array();

		if ( $icon_set === 'fa' ) {
			// Cards saved without a Font Awesome style ("fa") used the solid glyph.
			$prefixes = in_array( $prefix, array( 'fas', 'far' ), true ) ? array( $prefix ) : array( 'fas', 'far' );

			foreach ( $prefixes as $fa_prefix ) {
				if ( isset( $icons[ $fa_prefix . ' fa-' . $icon ] ) ) {
					return $fa_prefix . ' fa-' . $icon;
				}
			}

			return $prefixes[0] . ' fa-' . $icon;
		}

		// A few entries are stored with their set name in front ("oldicon-chef-cooking").
		if ( ! isset( $icons[ $icon ] ) && isset( $icons[ $icon_set . '-' . $icon ] ) ) {
			return $icon_set . '-' . $icon;
		}

		return $icon;
	}

	/**
	 * Check whether the registry has an SVG for the given icon.
	 *
	 * @param string      $icon_set Icon set (oldicon, foodicons, dashicons, fa).
	 * @param string      $icon     Icon name.
	 * @param string|null $prefix   For Font Awesome: 'fas' or 'far'. Optional.
	 * @return bool True if SVG exists in registry.
	 */
	public static function has_svg( $icon_set, $icon, $prefix = null ) {
		if ( empty( $icon_set ) || empty( $icon ) ) {
			return false;
		}
		$reg = self::get_registry();
		if ( ! isset( $reg[ $icon_set ] ) || ! is_array( $reg[ $icon_set ] ) ) {
			return false;
		}
		$key = self::get_lookup_key( $icon_set, $icon, $prefix );
		return isset( $reg[ $icon_set ][ $key ] ) && is_string( $reg[ $icon_set ][ $key ] ) && $reg[ $icon_set ][ $key ] !== '';
	}

	/**
	 * Get sanitized SVG markup for an icon, or null if not in registry.
	 *
	 * @param string      $icon_set Icon set (oldicon, foodicons, dashicons, fa).
	 * @param string      $icon     Icon name.
	 * @param string|null $prefix   For Font Awesome: 'fas' or 'far'. Optional.
	 * @return string|null Sanitized SVG string, or null if not found.
	 */
	public static function get_svg( $icon_set, $icon, $prefix = null ) {
		if ( ! self::has_svg( $icon_set, $icon, $prefix ) ) {
			return null;
		}
		$reg   = self::get_registry();
		$key   = self::get_lookup_key( $icon_set, $icon, $prefix );
		$markup = $reg[ $icon_set ][ $key ];
		return self::with_default_size( self::sanitize_svg( $markup ) );
	}

	/**
	 * Gives an SVG a width and height when it has none, so it never renders at the browser's
	 * 300x150 default where the card's stylesheet is missing (feeds, emails, late CSS).
	 *
	 * @since 3.8.0
	 * @param string $svg SVG markup.
	 * @return string
	 */
	private static function with_default_size( $svg ) {
		if ( ! is_string( $svg ) || '' === $svg ) {
			return '';
		}

		if ( ! preg_match( '/<svg\b[^>]*\swidth\s*=/i', $svg ) ) {
			$svg = preg_replace( '/<svg\b/i', '<svg width="20" height="20"', $svg, 1 );
		}

		return $svg;
	}

	/**
	 * Sanitize SVG markup (allowlist of tags and attributes).
	 *
	 * @param string $svg Raw SVG string.
	 * @return string Sanitized SVG.
	 */
	public static function sanitize_svg( $svg ) {
		if ( ! is_string( $svg ) || $svg === '' ) {
			return '';
		}
		return wp_kses( $svg, self::$allowed_svg );
	}

	/**
	 * Get all registered icon keys (iconSet.icon or for FA iconSet.prefix fa-icon) for conditional font loading.
	 *
	 * @return array List of keys like ['oldicon.clock', 'dashicons.calendar', 'fa.fas fa-clock'].
	 */
	public static function get_registered_keys() {
		$reg   = self::get_registry();
		$keys  = array();
		foreach ( $reg as $set => $icons ) {
			if ( ! is_array( $icons ) ) {
				continue;
			}
			foreach ( array_keys( $icons ) as $name ) {
				$keys[] = $set . '.' . $name;
			}
		}
		return $keys;
	}

	/**
	 * Check if an icon (iconSet + icon + optional prefix) is in the registry.
	 *
	 * @param string      $icon_set Icon set.
	 * @param string      $icon     Icon name.
	 * @param string|null $prefix   For FA: fas/far.
	 * @return bool
	 */
	public static function is_registered( $icon_set, $icon, $prefix = null ) {
		return self::has_svg( $icon_set, $icon, $prefix );
	}

	/**
	 * Get SVG markup for an icon from Elementor-style detail_item_icon (library + value).
	 *
	 * @param string $library Icon library (e.g. dashicons, foodicons, oldicon, fa).
	 * @param string $value   Icon value (e.g. dashicons-clock, fas fa-clock).
	 * @return string|null Sanitized SVG markup or null if not in registry.
	 */
	public static function get_svg_for_elementor( $library, $value ) {
		if ( empty( $library ) || empty( $value ) ) {
			return null;
		}
		$prefix = null;
		$icon   = $value;
		if ( $library === 'fa' && preg_match( '/^(fas|far)\s+fa-(.+)$/', $value, $m ) ) {
			$prefix = $m[1];
			$icon   = $m[2];
		} else {
			$lib_prefix = $library . '-';
			if ( strpos( $value, $lib_prefix ) === 0 ) {
				$icon = substr( $value, strlen( $lib_prefix ) );
			}
		}
		return self::get_svg( $library, $icon, $prefix );
	}
}
