<?php
namespace WPZOOMElementorRecipeCard\Controls;	

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Elementor\Plugin;
use Elementor\Base_Data_Control;

/**
 * Elementor tagfield control.
 *
 * A base control for creating an icon control. Displays a font icon select box
 * field. The control accepts `include` or `exclude` arguments to set a partial
 * list of icons.
 *
 * @since 1.0.0
 */
class WPZOOM_Tagfield extends Base_Data_Control {

	/**
	 * Get icon control type.
	 *
	 * Retrieve the control type, in this case `icon`.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string Control type.
	 */
	public function get_type() {
		return 'wpzoom_tagfield';
	}

	public function enqueue() {
		
		
		// Styles
		wp_register_style( 'wpzoom-tagfield', plugins_url( 'assets/css/tagsinput.css', __FILE__ ), array(), WPZOOM_RCB_VERSION );
		wp_register_script( 'wpzoom-tagfield', plugins_url( 'assets/js/tagsinput.js', __FILE__ ), array( 'jquery' ), WPZOOM_RCB_VERSION, true );
		wp_register_script( 'wpzoom-tagsinput-control', plugins_url( 'assets/js/tagsinput-control.js', __FILE__ ), array( 'jquery' ), WPZOOM_RCB_VERSION, true );
		
		wp_enqueue_style( 'wpzoom-tagfield' );
		wp_enqueue_style( 'elementor-editor' );
		wp_enqueue_script( 'wpzoom-tagfield' );
		wp_enqueue_script( 'wpzoom-tagsinput-control' );
		
	}

		/**
	 * Render text control output in the editor.
	 *
	 * Used to generate the control HTML in the editor using Underscore JS
	 * template. The variables for the class are available using `data` JS
	 * object.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function content_template() {
		$control_uid = $this->get_control_uid();
		?>
		<div class="elementor-control-field">
			<# if ( data.label ) {#>
				<label for="<?php echo $control_uid; ?>" class="elementor-control-title">{{{ data.label }}}</label>
			<# } #>
			<div class="elementor-control-input-wrapper elementor-control-unit-5 elementor-control-dynamic-switcher-wrapper">
				<input id="<?php echo $control_uid; ?>" type="{{ data.input_type }}" class="tooltip-target elementor-control-tag-area" data-tooltip="{{ data.title }}" title="{{ data.title }}" data-setting="{{ data.name }}" placeholder="{{ data.placeholder }}" />
			</div>
		</div>
		<# if ( data.description ) { #>
			<div class="elementor-control-field-description">{{{ data.description }}}</div>
		<# } #>
		<?php
	}

	/**
	 * Get text control default settings.
	 *
	 * Retrieve the default settings of the text control. Used to return the
	 * default settings while initializing the text control.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @return array Control default settings.
	 */
	protected function get_default_settings() {
		return [
			'input_type' => 'text',
			'placeholder' => '',
			'title' => '',
		];
	}
}
