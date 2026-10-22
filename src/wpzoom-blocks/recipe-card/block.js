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
import { useEffect } from '@wordpress/element';
import { select } from '@wordpress/data';

/*
 * Several Recipe Cards per post (3.7.0), matching Recipe Card Blocks PRO 7.5.0.
 * A recipe post (wpzoom_rcb) holds exactly one; `typenow` is printed by wp-admin.
 */
const IS_RECIPE_POST_EDITOR = typeof window !== 'undefined' && 'wpzoom_rcb' === window.typenow;
const RECIPE_CARD_BLOCK = 'wpzoom-recipe-card/block-recipe-card';
const ID_PREFIXES = {
    ingredients: 'ingredient-item',
    steps: 'direction-step',
    details: 'detail-item',
};

/**
 * A new key linking a card to the recipe post the saver creates for it.
 *
 * @returns {string} Key.
 */
function generateRecipeKey() {
    return `${ Date.now().toString( 36 ) }${ Math.random().toString( 36 ).slice( 2, 10 ) }`;
}

/**
 * Whether a card was just inserted and holds nothing yet.
 *
 * @param {Object} attributes Attributes at mount.
 * @returns {boolean} Whether it is new.
 */
function isNewRecipeCard( attributes ) {
    const hasText = ( value ) => ( typeof value === 'string' ? value.replace( /<[^>]*>/g, '' ).trim() !== '' : Array.isArray( value ) && value.length > 0 );
    const isFilled = ( items, key ) => Array.isArray( items ) && items.some( ( item ) => item && hasText( item[ key ] ) );

    return ! attributes.hasInstance && ! hasText( attributes.recipeTitle ) && ! isFilled( attributes.ingredients, 'name' ) && ! isFilled( attributes.steps, 'text' );
}

/**
 * Row lists with a new ID on every row, for a card that duplicates another.
 *
 * @param {Object} attributes Card attributes.
 * @returns {Object} Lists ready for setAttributes.
 */
function withFreshItemIds( attributes ) {
    const lists = {};
    Object.keys( ID_PREFIXES ).forEach( ( key ) => {
        if ( Array.isArray( attributes[ key ] ) ) {
            lists[ key ] = attributes[ key ].map( ( item ) => ( item && 'object' === typeof item ? { ...item, id: generateId( ID_PREFIXES[ key ] ) } : item ) );
        }
    } );
    return lists;
}
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
    title: __( 'Recipe Card (Schema.org)', 'recipe-card-blocks-by-wpzoom' ), // Block title.
    description: __( 'Display a Recipe Card with recipe metadata.', 'recipe-card-blocks-by-wpzoom' ),
    icon: {
        // // Specifying a background color to appear with the icon e.g.: in the inserter.
        // background: '#2EA55F',
        // Specifying a color for the icon (optional: if not set, a readable color will be automatically defined)
        foreground: '#2EA55F',
        // Block icon from Dashicons → https://developer.wordpress.org/resource/dashicons/.
        src: icon,
    },
    category: 'wpzoom-recipe-card', // Block category — Group blocks together based on common traits E.g. common, formatting, layout widgets, embed.
    // Several per post since 3.7.0; a recipe post holds one.
    supports: {
        multiple: ! IS_RECIPE_POST_EDITOR,
    },
    keywords: [
        __( 'Recipe Card', 'recipe-card-blocks-by-wpzoom' ),
        __( 'Block Recipe Card', 'recipe-card-blocks-by-wpzoom' ),
        __( 'WPZOOM', 'recipe-card-blocks-by-wpzoom' ),
    ],
    example: {
        attributes: {
            recipeTitle: __( 'Your recipe title goes here', 'recipe-card-blocks-by-wpzoom' ),
            hasImage: true,
            image: {
                id: 0,
                url: pluginURL + 'dist/assets/images/examples/recipe-card-image-example-1.jpg',
            },
            course: [ __( 'Main', 'recipe-card-blocks-by-wpzoom' ) ],
            cuisine: [ __( 'Italian', 'recipe-card-blocks-by-wpzoom' ) ],
            difficulty: [ __( 'Medium', 'recipe-card-blocks-by-wpzoom' ) ],
        },
    },
    styles: [
        // Mark style as default.
        {
            name: 'default',
            label: __( 'Modern', 'recipe-card-blocks-by-wpzoom' ),
            isDefault: setting_options.wpzoom_rcb_settings_template === 'default',
        },
        {
            name: 'newdesign',
            label: __( 'Amber', 'recipe-card-blocks-by-wpzoom' ),
            isDefault: setting_options.wpzoom_rcb_settings_template === 'newdesign',
        },
        {
            name: 'simple',
            label: __( 'Clean', 'recipe-card-blocks-by-wpzoom' ),
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
    edit: ( { attributes, setAttributes, className, clientId } ) => {
        // recipeKey links the card to its recipe post. New cards get one; a card
        // whose key an earlier card already has (Duplicate, paste) gets a new
        // key and new row IDs. The closure holds the attributes at mount.
        useEffect( () => {
            if ( ! attributes.recipeKey ) {
                if ( isNewRecipeCard( attributes ) ) {
                    setAttributes( { recipeKey: generateRecipeKey() } );
                }
                return;
            }

            const { getClientIdsWithDescendants, getBlockName, getBlockAttributes } = select( 'core/block-editor' );
            const clientIds = getClientIdsWithDescendants();

            for ( let i = 0; i < clientIds.length && clientIds[ i ] !== clientId; i++ ) {
                if ( RECIPE_CARD_BLOCK === getBlockName( clientIds[ i ] ) && getBlockAttributes( clientIds[ i ] ).recipeKey === attributes.recipeKey ) {
                    setAttributes( { ...withFreshItemIds( attributes ), recipeKey: generateRecipeKey() } );
                    break;
                }
            }
        }, [] );

        const style = getBlockStyle( className );
        const { settings, hasInstance, initDetails, details } = attributes;

        if ( ! hasInstance || ! initDetails ) {
			if ( 'newdesign' === style ) {
				settings[ 0 ] = { ...settings[ 0 ], primary_color: '#FFA921' };
			} else if ( 'default' === style ) {
				settings[ 0 ] = { ...settings[ 0 ], primary_color: '#222222' };
			} else if ( 'simple' === style ) {
				settings[ 0 ] = { ...settings[ 0 ], primary_color: '#222222' };
			}

			const newSettings = settings ? settings.slice() : [];
			const newDetails = details ? details.slice() : []; 

			newSettings[ 0 ] = {
				...newSettings[ 0 ],
				blockInit: true
			};

			newDetails[9] = {
				...newDetails[9],
				value: 1,
			}

			setAttributes( { 
				details : newDetails,
				settings : newSettings,
				hasInstance: true, 
				initDetails: true
			} );
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

