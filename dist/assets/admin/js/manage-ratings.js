/*global ajaxurl, wpzoomManageRatings*/
( function( $ ) {
    unapproveRating = function( el ) {
        if ( el.hasClass( 'unapproved' ) ) {
            el.find( 'span.approve a.approve' ).css( { 'pointer-events': '' } );
        }
    };

    approveRating = function( el ) {
        if ( el.hasClass( 'approved' ) ) {
            el.find( 'span.unapprove a.unapprove' ).css( { 'pointer-events': '' } );
        }
    };

    deleteRating = function( el ) {
        el.fadeOut( 300, function() {
            el.remove();
        } );
    };

    getCount = function( el ) {
        const n = parseInt( el.html().replace( /[^0-9]+/g, '' ), 10 );
        if ( isNaN( n ) ) {
            return 0;
        }
        return n;
    };

    updateCount = function( el, count ) {
        let n1 = '';
        if ( isNaN( count ) ) {
            return;
        }
        count = count < 1 ? '0' : count.toString();
        if ( count.length > 3 ) {
            while ( count.length > 3 ) {
                n1 = thousandsSeparator + count.substr( count.length - 3 ) + n1;
                count = count.substr( 0, count.length - 3 );
            }
            count = count + n1;
        }
        el.html( count );
    };

    updatePending = function( diff ) {
        $( '#toplevel_page_wpzoom-recipe-card-settings span.pending-count' ).each( function() {
            let el = $( this ),
                count = getCount( el ) + diff;
            if ( count < 1 ) {
                count = 0;
            }
            el.closest( '.awaiting-mod' )[ 0 === count ? 'addClass' : 'removeClass' ]( 'count-0' );
            updateCount( el, count );
        } );
    };

    updatePendingCount = function( newCount ) {
        $( '#toplevel_page_wpzoom-recipe-card-settings span.pending-count' ).each( function() {
            let el = $( this );
            el.closest( '.awaiting-mod' )[ 0 === newCount ? 'addClass' : 'removeClass' ]( 'count-0' );
            updateCount( el, newCount );
        } );
    };

    getUrlArg = function( arg, url = '' ) {
        const results = new RegExp( '[\?&]' + arg + '=([^&#]*)' ).exec( url || window.location.search );
        return ( results !== null ) ? results[ 1 ] || 0 : false;
    };

    ajaxAction = function( data, el ) {
        $.post( ajaxurl, data, function( r ) {
            let elementId = el.attr( 'id' ),
                $element = $( '#' + elementId ),
                diff;

            if ( r.success ) {
                if ( data.action === 'approverating' ) {
                    approveRating( $element );
                    diff = $element.is( '.approved' ) ? -1 : 1;
                } else if ( data.action === 'unapproverating' ) {
                    unapproveRating( $element );
                    diff = $element.is( '.unapproved' ) ? 1 : -1;
                } else if ( data.action === 'deleterating' ) {
                    deleteRating( $element );
                    diff = $element.is( '.negative-diff' ) ? -1 : 0;
                }
                updatePending( diff );
            }
        } );
    };

    // Bulk Actions Handler
    handleBulkAction = function() {
        const action = $( '#bulk-action-selector' ).val();
        const checkedBoxes = $( 'input[name="rating_ids[]"]:checked' );
        const ratingIds = [];

        checkedBoxes.each( function() {
            ratingIds.push( $( this ).val() );
        } );

        // Validation
        if ( ! action ) {
            alert( wpzoomManageRatings.strings.selectAction );
            return;
        }

        if ( ratingIds.length === 0 ) {
            alert( wpzoomManageRatings.strings.selectItems );
            return;
        }

        // Confirm delete action
        if ( action === 'delete' ) {
            if ( ! window.confirm( wpzoomManageRatings.strings.confirmDelete ) ) {
                return;
            }
        }

        // Disable button and show processing state
        const $button = $( '#doaction' );
        const originalText = $button.text();
        $button.prop( 'disabled', true ).text( wpzoomManageRatings.strings.processing );

        // Highlight selected rows
        checkedBoxes.each( function() {
            $( this ).closest( 'tr' ).css( 'opacity', '0.5' );
        } );

        // Make AJAX request
        $.post( wpzoomManageRatings.ajaxurl, {
            action: 'wpzoom_bulk_rating_action',
            bulk_action: action,
            rating_ids: ratingIds,
            nonce: wpzoomManageRatings.bulkNonce
        }, function( response ) {
            if ( response.success ) {
                // Update pending count in menu
                if ( typeof response.data.pending_count !== 'undefined' ) {
                    updatePendingCount( response.data.pending_count );
                }

                // Handle based on action type
                if ( action === 'delete' ) {
                    // Remove deleted rows with animation
                    checkedBoxes.each( function() {
                        $( this ).closest( 'tr' ).fadeOut( 300, function() {
                            $( this ).remove();
                        } );
                    } );
                } else {
                    // Reload the page to show updated data
                    window.location.reload();
                }
            } else {
                // Show error
                alert( response.data && response.data.message ? response.data.message : 'An error occurred.' );
                // Reset row opacity
                checkedBoxes.each( function() {
                    $( this ).closest( 'tr' ).css( 'opacity', '' );
                } );
            }

            // Re-enable button
            $button.prop( 'disabled', false ).text( originalText );
        } ).fail( function() {
            alert( 'An error occurred. Please try again.' );
            $button.prop( 'disabled', false ).text( originalText );
            checkedBoxes.each( function() {
                $( this ).closest( 'tr' ).css( 'opacity', '' );
            } );
        } );
    };

    $( document ).ready( function() {
        // Individual rating row actions
        $( document ).on( 'click', '.user-rating span.unapprove a.unapprove, .user-rating span.approve a.approve, .user-rating span.delete a.delete', function( e ) {
            e.preventDefault();

            let el = $( this ),
                url = el.attr( 'href' ),
                id = getUrlArg( 'id', url ),
                action = getUrlArg( 'action', url ),
                postId = getUrlArg( 'post', url ),
                userId = getUrlArg( 'user', url ),
                userIp = getUrlArg( 'ip', url ),
                rating = getUrlArg( 'rating', url ),
                date = getUrlArg( 'date', url ),
                time = getUrlArg( 'time', url ),
                postName = el.parents( '.column-comment' ).next().find( 'a.comments-edit-item-link' ).html(),
                nonce = getUrlArg( '_wpnonce', url );

            const $element = el.closest( 'tr.user-rating' );
            const bg = $element.hasClass( 'unapproved' ) ? '#FFFFE0' : $element.css( 'backgroundColor' );

            const data = {
                action,
                id,
                postId,
                userId,
                userIp,
                rating,
                date,
                time,
                nonce,
            };

            if ( 'deleterating' === action && window.confirm( `Do you really want to Delete rating for ${ postName }?` ) ) {
                $element.find( 'span.delete a.delete' ).css( { 'pointer-events': 'none' } );
                if ( $element.is( '.unapproved' ) ) {
                    $element.addClass( 'negative-diff' );
                }
                $element.removeClass( 'approved unapproved' )
                    .animate( { backgroundColor: '#FAAFAA' }, 300 );
                ajaxAction( data, $element );
            }
            if ( 'approverating' === action ) {
                $element.find( 'span.unapprove a.unapprove' ).css( { 'pointer-events': 'none' } );
                $element.animate( { backgroundColor: '#CCEEBB' }, 300 )
                    .animate( { backgroundColor: '' }, 300 )
                    .removeClass( 'unapproved' ).addClass( 'approved' );

                ajaxAction( data, $element );
            }
            if ( 'unapproverating' === action ) {
                $element.find( 'span.approve a.approve' ).css( { 'pointer-events': 'none' } );
                $element.animate( { backgroundColor: '#CCEEBB' }, 300 )
                    .animate( { backgroundColor: bg }, 300 )
                    .removeClass( 'approved' ).addClass( 'unapproved' );

                ajaxAction( data, $element );
            }
        } );

        // Bulk action button click
        $( document ).on( 'click', '#doaction', function( e ) {
            e.preventDefault();
            handleBulkAction();
        } );

        // Select all checkboxes
        $( document ).on( 'change', '#cb-select-all-1, #cb-select-all-2', function() {
            const isChecked = $( this ).prop( 'checked' );
            $( 'input[name="rating_ids[]"]' ).prop( 'checked', isChecked );
            $( '#cb-select-all-1, #cb-select-all-2' ).prop( 'checked', isChecked );
        } );

        // Update select-all checkboxes when individual checkboxes change
        $( document ).on( 'change', 'input[name="rating_ids[]"]', function() {
            const totalCheckboxes = $( 'input[name="rating_ids[]"]' ).length;
            const checkedCheckboxes = $( 'input[name="rating_ids[]"]:checked' ).length;
            const allChecked = totalCheckboxes === checkedCheckboxes && totalCheckboxes > 0;
            $( '#cb-select-all-1, #cb-select-all-2' ).prop( 'checked', allChecked );
        } );
    } );
}( jQuery ) );
