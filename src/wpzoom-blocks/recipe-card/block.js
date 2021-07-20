/**
 * BLOCK: block-recipe-card
 *
 * Registering a basic block with Gutenberg.
 * Simple block, renders and saves the same content without any interactivity.
 */

/* External dependencies */
import { __ } from '@wordpress/i18n';
import map from 'lodash/map';
import isNull from 'lodash/isNull';

/* Internal dependencies */
import RecipeCard from './components/RecipeCard';
import { getBlockStyle } from '../../helpers/getBlockStyle';
import { generateId } from '../../helpers/generateId';
import icon from './icon';

/* WordPress dependencies */
import { registerBlockType } from '@wordpress/blocks';
const { setting_options, pluginURL } = wpzoomRecipeCard;

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
    title: __( 'Recipe Card Block (Schema.org)', 'wpzoom-recipe-card' ), // Block title.
    description: __( 'Display a Recipe Card box with recipe metadata.', 'wpzoom-recipe-card' ),
    icon: {
        // // Specifying a background color to appear with the icon e.g.: in the inserter.
        // background: '#2EA55F',
        // Specifying a color for the icon (optional: if not set, a readable color will be automatically defined)
        foreground: '#2EA55F',
        // Block icon from Dashicons → https://developer.wordpress.org/resource/dashicons/.
        src: icon,
    },
    category: 'wpzoom-recipe-card', // Block category — Group blocks together based on common traits E.g. common, formatting, layout widgets, embed.
    // Allow only one Recipe Card Block per post.
    supports: {
        multiple: false,
    },
    keywords: [
        __( 'Recipe Card', 'wpzoom-recipe-card' ),
        __( 'Block Recipe Card', 'wpzoom-recipe-card' ),
        __( 'WPZOOM', 'wpzoom-recipe-card' ),
    ],
    example: {
        attributes: {
            recipeTitle: __( 'Your recipe title goes here', 'wpzoom-recipe-card' ),
            hasImage: true,
            image: {
                id: 0,
                url: pluginURL + 'dist/assets/images/examples/recipe-card-image-example-1.jpg',
            },
            course: [ __( 'Main', 'wpzoom-recipe-card' ) ],
            cuisine: [ __( 'Italian', 'wpzoom-recipe-card' ) ],
            difficulty: [ __( 'Medium', 'wpzoom-recipe-card' ) ],
        },
    },
    styles: [
        // Mark style as default.
        {
            name: 'default',
            label: __( 'Default', 'wpzoom-recipe-card' ),
            isDefault: setting_options.wpzoom_rcb_settings_template === 'default',
        },
        {
            name: 'newdesign',
            label: __( 'New Design', 'wpzoom-recipe-card' ),
            isDefault: setting_options.wpzoom_rcb_settings_template === 'newdesign',
        },
        {
            name: 'simple',
            label: __( 'Simple Design', 'wpzoom-recipe-card' ),
            isDefault: setting_options.wpzoom_rcb_settings_template === 'simple',
        },
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
                settings[ 0 ] = { ...settings[ 0 ], primary_color: '#FFA921' };
            } else if ( 'default' === style ) {
                settings[ 0 ] = { ...settings[ 0 ], primary_color: '#222222' };
            } else if ( 'simple' === style ) {
                settings[ 0 ] = { ...settings[ 0 ], primary_color: '#222222' };
            }

            setAttributes( { settings, hasInstance: true } );
        }

        // Fix issue with null value for custom details items
        // Add default value instead of null
        const customDetailsDetaults = [
            {
                id: generateId( 'detail-item' ),
                iconSet: 'fa',
                _prefix: 'far',
                icon: 'clock',
            },
            {
                id: generateId( 'detail-item' ),
                iconSet: 'oldicon',
                icon: 'chef-cooking',
            },
            {
                id: generateId( 'detail-item' ),
                iconSet: 'oldicon',
                icon: 'food-1',
            },
            {
                id: generateId( 'detail-item' ),
                iconSet: 'fa',
                _prefix: 'fas',
                icon: 'sort-amount-down',
            },
        ];

        attributes.details = map( attributes.details, ( item, index ) => {
            if ( isNull( item ) ) {
                if ( 4 === index ) {
                    return customDetailsDetaults[ 0 ];
                } else if ( 5 === index ) {
                    return customDetailsDetaults[ 1 ];
                } else if ( 6 === index ) {
                    return customDetailsDetaults[ 2 ];
                } else if ( 7 === index ) {
                    return customDetailsDetaults[ 3 ];
                }
            } else {
                return item;
            }
        } );

        return <RecipeCard { ...{ attributes, setAttributes, className } } />;
    },

    save() {
        // Rendering in PHP
        return null;
    },

} );

