/**
 * Recipe Card per-card colors: the Colors panel under the style thumbnails (4.0.0).
 *
 * Each style shows only its own colors. The values are stored in settings[0] under the same
 * keys PRO uses, so a card keeps its colors after upgrading. An unset color follows the Accent
 * Color setting (Modern, Amber, Clean) and then the style's own color; see getCardColorCss()
 * and WPZOOM_Recipe_Card_Block::get_card_color_css() for what each one paints.
 */

/* External dependencies */
import get from 'lodash/get';

/* Internal dependencies */
import { getBlockStyle, getCardColor, getAccentColor } from '../../../helpers/getBlockStyle';

/* WordPress dependencies */
import { __ } from '@wordpress/i18n';
import { BaseControl, ColorPalette, PanelBody } from '@wordpress/components';

// Palettes: the style's own color first, as in PRO.
const accentColors = ( own ) => [
    { name: __( 'Default', 'recipe-card-blocks-by-wpzoom' ), color: own },
    { name: __( 'Orange', 'recipe-card-blocks-by-wpzoom' ), color: '#e99e3c' },
    { name: __( 'Red', 'recipe-card-blocks-by-wpzoom' ), color: '#d1453b' },
    { name: __( 'Green', 'recipe-card-blocks-by-wpzoom' ), color: '#289283' },
    { name: __( 'Blue', 'recipe-card-blocks-by-wpzoom' ), color: '#2f5f9e' },
    { name: __( 'Plum', 'recipe-card-blocks-by-wpzoom' ), color: '#7a3e65' },
].filter( ( item, index, list ) => list.findIndex( ( other ) => other.color === item.color ) === index );

const PALETTES = {
    defaultHighlight: [
        { name: __( 'Default', 'recipe-card-blocks-by-wpzoom' ), color: '#fbf9e7' },
        { name: __( 'Vanilla', 'recipe-card-blocks-by-wpzoom' ), color: '#fff7d6' },
        { name: __( 'Soft Peach', 'recipe-card-blocks-by-wpzoom' ), color: '#ffeeda' },
        { name: __( 'Mint Light', 'recipe-card-blocks-by-wpzoom' ), color: '#eef8ef' },
        { name: __( 'Powder Blue', 'recipe-card-blocks-by-wpzoom' ), color: '#edf4fb' },
    ],
    simpleBackground: [
        { name: __( 'Default', 'recipe-card-blocks-by-wpzoom' ), color: '#fff3e0' },
        { name: __( 'Warm Cream', 'recipe-card-blocks-by-wpzoom' ), color: '#fdf0d6' },
        { name: __( 'Peach', 'recipe-card-blocks-by-wpzoom' ), color: '#ffe8cc' },
        { name: __( 'Soft Mint', 'recipe-card-blocks-by-wpzoom' ), color: '#edf7ef' },
        { name: __( 'Cool Gray', 'recipe-card-blocks-by-wpzoom' ), color: '#f2f4f7' },
    ],
    baseBackground: [
        { name: __( 'Default', 'recipe-card-blocks-by-wpzoom' ), color: '#f5f7fa' },
        { name: __( 'Cool Gray', 'recipe-card-blocks-by-wpzoom' ), color: '#eef3f8' },
        { name: __( 'Warm Sand', 'recipe-card-blocks-by-wpzoom' ), color: '#f8f3ea' },
        { name: __( 'Soft Mint', 'recipe-card-blocks-by-wpzoom' ), color: '#eef6f1' },
        { name: __( 'Lavender Mist', 'recipe-card-blocks-by-wpzoom' ), color: '#f4f1f8' },
    ],
    baseBorder: [
        { name: __( 'Default', 'recipe-card-blocks-by-wpzoom' ), color: '#d9e1eb' },
        { name: __( 'Cool Gray', 'recipe-card-blocks-by-wpzoom' ), color: '#c9d4e0' },
        { name: __( 'Slate', 'recipe-card-blocks-by-wpzoom' ), color: '#b7c4d3' },
        { name: __( 'Warm Gray', 'recipe-card-blocks-by-wpzoom' ), color: '#d7d0c6' },
        { name: __( 'Mint Gray', 'recipe-card-blocks-by-wpzoom' ), color: '#cbded5' },
    ],
    onyxInk: [
        { name: __( 'Default', 'recipe-card-blocks-by-wpzoom' ), color: '#111111' },
        { name: __( 'Charcoal', 'recipe-card-blocks-by-wpzoom' ), color: '#2b2b2b' },
        { name: __( 'Midnight', 'recipe-card-blocks-by-wpzoom' ), color: '#1b2438' },
        { name: __( 'Espresso', 'recipe-card-blocks-by-wpzoom' ), color: '#33261d' },
        { name: __( 'Oxblood', 'recipe-card-blocks-by-wpzoom' ), color: '#5a1a1f' },
    ],
    onyxPanel: [
        { name: __( 'Default', 'recipe-card-blocks-by-wpzoom' ), color: '#f2f2f2' },
        { name: __( 'Light Gray', 'recipe-card-blocks-by-wpzoom' ), color: '#f7f7f7' },
        { name: __( 'Warm Gray', 'recipe-card-blocks-by-wpzoom' ), color: '#f4f1ec' },
        { name: __( 'Cool Gray', 'recipe-card-blocks-by-wpzoom' ), color: '#eef1f4' },
        { name: __( 'Mid Gray', 'recipe-card-blocks-by-wpzoom' ), color: '#e6e6e6' },
    ],
    onyxLine: [
        { name: __( 'Default', 'recipe-card-blocks-by-wpzoom' ), color: '#e2e2e2' },
        { name: __( 'Mid Gray', 'recipe-card-blocks-by-wpzoom' ), color: '#cfcfcf' },
        { name: __( 'Warm Gray', 'recipe-card-blocks-by-wpzoom' ), color: '#d9d4cc' },
        { name: __( 'Cool Gray', 'recipe-card-blocks-by-wpzoom' ), color: '#d3d9df' },
        { name: __( 'Ink', 'recipe-card-blocks-by-wpzoom' ), color: '#111111' },
    ],
};

