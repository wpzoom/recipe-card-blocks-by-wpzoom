
jQuery(document).ready(function ($) {
	$('.elementor-rcb-print-button').on( 'click', function(e) {
		e.preventDefault();
		$( this ).closest('.wp-block-wpzoom-recipe-card-block-recipe-card').print({
			globalStyles: true,
			iframe: true,
			stylesheet: wpzoomRecipeCardPrint.stylesheetPrintURL,
		});
	});
	$( '.wp-block-wpzoom-recipe-card-block-ingredients .ingredients-list li, .wp-block-wpzoom-recipe-card-block-recipe-card .ingredients-list li' ).on( 'click', function( e ) {
		// Don't do any actions if clicked on link
		if ( e.target.nodeName === 'A' ) {
			return;
		}
		$( this ).toggleClass( 'ticked' );
	} );

});