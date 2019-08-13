<?php
/**
 * Class Lite vs PRO Page
 *
 * @since   2.2.0
 * @package WPZOOM Recipe Card Block
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class for lite-vs-pro page.
 */
class WPZOOM_Lite_vs_PRO {

	/**
	 * The Constructor.
	 */
	public function __construct() {

		// Check what page we are on.
		$page = isset( $_GET['page'] ) ? $_GET['page'] : '';

		// Only load if we are actually on the settings page.
		if ( 'wpzoom-recipe-card-vs-pro' === $page ) {
			add_action( 'wpzoom_rcb_admin_page', array( $this, 'lite_vs_pro_page' ) );

			// Include admin scripts & styles
            add_action( 'admin_enqueue_scripts', array( $this, 'scripts' ) );
		}

	}

	public function lite_vs_pro_page() {
		// check user capabilities
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
	?>
		<div class="wrap" id="wpzoom-plugin-comparison">
			<h3 class="plugin-comparison-intro">
				<?php esc_html_e('Recipe Card Lite vs. Recipe Card PRO', 'wpzoom-recipe-card'); ?>
			</h3>
			<table class="plugin-comparison-table">
				<thead>
					<tr>
						<th><h3><?php esc_html_e('Features', 'wpzoom-recipe-card'); ?></h3></th>
						<th><h3><?php esc_html_e('Lite', 'wpzoom-recipe-card'); ?></h3></th>
						<th><h3><?php esc_html_e('PRO', 'wpzoom-recipe-card'); ?></h3></th>
					</tr>
				</thead>
				<tbody>
                    <tr>
                        <td><h3><?php esc_html_e('Custom Widgets', 'wpzoom-recipe-card'); ?></h3></td>
                        <td><?php esc_html_e('1', 'wpzoom-recipe-card'); ?></td>
                        <td><?php esc_html_e('6 (Featured Categories, Carousel, Author Bio, Image Box)', 'wpzoom-recipe-card'); ?></td>
                    </tr>
                    <tr>
                        <td><h3><?php esc_html_e('Widget Areas', 'wpzoom-recipe-card'); ?></h3></td>
                        <td><?php esc_html_e('6', 'wpzoom-recipe-card'); ?></td>
                        <td><?php esc_html_e('15 (5 on Homepage)', 'wpzoom-recipe-card'); ?></td>
                    </tr>
					<tr>
						<td><h3><?php esc_html_e('Responsive Layout', 'wpzoom-recipe-card'); ?></h3></td>
						<td><span class="dashicons dashicons-yes"></span></td>
						<td><span class="dashicons dashicons-yes"></span></td>
					</tr>
                    <tr>
                        <td><h3><?php esc_html_e('Magazine Layout', 'wpzoom-recipe-card'); ?></h3></td>
                        <td><span class="dashicons dashicons-no"></span></td>
                        <td><span class="dashicons dashicons-yes"></span></td>
                    </tr>
                    <tr>
                        <td><h3><?php esc_html_e('Demo Content Importer', 'wpzoom-recipe-card'); ?></h3></td>
                        <td><span class="dashicons dashicons-no"></span></td>
                        <td><span class="dashicons dashicons-yes"></span></td>
                    </tr>
                    <tr>
                        <td><h3><?php esc_html_e('Recipe Index', 'wpzoom-recipe-card'); ?></h3></td>
                        <td><span class="dashicons dashicons-no"></span></td>
                        <td><span class="dashicons dashicons-yes"></span></td>
                    </tr>
                    <tr>
                        <td><h3><?php esc_html_e('Recipe Shortcodes', 'wpzoom-recipe-card'); ?></h3></td>
                        <td><span class="dashicons dashicons-no"></span></td>
                        <td><span class="dashicons dashicons-yes"></span></td>
                    </tr>
					<tr>
						<td><h3><?php esc_html_e('10 Color Schemes', 'wpzoom-recipe-card'); ?></h3></td>
						<td><span class="dashicons dashicons-no"></span></td>
						<td><span class="dashicons dashicons-yes"></span></td>
					</tr>
					<tr>
						<td><h3><?php esc_html_e('3 Slider Styles', 'wpzoom-recipe-card'); ?></h3></td>
						<td><span class="dashicons dashicons-no"></span></td>
						<td><span class="dashicons dashicons-yes"></span></td>
					</tr>
                    <tr>
                        <td><h3><?php esc_html_e('Multiple Posts Layouts', 'wpzoom-recipe-card'); ?></h3></td>
                        <td><span class="dashicons dashicons-no"></span></td>
                        <td><span class="dashicons dashicons-yes"></span></td>
                    </tr>
					<tr>
						<td><h3><?php esc_html_e('Built-in Social Buttons', 'wpzoom-recipe-card'); ?></h3></td>
						<td><span class="dashicons dashicons-no"></span></td>
						<td><span class="dashicons dashicons-yes"></span></td>
					</tr>
					<tr>
						<td><h3><?php esc_html_e('Extended WooCommerce Integration', 'wpzoom-recipe-card'); ?></h3></td>
						<td><span class="dashicons dashicons-no"></span></td>
						<td><span class="dashicons dashicons-yes"></span></td>
					</tr>
					<tr>
						<td><h3><?php esc_html_e('Advertising Options', 'wpzoom-recipe-card'); ?></h3></td>
						<td><span class="dashicons dashicons-no"></span></td>
						<td><span class="dashicons dashicons-yes"></span></td>
					</tr>
					<tr>
						<td><h3><?php esc_html_e('Theme Options', 'wpzoom-recipe-card'); ?></h3></td>
                        <td><span class="dashicons dashicons-no"></span></td>
						<td><span class="dashicons dashicons-yes"></span></td>
					</tr>
                    <tr>
                        <td><h3><?php esc_html_e('Carousel Widget', 'wpzoom-recipe-card'); ?></h3></td>
                        <td><span class="dashicons dashicons-no"></span></td>
                        <td><span class="dashicons dashicons-yes"></span></td>
                    </tr>
					<tr>
						<td><h3><?php esc_html_e('100+ Color Options', 'wpzoom-recipe-card'); ?></h3></td>
						<td><span class="dashicons dashicons-no"></span></td>
						<td><span class="dashicons dashicons-yes"></span></td>
					</tr>
					<tr>
						<td><h3><?php esc_html_e('600+ Google Fonts', 'wpzoom-recipe-card'); ?></h3></td>
						<td><span class="dashicons dashicons-no"></span></td>
						<td><span class="dashicons dashicons-yes"></span></td>
					</tr>
					<tr>
						<td><h3><?php esc_html_e('Typography Options', 'wpzoom-recipe-card'); ?></h3></td>
						<td><span class="dashicons dashicons-no"></span></td>
						<td><span class="dashicons dashicons-yes"></span></td>
					</tr>
					<tr>
						<td><h3><?php esc_html_e('Instagram Bar in the Footer', 'wpzoom-recipe-card'); ?></h3></td>
						<td><span class="dashicons dashicons-no"></span></td>
						<td><span class="dashicons dashicons-yes"></span></td>
					</tr>
					<tr>
						<td><h3><?php esc_html_e('Support', 'wpzoom-recipe-card'); ?></h3></td>
						<td><?php esc_html_e('Support Forum', 'wpzoom-recipe-card'); ?></td>
						<td><?php esc_html_e('Fast & Friendly Email Support', 'wpzoom-recipe-card'); ?></td>
					</tr>
					<tr>
						<td></td>
						<td></td>
						<td>
							<a href="<?php echo WPZOOM_Plugin_Activator::get_upgrade_url(); ?>" target="_blank" class="button button-primary">
								<?php esc_html_e('Upgrade to PRO', 'wpzoom-recipe-card'); ?>
							</a>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
	<?php
	}

	/**
	 * Enqueue scripts and styles
	 *
	 * @param string $hook
	 */
	public function scripts( $hook ) {
		$pos = strpos( $hook, 'wpzoom-recipe-card-vs-pro' );

	    if ( $pos === false ) {
	        return;
	    }

	    wp_enqueue_style(
	    	'wpzoom-rcb-admin-style',
	    	untrailingslashit( WPZOOM_RCB_PLUGIN_URL ) . '/dist/assets/admin/css/style.css',
	    	array(),
	    	WPZOOM_RCB_VERSION
	    );
	}
}

new WPZOOM_Lite_vs_PRO();
