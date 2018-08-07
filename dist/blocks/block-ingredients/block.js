var el = wp.element.createElement,
    registerBlockType = wp.blocks.registerBlockType,
    block = wp.blocks.getBlockType,
    _e = wp.editor,
    RichText = _e.RichText;

var atts = {
    ingredientsTitle: {
        type: 'array',
        selector: '.ingredients-title',
        source: 'children'
    },
    ingredientsContent: {
        type: 'array',
        selector: '.ingredients-list',
        source: 'children'
    },
}

registerBlockType( 'wpzoom-recipe-card/block-ingredients', {
    title: 'Ingredients',
    description: 'Add multiple ingredients.',
    keywords: ['ingredients', 'recipe', 'foodica'],
    icon: 'carrot',
    category: 'wpzoom-recipe-card',
    attributes: atts,
    edit: function( props ) {
        var title = props.attributes.ingredientsTitle,
            content = props.attributes.ingredientsContent;

        function onChangeTitle( newTitle ) {
            props.setAttributes( { ingredientsTitle: newTitle } );
        }

        function onChangeContent( newContent ) {
            var before = {
                    tagName: 'span',
                    className: 'tick'
                },
                after = false;

            newContent = insertBeforeAfterContent( newContent, before, after );

            props.setAttributes( { ingredientsContent: newContent } );
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
            before  = before || { tagName: false, className: false, children: false };
            after   = after  || { tagName: false, className: false, children: false };

            for ( var i = 0; i < content.length; i++ ) {
                if ( typeof content[i].props !== 'undefined' ) {
                    var children = content[i].props.children;

                    if ( before.tagName ) {
                        var b = el(
                            RichText,
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
                            RichText,
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
                    RichText,
                    {
                        key: 'editable',
                        tagName: 'h3',
                        placeholder: 'Title',
                        keepPlaceholderOnFocus: false,
                        className: 'ingredients-title',
                        onChange: onChangeTitle,
                        value: title,
                        formattingControls: ['bold', 'italic'],
                    }
                ),
                el(
                    RichText,
                    {
                        tagName: 'ul',
                        multiline: 'li',
                        placeholder: 'Add your ingredient',
                        keepPlaceholderOnFocus: false,
                        className: 'ingredients-list',
                        onChange: onChangeContent,
                        value: content,
                        formattingControls: ['bold', 'italic'],
                    },
                ),
            ),
        ];
    },
    save: function( props ) {
        var title = props.attributes.ingredientsTitle,
            content = props.attributes.ingredientsContent;

        return render = el( 'div', { className: props.className },
            el(
                RichText.Content,
                {
                    tagName: 'h3',
                    className: 'ingredients-title',
                    value: title
                },
            ),
            el(
                RichText.Content,
                {
                    tagName: 'ul',
                    className: 'ingredients-list',
                    value: content
                },
            )
        );
    },
} );


// function getSavedElement( props ) {
//     console.log(props);
// }

// wp.hooks.addFilter(
//     'blocks.getSaveContent.extraProps',
//     'foodica/gutenberg/blocks/ingredients',
//     getSavedElement
// );


