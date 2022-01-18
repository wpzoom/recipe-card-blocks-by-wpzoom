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
import './wpzoom-blocks/recipe-block-from-posts/block.js';

/* Internal dependencies */
import icon from './icon';

( function() {
    wp.blocks.updateCategory( 'wpzoom-recipe-card', { icon } );
}() );
