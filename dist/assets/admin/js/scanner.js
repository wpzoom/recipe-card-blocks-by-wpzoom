jQuery(document).on( 'ready', function(){
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
					$('#wpzoom_search_result').fadeIn( "slow", function() {});
					$('.wpzoom-recipe-card-search-note').fadeIn( "slow", function() {});
					$('#wpzoom_search_result span').html( response.data.searchTime );
					$('#wpzoom_recipe_cards_result_amount').html( response.data.recipes );
					setTimeout(
						function() { 
							$this.val( originVal );
							if( $('#wpzoom_cpt_empty_message') && 0 !== response.data.recipes ) {
								location.reload();	
							};
						}, 3000
					);
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

		$('.wpzoom-all-checkboxes').on( 'click', function( e ) {
			
			e.preventDefault();

			$('input:checkbox').parent().addClass('active');
			$('input:checkbox').prop( 'checked', 'checked' );

			numberChecked = $('#wpzoom-found-recipes input:checkbox:checked').length;

			if( 0 < numberChecked ) {
				$( '.wpzoom-amount-selected-recipes' ).show();
				$( '.wpzoom-amout-value' ).html( numberChecked );
				$( '#wpzoom-import-recipes' ).removeClass('disabled');
			}
			else if( 0 == numberChecked ) {
				$( '#wpzoom-import-recipes' ).addClass('disabled');
				$( '.wpzoom-amount-selected-recipes' ).hide();
			}
			
		});

		$('.wpzoom-none-checkboxes').on( 'click', function( e ) {
			e.preventDefault();
			
			$('input:checkbox').parent().removeClass('active');
			$('input:checkbox').prop( 'checked', '' );

			numberChecked = $('#wpzoom-found-recipes input:checkbox:checked').length;

			if( 0 < numberChecked ) {
				$( '.wpzoom-amount-selected-recipes' ).show();
				$( '.wpzoom-amout-value' ).html( numberChecked );
				$( '#wpzoom-import-recipes' ).removeClass('disabled');
			}
			else if( 0 == numberChecked ) {
				$( '#wpzoom-import-recipes' ).addClass('disabled');
				$( '.wpzoom-amount-selected-recipes' ).hide();
			}

		});

		$('#wpzoom-close-modal-window, #wpzoom-cancel-modal').on( 'click', function( e ) {
			e.preventDefault();
			$('.wpzoom-rcb-panel-modal').hide();
		});

		//Search recipes
		$('#wpzoom-scan-recipes-import').on( 'click', function(e) {
			e.preventDefault();
			var data = {
				security: Scanner.ajax_nonce,
				action: 'wpzoom_scan_recipes',
			};

			$('.wpzoom-rcb-import-scan-step').hide();
			$('.wpzoom-rcb-import-progress-step').css( 'display', 'flex' );

			$.ajax({
				url: Scanner.ajaxUrl,
				data: data,
				xhr: function() {
					var myXhr = $.ajaxSettings.xhr();

					//console.log( myXhr.responseText.length );

					if ( myXhr.response ) {
						myXhr.response.addEventListener( 'progress', function(e) {
							if ( e.lengthComputable ) {
								var perc = ( e.loaded / e.total ) * 100;
								perc = perc.toFixed();
								$('.wpzoom-rcb-import-scan-step').hide();
								$('.wpzoom-rcb-import-progress-step').css( 'display', 'flex' );
								$('.wpzoom-rcb-circular-progress' ).css( 'background', 'conic-gradient( rgb(34, 187, 102 ) ' + (perc * 3.6)  + 'deg, rgba(34, 187, 102, 0.2) ' + (perc * 3.6)  + 'deg' );
								$('.wpzoom-rcb-value-container').html( perc + '%');
							}
						}, false );
					}

					// Download progress
					myXhr.addEventListener("progress", function(evt){
						if (evt.lengthComputable) {
							var percentComplete = evt.loaded / evt.total;
							// Do something with download progress
							console.log(percentComplete);
						}
					}, false);

					return myXhr;
				},
				type: 'POST',
				complete: function() {
					
				},
				beforeSend: function() {

				},
				success: function( resp ) {
					if ( resp.success ) {

						$('.wpzoom-rcb-import-progress-step').hide();
						const recipes = resp.data.recipes;
						Object.keys(recipes).forEach( key => {
							$('#wpzoom-found-recipes').append(
								'<div class="wpzoom-rcb-import-checkbox"><input type="checkbox" id="wpzoom_rcb_' + recipes[ key ].id + '" name="recipes" data-block-index="' + recipes[ key ].block_index + '" data-recipe-id="' + recipes[ key ].wprm_recipe_id + '" value="' + recipes[ key ].id + '" /><label for="wpzoom_rcb_' + recipes[ key ].id + '">' +  recipes[key].name + '</label></div>'
							)

						});

						$('.wpzoom-rcb-result-value').html( resp.data.amount );
						$('.wpzoom-rcb-import-search-result-step').show();
						$('.wpzoom-rcb-admin-panel-side-notice').show();

					} else {

				}
			}

		});

		//Import Recipes
		$('#wpzoom-import-recipes').on( 'click', function(e) {
			e.preventDefault();

			$('.wpzoom-rcb-panel-modal').css( 'display', 'flex' );
			
			$('#wpzoom-scan-recipes-start-import').on( 'click', function(e) {
				e.preventDefault();

				var selectedRecipes = [];
				$.each($("input[name='recipes']:checked"), function(){

					var post_id     = $(this).val();
					var recipe_id   = $(this).data( 'recipe-id' );
					var block_index = $(this).data( 'block-index' );
					
					var recipe = { 
						post_id : post_id, 
						recipe_id : recipe_id, 
						block_index : block_index 
					};
					
					selectedRecipes.push( recipe );
				});

				var data = {
					security: Scanner.ajax_nonce,
					action: 'wpzoom_import_recipes',
					recipes : selectedRecipes, 
				};

				console.log( selectedRecipes );

				$.ajax({
					url: Scanner.ajaxUrl,
					data: data,
					
					xhr: function() {
						var myXhr = $.ajaxSettings.xhr();
						if ( myXhr.upload ) {
							myXhr.upload.addEventListener( 'progress', function(e) {
								if ( e.lengthComputable ) {
									var perc = ( e.loaded / e.total ) * 100;
									perc = perc.toFixed();
									$('.wpzoom-rcb-import-scan-step').hide();
									$('.wpzoom-rcb-import-search-result-step').hide();
									$('.wpzoom-rcb-admin-panel-side-notice').hide();
									$('.wpzoom-rcb-panel-modal').hide();
									$('.wpzoom-rcb-import-progress-step h1 > span').text('Importing...');
									$('.wpzoom-rcb-import-progress-step').css( 'display', 'flex' );
									$('.wpzoom-rcb-circular-progress' ).css( 'background', 'conic-gradient( rgb(34, 187, 102 ) ' + (perc * 3.6)  + 'deg, rgba(34, 187, 102, 0.2) ' + (perc * 3.6)  + 'deg' );
									$('.wpzoom-rcb-value-container').html( perc + '%');
								}
							}, false );
						}
						return myXhr;
					},
					type: 'POST',
					success: function( resp ) {
						if ( resp.success ) {

							$('.wpzoom-rcb-import-progress-step').hide();
							$('.wpzoom-rcb-recipes-imported-value').html( resp.data.amount );
							$('.wpzoom-rcb-import-finish-step').show();

						} else {

						}
					}

				});

			});

			});

		});

	})(jQuery, WPZOOM_Scanner);
});

