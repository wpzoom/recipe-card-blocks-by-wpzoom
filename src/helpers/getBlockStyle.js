/**
 * Get block style.
 *
 * @param {array} className  	The block classname.
 *
 * @returns {number} Block style.
 */
export function getBlockStyle( className ) {
	const { setting_options } = wpzoomRecipeCard;
	const regex = /is-style-(\S*)/g;
	let m = regex.exec( className );
	return m !== null ? m[1] : setting_options.wpzoom_rcb_settings_template;
}