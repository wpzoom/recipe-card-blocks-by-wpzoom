/**
 * BLOCK: block-details
 *
 * Registering a basic block with Gutenberg.
 * Simple block, renders and saves the same content without any interactivity.
 */

/* Internal dependencies */
import Detail from './components/Detail';
import legacy from "./legacy";
import Icons from "./utils/IconsArray";
import _merge from "lodash/merge";

import { getBlocksCount } from "../../helpers/getBlocksCount";

/* External dependencies */
const { __ } = wp.i18n;
const { registerBlockType } = wp.blocks; // Import registerBlockType() from wp.blocks

const attributes = {
    title: {
        type: 'array',
        selector: '.details-title',
        source: 'children',
        default: __( "Details", "wpzoom-recipe-card" )
    },
    id: {
        type: 'string',
    },
    details: {
        type: 'array',
        selector: '.details-items',
        default: [
            { id: Detail.generateId( "detail-item" ), iconSet: 'oldicon', icon: 'food', label: __( "Yield", "wpzoom-recipe-card" ) },
            { id: Detail.generateId( "detail-item" ), iconSet: 'oldicon', icon: 'room-service', label: __( "Prep time", "wpzoom-recipe-card" ) },
            { id: Detail.generateId( "detail-item" ), iconSet: 'oldicon', icon: 'cook', label: __( "Cooking time", "wpzoom-recipe-card" ) },
            { id: Detail.generateId( "detail-item" ), iconSet: 'oldicon', icon: 'shopping-basket', label: __( "Calories", "wpzoom-recipe-card" ) },
        ]
    },
    columns: {
        type: 'number',
        default: 4
    },
    toInsert: {
        type: 'string',
    },
    showModal: {
        type: 'string',
        default: false
    },
    activeIconSet: {
        type: 'string',
        default: 'oldicon'
    },
    searchIcon: {
        type: 'string',
        default: ''
    },
    icons: {
        type: 'object',
        default: Icons
    },
    jsonTitle: {
        type: "string",
    },
    course: {
        type: 'array',
    },
    cuisine: {
        type: 'array',
    },
    keywords: {
        type: 'array',
    },
    blocks_count: {
        type: 'string'
    }
}

/**
 * Register: Details Gutenberg Block.
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

registerBlockType( 'wpzoom-recipe-card/block-details', {
    // Block name. Block names must be string that contains a namespace prefix. Example: my-plugin/my-custom-block.
    title: __( "Details", "wpzoom-recipe-card" ), // Block title.
    icon: {
        // Specifying a background color to appear with the icon e.g.: in the inserter.
        background: '#2EA55F',
        // Specifying a color for the icon (optional: if not set, a readable color will be automatically defined)
        foreground: '#fff',
        // Block icon from Dashicons → https://developer.wordpress.org/resource/dashicons/.
        src: 'tag',
    },
    category: 'wpzoom-recipe-card', // Block category — Group blocks together based on common traits E.g. common, formatting, layout widgets, embed.
    // Allow only one Details block per post.
    supports: {
        multiple: false,
    },
    // Block attributes.
    attributes,
    keywords: [
        __( "details", "wpzoom-recipe-card" ),
        __( "wpzoom", "wpzoom-recipe-card" ),
        __( "recipe", "wpzoom-recipe-card" ),
    ],

    /**
     * The edit function describes the structure of your block in the context of the editor.
     * This represents what the editor will render when the block is used.
     *
     * The "edit" property must be a valid function.
     *
     * @link https://wordpress.org/gutenberg/handbook/block-api/block-edit-save/
     */

    edit: ( { attributes, setAttributes, className } ) => {
        // Because setAttributes is quite slow right after a block has been added we fake having a single step.
        if ( ! attributes.details || attributes.details.length === 0 ) {
            attributes.details = [
                {
                    id: Detail.generateId( "detail-item" ),
                    icon: null,
                    label: [],
                    value: []
                }
            ];
        }

        attributes.details = Detail.removeDuplicates( attributes.details );

        return <Detail { ...{ attributes, setAttributes, className } } />;
    },

    /**
     * The save function defines the way in which the different attributes should be combined
     * into the final markup, which is then serialized by Gutenberg into post_content.
     *
     * The "save" property must be specified and must be a valid function.
     *
     * @link https://wordpress.org/gutenberg/handbook/block-api/block-edit-save/
     */
    save: function( { attributes } ) {
        attributes.blocks_count = getBlocksCount( [ "block-details", "block-ingredients", "block-directions" ] );
        return <Detail.Content { ...attributes } />;
    },

    deprecated: [
        {
            attributes: attributes,
            save: legacy.v1_0,
        },
    ],

} );
