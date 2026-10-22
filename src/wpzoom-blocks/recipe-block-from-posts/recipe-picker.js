/**
 * Recipe picker: a searchable select of recipe posts (wpzoom_rcb).
 *
 * Shared by the Insert Existing Recipe block and the Recipe Card block's
 * "start from" chooser. Shows the 20 most recent recipes, searches the REST
 * API as the user types, and always lists the selected recipe.
 */

/**
 * External dependencies
 */
import ReactSelect from 'react-select';

/**
 * WordPress dependencies
 */
import { Disabled } from '@wordpress/components';
import { useSelect } from '@wordpress/data';
import { useEffect, useRef } from '@wordpress/element';
import { addQueryArgs } from '@wordpress/url';
import { __, sprintf } from '@wordpress/i18n';

/**
 * Label for a recipe post. Recipes saved without a title would otherwise show
 * as an empty row.
 *
 * @param {Object} post REST record of a recipe post.
 * @returns {string} Label.
 */
export function getRecipeLabel( post ) {
    const title = post && post.title ? ( post.title.raw || post.title.rendered || '' ) : '';

    if ( title.trim() ) {
        return title;
    }

    /* translators: %d: recipe post ID */
    return sprintf( __( '(no title) #%d', 'recipe-card-blocks-by-wpzoom' ), post.id );
}

/**
 * Options for a list of recipe posts. Recipes that share a title (copies,
 * imports, a recipe post and its twin) get their ID appended so they can be
 * told apart.
 *
 * @param {Array} posts REST records of recipe posts.
 * @returns {Array} react-select options.
 */
function toOptions( posts ) {
    const counts = {};
    posts.forEach( ( post ) => {
        const label = getRecipeLabel( post );
        counts[ label ] = ( counts[ label ] || 0 ) + 1;
    } );

    return posts.map( ( post ) => {
        const label = getRecipeLabel( post );
        /* translators: 1: recipe title, 2: recipe post ID */
        return { value: post.id, label: counts[ label ] > 1 ? sprintf( __( '%1$s (#%2$d)', 'recipe-card-blocks-by-wpzoom' ), label, post.id ) : label };
    } );
}

/**
 * @param {Object}   props             Component props.
 * @param {string}   props.value       Selected recipe ID, '-1' or '' for none.
 * @param {Function} props.onChange    Called with the recipe ID as a string, or '-1' when cleared.
 * @param {number}   [props.excludeId] Recipe ID to leave out of the list (the recipe being edited).
 * @param {boolean}  [props.focusOnMount] Focus the select once it is usable (the user just asked for it).
 * @returns {Element} The select.
 */
export default function RecipePicker( { value, onChange, excludeId = 0, focusOnMount = false } ) {
    const selectedId = value && String( value ).trim() !== '' && String( value ) !== '-1' ? Number( value ) : null;

    const { posts, selectedPost } = useSelect( ( select ) => {
        const { getEntityRecords, getEntityRecord } = select( 'core' );

        return {
            // Load only the 20 most recent recipes for the initial dropdown.
            posts: getEntityRecords( 'postType', 'wpzoom_rcb', { order: 'desc', orderby: 'date', per_page: 20 } ),
            // If a recipe is already selected, fetch it individually so it always appears in the dropdown.
            selectedPost: selectedId ? getEntityRecord( 'postType', 'wpzoom_rcb', selectedId ) : null,
        };
    }, [ selectedId ] );

    const isAllowed = ( post ) => post && Number( post.id ) !== Number( excludeId );

    // Build the default options list from the initial 20 + the currently selected recipe.
    const defaultPosts = ( posts || [] ).filter( isAllowed );
    if ( selectedPost && ! defaultPosts.some( ( post ) => post.id === selectedPost.id ) ) {
        defaultPosts.unshift( selectedPost );
    }
    const defaultOptions = toOptions( defaultPosts );

    // react-select's own autoFocus doesn't stick: while recipes load the select
    // renders inside <Disabled> and is remounted once they arrive. Focus it once,
    // when it first becomes usable.
    const isEnabled = defaultOptions.length > 0;
    const selectRef = useRef( null );
    const hasFocused = useRef( false );
    useEffect( () => {
        if ( ! focusOnMount || ! isEnabled || hasFocused.current ) {
            return undefined;
        }
        const timer = setTimeout( () => {
            if ( selectRef.current && selectRef.current.focus ) {
                selectRef.current.focus();
                hasFocused.current = true;
            }
        }, 50 );
        return () => clearTimeout( timer );
    }, [ isEnabled ] );

    // Search recipes server-side as the user types (react-select v1 Async callback style).
    const loadOptions = ( inputValue, callback ) => {
        // Empty input: return the default list instead of hitting the API.
        if ( ! inputValue || ! inputValue.trim() ) {
            callback( null, { options: defaultOptions } );
            return;
        }

        const url = addQueryArgs( '/wp/v2/wpzoom_rcb', {
            search: inputValue,
            per_page: 20,
            order: 'desc',
            orderby: 'relevance',
        } );
        wp.apiFetch( { path: url } ).then( ( results ) => {
            callback( null, {
                options: toOptions( results.filter( isAllowed ) ),
            } );
        } ).catch( () => {
            callback( null, { options: [] } );
        } );
    };

    const select = (
        <ReactSelect.Async
            ref={ selectRef }
            className="wpzoom-select-cpt-recipe-cards"
            options={ defaultOptions }
            loadOptions={ loadOptions }
            value={ selectedId ? String( selectedId ) : '-1' }
            onChange={ ( newValue ) => onChange( newValue ? String( newValue ) : '-1' ) }
            simpleValue
            clearable={ true }
            searchable={ true }
            placeholder={ __( 'Search for a recipe…', 'recipe-card-blocks-by-wpzoom' ) }
            noResultsText={ __( 'No recipes found', 'recipe-card-blocks-by-wpzoom' ) }
            loadingPlaceholder={ __( 'Searching…', 'recipe-card-blocks-by-wpzoom' ) }
            autoload={ false }
            cache={ false }
        />
    );

    return isEnabled ? select : <Disabled>{ select }</Disabled>;
}
