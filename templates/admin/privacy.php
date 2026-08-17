<?php
/**
 * Template for the privacy policy.
 *
 * @since      2.3.2
 *
 * @package    WPZOOM_Recipe_Card_Blocks
 * @subpackage WPZOOM_Recipe_Card_Blocks/templates/admin
 */

?>
<h2><?php esc_html_e( 'Who we are', 'recipe-card-blocks-by-wpzoom' ); ?></h2>
<?php
printf(
	/* translators: 1: developer name, 2: website address */
	wp_kses_post( __( 'We are %1$s, the developer of the Recipe Card Blocks plugin. Our website address is <a href="%2$s" target="_blank">%2$s</a>', 'recipe-card-blocks-by-wpzoom' ) ),
	'<strong>WPZOOM</strong>',
	esc_url( 'https://recipecard.io' )
);
?>
<h2><?php esc_html_e( 'What personal data we collect and why we collect it', 'recipe-card-blocks-by-wpzoom' ); ?></h2>
<?php echo wp_kses_post( __( '<strong>We have no access to any of the data collected by the plugin.</strong> It is stored in your own database and is never sent to us. The points below are provided so you can describe it in your own privacy policy.', 'recipe-card-blocks-by-wpzoom' ) ); ?>
<h3><?php esc_html_e( 'Recipe ratings', 'recipe-card-blocks-by-wpzoom' ); ?></h3>
<?php echo wp_kses_post( __( 'When user ratings are enabled, submitting a rating stores the rating value, the ID of the recipe, the date, and the IP address of the visitor who submitted it. Logged-in visitors also have their user ID stored. If the rating was submitted together with a review, the name, email address and review text supplied in the rating form are stored as well.', 'recipe-card-blocks-by-wpzoom' ) ); ?>
<h3><?php esc_html_e( 'IP address', 'recipe-card-blocks-by-wpzoom' ); ?></h3>
<?php echo wp_kses_post( __( 'The IP address is stored so that the same visitor cannot rate the same recipe more than once, and to limit how many ratings a single address may submit. It is held only in your own database and is not shared with us or any third party.', 'recipe-card-blocks-by-wpzoom' ) ); ?>
<h3><?php esc_html_e( 'Cookies', 'recipe-card-blocks-by-wpzoom' ); ?></h3>
<?php echo wp_kses_post( __( 'When a visitor rates a recipe we store a <em>wpzoom-user-rating-recipe-%recipe_ID%</em> cookie (where %recipe_ID% is the ID of the recipe) holding the rating that visitor gave. It lets us show them their own rating when they return, and is one of the measures used to prevent rating spam.', 'recipe-card-blocks-by-wpzoom' ) ); ?>
<h2><?php esc_html_e( 'How long we retain your data', 'recipe-card-blocks-by-wpzoom' ); ?></h2>
<?php echo wp_kses_post( __( 'The <em>wpzoom-user-rating-recipe-%recipe_ID%</em> cookie is stored for 30 days. Ratings, reviews and their associated IP addresses are stored in your local database indefinitely, until deleted from the Ratings screen or through the Reset Ratings action in the plugin settings.', 'recipe-card-blocks-by-wpzoom' ) ); ?>
