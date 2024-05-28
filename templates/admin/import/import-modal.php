<?php
/**
 * Template for recipe import lightbox.
 *
 * @package    WPZOOM_Recipe_Block
 */

?>
<div class="wpzoom-rcb-panel-modal">
	<div class="wpzoom-rcb-panel-modal-content">
		<div class="wpzoom-rcb-panel-modal-content-inner">

		<h3><?php esc_html_e( 'Are you sure you want to import selected recipes?', 'recipe-card-blocks-by-wpzoom' ); ?></h3>
		<a id="wpzoom-close-modal-window" href="#"><?php esc_html_e( 'Close', 'recipe-card-blocks-by-wpzoom' ); ?></a>

		<div class="wpzoom-rcb-panel-modal-content-text">
            <p><?php esc_html_e( 'We highly recommend backup your entire database before proceeding to import recipes. In case something goes wrong, you can easily restore everything. This will ensure you don\'t lose any data if something goes wrong during the importing process.', 'recipe-card-blocks-by-wpzoom' ); ?></p>
		</div>
		<a id="wpzoom-cancel-modal" href="#"><?php esc_html_e( 'Cancel', 'recipe-card-blocks-by-wpzoom' ); ?></a>
		<a id="wpzoom-scan-recipes-start-import" class="wpzoom-rcb-btn" href="#"><?php esc_html_e( 'Start Importing', 'recipe-card-blocks-by-wpzoom' ); ?></a>

		</div>
	</div>
</div>
