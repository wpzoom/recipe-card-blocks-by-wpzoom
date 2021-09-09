<?php

use Elementor\Control_Media;
use Elementor\Utils;

/**
 * Template: Simple
 *
 * @since       2.8.2
 * 
 * @package     WPZOOM_Recipe_Card_Blocks
 * @subpackage  WPZOOM_Recipe_Card_Blocks/elementor/templates/recipe
 */

$html = '<div ' . $this->get_render_attribute_string( '_wrapper_recipe_card' ) . '>';
	$html .= '<div class="recipe-card-header-wrap">';
		if ( ! empty( $settings['image']['url'] ) ) :

			$this->add_render_attribute( 'image', 'src', $settings['image']['url'] );
			//$this->add_render_attribute( 'image', 'class', 'wpzoom-recipe-card-image' );
			$this->add_render_attribute( 'image', 'alt', Control_Media::get_image_alt( $settings['image'] ) );
			$this->add_render_attribute( 'image', 'title', Control_Media::get_image_title( $settings['image'] ) );
		
			$attachment_size = [
				// Defaults sizes
				0 => null, // Width.
				1 => null, // Height.
		
				'bfi_thumb' => true,
				'crop' => true,
			];
		
			if( !empty( $settings['thumbnail_size'] ) ) {
				if( 'custom' == $settings['thumbnail_size'] ) {
					if ( ! empty( $settings['thumbnail_custom_dimension']['width'] ) ) {
						$attachment_size[0] = (int) $settings['thumbnail_custom_dimension']['width'];
					}
					if ( ! empty( $settings['thumbnail_custom_dimension']['height'] ) ) {
						$attachment_size[1] = (int) $settings['thumbnail_custom_dimension']['height'];
					}
					$imageSize = $attachment_size;
				} else {
					$imageSize = $settings['thumbnail_size'];
				}
			}
			else {
				$imageSize = 'thumbnail';
			}
		
			$alt = !empty( $settings['title'] ) ? esc_attr( $settings['title'] ) : Control_Media::get_image_alt( $settings['image'] );
		
			$image_html = wp_get_attachment_image(
				$settings['image']['id'],
				$imageSize,
				false,
				array(
					'alt'   => $alt,
					'id'    => $settings['image']['id'],
					'class' => 'wpzoom-recipe-card-image'
				)
			);

			$html .= '<div class="recipe-card-image">';
			$html .= '<figure>';
				$html .= $image_html;
					$html .= '<figcaption>';
						if ( 'yes' === $settings['show_pintereset'] ) {
							$html .= \WPZOOM_Recipe_Card_Block::get_pinterest_button( array( 'url' => $settings['image']['url'] ), get_the_permalink(), wp_kses_post( $settings['recipe_card_summary'] ) ); 
						}
						if ( 'yes' === $settings['show_print'] ) {
							$html .= $this->get_print_button();
						}
					$html .= '</figcaption>';
				$html .= '</figure>';
			$html .= '</div><!-- /.recipe-card-image -->';
		endif;
		$html .= '<div class="recipe-card-along-image">';
			$html .= '<div class="recipe-card-heading">';
			if( !empty( $settings['title'] ) ) {
				$this->add_inline_editing_attributes( 'title' );
				$html .= '<h2 ' . $this->get_render_attribute_string( 'title' ) . '>' . esc_html( $settings['title'] ) . '</h2>';
			}
			else {
				$html .= '<h2 class="recipe-card-title">' . esc_html( get_the_title() ) . '</h2>';
			}
			if ( 'yes' === $settings['show_author'] ) {
				$recipe_author = ! empty( $settings['custom_author'] ) ? esc_html( $settings['custom_author'] ) : get_the_author_meta( 'display_name' );
				$html .= '<span class="recipe-card-author">' . esc_html__( 'Recipe by', 'wpzoom-recipe-card' ) .' '. $recipe_author . '</span>';
			}
			if( !empty( $settings['recipe_course'] ) && $settings['show_course'] ) {
				$html .= $this->get_recipe_terms( $settings['recipe_course'], 'courses' );
			}
			if( !empty( $settings['recipe_cuisine'] ) && $settings['show_cuisine'] ) {
				$html .= $this->get_recipe_terms( $settings['recipe_cuisine'], 'cuisines' );
			}
			if( !empty( $settings['recipe_difficulty'] ) && $settings['show_difficulty'] ) {
				$html .= $this->get_recipe_terms( $settings['recipe_difficulty'], 'difficulties' );
			}
			$html .= '</div><!-- /.recipe-card-heading -->';

			//Recipe Card Details
			if ( is_array( $settings[ 'recipe_details_list' ] ) ) :

				$this->add_render_attribute( 'recipe_details_list', 'class', 'recipe-card-details' );

				$html .= '<div ' . $this->get_render_attribute_string( 'recipe_details_list' ) . '>';
					$html .= '<div class="details-items">';
					foreach ( $settings['recipe_details_list'] as $index => $detail_item ) :

						if( 'yes' == $detail_item['show_detail_item'] ) {

							$html .= '<div class="detail-item detail-item-' . $index . '">';
							
							$label_key = $this->get_repeater_setting_key( 'detail_item_label', 'recipe_details_list', $index );
							
							$this->add_render_attribute( $label_key, 'class', 'detail-item-label' );
							//$this->add_inline_editing_attributes( $label_key, 'basic' );

							if( !empty( $detail_item['detail_item_icon']['value'] ) ) {
								$html .= '<span class="detail-item-icon  ' . $detail_item['detail_item_icon']['library'] . ' ' . $detail_item['detail_item_icon']['value'] . '"></span>';
							}
							if( !empty( $detail_item['detail_item_label'] ) ) {
								$html .= '<span ' . $this->get_render_attribute_string( $label_key ) . '>' . esc_html( $detail_item['detail_item_label'] ) . '</span>';
							}

							if( !empty( $detail_item['detail_item_value'] ) ) {
								$html .= '<p class="detail-item-value">' . esc_html( $detail_item['detail_item_value'] ) . '</p>';
							}
							if( !empty( $detail_item['detail_item_unit'] ) ) {
								$html .= '<span class="detail-item-unit">' . esc_html( $detail_item['detail_item_unit'] ) . '</span>';
							}
							$html .= '</div>';

						}

					endforeach;

					$html .= '</div><!-- /.details-items -->';
				$html .= '</div><!-- /.recipe-card-details -->';
			endif;
		$html .= '</div><!-- /.recipe-card-along-image -->';
	$html .= '</div><!-- /.recipe-card-header-wrap -->';

	//Recipe Card Summary
	if ( ! Utils::is_empty( $settings['recipe_card_summary'] ) ) {	
		$this->add_render_attribute( 'recipe_card_summary', 'class', 'recipe-card-summary' );
		$this->add_inline_editing_attributes( 'recipe_card_summary' );
		$html .= sprintf(
			'<p %s>%s</p>',
			$this->get_render_attribute_string( 'recipe_card_summary' ),
			$settings['recipe_card_summary']
		);
	}

	//Recipe Card Ingridients
	if ( is_array( $settings[ 'recipe_ingredients_list' ] ) ) :
		
		$html .= '<div class="recipe-card-ingredients">';
		
		if( !empty( $settings['ingredients_title'] ) ) {
			$this->add_render_attribute( 'ingredients_title', 'class', 'ingredients-title' );
			$this->add_inline_editing_attributes( 'ingredients_title' );
			$html .= sprintf( '<h3 %s>%s</h3>', $this->get_render_attribute_string( 'ingredients_title' ), esc_html( $settings['ingredients_title'] ) );
		}
		
		$html .= '<ul class="ingredients-list layout-1-column">';
		foreach ( $settings['recipe_ingredients_list'] as $index => $item ) :
			$id = self::$helpers->generateId( 'ingredient-item' );
			$html .= '<li id="wpzoom-rcb-' . $id . '" class="ingredient-item"><span class="tick-circle"></span>';
				$html .= '<div class="ingredient-item-name is-strikethrough-active">';
					if( ! empty( $item['ingredient_item_label'] ) ) {
						$html .= '<span class="wpzoom-rcb-ingredient-name">' . wp_kses_post( $item['ingredient_item_label'] ) . '</span>';
					}
				$html .= '</div>';
			$html .= '</li>';
		endforeach;
		$html .= '</ul>';
		
		$html .= '</div><!-- /.recipe-card-ingredients -->';

	endif;

	//Recipe Card Directions
	if ( is_array( $settings[ 'recipe_directions_list' ] ) ) :
		$html .= '<div class="recipe-card-directions">';
		if( !empty( $settings['directions_title'] ) ) {
			$this->add_render_attribute( 'directions_title', 'class', 'directions-title' );
			$this->add_inline_editing_attributes( 'directions_title' );
			$html .= sprintf( '<h3 %s>%s</h3>', $this->get_render_attribute_string( 'directions_title' ), esc_html( $settings['directions_title'] ) );
		}
		$html .= '<ul class="directions-list">';
		foreach ( $settings['recipe_directions_list'] as $index => $item ) :
			$id = self::$helpers->generateId( 'direction-step' );
			$html .= '<li id="wpzoom-rcb-' . $id . '" class="direction-step">';
			if( ! empty( $item['directions_step_text'] ) ) {
				$html .= wp_kses_post( $item['directions_step_text'] );
			}
			if( ! empty( $item['image']['url'] ) ) {
				$image_html = wp_get_attachment_image(
					$item['image']['id'],
					'medium_large',
					false,
					array(
						'alt'   => Control_Media::get_image_alt( $item['image'] ),
						'id'    => $item['image']['id'],
						'class' => 'direction-step-image'
					)
				);
				$html .= $image_html;
			}
			
			if( $item['wp_gallery'] ) {
				$html .= '<div class="direction-step-gallery columns-2" data-grid-columns="2">';
					$html .= '<ul class="direction-step-gallery-grid" data-gallery-masonry-grid="true">';
					foreach( $item['wp_gallery'] as $key => $image ) {
						$html .= '<li class="direction-step-gallery-item">';
						$html .= '<figure>';
						$html .= wp_get_attachment_image(
							$image['id'],
							'medium_large',
							false,
							array(
								'alt'   => Control_Media::get_image_alt( $image ),
								'id'    => 'direction-step-gallery-image-' . $item['image']['id']
							)
						);
						$html .= '</figure>';
						$html .= '</li>';
					};
					$html .= '</ul>';	
				$html .= '</div>';
			}
			$html .= '</li>';
		endforeach;
		$html .= '</ul>';
		$html .= '</div><!-- /.recipe-card-directions -->';
	endif;

	//Recipe Card Video
	$html .= $this->get_video_content();

	//Recipe Card Directions
	if ( is_array( $settings[ 'notes_list' ] ) ) :

		$html .= '<div class="recipe-card-notes">';
		if( !empty( $settings['notes_title'] ) ) {
			$this->add_render_attribute( 'notes_title', 'class', 'notes-title' );
			$this->add_inline_editing_attributes( 'notes_title' );
			$html .= sprintf( '<h3 %s>%s</h3>', $this->get_render_attribute_string( 'notes_title' ), esc_html( $settings['notes_title'] ) );
		}
		$html .= '<ul class="recipe-card-notes-list">';
		foreach ( $settings['notes_list'] as $index => $item ) :
			$note_key = $this->get_repeater_setting_key( 'note_text', 'notes_list', $index );
				//$this->add_inline_editing_attributes( $note_key );
				$this->add_render_attribute( $note_key, 'class', 'wpzoom-rc-note-text' );
			
				if( ! empty( $item['note_text'] ) ) {	
				$html .= '<li ' . $this->get_render_attribute_string( $note_key ) . '>';
					$html .= wp_kses_post( $item['note_text'] );
				$html .= '</li>';
			}
		endforeach;
		$html .= '</ul>';
		$html .= '</div><!-- /.recipe-card-notes -->';
	endif;	

	$html .= '<script type="application/ld+json">'.  wp_json_encode( $this->get_json_ld() ) . '</script>';
$html .= '</div><!-- /.wp-block-wpzoom-recipe-card-block-recipe-card -->'; 

echo apply_filters( 'wpzoom_recipe_card_output', $html );

?>