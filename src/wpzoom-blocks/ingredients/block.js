/**
 * BLOCK: block-ingredients
 *
 * Registering a basic block with Gutenberg.
 * Simple block, renders and saves the same content without any interactivity.
 */

/* External dependencies */
import { __ } from "@wordpress/i18n";
import isUndefined from "lodash/isUndefined";

/* Internal dependencies */
import Ingredient from './components/Ingredient';
import { generateId } from "../../helpers/generateId";
import legacy from "./legacy";
import icon from "./icon";

/* External dependencies */
const { registerBlockType } = wp.blocks; // Import registerBlockType() from wp.blocks

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
        src: icon,
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
                    id: generateId( "ingredient-item" ),
                    isGroup: false,
                    name: [ "Lorem ipsum dolor sit amet" ]
                },
                {
                    id: generateId( "ingredient-item" ),
                    isGroup: false,
                    name: [ "Praesent feugiat dui eu pretium eleifend" ]
                },
                {
                    id: generateId( "ingredient-item" ),
                    isGroup: true,
                    name: [ "Group Title here" ]
                },
                {
                    id: generateId( "ingredient-item" ),
                    isGroup: false,
                    name: [ "Aenean nec diam a augue efficitur venenatis" ]
                },
                {
                    id: generateId( "ingredient-item" ),
                    isGroup: false,
                    name: [ "Pellentesque habitant morbi" ]
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
                        items.push( {
                            id: generateId( "ingredient-item" ),
                            name: content[i].props.children
                        } );
                    }
                }

                setAttributes( { items } );
            }
        }

        // Because setAttributes is quite slow right after a block has been added we fake having a four ingredients.
        if ( ! items || items.length === 0 ) {
            attributes.items = [
                {
                    id: generateId( "ingredient-item" ),
                    name: []
                },
                {
                    id: generateId( "ingredient-item" ),
                    name: []
                },
                {
                    id: generateId( "ingredient-item" ),
                    name: []
                },
                {
                    id: generateId( "ingredient-item" ),
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



