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
import { useEffect, useState } from '@wordpress/element';
import { select } from '@wordpress/data';
import RecipeChooser, {
    isFreshRecipeCard,
    isNewKeylessCard,
    generateRecipeKey,
    withFreshItemIds,
    useMultipleRecipesNotice,
    IS_RECIPE_POST_EDITOR,
    RECIPE_CARD_BLOCK,
} from './components/recipe-chooser';

const { pluginURL } = wpzoomRecipeCard;

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
    title: __( 'Recipe Card', 'recipe-card-blocks-by-wpzoom' ), // Block title.
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
        // The title was "Recipe Card (Schema.org)" before 3.7.0.
        __( 'Schema.org', 'recipe-card-blocks-by-wpzoom' ),
        __( 'structured data', 'recipe-card-blocks-by-wpzoom' ),
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
    // No `styles` here: the card's styles are picked from thumbnails in the Styles tab
    // (components/style-picker.js) and stored as the same `is-style-*` class.

    /**
     * The edit function describes the structure of your block in the context of the editor.
     * This represents what the editor will render when the block is used.
     *
     * The "edit" property must be a valid function.
     *
     * @link https://wordpress.org/gutenberg/handbook/block-api/block-edit-save/
     */
    edit: ( { attributes, setAttributes, className, clientId, isSelected } ) => {
        useMultipleRecipesNotice();

        // A freshly inserted card asks how to start (3.7.0, as PRO 7.5.0). Read
        // once at mount: hasInstance turns true during this first render.
        const [ showChooser, setShowChooser ] = useState( () => isFreshRecipeCard( attributes ) );

        // recipeKey links the card to its recipe post. New cards get one; a card
        // whose key an earlier card already has (Duplicate, paste) gets a new
        // key and new row IDs.
        useEffect( () => {
            if ( ! attributes.recipeKey ) {
                if ( showChooser ) {
                    setAttributes( { recipeKey: generateRecipeKey() } );
                } else if ( isNewKeylessCard( clientId ) ) {
                    // A duplicated or pasted pre-7.5.0 card: its own key and row IDs.
                    setAttributes( { ...withFreshItemIds( attributes ), recipeKey: generateRecipeKey() } );
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
			// No primary_color here since 4.0.0: an unset color follows the Accent Color setting
			// and the style's own color, and the Colors panel in the Styles tab sets it per card.

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

        if ( showChooser ) {
            return <RecipeChooser clientId={ clientId } attributes={ attributes } setAttributes={ setAttributes } onDone={ () => setShowChooser( false ) } />;
        }

        return <RecipeCard { ...{ attributes, setAttributes, className, clientId, isSelected } } />;
    },

    save() {
        // Rendering in PHP
        return null;
    },

} );