jQuery(document).ajaxComplete(function(){
	( function($, Scanner) {

		var $chkboxes        = $('#wpzoom-found-recipes input:checkbox');
		var lastChecked      = null;
	
		$chkboxes.click(function(e) {

			if ( !lastChecked ) {
				lastChecked = this;
				return;
			}
	
			if ( e.shiftKey ) {
				var start = $chkboxes.index(this);
				var end = $chkboxes.index(lastChecked);
	
				$chkboxes.slice( Math.min( start, end ), Math.max( start, end ) + 1 ).prop( 'checked', lastChecked.checked );
				
				if( $chkboxes.slice( Math.min( start, end ), Math.max( start, end )+ 1 ).is( ':checked' ) ) {
					$chkboxes.slice( Math.min( start, end ), Math.max( start, end )+ 1 ).parent().addClass('active');
				}
				else {
					$chkboxes.slice( Math.min( start, end ), Math.max( start, end )+ 1 ).parent().removeClass('active');
				}
			}
	
			lastChecked = this;
		});

		$('#wpzoom-found-recipes input:checkbox').on( 'change', function( e ) {
			
			numberChecked = $('#wpzoom-found-recipes input:checkbox:checked').length;
			
			var $this = $(this);
			if ( $this.is( ':checked' ) ) {
				$this.parent().addClass('active');
			}
			else {
				$this.parent().removeClass('active');
			}
			
			if( 0 < numberChecked ) {
				$( '.wpzoom-amount-selected-recipes' ).show();
				$( '.wpzoom-amout-value' ).html( numberChecked );
				$( '#wpzoom-import-recipes' ).removeClass('disabled');
				
			}
			else if( 0 == numberChecked ) {
				$( '#wpzoom-import-recipes' ).addClass('disabled');
				$( '.wpzoom-amount-selected-recipes' ).hide();
			}

		});

	})(jQuery, WPZOOM_Scanner);
});