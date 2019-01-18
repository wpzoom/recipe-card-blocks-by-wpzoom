/**
 * BLOCK: block-recipe-card
 *
 * Registering a basic block with Gutenberg.
 * Simple block, renders and saves the same content without any interactivity.
 */

/* Internal dependencies */
import RecipeCard from './components/RecipeCard';
import Icons from "./utils/IconsArray";
import _omit from "lodash/omit";

/* External dependencies */
const { __ } = wp.i18n;
const { registerBlockType, createBlock } = wp.blocks; // Import registerBlockType() from wp.blocks

let custom_author_name = wpzoomRecipeCard.setting_options.wpzoom_rcb_settings_author_custom_name;

if ( custom_author_name === '' ) {
    custom_author_name = wpzoomRecipeCard.post_author_name;
}

const attributes = {
    id: {
        type: 'string',
    },
    style: {
        type: 'string',
        default: wpzoomRecipeCard.setting_options.wpzoom_rcb_settings_template,
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
        type: 'string',
        selector: '.recipe-card-title',
        default: wpzoomRecipeCard.post_title
    },
    summary: {
        type: 'array',
        source: "children",
        selector: '.recipe-card-summary',
    },
    jsonSummary: {
        type: 'string',
    },
    notes: {
        type: 'array',
        source: "children",
        selector: '.recipe-card-notes-list',
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
                print_btn: wpzoomRecipeCard.setting_options.wpzoom_rcb_settings_display_print === '1',
                pin_btn: wpzoomRecipeCard.setting_options.wpzoom_rcb_settings_display_pin === '1',
                custom_author_name: custom_author_name,
                additionalClasses: '',
                displayCourse: wpzoomRecipeCard.setting_options.wpzoom_rcb_settings_display_course === '1',
                displayCuisine: wpzoomRecipeCard.setting_options.wpzoom_rcb_settings_display_cuisine === '1',
                displayDifficulty: wpzoomRecipeCard.setting_options.wpzoom_rcb_settings_display_difficulty === '1',
                displayAuthor: wpzoomRecipeCard.setting_options.wpzoom_rcb_settings_display_author === '1',
                headerAlign: wpzoomRecipeCard.setting_options.wpzoom_rcb_settings_heading_content_align,
                ingredientsLayout: '1-column'
            }
        ]
    },
    details: {
        type: 'array',
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
        default: wpzoomRecipeCard.setting_options.wpzoom_rcb_settings_ingredients_title
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
        default: wpzoomRecipeCard.setting_options.wpzoom_rcb_settings_steps_title
    },
    jsonDirectionsTitle: {
        type: "string",
    },
    notesTitle: {
        type: 'array',
        selector: '.notes-title',
        source: 'children',
        default: wpzoomRecipeCard.setting_options.wpzoom_rcb_settings_notes_title
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
                    return createBlock(
                        'wpzoom-recipe-card/block-premium-recipe-card',
                        _omit( attributes, ['icons', 'searchIcon'] )
                    );
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
        // Don't allow the block to be converted into a reusable block.
        reusable: false,
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
            isDefault: wpzoomRecipeCard.setting_options.wpzoom_rcb_settings_template === 'default'
        },
        { 
            name: 'newdesign', 
            label: __( "New Design", "wpzoom-recipe-card" ),
            isDefault: wpzoomRecipeCard.setting_options.wpzoom_rcb_settings_template === 'newdesign'
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



