/**
 * BLOCK: block-jump-to-recipe
 *
 * Registering a basic block with Gutenberg.
 * Simple block, renders and saves the same content without any interactivity.
 */

/* External dependencies */
import { __ } from '@wordpress/i18n';

/* Internal dependencies */
import icon from './icon';

/* WordPress dependencies */
import { Fragment } from '@wordpress/element';
import { registerBlockType } from '@wordpress/blocks';

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
    title: __( 'Jump To Recipe', 'wpzoom-recipe-card' ), // Block title.
    description: __( 'A button to jump to a WPZOOM Recipe Card on the same page.', 'wpzoom-recipe-card' ),
    icon: {
        // // Specifying a background color to appear with the icon e.g.: in the inserter.
        // background: '#2EA55F',
        // Specifying a color for the icon (optional: if not set, a readable color will be automatically defined)
        foreground: '#2EA55F',
        // Block icon from Dashicons → https://developer.wordpress.org/resource/dashicons/.
        src: icon,
    },
    category: 'wpzoom-recipe-card', // Block category — Group blocks together based on common traits E.g. common, formatting, layout widgets, embed.
    // Allow only one Recipe Card Block per post.
    supports: {
        multiple: false,
        html: false,
    },
    keywords: [
        __( 'Recipe Card', 'wpzoom-recipe-card' ),
        __( 'Block Recipe Card', 'wpzoom-recipe-card' ),
        __( 'WPZOOM', 'wpzoom-recipe-card' ),
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
    },

} );

