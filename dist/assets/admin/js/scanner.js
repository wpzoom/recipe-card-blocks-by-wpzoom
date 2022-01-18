jQuery(document).ready(function(){
	( function($, Scanner) {

		// Search Recipes cards and create custom posts
		$('#wpzoom_rcb_settings_search_recipes').on( 'click', function(e) {			
			var data = {
				security: Scanner.ajax_nonce,
				action: 'wpzoom_search_recipes',
			};

			var $this = $(this);
			var originVal = $this.val();
			$this.val('Searching...');

			$.post( Scanner.ajaxUrl, data, function(response){
				if ( response.success ) {
					$this.val( response.data.process );
					setTimeout(
						function() { 
							$this.val( originVal );
						}, 5000
					);
					$('#wpzoom_search_result').fadeIn( "slow", function() {});
					$('.wpzoom-recipe-card-search-note').fadeIn( "slow", function() {});
					$('#wpzoom_search_result span').html( response.data.searchTime );
					$('#wpzoom_recipe_cards_result_amount').html( response.data.recipes );
					//console.log( response );
				}
			});
		});

		// close scanner box
		$('.wpzoom-rcb-scanner-close').on( 'click', function(e){
			e.preventDefault();

			var box = $(this).attr('href');
			var data = {
			    security: Scanner.ajax_nonce,
			    action: 'wpzoom_search_recipes_box_close',
			};

			$(box).fadeOut();

			$.post( Scanner.ajaxUrl, data, function(response){
				if ( ! response.success ) {
					alert('Something went wrong!')
				}
			});
		});

	})(jQuery, WPZOOM_Scanner);
});