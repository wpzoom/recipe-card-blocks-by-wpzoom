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

export function convertMinutesToHours( minutes, returnObject = false ) {
	let output = '';
	let object = {
		hours: {
			value: '',
			unit: ''
		},
		minutes: {
			value: '',
			unit: ''
		}
	};

	const time = getNumberFromString( minutes );
	const hours = _floor( time / 60 );
	const mins = ( time % 60 );

	if ( ! time ) {
		return minutes;
	}

	if ( returnObject ) {
		if ( hours ) {
			object['hours']['value'] = hours;
			object['hours']['unit'] = _n( "hour", "hours", _toNumber( hours ), "wpzoom-recipe-card" );
		}

		if ( mins ) {
			object['minutes']['value'] = mins;
			object['minutes']['unit'] = _n( "minute", "minutes", _toNumber( mins ), "wpzoom-recipe-card" );
		}

		return object;
	}

	if ( hours ) {
		output += hours + ' ' + _n( "hour", "hours", _toNumber( hours ), "wpzoom-recipe-card" );
	}

	if ( mins ) {
		output += ' ' + mins;
		output += ' ' + _n( "minute", "minutes", _toNumber( mins ), "wpzoom-recipe-card" );
	}

	return output;
}