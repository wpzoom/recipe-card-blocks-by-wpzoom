/**
 * Gutenberg Blocks
 *
 * All blocks related JavaScript files should be imported here.
 * You can create a new block folder in this dir and include code
 * for that block here as well.
 *
 * All blocks should be included here since this is the file that
 * Webpack is compiling as the input file.
 */

import './wpzoom-blocks/details/block.js';
import './wpzoom-blocks/directions/block.js';
import './wpzoom-blocks/ingredients/block.js';
import './wpzoom-blocks/jump-to-recipe/block.js';
import './wpzoom-blocks/print-recipe/block.js';
import './wpzoom-blocks/recipe-card/block.js';
import './wpzoom-blocks/nutrition/block.js';

( function() {
    const el = wp.element.createElement;
    const SVG = wp.components.SVG;
    const path1 = el( 'path', { d: "M15.293 3.98a3.313 3.313 0 0 1 3.309 3.313 3.313 3.313 0 0 1-3.309 3.309c-.445 0-.875-.086-1.273-.25a5.964 5.964 0 0 0 1.714-2.79.66.66 0 1 0-1.273-.352A4.639 4.639 0 0 1 10 10.603 4.639 4.639 0 0 1 5.54 7.21a.66.66 0 1 0-1.274.351 5.933 5.933 0 0 0 1.714 2.79c-.398.164-.828.25-1.273.25a3.313 3.313 0 0 1-3.309-3.309A3.313 3.313 0 0 1 4.707 3.98c.188 0 .375.016.566.051a.665.665 0 0 0 .688-.328A4.638 4.638 0 0 1 10 1.336c1.672 0 3.219.906 4.04 2.367a.665.665 0 0 0 .687.328c.191-.035.378-.05.566-.05zm0 0", fill: "#ca8b48" } );
    const path2 = el( 'path', { d: "M14.633 11.875v6.395a.401.401 0 0 1-.395.394H5.762a.399.399 0 0 1-.395-.394v-6.395a4.614 4.614 0 0 0 1.813-.672 5.898 5.898 0 0 0 5.64 0 4.592 4.592 0 0 0 1.813.672zm0 0", fill: "#e8a863" } );
    const path3 = el( 'path', { d: "M19.918 7.293a4.63 4.63 0 0 1-3.969 4.578v6.399c0 .945-.77 1.714-1.71 1.714H5.761c-.942 0-1.711-.77-1.711-1.714V11.87A4.63 4.63 0 0 1 .082 7.293a4.63 4.63 0 0 1 4.961-4.617A5.962 5.962 0 0 1 10 .016a5.962 5.962 0 0 1 4.957 2.66 4.63 4.63 0 0 1 4.96 4.617zm-1.316 0a3.313 3.313 0 0 0-3.309-3.313c-.188 0-.375.016-.566.051a.665.665 0 0 1-.688-.328A4.638 4.638 0 0 0 10 1.336a4.638 4.638 0 0 0-4.04 2.367.665.665 0 0 1-.687.328 3.105 3.105 0 0 0-.562-.05 3.314 3.314 0 0 0-3.313 3.312 3.313 3.313 0 0 0 4.582 3.059 5.933 5.933 0 0 1-1.714-2.79.66.66 0 1 1 1.273-.352A4.639 4.639 0 0 0 10 10.603a4.647 4.647 0 0 0 4.465-3.391.655.655 0 0 1 .808-.461c.352.098.555.46.461.813a5.964 5.964 0 0 1-1.714 2.789c.398.164.828.25 1.273.25a3.313 3.313 0 0 0 3.309-3.309zm-3.97 10.977v-6.395a4.592 4.592 0 0 1-1.812-.672 5.898 5.898 0 0 1-5.64 0 4.614 4.614 0 0 1-1.813.672v6.395c0 .214.18.394.395.394h8.476c.211 0 .395-.18.395-.394zm0 0", fill: "#000" } );
    const g = el( 'g', { 'stroke': 'none', 'fill-rule': 'nonzero', 'fill-opacity': 1 }, path1, path2, path3 );
    const svgIcon = el( SVG, { x: 0, y: 0, width: 20, height: 20, viewBox: '0 0 20 20'}, g );
    wp.blocks.updateCategory( 'wpzoom-recipe-card', { icon: svgIcon } );
} )();
