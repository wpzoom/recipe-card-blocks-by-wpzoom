<?php
/**
 * Handle the recipe printing.
 *
 * @since   2.6.3
 * @package WPZOOM_Recipe_Card_Blocks
 */
class WPZOOM_Print {

	/**
	 * Register actions and filters.
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'print_page' ) );
	}

	/**
	 * The block a Print button belongs to. Recipe Cards are matched by the
	 * element ID they render with, so each card of a post with several prints
	 * itself (3.7.0, as PRO 7.5.0); other blocks by their `id` attribute.
	 * Nested blocks are searched at any depth; the first match wins.
	 *
	 * @since 3.7.0
	 * @param array[] $blocks       Parsed blocks.
	 * @param string  $block_name   Block to print.
	 * @param string  $requested_id Element ID the Print button sent.
	 * @param int     $host_id      Post the blocks belong to.
	 * @return array|null The block's attributes, or null.
	 */
	private static function find_print_block( $blocks, $block_name, $requested_id, $host_id, &$card_index = 0 ) {

		foreach ( $blocks as $block ) {
			if ( $block['blockName'] === $block_name ) {
				$attrs = is_array( $block['attrs'] ) ? $block['attrs'] : array();

				if ( 'wpzoom-recipe-card/block-recipe-card' === $block_name ) {
					// Same order as WPZOOM_Helpers::get_post_recipe_cards().
					$element_id = WPZOOM_Recipe_Card_Block::get_card_dom_id( $host_id, $attrs, $card_index );
					$card_index++;
				} else {
					$element_id = isset( $attrs['id'] ) ? $attrs['id'] : 'wpzoom-recipe-card';
				}

				if ( $element_id === $requested_id ) {
					return $attrs;
				}
			}

			if ( ! empty( $block['innerBlocks'] ) ) {
				$found = self::find_print_block( $block['innerBlocks'], $block_name, $requested_id, $host_id, $card_index );
				if ( null !== $found ) {
					return $found;
				}
			}
		}

		return null;
	}

	public static function print_page() {
		preg_match( '/[\/\?]wpzoom_rcb_print[\/=](\d+)([\/\?\&].*)?$/', $_SERVER['REQUEST_URI'], $print_url );
		$print_atts = array(
			'recipe-id' => isset( $print_url[1] ) ? $print_url[1] : 0,
		);

		// Exit early if we don't have post.
		if ( ! $print_atts['recipe-id'] ) {
			return;
		}

		// We have some params, let's check
		// extract params (e.g. /?servings=4&prep-time=15)
		if ( isset( $print_url[2] ) && is_string( $print_url[2] ) ) {
			preg_match_all( '/[\?|\&]([^=]+)\=([^&]+)/', $print_url[2], $params );

			if ( isset( $params[1] ) ) {
				foreach ( $params[1] as $key => $value ) {
					if ( 'block-type' === $value ) {
						$print_atts['block-type'] = isset( $params[2][ $key ] ) ? $params[2][ $key ] : 'recipe-card';
					} elseif ( 'servings' === $value ) {
						$print_atts['servings'] = isset( $params[2][ $key ] ) ? $params[2][ $key ] : 0;
					} elseif ( 'block-id' === $value ) {
						$print_atts['block-id'] = isset( $params[2][ $key ] ) ? $params[2][ $key ] : '';
					} elseif ( 'reusable-block-id' === $value ) {
						$print_atts['reusable-block-id'] = isset( $params[2][ $key ] ) ? $params[2][ $key ] : 0;
					}
				}
			}
		}

		if ( isset( $print_atts['block-type'] ) ) {
			$whitelist_blocks = array(
				'recipe-card'       => 'wpzoom-recipe-card/block-recipe-card',
				'ingredients-block' => 'wpzoom-recipe-card/block-ingredients',
				'directions-block'  => 'wpzoom-recipe-card/block-directions',
			);
			$block_name       = isset( $whitelist_blocks[ $print_atts['block-type'] ] ) ? $whitelist_blocks[ $print_atts['block-type'] ] : '';

			// Prevent WP Rocket lazy image loading on print page.
			add_filter( 'do_rocket_lazyload', '__return_false' );

			// Prevent Avada lazy image loading on print page.
			if ( class_exists( 'Fusion_Images' ) && property_exists( 'Fusion_Images', 'lazy_load' ) ) {
				Fusion_Images::$lazy_load = false;
			}

			$has_WPZOOM_block = false;
			$attributes       = array();
			$recipe           = get_post( intval( $print_atts['recipe-id'] ) );

			if ( 'publish' !== $recipe->post_status && '1' === WPZOOM_Settings::get( 'wpzoom_rcb_settings_print_only_published_posts' ) ) {
				wp_redirect( home_url() );
				exit();
			}

			if ( ! empty( $block_name ) && ! has_block( $block_name, $recipe ) ) {
				if ( 0 === intval( $print_atts['reusable-block-id'] ) ) {
					// Try to find the reusable block id from core/block.
					if ( has_blocks( $recipe->post_content ) ) {
						$blocks = parse_blocks( $recipe->post_content );
						foreach ( $blocks as $key => $block ) {
							if ( 'core/block' === $block['blockName'] ) {
								$print_atts['reusable-block-id'] = isset( $block['attrs']['ref'] ) ? $block['attrs']['ref'] : 0;
							}
						}
					}
				}
			}

			// Get reusable block post.
			if ( intval( $print_atts['reusable-block-id'] ) > 0 ) {
				$recipe = get_post( intval( $print_atts['reusable-block-id'] ) );
			}

			if ( has_blocks( $recipe->post_content ) && ! empty( $block_name ) ) {
				$found = self::find_print_block( parse_blocks( $recipe->post_content ), $block_name, (string) $print_atts['block-id'], $recipe->ID );
				if ( null !== $found ) {
					$has_WPZOOM_block = true;
					$attributes       = $found;
				}
			}

			if ( $has_WPZOOM_block ) {
				header( 'HTTP/1.1 200 OK' );
				require WPZOOM_RCB_PLUGIN_DIR . 'templates/public/print.php';
				flush();
				exit();
			}
		}
	}
}

WPZOOM_Print::init();
