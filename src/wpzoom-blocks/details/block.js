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

/* External dependencies */
const { __ } = wp.i18n;
const { registerBlockType } = wp.blocks; // Import registerBlockType() from wp.blocks

// Create SVG icon for block
const el = wp.element.createElement;
const SVG = wp.components.SVG;
const path1 = el( 'path', { d: "M 10.847656 14.761719 C 13.414062 14.761719 15.503906 12.675781 15.503906 10.109375 C 15.503906 7.542969 13.414062 5.457031 10.847656 5.457031 C 8.28125 5.457031 6.195312 7.542969 6.195312 10.109375 C 6.195312 12.675781 8.28125 14.761719 10.847656 14.761719 Z M 10.847656 14.761719 " } );
const path2 = el( 'path', { d: "M 10.847656 16.011719 C 14.109375 16.011719 16.75 13.367188 16.75 10.109375 C 16.75 6.847656 14.109375 4.207031 10.847656 4.207031 C 7.589844 4.207031 4.945312 6.847656 4.945312 10.109375 C 4.945312 13.367188 7.589844 16.011719 10.847656 16.011719 Z M 10.847656 5.113281 C 13.605469 5.113281 15.847656 7.355469 15.847656 10.109375 C 15.847656 12.863281 13.605469 15.105469 10.847656 15.105469 C 8.09375 15.105469 5.851562 12.863281 5.851562 10.109375 C 5.851562 7.355469 8.09375 5.113281 10.847656 5.113281 Z M 10.847656 5.113281 " } );
const path3 = el( 'path', { d: "M 20.03125 16.558594 L 19.6875 3.3125 C 19.6875 3.027344 19.453125 2.796875 19.167969 2.796875 C 19.09375 2.796875 19.023438 2.816406 18.957031 2.84375 L 18.957031 2.839844 C 18.957031 2.839844 18.941406 2.847656 18.925781 2.863281 C 18.875 2.890625 18.832031 2.921875 18.792969 2.964844 C 18.414062 3.273438 17.207031 4.539062 17.109375 6.246094 C 16.953125 8.855469 18.222656 9.910156 18.652344 10.878906 L 18.308594 16.558594 C 18.308594 16.84375 18.882812 17.078125 19.167969 17.078125 C 19.453125 17.078125 20.03125 16.84375 20.03125 16.558594 Z M 20.03125 16.558594 " } );
const path4 = el( 'path', { d: "M 0.902344 8.492188 L 1.792969 8.9375 C 1.894531 8.988281 2.007812 9.023438 2.125 9.050781 L 1.777344 16.800781 C 1.777344 17.039062 2.355469 17.234375 2.640625 17.234375 C 2.925781 17.234375 3.5 17.039062 3.5 16.800781 L 3.15625 9.011719 C 3.277344 8.972656 3.394531 8.925781 3.496094 8.863281 L 4.15625 8.460938 C 4.632812 8.171875 4.992188 7.53125 4.992188 6.972656 L 4.992188 3.503906 C 4.992188 3.214844 4.761719 2.984375 4.476562 2.984375 C 4.191406 2.984375 3.960938 3.214844 3.960938 3.503906 L 3.960938 6.628906 L 3.671875 6.628906 L 3.671875 3.503906 C 3.671875 3.214844 3.441406 2.984375 3.15625 2.984375 C 2.871094 2.984375 2.640625 3.214844 2.640625 3.503906 L 2.640625 6.628906 L 2.351562 6.628906 L 2.351562 3.503906 C 2.351562 3.214844 2.121094 2.984375 1.835938 2.984375 C 1.550781 2.984375 1.320312 3.214844 1.320312 3.503906 L 1.320312 6.628906 L 1.03125 6.628906 L 1.03125 3.613281 C 1.03125 3.328125 0.800781 3.097656 0.515625 3.097656 C 0.230469 3.097656 0 3.328125 0 3.613281 L 0 7.03125 C 0 7.605469 0.386719 8.234375 0.902344 8.492188 Z M 0.902344 8.492188 " } );
const svgIcon = el( SVG, { width: 20, height: 20, viewBox: '0 0 20 20'}, path1, path2, path3, path4 );

const deprecatedAttr = {
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
            { id: Detail.generateId( "detail-item" ), iconSet: 'oldicon', icon: 'food', label: __( "Servings", "wpzoom-recipe-card" ) },
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
        // // Specifying a background color to appear with the icon e.g.: in the inserter.
        // background: '#2EA55F',
        // Specifying a color for the icon (optional: if not set, a readable color will be automatically defined)
        foreground: '#2EA55F',
        // Block icon from Dashicons → https://developer.wordpress.org/resource/dashicons/.
        src: svgIcon,
    },
    category: 'wpzoom-recipe-card', // Block category — Group blocks together based on common traits E.g. common, formatting, layout widgets, embed.
    // Allow multiple Details block per post.
    supports: {
        multiple: false,
    },
    keywords: [
        __( "details", "wpzoom-recipe-card" ),
        __( "wpzoom", "wpzoom-recipe-card" ),
        __( "recipe", "wpzoom-recipe-card" ),
    ],
    example: {
        attributes: {
            course: [ __( "Main", "wpzoom-recipe-card" ) ],
            cuisine: [ __( "Italian", "wpzoom-recipe-card" ) ],
            difficulty: [ __( "Medium", "wpzoom-recipe-card" ) ],
            details: [
                { id: Detail.generateId( "detail-item" ), iconSet: 'oldicon', icon: 'food', label: __( "Servings", "wpzoom-recipe-card" ) },
                { id: Detail.generateId( "detail-item" ), iconSet: 'oldicon', icon: 'room-service', label: __( "Prep time", "wpzoom-recipe-card" ) },
                { id: Detail.generateId( "detail-item" ), iconSet: 'oldicon', icon: 'cook', label: __( "Cooking time", "wpzoom-recipe-card" ) },
                { id: Detail.generateId( "detail-item" ), iconSet: 'oldicon', icon: 'shopping-basket', label: __( "Calories", "wpzoom-recipe-card" ) }
            ]
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
                { id: Detail.generateId( "detail-item" ), iconSet: 'oldicon', icon: 'food', label: __( "Servings", "wpzoom-recipe-card" ) },
                { id: Detail.generateId( "detail-item" ), iconSet: 'oldicon', icon: 'room-service', label: __( "Prep time", "wpzoom-recipe-card" ) },
                { id: Detail.generateId( "detail-item" ), iconSet: 'oldicon', icon: 'cook', label: __( "Cooking time", "wpzoom-recipe-card" ) },
                { id: Detail.generateId( "detail-item" ), iconSet: 'oldicon', icon: 'shopping-basket', label: __( "Calories", "wpzoom-recipe-card" ) }
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
