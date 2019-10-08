<?php
/**
 * Structured Data Helpers functions
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
class WPZOOM_Structured_Data_Helpers {
	/**
	 * Returns the JSON-LD for a ingredient's name.
	 *
	 * @param array $ingredient The attributes of a ingredient.
	 *
	 * @return array The JSON-LD representation of the ingredient name.
	 */
	public function get_ingredient_json_ld( array $ingredient ) {
		$ingredient_json_ld = '';

		if ( ! empty( $ingredient['jsonName'] ) ) {
			$ingredient_json_ld = $ingredient['jsonName'];
		} else {
			$ingredient_json_ld = $this->ingredient_name_to_JSON( $ingredient['name'] );
		}

		return $ingredient_json_ld;
	}

	/**
	 * Backward compatibility with ingredients that don't have jsonName attribute.
	 *
	 * @param array|string $ingredient_name The ingredient name.
	 *
	 * @return string The json name generated from array.
	 */
	public function ingredient_name_to_JSON( $ingredient_name, $jsonName = '' ) {
		if ( is_array( $ingredient_name ) ) {
			foreach ( $ingredient_name as $name ) {
				if ( ! is_null( $name ) ) {
					if ( is_array( $name ) && isset( $name['props']['children'] ) && is_array( $text['props']['children'] ) ) {
						$jsonName = $this->ingredient_name_to_JSON( $name['props']['children'], $jsonName );
					} elseif ( is_string( $name ) ) {
						$jsonName .= $name;
					}
				}
			}
		} elseif ( is_string( $ingredient_name ) ) {
			$jsonName = $ingredient_name;
		}

		return $jsonName;
	}

	/**
	 * Returns the JSON-LD for a step's description.
	 *
	 * @param array $step The attributes of a step(-section).
	 *
	 * @return array The JSON-LD representation of the step's description.
	 */
	public function get_step_json_ld( array $step ) {
		$step_json_ld = array(
			'@type' => 'HowToStep',
		);

		if ( ! empty( $step['jsonText'] ) ) {
			$step_json_ld['text'] = $step['jsonText'];
		} else {
			$step_json_ld['text'] = $this->step_text_to_JSON( $step['text'] );
		}

		return $step_json_ld;
	}

	/**
	 * Backward compatibility with steps that don't have jsonText attribute.
	 *
	 * @param array|string $step_text The step text.
	 *
	 * @return string The json text generated from array.
	 */
	public function step_text_to_JSON( $step_text, $jsonText = '' ) {
		if ( is_array( $step_text ) ) {
			foreach ( $step_text as $text ) {
				if ( ! is_null( $text ) ) {
					if ( is_array( $text ) && isset( $text['props']['children'] ) && is_array( $text['props']['children'] ) ) {
						$jsonText = $this->step_text_to_JSON( $text['props']['children'], $jsonText );
					} elseif ( is_string( $text ) ) {
						$jsonText .= $text;
					}
				}
			}
		} elseif ( is_string( $step_text ) ) {
			$jsonText = $step_text;
		}

		return $jsonText;
	}

	/**
	 * Returns the date value in ISO 8601 date format.
	 *
	 * @param string $value The string value with number and unit.
	 *
	 * @return string A textual string indicating a time period in ISO 8601 time interval format.
	 */
	public function get_period_time( $value ) {
		$time = $this->get_number_from_string( $value );
		$hours = floor( $time / 60 );
		$days = round( $hours / 24 );
		$minutes = ( $time % 60 );
		$period = 'PT';

		if ( $days ) {
			$hours = ( $hours % 24 );
			$period .= $days . 'D';
		}

		if ( $hours ) {
			$period .= $hours . 'H';
		}

		if ( $minutes ) {
			$period .= $minutes . 'M';
		}

		return $period;
	}

	/**
	 * Returns the number from string.
	 *
	 * @param string $string The string value with number and unit.
	 *
	 * @return number The first number matched from string.
	 */
	public function get_number_from_string( $string ) {
		if ( is_numeric( $string ) ) {
			return $string;
		}

		$re = '/\d+/s';
		preg_match($re, $string, $matches);

		return isset($matches[0]) ? (int)$matches[0] : 0;
	}
}
