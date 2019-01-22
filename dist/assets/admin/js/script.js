jQuery(document).ready( function($) {
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
});