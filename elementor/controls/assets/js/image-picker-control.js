var WPZoomControlBaseDataView = elementor.modules.controls.BaseData.extend({
    onReady: function () {

			this.ui.select.imagepicker({
				show_label: true
			});
			this.ui.select.on('change', () => {
				this.saveValue();
			} )
    },
	saveValue: function() {
        this.setValue( this.ui.select.val() );
    },
    onBeforeDestroy: function () {
		//this.saveValue();
        this.ui.select.imagepicker( 'destroy' );
    }
});

elementor.addControlView( 'wpzoom_image_picker', WPZoomControlBaseDataView );