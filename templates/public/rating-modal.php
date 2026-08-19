<?php
/**
 * Template for the user ratings modal.
 *
 * @since       3.3.0
 *
 * @package     WPZOOM_Recipe_Card_Blocks
 * @subpackage  WPZOOM_Recipe_Card_Blocks/templates/public
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$modal_title       = WPZOOM_Settings::get( 'wpzoom_rcb_settings_rating_modal_title' );
$button_text       = WPZOOM_Settings::get( 'wpzoom_rcb_settings_rating_modal_button_text' );
$thank_you_message = WPZOOM_Settings::get( 'wpzoom_rcb_settings_rating_modal_thank_you' );
$button_color      = WPZOOM_Settings::get( 'wpzoom_rcb_settings_rating_modal_button_color' );
$comment_placeholder = WPZOOM_Settings::get( 'wpzoom_rcb_settings_rating_modal_comment_placeholder' );
$public_review_notice = WPZOOM_Settings::get( 'wpzoom_rcb_settings_rating_modal_public_review_notice' );
$show_name         = WPZOOM_Settings::get( 'wpzoom_rcb_settings_rating_modal_show_name' );
$show_email        = WPZOOM_Settings::get( 'wpzoom_rcb_settings_rating_modal_show_email' );
$require_name      = WPZOOM_Settings::get( 'wpzoom_rcb_settings_rating_modal_require_name' );
$require_email     = WPZOOM_Settings::get( 'wpzoom_rcb_settings_rating_modal_require_email' );
$force_comment     = WPZOOM_Settings::get( 'wpzoom_rcb_settings_rating_modal_force_comment' );

// Backward compatibility for require_comment setting
if ( empty( $force_comment ) ) {
	$require_comment_old = WPZOOM_Settings::get( 'wpzoom_rcb_settings_rating_modal_require_comment' );
	$force_comment = ( '1' === $require_comment_old ) ? 'always' : 'disabled';
}

// Defaults
if ( empty( $modal_title ) ) {
	$modal_title = __( 'Rate This Recipe', 'recipe-card-blocks-by-wpzoom' );
}
if ( empty( $button_text ) ) {
	$button_text = __( 'Rate and Review Recipe', 'recipe-card-blocks-by-wpzoom' );
}
if ( empty( $thank_you_message ) ) {
	$thank_you_message = __( 'Thank you for rating this recipe!', 'recipe-card-blocks-by-wpzoom' );
}
if ( empty( $button_color ) ) {
	$button_color = '#041728';
}
if ( empty( $comment_placeholder ) ) {
	$comment_placeholder = __( 'What did you think of this recipe? (optional)', 'recipe-card-blocks-by-wpzoom' );
}
if ( empty( $public_review_notice ) ) {
	$public_review_notice = __( 'Your review may be published publicly on this page if comments are enabled.', 'recipe-card-blocks-by-wpzoom' );
}
if ( false === $show_name ) {
	$show_name = '1';
}
if ( false === $show_email ) {
	$show_email = '1';
}
if ( false === $require_name ) {
	$require_name = '1';
}
if ( false === $require_email ) {
	$require_email = '1';
}
$can_publish_public_review = ( '1' === (string) $show_name && '1' === (string) $show_email );
$anonymous_review_mode     = ! $can_publish_public_review;

$rating_titles = array(
	1 => esc_attr__( 'Poor', 'recipe-card-blocks-by-wpzoom' ),
	2 => esc_attr__( 'Fair', 'recipe-card-blocks-by-wpzoom' ),
	3 => esc_attr__( 'Average', 'recipe-card-blocks-by-wpzoom' ),
	4 => esc_attr__( 'Good', 'recipe-card-blocks-by-wpzoom' ),
	5 => esc_attr__( 'Excellent!', 'recipe-card-blocks-by-wpzoom' ),
);

$rating_star_svg_empty = class_exists( 'WPZOOM_Rating_Stars' ) ? WPZOOM_Rating_Stars::get_rating_star_svg( 'empty' ) : '';
$rating_star_svg_full  = class_exists( 'WPZOOM_Rating_Stars' ) ? WPZOOM_Rating_Stars::get_rating_star_svg( 'full' ) : '';

// Get current user info if logged in
$current_user = wp_get_current_user();
$user_name    = $current_user->ID ? $current_user->display_name : '';
$user_email   = $current_user->ID ? $current_user->user_email : '';
?>

<div id="wpzoom-rating-modal" class="wpzoom-rating-modal" style="display:none;" role="dialog" aria-modal="true" aria-labelledby="wpzoom-rating-modal-title">
	<div class="wpzoom-rating-modal-overlay"></div>
	<div class="wpzoom-rating-modal-content">
		<button type="button" class="wpzoom-rating-modal-close" aria-label="<?php esc_attr_e( 'Close', 'recipe-card-blocks-by-wpzoom' ); ?>">&times;</button>

		<div class="wpzoom-rating-modal-form-wrapper">
			<h3 id="wpzoom-rating-modal-title" class="wpzoom-rating-modal-title"><?php echo esc_html( $modal_title ); ?></h3>
			<p class="wpzoom-rating-modal-recipe-name"></p>

			<div class="wpzoom-rating-modal-stars">
				<?php for ( $i = 1; $i <= 5; $i++ ) : ?>
					<span class="wpzoom-rating-modal-star wpz-empty-star wpz-star-icon" data-rating="<?php echo esc_attr( $i ); ?>" title="<?php echo esc_attr( $rating_titles[ $i ] ); ?>">
						<?php if ( $rating_star_svg_empty !== '' && $rating_star_svg_full !== '' ) : ?>
							<span class="wpzoom-rating-star-svg"><?php echo $rating_star_svg_empty; ?></span>
							<span class="wpzoom-rating-star-svg wpzoom-rating-star-full-svg"><?php echo $rating_star_svg_full; ?></span>
						<?php endif; ?>
					</span>
				<?php endfor; ?>
			</div>

			<form class="wpzoom-rating-modal-form" novalidate>
				<input type="hidden" name="wpzoom_rating_recipe_id" value="" />
				<input type="hidden" name="wpzoom_rating_value" value="" />

				<div class="wpzoom-rating-modal-field wpzoom-rating-modal-field-comment">
					<label for="wpzoom-rating-comment" class="screen-reader-text"><?php esc_html_e( 'Comment', 'recipe-card-blocks-by-wpzoom' ); ?></label>
					<textarea
						id="wpzoom-rating-comment"
						name="wpzoom_rating_comment"
						placeholder="<?php echo esc_attr( $comment_placeholder ); ?>"
						rows="3"
					></textarea>

					<?php if ( $anonymous_review_mode ) : ?>
						<p class="wpzoom-rating-modal-field-description"><?php esc_html_e( 'Anonymous mode is enabled. Reviews are saved for moderation (not published automatically).', 'recipe-card-blocks-by-wpzoom' ); ?></p>
						<input type="hidden" name="wpzoom_rating_publish_comment" value="1" />
					<?php else : ?>
						<p class="wpzoom-rating-modal-field-description"><?php echo esc_html( $public_review_notice ); ?></p>
						<label class="wpzoom-rating-modal-publish-comment-toggle">
							<input type="checkbox" name="wpzoom_rating_publish_comment" value="1" checked="checked" />
							<?php esc_html_e( 'Also publish my review as a comment', 'recipe-card-blocks-by-wpzoom' ); ?>
						</label>
					<?php endif; ?>
				</div>

				<?php if ( '1' === (string) $show_name ) : ?>
				<div class="wpzoom-rating-modal-field wpzoom-rating-modal-field-name">
					<label for="wpzoom-rating-name" class="screen-reader-text"><?php esc_html_e( 'Name', 'recipe-card-blocks-by-wpzoom' ); ?></label>
					<input
						type="text"
						id="wpzoom-rating-name"
						name="wpzoom_rating_name"
						placeholder="<?php echo esc_attr( $require_name === '1' ? __( 'Name *', 'recipe-card-blocks-by-wpzoom' ) : __( 'Name', 'recipe-card-blocks-by-wpzoom' ) ); ?>"
						value="<?php echo esc_attr( $user_name ); ?>"
						<?php echo $require_name === '1' ? 'required' : ''; ?>
					/>
				</div>
				<?php endif; ?>

				<?php if ( '1' === (string) $show_email ) : ?>
				<div class="wpzoom-rating-modal-field wpzoom-rating-modal-field-email">
					<label for="wpzoom-rating-email" class="screen-reader-text"><?php esc_html_e( 'Email', 'recipe-card-blocks-by-wpzoom' ); ?></label>
					<input
						type="email"
						id="wpzoom-rating-email"
						name="wpzoom_rating_email"
						placeholder="<?php echo esc_attr( $require_email === '1' ? __( 'Email *', 'recipe-card-blocks-by-wpzoom' ) : __( 'Email', 'recipe-card-blocks-by-wpzoom' ) ); ?>"
						value="<?php echo esc_attr( $user_email ); ?>"
						<?php echo $require_email === '1' ? 'required' : ''; ?>
					/>
				</div>
				<?php endif; ?>

				<div class="wpzoom-rating-modal-field wpzoom-rating-modal-field-submit">
					<button type="submit" class="wpzoom-rating-modal-submit" style="background-color: <?php echo esc_attr( $button_color ); ?>;">
						<?php echo esc_html( $button_text ); ?>
					</button>
				</div>

				<div class="wpzoom-rating-modal-error" style="display:none;"></div>
			</form>
		</div>

		<div class="wpzoom-rating-modal-thank-you" style="display:none;">
			<?php echo wp_kses_post( $thank_you_message ); ?>
		</div>

		<div class="wpzoom-rating-modal-loading" style="display:none;">
			<span class="wpzoom-rating-modal-spinner"></span>
		</div>
	</div>
</div>
