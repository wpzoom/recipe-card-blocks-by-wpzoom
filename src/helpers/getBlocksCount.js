import _map from "lodash/map";
import _filter from "lodash/filter";
import _indexOf from "lodash/indexOf";

/**
 * Get total number of blocks from post.
 *
 * @param {array} blocksNames  	The array of blocks name.
 * @param {string} namespace  	The namespace of registered block type.
 *
 * @returns {number} The length of blocks.
 */
export function getBlocksCount( blocksNames, namespace = 'wpzoom-recipe-card' ) {
	const { select }    = wp.data;
	const blocksList    = select('core/editor').getBlocks();
	let blocks 			= _map( blocksNames, function( value, key ) { return namespace + '/' + value; } );

	const wpzoomBlocksFilter = _filter( blocksList, function( item ) { return _indexOf( blocks, item.name ) !== -1 } );

	return wpzoomBlocksFilter.length;
}