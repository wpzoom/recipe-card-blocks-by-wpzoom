/**
 * External dependencies
 */
import { get } from 'lodash';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

const CookMode = ( { settingOptions } ) => {
    const isCookModeEnabled = get( settingOptions, 'wpzoom_rcb_settings_recipe_enable_prevent_sleep_toggle' ) === '1';

    if ( ! isCookModeEnabled ) {
        return null;
    }

    const isDefaultOn = get( settingOptions, 'wpzoom_rcb_settings_recipe_prevent_sleep_toggle_status' ) === '1';
    const cookModeLabel = get( settingOptions, 'wpzoom_rcb_settings_recipe_prevent_sleep_label' ) || __( 'Cook Mode', 'recipe-card-blocks-by-wpzoom' );
    const cookModeDescription = get( settingOptions, 'wpzoom_rcb_settings_recipe_prevent_sleep_description' ) || __( 'Keep the screen of your device on', 'recipe-card-blocks-by-wpzoom' );

    return (
        <div className="wpzoom-nosleep-toggle-container">
            <label className="switch">
                <input type="checkbox" checked={ isDefaultOn } onChange={ () => {} } />
                <div className="slider round"></div>
            </label>
            { cookModeLabel && <span className="wpzoom-nosleep-label">{ cookModeLabel }</span> }
            { /* On the site this is a button that opens the description as a tooltip (4.0.0). */ }
            { cookModeDescription && (
                <span className="wpzoom-nosleep-help">
                    <span className="wpzoom-nosleep-help-toggle" title={ cookModeDescription }>?</span>
                </span>
            ) }
        </div>
    );
};

export default CookMode;
