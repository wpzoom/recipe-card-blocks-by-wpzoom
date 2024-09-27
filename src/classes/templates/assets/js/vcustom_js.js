document.addEventListener( 'DOMContentLoaded', function() {
    // Get element with ID "request-processing"
    const loader = document.querySelector( '#request-processing' );

    // Get all elements with ID "rcb-pro-login-user"
    const loginForm = document.querySelector( '#rcb-pro-login-user' );

    // Get all elements with class "vsign" and "vcont"
    const signLink = document.querySelector( '.vsign' );

    // Get the corresponding popup elements
    const signPopup = document.querySelector( '.vpopup-fixed.vsign' );
    //const contPopup = document.querySelector( '.vpopup-fixed.vcont' );

    // Get all elements with class "conn" in contPopup popup
    const dConnectBtn = document.querySelectorAll( '.dconnect' );

    let info;

    // Function to remove the "hidden" class and add the "show" class
    function showPopup( popup ) {
        popup.classList.remove( 'hidden' );
        popup.classList.add( 'show' );
    }

    // Function to remove the "hidden" class from the loader element
    function showLoader() {
        loader.classList.remove( 'hidden' );
    }

    // Function to hide the popup
    function hidePopup( popup ) {
        popup.classList.remove( 'show' );
        popup.classList.add( 'hidden' );
    }

    // Function to remove the "hidden" class from the loader element
    function hideLoader() {
        loader.classList.add( 'hidden' );
    }

    // Event listener for login
    if ( loginForm ) {
        loginForm.addEventListener( 'submit', function( event ) {
            event.preventDefault(); // Prevent the default action of the form submission
            showLoader();

            const userLogin = this.querySelector( '[name=user_login]' ).value;
            const password = this.querySelector( '[name=password]' ).value;
            const requestBody = {
                action: 'get_user_info_ai_credits',
                username: userLogin,
                password: password,
                nonce: userParams.userDataNonce,
            }; // Request body

            jQuery.ajax( {
                url: userParams.ajaxEndpointURL,
                type: 'POST',
                data: requestBody,
                success: function( data ) {
                    // Handle response data
                    if ( data.success ) {
                        info = data;
                        hidePopup( signPopup );
                        //showPopup( contPopup );
                        //contPopup.querySelector( '.rcb-pro-user-name' ).innerHTML = info.user.name;
                        //contPopup.querySelector( '.rcb-pro-user-avatar' ).src = info.user.avatar;
                        location.reload();
                    } else {
                        if ( data.message ) {
                            alert( data.message );
                        }
                        console.error( 'Unexpected response:', data );
                    }
                    hideLoader();
                },
                error: function( xhr, status, error ) {
                    // Handle errors
                    console.error( 'There was a problem with the AJAX request:', error );
                    hideLoader();
                },
            } );
        } );
    }

    // Event listener for sign in link
    if ( signLink ) {
        signLink.addEventListener( 'click', function( event ) {
            event.preventDefault(); // Prevent the default action of the link
            showPopup( signPopup );
        } );
    }

    if ( dConnectBtn ) {
        dConnectBtn.forEach( function( button ) {
            button.addEventListener( 'click', function( event ) {
                event.preventDefault(); // Prevent the default action of the link

                if ( confirm( 'Are you sure you want to log out?' ) ) {
                    showLoader();

                    const requestBody = {
                        action: 'logout_user_ai_credits',
                        nonce: userParams.logoutUserDataNonce,
                    }; // Request body

                    jQuery.ajax( {
                        url: userParams.ajaxEndpointURL,
                        type: 'POST',
                        data: requestBody,
                        success: function( data ) {
                            // Handle response data
                            if ( data.success ) {
                                location.reload();
                            } else {
                                hideLoader();
                                if ( data.message ) {
                                    alert( data.message );
                                }
                                console.error( 'Unexpected response:', data );
                            }
                        },
                        error: function( xhr, status, error ) {
                            // Handle errors
                            console.error( 'There was a problem with the AJAX request:', error );
                            hideLoader();
                        },
                    } );
                }
            } );
        } );
    }

    // Event listener for close buttons
    const closeButtons = document.querySelectorAll( '.vclose-button' );
    if ( closeButtons ) {
        closeButtons.forEach( function( button ) {
            button.addEventListener( 'click', function() {
                const popup = this.closest( '.vpopup-fixed' );
                hidePopup( popup );
            } );
        } );
    }
} );
