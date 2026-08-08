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

		$recipes_link = admin_url( 'edit.php?post_type=wpzoom_rcb' );

		$pointer_title = esc_html__( 'Recipe Cards Block update!', 'recipe-card-blocks-by-wpzoom' );

		$pointer_content = sprintf(
			/* translators: 1: opening <strong> tag, 2: closing </strong> tag, 3: opening link tag to the All Recipes page, 4: closing link tag */
			esc_html__( 'Great news! You can now view all your recipes created using %1$sRecipe Card Blocks%2$s on the %3$sAll Recipes%4$s page. Managing your recipes or %1$sadding new ones%2$s has become much easier!', 'recipe-card-blocks-by-wpzoom' ),
			'<strong>',
			'</strong>',
			'<a href="' . esc_url( $recipes_link ) . '"><strong>',
			'</strong></a>'
		);

		if ( !get_user_meta( get_current_user_id(), 'rcb-recipe-slug-dismissed', true ) ) :
		?>
			<script>
			jQuery(
				function() {
					jQuery('#toplevel_page_wpzoom-recipe-card-settings').first().pointer( 
						{
							content:
								<?php echo wp_json_encode( '<h3>' . $pointer_title . '</h3><p>' . $pointer_content . '</p>' ); ?>,
							position:
								{
									edge:  'left',
									align: 'left'
								},

							pointerClass:
								'wp-pointer arrow-left',

							pointerWidth: 380,
							show: function(event, t){
								t.pointer.css({'position':'fixed'});
							},
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
