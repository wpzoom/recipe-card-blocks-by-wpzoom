/**
 * BLOCK: block-nutrition
 *
 * Registering a basic block with Gutenberg.
 * Simple block, renders and saves the same content without any interactivity.
 */

import icon from './icon';

/* External dependencies */
import { __ } from '@wordpress/i18n';

/* Internal dependencies */
import Nutrition from './components/Nutrition';

/* WordPress dependencies */
import { registerBlockType } from '@wordpress/blocks';

/**
 * Register: Nutrition Gutenberg Block.
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
registerBlockType( 'wpzoom-recipe-card/block-nutrition', {
    // Block name. Block names must be string that contains a namespace prefix. Example: my-plugin/my-custom-block.
    title: __( 'Nutrition Facts', 'recipe-card-blocks-by-wpzoom' ), // Block title.
    description: __( 'Display Nutrition Facts for your recipe.', 'recipe-card-blocks-by-wpzoom' ),
    icon: {
        // Specifying a background color to appear with the icon e.g.: in the inserter.
        // background: '#FDA921',
        // Specifying a color for the icon (optional: if not set, a readable color will be automatically defined)
        foreground: '#FDA921',
        // Block icon from Dashicons → https://developer.wordpress.org/resource/dashicons/.
        src: icon,
    },
    category: 'wpzoom-recipe-card', // Block category — Group blocks together based on common traits E.g. common, formatting, layout widgets, embed.
    // Allow multiple Nutrition blocks per post.
    supports: {
        multiple: true,
    },
    keywords: [
        __( 'Recipe Card', 'recipe-card-blocks-by-wpzoom' ),
        __( 'Nutrition', 'recipe-card-blocks-by-wpzoom' ),
        __( 'WPZOOM', 'recipe-card-blocks-by-wpzoom' ),
    ],
    attributes: {
        id: {
            type: 'string',
            default: 'wpzoom-recipe-nutrition',
        },
        data: {
            type: 'object',
            default: {},
        },
        settings: {
            type: 'object',
            default: {
                'layout-orientation': 'vertical',
            },
        },
    },

    /**
     * The edit function describes the structure of your block in the context of the editor.
     * This represents what the editor will render when the block is used.
     *
     * The "edit" property must be a valid function.
     *
     * @link https://wordpress.org/gutenberg/handbook/block-api/block-edit-save/
     */
    edit: ( { attributes, setAttributes, className, clientId } ) => {
        return <Nutrition { ...{ attributes, setAttributes, className, clientId } } />;
    },

    save() {
        // Rendering in PHP
        return null;
    },

} );
