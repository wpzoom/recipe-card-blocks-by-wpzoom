jQuery(document).ready(function(){
	( function($, Settings) {
		$('.wp-tab-bar a').click(function(event){
			event.preventDefault();

			var href = $(this).attr('href');
			var query_args = getUrlVars( href );
			var context = $(this).closest('.wp-tab-bar').parent(); // Limit effect to the container element.
			
			$('.wp-tab-bar li', context).removeClass('wp-tab-active');
			$(this).closest('li').addClass('wp-tab-active');
			$('.wp-tab-panel', context).hide();
			$( '#'+query_args['tab'], context ).show();

			// Change url depending by active tab
			window.history.pushState('', '', href);
		});

		// Make setting wp-tab-active optional.
		$('.wp-tab-bar').each(function(){
			if ( $('.wp-tab-active', this).length )
				$('.wp-tab-active', this).click();
			else
				$('a', this).first().click();
		});

		// reset settings to defaults
		$('#wpzoom_rcb_reset_settings').click(function(){
			var data = {
			    security: Settings.ajax_nonce,
			    action: 'wpzoom_reset_settings',
			};

			if (window.confirm("Do you really want to Reset all settings to default?")) {
				$.post( Settings.ajaxUrl, data, function(response){
					if ( response.status == '200' ) {
						var query_args = getUrlVars( window.location.href );
						
						if ( query_args.length > 0 ) {
							window.location.href = window.location.href + "&wpzoom_reset_settings=1";
						} else {
							window.location.href = window.location.href + "?wpzoom_reset_settings=1";
						}
					} else {
						alert('Something wrong happened when trying to reset settings!')
					}
				});
			}
		});

		function getUrlVars( $url ) {
		    var vars = [], hash;
		    var hashes = $url.slice($url.indexOf('?') + 1).split('&');
		    for(var i = 0; i < hashes.length; i++)
		    {
		        hash = hashes[i].split('=');
		        vars.push(hash[0]);
		        vars[hash[0]] = hash[1];
		    }
		    return vars;
		}
	})(jQuery, WPZOOM_Settings);
});