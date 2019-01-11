<?php
/**
 * Class Settings Page
 *
 * @since   1.1.0
 * @package WPZOOM Recipe Card Block
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class for settings page.
 */
class WPZOOM_Settings {
	/**
	 * Store all settings options.
	 */
	public $settings = array();

	/**
	 * Class WPZOOM_Settings_Fields instance.
	 */
	public $_fields;

	/**
	 * The Constructor.
	 */
	public function __construct() {
	    add_action( 'admin_menu', array( $this, 'admin_menu' ) );
	    add_action( 'admin_init', array( $this, 'settings_init' ) );
	    add_action( 'admin_enqueue_scripts', array( $this, 'scripts' ) );

	    $this->_fields = new WPZOOM_Settings_Fields();
	}

	/**
	 * Add subitem to Settings admin menu.
	 */
	public function admin_menu() {
		add_options_page(
			__( 'WPZOOM Recipe Card Settings', 'wpzoom-recipe-card' ),
			__( 'WPZOOM Recipe Card', 'wpzoom-recipe-card' ),
			'manage_options',
			'wpzoom-recipe-card-settings',
			array( $this, 'settings_page' )
		);
	}

	/**
	 * Initilize all settings
	 */
	public function settings_init() {
		$this->settings = array(
			'general' => array(
				'tab_id' 		=> 'tab-general',
				'tab_title' 	=> __( 'General', 'wpzoom-recipe-card' ),
				'is_active'		=> true,
				'option_group' 	=> 'wpzoom-recipe-card-settings-general',
				'option_name' 	=> 'wpzoom-recipe-card-settings',
				'sections' 		=> array(
					array(
						'id' 		=> 'wpzoom_section_general',
						'title' 	=> __( 'Defaults', 'wpzoom-recipe-card' ),
						'page' 		=> 'wpzoom-recipe-card-settings-general',
						'callback' 	=> '__return_false',
						'fields' 	=> array(
							array(
								'id' 		=> 'wpzoom_rcb_settings_display_course',
								'title' 	=> __( 'Display Course', 'wpzoom-recipe-card' ),
								'type'		=> 'checkbox',
								'args' 		=> array(
									'label_for' 	=> 'wpzoom_rcb_settings_display_course',
									'class' 		=> 'wpzoom-rcb-field-checkbox',
									'description' 	=> esc_html__( 'Here can be description', 'wpzoom-recipe-card' ),
									'default'		=> true
								)
							),
							array(
								'id' 		=> 'wpzoom_rcb_settings_display_cuisine',
								'title' 	=> __( 'Display Cuisine', 'wpzoom-recipe-card' ),
								'type'		=> 'checkbox',
								'args' 		=> array(
									'label_for' 	=> 'wpzoom_rcb_settings_display_cuisine',
									'class' 		=> 'wpzoom-rcb-field-checkbox',
									'description' 	=> esc_html__( 'Here can be description', 'wpzoom-recipe-card' ),
									'default'		=> true
								)
							),
							array(
								'id' 		=> 'wpzoom_rcb_settings_display_difficulty',
								'title' 	=> __( 'Display Difficulty', 'wpzoom-recipe-card' ),
								'type'		=> 'checkbox',
								'args' 		=> array(
									'label_for' 	=> 'wpzoom_rcb_settings_display_difficulty',
									'class' 		=> 'wpzoom-rcb-field-checkbox',
									'description' 	=> esc_html__( 'Here can be description', 'wpzoom-recipe-card' ),
									'default'		=> true
								)
							),
							array(
								'id' 		=> 'wpzoom_rcb_settings_display_author',
								'title' 	=> __( 'Display Author', 'wpzoom-recipe-card' ),
								'type'		=> 'checkbox',
								'args' 		=> array(
									'label_for' 	=> 'wpzoom_rcb_settings_display_author',
									'class' 		=> 'wpzoom-rcb-field-checkbox',
									'description' 	=> esc_html__( 'Here can be description', 'wpzoom-recipe-card' ),
									'default'		=> true
								)
							),
						)
					)
				)
			),
			'appearance' => array(
				'tab_id' 		=> 'tab-appearance',
				'tab_title' 	=> __( 'Appearance', 'wpzoom-recipe-card' ),
				'option_group' 	=> 'wpzoom-recipe-card-settings-appearance',
				'option_name' 	=> 'wpzoom-recipe-card-settings',
				'sections' 		=> array(
					array(
						'id' 		=> 'wpzoom_section_appearance',
						'title' 	=> __( 'Main options', 'wpzoom-recipe-card' ),
						'page' 		=> 'wpzoom-recipe-card-settings-appearance',
						'callback' 	=> '__return_false',
						'fields' 	=> array(
							
						)
					)
				)
			),
			'metadata' => array(
				'tab_id' 		=> 'tab-metadata',
				'tab_title' 	=> __( 'Metadata', 'wpzoom-recipe-card' ),
				'option_group' 	=> 'wpzoom-recipe-card-settings-metadata',
				'option_name' 	=> 'wpzoom-recipe-card-settings',
				'sections' 		=> array(
					array(
						'id' 		=> 'wpzoom_section_metadata',
						'title' 	=> __( 'Main options', 'wpzoom-recipe-card' ),
						'page' 		=> 'wpzoom-recipe-card-settings-metadata',
						'callback' 	=> '__return_false',
						'fields' 	=> array(
							
						)
					)
				)
			)
		);

		$this->register_settings();
	}

