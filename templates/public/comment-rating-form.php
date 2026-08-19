<?php
/**
 * Template to be used for the comment rating.
 *
 * @since       3.0.0
 *
 * @package     WPZOOM_Recipe_Card_Blocks
 * @subpackage  WPZOOM_Recipe_Card_Blocks/templates/public
 */
$should_display_comment_rating_form = false;

if ( isset( $post_ID ) ) {
	if ( has_block( 'wpzoom-recipe-card/block-recipe-card', $post_ID ) || has_block( 'wpzoom-recipe-card/recipe-block-from-posts', $post_ID )  || WPZOOM_Assets_Manager::has_reusable_block( 'wpzoom-recipe-card/block-recipe-card', $post_ID ) || WPZOOM_Assets_Manager::has_cpt_rcb_elementor_widget( $post_ID ) ) {
		$should_display_comment_rating_form = true;
	}
} else {
	if ( has_block( 'wpzoom-recipe-card/block-recipe-card' ) || has_block( 'wpzoom-recipe-card/recipe-block-from-posts' ) || WPZOOM_Assets_Manager::has_reusable_block( 'wpzoom-recipe-card/block-recipe-card' ) || WPZOOM_Assets_Manager::has_cpt_rcb_elementor_widget() ) {
		$should_display_comment_rating_form = true;
	}
}

$rating_titles       = array(
	esc_html__( 'Don\'t rate this recipe', 'recipe-card-blocks-by-wpzoom' ),
	esc_html__( 'Not at all useful', 'recipe-card-blocks-by-wpzoom' ),
	esc_html__( 'Poor quality', 'recipe-card-blocks-by-wpzoom' ),
	esc_html__( 'Average', 'recipe-card-blocks-by-wpzoom' ),
	esc_html__( 'Good', 'recipe-card-blocks-by-wpzoom' ),
	esc_html__( 'Excellent!', 'recipe-card-blocks-by-wpzoom' ),
);
$rating_stars_filled = '';

$comment_rating_svg_empty = class_exists( 'WPZOOM_Rating_Stars' ) ? WPZOOM_Rating_Stars::get_rating_star_svg( 'empty' ) : '';
$comment_rating_svg_full  = class_exists( 'WPZOOM_Rating_Stars' ) ? WPZOOM_Rating_Stars::get_rating_star_svg( 'full' ) : '';

?>
<?php if ( $should_display_comment_rating_form ) : ?>
	<?php if ( is_admin() ) : ?>
		<div class="wpzoom-rcb-comment-rating-form">
			<fieldset class="wpzoom-rcb-comment-rating-stars" style="color:#ffb900">
				<?php for ( $i = 0; $i <= 5; $i++ ) : ?>
					<label for="wpzoom-rcb-comment-rating-<?php echo esc_attr( $i ); ?>">
						<input id="wpzoom-rcb-comment-rating-<?php echo esc_attr( $i ); ?>" type="radio" name="wpzoom-rcb-comment-rating" value="<?php echo esc_attr( $i ); ?>" aria-label="<?php echo esc_attr( $rating_titles[ $i ] ); ?>" <?php echo $rating === $i ? 'checked="checked"' : ''; ?> />
						<?php
							$rating_stars_empty = '';

						if ( $i === 0 ) {
							for ( $k = 1; $k <= 5 - $i; $k++ ) {
								$rating_stars_empty .= '<span class="dashicons dashicons-star-empty"></span>';
							}
						} else {
							$rating_stars_filled .= '<span class="dashicons dashicons-star-filled"></span>';

							for ( $k = 1; $k <= 5 - $i; $k++ ) {
								$rating_stars_empty .= '<span class="dashicons dashicons-star-empty"></span>';
							}
						}

							echo $rating_stars_filled . $rating_stars_empty;
						?>
					</label>
					<br>
				<?php endfor; ?>
			</fieldset>
		</div>
	<?php else : ?>
		<div class="wpzoom-rcb-comment-rating-form">
			<label><?php _e( 'Recipe Rating', 'recipe-card-blocks-by-wpzoom' ); ?></label>
			<fieldset class="wpzoom-rcb-comment-rating-stars">
				<label for="wpzoom-rcb-comment-rating-0">
					<input id="wpzoom-rcb-comment-rating-0" class="hidden" type="radio" name="wpzoom-rcb-comment-rating" value="0" checked="checked" />
					<span class="wpz-empty-star wpz-star-icon" title="<?php echo esc_attr( $rating_titles[0] ); ?>">
						<?php if ( $comment_rating_svg_empty !== '' && $comment_rating_svg_full !== '' ) : ?>
							<span class="wpzoom-rating-star-svg"><?php echo $comment_rating_svg_empty; ?></span>
							<span class="wpzoom-rating-star-svg wpzoom-rating-star-full-svg"><?php echo $comment_rating_svg_full; ?></span>
						<?php endif; ?>
					</span>
				</label>
				<?php for ( $i = 1; $i <= 5; $i++ ) : ?>
					<label for="wpzoom-rcb-comment-rating-<?php echo esc_attr( $i ); ?>">
						<input id="wpzoom-rcb-comment-rating-<?php echo esc_attr( $i ); ?>" class="hidden" type="radio" name="wpzoom-rcb-comment-rating" value="<?php echo esc_attr( $i ); ?>" />
						<span class="wpz-empty-star wpz-star-icon" title="<?php echo esc_attr( $rating_titles[ $i ] ); ?>">
							<?php if ( $comment_rating_svg_empty !== '' && $comment_rating_svg_full !== '' ) : ?>
								<span class="wpzoom-rating-star-svg"><?php echo $comment_rating_svg_empty; ?></span>
								<span class="wpzoom-rating-star-svg wpzoom-rating-star-full-svg"><?php echo $comment_rating_svg_full; ?></span>
							<?php endif; ?>
						</span>
					</label>
				<?php endfor; ?>
			</fieldset>
		</div>
	<?php endif ?>
<?php endif ?>
