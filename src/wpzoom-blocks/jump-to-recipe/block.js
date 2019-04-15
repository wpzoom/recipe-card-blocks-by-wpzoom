/**
 * BLOCK: block-jump-to-recipe
 *
 * Registering a basic block with Gutenberg.
 * Simple block, renders and saves the same content without any interactivity.
 */

/* External dependencies */
const { __ } = wp.i18n;
const { Fragment } = wp.element;
const { registerBlockType } = wp.blocks; // Import registerBlockType() from wp.blocks

// Create SVG icon for block
const el = wp.element.createElement;
const SVG = wp.components.SVG;
const path = el( 'path', { d: "M 17.183594 13.253906 C 17.011719 12.902344 16.738281 12.726562 16.359375 12.726562 L 13.632812 12.726562 L 13.632812 0.46875 C 13.632812 0.335938 13.585938 0.226562 13.496094 0.136719 C 13.40625 0.046875 13.300781 0 13.179688 0 L 3.179688 0 C 2.988281 0 2.851562 0.0898438 2.765625 0.269531 C 2.679688 0.460938 2.699219 0.621094 2.824219 0.753906 L 5.09375 3.480469 C 5.199219 3.585938 5.316406 3.636719 5.449219 3.636719 L 9.996094 3.636719 L 9.996094 12.726562 L 7.269531 12.726562 C 6.890625 12.726562 6.617188 12.902344 6.445312 13.253906 C 6.285156 13.613281 6.328125 13.9375 6.574219 14.234375 L 11.117188 19.6875 C 11.289062 19.894531 11.519531 20 11.816406 20 C 12.109375 20 12.339844 19.894531 12.511719 19.6875 L 17.054688 14.234375 C 17.3125 13.929688 17.355469 13.601562 17.183594 13.253906 Z M 17.183594 13.253906 " } );
const svgIcon = el( SVG, { width: 20, height: 20, viewBox: '0 0 20 20' }, path );

/**
 * Register: Ingredients Gutenberg Block.
 *
 * Registers a new block provided a unique name and an object defining its
 * behavior. Once registered, the block is made editor as an option to any
 * editor interface where blocks are implemented.
 *
 * @link https://wordpress.org/gutenberg/handbook/block-api/
 * @param  {string}   name     Block name.
 * @param  {Object}   settings Block settings.
 * @return {?WPBlock}          The block, if it has been successfully
 *                             registered; otherwise `undefined`.
 */
registerBlockType( 'wpzoom-recipe-card/block-jump-to-recipe', {
    // Block name. Block names must be string that contains a namespace prefix. Example: my-plugin/my-custom-block.
    title: __( "Jump To Recipe", "wpzoom-recipe-card" ), // Block title.
    description: __( "A button to jump to a WPZOOM Recipe Card on the same page.", "wpzoom-recipe-card" ),
    icon: {
        // // Specifying a background color to appear with the icon e.g.: in the inserter.
        // background: '#2EA55F',
        // Specifying a color for the icon (optional: if not set, a readable color will be automatically defined)
        foreground: '#2EA55F',
        // Block icon from Dashicons → https://developer.wordpress.org/resource/dashicons/.
        src: svgIcon,
    },
    category: 'wpzoom-recipe-card', // Block category — Group blocks together based on common traits E.g. common, formatting, layout widgets, embed.
    // Allow only one Recipe Card Block per post.
    supports: {
        multiple: false,
        html: false
    },
    keywords: [
        __( "Recipe Card", "wpzoom-recipe-card" ),
        __( "Block Recipe Card", "wpzoom-recipe-card" ),
        __( "WPZOOM", "wpzoom-recipe-card" ),
    ],

    /**
     * The edit function describes the structure of your block in the context of the editor.
     * This represents what the editor will render when the block is used.
     *
     * The "edit" property must be a valid function.
     *
     * @link https://wordpress.org/gutenberg/handbook/block-api/block-edit-save/
     */
    edit: ( { attributes, setAttributes, className, clientId } ) => {
        const { id, text } = attributes;

        return (
            <Fragment>
                <a href={ `#${ id }` } className={ className }>{ text }</a>
            </Fragment>
        );
    },

    save() {
        // Rendering in PHP
        return null;
    }

} );



