<?php
/**
 * Template for recipe import page panel.
 *
 * @package    WPZOOM_Recipe_Block
 */

?>
<div class="wrap">
	<h2></h2>
	<?php require_once( WPZOOM_RCB_PLUGIN_DIR . 'templates/admin/header.php' ); ?>
	<div class="wpzoom-rcb-admin-panel-content">
		
		<div class="wpzoom-rcb-admin-panel-content-inner wpzoom-rcb-import-scan-step">
			<h1><?php esc_html_e( 'Import recipes from WP Recipe Maker', 'recipe-card-blocks-by-wpzoom' ) ?></h1>
			<p><?php esc_html_e( 'Easily import all existing recipes from the WP Recipe Maker plugin to Recipe Card Blocks.', 'recipe-card-blocks-by-wpzoom' ); ?></p>
			<p><?php esc_html_e( 'The process is quick and easy:', 'recipe-card-blocks-by-wpzoom' ); ?></p>
			<ul>
				<li><?php esc_html_e( 'Scan all your existing recipes.', 'recipe-card-blocks-by-wpzoom' ); ?></li>
				<li><?php esc_html_e( 'Select the recipes you want to be imported.', 'recipe-card-blocks-by-wpzoom' ); ?></li>
				<li><?php esc_html_e( 'Click the Import button.', 'recipe-card-blocks-by-wpzoom' ); ?></li>
				<li><?php esc_html_e( 'All recipes from your pages/posts will be replaced with Recipe Card Blocks.', 'recipe-card-blocks-by-wpzoom' ); ?></li>
			</ul>
			<?php require( WPZOOM_RCB_PLUGIN_DIR . 'templates/admin/import/import-notice.php' ); ?>
			<a id="wpzoom-scan-recipes-import" class="wpzoom-rcb-btn" href="#"><?php esc_html_e( 'Search for recipes', 'recipe-card-blocks-by-wpzoom' ); ?></a>
		</div><!--//.wpzoom-rcb-import-scan-step -->

		<div class="wpzoom-rcb-admin-panel-content-inner wpzoom-rcb-import-progress-step">
			<div class="wpzoom-rcb-circular-progress"><svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" style="margin: auto; background: rgb(255, 255, 255); display: block; shape-rendering: auto;" width="40px" height="40px" viewBox="0 0 100 100" preserveAspectRatio="xMidYMid">
<circle cx="50" cy="50" r="32" stroke-width="8" stroke="#22bb66" stroke-dasharray="50.26548245743669 50.26548245743669" fill="none" stroke-linecap="round">
  <animateTransform attributeName="transform" type="rotate" repeatCount="indefinite" dur="1s" keyTimes="0;1" values="0 50 50;360 50 50"></animateTransform>
</circle></svg></div>
			<h1><span><?php esc_html_e( 'Searching...', 'recipe-card-blocks-by-wpzoom' ); ?></span></h1>
		</div><!--//.wpzoom-rcb-import-progress-step -->

		<div class="wpzoom-rcb-admin-panel-content-inner wpzoom-rcb-import-search-result-step">
			<h1><span class="wpzoom-rcb-result-value">0</span> <?php esc_html_e( 'recipes were found', 'recipe-card-blocks-by-wpzoom' ); ?></h1>
			<a href="#" class="wpzoom-rcb-btn wpzoom-rcb-btn-border wpzoom-all-checkboxes"><?php esc_html_e( 'Select all', 'recipe-card-blocks-by-wpzoom' ); ?></a>
			<a href="#" class="wpzoom-rcb-btn wpzoom-rcb-btn-border wpzoom-none-checkboxes"><?php esc_html_e( 'None', 'recipe-card-blocks-by-wpzoom' ); ?></a>
			<div class="wpzoom-list-all-recipes-options">
			<form id="wpzoom-found-recipes">
			<?php 
				$found_recipes = get_option( 'wpzoom_import_wprm_recipes', array() );
				if( !empty( $found_recipes ) ) {
					foreach( $found_recipes as $recipe ) {
						echo '<label>';
						echo '<input type="checkbox" name="recipes[]" value="">';
						echo  esc_html( $recipe['name'] ) . ' </label>';
					}
				}
			?>
			</form>
			</div>
			<a href="#" class="wpzoom-rcb-btn wpzoom-rcb-btn-border wpzoom-all-checkboxes"><?php esc_html_e( 'Select all', 'recipe-card-blocks-by-wpzoom' ); ?></a>
			<a href="#" class="wpzoom-rcb-btn wpzoom-rcb-btn-border wpzoom-none-checkboxes"><?php esc_html_e( 'None', 'recipe-card-blocks-by-wpzoom' ); ?></a>
			<span class="wpzoom-amount-selected-recipes"><span class="wpzoom-amout-value"></span> <?php esc_html_e( 'recipes selected', 'recipe-card-blocks-by-wpzoom' ); ?></span>
			<br/>
			<a id="wpzoom-import-recipes" class="wpzoom-rcb-btn disabled" href="#"><?php esc_html_e( 'Import recipes', 'recipe-card-blocks-by-wpzoom' ); ?></a>
		</div><!--//.wpzoom-rcb-import-search-result-step -->
		
		<div class="wpzoom-rcb-admin-panel-content-inner wpzoom-rcb-import-finish-step">
			<h1><span class="wpzoom-rcb-recipes-imported-value">0</span> <?php esc_html_e( 'recipe(s) successfully imported', 'recipe-card-blocks-by-wpzoom' ); ?></h1>
			<a class="wpzoom-rcb-import-link" href="<?php echo esc_url( admin_url( 'edit.php?post_type=wpzoom_rcb' ) ) ?>"><?php esc_html_e( 'View all recipes', 'recipe-card-blocks-by-wpzoom' ); ?></a>
			<a class="wpzoom-rcb-import-link" href="<?php echo esc_url( admin_url( 'admin.php?page=wpzoom_import_panel' ) ) ?>"><?php esc_html_e( 'Import more recipes', 'recipe-card-blocks-by-wpzoom' ); ?></a>
		</div><!--//.wpzoom-rcb-import-finish-step -->

	</div>
	<div class="wpzoom-rcb-admin-panel-side-notice">
		<?php require( WPZOOM_RCB_PLUGIN_DIR . 'templates/admin/import/import-notice.php' ); ?>
	</div>
	<?php require_once( WPZOOM_RCB_PLUGIN_DIR . 'templates/admin/footer.php' ); ?>
</div>
<?php require_once( WPZOOM_RCB_PLUGIN_DIR . 'templates/admin/import/import-modal.php' ); ?>