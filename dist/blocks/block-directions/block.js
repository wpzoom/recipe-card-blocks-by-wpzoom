( function( blocks, editor, i18n, element, components, _ ) {
    var el = element.createElement;

    var atts = {
        title: {
            type: 'array',
            selector: '.directions-title',
            source: 'children'
        },
        content: {
            type: 'array',
            selector: '.directions-list',
            source: 'children'
        },
        id: {
            type: 'string',
        },
        print_visibility: {
            type: 'string',
            default: 'visible'
        },
    }

    blocks.registerBlockType( 'wpzoom-recipe-card/block-directions', {
        title: i18n.__( 'Directions' ),
        description: i18n.__( 'Add multiple steps directions.' ),
        keywords: ['directions', 'recipe', 'foodica'],
        icon: {
            // Specifying a background color to appear with the icon e.g.: in the inserter.
            background: '#2EA55F',
            // Specifying a color for the icon (optional: if not set, a readable color will be automatically defined)
            foreground: '#fff',
            // Specifying a dashicon for the block
            src: 'editor-ol',
        },
        category: 'wpzoom-recipe-card',
        attributes: atts,
        edit: function( props ) {
            var focusedEditable = props.focus ? props.focus.editable || 'title' : null;
            var attributes = props.attributes;

            /*
             * Event handlers
             */

            function onChangeTitle( newTitle ) {
                props.setAttributes( { title: newTitle } );

                // set unique id for block
                if ( ! attributes.id ) {
                    props.setAttributes( { id: randomID() } );
                }
            }

            function onFocusTitle( focus ) {
                props.setFocus( _.extend( {}, focus, { editable: 'title' } ) );
            }

            function onChangeContent( newContent ) {
                props.setAttributes( { content: newContent } );

                // set unique id for block
                if ( ! attributes.id ) {
                    props.setAttributes( { id: randomID() } );
                }
            }

            function onFocusContent( focus ) {
                props.setFocus( _.extend( {}, focus, { editable: 'content' } ) );
            }

            function onChangePrintVisibility( newVisibility ) {
                if ( ! newVisibility ) {
                    props.setAttributes( { print_visibility: 'hidden' } );
                } else {
                    props.setAttributes( { print_visibility: 'visible' } );
                }
            }

            function randomID( prefix ) {
                prefix = prefix || false;

                var text = "";
                var possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";

                for (var i = 0; i < 10; i++)
                    text += possible.charAt(Math.floor(Math.random() * possible.length));

                return prefix ? prefix + '-' + text : text;
            }

            
            return [
                el(
                    editor.InspectorControls,
                    { key: 'inspector' },
                    el(
                        'br', {}
                    ),
                    el(
                        'h3', {},
                        i18n.__( 'Block Settings' )
                    ),
                    el(
                        components.ToggleControl, {
                            label: i18n.__( 'Print Button Visibility' ),
                            checked: attributes.print_visibility === 'visible' ? true : false,
                            onChange: onChangePrintVisibility,
                        }
                    ),
                ),
                el(
                    'div',
                    {
                        className: props.className,
                        id: attributes.id,
                    },
                    el(
                        'div', {
                            className: 'wpzoom-recipe-card-print-link' + ' ' + attributes.print_visibility
                        },
                        el(
                            'a', {
                                className: 'btn-print-link no-print',
                                href: '#' + attributes.id,
                                title: i18n.__( 'Print directions...' )
                            },
                            el(
                                'img', {
                                    className: 'icon-print-link',
                                    src: wpzoomRecipeCard.plugin_url + '/dist/assets/images/printer.svg',
                                    alt: i18n.__( 'Print' )
                                }
                            ),
                            i18n.__( 'Print' )
                        )
                    ),
                    el(
                        editor.RichText, {
                            tagName: 'h3',
                            placeholder: i18n.__( 'Write Directions title' ),
                            value: attributes.title,
                            className: 'directions-title',
                            focus: focusedEditable === 'title' ? focus : null,
                            formattingControls: ['bold', 'italic'],
                            onChange: onChangeTitle,
                            onFocus: onFocusTitle,
                        }
                    ),
                    el(
                        editor.RichText, {
                            tagName: 'ol',
                            multiline: 'li',
                            placeholder: i18n.__( 'Write directions...' ),
                            value: attributes.content,
                            className: 'directions-list',
                            focus: focusedEditable === 'content' ? focus : null,
                            formattingControls: ['bold', 'italic'],
                            onChange: onChangeContent,
                            onFocus: onFocusContent,
                        },
                    ),
                    el(
                        'p', { className: 'help' }, i18n.__( 'Press Enter to add new direction...' )
                    )
                ),
            ];
        },
        save: function( props ) {
            var attributes = props.attributes;

            return el(
                'div',
                {
                    className: props.className,
                    id: attributes.id
                },
                el(
                    'div', {
                        className: 'wpzoom-recipe-card-print-link' + ' ' + attributes.print_visibility
                    },
                    el(
                        'a', {
                            className: 'btn-print-link no-print',
                            href: '#' + attributes.id,
                            title: i18n.__( 'Print directions...' )
                        },
                        el(
                            'img', {
                                className: 'icon-print-link',
                                src: wpzoomRecipeCard.plugin_url + '/dist/assets/images/printer.svg',
                                alt: i18n.__( 'Print' )
                            }
                        ),
                        i18n.__( 'Print' )
                    )
                ),
                el(
                    editor.RichText.Content, {
                        tagName: 'h3',
                        className: 'directions-title',
                        value: attributes.title
                    },
                ),
                el(
                    editor.RichText.Content, {
                        tagName: 'ul',
                        className: 'directions-list',
                        value: attributes.content
                    },
                )
            );
        },
    } );

})(
    window.wp.blocks,
    window.wp.editor,
    window.wp.i18n,
    window.wp.element,
    window.wp.components,
    window._,
);

