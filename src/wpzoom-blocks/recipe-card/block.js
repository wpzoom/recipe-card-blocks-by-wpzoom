/**
 * BLOCK: block-recipe-card
 *
 * Registering a basic block with Gutenberg.
 * Simple block, renders and saves the same content without any interactivity.
 */

/* Internal dependencies */
import RecipeCard from './components/RecipeCard';
import Icons from "./utils/IconsArray";
import _isUndefined from "lodash/isUndefined";
import _merge from "lodash/merge";

/* External dependencies */
const { __ } = wp.i18n;
const { registerBlockType } = wp.blocks; // Import registerBlockType() from wp.blocks

const attributes = {
    id: {
        type: 'string',
    },
    style: {
        type: 'string',
        default: 'default'
    },
    image: {
        type: 'object',
        default: null
    },
    hasImage: {
        type: 'string',
        default: false
    },
    video: {
        type: 'object',
        default: null
    },
    hasVideo: {
        type: 'string',
        default: false
    },
    recipeTitle: {
        type: 'array',
        selector: '.recipe-card-title',
        source: 'children',
        default: wpzoomRecipeCard.post_title
    },
    jsonName: {
        type: 'string',
        default: wpzoomRecipeCard.post_title
    },
    summary: {
        type: 'array',
        selector: '.recipe-card-summary',
        source: 'children',
    },
    jsonSummary: {
        type: 'string',
    },
    course: {
        type: 'array',
    },
    cuisine: {
        type: 'array',
    },
    difficulty: {
        type: 'array',
    },
    keywords: {
        type: 'array',
    },
    settings: {
        type: 'array',
        default: [
            {
                primary_color: '#222',
                print_btn: 'visible',
                pin_btn: 'hidden',
                custom_author_name: wpzoomRecipeCard.post_author_name,
                additionalClasses: '',
                displayCourse: true,
                displayCuisine: true,
                displayDifficulty: true,
                displayAuthor: true,
                ingredientsLayout: '1-column'
            }
        ]
    },
    details: {
        type: 'array',
        selector: '.details-items',
        default: [
            { id: RecipeCard.generateId( "detail-item" ), iconSet: 'oldicon', icon: 'food', label: __( "Yield", "wpzoom-recipe-card" ), unit: __( "servings", "wpzoom-recipe-card" ) },
            { id: RecipeCard.generateId( "detail-item" ), iconSet: 'oldicon', icon: 'clock', label: __( "Prep time", "wpzoom-recipe-card" ), unit: __( "minutes", "wpzoom-recipe-card" ) },
            { id: RecipeCard.generateId( "detail-item" ), iconSet: 'foodicons', icon: 'cooking-food-in-a-hot-casserole', label: __( "Cooking time", "wpzoom-recipe-card" ), unit: __( "minutes", "wpzoom-recipe-card" ) },
            { id: RecipeCard.generateId( "detail-item" ), iconSet: 'foodicons', icon: 'fire-flames', label: __( "Calories", "wpzoom-recipe-card" ), unit: __( "kcal", "wpzoom-recipe-card" ) },
        ]
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
        default: 'foodicons'
    },
    searchIcon: {
        type: 'string',
        default: ''
    },
    icons: {
        type: 'object',
        default: Icons
    },
    ingredientsTitle: {
        type: 'array',
        selector: '.ingredients-title',
        source: 'children',
        default: __( "Ingredients", "wpzoom-recipe-card" )
    },
    jsonIngredientsTitle: {
        type: "string",
    },
    ingredients: {
        type: 'array',
    },
    directionsTitle: {
        type: 'array',
        selector: '.directions-title',
        source: 'children',
        default: __( "Directions", "wpzoom-recipe-card" )
    },
    jsonDirectionsTitle: {
        type: "string",
    },
    steps: {
        type: 'array',
    },
}

let transforms = [];

if ( wpzoomRecipeCard.is_pro ) {
    transforms = {
        to: [
            {
                type: 'block',
                blocks: [ 'wpzoom-recipe-card/block-premium-recipe-card' ],
                transform: function( attributes ) {
                    return createBlock( 'wpzoom-recipe-card/block-premium-recipe-card', attributes );
                },
            },
        ],
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
registerBlockType( 'wpzoom-recipe-card/block-recipe-card', {
    // Block name. Block names must be string that contains a namespace prefix. Example: my-plugin/my-custom-block.
    title: __( "Recipe Card Block (Rich Snippets)", "wpzoom-recipe-card" ), // Block title.
    icon: {
        // Specifying a background color to appear with the icon e.g.: in the inserter.
        background: '#2EA55F',
        // Specifying a color for the icon (optional: if not set, a readable color will be automatically defined)
        foreground: '#fff',
        // Block icon from Dashicons → https://developer.wordpress.org/resource/dashicons/.
        src: 'media-spreadsheet',
    },
    category: 'wpzoom-recipe-card', // Block category — Group blocks together based on common traits E.g. common, formatting, layout widgets, embed.
    // Allow only one Recipe Card Block per post.
    supports: {
        multiple: false,
    },
    // Block attributes
    attributes,
    keywords: [
        __( "Recipe Card", "wpzoom-recipe-card" ),
        __( "Block Recipe Card", "wpzoom-recipe-card" ),
        __( "WPZOOM", "wpzoom-recipe-card" ),
    ],
    styles: [
        // Mark style as default.
        { 
            name: 'default', 
            label: __( "Default", "wpzoom-recipe-card" ), 
            isDefault: true 
        },
        { 
            name: 'newdesign', 
            label: __( "New Design", "wpzoom-recipe-card" ),
        }
    ],
    // Transform block to Premium Recipe Card if is PRO active
    transforms,

    /**
     * The edit function describes the structure of your block in the context of the editor.
     * This represents what the editor will render when the block is used.
     *
     * The "edit" property must be a valid function.
     *
     * @link https://wordpress.org/gutenberg/handbook/block-api/block-edit-save/
     */
    edit: ( { attributes, setAttributes, className, clientId } ) => {
        return <RecipeCard { ...{ attributes, setAttributes, className, clientId } } />;
    },

    save: function( { attributes } ) {
        return <RecipeCard.Content { ...attributes } />;
    },

} );



