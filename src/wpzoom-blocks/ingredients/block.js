/**
 * BLOCK: block-ingredients
 *
 * Registering a basic block with Gutenberg.
 * Simple block, renders and saves the same content without any interactivity.
 */

/* Internal dependencies */
import Ingredient from './components/Ingredient';
import legacy from "./legacy";
import isUndefined from "lodash/isUndefined";

/* External dependencies */
const { __ } = wp.i18n;
const { registerBlockType } = wp.blocks; // Import registerBlockType() from wp.blocks

// Create SVG icon for block
const el = wp.element.createElement;
const SVG = wp.components.SVG;
const path1 = el( 'path', { d: "M 16.882812 9.503906 C 17.285156 8.8125 17.304688 7.964844 16.941406 7.253906 L 16.964844 7.210938 C 17.375 7.300781 17.816406 7.109375 18.027344 6.742188 C 18.160156 6.515625 18.195312 6.25 18.128906 5.996094 C 18.0625 5.742188 17.898438 5.527344 17.671875 5.394531 L 15.988281 4.414062 C 15.53125 4.152344 14.902344 4.316406 14.636719 4.769531 C 14.417969 5.148438 14.480469 5.609375 14.757812 5.925781 L 14.734375 5.964844 C 13.933594 6.003906 13.203125 6.441406 12.804688 7.125 L 12.140625 8.265625 L 12.140625 7.441406 C 12.140625 6.101562 11.050781 5.011719 9.710938 5.011719 L 7.417969 5.011719 C 6.078125 5.011719 4.988281 6.101562 4.988281 7.441406 L 4.988281 9.503906 L 1.941406 9.503906 L 1.941406 11.972656 L 3.324219 11.972656 L 4.789062 17.738281 C 4.789062 18.984375 6.558594 20 7.898438 20 L 13.027344 20 C 14.367188 20 15.753906 18.964844 15.753906 17.71875 L 17.382812 11.972656 L 18.765625 11.972656 L 18.765625 9.507812 Z M 5.621094 7.445312 C 5.621094 6.453125 6.429688 5.648438 7.417969 5.648438 L 9.710938 5.648438 C 10.699219 5.648438 11.507812 6.453125 11.507812 7.445312 L 11.507812 9.367188 C 11.496094 9.382812 11.488281 9.398438 11.480469 9.417969 L 5.621094 9.417969 Z M 13.351562 7.449219 C 13.660156 6.917969 14.21875 6.597656 14.84375 6.597656 L 15.09375 6.605469 L 15.4375 6.011719 L 15.585938 5.730469 L 15.3125 5.570312 C 15.144531 5.472656 15.085938 5.257812 15.183594 5.089844 C 15.277344 4.929688 15.5 4.867188 15.667969 4.960938 L 17.351562 5.945312 C 17.429688 5.992188 17.488281 6.066406 17.511719 6.15625 C 17.539062 6.246094 17.523438 6.34375 17.476562 6.425781 C 17.382812 6.585938 17.183594 6.660156 16.984375 6.546875 L 16.710938 6.382812 L 16.203125 7.253906 L 16.304688 7.414062 C 16.640625 7.960938 16.648438 8.640625 16.332031 9.183594 L 16.148438 9.503906 L 12.152344 9.503906 Z M 8.703125 17.558594 C 8.703125 17.90625 8.398438 18.191406 8.023438 18.191406 C 7.648438 18.191406 7.34375 17.90625 7.34375 17.558594 L 7.34375 13.824219 C 7.34375 13.472656 7.648438 13.1875 8.023438 13.1875 C 8.398438 13.1875 8.703125 13.472656 8.703125 13.824219 Z M 11.042969 17.558594 C 11.042969 17.90625 10.738281 18.191406 10.363281 18.191406 C 9.984375 18.191406 9.679688 17.90625 9.679688 17.558594 L 9.679688 13.824219 C 9.679688 13.472656 9.984375 13.1875 10.363281 13.1875 C 10.738281 13.1875 11.042969 13.472656 11.042969 13.824219 Z M 13.320312 17.558594 C 13.320312 17.90625 13.015625 18.191406 12.640625 18.191406 C 12.265625 18.191406 11.957031 17.90625 11.957031 17.558594 L 11.957031 13.824219 C 11.957031 13.472656 12.265625 13.1875 12.640625 13.1875 C 13.015625 13.1875 13.320312 13.472656 13.320312 13.824219 Z M 13.320312 17.558594 " } );
const path2 = el( 'path', { d: "M 4.707031 7.859375 L 4.707031 7.480469 C 4.707031 6.183594 5.609375 5.097656 6.816406 4.808594 C 4.632812 1.65625 2.269531 -0.449219 1.488281 0.0820312 C 0.691406 0.625 1.84375 3.703125 4.0625 6.957031 C 4.273438 7.269531 4.488281 7.566406 4.707031 7.859375 Z M 5.875 4.796875 C 5.703125 5.117188 5.457031 5.332031 5.195312 5.519531 C 4.921875 5.695312 4.636719 5.84375 4.273438 5.890625 C 4.445312 5.570312 4.691406 5.355469 4.957031 5.167969 C 5.226562 4.992188 5.515625 4.84375 5.875 4.796875 Z M 3.0625 4.113281 C 3.234375 3.792969 3.480469 3.578125 3.746094 3.390625 C 4.015625 3.21875 4.304688 3.066406 4.664062 3.023438 C 4.492188 3.34375 4.246094 3.554688 3.984375 3.742188 C 3.710938 3.917969 3.425781 4.070312 3.0625 4.113281 Z M 3.0625 4.113281 " } );
const svgIcon = el( SVG, { width: 20, height: 20, viewBox: '0 0 20 20'}, path1, path2 );

