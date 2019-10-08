<?php
/**
 * Class Lite vs PRO Page
 *
 * @since   2.2.0
 * @package WPZOOM_Recipe_Card_Blocks
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
		<div class="plugin-info-wrap" id="wpzoom-plugin-comparison">

            <div id="getting-started">
                <h3>
                    <?php esc_html_e('Get Started with Recipe Card Blocks', 'wpzoom-recipe-card'); ?>
                </h3>
                <div class="wpz-row wpz-clearfix">
                    <div class="wpz-col-1-2">
                        <div class="section">
                            <h4>
                                <span class="dashicons dashicons-editor-help"></span>
                                <?php esc_html_e('Plugin Documentation', 'wpzoom-recipe-card'); ?>
                            </h4>
                            <p class="about">
                                <?php esc_html_e('Need help configuring the plugin? In the documentation you can find all the information that is needed to get started adding recipes in your blog.', 'wpzoom-recipe-card'); ?>
                            </p>
                            <p><br/>
                                <a href="<?php echo esc_url(__('https://www.wpzoom.com/documentation/recipe-card-blocks/', 'wpzoom-recipe-card')); ?>" target="_blank" class="button button-primary">
                                    <?php esc_html_e('Documentation', 'wpzoom-recipe-card'); ?>
                                </a>
                            </p>
                        </div>

                        <hr /><br/>
                        <div class="section">
                           <h4>
                               <span class="dashicons dashicons-star-filled"></span>
                               <?php esc_html_e('Why Upgrade?', 'wpzoom-recipe-card'); ?>
                           </h4>
                           <p class="about">
                               <?php esc_html_e('Upgrading to Recipe Card Blocks PRO plugin you will unlock a dozen of new features that will take your food blog to the next level. See in the table below just a few of the features included in the PRO version.', 'wpzoom-recipe-card'); ?>
                           </p>
                       </div>
                    </div>
                    <div class="wpz-col-1-2">
                        <div class="section">
                            <h4>
                                <span class="dashicons dashicons-cart"></span>
                                <?php esc_html_e('Recipe Card Blocks PRO', 'wpzoom-recipe-card'); ?>
                            </h4>
                            <p class="about">
                                <?php esc_html_e('If you like the free version of this plugin, you will LOVE the PRO version which includes features like Recipe Ratings, Color Schemes and other useful features to take your food blog to the next level!', 'wpzoom-recipe-card'); ?>
                            </p>
                            <p>
                                <a href="<?php echo esc_url(__('https://www.wpzoom.com/plugins/recipe-card-blocks/', 'wpzoom-recipe-card')); ?>" target="_blank" class="button button-primary">
                                    <?php esc_html_e('Upgrade to Recipe Card Blocks PRO', 'wpzoom-recipe-card'); ?>
                                </a>
                            </p>
                        </div><hr /><br/>

                    </div>
                </div>
            </div>
            <hr>


			<h3 class="plugin-comparison-intro">
				<?php esc_html_e('Recipe Card Block Free vs. PRO', 'wpzoom-recipe-card'); ?>
			</h3>
            <p>
                <?php esc_html_e('Take your recipes to the next level with Recipe Card Blocks PRO!', 'wpzoom-recipe-card'); ?>
               </p>
            <p>
                <?php esc_html_e('Unlock premium features and extend the functionalities & look of your food blog.', 'wpzoom-recipe-card'); ?>
            </p>

			<table class="plugin-comparison-table">
				<thead>
					<tr>
						<th><h3><?php esc_html_e('Features', 'wpzoom-recipe-card'); ?></h3></th>
						<th><h3><?php esc_html_e('Free', 'wpzoom-recipe-card'); ?></h3></th>
						<th><h3><?php esc_html_e('PRO', 'wpzoom-recipe-card'); ?></h3></th>
					</tr>
				</thead>
				<tbody>
                    <tr>
                        <td class="table-index"><h3><?php esc_html_e('Color Schemes', 'wpzoom-recipe-card'); ?></h3></td>
                        <td><?php esc_html_e('1', 'wpzoom-recipe-card'); ?></td>
                        <td><?php esc_html_e('4 + Unlimited Colors', 'wpzoom-recipe-card'); ?></td>
                    </tr>
                    <tr>
                        <td class="table-index"><h3><?php esc_html_e('Recipe Card Styles', 'wpzoom-recipe-card'); ?></h3></td>
                        <td><?php esc_html_e('3', 'wpzoom-recipe-card'); ?></td>
                        <td><?php esc_html_e('3 + More Coming Soon', 'wpzoom-recipe-card'); ?></td>
                    </tr>
                    <tr>
                        <td class="table-index"><h3><?php esc_html_e('Schema Markup', 'wpzoom-recipe-card'); ?></h3></td>
                        <td><span class="dashicons dashicons-yes"></span></td>
                        <td><span class="dashicons dashicons-yes"></span></td>
                    </tr>
                    <tr>
                        <td class="table-index"><h3><?php esc_html_e('Video Support', 'wpzoom-recipe-card'); ?></h3></td>
                        <td><span class="dashicons dashicons-yes"></span></td>
                        <td><span class="dashicons dashicons-yes"></span></td>
                    </tr>
                    <tr>
                        <td class="table-index"><h3><?php esc_html_e('Bulk Add Ingredients & Directions', 'wpzoom-recipe-card'); ?></h3></td>
                        <td><span class="dashicons dashicons-yes"></span></td>
                        <td><span class="dashicons dashicons-yes"></span></td>
                    </tr>
                    <tr>
                        <td class="table-index"><h3><?php esc_html_e('Inline Structured Data Validator', 'wpzoom-recipe-card'); ?></h3></td>
                        <td><span class="dashicons dashicons-yes"></span></td>
                        <td><span class="dashicons dashicons-yes"></span></td>
                    </tr>
					<tr>
						<td class="table-index"><h3><?php esc_html_e('Star Rating', 'wpzoom-recipe-card'); ?></h3></td>
						<td><span class="dashicons dashicons-no"></span></td>
						<td><span class="dashicons dashicons-yes"></span></td>
					</tr>
                    <tr>
                        <td class="table-index"><h3><?php esc_html_e('Hide Footer Credit', 'wpzoom-recipe-card'); ?></h3></td>
                        <td><span class="dashicons dashicons-no"></span></td>
                        <td><span class="dashicons dashicons-yes"></span></td>
                    </tr>
                    <tr>
                        <td class="table-index"><h3><?php esc_html_e('Premium Support', 'wpzoom-recipe-card'); ?></h3></td>
                        <td><span class="dashicons dashicons-no"></span></td>
                        <td><span class="dashicons dashicons-yes"></span></td>
                    </tr>
                    <tr>
                        <td class="table-index"><h3><?php esc_html_e('Advanced Pinterest Settings', 'wpzoom-recipe-card'); ?></h3></td>
                        <td><span class="dashicons dashicons-no"></span></td>
                        <td><span class="dashicons dashicons-yes"></span></td>
                    </tr>
                    <tr>
                        <td class="table-index"><h3><?php esc_html_e('Nutrition Info', 'wpzoom-recipe-card'); ?></h3></td>
                        <td><span class="dashicons dashicons-no"></span></td>
                        <td><?php esc_html_e('Coming Soon', 'wpzoom-recipe-card'); ?></td>
                    </tr>
                    <tr>
                        <td class="table-index"><h3><?php esc_html_e('Automatic Recipe Archives', 'wpzoom-recipe-card'); ?></h3></td>
                        <td><span class="dashicons dashicons-no"></span></td>
                        <td><?php esc_html_e('Coming Soon', 'wpzoom-recipe-card'); ?></td>
                    </tr>
					<tr>
						<td class="table-index"><h3><?php esc_html_e('Support', 'wpzoom-recipe-card'); ?></h3></td>
						<td><?php esc_html_e('Community Forum', 'wpzoom-recipe-card'); ?></td>
						<td><?php esc_html_e('Email Support', 'wpzoom-recipe-card'); ?></td>
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