/**
 * The controls for one style. `own` is the style's color when nothing is picked.
 *
 * @param {string} style The block style.
 *
 * @return {Array} Controls.
 */
function getStyleControls( style ) {
    // Modern, Amber and Clean fall back to the Accent Color setting first.
    const accent = getAccentColor().toLowerCase();

    switch ( style ) {
        case 'default':
            return [
                { key: 'primary_color', label: __( 'Accent Color', 'recipe-card-blocks-by-wpzoom' ), help: __( 'Detail icons, ingredient checkboxes and the notes badge.', 'recipe-card-blocks-by-wpzoom' ), own: accent || '#041728', palette: accentColors( '#041728' ) },
                { key: 'default_highlight_color', label: __( 'Highlight Color', 'recipe-card-blocks-by-wpzoom' ), help: __( 'Background of the ingredients and the notes.', 'recipe-card-blocks-by-wpzoom' ), own: '#fbf9e7', palette: PALETTES.defaultHighlight },
            ];
        case 'newdesign':
            return [
                { key: 'primary_color', label: __( 'Accent Color', 'recipe-card-blocks-by-wpzoom' ), help: __( 'Detail icons and the ingredient checkboxes.', 'recipe-card-blocks-by-wpzoom' ), own: accent || '#e99e3c', palette: accentColors( '#e99e3c' ) },
            ];
        case 'simple':
            return [
                { key: 'primary_color', label: __( 'Accent Color', 'recipe-card-blocks-by-wpzoom' ), help: __( 'Print button, detail icons, ingredient checkboxes and the notes badge.', 'recipe-card-blocks-by-wpzoom' ), own: accent || '#041728', palette: accentColors( '#041728' ) },
                { key: 'simple_background_color', label: __( 'Background Color', 'recipe-card-blocks-by-wpzoom' ), help: __( 'Background of the header and the notes.', 'recipe-card-blocks-by-wpzoom' ), own: '#fff3e0', palette: PALETTES.simpleBackground },
            ];
        case 'base':
            return [
                { key: 'primary_color', label: __( 'Accent Color', 'recipe-card-blocks-by-wpzoom' ), help: __( 'Buttons, detail icons, ticked ingredients and the notes badge.', 'recipe-card-blocks-by-wpzoom' ), own: '#041728', palette: accentColors( '#041728' ) },
                { key: 'base_background_color', label: __( 'Background Color', 'recipe-card-blocks-by-wpzoom' ), help: __( 'Background of the details panel and the notes.', 'recipe-card-blocks-by-wpzoom' ), own: '#f5f7fa', palette: PALETTES.baseBackground },
                { key: 'base_border_color', label: __( 'Border Color', 'recipe-card-blocks-by-wpzoom' ), help: __( 'The card border and the dividers.', 'recipe-card-blocks-by-wpzoom' ), own: '#d9e1eb', palette: PALETTES.baseBorder },
            ];
        case 'onyx':
            return [
                { key: 'onyx_ink_color', label: __( 'Ink Color', 'recipe-card-blocks-by-wpzoom' ), help: __( 'Header band, step numbers, checkboxes and headings.', 'recipe-card-blocks-by-wpzoom' ), own: '#111111', palette: PALETTES.onyxInk },
                { key: 'onyx_panel_color', label: __( 'Panel Color', 'recipe-card-blocks-by-wpzoom' ), help: __( 'Details, ingredients and notes panels.', 'recipe-card-blocks-by-wpzoom' ), own: '#f2f2f2', palette: PALETTES.onyxPanel },
                { key: 'onyx_line_color', label: __( 'Line Color', 'recipe-card-blocks-by-wpzoom' ), help: __( 'The card border and the dividers.', 'recipe-card-blocks-by-wpzoom' ), own: '#e2e2e2', palette: PALETTES.onyxLine },
            ];
        default:
            return [];
    }
}

const StyleColors = ( { attributes, setAttributes, className, cardId } ) => {
    const style = getBlockStyle( className );
    const controls = getStyleControls( style );

    if ( ! controls.length ) {
        return null;
    }

    const settings = get( attributes, [ 'settings', 0 ] ) || {};

    const onChange = ( key, color ) => {
        const newSettings = attributes.settings ? attributes.settings.slice() : [];
        // Cleared, or set back to the style's own color: store nothing, so the card keeps
        // following the settings page.
        newSettings[ 0 ] = { ...( newSettings[ 0 ] || {} ), [ key ]: color ? color.toLowerCase() : '' };
        setAttributes( { settings: newSettings } );
    };

    return (
        <PanelBody className="wpzoom-recipe-card-colors" title={ __( 'Colors', 'recipe-card-blocks-by-wpzoom' ) } initialOpen={ true }>
            { controls.map( ( control ) => {
                const value = getCardColor( settings, control.key ) || control.own;

                return (
                    <BaseControl
                        key={ control.key }
                        id={ `wpzoom-rcb-${ cardId }-${ control.key }` }
                        label={ control.label }
                        help={ control.help }
                    >
                        <ColorPalette
                            colors={ control.palette }
                            value={ value }
                            onChange={ ( color ) => onChange( control.key, color ) }
                        />
                    </BaseControl>
                );
            } ) }
        </PanelBody>
    );
};

export default StyleColors;
