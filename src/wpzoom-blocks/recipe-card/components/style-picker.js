/* global wpzoomRecipeCard */

/**
 * Recipe Card style picker: one thumbnail per style, in the block's Styles tab (3.8.0).
 *
 * The chosen style is stored the way block styles always were, as an `is-style-*` class in
 * the block's className, so cards saved by earlier versions keep their style.
 */

/* External dependencies */
import classnames from 'classnames';

/* Internal dependencies */
import { getBlockStyle } from '../../../helpers/getBlockStyle';

/* WordPress dependencies */
import { __ } from '@wordpress/i18n';
import { InspectorControls } from '@wordpress/block-editor';
import { PanelBody } from '@wordpress/components';

const { pluginURL } = wpzoomRecipeCard;
const EXAMPLES_URL = pluginURL + 'dist/assets/images/examples/';

// Keep in sync with WPZOOM_Helpers::get_recipe_card_styles().
export const RECIPE_CARD_STYLES = [
    {
        name: 'base',
        label: __( 'Base', 'recipe-card-blocks-by-wpzoom' ),
        preview: EXAMPLES_URL + 'rcb-example-base.jpg',
    },
    {
        name: 'onyx',
        label: __( 'Onyx', 'recipe-card-blocks-by-wpzoom' ),
        preview: EXAMPLES_URL + 'rcb-example-onyx.jpg',
    },
    {
        name: 'simple',
        label: __( 'Clean', 'recipe-card-blocks-by-wpzoom' ),
        preview: EXAMPLES_URL + 'rcb-example-simple.jpg',
    },
    {
        name: 'default',
        label: __( 'Modern', 'recipe-card-blocks-by-wpzoom' ),
        preview: EXAMPLES_URL + 'rcb-example-default.jpg',
    },
    {
        name: 'newdesign',
        label: __( 'Amber', 'recipe-card-blocks-by-wpzoom' ),
        preview: EXAMPLES_URL + 'rcb-example-newdesign.jpg',
    },
];

/**
 * Replaces the style class in a className.
 *
 * @param {string} className The block's className attribute (may be undefined).
 * @param {string} styleName The style to switch to.
 *
 * @return {string} The updated className.
 */
export function replaceStyleClassName( className, styleName ) {
    const kept = ( className || '' )
        .split( /\s+/ )
        .filter( ( name ) => name && 0 !== name.indexOf( 'is-style-' ) );

    kept.push( 'is-style-' + styleName );

    return kept.join( ' ' );
}

const StylePicker = ( { attributes, setAttributes, className } ) => {
    const activeStyle = getBlockStyle( className );

    return (
        <InspectorControls group="styles">
            <PanelBody className="wpzoom-recipe-card-styles" title={ __( 'Styles', 'recipe-card-blocks-by-wpzoom' ) } initialOpen={ true }>
                <div className="wpzoom-rcb-style-picker">
                    { RECIPE_CARD_STYLES.map( ( style ) => (
                        <button
                            key={ style.name }
                            type="button"
                            className={ classnames( 'wpzoom-rcb-style-picker__item', { 'is-active': activeStyle === style.name } ) }
                            aria-pressed={ activeStyle === style.name }
                            onClick={ () => setAttributes( { className: replaceStyleClassName( attributes.className, style.name ) } ) }
                        >
                            <span className="wpzoom-rcb-style-picker__preview">
                                <img src={ style.preview } alt="" />
                            </span>
                            <span className="wpzoom-rcb-style-picker__label">{ style.label }</span>
                        </button>
                    ) ) }
                </div>
            </PanelBody>
        </InspectorControls>
    );
};

export default StylePicker;
