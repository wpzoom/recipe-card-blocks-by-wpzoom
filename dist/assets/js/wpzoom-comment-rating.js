jQuery( function( $ ) {
    const ratings = $( '.wpzoom-rcb-comment-rating-stars' );
    const selectedClass = 'wpz-full-star';

    function toggleStyles( currentInput ) {
        const thisInput = $( currentInput );
        const index = parseInt( thisInput.val() );

        stars.removeClass( selectedClass );
        stars.slice( 0, index + 1 ).addClass( selectedClass );
    }

    // If the ratings exist on the page
    if ( ratings.length !== 0 ) {
        const inputs = ratings.find( 'input[type="radio"]' );
        const labels = ratings.find( 'label' );
        var stars = inputs.next();

        inputs.on( 'change', function( event ) {
            toggleStyles( event.target );
        } );

        labels.hover( function( event ) {
            $curInput = $( event.currentTarget ).find( 'input' );
            toggleStyles( $curInput );
        }, function() {
            $currentSelected = ratings.find( 'input[type="radio"]:checked' );
            toggleStyles( $currentSelected );
        } );
    }
} );
