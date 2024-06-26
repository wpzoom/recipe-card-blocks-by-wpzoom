/**
 * BLOCK: block-details
 *
 * Registering a basic block with Gutenberg.
 * Simple block, renders and saves the same content without any interactivity.
 */

/* External dependencies */
import { __ } from '@wordpress/i18n';

/* Internal dependencies */
import Detail from './components/Detail';
import legacy from './legacy';
import Icons from '../../utils/IconsArray';
import { generateId } from '../../helpers/generateId';
import icon from './icon';

/* WordPress dependencies */
import { registerBlockType } from '@wordpress/blocks';

const deprecatedAttr = {
    title: {
        type: 'array',
        selector: '.details-title',
        source: 'children',
        default: __( 'Details', 'recipe-card-blocks-by-wpzoom' ),
    },
    id: {
        type: 'string',
    },
    details: {
        type: 'array',
        selector: '.details-items',
        default: [
            { id: generateId( 'detail-item' ), iconSet: 'oldicon', icon: 'food', label: __( 'Servings', 'recipe-card-blocks-by-wpzoom' ) },
            { id: generateId( 'detail-item' ), iconSet: 'oldicon', icon: 'room-service', label: __( 'Prep time', 'recipe-card-blocks-by-wpzoom' ) },
            { id: generateId( 'detail-item' ), iconSet: 'oldicon', icon: 'cook', label: __( 'Cooking time', 'recipe-card-blocks-by-wpzoom' ) },
            { id: generateId( 'detail-item' ), iconSet: 'oldicon', icon: 'shopping-basket', label: __( 'Calories', 'recipe-card-blocks-by-wpzoom' ) },
        ],
    },
    columns: {
        type: 'number',
        default: 4,
    },
    toInsert: {
        type: 'string',
    },
    showModal: {
        type: 'string',
        default: false,
    },
    activeIconSet: {
        type: 'string',
        default: 'oldicon',
    },
    searchIcon: {
        type: 'string',
        default: '',
    },
    icons: {
        type: 'object',
        default: Icons,
    },
    jsonTitle: {
        type: 'string',
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
        type: 'string',
    },
};

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
    title: __( 'Details', 'recipe-card-blocks-by-wpzoom' ), // Block title.
    icon: {
        // // Specifying a background color to appear with the icon e.g.: in the inserter.
        // background: '#2EA55F',
        // Specifying a color for the icon (optional: if not set, a readable color will be automatically defined)
        foreground: '#2EA55F',
        // Block icon from Dashicons → https://developer.wordpress.org/resource/dashicons/.
        src: icon,
    },
    category: 'wpzoom-recipe-card', // Block category — Group blocks together based on common traits E.g. common, formatting, layout widgets, embed.
    // Allow multiple Details block per post.
    supports: {
        multiple: false,
    },
    keywords: [
        __( 'details', 'recipe-card-blocks-by-wpzoom' ),
        __( 'wpzoom', 'recipe-card-blocks-by-wpzoom' ),
        __( 'recipe', 'recipe-card-blocks-by-wpzoom' ),
    ],
    example: {
        attributes: {
            course: [ __( 'Main', 'recipe-card-blocks-by-wpzoom' ) ],
            cuisine: [ __( 'Italian', 'recipe-card-blocks-by-wpzoom' ) ],
            difficulty: [ __( 'Medium', 'recipe-card-blocks-by-wpzoom' ) ],
            details: [
                { id: generateId( 'detail-item' ), iconSet: 'oldicon', icon: 'food', label: __( 'Servings', 'recipe-card-blocks-by-wpzoom' ) },
                { id: generateId( 'detail-item' ), iconSet: 'oldicon', icon: 'room-service', label: __( 'Prep time', 'recipe-card-blocks-by-wpzoom' ) },
                { id: generateId( 'detail-item' ), iconSet: 'oldicon', icon: 'cook', label: __( 'Cooking time', 'recipe-card-blocks-by-wpzoom' ) },
                { id: generateId( 'detail-item' ), iconSet: 'oldicon', icon: 'shopping-basket', label: __( 'Calories', 'recipe-card-blocks-by-wpzoom' ) },
            ],
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

    edit: ( { attributes, setAttributes, className } ) => {
        // Because setAttributes is quite slow right after a block has been added we fake having a single detail.
        if ( ! attributes.details || attributes.details.length === 0 ) {
            attributes.details = [
                { id: generateId( 'detail-item' ), iconSet: 'oldicon', icon: 'food', label: __( 'Servings', 'recipe-card-blocks-by-wpzoom' ) },
                { id: generateId( 'detail-item' ), iconSet: 'oldicon', icon: 'room-service', label: __( 'Prep time', 'recipe-card-blocks-by-wpzoom' ) },
                { id: generateId( 'detail-item' ), iconSet: 'oldicon', icon: 'cook', label: __( 'Cooking time', 'recipe-card-blocks-by-wpzoom' ) },
                { id: generateId( 'detail-item' ), iconSet: 'oldicon', icon: 'shopping-basket', label: __( 'Calories', 'recipe-card-blocks-by-wpzoom' ) },
            ];
        }

        return <Detail { ...{ attributes, setAttributes, className } } />;
    },

    save() {
        // Rendering in PHP
        return null;
    },

    deprecated: [
        {
            attributes: deprecatedAttr,
            save: legacy.v1_0,
        },
    ],

} );
