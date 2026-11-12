/* global wpzoomRecipeCard */

/**
 * Detail icons as inline SVG in the editor (4.0.0).
 *
 * The front end prints detail icons from src/assets/icons/svg-registry.json (see
 * WPZOOM_Recipe_Block_Icon_SVG in PHP). The editor reads the same file, once, so the card
 * preview and the icon picker show the drawing that will be published. Until the file has
 * loaded, and for icons it does not have (Genericons), the icon font is used.
 */

/* WordPress dependencies */
import { useState, useEffect } from '@wordpress/element';

let registry = null;
let request = null;

function loadRegistry() {
    if ( ! request ) {
        const { pluginURL, version } = wpzoomRecipeCard;

        request = window.fetch( `${ pluginURL }src/assets/icons/svg-registry.json?ver=${ version }` )
            .then( ( response ) => ( response.ok ? response.json() : {} ) )
            .catch( () => ( {} ) )
            .then( ( data ) => {
                registry = data && 'object' === typeof data ? data : {};
                return registry;
            } );
    }

    return request;
}

/**
 * SVG markup for an icon, or null. Same lookup as get_lookup_key() in PHP.
 *
 * @param {string} iconSet Icon set: oldicon, foodicons, dashicons, fa, genericons.
 * @param {string} icon    Icon name inside the set.
 * @param {string} prefix  Font Awesome style (fas, far); other sets ignore it.
 *
 * @return {?string} The SVG markup.
 */
export function getIconSvg( iconSet, icon, prefix ) {
    const icons = registry && registry[ iconSet ];

    if ( ! icons || ! icon ) {
        return null;
    }

    if ( 'fa' === iconSet ) {
        const prefixes = 'fas' === prefix || 'far' === prefix ? [ prefix ] : [ 'fas', 'far' ];

        for ( const faPrefix of prefixes ) {
            if ( icons[ `${ faPrefix } fa-${ icon }` ] ) {
                return icons[ `${ faPrefix } fa-${ icon }` ];
            }
        }

        return null;
    }

    return icons[ icon ] || icons[ `${ iconSet }-${ icon }` ] || null;
}

/**
 * Re-renders the calling component once the registry has loaded.
 *
 * @return {boolean} Whether the registry is ready.
 */
export function useIconRegistry() {
    const [ isReady, setReady ] = useState( null !== registry );

    useEffect( () => {
        let isMounted = true;

        if ( ! isReady ) {
            loadRegistry().then( () => isMounted && setReady( true ) );
        }

        return () => {
            isMounted = false;
        };
    }, [] );

    return isReady;
}

/**
 * The icon shown on a detail in the card.
 */
export function DetailIcon( { iconSet, icon, prefix, style } ) {
    useIconRegistry();

    const svg = getIconSvg( iconSet, icon, prefix );

    if ( svg ) {
        return <span className="wpzoom-rcb-icon-svg" style={ style } dangerouslySetInnerHTML={ { __html: svg } } />;
    }

    return <span className={ `${ prefix || iconSet } ${ iconSet }-${ icon }` } style={ style }></span>;
}

/**
 * One icon in the picker's grid.
 */
export function PickerIcon( { fontClassName, isActive, iconSet, icon, prefix, onClick } ) {
    useIconRegistry();

    const svg = getIconSvg( iconSet, icon, prefix );
    const active = isActive ? ' icon-element-active' : '';

    if ( svg ) {
        return <span className={ `wpzoom-recipe-card-icons__single-element has-svg${ active }` } onClick={ onClick } dangerouslySetInnerHTML={ { __html: svg } } />;
    }

    return <span className={ `${ fontClassName }${ active }` } onClick={ onClick }></span>;
}
