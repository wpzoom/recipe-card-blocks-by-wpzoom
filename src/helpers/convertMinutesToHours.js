import floor from "lodash/floor";
import toNumber from "lodash/toNumber";
import isNull from "lodash/isNull";

const { _n } = wp.i18n;

export function getNumberFromString( string ) {
	const re = /\d+/g;
	const match = re.exec( string );

	return ! isNull( match ) ? toNumber( match[0] ) : 0;
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
	const hours = floor( time / 60 );
	const mins = ( time % 60 );

	if ( ! time ) {
		return minutes;
	}

	if ( returnObject ) {
		if ( hours ) {
			object['hours']['value'] = hours;
			object['hours']['unit'] = _n( "hour", "hours", toNumber( hours ), "wpzoom-recipe-card" );
		}

		if ( mins ) {
			object['minutes']['value'] = mins;
			object['minutes']['unit'] = _n( "minute", "minutes", toNumber( mins ), "wpzoom-recipe-card" );
		}

		return object;
	}

	if ( hours ) {
		output += hours + ' ' + _n( "hour", "hours", toNumber( hours ), "wpzoom-recipe-card" );
	}

	if ( mins ) {
		output += ' ' + mins;
		output += ' ' + _n( "minute", "minutes", toNumber( mins ), "wpzoom-recipe-card" );
	}

	return output;
}