const deprecatedAttr = {
    title: {
        type: 'array',
        selector: '.ingredients-title',
        source: 'children',
        default: wpzoomRecipeCard.setting_options.wpzoom_rcb_settings_ingredients_title
    },
    id: {
        type: 'string',
    },
    print_visibility: {
        type: 'string',
        default: 'visible'
    },
    jsonTitle: {
        type: "string",
    },
    items: {
        type: "array",
    },
    content: {
        type: 'array',
        selector: '.ingredients-list',
        source: 'children'
    }
}

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
registerBlockType( 'wpzoom-recipe-card/block-ingredients', {
    // Block name. Block names must be string that contains a namespace prefix. Example: my-plugin/my-custom-block.
    title: __( "Ingredients", "wpzoom-recipe-card" ), // Block title.
    icon: {
        // // Specifying a background color to appear with the icon e.g.: in the inserter.
        // background: '#2EA55F',
        // Specifying a color for the icon (optional: if not set, a readable color will be automatically defined)
        foreground: '#2EA55F',
        // Block icon from Dashicons → https://developer.wordpress.org/resource/dashicons/.
        src: svgIcon,
    },
    category: 'wpzoom-recipe-card', // Block category — Group blocks together based on common traits E.g. common, formatting, layout widgets, embed.
    // Allow multiple Ingredients block per post.
    supports: {
        multiple: true,
    },
    keywords: [
        __( "ingredients", "wpzoom-recipe-card" ),
        __( "wpzoom", "wpzoom-recipe-card" ),
        __( "recipe", "wpzoom-recipe-card" ),
    ],
    example: {
        attributes: {
            items: [
                {
                    id: Ingredient.generateId( "ingredient-item" ),
                    isGroup: false,
                    name: ["Lorem ipsum dolor sit amet"]
                },
                {
                    id: Ingredient.generateId( "ingredient-item" ),
                    isGroup: false,
                    name: ["Praesent feugiat dui eu pretium eleifend"]
                },
                {
                    id: Ingredient.generateId( "ingredient-item" ),
                    isGroup: true,
                    name: ["Group Title here"]
                },
                {
                    id: Ingredient.generateId( "ingredient-item" ),
                    isGroup: false,
                    name: ["Aenean nec diam a augue efficitur venenatis"]
                },
                {
                    id: Ingredient.generateId( "ingredient-item" ),
                    isGroup: false,
                    name: ["Pellentesque habitant morbi"]
                }
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
        const items = attributes.items ? attributes.items.slice() : [];

        // Populate deprecated attribute 'content'
        // Backward compatibility
        if ( attributes.content && attributes.content.length > 0 ) {
            const content = attributes.content;

            if ( items.length === 0 ) {
                for ( var i = 0; i < content.length; i++ ) {
                    if ( ! isUndefined( content[i].props ) ) {
                        items.push({
                            id: Ingredient.generateId( "ingredient-item" ),
                            name: content[i].props.children
                        });
                    }
                }

                setAttributes( { items } );
            }
        }

        // Because setAttributes is quite slow right after a block has been added we fake having a four ingredients.
        if ( ! items || items.length === 0 ) {
            attributes.items = [
                { 
                    id: Ingredient.generateId( "ingredient-item" ),
                    name: []
                },
                { 
                    id: Ingredient.generateId( "ingredient-item" ),
                    name: []
                },
                { 
                    id: Ingredient.generateId( "ingredient-item" ),
                    name: []
                },
                { 
                    id: Ingredient.generateId( "ingredient-item" ),
                    name: []
                }
            ];
        }

        return <Ingredient { ...{ attributes, setAttributes, className } } />;
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



