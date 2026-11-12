/* global wpzoomRecipeCard */

import split from 'lodash/split';

/**
 * Get block style.
 *
 * @param {array} className  	The block classname.
 *
 * @returns {number} Block style.
 */
export function getBlockStyle( className ) {
    const { setting_options } = wpzoomRecipeCard;
    const regex = /is-style-(\S*)/g;
    const m = regex.exec( className );
    return m !== null ? m[ 1 ] : setting_options.wpzoom_rcb_settings_template;
}

/**
 * Whether a style uses the Base header (Base and Onyx share markup).
 *
 * @param {string} style The block style name.
 *
 * @returns {boolean} True for Base and Onyx.
 */
export function isBaseFamilyStyle( style ) {
    return 'base' === style || 'onyx' === style;
}

/**
 * Get block className without additions class names (e.g. is-style-).
 *
 * @param {array} className     The block classname.
 *
 * @returns {number} Block style.
 */
export function parseClassName( className ) {
    const m = split( className, ' ' );
    return m ? m[ 0 ] : className;
}
