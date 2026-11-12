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

/**
 * The Accent Color setting, or '' when it is not set (3.8.0). Same rules as
 * WPZOOM_Helpers::get_accent_color() in PHP.
 *
 * @returns {string} A hex color or ''.
 */
export function getAccentColor() {
    const { setting_options } = wpzoomRecipeCard;
    const color = String( setting_options.wpzoom_rcb_settings_primary_color || '' );

    if ( '#F2A123' === color.toUpperCase() && '1' !== String( setting_options.wpzoom_rcb_accent_color_unlocked || '' ) ) {
        return '';
    }

    return /^#([0-9a-f]{3}|[0-9a-f]{6})$/i.test( color ) ? color : '';
}

/**
 * A Global Colors setting, or '' when it is unset or equal to its default (3.8.0).
 *
 * @param {string} option       Option id.
 * @param {string} defaultColor The field's default.
 *
 * @returns {string} A hex color or ''.
 */
export function getGlobalColor( option, defaultColor ) {
    const { setting_options } = wpzoomRecipeCard;
    const color = String( setting_options[ option ] || '' );

    if ( ! /^#([0-9a-f]{3}|[0-9a-f]{6})$/i.test( color ) || color.toLowerCase() === defaultColor.toLowerCase() ) {
        return '';
    }

    return color;
}

// primary_color values that Free wrote into every new card before 3.8.0 without a picker.
// Matched exactly: those were uppercase, the color picker writes lowercase.
const UNSET_PRIMARY_COLORS = [ '#222222', '#E99E3C', '#FFA921' ];

/**
 * A color picked on the card itself, or '' (3.8.0). Same rules as
 * WPZOOM_Helpers::get_card_color() in PHP.
 *
 * @param {Object} settings     The card's settings (settings[0]).
 * @param {string} key          Settings key, e.g. 'primary_color'.
 * @param {string} defaultColor The style's own color for that key.
 *
 * @returns {string} A lowercase hex color or ''.
 */
export function getCardColor( settings, key, defaultColor = '' ) {
    const raw = String( ( settings && settings[ key ] ) || '' );

    if ( ! /^#([0-9a-f]{3}|[0-9a-f]{6})$/i.test( raw ) ) {
        return '';
    }
    if ( 'primary_color' === key && UNSET_PRIMARY_COLORS.includes( raw ) ) {
        return '';
    }

    const color = raw.toLowerCase();

    return color === defaultColor.toLowerCase() ? '' : color;
}

/**
 * The accent a card uses: its own color, else the Accent Color setting (Modern, Amber,
 * Clean), else '' for the style's own color. Mirrors parse_block_settings() in PHP.
 *
 * @param {Object} settings The card's settings (settings[0]).
 * @param {string} style    The block style.
 *
 * @returns {string} A hex color or ''.
 */
export function getCardAccent( settings, style ) {
    if ( 'base' === style ) {
        return getCardColor( settings, 'primary_color', '#041728' );
    }
    if ( [ 'default', 'newdesign', 'simple' ].includes( style ) ) {
        return getCardColor( settings, 'primary_color' ) || getAccentColor();
    }

    return '';
}

/**
 * The same CSS the front end prints for the colors (see get_card_color_css() in PHP),
 * scoped to one card in the editor.
 *
 * @param {string} card     Selector for this card, starting with its #id.
 * @param {string} style    The block style.
 * @param {Object} settings The card's settings (settings[0]).
 *
 * @returns {string} CSS.
 */
