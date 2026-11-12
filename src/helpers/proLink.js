/* global wpzoomRecipeCard */

/**
 * Links from the block editor to recipecard.io for features that are in PRO (4.0.0).
 *
 * Every prompt in the editor goes through proUrl(), so the links share one tracking scheme:
 * utm_source=wpadmin, utm_medium=block-editor and the feature as utm_campaign. Keep the
 * campaign names short and stable; they are what the site's analytics group by.
 *
 * The PHP screens use the same scheme with their own utm_medium (about-rcb-page, rcb-settings,
 * recipes-page-notice, ratings-page, plugins-page) and the same campaign names:
 *   plans (a general "get PRO" button), features (the features page), styles,
 *   adjustable-servings, unit-conversion, nutrition, equipment, ratings, recipe-index,
 *   roundups, woocommerce, languages.
 */

import get from 'lodash/get';

/**
 * Whether Recipe Card Blocks PRO is active. No PRO prompt is shown then.
 */
export const isPro = !! get( wpzoomRecipeCard, 'is_pro', false );

/**
 * A recipecard.io URL for a PRO prompt in the block editor.
 *
 * @param {string} path     Path on recipecard.io, e.g. 'features/unit-conversion/'.
 * @param {string} campaign What the prompt is about, e.g. 'unit-conversion'.
 *
 * @returns {string} The URL.
 */
export function proUrl( path, campaign ) {
    return `https://recipecard.io/${ path }?utm_source=wpadmin&utm_medium=block-editor&utm_campaign=${ campaign }`;
}
