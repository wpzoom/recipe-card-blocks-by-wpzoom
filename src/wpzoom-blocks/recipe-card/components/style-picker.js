/* global wpzoomRecipeCard */

/**
 * Recipe Card style picker: one thumbnail per style, in the block's Styles tab (4.0.0).
 *
 * The chosen style is stored the way block styles always were, as an `is-style-*` class in
 * the block's className, so cards saved by earlier versions keep their style.
 */

/* External dependencies */
import classnames from 'classnames';

/* Internal dependencies */
import { getBlockStyle } from '../../../helpers/getBlockStyle';
import StyleColors from './style-colors';
import { isPro, proUrl } from '../../../helpers/proLink';

/* WordPress dependencies */
import { __, sprintf } from '@wordpress/i18n';
import { Fragment } from '@wordpress/element';
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

// Styles that only PRO has, shown under the others as locked previews that link to the styles
// page on recipecard.io (4.0.0). Thumbnails come from the same script as the ones above
// (rcb-demo-site/tools/free-style-thumbs.py).
const PRO_STYLES = [
    {
        name: 'slate',
        label: __( 'Slate', 'recipe-card-blocks-by-wpzoom' ),
        preview: EXAMPLES_URL + 'rcb-example-slate.jpg',
    },
    {
        name: 'mint',
        label: __( 'Mint', 'recipe-card-blocks-by-wpzoom' ),
        preview: EXAMPLES_URL + 'rcb-example-mint.jpg',
    },
    {
        name: 'accent-color-header',
        label: __( 'Vibrant', 'recipe-card-blocks-by-wpzoom' ),
        preview: EXAMPLES_URL + 'rcb-example-accent-color-header.jpg',
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

const StylePicker = ( { attributes, setAttributes, className, cardId } ) => {
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
                { ! isPro && (
                    <Fragment>
                        { /* A div: the editor sidebar removes the top margin of paragraphs. */ }
                        <div className="wpzoom-rcb-style-picker__more">{ __( 'More styles in PRO', 'recipe-card-blocks-by-wpzoom' ) }</div>
                        <div className="wpzoom-rcb-style-picker">
                            { PRO_STYLES.map( ( style ) => (
                                <a
                                    key={ style.name }
                                    className="wpzoom-rcb-style-picker__item is-locked"
                                    href={ proUrl( 'features/styles/', 'styles' ) }
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    title={ sprintf( __( '%s is a PRO style. See all styles.', 'recipe-card-blocks-by-wpzoom' ), style.label ) }
                                >
                                    <span className="wpzoom-rcb-style-picker__preview">
                                        <img src={ style.preview } alt="" />
                                        <span className="wpzoom-rcb-style-picker__badge">{ __( 'PRO', 'recipe-card-blocks-by-wpzoom' ) }</span>
                                    </span>
                                    <span className="wpzoom-rcb-style-picker__label">{ style.label }</span>
                                </a>
                            ) ) }
                        </div>
                    </Fragment>
                ) }
            </PanelBody>
            <StyleColors { ...{ attributes, setAttributes, className, cardId } } />
        </InspectorControls>
    );
};

export default StylePicker;
