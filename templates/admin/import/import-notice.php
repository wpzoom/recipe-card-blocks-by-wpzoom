<?php
/**
 * Template for recipe import notice.
 *
 * @package    WPZOOM_Recipe_Block
 */

?>
<div class="wpzoom-rcb-admin-panel-note">
	<p><?php esc_html_e( 'We highly recommend backup your entire database before proceeding to import recipes. In case something goes wrong, you can easily restore everything. This will ensure you don\'t lose any data if something goes wrong during the importing process.', 'recipe-card-blocks-by-wpzoom' ); ?></p>
	<p><a target="_blank" href="<?php echo esc_url( admin_url( 'plugin-install.php?s=database+backup&tab=search&type=term' ) ) ?>"><?php esc_html_e( 'Install a Database Backup plugin &rarr;', 'recipe-card-blocks-by-wpzoom' ); ?></a></p>
</div>