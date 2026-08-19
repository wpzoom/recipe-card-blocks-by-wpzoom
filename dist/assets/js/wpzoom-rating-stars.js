jQuery( function() {
    const __slice = [].slice;

    ( function( $, wpzoomRatingStars ) {
        // Modal handling - initialize immediately
        const RatingModal = {
            $modal: null,
            currentRecipeId: null,
            currentPostId: null,
            currentRating: 0,
            initialized: false,

            init: function() {
                if ( this.initialized ) {
                    return;
                }

                this.$modal = $( '#wpzoom-rating-modal' );
                if ( ! this.$modal.length ) {
                    return;
                }

                this.initialized = true;
                this.bindEvents();
            },

            bindEvents: function() {
                const self = this;

                // Close modal
                this.$modal.on( 'click', '.wpzoom-rating-modal-close, .wpzoom-rating-modal-overlay', function() {
                    self.close();
                } );

                // Close on escape key
                $( document ).on( 'keydown', function( e ) {
                    if ( e.key === 'Escape' && self.$modal && self.$modal.is( ':visible' ) ) {
                        self.close();
                    }
                } );

                // Star click in modal
                this.$modal.on( 'click', '.wpzoom-rating-modal-star', function() {
                    const rating = $( this ).data( 'rating' );
                    self.setModalRating( rating );
                } );

                // Star hover in modal
                this.$modal.on( 'mouseenter', '.wpzoom-rating-modal-star', function() {
                    const rating = $( this ).data( 'rating' );
                    self.highlightStars( rating );
                } );

                this.$modal.on( 'mouseleave', '.wpzoom-rating-modal-stars', function() {
                    self.highlightStars( self.currentRating );
                } );

                // Form submission
                this.$modal.on( 'submit', '.wpzoom-rating-modal-form', function( e ) {
                    e.preventDefault();
                    self.submitRating();
                } );
            },

            open: function( recipeId, recipeName, initialRating, postId ) {
                // Initialize if not already done
                if ( ! this.initialized ) {
                    this.init();
                }

                if ( ! this.$modal || ! this.$modal.length ) {
                    console.warn( 'WPZOOM Rating Modal: Modal element not found' );
                    return;
                }

                this.currentRecipeId = recipeId;
                this.currentPostId = postId || wpzoomRatingStars.current_post_id || recipeId; // Prefer current page/post for embedded recipes
                this.currentRating = initialRating || 0;

                // Set recipe info
                this.$modal.find( '.wpzoom-rating-modal-recipe-name' ).text( recipeName || '' );
                this.$modal.find( 'input[name="wpzoom_rating_recipe_id"]' ).val( recipeId );
                this.$modal.find( 'input[name="wpzoom_rating_value"]' ).val( this.currentRating );

                // Reset form
                var $form = this.$modal.find( '.wpzoom-rating-modal-form' );
                if ( $form.length && $form[0] ) {
                    $form[0].reset();
                }
                this.$modal.find( 'input[name="wpzoom_rating_recipe_id"]' ).val( recipeId );

                // Pre-fill user info if logged in
                if ( wpzoomRatingStars.user_name ) {
                    this.$modal.find( 'input[name="wpzoom_rating_name"]' ).val( wpzoomRatingStars.user_name );
                }
                if ( wpzoomRatingStars.user_email ) {
                    this.$modal.find( 'input[name="wpzoom_rating_email"]' ).val( wpzoomRatingStars.user_email );
                }

                // Show form, hide thank you and loading
                this.$modal.find( '.wpzoom-rating-modal-form-wrapper' ).show();
                this.$modal.find( '.wpzoom-rating-modal-thank-you' ).hide();
                this.$modal.find( '.wpzoom-rating-modal-loading' ).hide();
                this.$modal.find( '.wpzoom-rating-modal-error' ).hide();

                // Update stars
                this.setModalRating( this.currentRating );

                // Show modal
                this.$modal.css( 'display', 'flex' );
                $( 'body' ).css( 'overflow', 'hidden' );

                // Focus first input or textarea
                var self = this;
                setTimeout( function() {
                    var $firstInput = self.$modal.find( 'textarea, input[type="text"]' ).first();
                    if ( $firstInput.length ) {
                        $firstInput.focus();
                    }
                }, 100 );
            },

            close: function() {
                if ( this.$modal ) {
                    this.$modal.hide();
                }
                $( 'body' ).css( 'overflow', '' );
                this.currentRecipeId = null;
                this.currentPostId = null;
                this.currentRating = 0;
            },

            setModalRating: function( rating ) {
                this.currentRating = rating;
                if ( this.$modal ) {
                    this.$modal.find( 'input[name="wpzoom_rating_value"]' ).val( rating );
                }
                this.highlightStars( rating );
                this.updateCommentRequirement( rating );
            },

            updateCommentRequirement: function( rating ) {
                if ( ! this.$modal ) return;

                var forceComment = wpzoomRatingStars.force_comment || 'disabled';
                var commentRequired = false;

                if ( forceComment === 'always' ) {
                    commentRequired = true;
                } else if ( forceComment !== 'disabled' && ! isNaN( parseInt( forceComment ) ) ) {
                    commentRequired = rating <= parseInt( forceComment );
                }

                var $commentField = this.$modal.find( '.wpzoom-rating-modal-field-comment' );
                var $textarea = $commentField.find( 'textarea' );
                var $publishCommentCheckbox = $commentField.find( 'input[name="wpzoom_rating_publish_comment"]' );

                if ( ! $textarea.length ) {
                    return;
                }

                // Store original placeholder on first access
                if ( ! $textarea.attr( 'data-original-placeholder' ) ) {
                    $textarea.attr( 'data-original-placeholder', $textarea.attr( 'placeholder' ) || '' );
                }

                if ( commentRequired ) {
                    $commentField.addClass( 'is-required' );
                    if ( $publishCommentCheckbox.length ) {
                        $publishCommentCheckbox.prop( 'checked', true ).prop( 'disabled', true );
                    }
                    // Update placeholder to indicate required - remove "(optional)" and add "*"
                    var originalPlaceholder = $textarea.attr( 'data-original-placeholder' ) || '';
                    var requiredPlaceholder = originalPlaceholder.replace( /\s*\(optional\)\s*/gi, '' ).trim() + ' *';
                    $textarea.attr( 'placeholder', requiredPlaceholder );
                } else {
                    $commentField.removeClass( 'is-required' );
                    if ( $publishCommentCheckbox.length ) {
                        $publishCommentCheckbox.prop( 'disabled', false );
                    }
                    // Restore original placeholder
                    var originalPlaceholder = $textarea.attr( 'data-original-placeholder' );
                    if ( originalPlaceholder ) {
                        $textarea.attr( 'placeholder', originalPlaceholder );
                    }
                }
            },

            highlightStars: function( rating ) {
                if ( ! this.$modal ) return;

                this.$modal.find( '.wpzoom-rating-modal-star' ).each( function() {
                    var starRating = $( this ).data( 'rating' );
                    if ( starRating <= rating ) {
                        $( this ).removeClass( 'wpz-empty-star' ).addClass( 'wpz-full-star' );
                    } else {
                        $( this ).removeClass( 'wpz-full-star' ).addClass( 'wpz-empty-star' );
                    }
                } );
            },

            submitRating: function() {
                var self = this;
                var $form = this.$modal.find( '.wpzoom-rating-modal-form' );
                var $submitBtn = $form.find( '.wpzoom-rating-modal-submit' );
                var $error = this.$modal.find( '.wpzoom-rating-modal-error' );
                var showNameField = wpzoomRatingStars.show_name !== '0';
                var showEmailField = wpzoomRatingStars.show_email !== '0';

                // Validate rating
                if ( ! this.currentRating || this.currentRating < 1 ) {
                    $error.text( wpzoomRatingStars.strings.rating_required || 'Please select a rating' ).show();
                    return;
                }

                // Validate required fields
                var name = showNameField ? $form.find( 'input[name="wpzoom_rating_name"]' ).val().trim() : '';
                var email = showEmailField ? $form.find( 'input[name="wpzoom_rating_email"]' ).val().trim() : '';
                var $commentInput = $form.find( 'textarea[name="wpzoom_rating_comment"]' );
                var $publishCommentCheckbox = $form.find( 'input[name="wpzoom_rating_publish_comment"]' );
                var comment = $commentInput.length ? ( $commentInput.val() || '' ).trim() : '';
                var publishComment = $publishCommentCheckbox.length ? $publishCommentCheckbox.is( ':checked' ) : true;

                if ( showNameField && wpzoomRatingStars.require_name === '1' && ! name ) {
                    $error.text( wpzoomRatingStars.strings.name_required || 'Please enter your name' ).show();
                    return;
                }

                if ( showEmailField ) {
                    if ( wpzoomRatingStars.require_email === '1' && ! email ) {
                        $error.text( wpzoomRatingStars.strings.email_required || 'Please enter your email' ).show();
                        return;
                    }
                    // Validate any provided email, even when field is optional
                    if ( email ) {
                        var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                        if ( ! emailRegex.test( email ) ) {
                            $error.text( wpzoomRatingStars.strings.email_invalid || 'Please enter a valid email' ).show();
                            return;
                        }
                    }
                }

                // Check if comment is required based on force_comment setting and current rating
                var forceComment = wpzoomRatingStars.force_comment || 'disabled';
                var commentRequired = false;

                if ( forceComment === 'always' ) {
                    commentRequired = true;
                } else if ( forceComment !== 'disabled' && ! isNaN( parseInt( forceComment ) ) ) {
                    // forceComment is a number (e.g., '3' means required for 3 stars or less)
                    commentRequired = this.currentRating <= parseInt( forceComment );
                }

                if ( commentRequired && ! comment ) {
                    $error.text( wpzoomRatingStars.strings.comment_required || 'Please leave a comment' ).show();
                    return;
                }

                $error.hide();

                // Show loading
                $submitBtn.prop( 'disabled', true );
                this.$modal.find( '.wpzoom-rating-modal-form-wrapper' ).hide();
                this.$modal.find( '.wpzoom-rating-modal-loading' ).show();

                var data = {
                    action: 'wpzoom_user_vote_recipe',
                    rating: this.currentRating,
                    recipe_id: this.currentRecipeId,
                    post_id: this.currentPostId,
                    rating_name: name,
                    rating_email: email,
                    rating_comment: comment,
                    publish_review_comment: publishComment ? '1' : '0',
                    security: wpzoomRatingStars.ajax_nonce,
                };

                $.post( wpzoomRatingStars.ajaxurl, data, function( response ) {
                    self.$modal.find( '.wpzoom-rating-modal-loading' ).hide();
                    $submitBtn.prop( 'disabled', false );

                    if ( response.success ) {
                        // Update stars on the page
                        var $container = $( '.wpzoom-rating-stars-container[data-recipe-id="' + self.currentRecipeId + '"]' );
                        if ( $container.length ) {
                            $container.find( '.wpzoom-rating-average' ).text( response.data.rating_avg );
                            $container.find( '.wpzoom-rating-total-votes' ).text( response.data.rating_total );
                            $container.data( 'rating', response.data.rating_avg );
                            $container.data( 'rating-total', response.data.rating_total );
                            $container.find( '.wpzoom-rating-stars' ).addClass( 'wpzoom-recipe-user-rated' );

                            // Trigger syncRating on the star element
                            var starInstance = $container.find( '.wpzoom-rating-stars' ).data( 'star-rating' );
                            if ( starInstance ) {
                                starInstance.options.rating = response.data.rating_avg;
                                starInstance.options.rating_total = response.data.rating_total;
                                starInstance.options.user_rating = self.currentRating;
                                starInstance.syncRating();
                            }
                        }

                        // Show thank you message
                        self.$modal.find( '.wpzoom-rating-modal-thank-you' ).show();

                        // Close modal after delay
                        setTimeout( function() {
                            self.close();
                        }, 2500 );
                    } else {
                        // Show error
                        self.$modal.find( '.wpzoom-rating-modal-form-wrapper' ).show();
                        $error.text( response.data.message || 'An error occurred' ).show();
                    }
                } ).fail( function() {
                    self.$modal.find( '.wpzoom-rating-modal-loading' ).hide();
                    self.$modal.find( '.wpzoom-rating-modal-form-wrapper' ).show();
                    $submitBtn.prop( 'disabled', false );
                    $error.text( 'An error occurred. Please try again.' ).show();
                } );
            }
        };

        // Initialize modal immediately
        RatingModal.init();

        // Make globally accessible
        window.WPZOOMRatingModal = RatingModal;

        // Handle footer CTA "Rate this recipe" trigger.
        $( document ).on( 'click', '.recipe-card-cta-rate-trigger', function( e ) {
            e.preventDefault();

            var $trigger = $( this );
            var $card = $trigger.closest( '.wp-block-wpzoom-recipe-card-block-recipe-card' );
            var $ratingContainer = $card.length ? $card.find( '.wpzoom-rating-stars-container' ).first() : $( '.wpzoom-rating-stars-container' ).first();

            if ( ! $ratingContainer.length ) {
                return;
            }

            var recipeId = parseInt( $ratingContainer.data( 'recipe-id' ), 10 ) || 0;
            if ( ! recipeId ) {
                return;
            }

            var postId = parseInt( $ratingContainer.data( 'post-id' ), 10 ) || parseInt( wpzoomRatingStars.current_post_id, 10 ) || recipeId;
            var recipeName = '';
            var ratingMode = wpzoomRatingStars.rating_mode || 'instant';

            if ( $card.length ) {
                recipeName = $.trim( $card.find( '.recipe-card-title' ).first().text() );
            }

            if ( ratingMode === 'modal' && window.WPZOOMRatingModal && typeof window.WPZOOMRatingModal.open === 'function' ) {
                window.WPZOOMRatingModal.open( recipeId, recipeName, 0, postId );
                return;
            }

            if ( ratingMode === 'jump_to_comments' ) {
                var $comments = $( '#respond, #comments, .comments-area' ).first();
                if ( $comments.length ) {
                    var commentsNode = $comments.get( 0 );
                    if ( commentsNode && typeof commentsNode.scrollIntoView === 'function' ) {
                        commentsNode.scrollIntoView( { behavior: 'smooth', block: 'start' } );
                        return;
                    }
                }

                if ( window.WPZOOMRatingModal && typeof window.WPZOOMRatingModal.open === 'function' ) {
                    window.WPZOOMRatingModal.open( recipeId, recipeName, 0, postId );
                    return;
                }
            }

            var ratingNode = $ratingContainer.get( 0 );
            if ( ratingNode && typeof ratingNode.scrollIntoView === 'function' ) {
                ratingNode.scrollIntoView( { behavior: 'smooth', block: 'center' } );
            }
        } );

        class WPZOOM_Rating_Star {
            constructor( $el, options ) {
                let i,
                    _,
                    _ref,
                    _this = this;
                this.$el = $el;
                this.defaults = {
                    rating: this.$el.parent().data( 'rating' ),
                    rating_total: this.$el.parent().data( 'rating-total' ),
                    recipe_id: this.$el.parent().data( 'recipe-id' ),
                    post_id: this.$el.parent().data( 'post-id' ),
                    user_rating: void 0,
                    numStars: 5,
                    change: function( e, value ) { },
                };
                this.options = $.extend( {}, this.defaults, options );
                _ref = this.defaults;
                for ( i in _ref ) {
                    _ = _ref[ i ];
                    if ( this.$el.data( i ) != null ) {
                        this.options[ i ] = this.$el.data( i );
                    }
                }
                this.$el
                    .next()
                    .find( '.wpzoom-rating-average' )
                    .html( this.options.rating );
                this.$el
                    .next()
                    .find( '.wpzoom-rating-total-votes' )
                    .html( this.options.rating_total );
                this.syncRating();
                this.$el.on( 'mouseover.starrr', 'li', function( e ) {
                    return _this.syncRating(
                        _this.$el.find( 'li' ).index( e.currentTarget ) + 1
                    );
                } );
                this.$el.on( 'mouseout.starrr', function() {
                    return _this.syncRating();
                } );
                this.$el.on( 'click.starrr', 'li', function( e ) {
                    var element = $( this );
                    var rating = _this.$el.find( 'li' ).index( e.currentTarget ) + 1;

                    // Check rating mode
                    var ratingMode = wpzoomRatingStars.rating_mode || 'instant';

                    if ( ratingMode === 'modal' ) {
                        // Get recipe name from the page
                        var recipeName = '';
                        var $recipeCard = element.closest( '.wp-block-wpzoom-recipe-card-block-recipe-card, .wpzoom-recipe-card' );
                        if ( $recipeCard.length ) {
                            recipeName = $recipeCard.find( '.recipe-card-title, .wpzoom-rcb-post-title' ).first().text();
                        }
                        if ( ! recipeName ) {
                            recipeName = $( 'h1.entry-title, h1.post-title, .recipe-card-title' ).first().text();
                        }

                        RatingModal.open( _this.options.recipe_id, recipeName, rating, _this.options.post_id );
                        return false;
                    } else if ( ratingMode === 'jump_to_comments' ) {
                        // Check if comment form exists - prioritize the form over comments list
                        var $commentForm = $( '#respond, .comment-respond, #commentform' ).first();
                        var $ratingForm = $( '.wpzoom-rcb-comment-rating-form' );

                        if ( $commentForm.length && $commentForm.is( ':visible' ) ) {
                            // Pre-select the rating in the comment form rating stars
                            if ( $ratingForm.length ) {
                                // Click the corresponding rating radio input
                                var $ratingInput = $ratingForm.find( 'input[name="wpzoom-rcb-comment-rating"][value="' + rating + '"]' );
                                if ( $ratingInput.length ) {
                                    $ratingInput.prop( 'checked', true ).trigger( 'change' );

                                    // Update star visual appearance
                                    $ratingForm.find( '.wpzoom-rcb-comment-rating-stars label .wpz-star-icon' ).each( function() {
                                        var $star = $( this );
                                        var $label = $star.closest( 'label' );
                                        var starValue = parseInt( $label.find( 'input' ).val() );

                                        if ( starValue > 0 && starValue <= rating ) {
                                            $star.removeClass( 'wpz-empty-star' ).addClass( 'wpz-full-star' );
                                        } else {
                                            $star.removeClass( 'wpz-full-star' ).addClass( 'wpz-empty-star' );
                                        }
                                    } );
                                }
                            }

                            // Scroll to comment form
                            $( 'html, body' ).animate( {
                                scrollTop: $commentForm.offset().top - 100
                            }, 500, function() {
                                // Focus on comment textarea after scroll
                                var $textarea = $commentForm.find( 'textarea#comment, textarea[name="comment"]' ).first();
                                if ( $textarea.length ) {
                                    $textarea.focus();
                                }
                            } );
                            return false;
                        } else {
                            // Fallback to modal if no comment form
                            var recipeName = '';
                            var $recipeCard = element.closest( '.wp-block-wpzoom-recipe-card-block-recipe-card, .wpzoom-recipe-card' );
                            if ( $recipeCard.length ) {
                                recipeName = $recipeCard.find( '.recipe-card-title, .wpzoom-rcb-post-title' ).first().text();
                            }
                            if ( ! recipeName ) {
                                recipeName = $( 'h1.entry-title, h1.post-title, .recipe-card-title' ).first().text();
                            }

                            RatingModal.open( _this.options.recipe_id, recipeName, rating, _this.options.post_id );
                            return false;
                        }
                    } else {
                        // Instant mode (legacy)
                        return _this.setRating( rating, element );
                    }
                } );
                this.$el.on( 'starrr:change', this.options.change );
            }
            setRating( rating, element ) {
                // prevent user multiple votes with same rating value
                if (
                    element.parent().hasClass( 'wpzoom-recipe-user-rated' ) &&
                    parseInt( this.options.user_rating ) === rating
                ) {
                    return false;
                }

                let _this = this,
                    rating_avg = this.options.rating,
                    rating_total = this.options.rating_total,
                    recipe_id = this.options.recipe_id;

                // store user rating
                this.options.user_rating = rating;

                const data = {
                    action: 'wpzoom_user_vote_recipe',
                    rating: rating,
                    recipe_id: recipe_id,
                    security: wpzoomRatingStars.ajax_nonce,
                };

                element
                    .parents( '.wpzoom-rating-stars-container' )
                    .addClass( 'is-loading' );

                $.post( wpzoomRatingStars.ajaxurl, data, function( response ) {
                    const data = response.data;
                    if ( response.success ) {
                        rating_avg = data.rating_avg;
                        rating_total = data.rating_total;

                        element.parent().next().find( '.wpzoom-rating-average' ).html( rating_avg );
                        element.parent().data( 'rating', rating_avg );
                        element.parent().next().find( '.wpzoom-rating-total-votes' ).html( rating_total );
                        element.parent().data( 'rating-total', rating_total );
                        element.parents( '.wpzoom-rating-stars-container' ).removeClass( 'is-loading' );

                        if ( ! element.parent().hasClass( 'wpzoom-recipe-user-rated' ) ) {
                            element
                                .parent()
                                .addClass( 'wpzoom-recipe-user-rated' );
                        }
                    } else {
                        element.parents( '.wpzoom-rating-stars-container' ).removeClass( 'is-loading' );
                        element.parents( '.wpzoom-rating-stars-container' ).attr( 'data-user-can-rate', '0' );
                        element.parents( '.wpzoom-rating-stars-container' ).find( '.wpzoom-rating-stars-tooltip' ).html( data.message );
                    }
                } ).done( function( response ) {
                    const data = response.data;
                    if ( response.success ) {
                        _this.options.rating = data.rating_avg;
                        _this.options.rating_total = data.rating_total;
                        _this.syncRating();
                        return _this.$el.trigger( 'starrr:change', data.rating_avg );
                    }
                } );
            }
            syncRating( rating ) {

                let i, _i, _j, _ref;

                // Make sure we make full stars
                if ( rating ) {
                    rating = parseFloat( rating );
                } else {
                    rating = parseFloat( this.options.rating );
                }

                this.$el
                .find( 'li' )
                .removeClass( 'wpz-one-fourth-star' )
                .removeClass( 'wpz-one-half-star' )
                .removeClass( 'wpz-three-quarters-star' );


                let integer_average = Math.floor( rating ),
                    float_average = rating - integer_average;

                //Add full stars
                for ( i = 0, _ref = integer_average; i < _ref; i++ ) {

                    this.$el
                        .find('li')
                        .eq(i)
                        .removeClass('wpz-empty-star')
                        .addClass('wpz-full-star');
                }

                // check float value and add class to stars
                if ( float_average > 0 ) {

                    if ( 0.05 < float_average && 0.35 >= float_average ) {
                        this.$el
                        .find('li')
                        .eq( integer_average )
                        .removeClass('wpz-empty-star')
                        .removeClass('wpz-full-star')
                        .addClass('wpz-one-fourth-star');
                    }
                    else if( 0.35 < float_average && 0.65 >= float_average ) {
                        this.$el
                            .find('li')
                            .eq( integer_average )
                            .removeClass('wpz-empty-star')
                            .removeClass('wpz-full-star')
                            .addClass('wpz-one-half-star');
                    } else if( 0.65 < float_average && 0.95 >= float_average ) {
                        this.$el
                            .find('li')
                            .eq( integer_average )
                            .removeClass('wpz-empty-star')
                            .removeClass('wpz-full-star')
                            .addClass('wpz-three-quarters-star');
                    } else if( 0.95 < float_average ) {
                        this.$el
                            .find('li')
                            .eq( integer_average )
                            .removeClass('wpz-empty-star')
                            .removeClass('wpz-full-star')
                            .addClass('wpz-full-star');
                    }
                }

                if ( rating && rating < 5 ) {
                    // Calculate how many stars should be filled (including partial stars)
                    let filledStars = integer_average + (float_average > 0 ? 1 : 0);
                    for (
                        i = filledStars;
                        i < 5;
                        i++
                    ) {
                        this.$el
                            .find( 'li' )
                            .eq( i )
                            .removeClass( 'wpz-full-star' )
                            .addClass( 'wpz-empty-star' );
                    }
                }

                if ( ! rating ) {
                    return this.$el
                        .find( 'li' )
                        .removeClass( 'wpz-full-star' )
                       .addClass( 'wpz-empty-star' );
                }

            }
        }

        return $.fn.extend( {
            starrr: function() {
                let args, option;

                ( option = arguments[ 0 ] ),
                ( args = 2 <= arguments.length ? __slice.call( arguments, 1 ) : [] );
                return this.each( function() {
                    let data;

                    data = $( this ).data( 'star-rating' );
                    if ( ! data ) {
                        $( this ).data(
                            'star-rating',
                            ( data = new WPZOOM_Rating_Star( $( this ), option ) )
                        );
                    }
                    if ( typeof option === 'string' ) {
                        return data[ option ].apply( data, args );
                    }
                } );
            },
        } );
    }( jQuery, wpzoomRatingStars ) );

    jQuery( 'ul.wpzoom-rating-stars' ).starrr();
} );
