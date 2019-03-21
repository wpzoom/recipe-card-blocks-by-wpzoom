/**
 * BLOCK: block-recipe-card
 *
 * Registering a basic block with Gutenberg.
 * Simple block, renders and saves the same content without any interactivity.
 */

/* Internal dependencies */
import RecipeCard from './components/RecipeCard';
import _omit from "lodash/omit";

/* External dependencies */
const { __ } = wp.i18n;
const { registerBlockType, createBlock } = wp.blocks; // Import registerBlockType() from wp.blocks
const { is_pro, setting_options } = wpzoomRecipeCard;

let transforms = [];

if ( is_pro ) {
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
    title: __( "Recipe Card Block (Schema.org)", "wpzoom-recipe-card" ), // Block title.
    description: __( "Display a Recipe Card box with recipe metadata.", "wpzoom-recipe-card" ),
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
            isDefault: setting_options.wpzoom_rcb_settings_template === 'default'
        },
        { 
            name: 'newdesign', 
            label: __( "New Design", "wpzoom-recipe-card" ),
            isDefault: setting_options.wpzoom_rcb_settings_template === 'newdesign'
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

    save() {
        // Rendering in PHP
        return null;
    }

} );


