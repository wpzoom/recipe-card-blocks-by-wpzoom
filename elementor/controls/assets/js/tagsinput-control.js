var WPZoomControlBaseDataView = elementor.modules.controls.BaseData.extend({
    onReady: function () {

			this.ui.input.tagsinput();
			this.ui.input.on('change', () => {
				this.saveValue();
			} )
    },
	saveValue: function() {
        this.setValue( this.ui.input.val() );
    },
    onBeforeDestroy: function () {
		//this.saveValue();
        this.ui.input.tagsinput( 'destroy' );
    }
});

elementor.addControlView( 'wpzoom_tagfield', WPZoomControlBaseDataView );