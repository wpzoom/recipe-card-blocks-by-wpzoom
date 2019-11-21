/**
 * BLOCK: block-recipe-card
 *
 * Registering a basic block with Gutenberg.
 * Simple block, renders and saves the same content without any interactivity.
 */

/* External dependencies */
import { __ } from "@wordpress/i18n";

/* Internal dependencies */
import RecipeCard from "./components/RecipeCard";
import { getBlockStyle } from "../../helpers/getBlockStyle";

/* WordPress dependencies */
const { registerBlockType } = wp.blocks; // Import registerBlockType() from wp.blocks
const { setting_options, pluginURL } = wpzoomRecipeCard;

// Create SVG icon for block
const el = wp.element.createElement;
const SVG = wp.components.SVG;
const path = el( 'path', { d: "M 17.191406 17.058594 L 17.191406 1.066406 C 17.191406 0.480469 16.710938 0 16.125 0 L 3.597656 0 C 3.011719 0 2.53125 0.480469 2.53125 1.066406 L 2.53125 18.65625 C 2.53125 19.246094 3.011719 19.722656 3.597656 19.722656 L 16.125 19.722656 C 16.515625 19.722656 16.855469 19.507812 17.042969 19.191406 L 4.664062 19.191406 C 4.078125 19.191406 3.597656 18.710938 3.597656 18.125 L 16.125 18.125 C 16.710938 18.125 17.191406 17.644531 17.191406 17.058594 Z M 6.171875 14.265625 C 5.984375 14.457031 5.675781 14.457031 5.484375 14.265625 C 5.292969 14.074219 5.292969 13.765625 5.484375 13.574219 L 8.523438 10.535156 L 9.214844 11.226562 Z M 13.574219 14.308594 C 13.382812 14.5 13.074219 14.496094 12.882812 14.308594 L 7.714844 9.136719 C 7.691406 9.117188 7.679688 9.085938 7.660156 9.0625 C 6.914062 9.417969 5.839844 9.160156 5.042969 8.363281 C 4.089844 7.414062 3.898438 6.0625 4.613281 5.347656 C 5.324219 4.636719 6.675781 4.828125 7.625 5.78125 C 8.425781 6.578125 8.679688 7.648438 8.324219 8.398438 C 8.351562 8.414062 8.378906 8.425781 8.402344 8.449219 L 13.570312 13.617188 C 13.761719 13.808594 13.761719 14.117188 13.574219 14.308594 Z M 15.464844 6.867188 L 13.25 9.082031 C 13.234375 9.101562 13.214844 9.109375 13.191406 9.121094 C 12.4375 9.792969 11.660156 9.800781 11.03125 9.394531 C 11.015625 9.410156 11.011719 9.425781 10.996094 9.441406 L 10.488281 9.949219 L 9.800781 9.261719 L 10.308594 8.75 C 10.324219 8.738281 10.339844 8.730469 10.355469 8.71875 C 9.949219 8.085938 9.953125 7.3125 10.628906 6.554688 C 10.636719 6.539062 10.648438 6.515625 10.664062 6.5 L 12.878906 4.285156 C 12.980469 4.179688 13.148438 4.179688 13.25 4.285156 C 13.351562 4.386719 13.351562 4.550781 13.25 4.652344 L 11.109375 6.792969 L 11.476562 7.164062 L 13.617188 5.023438 C 13.71875 4.921875 13.886719 4.921875 13.988281 5.023438 C 14.089844 5.125 14.089844 5.289062 13.988281 5.390625 L 11.847656 7.53125 L 12.21875 7.902344 L 14.359375 5.761719 C 14.460938 5.660156 14.625 5.660156 14.726562 5.761719 C 14.828125 5.863281 14.828125 6.027344 14.726562 6.132812 L 12.585938 8.273438 L 12.957031 8.640625 L 15.097656 6.5 C 15.199219 6.398438 15.363281 6.398438 15.464844 6.5 C 15.566406 6.601562 15.566406 6.765625 15.464844 6.867188 Z M 15.464844 6.867188 " } );
const svgIcon = el( SVG, { width: 20, height: 20, viewBox: '0 0 20 20' }, path );

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
        // // Specifying a background color to appear with the icon e.g.: in the inserter.
        // background: '#2EA55F',
        // Specifying a color for the icon (optional: if not set, a readable color will be automatically defined)
        foreground: '#2EA55F',
        // Block icon from Dashicons → https://developer.wordpress.org/resource/dashicons/.
        src: svgIcon,
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
    example: {
        attributes: {
            recipeTitle: __( "Your recipe title goes here", "wpzoom-recipe-card" ),
            hasImage: true,
            image: {
                id: 0,
                url: pluginURL + 'dist/assets/images/examples/recipe-card-image-example-1.jpg',
            },
            course: [ __( "Main", "wpzoom-recipe-card" ) ],
            cuisine: [ __( "Italian", "wpzoom-recipe-card" ) ],
            difficulty: [ __( "Medium", "wpzoom-recipe-card" ) ],
        },
    },
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
        },
        {
            name: 'simple',
            label: __( "Simple Design", "wpzoom-recipe-card" ),
            isDefault: setting_options.wpzoom_rcb_settings_template === 'simple'
        }
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
        const style = getBlockStyle( className );
        const { settings, hasInstance } = attributes;

        if ( ! hasInstance ) {
            if ( 'newdesign' === style ) {
                settings[0] = { ...settings[0], primary_color: '#FFA921' };
            }
            else if ( 'default' === style ) {
                settings[0] = { ...settings[0], primary_color: '#222222' };
            }
            else if ( 'simple' === style ) {
                settings[0] = { ...settings[0], primary_color: '#222222' };
            }

            setAttributes( { settings, hasInstance: true } );
        }

        return <RecipeCard { ...{ attributes, setAttributes, className } } />;
    },

    save() {
        // Rendering in PHP
        return null;
    }

} );



