/**
 * "How do you want to start?" chooser, shown by a freshly inserted Recipe Card
 * and by an empty Insert Existing Recipe block. Both offer the same three
 * paths, so users find them under either name.
 *
 * Every path ends in a block that already exists, saved exactly as before:
 * - Start from scratch: the Recipe Card as it has always been.
 * - Copy an existing recipe: the Recipe Card filled with another recipe's
 *   text (title, summary, details, ingredients, steps, notes, equipment),
 *   item IDs regenerated. An independent recipe.
 * - Insert an existing recipe: the block is replaced with an Insert Existing
 *   Recipe block, a live reference to the recipe post.
 *
 * The choice lives in component state only, never in an attribute, so saved
 * markup does not change.
 *
 * @since 3.7.0
 */

/**
 * Internal dependencies
 */
import RecipePicker from '../../../recipe-block-from-posts/recipe-picker';
import icon from '../../icon';
import { generateId } from '../../../../helpers/generateId';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { Button, Notice, Placeholder, Spinner } from '@wordpress/components';
import { useEffect, useRef, useState } from '@wordpress/element';
import { useDispatch, useSelect } from '@wordpress/data';
import { createBlock, parse } from '@wordpress/blocks';
import { addQueryArgs } from '@wordpress/url';
import apiFetch from '@wordpress/api-fetch';
import { Icon, chevronRight, info } from '@wordpress/icons';

export const RECIPE_CARD_BLOCK = 'wpzoom-recipe-card/block-recipe-card';
export const EXISTING_RECIPE_BLOCK = 'wpzoom-recipe-card/recipe-block-from-posts';

/**
 * Whether this editor edits a recipe post (wpzoom_rcb). A recipe post holds
 * exactly one Recipe Card; regular posts may hold several since 3.7.0.
 * `typenow` is printed by wp-admin before any block script runs.
 */
export const IS_RECIPE_POST_EDITOR = typeof window !== 'undefined' && 'wpzoom_rcb' === window.typenow;

const MULTIPLE_RECIPES_NOTICE = 'wpzoom-rcb-multiple-recipes';

/**
 * Show a dismissible editor notice while a post holds more than one recipe
 * (Recipe Card and Insert Existing Recipe blocks together). It informs only;
 * saving is never blocked. Every recipe block calls this; the notice ID keeps
 * it single.
 *
 * @since 3.7.0
 */
export function useMultipleRecipesNotice() {
    const count = useSelect( ( select ) => {
        const { getGlobalBlockCount } = select( 'core/block-editor' );
        return getGlobalBlockCount( RECIPE_CARD_BLOCK ) + getGlobalBlockCount( EXISTING_RECIPE_BLOCK );
    }, [] );
    const { createWarningNotice, removeNotice } = useDispatch( 'core/notices' );
    const wasMultiple = useRef( false );

    useEffect( () => {
        const isMultiple = count > 1;

        if ( isMultiple && ! wasMultiple.current ) {
            createWarningNotice(
                __( 'This post now has more than one recipe. Each recipe keeps its own ratings and recipe post, but Google shows recipe rich results best with one recipe per page, so consider giving each recipe its own post.', 'recipe-card-blocks-by-wpzoom' ),
                { id: MULTIPLE_RECIPES_NOTICE, isDismissible: true }
            );
        } else if ( ! isMultiple && wasMultiple.current ) {
            removeNotice( MULTIPLE_RECIPES_NOTICE );
        }

        wasMultiple.current = isMultiple;
    }, [ count ] );
}

/**
 * Option icons, 24x24, drawn in currentColor.
 */
