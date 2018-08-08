( function( blocks, editor, i18n, element, _ ) {
    var el = element.createElement;

    var atts = {
        title: {
            type: 'array',
            selector: '.ingredients-title',
            source: 'children'
        },
        content: {
            type: 'array',
            selector: '.ingredients-list',
            source: 'children'
        },
    }

    blocks.registerBlockType( 'wpzoom-recipe-card/block-ingredients', {
        title: i18n.__( 'Ingredients' ),
        description: i18n.__( 'Add multiple ingredients.' ),
        keywords: ['ingredients', 'recipe', 'foodica'],
        icon: 'carrot',
        category: 'wpzoom-recipe-card',
        attributes: atts,
        edit: function( props ) {
            var focusedEditable = props.focus ? props.focus.editable || 'title' : null;
            var attributes = props.attributes;

            function onChangeTitle( newTitle ) {
                props.setAttributes( { title: newTitle } );
            }

            function onFocusTitle( focus ) {
                props.setFocus( _.extend( {}, focus, { editable: 'title' } ) );
            }

            function onChangeContent( newContent ) {
                var before = {
                        tagName: 'span',
                        className: 'tick'
                    },
                    after = false;

                newContent = insertBeforeAfterContent( newContent, before, after );

                props.setAttributes( { content: newContent } );
            }

            function onFocusContent( focus ) {
                props.setFocus( _.extend( {}, focus, { editable: 'content' } ) );
            }

            function prependArrayValue( value, array ) {
                var newArray = array.slice();
                newArray.unshift(value);
                return newArray;
            }

            function appendArrayValue( value, array ) {
                array.push(value);
                var newArray = array;
                return newArray;
            }

            function insertBeforeAfterContent( content, before, after ) {
                content = content || {};
                before  = before || { tagName: false };
                after   = after  || { tagName: false };

                for ( var i = 0; i < content.length; i++ ) {
                    if ( typeof content[i].props !== 'undefined' ) {
                        var children = content[i].props.children;

                        if ( before.tagName ) {
                            var b = el(
                                editor.RichText,
                                {
                                    tagName: before.tagName,
                                    className: (before.className ? before.className : null),
                                    value: (before.children ? before.children : null)
                                }
                            );

                            content[i].props.children = prependArrayValue( b, [ children ] );
                        }

                        if ( after.tagName ) {
                            var a = el(
                                editor.RichText,
                                {
                                    tagName: after.tagName,
                                    className: (after.className ? after.className : null),
                                    value: (after.children ? after.children : null)
                                }
                            );

                            content[i].props.children = appendArrayValue( a, children );
                        }
                    }
                }

                return content;
            }

            
            return render = [
                el(
                    'div',
                    {
                        className: props.className,
                    },
                    el(
                        editor.RichText,
                        {
                            tagName: 'h3',
                            placeholder: i18n.__( 'Write Ingredients title' ),
                            value: attributes.title,
                            className: 'ingredients-title',
                            focus: focusedEditable === 'title' ? focus : null,
                            formattingControls: ['bold', 'italic'],
                            onChange: onChangeTitle,
                            onFocus: onFocusTitle,
                        }
                    ),
                    el(
                        editor.RichText,
                        {
                            tagName: 'ul',
                            multiline: 'li',
                            placeholder: i18n.__( 'Write ingredients...' ),
                            value: attributes.content,
                            className: 'ingredients-list',
                            focus: focusedEditable === 'content' ? focus : null,
                            formattingControls: ['bold', 'italic'],
                            onChange: onChangeContent,
                            onFocus: onFocusContent,
                        },
                    ),
                ),
            ];
        },
        save: function( props ) {
            var attributes = props.attributes;

            return render = el( 'div', { className: props.className },
                el(
                    editor.RichText.Content,
                    {
                        tagName: 'h3',
                        className: 'ingredients-title',
                        value: attributes.title
                    },
                ),
                el(
                    editor.RichText.Content,
                    {
                        tagName: 'ul',
                        className: 'ingredients-list',
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
    window._,
);



