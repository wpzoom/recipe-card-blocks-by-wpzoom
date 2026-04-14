<?php
/**
 * Template for the Import & Export admin page.
 *
 * @package    WPZOOM_Recipe_Block
 */

$active_tab  = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'import';
$page_slug   = 'wpzoom_import_panel';
$upgrade_url = 'https://recipecard.io/pricing/?utm_source=wpadmin&utm_medium=import-export-page&utm_campaign=upgrade-premium';
?>
<div class="wrap">
	<h2></h2>
	<?php require_once( WPZOOM_RCB_PLUGIN_DIR . 'templates/admin/header.php' ); ?>

	<ul class="wp-tab-bar">
		<li class="<?php echo 'import' === $active_tab ? 'wp-tab-active' : ''; ?>">
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=' . $page_slug . '&tab=import' ) ); ?>"><?php esc_html_e( 'Import from WPRM', 'recipe-card-blocks-by-wpzoom' ); ?></a>
		</li>
		<li class="<?php echo 'import-csv-json' === $active_tab ? 'wp-tab-active' : ''; ?>">
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=' . $page_slug . '&tab=import-csv-json' ) ); ?>"><?php esc_html_e( 'Import Recipes', 'recipe-card-blocks-by-wpzoom' ); ?> <span class="rcb-premium-badge" style="background:#e15819; color:#fff; font-size:10px; padding:1px 6px; border-radius:3px; margin-left:4px; vertical-align:middle;">PRO</span></a>
		</li>
		<li class="<?php echo 'export' === $active_tab ? 'wp-tab-active' : ''; ?>">
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=' . $page_slug . '&tab=export' ) ); ?>"><?php esc_html_e( 'Export Recipes', 'recipe-card-blocks-by-wpzoom' ); ?> <span class="rcb-premium-badge" style="background:#e15819; color:#fff; font-size:10px; padding:1px 6px; border-radius:3px; margin-left:4px; vertical-align:middle;">PRO</span></a>
		</li>
	</ul>

	<!-- ── Tab: Import from WPRM (existing functionality) ────────── -->
	<div id="import" class="wp-tab-panel"<?php echo 'import' !== $active_tab ? ' style="display:none"' : ''; ?>>
		<div class="wpzoom-rcb-admin-panel-content">

			<div class="wpzoom-rcb-admin-panel-content-inner wpzoom-rcb-import-scan-step">
				<h1><?php esc_html_e( 'Import recipes from WP Recipe Maker', 'recipe-card-blocks-by-wpzoom' ); ?></h1>
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
				<div class="wpzoom-rcb-circular-progress"><svg xmlns="http://www.w3.org/2000/svg" style="margin: auto; background: rgb(255, 255, 255); display: block; shape-rendering: auto;" width="40px" height="40px" viewBox="0 0 100 100" preserveAspectRatio="xMidYMid">
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
				<a class="wpzoom-rcb-import-link" href="<?php echo esc_url( admin_url( 'edit.php?post_type=wpzoom_rcb' ) ); ?>"><?php esc_html_e( 'View all recipes', 'recipe-card-blocks-by-wpzoom' ); ?></a>
				<a class="wpzoom-rcb-import-link" href="<?php echo esc_url( admin_url( 'admin.php?page=wpzoom_import_panel' ) ); ?>"><?php esc_html_e( 'Import more recipes', 'recipe-card-blocks-by-wpzoom' ); ?></a>
			</div><!--//.wpzoom-rcb-import-finish-step -->

		</div>
		<div class="wpzoom-rcb-admin-panel-side-notice">
			<?php require( WPZOOM_RCB_PLUGIN_DIR . 'templates/admin/import/import-notice.php' ); ?>
		</div>
	</div>

	<!-- ── Tab: Import Recipes (PRO upsell) ──────────────────────── -->
	<div id="import-csv-json" class="wp-tab-panel"<?php echo 'import-csv-json' !== $active_tab ? ' style="display:none"' : ''; ?>>
		<div class="wpzoom-rcb-admin-panel-content">
			<div class="wpzoom-rcb-admin-panel-content-inner">

				<div style="position: relative;">
					<!-- Locked overlay -->
					<div style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: rgba(255,255,255,0.85); z-index: 10; display: flex; flex-direction: column; align-items: center; justify-content: center; border-radius: 8px;">
						<div style="text-align: center; max-width: 480px; padding: 40px 20px;">
							<span class="dashicons dashicons-lock" style="font-size: 48px; color: #e15819; margin-bottom: 16px; display: block;"></span>
							<h2 style="margin: 0 0 12px; font-size: 22px;"><?php esc_html_e( 'PRO Feature', 'recipe-card-blocks-by-wpzoom' ); ?></h2>
							<p style="color: #555; margin-bottom: 20px;">
								<?php esc_html_e( 'Import recipes from CSV or JSON files exported from spreadsheets, other recipe plugins like WP Recipe Maker, or any external source. Includes smart auto-mapping, ingredient parsing, and support for WordPress XML migration.', 'recipe-card-blocks-by-wpzoom' ); ?>
							</p>
							<a href="<?php echo esc_url( $upgrade_url ); ?>" target="_blank" class="wpzoom-rcb-btn" style="background: #e15819; font-size: 15px; padding: 10px 28px;">
								<?php esc_html_e( 'Upgrade to PRO', 'recipe-card-blocks-by-wpzoom' ); ?> &rarr;
							</a>
						</div>
					</div>

					<!-- Blurred preview of Import UI -->
					<div style="pointer-events: none; user-select: none; filter: blur(1.5px); opacity: 0.6;">
						<h1><?php esc_html_e( 'Import from WordPress XML', 'recipe-card-blocks-by-wpzoom' ); ?></h1>
						<div><span style="display: inline-block; background: #dff0d8; color: #3c763d; padding: 2px 10px; border-radius: 3px; font-size: 12px; font-weight: 600; margin-bottom: 12px;"><?php esc_html_e( 'Recommended for migration', 'recipe-card-blocks-by-wpzoom' ); ?></span></div>
						<p><?php esc_html_e( 'For migrating recipes created with Recipe Card Blocks from another WordPress site. This is a lossless import that preserves all recipe data, images, and settings.', 'recipe-card-blocks-by-wpzoom' ); ?></p>

						<div style="border: 2px dashed #c3c4c7; border-radius: 6px; background: #f9f9f9; padding: 32px 24px; text-align: center; margin: 20px 0 24px;">
							<p style="color: #646970;"><?php esc_html_e( 'Drag & drop your .xml file here, or', 'recipe-card-blocks-by-wpzoom' ); ?></p>
							<span class="wpzoom-rcb-btn wpzoom-rcb-btn-border"><?php esc_html_e( 'Choose File', 'recipe-card-blocks-by-wpzoom' ); ?></span>
						</div>

						<hr style="margin: 30px 0;" />

						<h1><?php esc_html_e( 'Import from CSV or JSON', 'recipe-card-blocks-by-wpzoom' ); ?></h1>
						<p><?php esc_html_e( 'Import recipes from spreadsheets, other recipe plugins, or external sources with flexible column mapping.', 'recipe-card-blocks-by-wpzoom' ); ?></p>

						<div style="border: 2px dashed #c3c4c7; border-radius: 6px; background: #f9f9f9; padding: 32px 24px; text-align: center; margin: 20px 0 24px;">
							<p style="color: #646970;"><?php esc_html_e( 'Drag & drop your file here, or', 'recipe-card-blocks-by-wpzoom' ); ?></p>
							<span class="wpzoom-rcb-btn wpzoom-rcb-btn-border"><?php esc_html_e( 'Choose File', 'recipe-card-blocks-by-wpzoom' ); ?></span>
						</div>
					</div>
				</div>

			</div>
		</div>
	</div>

	<!-- ── Tab: Export Recipes (PRO upsell) ───────────────────────── -->
	<div id="export" class="wp-tab-panel"<?php echo 'export' !== $active_tab ? ' style="display:none"' : ''; ?>>
		<div class="wpzoom-rcb-admin-panel-content">
			<div class="wpzoom-rcb-admin-panel-content-inner">

				<div style="position: relative;">
					<!-- Locked overlay -->
					<div style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: rgba(255,255,255,0.85); z-index: 10; display: flex; flex-direction: column; align-items: center; justify-content: center; border-radius: 8px;">
						<div style="text-align: center; max-width: 480px; padding: 40px 20px;">
							<span class="dashicons dashicons-lock" style="font-size: 48px; color: #e15819; margin-bottom: 16px; display: block;"></span>
							<h2 style="margin: 0 0 12px; font-size: 22px;"><?php esc_html_e( 'PRO Feature', 'recipe-card-blocks-by-wpzoom' ); ?></h2>
							<p style="color: #555; margin-bottom: 20px;">
								<?php esc_html_e( 'Export all your recipes in WordPress XML (lossless migration), JSON, or CSV formats. Perfect for backups, moving to another site, or editing recipes in bulk using a spreadsheet.', 'recipe-card-blocks-by-wpzoom' ); ?>
							</p>
							<a href="<?php echo esc_url( $upgrade_url ); ?>" target="_blank" class="wpzoom-rcb-btn" style="background: #e15819; font-size: 15px; padding: 10px 28px;">
								<?php esc_html_e( 'Upgrade to PRO', 'recipe-card-blocks-by-wpzoom' ); ?> &rarr;
							</a>
						</div>
					</div>

					<!-- Blurred preview of Export UI -->
					<div style="pointer-events: none; user-select: none; filter: blur(1.5px); opacity: 0.6;">
						<h1><?php esc_html_e( 'Export Recipes', 'recipe-card-blocks-by-wpzoom' ); ?></h1>
						<p><?php esc_html_e( 'Choose an export format below depending on your needs.', 'recipe-card-blocks-by-wpzoom' ); ?></p>

						<h3><?php esc_html_e( 'Recommended: Recipe Migration', 'recipe-card-blocks-by-wpzoom' ); ?></h3>

						<div style="display: flex; gap: 20px; flex-wrap: wrap; margin: 20px 0;">
							<div style="flex: 1; min-width: 200px; border: 1px solid #ddd; border-radius: 8px; padding: 24px; text-align: center;">
								<h3 style="margin-top: 0;">WordPress XML</h3>
								<p style="color: #646970; font-size: 13px;"><?php esc_html_e( 'Lossless export for migrating recipes to another WordPress site.', 'recipe-card-blocks-by-wpzoom' ); ?></p>
								<span class="wpzoom-rcb-btn"><?php esc_html_e( 'Export as WordPress XML', 'recipe-card-blocks-by-wpzoom' ); ?></span>
							</div>
						</div>

						<h3><?php esc_html_e( 'Other Formats', 'recipe-card-blocks-by-wpzoom' ); ?></h3>

						<div style="display: flex; gap: 20px; flex-wrap: wrap; margin: 20px 0;">
							<div style="flex: 1; min-width: 200px; border: 1px solid #ddd; border-radius: 8px; padding: 24px; text-align: center;">
								<h3 style="margin-top: 0;">JSON</h3>
								<p style="color: #646970; font-size: 13px;"><?php esc_html_e( 'Best for migrating to other plugins.', 'recipe-card-blocks-by-wpzoom' ); ?></p>
								<span class="wpzoom-rcb-btn wpzoom-rcb-btn-border"><?php esc_html_e( 'Export as JSON', 'recipe-card-blocks-by-wpzoom' ); ?></span>
							</div>
							<div style="flex: 1; min-width: 200px; border: 1px solid #ddd; border-radius: 8px; padding: 24px; text-align: center;">
								<h3 style="margin-top: 0;">CSV</h3>
								<p style="color: #646970; font-size: 13px;"><?php esc_html_e( 'Best for editing in a spreadsheet.', 'recipe-card-blocks-by-wpzoom' ); ?></p>
								<span class="wpzoom-rcb-btn wpzoom-rcb-btn-border"><?php esc_html_e( 'Export as CSV', 'recipe-card-blocks-by-wpzoom' ); ?></span>
							</div>
						</div>
					</div>
				</div>

			</div>
		</div>
	</div>

	<?php require_once( WPZOOM_RCB_PLUGIN_DIR . 'templates/admin/footer.php' ); ?>
</div>
<?php require_once( WPZOOM_RCB_PLUGIN_DIR . 'templates/admin/import/import-modal.php' ); ?>