const OPTION_ICONS = {
    scratch: (
        <svg width="24" height="24" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">
            <path fillRule="evenodd" d="M14.3 3H6a1 1 0 0 0-1 1v16a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V7.7L14.3 3zM13.5 4.9 17.1 8.5h-3.6V4.9zM6.5 19.5v-15H12V10h5.5v9.5h-11z" fill="currentColor" />
            <path d="M11.25 11.5h1.5v2.25H15v1.5h-2.25v2.25h-1.5v-2.25H9v-1.5h2.25z" fill="currentColor" />
        </svg>
    ),
    copy: (
        <svg width="24" height="24" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">
            <path d="M9 3h9a2 2 0 0 1 2 2v11h-1.5V5a.5.5 0 0 0-.5-.5H9V3z" fill="currentColor" />
            <path fillRule="evenodd" d="M5 7h9a1 1 0 0 1 1 1v12a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V8a1 1 0 0 1 1-1zm.5 1.5v11h8v-11h-8z" fill="currentColor" />
            <path d="M7 11h5v1.5H7zM7 14h5v1.5H7z" fill="currentColor" />
        </svg>
    ),
    insert: (
        <svg width="24" height="24" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">
            <path d="M4 10a1 1 0 0 1 1-1h3v1.5H5.5v9h13v-9H16V9h3a1 1 0 0 1 1 1v10a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V10z" fill="currentColor" />
            <path d="M11.25 3h1.5v8.19l2.47-2.47 1.06 1.06L12 14.06 7.72 9.78l1.06-1.06 2.47 2.47V3z" fill="currentColor" />
        </svg>
    ),
};

/**
 * Item lists whose rows carry an `id`, and the prefix their editors use.
 */
const ID_PREFIXES = {
    ingredients: 'ingredient-item',
    steps: 'direction-step',
    details: 'detail-item',
    equipment: 'equipment-item',
};

/**
 * Attributes never copied from another recipe: media, taxonomy terms, the
 * skin, card settings and the instance flags (decided Oct 19, 2026: a copy
 * takes the text only).
 */
const NOT_COPIED = [
    'recipeKey',
    'hasInstance',
    'initDetails',
    'className',
    'settings',
    'image',
    'hasImage',
    'video',
    'hasVideo',
    'videoTitle',
    'course',
    'cuisine',
    'difficulty',
    'keywords',
];

/**
 * A new key linking a Recipe Card to the recipe post the saver creates for it
 * (`recipeKey` attribute, `_wpzoom_rcb_card_key` meta on the recipe post).
 *
 * @since 3.7.0
 * @returns {string} Key.
 */
export function generateRecipeKey() {
    return `${ Date.now().toString( 36 ) }${ Math.random().toString( 36 ).slice( 2, 10 ) }`;
}

/**
 * Whether a rich-text value has visible text. Legacy content from the free
 * plugin stores rich text as an array of children.
 *
 * @param {*} value Attribute value.
 * @returns {boolean} Whether it holds text.
 */
function hasText( value ) {
    if ( typeof value === 'string' ) {
        return value.replace( /<[^>]*>/g, '' ).trim() !== '';
    }
    if ( Array.isArray( value ) ) {
        return value.length > 0;
    }
    return false;
}

/**
 * Whether a Recipe Card was just inserted and holds nothing yet.
 *
 * `hasInstance` is false only until the block's first render, so this must be
 * read once, when the block mounts. Title, ingredients and steps are checked
 * too, so content saved before `hasInstance` existed never shows the chooser.
 *
 * @param {Object} attributes Block attributes at mount.
 * @returns {boolean} Whether to show the chooser.
 */
export function isFreshRecipeCard( attributes ) {
    if ( ! attributes || attributes.hasInstance ) {
        return false;
    }

    if ( hasText( attributes.recipeTitle ) ) {
        return false;
    }

    const isFilled = ( items, key ) => Array.isArray( items ) && items.some( ( item ) => item && hasText( item[ key ] ) );

    return ! isFilled( attributes.ingredients, 'name' ) && ! isFilled( attributes.steps, 'text' );
}

/**
 * First Recipe Card block in a block tree.
 *
 * @param {Array} blocks Parsed blocks.
 * @returns {?Object} The block, or null.
 */
function findRecipeCard( blocks ) {
    for ( let i = 0; i < blocks.length; i++ ) {
        const block = blocks[ i ];
        if ( block.name === RECIPE_CARD_BLOCK ) {
            return block;
        }
        const inner = findRecipeCard( block.innerBlocks || [] );
        if ( inner ) {
            return inner;
        }
    }
    return null;
}

