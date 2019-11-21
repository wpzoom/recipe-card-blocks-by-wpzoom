/**
 * BLOCK: block-print-recipe
 *
 * Registering a basic block with Gutenberg.
 * Simple block, renders and saves the same content without any interactivity.
 */

/* External dependencies */
import { __ } from "@wordpress/i18n";

/* WordPress dependencies */
const { Fragment } = wp.element;
const { registerBlockType } = wp.blocks; // Import registerBlockType() from wp.blocks

// Create SVG icon for block
const el = wp.element.createElement;
const SVG = wp.components.SVG;
const path1 = el( 'path', { d: "M 5.003906 1.25 L 15.007812 1.25 L 15.007812 3.75 L 16.257812 3.75 L 16.257812 1.25 C 16.257812 0.5625 15.699219 0 15.007812 0 L 5.003906 0 C 4.3125 0 3.75 0.5625 3.75 1.25 L 3.75 3.75 L 5.003906 3.75 Z M 5.003906 1.25 " } );
const path2 = el( 'path', { d: "M 18.757812 5.003906 L 1.25 5.003906 C 0.5625 5.003906 0 5.5625 0 6.253906 L 0 12.503906 C 0 13.195312 0.5625 13.757812 1.25 13.757812 L 3.75 13.757812 L 3.75 18.757812 C 3.75 19.449219 4.3125 20.007812 5.003906 20.007812 L 15.007812 20.007812 C 15.699219 20.007812 16.257812 19.449219 16.257812 18.757812 L 16.257812 13.757812 L 18.757812 13.757812 C 19.449219 13.757812 20.007812 13.195312 20.007812 12.503906 L 20.007812 6.253906 C 20.007812 5.5625 19.449219 5.003906 18.757812 5.003906 Z M 15.007812 18.757812 L 5.003906 18.757812 L 5.003906 10.003906 L 15.007812 10.003906 Z M 17.507812 8.753906 C 16.816406 8.753906 16.257812 8.195312 16.257812 7.503906 C 16.257812 6.8125 16.816406 6.253906 17.507812 6.253906 C 18.199219 6.253906 18.757812 6.8125 18.757812 7.503906 C 18.757812 8.195312 18.199219 8.753906 17.507812 8.753906 Z M 17.507812 8.753906 " } );
const path3 = el( 'path', { d: "M 6.253906 11.253906 L 11.253906 11.253906 L 11.253906 12.503906 L 6.253906 12.503906 Z M 6.253906 11.253906 " } );
const path4 = el( 'path', { d: "M 6.253906 13.757812 L 13.757812 13.757812 L 13.757812 15.007812 L 6.253906 15.007812 Z M 6.253906 13.757812 " } );
const path5 = el( 'path', { d: "M 6.253906 16.257812 L 13.757812 16.257812 L 13.757812 17.507812 L 6.253906 17.507812 Z M 6.253906 16.257812 " } );
const svgIcon = el( SVG, { width: 20, height: 20, viewBox: '0 0 20 20' }, path1, path2, path3, path4, path5 );

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
registerBlockType( 'wpzoom-recipe-card/block-print-recipe', {
    // Block name. Block names must be string that contains a namespace prefix. Example: my-plugin/my-custom-block.
    title: __( "Print Recipe", "wpzoom-recipe-card" ), // Block title.
    description: __( "A button to print WPZOOM Recipe Card.", "wpzoom-recipe-card" ),
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
    edit: ( { attributes, className } ) => {
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