export function getCardColorCss( card, style, settings = {} ) {
    const rules = [];
    const accent = getCardAccent( settings, style );

    if ( accent ) {
        rules.push( `${ card } .ingredients-list { --rcb-ticked-color: ${ accent }; }` );

        if ( 'newdesign' !== style ) {
            rules.push( `${ card } .recipe-card-notes .recipe-card-notes-list > li::before { border-color: ${ accent }; color: ${ accent }; }` );
        }
        if ( 'default' === style || 'newdesign' === style || 'simple' === style ) {
            rules.push( `${ card } .ingredients-list > li .tick-circle { border-color: ${ accent }; }` );
        }
        // Amber's own orange stays on the ingredient circles; its icons keep the ink.
        if ( 'default' === style || 'simple' === style || ( 'newdesign' === style && '#e99e3c' !== accent.toLowerCase() ) ) {
            rules.push( `${ card } .details-items .detail-item-icon { color: ${ accent } !important; }` );
        }
        if ( 'simple' === style ) {
            rules.push( `${ card } .recipe-card-image .btn-print-link { background-color: ${ accent } !important; }` );
        }
    }

    if ( 'default' === style ) {
        const highlight = getCardColor( settings, 'default_highlight_color', '#fbf9e7' );
        if ( highlight ) {
            rules.push( `${ card } .recipe-card-ingredients, ${ card } .recipe-card-notes .recipe-card-notes-list > li { background-color: ${ highlight }; }` );
        }
    } else if ( 'simple' === style ) {
        const background = getCardColor( settings, 'simple_background_color', '#fff3e0' );
        if ( background ) {
            rules.push( `${ card } .recipe-card-header-wrap, ${ card } .recipe-card-notes .recipe-card-notes-list > li { background-color: ${ background }; }` );
        }
    } else if ( 'base' === style ) {
        if ( accent ) {
            rules.push( `${ card } .recipe-card-buttons .btn-pinit-link, ${ card } .recipe-card-buttons .btn-print-link { border-color: ${ accent }; color: ${ accent }; }` );
            rules.push( `${ card } .recipe-card-buttons .btn-pinit-link:hover, ${ card } .recipe-card-buttons .btn-print-link:hover { background-color: ${ accent }; border-color: ${ accent }; color: #fff; }` );
            rules.push( `${ card } .recipe-card-details .detail-item-icon { color: ${ accent }; }` );
        }
        const background = getCardColor( settings, 'base_background_color', '#f5f7fa' );
        if ( background ) {
            rules.push( `${ card } .recipe-card-details-wrap, ${ card } .recipe-card-notes .recipe-card-notes-list > li { background-color: ${ background }; }` );
        }
        const border = getCardColor( settings, 'base_border_color', '#d9e1eb' );
        if ( border ) {
            rules.push( `${ card }, ${ card } .recipe-card-heading .recipe-sub-title, ${ card } .recipe-card-details-top, ${ card } .recipe-card-notes { border-color: ${ border }; }` );
        }
    } else if ( 'onyx' === style ) {
        const vars = [];
        [ [ 'ink', '#111111' ], [ 'panel', '#f2f2f2' ], [ 'line', '#e2e2e2' ] ].forEach( ( [ key, defaultColor ] ) => {
            const color = getCardColor( settings, `onyx_${ key }_color`, defaultColor );
            if ( color ) {
                vars.push( `--rcb-onyx-${ key }: ${ color };` );
            }
        } );
        if ( vars.length ) {
            rules.push( `${ card } { ${ vars.join( ' ' ) } }` );
        }
    }

    const title = getGlobalColor( 'wpzoom_rcb_settings_global_recipe_title_color', '#041728' );
    if ( title && 'onyx' !== style ) {
        rules.push( `${ card } .recipe-card-title { color: ${ title } !important; }` );
    }
    const text = getGlobalColor( 'wpzoom_rcb_settings_global_recipe_card_text_color', '#041728' );
    if ( text ) {
        // Onyx's summary sits on its dark header and keeps its own light color.
        const summary = 'onyx' === style ? '' : `, ${ card } .recipe-card-summary`;
        rules.push( `${ card }${ summary }, ${ card } .ingredients-list > li, ${ card } .directions-list > li, ${ card } .recipe-card-notes-list > li { color: ${ text }; }` );
    }
    const links = getGlobalColor( 'wpzoom_rcb_settings_global_links_color', '#0073aa' );
    if ( links ) {
        rules.push( `${ card } .recipe-card-summary a, ${ card } .recipe-card-ingredients a, ${ card } .recipe-card-directions a, ${ card } .recipe-card-notes a { color: ${ links }; }` );
    }

    return rules.join( ' ' );
}
