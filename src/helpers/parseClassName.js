import trim from "lodash/trim";
import includes from "lodash/includes";

/**
 * Exclude uneeded class names.
 *
 * @param {array} className  	The block classname.
 * @param {array} exclude  		The classnames to exclude.
 *
 * @returns {string} className.
 */
export function excludeClassNames( className, exclude ) {
	let classname = className;
	exclude.map( (item, index) => {
		if ( includes( classname, item ) ) {
			classname = trim( classname, item );
		}
	} );
	return classname;
}