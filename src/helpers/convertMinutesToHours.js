import _floor from "lodash/floor";
import _toNumber from "lodash/toNumber";
import _isNull from "lodash/isNull";
import _isUndefined from "lodash/isUndefined";

const { _n } = wp.i18n;

export function getNumberFromString( string ) {
	const re = /\d+/g;
	const match = re.exec( string );

	return ! _isNull( match ) ? _toNumber( match[0] ) : 0;
}

/**
 * Get total number of blocks from post.
 *
 * @param {array} blocksNames  	The array of blocks name.
 * @param {string} namespace  	The namespace of registered block type.
 *
 * @returns {number} The length of blocks.
 */
export function convertMinutesToHours( minutes, showUnit = false ) {
	let output = '';

	const time = getNumberFromString( minutes );
	const hours = _floor( time / 60 );
	const mins = ( time % 60 );

	if ( ! time ) {
		return minutes;
	}

	if ( hours ) {
		output += hours + ' ' + _n( "hour", "hours", _toNumber( hours ), "wpzoom-recipe-card" );
	}
	if ( mins ) {
		output += ' ' + mins;

		if ( showUnit ) {
			output += ' ' + _n( "minute", "minutes", _toNumber( mins ), "wpzoom-recipe-card" );
		}
	}

	return output;
}