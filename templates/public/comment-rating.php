<?php
/**
 * Template to be used for the comment rating.
 *
 * @since       3.5.0
 *
 * @package     WPZOOM_Recipe_Card_Blocks
 * @subpackage  WPZOOM_Recipe_Card_Blocks/templates/public
 */

$rating_star_svg_empty = class_exists( 'WPZOOM_Comment_Rating' ) ? WPZOOM_Comment_Rating::get_rating_star_svg( 'empty' ) : '';
$rating_star_svg_full  = class_exists( 'WPZOOM_Comment_Rating' ) ? WPZOOM_Comment_Rating::get_rating_star_svg( 'full' ) : '';
$stars_inner           = '';
$has_svg               = ( $rating_star_svg_empty !== '' && $rating_star_svg_full !== '' );
for ( $i = 1; $i <= 5; $i++ ) {
	$star_class = $i <= $rating ? 'wpz-full-star' : 'wpz-empty-star';
	$stars_inner .= '<span class="' . esc_attr( $star_class . ' wpz-star-icon' ) . '">';
	if ( $has_svg ) {
		$stars_inner .= '<span class="wpzoom-rating-star-svg">' . $rating_star_svg_empty . '</span>';
		$stars_inner .= '<span class="wpzoom-rating-star-svg wpzoom-rating-star-full-svg">' . $rating_star_svg_full . '</span>';
	}
	$stars_inner .= '</span>';
}
?>
<div class="wpzoom-rcb-comment-rating"><div class="wpzoom-rcb-comment-rating-stars"><?php echo $stars_inner; ?></div></div>