/**
 * The card's row lists (ingredients, steps, details, equipment) with a new
 * ID on every row, for a card that duplicates another (3.7.0).
 *
 * @param {Object} attributes Card attributes.
 * @returns {Object} Only the lists that exist, ready for setAttributes.
 */
export function withFreshItemIds( attributes ) {
    const lists = {};

    Object.keys( ID_PREFIXES ).forEach( ( key ) => {
        if ( Array.isArray( attributes[ key ] ) ) {
            lists[ key ] = attributes[ key ].map( ( item ) => (
                item && 'object' === typeof item ? { ...item, id: generateId( ID_PREFIXES[ key ] ) } : item
            ) );
        }
    } );

    return lists;
}

/**
 * The text of another recipe, ready to fill this card.
 *
 * Media, taxonomy terms, the skin and card settings stay as this card has
 * them; step galleries and equipment images are left out too. The one
 * setting carried over is the unit system the ingredients are written in, so
 * unit conversion reads the copied amounts correctly. Every row gets a new ID
 * so nothing that matches rows by ID (ingredient database, unit conversion,
 * Cook Mode progress) mixes the two recipes up.
 *
 * @param {Object} sourceAttributes  The source card's attributes.
 * @param {Array}  currentSettings   This card's `settings` attribute.
 * @returns {Object} Attributes to set.
 */
export function getCopiedAttributes( sourceAttributes, currentSettings ) {
    const attributes = { ...sourceAttributes };

    NOT_COPIED.forEach( ( key ) => {
        delete attributes[ key ];
    } );

    Object.keys( ID_PREFIXES ).forEach( ( key ) => {
        if ( Array.isArray( attributes[ key ] ) ) {
            attributes[ key ] = attributes[ key ].map( ( item ) => (
                item && 'object' === typeof item ? { ...item, id: generateId( ID_PREFIXES[ key ] ) } : item
            ) );
        }
    } );

    if ( Array.isArray( attributes.steps ) ) {
        attributes.steps = attributes.steps.map( ( step ) => {
            if ( ! step || 'object' !== typeof step || ! step.gallery ) {
                return step;
            }
            const copy = { ...step };
            delete copy.gallery;
            return copy;
        } );
    }

    if ( Array.isArray( attributes.equipment ) ) {
        attributes.equipment = attributes.equipment.map( ( item ) => {
            if ( ! item || 'object' !== typeof item || ! item.image ) {
                return item;
            }
            const copy = { ...item };
            delete copy.image;
            return copy;
        } );
    }

    const sourceSettings = Array.isArray( sourceAttributes.settings ) ? sourceAttributes.settings[ 0 ] : null;
    if ( sourceSettings && sourceSettings.defaultUnitSystem && Array.isArray( currentSettings ) && currentSettings.length ) {
        const settings = currentSettings.slice();
        settings[ 0 ] = { ...settings[ 0 ], defaultUnitSystem: sourceSettings.defaultUnitSystem };
        attributes.settings = settings;
    }

    return attributes;
}

/**
 * @param {Object}   props               Component props.
 * @param {string}   props.clientId      Block client ID.
 * @param {Object}   props.attributes    Block attributes.
 * @param {Function} props.setAttributes Block setAttributes.
 * @param {string}   [props.host]        Block showing the chooser: 'card' (Recipe Card) or 'existing' (Insert Existing Recipe).
 * @param {Function} [props.onDone]      Recipe Card only: called when the card should show (scratch or copy done).
 * @param {*}        [props.icon]        Placeholder icon.
 * @returns {Element} The chooser.
 */
