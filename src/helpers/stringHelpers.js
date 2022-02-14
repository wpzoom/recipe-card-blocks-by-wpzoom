/**
 * External dependencies
 */
import { replace, includes } from 'lodash';

 /**
 * Capitalize the first letter of a string.
 *
 * @param   {string} string The string to capitalize.
 *
 * @returns {string}        The string with the first letter capitalized.
 */
export function firstToUpperCase( string ) {
    return string.charAt( 0 ).toUpperCase() + string.slice( 1 );
}

/**
 * Strips HTML from a string.
 *
 * @param {string} string  The string to strip HTML from.
 *
 * @returns {string} The string with HTML stripped.
 */
export function stripHTML( string ) {
    const tmp = document.createElement( 'DIV' );
    tmp.innerHTML = string;
    return tmp.textContent || tmp.innerText || '';
}

/**
 * Replace underscores with spaces and capitalize words.
 *
 * @param   {string} string The string to capitalize.
 *
 * @returns {string}        The string with the first letter capitalized and underscore removed.
 */
export function humanize( string ) {
    const frags = string.split( '_' );
    for ( let i = 0; i < frags.length; i++ ) {
        frags[ i ] = firstToUpperCase( frags[ i ] );
    }
    return frags.join( ' ' );
}

/**
 * Extract the src element from all image tags in an HTML string passed.
 *
 * @param   {string} string The string to extract img src.
 *
 * @returns {array}         The array with all extracted src from string.
 */
export function matchIMGsrc( string ) {
    const regex = /<img[^>]+src="([^">]+)"/gm;
    const IMGsources = [];
    let m;
    let i = 0;

    while ( ( m = regex.exec( string ) ) !== null ) {
        // This is necessary to avoid infinite loops with zero-width matches
        if ( m.index === regex.lastIndex ) {
            regex.lastIndex++;
        }

        // The result can be accessed through the `m`-variable.
        m.forEach( ( match, groupIndex ) => {
            if ( groupIndex === 1 ) {
                IMGsources[ i ] = match;
            }
        } );

        i++;
    }

    if ( IMGsources.length ) {
        return IMGsources;
    }

    return false;
}

/**
 * Deserealize and convert the block attributes into the HTML
 *
 * @param {string} string  The string to deserialize from.
 *
 * @returns {string} The deseralized string.
 */
 export function deserializeAttributes( string ) {
	if ( ! includes( string, '\\u003c' ) && includes( string, 'u003c' ) ) {
        string = replace( string, /u003c/g, '<' );
    }
    if ( ! includes( string, '\\u003e' ) && includes( string, 'u003e' ) ) {
        string = replace( string, /u003e/g, '>' );
    }
    if ( ! includes( string, '\\u0022' ) && includes( string, 'u0022' ) ) {
        string = replace( string, /u0022/g, '"' );
    }
    if ( ! includes( string, '\\u002d' ) && includes( string, 'u002d' ) ) {
        string = replace( string, /u002d/g, '--' );
    }
    if ( ! includes( string, '\\u0026' ) && includes( string, 'u0026' ) ) {
        string = replace( string, /u0026/g, '&' );
    }
    return string;
}