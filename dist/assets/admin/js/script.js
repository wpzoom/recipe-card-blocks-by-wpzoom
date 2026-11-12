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

			// The left sub-menu follows the tab.
			syncSubnav( query_args['tab'] );

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
					if ( response.success ) {
						var query_args = getUrlVars( window.location.href );

						if ( query_args.length > 0 ) {
							window.location.href = window.location.href + "&wpzoom_reset_settings=1";
						} else {
							window.location.href = window.location.href + "?wpzoom_reset_settings=1";
						}
					} else {
						alert('Something went wrong when tried to reset the settings!')
					}
				});
			}
		});

		// Cook Mode sub-options only apply when the toggle itself is on
		function showCookModeOptions() {
			if ($('input[name="wpzoom-recipe-card-settings[wpzoom_rcb_settings_recipe_enable_prevent_sleep_toggle]"]:checked').val() !== '1') {
				$('tr.required-cook-mode').hide();
			} else {
				$('tr.required-cook-mode').show();
			}
		}

		showCookModeOptions();
		$('input[name="wpzoom-recipe-card-settings[wpzoom_rcb_settings_recipe_enable_prevent_sleep_toggle]"]').on('change', showCookModeOptions);

		// Rating display sub-options only apply when the auto-insert toggle is on
		function showAutoRatingStarsOptions() {
			if ($('input[name="wpzoom-recipe-card-settings[wpzoom_rcb_settings_display_rating_stars]"]:checked').val() !== '1') {
				$('tr.required-auto-rating-stars').hide();
			} else {
				$('tr.required-auto-rating-stars').show();
			}
		}

		showAutoRatingStarsOptions();
		$('input[name="wpzoom-recipe-card-settings[wpzoom_rcb_settings_display_rating_stars]"]').on('change', showAutoRatingStarsOptions);

		// reset all ratings to zero
		$('#wpzoom_rcb_settings_reset_ratings').on( 'click', function(){
			var data = {
			    security: Settings.ajax_nonce,
			    action: 'wpzoom_reset_ratings',
			};
			var $this = $(this);

			if (window.confirm("Do you really want to Reset all ratings?")) {

				$this.val('Loading...');

				$.post( Settings.ajaxUrl, data, function(response){
					if ( response.success ) {
						$this.val('Done!');
						$this.prop('disabled', true);
						$this.next().html(response.data.message);
					} else {
						$this.val('Reset Ratings');
						alert( ( response.data && response.data.message ) || 'Something went wrong when trying to reset the ratings!' );
					}
				});
			}
		});

		// close Welcome banner
		$('.wpzoom-rcb-welcome-close').click(function(e){
			e.preventDefault();

			var banner = $(this).attr('href');
			var data = {
			    security: Settings.ajax_nonce,
			    action: 'wpzoom_welcome_banner_close',
			};

			$(banner).fadeOut();

			$.post( Settings.ajaxUrl, data, function(response){
				if ( ! response.success ) {
					alert('Something went wrong!')
				}
			});
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

        // setting field preview
        $('.wpzoom-rcb-field-preview').each(function(){
            var $this = $(this),
                $field = $(this).parents('fieldset');
            var thumbnail = $(this).data('preview-thumbnail'),
                position = $(this).data('preview-position');

            $(this).on('mouseover', function(){

                if ( $this.hasClass('active') ) {
                    $this.removeClass('active');
                    $field.find('.wpzoom-rcb-field-preview-thumbnail').remove();
                    return;
                }

                $this.addClass('active');
                $field.append('<span class="wpzoom-rcb-field-preview-thumbnail preview-position-'+ position +'"><img src="'+ thumbnail +'" width="400" height="300"></span>');

                $('.wpzoom-rcb-field-preview').not(this).parent().find('.wpzoom-rcb-field-preview-thumbnail').remove();
                $('.wpzoom-rcb-field-preview').not(this).removeClass('active');
                
            });

            $(this).on('mouseout', function(){

                if ( $this.hasClass('active') ) {
                    $this.removeClass('active');
                    $field.find('.wpzoom-rcb-field-preview-thumbnail').remove();
                    return;
                }

            });

        });

        // Add Color Picker to all inputs that have 'color-field' class
        $('.wpzoom-rcb-color-picker').wpColorPicker({
            change: function(event, ui){
                var $this = $(this);
                setTimeout(function(){
                    $this.val( ui.color.toString().toUpperCase() ) // uppercase color value
                },1);
            }
        });


		// ---------------------------------------------------------------------------------
		// Sub-menu (3.8.0): the sections of the active tab, listed on the left.
		// ---------------------------------------------------------------------------------

		// True right after a sub-menu or search click, so the scroll spy does not move the
		// highlight away from what was just picked. Cleared when the user scrolls by hand.
		var subnavPinned = false;

		function scrollOffset() {
			return ( $( '#wpadminbar' ).outerHeight() || 0 ) + 20;
		}

		function scrollToElement( $element ) {
			$( 'html, body' ).stop( true ).animate( { scrollTop: Math.max( 0, $element.offset().top - scrollOffset() ) }, 200 );
		}

		function syncSubnav( tabId ) {
			var $navs = $( '.wpzoom-sidebar-nav' );
			var $nav  = $navs.filter( '[data-tab="' + tabId + '"]' );

			$navs.prop( 'hidden', true );
			$nav.prop( 'hidden', false );
			$( '.wpzoom-settings-layout' ).toggleClass( 'has-subnav', $nav.length > 0 );

			subnavPinned = false;
			updateActiveSection();
		}

		function updateActiveSection() {
			var $links = $( '.wpzoom-sidebar-nav:not([hidden]) .wpzoom-sidebar-link' );

			if ( ! $links.length || subnavPinned ) {
				return;
			}

			var line    = $( window ).scrollTop() + scrollOffset() + 60;
			var current = $links.get( 0 );

			$links.each( function() {
				var $section = $( $( this ).attr( 'href' ) );

				if ( $section.length && $section.is( ':visible' ) && $section.offset().top <= line ) {
					current = this;
				}
			} );

			$links.removeClass( 'active' ).removeAttr( 'aria-current' );
			$( current ).addClass( 'active' ).attr( 'aria-current', 'true' );
		}

		$( '.wpzoom-sidebar-link' ).on( 'click', function( event ) {
			var $section = $( $( this ).attr( 'href' ) );

			if ( ! $section.length ) {
				return;
			}

			event.preventDefault();

			$( this ).closest( '.wpzoom-sidebar-nav' ).find( '.wpzoom-sidebar-link' ).removeClass( 'active' ).removeAttr( 'aria-current' );
			$( this ).addClass( 'active' ).attr( 'aria-current', 'true' );

			subnavPinned = true;
			scrollToElement( $section );
		} );

		var spyTimer;
		$( window ).on( 'scroll resize', function() {
			clearTimeout( spyTimer );
			spyTimer = setTimeout( updateActiveSection, 50 );
		} );
		$( window ).on( 'wheel touchmove keydown', function() {
			subnavPinned = false;
		} );

		// The tabs were activated above, before the sub-menu existed for them.
		syncSubnav( $( '.wp-tab-bar li.wp-tab-active' ).data( 'tab' ) );

		// ---------------------------------------------------------------------------------
		// Search (3.8.0): finds a setting in any tab by its label, section or description.
		// ---------------------------------------------------------------------------------

		var $searchInput   = $( '#wpzoom-settings-search' );
		var $searchResults = $( '.wpzoom-settings-search-results' );
		var searchIndex    = null;
		var MAX_RESULTS    = 8;

		function buildSearchIndex() {
			var index = [];

			$( '.wpzoom-settings-main .wp-tab-panel' ).each( function() {
				var tabId    = this.id;
				var tabTitle = $.trim( $( '.wp-tab-bar li[data-tab="' + tabId + '"] a' ).clone().children().remove().end().text() );

				$( this ).find( '.wpzoom-settings-section' ).each( function() {
					var section      = this;
					var sectionTitle = $.trim( $( section ).children( 'h2' ).first().text() );

					$( section ).find( '.form-table tr' ).each( function() {
						var $row  = $( this );
						var label = $.trim( $row.children( 'th' ).text() );

						if ( ! label ) {
							return;
						}

						var extra = $.trim( $row.find( 'td label, td .description' ).text() );

						index.push( {
							row: this,
							section: section,
							tabId: tabId,
							tabTitle: tabTitle,
							sectionTitle: sectionTitle,
							label: label,
							labelLower: label.toLowerCase(),
							haystack: ( label + ' ' + sectionTitle + ' ' + tabTitle + ' ' + extra ).toLowerCase()
						} );
					} );
				} );
			} );

			return index;
		}

		function hideSearchResults() {
			$searchResults.prop( 'hidden', true ).empty();
		}

		function runSearch() {
			var words = $.trim( $searchInput.val() ).toLowerCase().split( /\s+/ ).filter( Boolean );

			if ( ! words.length ) {
				hideSearchResults();
				return;
			}

			searchIndex = searchIndex || buildSearchIndex();

			var matches = function( text ) {
				return words.every( function( word ) {
					return text.indexOf( word ) !== -1;
				} );
			};

			// Label matches first, then matches in the section, tab or description.
			var inLabel = [], elsewhere = [];
			$.each( searchIndex, function( i, entry ) {
				if ( matches( entry.labelLower ) ) {
					inLabel.push( entry );
				} else if ( matches( entry.haystack ) ) {
					elsewhere.push( entry );
				}
			} );

			var hits = inLabel.concat( elsewhere ).slice( 0, MAX_RESULTS );

			$searchResults.empty();

			if ( ! hits.length ) {
				$( '<div class="wpzoom-settings-search-empty"></div>' ).text( Settings.i18n ? Settings.i18n.noResults : 'No settings found' ).appendTo( $searchResults );
			}

			$.each( hits, function( i, entry ) {
				var $item = $( '<button type="button" class="wpzoom-settings-search-result"></button>' );

				$( '<span class="wpzoom-settings-search-result-label"></span>' ).text( entry.label ).appendTo( $item );
				$( '<span class="wpzoom-settings-search-result-path"></span>' ).text( entry.sectionTitle ? entry.tabTitle + ' \u203A ' + entry.sectionTitle : entry.tabTitle ).appendTo( $item );

				$item.data( 'entry', entry ).appendTo( $searchResults );
			} );

			$searchResults.prop( 'hidden', false );
		}

		function goToSearchResult( entry ) {
			var $tab = $( '.wp-tab-bar li[data-tab="' + entry.tabId + '"]' );

			if ( ! $tab.hasClass( 'wp-tab-active' ) ) {
				$tab.find( 'a' ).trigger( 'click' );
			}

			hideSearchResults();
			$searchInput.trigger( 'blur' );

			// A row can be hidden by another option (Cook Mode off, for example): show its section then.
			var $row    = $( entry.row );
			var $target = $row.is( ':visible' ) ? $row : $( entry.section );

			subnavPinned = true;
			$( '.wpzoom-sidebar-nav:not([hidden]) .wpzoom-sidebar-link' ).removeClass( 'active' ).removeAttr( 'aria-current' )
				.filter( '[href="#' + entry.section.id + '"]' ).addClass( 'active' ).attr( 'aria-current', 'true' );

			scrollToElement( $target );

			$( '.wpzoom-rcb-search-hit' ).removeClass( 'wpzoom-rcb-search-hit' );
			$row.addClass( 'wpzoom-rcb-search-hit' );
			setTimeout( function() {
				$row.removeClass( 'wpzoom-rcb-search-hit' );
			}, 2500 );
		}

		$searchInput.on( 'input focus', runSearch );

		$searchInput.on( 'keydown', function( event ) {
			var $items   = $searchResults.find( '.wpzoom-settings-search-result' );
			var $focused = $items.filter( '.is-focused' );

			if ( 'Enter' === event.key ) {
				// Never submit the settings form from the search box.
				event.preventDefault();

				var $pick = $focused.length ? $focused : $items.first();
				if ( $pick.length ) {
					goToSearchResult( $pick.data( 'entry' ) );
				}
			} else if ( 'Escape' === event.key ) {
				$searchInput.val( '' );
				hideSearchResults();
			} else if ( 'ArrowDown' === event.key || 'ArrowUp' === event.key ) {
				if ( ! $items.length ) {
					return;
				}

				event.preventDefault();

				var index = $items.index( $focused ) + ( 'ArrowDown' === event.key ? 1 : -1 );
				index = ( index + $items.length ) % $items.length;

				$items.removeClass( 'is-focused' ).eq( index ).addClass( 'is-focused' );
			}
		} );

		$searchResults.on( 'click', '.wpzoom-settings-search-result', function() {
			goToSearchResult( $( this ).data( 'entry' ) );
		} );

		$( document ).on( 'click', function( event ) {
			if ( ! $( event.target ).closest( '.wpzoom-settings-search-wrap' ).length ) {
				hideSearchResults();
			}
		} );

	})(jQuery, WPZOOM_Settings);
});
