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
<h2><?php _e( 'Who we are', 'recipe-card-blocks-by-wpzoom' ); ?></h2>
<?php printf( __( 'We are %1$s, the developer of Recipe Card Blocks plugin. Our website address is <a href="%2$s" target="_blank">%2$s</a>', 'recipe-card-blocks-by-wpzoom' ), '<strong>WPZOOM</strong>', esc_url( 'https://recipecard.io' ) ); ?>
<h2><?php _e( 'What personal data we collect and why we collect it' ); ?></h2>
<?php _e( '<strong>First of all, we want to inform you that we does not have access to any of the data collected by the plugin.</strong> This is all stored in your local database and not communicated back to us. Take note of the following topics for your own privacy policy.', 'recipe-card-blocks-by-wpzoom' ); ?>
<h3><?php _e( 'Cookies' ); ?></h3>
<?php _e( 'When user ratings are enabled we store a <em>wpzoom-user-rating-recipe-%recipe_ID</em> cookie (with %recipe_ID% the ID of the recipe post) that contains the rating this user has given to a particular recipe. And we store a <em>wpzoom-not-logged-user-id</em> cookie (with generated random string ID as value) if user is not logged in on your website. This cookies is used as (one of the) measures to prevent rating spam.', 'recipe-card-blocks-by-wpzoom' ); ?>
<h3><?php _e( 'IP Address' ); ?></h3>
<?php _e( 'We do not collect or store your IP Address.', 'recipe-card-blocks-by-wpzoom' ); ?>
<h2><?php _e( 'How long we retain your data' ); ?></h2>
<?php _e( 'Following cookies <em>wpzoom-user-rating-recipe-%recipe_ID%</em> are stored for 365 days (one year) and <em>wpzoom-not-logged-user-id</em> are stored for 7 days.', 'recipe-card-blocks-by-wpzoom' ); ?> <?php _e( 'User submitted data is stored indefinitely in the local database.', 'recipe-card-blocks-by-wpzoom' ); ?>