	public function register_settings() {
		// filter hook
		$this->settings = apply_filters( 'wpzoom_rcb_before_register_settings', $this->settings );

		if ( empty( $this->settings ) ) {
			return;
		}

		foreach ( $this->settings as $key => $setting ) {
			register_setting( $setting['option_group'], $setting['option_name'] );

			if ( isset( $setting['sections'] ) && is_array( $setting['sections'] ) ) {
				foreach ( $setting['sections'] as $section ) {
					if ( ! isset( $section['id'] ) ) {
						return;
					}
					add_settings_section( $section['id'], $section['title'], $section['callback'], $section['page'] );

					if ( isset( $section['fields'] ) && is_array( $section['fields'] ) ) {
						foreach ( $section['fields'] as $field ) {
							if ( ! isset( $field['id'] ) ) {
								return;
							}

							$field['callback'] = array( $this->_fields, $field['type'] );

							add_settings_field( $field['id'], $field['title'], $field['callback'], $section['page'], $section['id'], $field['args'] );
						}
					}
				}
			}
		}
	}

	public function settings_page() {
		// check user capabilities
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		
		// add error/update messages
		
		// check if the user have submitted the settings
		// wordpress will add the "settings-updated" $_GET parameter to the url
		if ( isset( $_GET['settings-updated'] ) ) {
			// add settings saved message with the class of "updated"
			add_settings_error( 'wpzoom_rcb_messages', 'wpzoom_rcb_message', __( 'Settings Saved', 'wpzoom-recipe-card' ), 'updated' );
		}
	?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			<?php 
				// show error/update messages
				settings_errors( 'wpzoom_rcb_messages' );
			?>
			<form action="options.php" method="post">
				<ul class="wp-tab-bar">
					<?php foreach ( $this->settings as $setting ): ?>
						<?php if ( isset( $setting['is_active'] ) ): ?>
							<li class="wp-tab-active"><a href="#<?php echo $setting['tab_id'] ?>"><?php echo $setting['tab_title'] ?></a></li>
						<?php else: ?>
							<li><a href="#<?php echo $setting['tab_id'] ?>"><?php echo $setting['tab_title'] ?></a></li>
						<?php endif ?>
					<?php endforeach ?>
					<li id="wpzoom_rcb_settings_submit"><?php submit_button( 'Save Settings' ); ?></li>
				</ul>
				<?php foreach ( $this->settings as $setting ): ?>
					<?php if ( isset( $setting['is_active'] ) ): ?>
						<div class="wp-tab-panel" id="<?php echo $setting['tab_id'] ?>">
							<?php 
								settings_fields( $setting['option_group'] );
								do_settings_sections( $setting['option_group'] );
							?>
						</div>
					<?php else: ?>
						<div class="wp-tab-panel" id="<?php echo $setting['tab_id'] ?>" style="display: none;">
							<?php 
								settings_fields( $setting['option_group'] );
								do_settings_sections( $setting['option_group'] );
							?>
						</div>
					<?php endif ?>
				<?php endforeach ?>
			</form>
		</div>
	<?php
	}

	public function scripts( $hook ) {
	    if ( $hook != 'settings_page_wpzoom-recipe-card-settings' ) {
	        return;
	    }

	    wp_enqueue_style(
	    	'wpzoom-rcb-admin-style',
	    	untrailingslashit( WPZOOM_RCB_PLUGIN_URL ) . '/dist/assets/admin/css/style.css',
	    	array(),
	    	WPZOOM_RCB_VERSION
	    );

	    wp_enqueue_script(
	    	'wpzoom-rcb-admin-script',
	    	untrailingslashit( WPZOOM_RCB_PLUGIN_URL ) . '/dist/assets/admin/js/script.js',
	    	array( 'jquery' ),
	    	WPZOOM_RCB_VERSION
	    );
	}
}

new WPZOOM_Settings();