export default function RecipeChooser( { clientId, attributes, setAttributes, host = 'card', onDone, icon: placeholderIcon = icon } ) {
    const [ mode, setMode ] = useState( 'menu' );
    const [ isBusy, setIsBusy ] = useState( false );
    const [ error, setError ] = useState( '' );

    const isCardHost = 'card' === host;

    const { postType, postId, canInsertExisting } = useSelect( ( select ) => {
        const editor = select( 'core/editor' );
        const blockEditor = select( 'core/block-editor' );
        const allowed = blockEditor.getSettings().allowedBlockTypes;

        return {
            postType: editor ? editor.getCurrentPostType() : '',
            postId: editor ? editor.getCurrentPostId() : 0,
            // The recipe post editor allows only the recipe blocks.
            canInsertExisting: allowed === true || allowed === undefined || ( Array.isArray( allowed ) && allowed.indexOf( EXISTING_RECIPE_BLOCK ) !== -1 ),
        };
    }, [] );

    const { replaceBlocks } = useDispatch( 'core/block-editor' );

    const isRecipePost = 'wpzoom_rcb' === postType;
    const showInsertOption = ! isRecipePost && canInsertExisting;

    /**
     * Replace this Insert Existing Recipe block with a Recipe Card.
     * `hasInstance` is set so the new card skips this chooser; its first-render
     * setup still runs because `initDetails` is not.
     *
     * @param {Object} cardAttributes Attributes for the card.
     */
    const replaceWithCard = ( cardAttributes = {} ) => {
        replaceBlocks( clientId, createBlock( RECIPE_CARD_BLOCK, { ...cardAttributes, hasInstance: true, recipeKey: generateRecipeKey() } ) );
    };

    const startFromScratch = () => {
        if ( isCardHost ) {
            onDone();
        } else {
            replaceWithCard();
        }
    };

    const insertExisting = ( recipeId ) => {
        if ( ! recipeId || '-1' === recipeId ) {
            return;
        }
        if ( isCardHost ) {
            replaceBlocks( clientId, createBlock( EXISTING_RECIPE_BLOCK, { postId: String( recipeId ) } ) );
        } else {
            setAttributes( { postId: String( recipeId ) } );
        }
    };

    const copyExisting = ( recipeId ) => {
        if ( ! recipeId || '-1' === recipeId ) {
            return;
        }

        setIsBusy( true );
        setError( '' );

        apiFetch( {
            path: addQueryArgs( `/wp/v2/wpzoom_rcb/${ recipeId }`, { context: 'edit', _fields: 'content' } ),
        } ).then( ( record ) => {
            const raw = record && record.content && record.content.raw ? record.content.raw : '';
            const source = findRecipeCard( parse( raw ) );

            if ( ! source ) {
                setIsBusy( false );
                setError( __( 'That recipe has no recipe card to copy.', 'recipe-card-blocks-by-wpzoom' ) );
                return;
            }

            if ( isCardHost ) {
                setAttributes( getCopiedAttributes( source.attributes, attributes.settings ) );
                onDone();
            } else {
                // A new card's default settings, so the unit system can be carried over.
                const defaults = createBlock( RECIPE_CARD_BLOCK ).attributes;
                replaceWithCard( getCopiedAttributes( source.attributes, defaults.settings ) );
            }
        } ).catch( ( xhr ) => {
            setIsBusy( false );
            setError( xhr && xhr.message ? xhr.message : __( 'Could not load that recipe.', 'recipe-card-blocks-by-wpzoom' ) );
        } );
    };

    /**
     * One choice: icon tile, title, description and an arrow. A disabled
     * choice says why in its own line instead of in the title.
     *
     * @param {string}   key          Option key (also picks the icon).
     * @param {string}   title        Title.
     * @param {string}   description  What happens.
     * @param {Function} onClick      Click handler.
     * @param {string}   disabledNote Why it can't be used now; empty when it can.
     * @returns {Element} The option.
     */
    const renderOption = ( key, title, description, onClick, disabledNote = '' ) => (
        <Button
            key={ key }
            className={ `wpzoom-rcb-recipe-chooser__option wpzoom-rcb-recipe-chooser__option--${ key }` }
            onClick={ onClick }
            disabled={ !! disabledNote }
        >
            <span className="wpzoom-rcb-recipe-chooser__option-icon">{ OPTION_ICONS[ key ] }</span>
            <span className="wpzoom-rcb-recipe-chooser__option-text">
                <span className="wpzoom-rcb-recipe-chooser__option-title">{ title }</span>
                <span className="wpzoom-rcb-recipe-chooser__option-description">{ description }</span>
                { disabledNote && (
                    <span className="wpzoom-rcb-recipe-chooser__option-note">
                        <Icon icon={ info } size={ 16 } />
                        { disabledNote }
                    </span>
                ) }
            </span>
            { ! disabledNote && (
                <span className="wpzoom-rcb-recipe-chooser__option-arrow">
                    <Icon icon={ chevronRight } size={ 20 } />
                </span>
            ) }
        </Button>
    );

    const scratchOption = renderOption(
        'scratch',
        __( 'Start from scratch', 'recipe-card-blocks-by-wpzoom' ),
        __( 'An empty recipe card.', 'recipe-card-blocks-by-wpzoom' ),
        startFromScratch
    );

    const copyOption = renderOption(
        'copy',
        __( 'Copy an existing recipe', 'recipe-card-blocks-by-wpzoom' ),
        __( 'Start from the text of one of your recipes. Images, video and categories are not copied.', 'recipe-card-blocks-by-wpzoom' ),
        () => setMode( 'copy' )
    );

    const backButton = (
        <Button
            className="wpzoom-rcb-recipe-chooser__back"
            isLink
            disabled={ isBusy }
            onClick={ () => {
                setError( '' );
                setMode( 'menu' );
            } }
        >
            { __( '← Back', 'recipe-card-blocks-by-wpzoom' ) }
        </Button>
    );

    let content;

    if ( 'menu' === mode && ! isCardHost ) {
        // Insert Existing Recipe: the recipe picker first, as this block has
        // always shown it, then the ways to start a new recipe instead.
        content = (
            <div className="wpzoom-rcb-recipe-chooser__picker">
                <p className="wpzoom-rcb-recipe-chooser__picker-label">
                    { __( 'Choose the recipe to insert', 'recipe-card-blocks-by-wpzoom' ) }
                </p>
                <RecipePicker value="-1" onChange={ insertExisting } focusOnMount />
                <p className="wpzoom-rcb-recipe-chooser__divider">
                    <span>{ __( 'or start a new recipe', 'recipe-card-blocks-by-wpzoom' ) }</span>
                </p>
                <div className="wpzoom-rcb-recipe-chooser__options">
                    { scratchOption }
                    { copyOption }
                </div>
            </div>
        );
    } else if ( 'menu' === mode ) {
        content = (
            <div className="wpzoom-rcb-recipe-chooser__options">
                { scratchOption }
                { copyOption }
                { showInsertOption && renderOption(
                    'insert',
                    __( 'Insert an existing recipe', 'recipe-card-blocks-by-wpzoom' ),
                    __( 'Show one of your recipes here. Edits to it appear everywhere it is used.', 'recipe-card-blocks-by-wpzoom' ),
                    () => setMode( 'insert' )
                ) }
            </div>
        );
    } else {
        const isCopy = 'copy' === mode;

        content = (
            <div className="wpzoom-rcb-recipe-chooser__picker">
                <p className="wpzoom-rcb-recipe-chooser__picker-label">
                    { isCopy ?
                        __( 'Choose the recipe to copy', 'recipe-card-blocks-by-wpzoom' ) :
                        __( 'Choose the recipe to insert', 'recipe-card-blocks-by-wpzoom' ) }
                </p>
                { isBusy ? <Spinner /> : (
                    <RecipePicker
                        value="-1"
                        excludeId={ isRecipePost ? postId : 0 }
                        onChange={ isCopy ? copyExisting : insertExisting }
                        focusOnMount
                    />
                ) }
                { error && <Notice status="error" isDismissible={ false }>{ error }</Notice> }
                { backButton }
            </div>
        );
    }

    return (
        <Placeholder
            className="wpzoom-rcb-recipe-chooser"
            icon={ placeholderIcon }
            label={ isCardHost ?
                __( 'Recipe Card', 'recipe-card-blocks-by-wpzoom' ) :
                __( 'Insert Existing Recipe', 'recipe-card-blocks-by-wpzoom' ) }
            instructions={ 'menu' === mode && isCardHost ? __( 'How do you want to start?', 'recipe-card-blocks-by-wpzoom' ) : '' }
        >
            { content }
        </Placeholder>
    );
}
