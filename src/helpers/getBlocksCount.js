import map from "lodash/map";
import filter from "lodash/filter";
import indexOf from "lodash/indexOf";

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
	let blocks 			= map( blocksNames, function( value, key ) { return namespace + '/' + value; } );

	const wpzoomBlocksFilter = filter( blocksList, function( item ) { return indexOf( blocks, item.name ) !== -1 } );

	return wpzoomBlocksFilter.length;
}