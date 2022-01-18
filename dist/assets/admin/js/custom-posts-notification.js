
//console.log( wpzoomCPTNotification );

( function ( wp ) {
	wp.data.dispatch( 'core/notices' ).createNotice(
		'warning', // Can be one of: success, info, warning, error.
		wpzoomCPTNotification.message, // Text string to display.
		{
			isDismissible: true, // Whether the user can dismiss the notice.
			// Any actions the user can perform.
			actions: [
                {
                    url: wpzoomCPTNotification.parent_url,
                    label: wpzoomCPTNotification.parent_link_label,
                },
            ],
		}
	);
} )( window.wp );