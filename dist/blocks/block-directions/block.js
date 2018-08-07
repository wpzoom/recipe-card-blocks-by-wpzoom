var el = wp.element.createElement,
    registerBlockType = wp.blocks.registerBlockType,
    block = wp.blocks.getBlockType,
    _e = wp.editor,
    RichText = _e.RichText;

var atts = {
    directionsTitle: {
        type: 'array',
        selector: '.directions-title',
        source: 'children'
    },
    directionsContent: {
        type: 'array',
        selector: '.directions-list',
        source: 'children'
    },
}

registerBlockType( 'wpzoom-recipe-card/block-directions', {
    title: 'Directions',
    description: 'Add multiple steps directions.',
    keywords: ['directions', 'recipe', 'foodica'],
    icon: 'editor-ol',
    category: 'wpzoom-recipe-card',
    attributes: atts,
    edit: function( props ) {
        var title = props.attributes.directionsTitle,
            content = props.attributes.directionsContent;

        function onChangeTitle( newTitle ) {
            props.setAttributes( { directionsTitle: newTitle } );
        }

        function onChangeContent( newContent ) {
            props.setAttributes( { directionsContent: newContent } );
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
                        className: 'directions-title',
                        onChange: onChangeTitle,
                        value: title,
                        formattingControls: ['bold', 'italic'],
                    }
                ),
                el(
                    RichText,
                    {
                        tagName: 'ol',
                        multiline: 'li',
                        placeholder: 'Add your direction',
                        keepPlaceholderOnFocus: false,
                        className: 'directions-list',
                        onChange: onChangeContent,
                        value: content,
                        formattingControls: ['bold', 'italic'],
                    },
                ),
            ),
        ];
    },
    save: function( props ) {
        var title = props.attributes.directionsTitle,
            content = props.attributes.directionsContent;

        return render = el( 'div', { className: props.className },
            el(
                RichText.Content,
                {
                    tagName: 'h3',
                    className: 'directions-title',
                    value: title
                },
            ),
            el(
                RichText.Content,
                {
                    tagName: 'ul',
                    className: 'directions-list',
                    value: content
                },
            )
        );
    },
} );

