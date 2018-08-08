( function( blocks, editor, i18n, element, _ ) {
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
    }

    blocks.registerBlockType( 'wpzoom-recipe-card/block-directions', {
        title: i18n.__( 'Directions' ),
        description: i18n.__( 'Add multiple steps directions.' ),
        keywords: ['directions', 'recipe', 'foodica'],
        icon: 'editor-ol',
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
                props.setAttributes( { content: newContent } );
            }

            function onFocusContent( focus ) {
                props.setFocus( _.extend( {}, focus, { editable: 'content' } ) );
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
                        editor.RichText,
                        {
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
                        className: 'directions-title',
                        value: attributes.title
                    },
                ),
                el(
                    editor.RichText.Content,
                    {
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
    window._,
);

