<?php
/**
 * Add menu pointer.
 *
 * @since   2.9.1
 * @package WPZOOM_Recipe_Card_Blocks
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class for admin menu pointer.
 */
class WPZOOM_Admin_Menu_Pointer {

	/**
	 * The Constructor.
	 */
	public function __construct() {

		// Let's add menu item with subitems
		add_action( 'in_admin_header', array( $this, 'enqueue_scripts' ) );
		add_action( 'admin_init', array( $this, 'dismis_pointer' ) );
		
	}

	/**
	 * Scripts to show pointer.
	 *
	 * @since 2.9.1
	 */
	public function enqueue_scripts() {

		wp_enqueue_script( 'jquery' );
        wp_enqueue_style( 'wp-pointer' );
        wp_enqueue_script( 'wp-pointer' );

		if ( !get_user_meta( get_current_user_id(), 'rcb-recipe-slug-dismissed', true ) ) :
		?>
			<script>
			jQuery(
				function() {
					jQuery('#toplevel_page_wpzoom-recipe-card-settings').first().pointer( 
						{
							content:
								"<h3>Recipe Cards Block update!<\/h3>" +
								"<p>Great news! You can now view all your recipes created using <strong>Recipe Card Blocks</strong> on the <strong>All Recipes</strong> page. Managing your recipes or <stong>adding new ones</stong> has become much easier!</p>",
							position:
								{
									edge:  'left',
									align: 'left'
								},

							pointerClass:
								'wp-pointer arrow-left',

							pointerWidth: 380,
							
							close: function() {
								jQuery.post(
									ajaxurl,
									{
										pointer: 'rcb-recipe-slug',
										action: 'dismiss-wp-pointer',
									}
								);
							},

						}
					).pointer('open');
				}
			);
			</script>
		<?php
		endif;
	}

	/**
	 * Dismis pointer.
	 *
	 * @since 2.9.1
	 */
	public function dismis_pointer() {

		if ( isset( $_POST['action'] ) && 'dismiss-wp-pointer' == $_POST['action'] ) {
			update_user_meta( get_current_user_id(), 'rcb-recipe-slug-dismissed', $_POST['pointer'], true );
		}
	}

}

new WPZOOM_Admin_Menu_Pointer();
